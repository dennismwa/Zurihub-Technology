-- =====================================================
-- ZURIHUB TECHNOLOGY DATABASE SCHEMA
-- Database: zurihubc_Technology
-- =====================================================

-- Set default charset
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- ADMIN USERS TABLE
-- =====================================================
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
    `avatar` VARCHAR(255) DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: Admin@123)
INSERT INTO `admin_users` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'info@zurihub.co.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Zurihub Admin', 'super_admin');

-- =====================================================
-- SERVICE CATEGORIES TABLE
-- =====================================================
DROP TABLE IF EXISTS `service_categories`;
CREATE TABLE `service_categories` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `icon` VARCHAR(100) DEFAULT NULL,
    `sort_order` INT(11) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default service categories
INSERT INTO `service_categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Web Development', 'web-development', 'Custom website development services', 1),
('Software Development', 'software-development', 'Custom software and system development', 2),
('Ecommerce', 'ecommerce', 'Online store and ecommerce solutions', 3),
('CRM Systems', 'crm-system', 'Customer Relationship Management systems', 4),
('POS Systems', 'pos-system', 'Point of Sale systems', 5),
('ERP Systems', 'erp-system', 'Enterprise Resource Planning systems', 6),
('LMS Systems', 'lms-system', 'Learning Management Systems', 7),
('SEO Services', 'seo-services', 'Search Engine Optimization', 8),
('Digital Marketing', 'digital-marketing', 'Digital marketing services', 9),
('Website Maintenance', 'website-maintenance', 'Ongoing website maintenance', 10);

-- =====================================================
-- PRICING PACKAGES TABLE
-- =====================================================
DROP TABLE IF EXISTS `pricing_packages`;
CREATE TABLE `pricing_packages` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` INT(11) UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `original_price` DECIMAL(10,2) DEFAULT NULL,
    `currency` VARCHAR(3) DEFAULT 'KES',
    `billing_type` ENUM('one_time', 'monthly', 'yearly', 'custom') DEFAULT 'one_time',
    `features` JSON,
    `is_popular` TINYINT(1) DEFAULT 0,
    `sort_order` INT(11) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`),
    CONSTRAINT `pricing_category_fk` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample pricing packages
INSERT INTO `pricing_packages` (`category_id`, `name`, `slug`, `description`, `price`, `original_price`, `currency`, `billing_type`, `features`, `is_popular`, `sort_order`) VALUES
(1, 'Basic Website', 'basic-website', 'Perfect for small businesses', 25000.00, 35000.00, 'KES', 'one_time', '["5 Pages", "Mobile Responsive", "Contact Form", "Basic SEO", "1 Month Support"]', 0, 1),
(1, 'Business Website', 'business-website', 'For growing businesses', 45000.00, 60000.00, 'KES', 'one_time', '["10 Pages", "Mobile Responsive", "Contact Form", "Blog Integration", "Advanced SEO", "Social Media Integration", "3 Months Support"]', 1, 2),
(1, 'Premium Website', 'premium-website', 'Full-featured corporate website', 85000.00, 120000.00, 'KES', 'one_time', '["Unlimited Pages", "Mobile Responsive", "Custom Design", "CMS Integration", "Advanced SEO", "E-commerce Ready", "6 Months Support", "Priority Support"]', 0, 3),
(3, 'Starter Store', 'starter-store', 'Start selling online', 55000.00, 75000.00, 'KES', 'one_time', '["Up to 50 Products", "Payment Integration", "M-Pesa Integration", "Order Management", "Basic SEO", "3 Months Support"]', 0, 1),
(3, 'Business Store', 'business-store', 'For serious e-commerce', 95000.00, 130000.00, 'KES', 'one_time', '["Up to 500 Products", "Multiple Payment Gateways", "M-Pesa Integration", "Inventory Management", "Advanced SEO", "Analytics Dashboard", "6 Months Support"]', 1, 2),
(3, 'Enterprise Store', 'enterprise-store', 'Full e-commerce solution', 180000.00, 250000.00, 'KES', 'one_time', '["Unlimited Products", "Multi-vendor Support", "All Payment Gateways", "Advanced Inventory", "Custom Features", "1 Year Support", "Dedicated Manager"]', 0, 3),
(4, 'Basic CRM', 'basic-crm', 'Essential CRM features', 75000.00, 100000.00, 'KES', 'one_time', '["Contact Management", "Lead Tracking", "Basic Reports", "Email Integration", "3 Users", "3 Months Support"]', 0, 1),
(4, 'Professional CRM', 'professional-crm', 'Advanced CRM solution', 150000.00, 200000.00, 'KES', 'one_time', '["All Basic Features", "Sales Pipeline", "Task Management", "Advanced Analytics", "10 Users", "Custom Fields", "6 Months Support"]', 1, 2),
(4, 'Enterprise CRM', 'enterprise-crm', 'Complete CRM platform', 300000.00, 400000.00, 'KES', 'one_time', '["All Pro Features", "Unlimited Users", "API Access", "Custom Integrations", "Workflow Automation", "1 Year Support", "Training Included"]', 0, 3),
(5, 'Retail POS', 'retail-pos', 'For retail shops', 45000.00, 60000.00, 'KES', 'one_time', '["Sales Management", "Inventory Tracking", "Receipt Printing", "Basic Reports", "1 Terminal", "3 Months Support"]', 0, 1),
(5, 'Restaurant POS', 'restaurant-pos', 'For restaurants & cafes', 75000.00, 100000.00, 'KES', 'one_time', '["Table Management", "Kitchen Display", "Menu Management", "Split Bills", "3 Terminals", "6 Months Support"]', 1, 2),
(5, 'Enterprise POS', 'enterprise-pos', 'Multi-location POS', 150000.00, 200000.00, 'KES', 'one_time', '["Multi-Location", "Advanced Inventory", "Employee Management", "Loyalty Program", "Unlimited Terminals", "1 Year Support"]', 0, 3),
(6, 'Basic ERP', 'basic-erp', 'Core ERP modules', 200000.00, 280000.00, 'KES', 'one_time', '["Finance Module", "Inventory Module", "HR Module", "Basic Reports", "10 Users", "6 Months Support"]', 0, 1),
(6, 'Business ERP', 'business-erp', 'Comprehensive ERP', 400000.00, 550000.00, 'KES', 'one_time', '["All Basic Modules", "Manufacturing", "Project Management", "Advanced Analytics", "50 Users", "1 Year Support"]', 1, 2),
(6, 'Enterprise ERP', 'enterprise-erp', 'Full ERP suite', 800000.00, 1000000.00, 'KES', 'one_time', '["All Modules", "Unlimited Users", "Custom Development", "API Integration", "Multi-company", "2 Years Support", "Dedicated Team"]', 0, 3);

-- =====================================================
-- PORTFOLIO PROJECTS TABLE
-- =====================================================
DROP TABLE IF EXISTS `portfolio_projects`;
CREATE TABLE `portfolio_projects` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` INT(11) UNSIGNED DEFAULT NULL,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL,
    `client_name` VARCHAR(100) DEFAULT NULL,
    `short_description` TEXT,
    `full_description` TEXT,
    `challenge` TEXT,
    `solution` TEXT,
    `results` TEXT,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `images` JSON,
    `technologies` JSON,
    `features` JSON,
    `project_url` VARCHAR(255) DEFAULT NULL,
    `completion_date` DATE DEFAULT NULL,
    `is_featured` TINYINT(1) DEFAULT 0,
    `sort_order` INT(11) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `views` INT(11) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `category_id` (`category_id`),
    CONSTRAINT `portfolio_category_fk` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample portfolio projects
