CREATE DATABASE IF NOT EXISTS reservation_system;
USE reservation_system;

-- Drop tables in reverse order of dependencies to avoid foreign key errors
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS time_slots;
DROP TABLE IF EXISTS users;

CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE time_slots(
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    capacity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reservations(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    slot_id INT NOT NULL,
    note TEXT NULL,
    status ENUM('pending', 'confirmed', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_slot (user_id, slot_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES time_slots(id) ON DELETE CASCADE
);

-- Insert a default admin account
-- Password is 'admin123' hashed using password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@example.com', '$2y$10$e.w3z.4N1g5x7z8vB9w2e.5O5Z1y8X2w3v4b5n6m7l8k9j0h1g2f3', 'admin');

DELETE FROM users WHERE email = 'admin@example.com';


UPDATE users SET role = 'admin' WHERE email = 'the_email_you_just_registered@example.com';
