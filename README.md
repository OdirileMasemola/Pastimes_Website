# Pastimes Website - Project Skeleton

## Project Structure

```
Pastimes_Website/
├── index.php                 # Home page
├── includes/
│   ├── DBConn.php           # Database connection
│   ├── createTable.php      # Create tblUser script
│   └── loadClothingStore.php # Load all database tables
├── pages/
│   ├── login.php            # User login page
│   ├── register.php         # User registration page
│   ├── logout.php           # User logout script
│   ├── shop.php             # Shop page - all products
│   ├── product-details.php  # Individual product details
│   ├── cart.php             # Shopping cart
│   ├── checkout.php         # Checkout page
│   ├── account.php          # User dashboard
│   ├── my-orders.php        # User orders history
│   ├── my-messages.php      # User messages
│   └── sell-item.php        # Sell item form
├── admin/
│   ├── admin-login.php      # Admin login
│   ├── dashboard.php        # Admin dashboard
│   ├── admin-logout.php     # Admin logout
│   ├── manage-users.php     # View all users
│   ├── add-user.php         # Add new user
│   ├── edit-user.php        # Edit user info
│   ├── delete-user.php      # Delete user
│   ├── manage-clothes.php   # View all clothes
│   ├── add-clothing.php     # Add new clothing
│   ├── edit-clothing.php    # Edit clothing
│   ├── delete-clothing.php  # Delete clothing
│   └── manage-orders.php    # View all orders
├── data/
│   ├── userData.txt         # User records
│   ├── adminData.txt        # Admin records
│   └── clothesData.txt      # Clothing records
├── assets/
│   └── style.css            # Stylesheet
└── README.md                # This file
```

## Database Tables

### tblUser
- userID (Primary Key)
- fullName
- email (Unique)
- passwordHash
- address
- city
- zipCode
- phone
- isVerified
- createdDate
- updatedDate

### tblAdmin
- adminID (Primary Key)
- adminName
- adminEmail (Unique)
- passwordHash
- createdDate

### tblClothes
- clothingID (Primary Key)
- clothingName
- category
- description
- price
- quantity
- imageURL
- createdDate

### tblOrder
- orderID (Primary Key)
- userID (Foreign Key → tblUser.userID)
- orderDate
- totalAmount
- status
- FOREIGN KEY (userID) REFERENCES tblUser(userID)

## User Roles

### Regular User
- Register an account
- Login (after admin verification)
- Browse products
- Add products to cart
- Checkout
- View order history
- View profile information

### Admin
- Verify new user registrations
- Manage users (Add, Edit, Delete)
- Manage clothing inventory (Add, Edit, Delete)
- View all orders
- Manage orders status

## Features Implemented

✅ Database connection (DBConn.php)
✅ Create/Load database tables
✅ User registration with pending verification
✅ User login with password hashing
✅ Admin login
✅ User authentication & sessions
✅ Shopping cart functionality
✅ Product catalog
✅ Admin management panels
✅ Basic responsive CSS styling

## Features To Complete

- [ ] Complete checkout process (calculate totals, process payments)
- [ ] Order management (update order status)
- [ ] User messaging system
- [ ] Product image uploads
- [ ] Email notifications
- [ ] Advanced search/filtering
- [ ] User reviews and ratings
- [ ] Sell items functionality
- [ ] More comprehensive error handling
- [ ] Input validation enhancements

## Setup Instructions

1. Create database "ClothingStore" in MySQL
2. Run `/includes/loadClothingStore.php` to create tables
3. Optionally run `/includes/createTable.php` to load sample user data
4. Access application via web server (http://localhost/Pastimes_Website/)

## Default Test Credentials

**Admin:**
- Email: admin@abc.co.za
- Password: admin

**Sample Users (from userData.txt):**
- Email: john.doe@abc.co.za
- Email: jane.smith@abc.co.za
- (All with their respective hashed passwords)

## Notes

- All passwords are stored as MD5 hashes
- User accounts must be verified by admin before login
- Shopping cart uses PHP sessions
- Database uses MySQLi for improved security
- Default CSS is basic - can be enhanced for better UX
