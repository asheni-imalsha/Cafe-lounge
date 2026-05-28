DROP DATABASE IF EXISTS cafe_lounge;
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
  space_type ENUM('desk', 'Meeting Room', 'Study Desks', 'Rooftop Lounge', 'Outdoor Space', 'Group Space', 'Outdoor Swing') DEFAULT 'desk',
  booking_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cafe menu items
CREATE TABLE IF NOT EXISTS cafe_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description TEXT DEFAULT NULL,
  image VARCHAR(500) DEFAULT NULL,
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

-- Add cafe items
INSERT INTO cafe_items (id, name, price, description, image) VALUES
(1, 'Espresso', 250.00, 'Short, strong shot of espresso — bold, rich and perfect as a quick pick-me-up.', 'https://images.pexels.com/photos/37548741/pexels-photo-37548741.jpeg'),
(2, 'Latte', 450.00, 'Creamy steamed milk poured over a shot of espresso — smooth, milky and comforting.', 'https://images.pexels.com/photos/31139336/pexels-photo-31139336.jpeg'),
(3, 'Cappuccino', 400.00, 'Espresso topped with velvety steamed milk foam — a classic with a delicate texture.', 'https://images.pexels.com/photos/27528586/pexels-photo-27528586.jpeg'),
(4, 'Flat White', 420.00, 'Smooth espresso with a silky layer of steamed milk — balanced and slightly sweeter.', 'https://images.pexels.com/photos/4869282/pexels-photo-4869282.jpeg'),
(5, 'Croissant', 220.00, 'Buttery, flaky pastry — crisp outside, soft inside; perfect with coffee.', 'https://images.pexels.com/photos/19498993/pexels-photo-19498993.jpeg'),
(6, 'Blueberry Muffin', 260.00, 'Moist blueberry muffin with tender crumb and a hint of sweetness.', 'https://images.pexels.com/photos/17738649/pexels-photo-17738649.jpeg'),
(7, 'Coffee Doughnut', 200.00, 'Glazed coffee doughnut — light, sweet and excellent alongside a hot drink.', 'https://images.pexels.com/photos/30822875/pexels-photo-30822875.jpeg'),
(8, 'Macarons', 600.00, 'Delicate French macarons — crisp shell with a soft, flavorful center.', 'https://images.pexels.com/photos/8714791/pexels-photo-8714791.jpeg');