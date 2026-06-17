<?php
/**
 * Message table schema helper
 *
 * Ensures tblMessage supports user/admin routing and optional product links
 */

if (!function_exists('pastimesEnsureMessageSchema')) {
    function pastimesEnsureMessageSchema(mysqli $conn): void
    {
        static $schemaChecked = false;
        if ($schemaChecked) {
            return;
        }

        $schemaChecked = true;

        if (!$conn->query("CREATE TABLE IF NOT EXISTS tblMessage (
            messageID INT AUTO_INCREMENT PRIMARY KEY,
            senderType VARCHAR(20) NOT NULL,
            senderID INT NOT NULL,
            receiverType VARCHAR(20) NOT NULL DEFAULT 'user',
            receiverID INT NOT NULL,
            productID INT NULL,
            subject VARCHAR(200) NOT NULL,
            messageText TEXT NOT NULL,
            isRead TINYINT(1) NOT NULL DEFAULT 0,
            sentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")) {
            return;
        }

        $columns = array();
        $colResult = $conn->query("SHOW COLUMNS FROM tblMessage");
        if ($colResult) {
            while ($col = $colResult->fetch_assoc()) {
                $columns[$col['Field']] = true;
            }
            $colResult->free();
        }

        $addedReceiverType = false;
        if (!isset($columns['receiverType'])) {
            $conn->query("ALTER TABLE tblMessage ADD COLUMN receiverType VARCHAR(20) NOT NULL DEFAULT 'user' AFTER senderID");
            $addedReceiverType = true;
        }
        if (!isset($columns['receiverID'])) {
            $conn->query("ALTER TABLE tblMessage ADD COLUMN receiverID INT NOT NULL AFTER receiverType");
        }
        if (!isset($columns['productID'])) {
            $conn->query("ALTER TABLE tblMessage ADD COLUMN productID INT NULL AFTER receiverID");
        }
        if (!isset($columns['isRead'])) {
            $conn->query("ALTER TABLE tblMessage ADD COLUMN isRead TINYINT(1) NOT NULL DEFAULT 0 AFTER messageText");
        }
        if (!isset($columns['sentDate'])) {
            $conn->query("ALTER TABLE tblMessage ADD COLUMN sentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER isRead");
        }

        $fkSql = "SELECT CONSTRAINT_NAME
                  FROM information_schema.KEY_COLUMN_USAGE
                  WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'tblMessage'
                    AND COLUMN_NAME = 'receiverID'
                    AND REFERENCED_TABLE_NAME = 'tblUser'";
        $fkResult = $conn->query($fkSql);
        if ($fkResult) {
            while ($fkRow = $fkResult->fetch_assoc()) {
                $fkName = $fkRow['CONSTRAINT_NAME'];
                if ($fkName !== '') {
                    $conn->query("ALTER TABLE tblMessage DROP FOREIGN KEY `" . $conn->real_escape_string($fkName) . "`");
                }
            }
            $fkResult->free();
        }

        // Legacy routing backfill should run only once, when receiverType is
        // first introduced. Running this on every request would incorrectly
        // convert buyer->seller user messages to admin messages.
        if ($addedReceiverType) {
            $conn->query("UPDATE tblMessage
                          SET receiverType = 'admin'
                          WHERE senderType = 'user' AND receiverType = 'user'");
        }

        // Safety repair for any already-misrouted product enquiries:
        // if a user-sent product message has a valid seller owner, ensure it
        // routes to that seller as receiverType='user'.
        $conn->query("UPDATE tblMessage m
                      INNER JOIN tblClothes c ON m.productID = c.clothingID
                      SET m.receiverType = 'user',
                          m.receiverID = c.sellerID
                      WHERE m.senderType = 'user'
                        AND m.productID IS NOT NULL
                        AND c.sellerID IS NOT NULL
                        AND c.sellerID > 0
                        AND m.receiverType = 'admin'");

        $conn->query("CREATE INDEX idx_tblMessage_receiver_lookup ON tblMessage (receiverType, receiverID, isRead, sentDate)");
        $conn->query("CREATE INDEX idx_tblMessage_sender_lookup ON tblMessage (senderType, senderID, sentDate)");
        $conn->query("CREATE INDEX idx_tblMessage_product ON tblMessage (productID)");
    }
}
?>