INSERT INTO `portfolio_projects` (`category_id`, `title`, `slug`, `client_name`, `short_description`, `full_description`, `technologies`, `features`, `thumbnail`, `is_featured`, `sort_order`) VALUES
(1, 'Gatwan Transport Logistics', 'gatwan-transport-logistics', 'Gatwan Logistics Ltd', 'Professional logistics website with freight services and quote system', 'A comprehensive logistics website featuring real-time tracking, quote requests, and service management for a leading transport company in Kenya.', '["HTML5", "CSS3", "JavaScript", "PHP", "MySQL"]', '["Real-time Tracking", "Quote Calculator", "Service Booking", "Fleet Management Display", "SEO Optimized"]', '/assets/Gatwan view.png', 1, 1),
(4, 'Zuri Real Estate CRM', 'zuri-real-estate-crm', 'Neoway Properties', 'Enterprise CRM platform with lead pipeline and automated follow-ups', 'A powerful real estate CRM system designed to manage property listings, client relationships, and sales pipelines with automated workflows.', '["PHP", "MySQL", "JavaScript", "Bootstrap", "Chart.js"]', '["Lead Management", "Property Listings", "Automated Follow-ups", "Sales Pipeline", "Reporting Dashboard", "Email Integration"]', '/assets/Zuri CRM Dashboard.png', 1, 2),
(1, 'Elegant Kaya', 'elegant-kaya', 'Elegant Kaya Villas', 'Luxury villa booking platform with availability calendar', 'An elegant booking platform for luxury villas featuring virtual tours, availability management, and seamless reservation system.', '["HTML5", "CSS3", "JavaScript", "PHP", "MySQL"]', '["Online Booking", "Virtual Tours", "Availability Calendar", "Payment Integration", "Review System"]', '/assets/Elegant Kaya small home view.png', 1, 3),
(3, 'Safari Motors Auto Parts', 'safari-motors-auto-parts', 'Safari Motors Ltd', 'E-commerce platform for automotive parts with M-Pesa integration', 'A full-featured e-commerce solution for automotive parts with inventory management, M-Pesa payments, and delivery tracking.', '["WooCommerce", "WordPress", "PHP", "MySQL", "M-Pesa API"]', '["Product Catalog", "M-Pesa Payment", "Order Tracking", "Inventory Management", "Customer Reviews"]', '/assets/portfolio-placeholder.jpg', 0, 4),
(5, 'Sweetcorn Supermarket POS', 'sweetcorn-supermarket-pos', 'Sweetcorn Ltd', 'Complete point of sale system for retail operations', 'A robust POS system designed for supermarket operations with inventory tracking, sales analytics, and multi-terminal support.', '["PHP", "MySQL", "JavaScript", "Electron", "Thermal Printing"]', '["Sales Processing", "Inventory Management", "Receipt Printing", "Staff Management", "Daily Reports"]', '/assets/portfolio-placeholder.jpg', 0, 5),
(1, 'Traverze Culture', 'traverze-culture', 'Traverze Culture Tours', 'Travel agency website with booking integration', 'A stunning travel agency website showcasing tour packages with online booking capabilities and payment integration.', '["HTML5", "CSS3", "JavaScript", "PHP", "MySQL"]', '["Tour Packages", "Online Booking", "Payment Gateway", "Gallery", "Blog"]', '/assets/portfolio-placeholder.jpg', 0, 6);

