<?php
/**
 * Sell an Item Page
 *
 * Logged-in users can submit clothing items for sale using a multi-step form.
 * Items are marked as pending and must be approved by admin.
 */

session_start();
include '../includes/DBConn.php';

// Only logged-in users may access this page
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$message = '';
$messageType = '';

// Form variables (kept so the form can repopulate after a failed submit)
$clothingName = '';
$brand = '';
$category = '';
$gender = '';
$size = '';
$clothingCondition = '';
$description = '';
$imageURL = '';
$price = '';
$quantity = '';

// Find out which columns tblClothes actually has, so we only insert valid ones.
// This keeps the page working even if the table has no gender/quantity column.
$existingColumns = array();
$colResult = $conn->query("SHOW COLUMNS FROM tblClothes");
if ($colResult) {
    while ($col = $colResult->fetch_assoc()) {
        $existingColumns[] = $col['Field'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clothingName = isset($_POST['clothingName']) ? trim($_POST['clothingName']) : '';
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $size = isset($_POST['size']) ? trim($_POST['size']) : '';
    $clothingCondition = isset($_POST['clothingCondition']) ? trim($_POST['clothingCondition']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $imageURL = isset($_POST['imageURL']) ? trim($_POST['imageURL']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';

    // Handle optional file upload
    if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imageFile']['tmp_name'];
        $fileName = $_FILES['imageFile']['name'];
        $fileSize = $_FILES['imageFile']['size'];
        $fileType = $_FILES['imageFile']['type'];

        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array($fileType, $allowedTypes)) {
            $message = "Only JPEG, PNG, GIF, and WebP image formats are allowed.";
            $messageType = "error";
        } elseif ($fileSize > 5242880) { // 5MB max
            $message = "File size must be less than 5MB.";
            $messageType = "error";
        } else {
            $uploadDir = '../images/uploads/sellers/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $uploadedFileName = 'item_' . $userID . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $uploadedFileName;
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                $imageURL = $uploadPath; // Use uploaded file as image URL
            } else {
                $message = "Error uploading file. Please try again.";
                $messageType = "error";
            }
        }
    }

    // Validate required fields and numeric values
    if ($messageType === "error") {
        // keep existing upload error message
    } elseif ($clothingName === '' || $category === '' || $size === '' || $clothingCondition === '') {
        $message = "Please fill in all required fields.";
        $messageType = "error";
    } elseif (!is_numeric($price) || (float) $price <= 0) {
        $message = "Please enter a valid price (numbers only).";
        $messageType = "error";
    } elseif (!is_numeric($quantity) || (int) $quantity < 1) {
        $message = "Please enter a valid quantity (numbers only).";
        $messageType = "error";
    } else {
        $priceVal = (float) $price;
        $quantityVal = (int) $quantity;

        // Candidate columns => array(value, mysqli bind type)
        $candidates = array(
            'clothingName'      => array($clothingName, 's'),
            'brand'             => array($brand, 's'),
            'category'          => array($category, 's'),
            'gender'            => array($gender, 's'),
            'description'       => array($description, 's'),
            'size'              => array($size, 's'),
            'clothingCondition' => array($clothingCondition, 's'),
            'price'             => array($priceVal, 'd'),
            'quantity'          => array($quantityVal, 'i'),
            'imageURL'          => array($imageURL, 's'),
            'sellerID'          => array($userID, 'i'),
            'approvalStatus'    => array('pending', 's'),
        );

        // Build the INSERT using only columns that exist in the table
        $cols = array();
        $placeholders = array();
        $types = '';
        $values = array();
        foreach ($candidates as $colName => $info) {
            if (in_array($colName, $existingColumns)) {
                $cols[] = $colName;
                $placeholders[] = '?';
                $types .= $info[1];
                $values[] = $info[0];
            }
        }

        $sql = "INSERT INTO tblClothes (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $message = "Database error: " . $conn->error;
            $messageType = "error";
        } else {
            $stmt->bind_param($types, ...$values);
            if ($stmt->execute()) {
                $message = "Your item has been submitted for admin approval.";
                $messageType = "success";
                // Clear the form values on success
                $clothingName = $brand = $category = $gender = $size = '';
                $clothingCondition = $description = $imageURL = $price = $quantity = '';
            } else {
                $message = "Error submitting item: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell an Item - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css?v=3">
</head>
<body class="sell-page-body">
    <?php include '../includes/navbar.php'; ?>

    <!-- Message Icon + Notification Popover (only for logged-in users) -->
    <?php include '../includes/messagePopover.php'; ?>

    <!-- Shopping Cart Icon -->
    <a href="cart.php" class="cart-icon-link" title="Shopping Cart">
        <i class="fa-solid fa-bag-shopping"></i>
        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="cart-badge"><?php echo count($_SESSION['cart']); ?></span>
        <?php endif; ?>
    </a>

    <main>
        <div class="sell-wrap">
            <div class="sell-card">
                <h2>Sell Your Item</h2>
                <p class="sell-intro">List your pre-loved fashion piece on Pastimes. Our team reviews every submission before it goes live.</p>

                <?php if (!empty($message)): ?>
                    <div class="<?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="sell-progress-row">
                    <div class="sell-progress">
                        <div class="sell-progress-bar" id="progressBar"></div>
                    </div>
                    <div class="sell-step-count">Step <span id="currentStep">1</span> of 4</div>
                </div>

                <form method="POST" action="" enctype="multipart/form-data" id="sellForm">

                    <!-- Step 1: Item Information -->
                    <div class="sell-step active" data-step="1">
                        <div class="sell-step-title">Item Information</div>

                        <div class="sell-grid">
                            <div class="form-group sell-full">
                                <label for="clothingName">Item Name *</label>
                                <input type="text" id="clothingName" name="clothingName" value="<?php echo htmlspecialchars($clothingName); ?>" placeholder="e.g., Wool Overcoat" required>
                            </div>

                            <div class="form-group">
                                <label for="brand">Brand</label>
                                <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($brand); ?>" placeholder="e.g., Acne Studios">
                            </div>

                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select id="category" name="category" required>
                                    <option value="">Select a category</option>
                                    <option value="Men" <?php if ($category === 'Men') echo 'selected'; ?>>Men</option>
                                    <option value="Women" <?php if ($category === 'Women') echo 'selected'; ?>>Women</option>
                                    <option value="Unisex" <?php if ($category === 'Unisex') echo 'selected'; ?>>Unisex</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="">Select gender</option>
                                    <option value="Men" <?php if ($gender === 'Men') echo 'selected'; ?>>Men</option>
                                    <option value="Women" <?php if ($gender === 'Women') echo 'selected'; ?>>Women</option>
                                    <option value="Unisex" <?php if ($gender === 'Unisex') echo 'selected'; ?>>Unisex</option>
                                    <option value="Kids" <?php if ($gender === 'Kids') echo 'selected'; ?>>Kids</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="size">Size *</label>
                                <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($size); ?>" placeholder="e.g., S, M, L, XL" required>
                            </div>

                            <div class="form-group">
                                <label for="clothingCondition">Condition *</label>
                                <select id="clothingCondition" name="clothingCondition" required>
                                    <option value="">Select condition</option>
                                    <option value="New" <?php if ($clothingCondition === 'New') echo 'selected'; ?>>New</option>
                                    <option value="Like New" <?php if ($clothingCondition === 'Like New') echo 'selected'; ?>>Like New</option>
                                    <option value="Good" <?php if ($clothingCondition === 'Good') echo 'selected'; ?>>Good</option>
                                    <option value="Fair" <?php if ($clothingCondition === 'Fair') echo 'selected'; ?>>Fair</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Description -->
                    <div class="sell-step" data-step="2">
                        <div class="sell-step-title">Description</div>

                        <div class="form-group">
                            <label for="description">Tell buyers about your item</label>
                            <textarea id="description" name="description" maxlength="1000" placeholder="Describe the fabric, fit, styling and any flaws..."><?php echo htmlspecialchars($description); ?></textarea>
                            <div class="sell-char-count"><span id="charCount">0</span> / 1000 characters</div>
                        </div>
                    </div>

                    <!-- Step 3: Images -->
                    <div class="sell-step" data-step="3">
                        <div class="sell-step-title">Images</div>

                        <div class="form-group">
                            <label>Upload Image</label>
                            <div class="sell-dropzone" id="dropzone">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <div class="sell-dz-main">Drag &amp; drop an image here, or click to browse</div>
                                <div class="sell-dz-hint">JPEG, PNG, GIF or WebP &middot; Max 5MB</div>
                            </div>
                            <input type="file" id="imageFile" name="imageFile" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;">
                        </div>

                        <div class="form-group">
                            <label for="imageURL">Or paste an image URL / filename</label>
                            <input type="text" id="imageURL" name="imageURL" value="<?php echo htmlspecialchars($imageURL); ?>" placeholder="e.g., https://example.com/coat.jpg">
                        </div>

                        <div class="sell-image-preview" id="imagePreview">
                            <img id="imagePreviewImg" src="" alt="Image preview">
                        </div>
                    </div>

                    <!-- Step 4: Pricing & Review -->
                    <div class="sell-step" data-step="4">
                        <div class="sell-step-title">Pricing &amp; Review</div>

                        <div class="sell-grid">
                            <div class="form-group">
                                <label for="price">Price (R) *</label>
                                <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" step="0.01" min="0.01" placeholder="0.00" required>
                            </div>

                            <div class="form-group">
                                <label for="quantity">Quantity *</label>
                                <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity !== '' ? $quantity : '1'); ?>" step="1" min="1" placeholder="1" required>
                            </div>
                        </div>

                        <div class="sell-review" id="reviewBox" style="margin-top:1.75rem;"></div>
                    </div>

                    <div class="sell-actions">
                        <button type="button" class="sell-btn sell-btn-ghost" id="backBtn" style="visibility:hidden;">Back</button>
                        <button type="button" class="sell-btn sell-btn-primary" id="nextBtn">Next</button>
                        <button type="submit" class="sell-btn sell-btn-primary" id="submitBtn" style="display:none;">Submit Listing</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <footer class="footer">
        <!-- Top Card Area -->
        <div class="footer-top-card"></div>

        <!-- Footer Content -->
        <div class="footer-content">
            <!-- Brand Section -->
            <div class="footer-brand">
                <h2 class="footer-brand-title">Pastimes</h2>
                <p class="footer-copyright">&copy; 2026 Pastimes. All rights reserved.</p>
            </div>

            <!-- Footer Grid (4 Columns) -->
            <div class="footer-grid">
                <!-- Product Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Product</h4>
                    <ul class="footer-links">
                        <li><a href="pages/shop.php" class="footer-link">Shop</a></li>
                        <li><a href="pages/sell-item.php" class="footer-link">Sell Item</a></li>
                        <li><a href="pages/cart.php" class="footer-link">Cart</a></li>
                    </ul>
                </div>

                <!-- Company Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Company</h4>
                    <ul class="footer-links">
                        <li><a href="index.php" class="footer-link">About</a></li>
                        <li><a href="pages/account.php" class="footer-link">Account</a></li>
                        <li><a href="pages/login.php" class="footer-link">Login</a></li>
                        <li><a href="pages/register.php" class="footer-link">Register</a></li>
                    </ul>
                </div>

                <!-- Resources Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Resources</h4>
                    <ul class="footer-links">
                        <li><a href="index.php" class="footer-link">Help</a></li>
                        <li><a href="pages/my-messages.php" class="footer-link">Messages</a></li>
                        <li><a href="pages/my-listings.php" class="footer-link">Seller Listings</a></li>
                        <li><a href="admin/admin-login.php" class="footer-link">Admin</a></li>
                    </ul>
                </div>

                <!-- Social Links Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Social</h4>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link">Facebook</a></li>
                        <li><a href="#" class="footer-link">Instagram</a></li>
                        <li><a href="#" class="footer-link">YouTube</a></li>
                        <li><a href="#" class="footer-link">LinkedIn</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <script>
    (function () {
        var form              = document.getElementById('sellForm');
        var steps             = Array.prototype.slice.call(form.querySelectorAll('.sell-step'));
        var total             = steps.length;
        var current           = 0;

        var backBtn           = document.getElementById('backBtn');
        var nextBtn = document.getElementById('nextBtn');
        var submitBtn = document.getElementById('submitBtn');
        var progressBar = document.getElementById('progressBar');
        var currentStepLabel  = document.getElementById('currentStep');

        function showStep(index) {
            steps.forEach(function (step, i) {
                step.classList.toggle('active', i === index);
            });
            current = index;
            currentStepLabel.textContent = index + 1;
            progressBar.style.width = (((index + 1) / total) * 100) + '%';

            backBtn.style.visibility = (index === 0) ? 'hidden' : 'visible';

            if (index === total - 1) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = '';
                buildReview();
            } else {
                nextBtn.style.display = '';
                submitBtn.style.display = 'none';
            }
        }

        // Use HTML5 validation for the fields on the current step before moving on
        function validateStep(index) {
            var fields = steps[index].querySelectorAll('input, select, textarea');
            for (var i = 0; i < fields.length; i++) {
                if (!fields[i].checkValidity()) {
                    fields[i].reportValidity();
                    return false;
                }
            }
            return true;
        }

        nextBtn.addEventListener('click', function () {
            if (validateStep(current)) {
                showStep(current + 1);
            }
        });

        backBtn.addEventListener('click', function () {
            showStep(current - 1);
        });

        // Character counter for the description
        var description = document.getElementById('description');
        var charCount   = document.getElementById('charCount');
        function updateCount() {
            if (description && charCount) {
                charCount.textContent = description.value.length;
            }
        }
        if (description) {
            description.addEventListener('input', updateCount);
            updateCount();
        }

        // Image preview (URL or uploaded file)
        var imageURL     = document.getElementById('imageURL');
        var imageFile    = document.getElementById('imageFile');
        var dropzone     = document.getElementById('dropzone');
        var preview      = document.getElementById('imagePreview');
        var previewImg   = document.getElementById('imagePreviewImg');

        function showPreview(src) {
            if (src) {
                previewImg.src = src;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        function previewFile(file) {
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) { showPreview(e.target.result); };
                reader.readAsDataURL(file);
            }
        }

        if (imageURL) {
            imageURL.addEventListener('input', function () {
                showPreview(imageURL.value.trim());
            });
        }

        if (imageFile) {
            imageFile.addEventListener('change', function () {
                if (imageFile.files && imageFile.files[0]) {
                    previewFile(imageFile.files[0]);
                }
            });
        }

        // Drag & drop area
        if (dropzone && imageFile) {
            dropzone.addEventListener('click', function () {
                imageFile.click();
            });

            ['dragenter', 'dragover'].forEach(function (evt) {
                dropzone.addEventListener(evt, function (e) {
                    e.preventDefault();
                    dropzone.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (evt) {
                dropzone.addEventListener(evt, function (e) {
                    e.preventDefault();
                    dropzone.classList.remove('dragover');
                });
            });

            dropzone.addEventListener('drop', function (e) {
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                    imageFile.files = e.dataTransfer.files;
                    previewFile(e.dataTransfer.files[0]);
                }
            });
        }

        // Build the review summary on the last step
        function escapeHtml(text) {
            return text.replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function buildReview() {
            var rows = [
                ['Clothing Name', 'clothingName'],
                ['Brand', 'brand'],
                ['Category', 'category'],
                ['Gender', 'gender'],
                ['Size', 'size'],
                ['Condition', 'clothingCondition'],
                ['Description', 'description'],
                ['Image', 'imageURL'],
                ['Price', 'price'],
                ['Quantity', 'quantity']
            ];

            var html = '';
            rows.forEach(function (row) {
                var el = form.elements[row[1]];
                var val = el ? el.value.trim() : '';

                if (row[1] === 'imageURL' && !val && imageFile && imageFile.files && imageFile.files[0]) {
                    val = imageFile.files[0].name;
                }
                if (row[1] === 'price' && val) {
                    val = 'R ' + val;
                }
                if (!val) {
                    val = '\u2014';
                }

                html += '<div class="sell-review-row"><span class="label">' + row[0] +
                        '</span><span class="value">' + escapeHtml(val) + '</span></div>';
            });

            document.getElementById('reviewBox').innerHTML = html;
        }

        showStep(0);
    })();
    </script>
</body>
</html>
<?php
/*
This code is the original work of:
ST10441421 - Odirile Masemola
ST10450294 - Ripfumelo Mabasa
*/
?>
