-- RELAPSE Rental System Database
CREATE DATABASE IF NOT EXISTS relapse_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE relapse_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    avatar VARCHAR(255) DEFAULT 'default.png',
    role ENUM('user','admin') DEFAULT 'user',
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    description TEXT,
    specs JSON,
    price_per_day DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 1,
    image VARCHAR(255),
    images JSON,
    condition_rating ENUM('Excellent','Good','Fair') DEFAULT 'Good',
    is_available TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rental_start DATE NOT NULL,
    rental_end DATE NOT NULL,
    total_days INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','active','completed','cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('unpaid','paid','refunded') DEFAULT 'unpaid',
    delivery_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('rental','payment','system','promo') DEFAULT 'system',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    reply TEXT,
    status ENUM('open','replied','closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rental_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (rental_id) REFERENCES rentals(id)
);

-- Admin user (password: admin123)
INSERT INTO users (full_name, email, password, role, is_verified) VALUES
('RELAPSE Admin', 'admin@relapse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

INSERT INTO categories (name, icon) VALUES
('Laptops','laptop'),('Gaming','gamepad-2'),('Tablets','tablet'),('Accessories','keyboard'),('Monitors','monitor');

INSERT INTO products (category_id, name, brand, model, description, specs, price_per_day, stock, image, condition_rating, is_featured) VALUES
(1,'ASUS ROG Zephyrus G14','ASUS','ROG G14','High-performance gaming laptop with AMD Ryzen 9','{"CPU":"AMD Ryzen 9 6900HX","RAM":"16GB DDR5","Storage":"1TB NVMe SSD","GPU":"RX 6700S 8GB","Display":"14-inch QHD 120Hz"}',750.00,3,'rog_g14.jpg','Excellent',1),
(1,'ASUS ROG Strix G15','ASUS','ROG G15','Powerhouse gaming laptop built for pros','{"CPU":"Intel Core i7-12700H","RAM":"16GB DDR5","Storage":"512GB SSD","GPU":"RTX 3070 Ti","Display":"15.6-inch FHD 144Hz"}',850.00,2,'rog_g15.jpg','Excellent',1),
(1,'MacBook Pro 14"','Apple','MacBook Pro M2','Apple Silicon pro laptop','{"CPU":"Apple M2 Pro","RAM":"16GB Unified","Storage":"512GB SSD","Display":"14.2-inch Liquid Retina XDR"}',950.00,2,'macbook.jpg','Excellent',1),
(1,'Lenovo ThinkPad X1','Lenovo','ThinkPad X1 Carbon','Premium business ultrabook','{"CPU":"Intel Core i7","RAM":"16GB","Storage":"512GB SSD","Display":"14-inch FHD IPS"}',600.00,4,'thinkpad.jpg','Good',0),
(2,'PlayStation 5','Sony','PS5','Next-gen gaming console','{"CPU":"AMD Zen 2 8-core","RAM":"16GB GDDR6","Storage":"825GB Custom SSD","Resolution":"Up to 4K/120fps"}',500.00,3,'ps5.jpg','Excellent',1),
(3,'iPad Pro 12.9"','Apple','iPad Pro M2','Pro-grade tablet','{"CPU":"Apple M2","RAM":"8GB","Storage":"256GB","Display":"12.9-inch Liquid Retina XDR"}',450.00,2,'ipad.jpg','Good',0);