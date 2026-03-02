# E-Shop - Modern eCommerce Website

A complete, modern eCommerce website built with PHP, MySQL, Bootstrap 5, and MDBootstrap (Material Design for Bootstrap).

## Features

### Frontend (User Website)
- **Home Page**: Hero section with banner, featured categories, trending products grid
- **Shop Page**: Product listing with category filter, search bar, and sorting options
- **Product Detail Page**: Product images, reviews section, "Add to Cart" button, related products
- **Cart Page**: View cart items, update quantity, remove items, display subtotal and total
- **Checkout Page**: Billing/shipping form with payment confirmation
- **User Authentication**: Login, Signup, and Forgot Password pages
- **User Dashboard**: Profile management and order history
- **Responsive Design**: Fully responsive layout using Bootstrap 5 and MDBootstrap

### Admin Panel
- **Dashboard**: Sales charts, statistics, recent orders, low stock alerts
- **Product Management**: Add, edit, delete products with image URLs
- **Category Management**: Add, edit, delete categories
- **Order Management**: View orders, update order status
- **User Management**: View users, toggle admin role, delete users
- **Analytics**: Revenue tracking with Chart.js visualization

### Backend
- **Database**: MySQL with optimized schema
- **Security**: Password hashing, input sanitization, session management
- **Cart System**: Both guest (session-based) and user (database-based) carts
- **Order System**: Complete order workflow with status tracking

## Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript, jQuery, Bootstrap 5, MDBootstrap
- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Charts**: Chart.js
- **Icons**: Font Awesome 6
- **Fonts**: Poppins, Montserrat

## Installation

### Prerequisites
- XAMPP/WAMP/MAMP or any PHP server with MySQL
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Clone or Download the Project**
   ```
   Place the "E-Commerce Website" folder in your web server directory:
   - For XAMPP: c:\xampp\htdocs\
   ```

2. **Create the Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database.sql` file located in the project root
   - This will create the `ecommerce_db` database with all tables and sample data

3. **Database Configuration** (if needed)
   - Edit `includes/db_connect.php` if your database credentials differ:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USERNAME', 'root');     // Change if different
   define('DB_PASSWORD', '');         // Change if different
   define('DB_NAME', 'ecommerce_db');
   ```

4. **Access the Website**
   - Frontend: http://localhost/E-Commerce%20Website/
   - Admin Panel: http://localhost/E-Commerce%20Website/admin/admin_login.php

## Default Credentials

### Admin Account
- **Email**: admin@eshop.com
- **Password**: admin123

### Sample User Accounts
You can create new user accounts through the signup page, or use the sample data inserted by the database script.

## Folder Structure

```
E-Commerce Website/
├── admin/                    # Admin panel files
│   ├── admin_login.php      # Admin login page
│   ├── index.php            # Admin dashboard
│   ├── products.php         # Product management
│   ├── add_product.php      # Add new product
│   ├── edit_product.php     # Edit product
│   ├── categories.php       # Category management
│   ├── orders.php           # Order management
│   └── users.php            # User management
│
├── assets/                   # Static assets
│   ├── css/
│   │   └── style.css        # Custom styles
│   └── js/
│       └── main.js          # JavaScript functions
│
├── includes/                 # Shared PHP components
│   ├── header.php           # Site header with navigation
│   ├── footer.php           # Site footer
│   └── db_connect.php       # Database connection
│
├── database.sql             # Database schema and sample data
├── index.php               # Homepage
├── shop.php                # Product listing
├── product.php             # Product detail page
├── cart.php                # Shopping cart
├── cart_actions.php        # AJAX cart handlers
├── checkout.php            # Checkout process
├── order_confirmation.php  # Order success page
├── login.php               # User login
├── signup.php              # User registration
├── forgot_password.php     # Password reset
├── logout.php              # Logout handler
├── profile.php             # User profile
├── orders.php              # User order history
├── quick_view.php          # Quick view modal content
└── submit_review.php       # Review submission
```

## Database Schema

### Tables
- **users**: User accounts (id, name, email, password, role, created_at)
- **categories**: Product categories (id, name, slug, description)
- **products**: Product catalog (id, name, slug, description, price, stock, image, etc.)
- **orders**: Customer orders (id, order_number, user_id, total_amount, status, etc.)
- **order_items**: Individual items in orders (id, order_id, product_id, quantity, price)
- **reviews**: Product reviews (id, product_id, user_id, rating, comment)
- **cart**: Shopping cart for logged-in users (id, user_id, product_id, quantity)

## Key Features Explained

### Shopping Cart
- **Guest Users**: Cart stored in PHP session
- **Logged-in Users**: Cart stored in database, persists across devices
- Cart seamlessly transfers from session to database upon login

### Order Workflow
1. **Pending**: Order placed, awaiting processing
2. **Processing**: Order being prepared
3. **Shipped**: Order dispatched
4. **Delivered**: Order completed
5. **Cancelled**: Order cancelled

### Product Management
- Products have categories, images (URLs), stock levels
- Featured products highlighted on homepage
- Low stock alerts in admin dashboard

### Security Features
- Password hashing with bcrypt
- Input sanitization to prevent SQL injection
- XSS protection through htmlspecialchars
- Session-based authentication
- Role-based access control (admin vs user)

## Customization

### Changing Colors/Theme
Edit `assets/css/style.css` to modify:
- Primary color: `--primary-color`
- Fonts: Already using Poppins and Montserrat
- Card styles, shadows, and animations

### Adding Payment Methods
Edit `checkout.php` to add real payment gateway integration. Currently includes:
- Cash on Delivery (default)
- Credit/Debit Card (demo)

### Email Notifications
To add email functionality:
1. Use PHPMailer or similar library
2. Configure SMTP settings
3. Add email triggers in:
   - `checkout.php` - Order confirmation
   - `signup.php` - Welcome email
   - `forgot_password.php` - Password reset

## Troubleshooting

### Common Issues

1. **Database connection error**
   - Check credentials in `includes/db_connect.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **Images not loading**
   - Product images use external URLs by default
   - Replace with local paths or your own image URLs

3. **Session issues**
   - Ensure `session_start()` is not blocked
   - Check PHP session save path is writable

4. **XAMPP path issues**
   - Project is designed for `c:\xampp\htdocs\E-Commerce Website\`
   - Adjust paths if using different setup

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Tips

1. Enable PHP OPcache for better performance
2. Use CDN for Bootstrap/MDBootstrap files
3. Implement caching for database queries
4. Optimize images before uploading

## License

This project is open source and available for personal and commercial use.

## Support

For issues or questions:
1. Check the troubleshooting section
2. Verify your server meets requirements
3. Review error logs in `c:\xampp\php\logs\`

---

**Created with PHP, MySQL, Bootstrap 5, and MDBootstrap**
