-- =============================================
-- Campus Lost & Found Portal - Database Schema
-- MySQL 5.7+ / MariaDB 10.2+
-- =============================================

CREATE DATABASE IF NOT EXISTS lost_found_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE lost_found_db;

-- -------------------------------------------
-- USERS TABLE
-- -------------------------------------------
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    mobile VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    role ENUM('student','admin') NOT NULL DEFAULT 'student',
    points_balance INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','suspended','deleted') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_users_email (email)
) ENGINE=InnoDB;

-- -------------------------------------------
-- ITEMS TABLE
-- -------------------------------------------
CREATE TABLE items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    item_type ENUM('lost','found') NOT NULL,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    brand_model VARCHAR(100) DEFAULT NULL,
    color VARCHAR(50) DEFAULT NULL,
    serial_number VARCHAR(100) DEFAULT NULL,
    campus_zone VARCHAR(100) NOT NULL,
    specific_location VARCHAR(150) DEFAULT NULL,
    date_lost_found DATE NOT NULL,
    time_period VARCHAR(30) DEFAULT NULL,
    image_1 VARCHAR(255) DEFAULT NULL,
    image_2 VARCHAR(255) DEFAULT NULL,
    image_3 VARCHAR(255) DEFAULT NULL,
    custody_status VARCHAR(50) DEFAULT NULL,
    status ENUM('active','matched','returned','closed') NOT NULL DEFAULT 'active',
    views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_items_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_items_type ON items(item_type);
CREATE INDEX idx_items_category ON items(category);
CREATE INDEX idx_items_status ON items(status);
CREATE INDEX idx_items_zone ON items(campus_zone);
CREATE INDEX idx_items_created ON items(created_at DESC);

-- -------------------------------------------
-- CLAIMS TABLE
-- -------------------------------------------
CREATE TABLE claims (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    claimant_id INT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    evidence_details VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_claims_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    CONSTRAINT fk_claims_user FOREIGN KEY (claimant_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_claims_item ON claims(item_id);
CREATE INDEX idx_claims_claimant ON claims(claimant_id);
CREATE INDEX idx_claims_status ON claims(status);

-- -------------------------------------------
-- POINTS TRANSACTIONS TABLE
-- -------------------------------------------
CREATE TABLE points_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    points INT NOT NULL,
    transaction_type ENUM('earned','spent','bonus','penalty') NOT NULL,
    description VARCHAR(255) NOT NULL,
    reference_id INT UNSIGNED DEFAULT NULL,
    reference_type VARCHAR(30) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_points_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_points_user ON points_transactions(user_id);

-- -------------------------------------------
-- REWARDS TABLE (cafeteria/campus rewards)
-- -------------------------------------------
CREATE TABLE rewards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    points_cost INT UNSIGNED NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------
-- REDEMPTIONS TABLE
-- -------------------------------------------
CREATE TABLE redemptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    reward_id INT UNSIGNED NOT NULL,
    points_spent INT UNSIGNED NOT NULL,
    status ENUM('pending','fulfilled','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_redemptions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_redemptions_reward FOREIGN KEY (reward_id) REFERENCES rewards(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------
-- SEED DATA
-- -------------------------------------------

-- Default admin account (password: Admin@123)
INSERT INTO users (full_name, email, mobile, password, role, points_balance)
VALUES ('System Admin', 'admin@campus.edu', '03000000000',
        '$2y$10$1LVqfyAXLVxC0kCxpNfPeu2p2yJl/lUN.0dINW3m31FJI5vS3UTmu',
        'admin', 0);

-- Sample rewards
INSERT INTO rewards (name, description, points_cost, stock, status) VALUES
('Free Coffee', 'Redeem for a free coffee at the campus cafe', 50, 100, 'active'),
('Cafeteria Meal Voucher', 'Get a free meal at the campus cafeteria', 150, 50, 'active'),
('Print Credit $5', '$5 credit for campus printing services', 75, 200, 'active'),
('Bookstore Discount 10%', '10% off at the campus bookstore', 100, 100, 'active');
