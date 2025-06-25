-- Mail Sender Database Schema
CREATE DATABASE IF NOT EXISTS mail_sender;
USE mail_sender;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'user') DEFAULT 'user',
    full_name VARCHAR(100) NOT NULL,
    designation VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Email configurations table
CREATE TABLE email_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_name VARCHAR(100) NOT NULL,
    email_address VARCHAR(100) NOT NULL,
    smtp_host VARCHAR(100) NOT NULL,
    smtp_port INT NOT NULL,
    smtp_username VARCHAR(100) NOT NULL,
    smtp_password VARCHAR(255) NOT NULL,
    encryption_type ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
    from_name VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Email templates table
CREATE TABLE email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_by INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Mail history table
CREATE TABLE mail_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_email VARCHAR(100) NOT NULL,
    from_name VARCHAR(100) NOT NULL,
    to_email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    footer TEXT,
    sender_name VARCHAR(100),
    sender_designation VARCHAR(100),
    template_id INT,
    config_id INT,
    sent_by INT,
    status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
    error_message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES email_templates(id),
    FOREIGN KEY (config_id) REFERENCES email_configs(id),
    FOREIGN KEY (sent_by) REFERENCES users(id)
);

-- Insert default super admin user (password: admin123)
INSERT INTO users (username, email, password, role, full_name, designation) VALUES 
('admin', 'admin@mailsender.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'Super Administrator', 'System Administrator');

-- Sample email configuration
INSERT INTO email_configs (config_name, email_address, smtp_host, smtp_port, smtp_username, smtp_password, from_name, created_by) VALUES 
('Default Gmail', 'your-email@gmail.com', 'smtp.gmail.com', 587, 'your-email@gmail.com', 'your-app-password', 'Mail Sender System', 1);

-- Sample email template
INSERT INTO email_templates (title, content, created_by) VALUES 
('Welcome Email', '<h2>Welcome!</h2><p>Thank you for joining us. We are excited to have you on board.</p><p>Best regards,<br>The Team</p>', 1),
('Newsletter', '<h2>Monthly Newsletter</h2><p>Here are the latest updates from our company...</p>', 1);