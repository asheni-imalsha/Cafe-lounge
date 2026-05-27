-- Create application DB user for Cafe Lounge
-- Run this in MySQL (phpMyAdmin, XAMPP Shell, or mysql client)

CREATE USER IF NOT EXISTS 'cafe_user'@'localhost' IDENTIFIED BY 'app_pass';
CREATE USER IF NOT EXISTS 'cafe_user'@'127.0.0.1' IDENTIFIED BY 'app_pass';

GRANT ALL PRIVILEGES ON cafe_lounge.* TO 'cafe_user'@'localhost';
GRANT ALL PRIVILEGES ON cafe_lounge.* TO 'cafe_user'@'127.0.0.1';

FLUSH PRIVILEGES;
