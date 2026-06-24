
CREATE DATABASE autoRepairwd_db;

USE autoRepairwd_db;


CREATE TABLE customer (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    last_name varchar(50) NOT NULL,
    first_name varchar(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE vehicle (
    vehicle_id int NOT NULL AUTO_INCREMENT PRIMARY KEY 
) ENGINE=InnoDB;

ALTER TABLE vehicle 
ADD COLUMN plate_number VARCHAR(20) NOT NULL,
ADD COLUMN model VARCHAR(50) NOT NULL;

CREATE TABLE service_type (
    service_type_id INT AUTO_INCREMENT PRIMARY KEY,
    service_type_name VARCHAR(50) NOT NULL,
    predetermined_hours decimal(5,2) NOT NULL,
    book_rate decimal(6,2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE service_advisor (
    advisor_id INT AUTO_INCREMENT PRIMARY KEY,
    last_name varchar(50) NOT NULL,
    first_name varchar(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE repair_order (
    order_no INT AUTO_INCREMENT PRIMARY KEY,
    date_of_service DATE NOT NULL,
    customer_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    advisor_id INT NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicle(vehicle_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (advisor_id) REFERENCES service_advisor(advisor_id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE repair_item (
    repair_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_no int NOT NULL,
    service_type_id INT NOT NULL,
    FOREIGN KEY (service_type_id) REFERENCES service_type(service_type_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (order_no) REFERENCES repair_order(order_no)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'service_advisor') NOT NULL,
    advisor_id INT DEFAULT NULL,
    FOREIGN KEY (advisor_id) REFERENCES service_advisor(advisor_id)
) ENGINE=InnoDB;

CREATE TABLE audit_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(10) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    changes TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'admin';

UPDATE users SET password = '$2y$10$pcwqbeRUn.79QFrAoomUKuU9V7Sy5hzSpMQAWBqmv0IitFA7OT5yO' WHERE username = 'admin';

USE autorepairwd_db;

ALTER TABLE users 
ADD COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL;

USE autorepairwd_db;

CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    attempts INT DEFAULT 0,
    last_attempt DATETIME DEFAULT NULL,
    locked_until DATETIME DEFAULT NULL
) ENGINE=InnoDB;

ALTER TABLE audit_log 
MODIFY COLUMN action VARCHAR(20) NOT NULL;

ALTER TABLE vehicle 
ADD COLUMN customer_id INT DEFAULT NULL,
ADD FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE vehicle SET customer_id = 1 WHERE vehicle_id = 1;
UPDATE vehicle SET customer_id = 3 WHERE vehicle_id = 2;
UPDATE vehicle SET customer_id = 4 WHERE vehicle_id = 3;
UPDATE vehicle SET customer_id = 5 WHERE vehicle_id = 4;

USE autorepairwd_db;

ALTER TABLE customer ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE vehicle ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE service_advisor ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE service_type ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;


-- Revert repair_order customer FK
ALTER TABLE repair_order DROP FOREIGN KEY repair_order_customer_fk;
ALTER TABLE repair_order MODIFY COLUMN customer_id INT NOT NULL;
ALTER TABLE repair_order ADD CONSTRAINT repair_order_customer_fk
FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- Revert repair_order vehicle FK
ALTER TABLE repair_order DROP FOREIGN KEY repair_order_vehicle_fk;
ALTER TABLE repair_order MODIFY COLUMN vehicle_id INT NOT NULL;
ALTER TABLE repair_order ADD CONSTRAINT repair_order_vehicle_fk
FOREIGN KEY (vehicle_id) REFERENCES vehicle(vehicle_id)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- Revert repair_order advisor FK
ALTER TABLE repair_order DROP FOREIGN KEY repair_order_advisor_fk;
ALTER TABLE repair_order MODIFY COLUMN advisor_id INT NOT NULL;
ALTER TABLE repair_order ADD CONSTRAINT repair_order_advisor_fk
FOREIGN KEY (advisor_id) REFERENCES service_advisor(advisor_id)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- Revert repair_item service_type FK
ALTER TABLE repair_item DROP FOREIGN KEY repair_item_service_type_fk;
ALTER TABLE repair_item MODIFY COLUMN service_type_id INT NOT NULL;
ALTER TABLE repair_item ADD CONSTRAINT repair_item_service_type_fk
FOREIGN KEY (service_type_id) REFERENCES service_type(service_type_id)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- Revert vehicle customer FK
ALTER TABLE vehicle DROP FOREIGN KEY vehicle_customer_fk;
ALTER TABLE vehicle ADD CONSTRAINT vehicle_customer_fk
FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
ON DELETE RESTRICT ON UPDATE CASCADE;

USE autorepairwd_db;

-- First check which repair_orders have NULL advisor_id
SELECT * FROM repair_order WHERE advisor_id IS NULL;

-- Check which repair_items have NULL service_type_id
SELECT * FROM repair_item WHERE service_type_id IS NULL;

USE autorepairwd_db;

-- Check existing FK constraint names
SELECT CONSTRAINT_NAME 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_NAME = 'repair_order' 
AND TABLE_SCHEMA = 'autorepairwd_db'
AND CONSTRAINT_TYPE = 'FOREIGN KEY';

USE autorepairwd_db;
USE autorepairwd_db;

-- Drop the primary key first
ALTER TABLE repair_item DROP PRIMARY KEY;

-- Drop the repair_item_id column
ALTER TABLE repair_item DROP COLUMN repair_item_id;

-- Add composite primary key instead
ALTER TABLE repair_item ADD PRIMARY KEY (order_no, service_type_id);

SELECT CONSTRAINT_NAME 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_NAME = 'repair_item' 
AND TABLE_SCHEMA = 'autorepairwd_db'
AND CONSTRAINT_TYPE = 'FOREIGN KEY';

USE autorepairwd_db;

ALTER TABLE repair_item
ADD CONSTRAINT repair_item_order_fk
FOREIGN KEY (order_no) REFERENCES repair_order(order_no)
ON DELETE CASCADE ON UPDATE CASCADE;


select * FROm customer;

--REVISIONS--
USE autorepairwd_db;

-- Permissions table
CREATE TABLE permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(20) NOT NULL,
    display_name VARCHAR(100) NOT NULL
);

-- User permissions table
CREATE TABLE user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    is_granted TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Insert all permissions
INSERT INTO permissions (module, action, display_name) VALUES
-- Customer
('customer', 'view', 'View Customer'),
('customer', 'add', 'Add Customer'),
('customer', 'edit', 'Edit Customer'),
('customer', 'delete', 'Delete Customer'),
-- Vehicle
('vehicle', 'view', 'View Vehicle'),
('vehicle', 'add', 'Add Vehicle'),
('vehicle', 'edit', 'Edit Vehicle'),
('vehicle', 'delete', 'Delete Vehicle'),
-- Service Type
('service_type', 'view', 'View Service Type'),
('service_type', 'add', 'Add Service Type'),
('service_type', 'edit', 'Edit Service Type'),
('service_type', 'delete', 'Delete Service Type'),
-- Service Advisor
('service_advisor', 'view', 'View Service Advisor'),
('service_advisor', 'add', 'Add Service Advisor'),
('service_advisor', 'edit', 'Edit Service Advisor'),
('service_advisor', 'delete', 'Delete Service Advisor'),
-- Repair Order
('repair_order', 'view', 'View Repair Order'),
('repair_order', 'add', 'Add Repair Order'),
('repair_order', 'edit', 'Edit Repair Order'),
('repair_order', 'delete', 'Delete Repair Order'),
-- Appointment
('appointment', 'view', 'View Appointment'),
('appointment', 'add', 'Add Appointment'),
('appointment', 'edit', 'Edit Appointment'),
('appointment', 'delete', 'Delete Appointment'),
-- Inventory
('inventory', 'view', 'View Inventory'),
('inventory', 'add', 'Add Inventory'),
('inventory', 'edit', 'Edit Inventory'),
('inventory', 'delete', 'Delete Inventory'),
-- Users
('users', 'view', 'View Users'),
('users', 'add', 'Add Users'),
('users', 'edit', 'Edit Users'),
('users', 'delete', 'Delete Users'),
-- Logs
('audit_log', 'view', 'View Audit Logs'),
('login_log', 'view', 'View Login Logs'),
('database', 'view', 'View Database');

ALTER TABLE users 
MODIFY COLUMN role ENUM('admin', 'service_advisor', 'customer') NOT NULL;

-- Admin permissions (all granted)
INSERT INTO user_permissions (user_id, permission_id, is_granted)
SELECT u.user_id, p.permission_id, 1
FROM users u
CROSS JOIN permissions p
WHERE u.role = 'admin'
ON DUPLICATE KEY UPDATE is_granted = 1;

-- Service advisor default permissions
INSERT INTO user_permissions (user_id, permission_id, is_granted)
SELECT u.user_id, p.permission_id,
    CASE
        WHEN p.module = 'customer' AND p.action IN ('view', 'add', 'edit') THEN 1
        WHEN p.module = 'vehicle' AND p.action IN ('view', 'add', 'edit') THEN 1
        WHEN p.module = 'service_type' AND p.action = 'view' THEN 1
        WHEN p.module = 'service_advisor' AND p.action = 'view' THEN 1
        WHEN p.module = 'repair_order' AND p.action IN ('view', 'add', 'edit') THEN 1
        WHEN p.module = 'appointment' AND p.action IN ('view', 'add', 'edit', 'delete') THEN 1
        WHEN p.module = 'inventory' AND p.action = 'view' THEN 1
        ELSE 0
    END as is_granted
FROM users u
CROSS JOIN permissions p
WHERE u.role = 'service_advisor'
ON DUPLICATE KEY UPDATE is_granted = is_granted;

USE autorepairwd_db;

-- Check duplicates
SELECT user_id, permission_id, COUNT(*) 
FROM user_permissions 
GROUP BY user_id, permission_id 
HAVING COUNT(*) > 1;

SELECT * FROM permissions ORDER BY module, action;

-- Delete duplicates keeping only the first occurrence
DELETE p1 FROM permissions p1
INNER JOIN permissions p2
WHERE p1.permission_id > p2.permission_id
AND p1.module = p2.module
AND p1.action = p2.action;

USE autorepairwd_db;

CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    service_type_id INT NOT NULL,
    advisor_id INT DEFAULT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    booked_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicle(vehicle_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (service_type_id) REFERENCES service_type(service_type_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (advisor_id) REFERENCES service_advisor(advisor_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (booked_by) REFERENCES users(user_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

USE autorepairwd_db;

CREATE TABLE supplies (
    supply_id INT AUTO_INCREMENT PRIMARY KEY,
    supply_name VARCHAR(100) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    current_stock DECIMAL(10,2) DEFAULT 0,
    minimum_stock DECIMAL(10,2) DEFAULT 0,
    price_per_unit DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE supply_usage (
    usage_id INT AUTO_INCREMENT PRIMARY KEY,
    supply_id INT NOT NULL,
    order_no INT DEFAULT NULL,
    quantity_used DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT DEFAULT NULL,
    FOREIGN KEY (supply_id) REFERENCES supplies(supply_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (order_no) REFERENCES repair_order(order_no)
        ON DELETE SET NULL ON UPDATE CASCADE
);

