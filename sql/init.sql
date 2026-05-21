-- Donut Shop Management System - Database Initialization
-- Creates database, tables, and sample data

CREATE DATABASE IF NOT EXISTS donutdb;
USE donutdb;

-- Products (donut menu) table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    emoji VARCHAR(10) DEFAULT '🍩',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample donut products
INSERT INTO products (name, description, price, emoji) VALUES
('Glazed Classic', 'Our signature sweet glazed donut', 2.50, '🍩'),
('Chocolate Frosted', 'Rich chocolate frosting on a soft ring', 3.00, '🍫'),
('Strawberry Sprinkle', 'Pink frosting with colorful sprinkles', 3.25, '🍓'),
('Boston Cream', 'Filled with vanilla cream and chocolate top', 3.75, '🥧'),
('Maple Bacon', 'Savory-sweet maple glaze with crispy bacon', 4.50, '🥓'),
('Blueberry Cake', 'Moist cake donut with blueberry glaze', 3.50, '🫐');

-- Sample orders
INSERT INTO orders (product_id, product_name, customer_name, quantity, total_price, status) VALUES
(1, 'Glazed Classic', 'Alice Johnson', 2, 5.00, 'completed'),
(3, 'Strawberry Sprinkle', 'Bob Smith', 1, 3.25, 'pending'),
(5, 'Maple Bacon', 'Carol Davis', 3, 13.50, 'pending');

-- Application user for Docker/Kubernetes (optional - created by env in compose/k8s)
-- GRANT statements may run when using custom init; root handles local dev
