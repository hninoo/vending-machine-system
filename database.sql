-- Create the database
CREATE DATABASE vending_machine;

-- Use the database
USE vending_machine;

-- Create the products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 3) NOT NULL,
    quantity_available INT NOT NULL,
    product_badge ENUM('none', 'new', 'sale') NOT NULL DEFAULT 'none',
    old_price DECIMAL(10, 3) NULL,
    CONSTRAINT products_price_positive CHECK (price > 0),
    CONSTRAINT products_quantity_available_non_negative CHECK (quantity_available >= 0),
    CONSTRAINT products_old_price_above_price CHECK (old_price IS NULL OR old_price > price)
);


-- Create the users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL
);

-- Insert default admin user
-- Username: admin
-- Password: admin123
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$ifVPXOry8YAmGRFIp4UA3OmyT32AICERWyecmYDDuceYb.wLSHBeu', 'admin');

-- Create the transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 3) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT transactions_quantity_positive CHECK (quantity > 0),
    CONSTRAINT transactions_total_price_positive CHECK (total_price > 0),
    INDEX idx_transactions_date (transaction_date),
    INDEX idx_transactions_product_date (product_id, transaction_date),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert some sample data into the products table
INSERT INTO products (name, price, quantity_available, product_badge, old_price) VALUES
('Coke', 3.99, 100, 'none', NULL),
('Pepsi', 6.885, 50, 'sale', 7.385),
('Water', 0.50, 200, 'new', NULL);