-- =====================================================
-- QUOTATION REQUESTS TABLE
-- =====================================================
DROP TABLE IF EXISTS `quotation_requests`;
CREATE TABLE `quotation_requests` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference_no` VARCHAR(20) NOT NULL,
    `category_id` INT(11) UNSIGNED DEFAULT NULL,
    `package_id` INT(11) UNSIGNED DEFAULT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `company_name` VARCHAR(100) DEFAULT NULL,
    `project_type` VARCHAR(100) DEFAULT NULL,
    `budget_range` VARCHAR(50) DEFAULT NULL,
    `timeline` VARCHAR(50) DEFAULT NULL,
    `project_description` TEXT,
    `requirements` TEXT,
    `how_found_us` VARCHAR(100) DEFAULT NULL,
    `attachments` JSON,
    `status` ENUM('new', 'contacted', 'in_progress', 'quoted', 'converted', 'closed', 'spam') DEFAULT 'new',
    `assigned_to` INT(11) UNSIGNED DEFAULT NULL,
    `admin_notes` TEXT,
    `quoted_amount` DECIMAL(12,2) DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `reference_no` (`reference_no`),
    KEY `category_id` (`category_id`),
    KEY `package_id` (`package_id`),
    KEY `status` (`status`),
    KEY `assigned_to` (`assigned_to`),
    CONSTRAINT `quotation_category_fk` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `quotation_package_fk` FOREIGN KEY (`package_id`) REFERENCES `pricing_packages` (`id`) ON DELETE SET NULL,
    CONSTRAINT `quotation_assigned_fk` FOREIGN KEY (`assigned_to`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CONTACT MESSAGES TABLE
-- =====================================================
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `subject` VARCHAR(200) DEFAULT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('new', 'read', 'replied', 'closed', 'spam') DEFAULT 'new',
    `admin_notes` TEXT,
    `is_read` TINYINT(1) DEFAULT 0,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CAREER APPLICATIONS TABLE
-- =====================================================
DROP TABLE IF EXISTS `career_applications`;
CREATE TABLE `career_applications` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `position` VARCHAR(100) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `experience_years` INT(11) DEFAULT NULL,
    `current_company` VARCHAR(100) DEFAULT NULL,
    `linkedin_url` VARCHAR(255) DEFAULT NULL,
    `portfolio_url` VARCHAR(255) DEFAULT NULL,
    `cover_letter` TEXT,
    `resume_path` VARCHAR(255) DEFAULT NULL,
    `skills` JSON,
    `expected_salary` VARCHAR(50) DEFAULT NULL,
    `availability` VARCHAR(50) DEFAULT NULL,
    `status` ENUM('new', 'reviewing', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected') DEFAULT 'new',
    `admin_notes` TEXT,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FEEDBACK/TESTIMONIALS TABLE
-- =====================================================
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_name` VARCHAR(100) NOT NULL,
    `client_title` VARCHAR(100) DEFAULT NULL,
    `company_name` VARCHAR(100) DEFAULT NULL,
    `client_image` VARCHAR(255) DEFAULT NULL,
    `rating` TINYINT(1) DEFAULT 5,
    `testimonial` TEXT NOT NULL,
    `service_type` VARCHAR(100) DEFAULT NULL,
    `project_id` INT(11) UNSIGNED DEFAULT NULL,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_approved` TINYINT(1) DEFAULT 0,
    `sort_order` INT(11) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `project_id` (`project_id`),
    CONSTRAINT `testimonial_project_fk` FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample testimonials
INSERT INTO `testimonials` (`client_name`, `client_title`, `company_name`, `rating`, `testimonial`, `service_type`, `is_featured`, `is_approved`, `sort_order`) VALUES
('Miss Kativa', 'CEO', 'Kativa Makeup', 5, 'The website elevated my online presence completely. Visually stunning, user-friendly, and drives real client bookings every week without fail.', 'Web Development', 1, 1, 1),
('Patrick Ochieng', 'Managing Director', 'Neoway Properties', 5, 'The CRM transformed how we manage leads. We close 40% more deals per month with full pipeline visibility and automated follow-ups.', 'Real Estate CRM', 1, 1, 2),
('Mercy', 'Director', 'Sweetcorn Ltd', 5, 'After implementing the custom POS, every process runs smoothly. Inventory losses dropped by 80% and checkout time halved overnight.', 'POS System', 1, 1, 3),
('Kea Simmons', 'CEO', 'Traverze Culture', 5, 'My travel agency has seen remarkable growth. The website captures our brand perfectly and converts visitors into bookings consistently.', 'Travel Website', 1, 1, 4),
('Brian Kamau', 'Founder', 'Modern Auto Parts', 5, 'Handles hundreds of daily orders seamlessly. WhatsApp and M-Pesa integration are flawless. Online revenue doubled in 3 months.', 'Ecommerce', 1, 1, 5);

-- =====================================================
-- SITE SETTINGS TABLE
-- =====================================================
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'textarea', 'number', 'boolean', 'json', 'image') DEFAULT 'text',
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `description` VARCHAR(255) DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `description`) VALUES
('site_name', 'Zurihub Technology', 'text', 'general', 'Website name'),
('site_tagline', 'Web Development & Software Solutions', 'text', 'general', 'Website tagline'),
('contact_email', 'info@zurihub.co.ke', 'text', 'contact', 'Primary contact email'),
('contact_phone', '+254 758 256 440', 'text', 'contact', 'Primary contact phone'),
('contact_address', 'Ruiru, Kamakis, Kiambu, Kenya', 'textarea', 'contact', 'Office address'),
('notification_email', 'info@zurihub.co.ke', 'text', 'notifications', 'Email for notifications'),
('cc_email', 'mwangidennis546@gmail.com', 'text', 'notifications', 'CC email for notifications'),
('social_facebook', 'https://facebook.com/zurihub', 'text', 'social', 'Facebook URL'),
('social_twitter', 'https://twitter.com/zurihub', 'text', 'social', 'Twitter URL'),
('social_linkedin', 'https://linkedin.com/company/zurihub', 'text', 'social', 'LinkedIn URL'),
('social_instagram', 'https://instagram.com/zurihub', 'text', 'social', 'Instagram URL'),
('currency', 'KES', 'text', 'pricing', 'Default currency'),
('currency_symbol', 'KSh', 'text', 'pricing', 'Currency symbol');

-- =====================================================
-- ACTIVITY LOG TABLE
-- =====================================================
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` INT(11) UNSIGNED DEFAULT NULL,
    `description` TEXT,
    `old_values` JSON,
    `new_values` JSON,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `entity_type` (`entity_type`),
    CONSTRAINT `activity_user_fk` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- EMAIL LOGS TABLE
