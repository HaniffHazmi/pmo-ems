-- Create the database
CREATE DATABASE IF NOT EXISTS pmo_ems CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pmo_ems;

-- Create admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create staff table
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    matric_no VARCHAR(50),
    phone_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create shifts table
CREATE TABLE shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    shift_type ENUM('evening', 'night') NOT NULL,
    shift_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_shift_staff FOREIGN KEY (staff_id) REFERENCES staff(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (shift_date, shift_type) -- only one staff per shift per day
) ENGINE=InnoDB;

-- Create salaries table
CREATE TABLE salaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    salary_month INT NOT NULL,
    amount_paid DECIMAL(8,2) NOT NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_salary_staff FOREIGN KEY (staff_id) REFERENCES staff(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
