CREATE DATABASE IF NOT EXISTS cafe_lounge;
USE cafe_lounge;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(150) DEFAULT NULL,
  email VARCHAR(200) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  space_name VARCHAR(255) NOT NULL,
  space_type VARCHAR(100) DEFAULT 'desk',
  booking_date DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cafe menu items
CREATE TABLE IF NOT EXISTS cafe_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart (stores user carts). session_id optional for guest support.
CREATE TABLE IF NOT EXISTS cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  session_id VARCHAR(128) DEFAULT NULL,
  item_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (item_id) REFERENCES cafe_items(id) ON DELETE CASCADE
);

-- Seed cafe items (at least 5)
INSERT IGNORE INTO cafe_items (id, name, price, image) VALUES
(1, 'Espresso', 2.50, NULL),
(2, 'Latte', 3.50, NULL),
(3, 'Cappuccino', 3.75, NULL),
(4, 'Flat White', 3.75, NULL),
(5, 'Croissant', 2.00, NULL),
(6, 'Blueberry Muffin', 2.25, NULL);
