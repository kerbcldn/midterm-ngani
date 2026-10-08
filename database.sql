CREATE DATABASE IF NOT EXISTS pamati_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pamati_db;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role ENUM('admin','authorized_personnel') NOT NULL DEFAULT 'authorized_personnel',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE concern_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active'
);

CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    report_code VARCHAR(30) NOT NULL UNIQUE,
    category_id INT NOT NULL,
    assigned_user_id INT NULL,
    concern_title VARCHAR(200) NOT NULL,
    concern_description TEXT NOT NULL,
    priority ENUM('Low','Medium','High') NOT NULL DEFAULT 'Medium',
    status ENUM('New','In Review','Resolved') NOT NULL DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_report_category FOREIGN KEY (category_id) REFERENCES concern_categories(category_id),
    CONSTRAINT fk_report_user FOREIGN KEY (assigned_user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE report_attachments (
    attachment_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attachment_report FOREIGN KEY (report_id) REFERENCES reports(report_id) ON DELETE CASCADE
);

CREATE TABLE report_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    sender_type ENUM('student','personnel') NOT NULL,
    message_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_message_report FOREIGN KEY (report_id) REFERENCES reports(report_id) ON DELETE CASCADE
);

CREATE TABLE report_status_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    changed_by INT NULL,
    old_status VARCHAR(30),
    new_status VARCHAR(30) NOT NULL,
    remarks TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_report FOREIGN KEY (report_id) REFERENCES reports(report_id) ON DELETE CASCADE,
    CONSTRAINT fk_history_user FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE SET NULL
);

INSERT INTO concern_categories (category_name, description) VALUES
('Gender-Based Discrimination','Unequal or discriminatory treatment based on gender.'),
('Sexual Harassment','Unwanted sexual remarks, conduct, or behavior.'),
('Gender-Based Harassment','Harassment related to gender or gender expression.'),
('Inappropriate or Unwanted Behavior','Other unwanted behavior related to gender-based concerns.'),
('Gender-Related Bullying','Bullying involving gender-related characteristics.'),
('Threat or Intimidation','Threatening or intimidating gender-based conduct.'),
('Other Gender-Based Concern','Other gender-based concern.');

-- Demo account. Change this password before real deployment.
INSERT INTO users (username,password_hash,full_name,role)
VALUES ('admin', SHA2('admin123',256), 'PAMATI Administrator', 'admin');