-- =====================================================
DROP TABLE IF EXISTS `email_logs`;
CREATE TABLE `email_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `to_email` VARCHAR(255) NOT NULL,
    `cc_email` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT,
    `status` ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
    `error_message` TEXT,
    `related_type` VARCHAR(50) DEFAULT NULL,
    `related_id` INT(11) UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- NEWSLETTER SUBSCRIBERS TABLE
-- =====================================================
DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `name` VARCHAR(100) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `unsubscribed_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DASHBOARD STATS VIEW (for quick stats)
-- =====================================================
DROP VIEW IF EXISTS `dashboard_stats`;
CREATE VIEW `dashboard_stats` AS
SELECT
    (SELECT COUNT(*) FROM quotation_requests WHERE status = 'new') AS new_quotations,
    (SELECT COUNT(*) FROM quotation_requests) AS total_quotations,
    (SELECT COUNT(*) FROM contact_messages WHERE status = 'new') AS new_messages,
    (SELECT COUNT(*) FROM contact_messages) AS total_messages,
    (SELECT COUNT(*) FROM career_applications WHERE status = 'new') AS new_applications,
    (SELECT COUNT(*) FROM portfolio_projects WHERE is_active = 1) AS total_projects,
    (SELECT COUNT(*) FROM testimonials WHERE is_approved = 1) AS total_testimonials,
    (SELECT SUM(views) FROM portfolio_projects) AS total_portfolio_views,
    (SELECT COUNT(*) FROM quotation_requests WHERE DATE(created_at) = CURDATE()) AS quotations_today,
    (SELECT COUNT(*) FROM quotation_requests WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) AS quotations_this_month;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- END OF SCHEMA
-- =====================================================
