-- Car Rental System Database Schema
-- Run this SQL script to create the database and tables

CREATE DATABASE IF NOT EXISTS car_rental_db;
USE car_rental_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cars table
CREATE TABLE cars (
    id INT PRIMARY KEY AUTO_INCREMENT,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    color VARCHAR(30),
    license_plate VARCHAR(20) UNIQUE NOT NULL,
    vin VARCHAR(17) UNIQUE,
    mileage INT DEFAULT 0,
    fuel_type ENUM('petrol', 'diesel', 'hybrid', 'electric') NOT NULL,
    transmission ENUM('manual', 'automatic') NOT NULL,
    seats INT NOT NULL,
    doors INT NOT NULL,
    category ENUM('economy', 'compact', 'mid-size', 'full-size', 'luxury', 'suv', 'sports') NOT NULL,
    daily_rate DECIMAL(10,2) NOT NULL,
    description TEXT,
    features JSON,
    images JSON,
    status ENUM('available', 'rented', 'maintenance', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    pickup_location VARCHAR(100) NOT NULL,
    return_location VARCHAR(100) NOT NULL,
    total_days INT NOT NULL,
    daily_rate DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    admin_notes TEXT,
    user_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('credit_card', 'debit_card', 'cash', 'bank_transfer') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    booking_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_review (booking_id)
);

-- Admin logs table
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, email, password_hash, first_name, last_name, role) 
VALUES ('admin', 'admin@carrental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin');

-- Insert test user
INSERT INTO users (username, email, password_hash, first_name, last_name, role) 
VALUES ('testuser', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'User', 'user');

-- Insert sample cars
INSERT INTO cars (make, model, year, color, license_plate, vin, fuel_type, transmission, seats, doors, category, daily_rate, description, features, images) VALUES
('BMW', '3 Series', 2023, 'White', 'BMW-001', 'WBA3A5C59EF123456', 'petrol', 'automatic', 5, 4, 'luxury', 89.00, 'Luxury sedan with premium features', '["GPS", "Bluetooth", "Leather Seats", "Sunroof"]', '["https://images.unsplash.com/photo-1555215695-3004980ad54e?w=500"]'),
('Ferrari', '488', 2023, 'Red', 'FER-001', 'ZFF68AHA0F0123456', 'petrol', 'automatic', 2, 2, 'sports', 299.00, 'High-performance sports car', '["GPS", "Sport Mode", "Premium Sound", "Carbon Fiber"]', '["https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=500"]'),
('Range Rover', 'Evoque', 2023, 'Black', 'RR-001', 'SALVA2BG8EH123456', 'diesel', 'automatic', 5, 5, 'suv', 149.00, 'Luxury SUV with off-road capabilities', '["4WD", "GPS", "Premium Interior", "Panoramic Roof"]', '["https://images.unsplash.com/photo-1549317336-206569e8475c?w=500"]'),
('Mercedes', 'C-Class', 2023, 'Silver', 'MB-001', 'WDD2050461A123456', 'hybrid', 'automatic', 5, 4, 'luxury', 95.00, 'Elegant luxury sedan', '["GPS", "Adaptive Cruise", "Premium Audio", "LED Lights"]', '["https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=500"]'),
('Toyota', 'Corolla', 2023, 'Red', 'TOY-001', '1HGBH41JXMN123456', 'petrol', 'manual', 5, 4, 'economy', 45.00, 'Reliable economy car', '["GPS", "Bluetooth", "Air Conditioning", "USB Ports"]', '["https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=500"]'),
('Audi', 'Q7', 2023, 'White', 'AUD-001', 'WAUZZZ4G5FN123456', 'diesel', 'automatic', 7, 5, 'suv', 159.00, 'Premium 7-seater SUV', '["Quattro AWD", "Virtual Cockpit", "Premium Sound", "Third Row Seats"]', '["https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=500"]');

-- Create indexes for better performance
CREATE INDEX idx_cars_status ON cars(status);
CREATE INDEX idx_cars_category ON cars(category);
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_car_id ON bookings(car_id);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_dates ON bookings(pickup_date, return_date);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
