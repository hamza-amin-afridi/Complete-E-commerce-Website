-- eCommerce Database Schema
-- Database: ecommerce_db

-- Create database
CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500),
    category_id INT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    additional_images TEXT,
    status TINYINT DEFAULT 1,
    featured TINYINT DEFAULT 0,
    popularity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_name VARCHAR(100),
    shipping_email VARCHAR(100),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    shipping_city VARCHAR(50),
    shipping_zip VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200),
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cart table (for persistent cart)
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(100),
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Newsletter subscriptions
CREATE TABLE IF NOT EXISTS newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    status TINYINT DEFAULT 1,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact form submissions
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Promo codes / Coupons
CREATE TABLE IF NOT EXISTS promo_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10, 2) NOT NULL,
    min_order_amount DECIMAL(10, 2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    uses_count INT DEFAULT 0,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    applies_to ENUM('all', 'category', 'product') DEFAULT 'all',
    category_id INT DEFAULT NULL,
    product_id INT DEFAULT NULL,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- User profile pictures
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL;

-- Insert sample admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@eshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample categories
INSERT INTO categories (name, slug, description) VALUES
('Electronics', 'electronics', 'Latest electronic gadgets and devices'),
('Fashion', 'fashion', 'Trendy clothing and accessories'),
('Home & Living', 'home-living', 'Home decor and living essentials'),
('Sports', 'sports', 'Sports equipment and accessories'),
('Books', 'books', 'Books and educational materials');

-- Insert sample products
INSERT INTO products (name, slug, description, short_description, category_id, price, stock, image, featured, popularity) VALUES
('Wireless Bluetooth Headphones', 'wireless-bluetooth-headphones', 'Premium wireless headphones with active noise cancellation and 30-hour battery life.', 'Premium wireless headphones with ANC', 1, 89.99, 50, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400', 1, 120),
('Smart Watch Pro', 'smart-watch-pro', 'Advanced fitness tracking smartwatch with heart rate monitor and GPS.', 'Advanced fitness tracking smartwatch', 1, 199.99, 30, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400', 1, 95),
('Laptop Stand Aluminum', 'laptop-stand-aluminum', 'Ergonomic aluminum laptop stand for better posture and cooling.', 'Ergonomic aluminum laptop stand', 1, 49.99, 100, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400', 0, 45),
('Men\'s Casual Shirt', 'mens-casual-shirt', 'Comfortable cotton casual shirt perfect for everyday wear.', 'Comfortable cotton casual shirt', 2, 34.99, 80, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=400', 1, 67),
('Women\'s Summer Dress', 'womens-summer-dress', 'Beautiful floral summer dress with breathable fabric.', 'Beautiful floral summer dress', 2, 59.99, 45, 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=400', 1, 89),
('Running Shoes', 'running-shoes', 'Lightweight running shoes with cushioned sole for maximum comfort.', 'Lightweight running shoes', 4, 79.99, 60, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400', 1, 110),
('Yoga Mat Premium', 'yoga-mat-premium', 'Non-slip premium yoga mat with carrying strap.', 'Non-slip premium yoga mat', 4, 29.99, 120, 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=400', 0, 55),
('Table Lamp Modern', 'table-lamp-modern', 'Modern minimalist table lamp with adjustable brightness.', 'Modern minimalist table lamp', 3, 45.99, 40, 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=400', 0, 38),
('Coffee Maker', 'coffee-maker', 'Automatic drip coffee maker with programmable timer.', 'Automatic drip coffee maker', 3, 89.99, 25, 'https://images.unsplash.com/photo-1517080319786-242979c8e63b?w=400', 1, 72),
('Programming Book Bundle', 'programming-book-bundle', 'Complete programming book bundle covering web development.', 'Complete programming book bundle', 5, 129.99, 20, 'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=400', 0, 28);
