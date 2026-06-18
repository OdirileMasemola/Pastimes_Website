# Pastimes

> A PHP/MySQL second-hand fashion marketplace with seller workflows, cart and checkout, messaging, and admin moderation — all in one.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)
![Session Auth](https://img.shields.io/badge/Session_Auth-grey?style=flat)

---

## Features

### Authentication & Accounts
- User registration, login, and logout
- Separate admin login/logout flow
- Admin verification workflow for user accounts (`isVerified` flow)
- Account profile page

### Shopping & Cart
- Product browsing and shop page
- Product details page
- Session-based cart with quantity increase, decrease, and remove
- Cart totals and order summary

### Orders & Checkout
- Auth-gated checkout — unauthenticated attempts redirect to login/register with a return path
- Order creation and order-item persistence on checkout
- Purchase history page
- Order tracking and status view for logged-in users
- Admin order management

### Messaging
- User inbox and message sending
- Admin messages view and reply
- Message popover with unread/read state handling

### Seller Workflow
- Seller item submission
- My listings page
- Admin seller request moderation (approve/reject)

### Admin Panel
- Dashboard overview
- Full CRUD for users, clothing items, and orders

---

## Project Structure

```text
Pastimes_Website/
├── index.php
├── myClothingStore.sql
├── admin/
│   ├── dashboard.php
│   ├── manage-clothes.php
│   ├── manage-users.php
│   ├── manage-orders.php
│   ├── manage-seller-requests.php
│   ├── messages.php
│   └── ... (add/edit/delete/login/logout)
├── assets/
│   └── style.css
├── includes/
│   ├── DBConn.php
│   ├── navbar.php
│   ├── messagePopover.php
│   └── ... (createTable, loadClothingStore, markMessagesRead, messageSchema)
├── images/
│   ├── products/
│   └── uploads/
│       ├── admin/
│       └── sellers/
└── pages/
    ├── shop.php
    ├── product-details.php
    ├── cart.php
    ├── checkout.php
    ├── login.php
    ├── register.php
    ├── sell-item.php
    ├── my-listings.php
    ├── my-messages.php
    ├── purchase-history.php
    └── account.php
```

---

## Database Tables

| Table | Description |
|---|---|
| `tblUser` | Registered users |
| `tblAdmin` | Admin accounts |
| `tblClothes` | Product/clothing listings |
| `tblOrder` | Placed orders |
| `tblOrderItem` | Line items per order |
| Messaging tables | Managed via `messageSchema.php` |

---

## Setup

**1. Create a MySQL database**

Commonly named `ClothingStore`. Use your MySQL client or CLI.

**2. Configure credentials**

Open `includes/DBConn.php` and set your host, username, password, and database name.

**3. Initialise the schema**

Option A — import the SQL dump:
```bash
mysql -u your_user -p ClothingStore < myClothingStore.sql
```

Option B — run the PHP loader:
```
http://localhost/Pastimes_Website/includes/loadClothingStore.php
```
Run `includes/createTable.php` first if tables don't exist yet.

**4. Serve via WAMP / Apache**

Open in your browser:
```
http://localhost/Pastimes_Website/
```

The admin panel is at:
```
http://localhost/Pastimes_Website/admin/
```

---

## Notes

> **Passwords** are currently hashed with `MD5`. For any production deployment, upgrade to `password_hash()` with `PASSWORD_BCRYPT`.

> **Cart state** is stored in PHP sessions. Unauthenticated checkout attempts redirect to the login/register flow and return the user to checkout after authentication.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Server-side | PHP (server-side rendering) |
| Database | MySQL + MySQLi |
| Frontend | HTML, CSS, Vanilla JavaScript |
| Auth & State | Session-based (PHP `$_SESSION`) |