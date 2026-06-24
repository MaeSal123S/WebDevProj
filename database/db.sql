-- ============================================================
--  AutoRepair Management System — Full Database Schema
--  Last updated: 2026-06-24
--  Run this file on a fresh MySQL instance to recreate the DB
-- ============================================================

CREATE DATABASE IF NOT EXISTS autorepairwd_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE autorepairwd_db;

-- ============================================================
--  CUSTOMER
-- ============================================================
CREATE TABLE `customer` (
    `customer_id` INT NOT NULL AUTO_INCREMENT,
    `last_name`   VARCHAR(50) NOT NULL,
    `first_name`  VARCHAR(50) NOT NULL,
    `deleted_at`  TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  VEHICLE
-- ============================================================
CREATE TABLE `vehicle` (
    `vehicle_id`   INT NOT NULL AUTO_INCREMENT,
    `plate_number` VARCHAR(20) NOT NULL,
    `model`        VARCHAR(50) NOT NULL,
    `customer_id`  INT DEFAULT NULL,
    `deleted_at`   TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`vehicle_id`),
    KEY `vehicle_customer_fk` (`customer_id`),
    CONSTRAINT `vehicle_customer_fk`
        FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  SERVICE TYPE
-- ============================================================
CREATE TABLE `service_type` (
    `service_type_id`    INT NOT NULL AUTO_INCREMENT,
    `service_type_name`  VARCHAR(50) NOT NULL,
    `predetermined_hours` DECIMAL(5,2) NOT NULL,
    `book_rate`          DECIMAL(6,2) NOT NULL,
    `deleted_at`         TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`service_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  SERVICE ADVISOR
-- ============================================================
CREATE TABLE `service_advisor` (
    `advisor_id` INT NOT NULL AUTO_INCREMENT,
    `last_name`  VARCHAR(50) NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`advisor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  USERS
--  role: admin | service_advisor | customer
--  advisor_id  → linked service_advisor (for service_advisor role)
--  customer_id → linked customer record (for customer role)
-- ============================================================
CREATE TABLE `users` (
    `user_id`    INT NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(50) NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('admin','service_advisor','customer') NOT NULL,
    `advisor_id` INT DEFAULT NULL,
    `customer_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `username` (`username`),
    KEY `advisor_id` (`advisor_id`),
    CONSTRAINT `users_ibfk_1`
        FOREIGN KEY (`advisor_id`) REFERENCES `service_advisor` (`advisor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  REPAIR ORDER
-- ============================================================
CREATE TABLE `repair_order` (
    `order_no`        INT NOT NULL AUTO_INCREMENT,
    `date_of_service` DATE NOT NULL,
    `customer_id`     INT NOT NULL,
    `vehicle_id`      INT NOT NULL,
    `advisor_id`      INT NOT NULL,
    PRIMARY KEY (`order_no`),
    KEY `repair_order_customer_fk` (`customer_id`),
    KEY `repair_order_vehicle_fk`  (`vehicle_id`),
    KEY `repair_order_advisor_fk`  (`advisor_id`),
    CONSTRAINT `repair_order_customer_fk`
        FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `repair_order_vehicle_fk`
        FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`vehicle_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `repair_order_advisor_fk`
        FOREIGN KEY (`advisor_id`) REFERENCES `service_advisor` (`advisor_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  REPAIR ITEM  (order ↔ service_type many-to-many)
-- ============================================================
CREATE TABLE `repair_item` (
    `order_no`        INT NOT NULL,
    `service_type_id` INT NOT NULL,
    PRIMARY KEY (`order_no`, `service_type_id`),
    KEY `repair_item_service_type_fk` (`service_type_id`),
    CONSTRAINT `repair_item_order_fk`
        FOREIGN KEY (`order_no`) REFERENCES `repair_order` (`order_no`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `repair_item_service_type_fk`
        FOREIGN KEY (`service_type_id`) REFERENCES `service_type` (`service_type_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  APPOINTMENTS
--  advisor_id NULL = unassigned (pending customer booking)
--  booked_by  = user_id who created the record
-- ============================================================
CREATE TABLE `appointments` (
    `appointment_id`   INT NOT NULL AUTO_INCREMENT,
    `customer_id`      INT NOT NULL,
    `vehicle_id`       INT NOT NULL,
    `service_type_id`  INT NOT NULL,       -- legacy single value (first selected)
    `advisor_id`       INT DEFAULT NULL,   -- NULL until an advisor accepts
    `appointment_date` DATE NOT NULL,
    `appointment_time` TIME NOT NULL,
    `status`           ENUM('pending','confirmed','cancelled','completed')
                           DEFAULT 'pending',
    `notes`            TEXT DEFAULT NULL,
    `booked_by`        INT NOT NULL,
    `created_at`       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`appointment_id`),
    KEY `customer_id`     (`customer_id`),
    KEY `vehicle_id`      (`vehicle_id`),
    KEY `service_type_id` (`service_type_id`),
    KEY `advisor_id`      (`advisor_id`),
    KEY `booked_by`       (`booked_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  APPOINTMENT SERVICE TYPES
--  appointment ↔ service_type many-to-many
--  (supports multiple services per appointment)
-- ============================================================
CREATE TABLE `appointment_service_types` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `appointment_id`   INT UNSIGNED NOT NULL,
    `service_type_id`  INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `appt_st_unique` (`appointment_id`, `service_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  SUPPLIES (inventory)
-- ============================================================
CREATE TABLE `supplies` (
    `supply_id`     INT NOT NULL AUTO_INCREMENT,
    `supply_name`   VARCHAR(100) NOT NULL,
    `unit`          VARCHAR(20) NOT NULL,
    `current_stock` DECIMAL(10,2) DEFAULT 0,
    `minimum_stock` DECIMAL(10,2) DEFAULT 0,
    `price_per_unit` DECIMAL(10,2) DEFAULT 0,
    `created_at`    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at`    TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`supply_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  SUPPLY USAGE
-- ============================================================
CREATE TABLE `supply_usage` (
    `usage_id`      INT NOT NULL AUTO_INCREMENT,
    `supply_id`     INT NOT NULL,
    `order_no`      INT DEFAULT NULL,
    `quantity_used` DECIMAL(10,2) NOT NULL,
    `used_at`       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `notes`         TEXT DEFAULT NULL,
    PRIMARY KEY (`usage_id`),
    KEY `supply_id` (`supply_id`),
    KEY `order_no`  (`order_no`),
    CONSTRAINT `supply_usage_supply_fk`
        FOREIGN KEY (`supply_id`) REFERENCES `supplies` (`supply_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `supply_usage_order_fk`
        FOREIGN KEY (`order_no`) REFERENCES `repair_order` (`order_no`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  AUDIT LOG
-- ============================================================
CREATE TABLE `audit_log` (
    `log_id`     INT NOT NULL AUTO_INCREMENT,
    `user_id`    INT NOT NULL,
    `action`     VARCHAR(20) NOT NULL,
    `table_name` VARCHAR(50) NOT NULL,
    `record_id`  INT NOT NULL,
    `changes`    TEXT DEFAULT NULL,
    `timestamp`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `audit_log_user_fk`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  LOGIN ATTEMPTS
-- ============================================================
CREATE TABLE `login_attempts` (
    `id`           INT NOT NULL AUTO_INCREMENT,
    `username`     VARCHAR(50) NOT NULL,
    `attempts`     INT DEFAULT 0,
    `last_attempt` DATETIME DEFAULT NULL,
    `locked_until` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  PERMISSIONS
-- ============================================================
CREATE TABLE `permissions` (
    `permission_id` INT NOT NULL AUTO_INCREMENT,
    `module`        VARCHAR(50) NOT NULL,
    `action`        VARCHAR(20) NOT NULL,
    `display_name`  VARCHAR(100) NOT NULL,
    PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  USER PERMISSIONS
-- ============================================================
CREATE TABLE `user_permissions` (
    `id`            INT NOT NULL AUTO_INCREMENT,
    `user_id`       INT NOT NULL,
    `permission_id` INT NOT NULL,
    `is_granted`    TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `user_id`       (`user_id`),
    KEY `permission_id` (`permission_id`),
    CONSTRAINT `up_user_fk`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `up_permission_fk`
        FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  LARAVEL MIGRATIONS TABLE
-- ============================================================
CREATE TABLE `migrations` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch`     INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  SEED DATA — DEFAULT ADMIN USER
--  Password: password  (bcrypt hash)
-- ============================================================
INSERT INTO `users` (`username`, `password`, `role`, `created_at`)
VALUES (
    'admin',
    '$2y$10$pcwqbeRUn.79QFrAoomUKuU9V7Sy5hzSpMQAWBqmv0IitFA7OT5yO',
    'admin',
    NOW()
);

-- ============================================================
--  SEED DATA — PERMISSIONS
-- ============================================================
INSERT INTO `permissions` (`module`, `action`, `display_name`) VALUES
-- Customer
('customer',        'view',   'View Customer'),
('customer',        'add',    'Add Customer'),
('customer',        'edit',   'Edit Customer'),
('customer',        'delete', 'Delete Customer'),
-- Vehicle
('vehicle',         'view',   'View Vehicle'),
('vehicle',         'add',    'Add Vehicle'),
('vehicle',         'edit',   'Edit Vehicle'),
('vehicle',         'delete', 'Delete Vehicle'),
-- Service Type
('service_type',    'view',   'View Service Type'),
('service_type',    'add',    'Add Service Type'),
('service_type',    'edit',   'Edit Service Type'),
('service_type',    'delete', 'Delete Service Type'),
-- Service Advisor
('service_advisor', 'view',   'View Service Advisor'),
('service_advisor', 'add',    'Add Service Advisor'),
('service_advisor', 'edit',   'Edit Service Advisor'),
('service_advisor', 'delete', 'Delete Service Advisor'),
-- Repair Order
('repair_order',    'view',   'View Repair Order'),
('repair_order',    'add',    'Add Repair Order'),
('repair_order',    'edit',   'Edit Repair Order'),
('repair_order',    'delete', 'Delete Repair Order'),
-- Appointment
('appointment',     'view',   'View Appointment'),
('appointment',     'add',    'Add Appointment'),
('appointment',     'edit',   'Edit Appointment'),
('appointment',     'delete', 'Delete Appointment'),
('appointment',     'status', 'Change Appointment Status'),
-- Inventory
('inventory',       'view',   'View Inventory'),
('inventory',       'add',    'Add Inventory'),
('inventory',       'edit',   'Edit Inventory'),
('inventory',       'delete', 'Delete Inventory'),
-- Users
('users',           'view',   'View Users'),
('users',           'add',    'Add Users'),
('users',           'edit',   'Edit Users'),
('users',           'delete', 'Delete Users'),
-- System Logs
('audit_log',       'view',   'View Audit Logs'),
('login_log',       'view',   'View Login Logs'),
('database',        'view',   'View Database');

-- ============================================================
--  SEED DATA — GRANT ALL PERMISSIONS TO ADMIN
-- ============================================================
INSERT INTO `user_permissions` (`user_id`, `permission_id`, `is_granted`)
SELECT
    (SELECT `user_id` FROM `users` WHERE `role` = 'admin' LIMIT 1),
    `permission_id`,
    1
FROM `permissions`;

-- ============================================================
--  SEED DATA — LARAVEL MIGRATIONS RECORD
--  (marks our custom migrations as already run)
-- ============================================================
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_06_24_000002_create_appointment_service_types_table', 1),
('2026_06_24_000003_add_appointment_status_permission', 2);
