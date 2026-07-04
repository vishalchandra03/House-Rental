-- Database structure for House Rental Project
CREATE DATABASE IF NOT EXISTS house_rental;
USE house_rental;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin','tenant') DEFAULT 'tenant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS houses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT DEFAULT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    city VARCHAR(100),
    bhk INT,
    rent DECIMAL(10,2),
    deposit DECIMAL(10,2),
    furnishing ENUM('Unfurnished','Semi-Furnished','Fully-Furnished') DEFAULT 'Unfurnished',
    area_sqft INT,
    available_from DATE,
    status ENUM('available','booked','inactive') DEFAULT 'available',
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    house_id INT NOT NULL,
    user_id INT NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (house_id) REFERENCES houses(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@admin.com', MD5('admin123'), 'admin')
ON DUPLICATE KEY UPDATE email = email;
