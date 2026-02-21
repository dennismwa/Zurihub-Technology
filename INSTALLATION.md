# Zurihub Technology Website - Installation Guide

## System Requirements

- PHP 7.4 or higher (PHP 8.x recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite enabled OR Nginx
- SSL Certificate (recommended for production)

## Quick Start

### 1. Upload Files

Upload all files to your web hosting root directory (usually `public_html` or `www`).

```
/public_html/
├── admin/
├── api/
├── assets/
├── blog/
├── config/
├── css/
├── database/
├── js/
├── uploads/
├── index.html
├── pricing.html
├── quotation.html
├── ... (other HTML files)
├── .htaccess
└── robots.txt
```

### 2. Create Database

1. Log in to your hosting cPanel or database management tool
2. Create a new MySQL database
3. Create a database user and grant all privileges
4. Note down the credentials:
   - Database name
   - Database user
   - Database password
   - Host (usually `localhost`)

### 3. Import Database Schema

1. Open phpMyAdmin or your database management tool
2. Select your database
3. Click "Import"
4. Choose the file: `database/schema.sql`
5. Click "Go" to import

### 4. Configure Database Connection

Edit `config/config.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
```

Also update:
```php
define('SITE_URL', 'https://zurihub.co.ke');
define('SITE_EMAIL', 'info@zurihub.co.ke');
define('CC_EMAIL', 'mwangidennis546@gmail.com');
```

### 5. Set Permissions

Set proper permissions for the uploads folder:

```bash
chmod 755 uploads/
chmod 755 uploads/portfolio/
```

### 6. Access Admin Panel

1. Navigate to: `https://yourdomain.com/admin/`
2. Default login credentials:
   - **Username:** `admin`
   - **Password:** `Admin@123`

⚠️ **IMPORTANT:** Change the default password immediately after first login!

---

## Server Configuration

### Apache (.htaccess)

The included `.htaccess` file handles:
- Clean URLs (no .html extension)
- Security headers
- Caching
- GZIP compression

Make sure `mod_rewrite` is enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Nginx

If using Nginx, add this to your server block:

```nginx
location / {
    try_files $uri $uri/ $uri.html =404;
}

location /admin {
    try_files $uri $uri/ =404;
}

location /api {
    try_files $uri $uri/ =404;
}

# Block access to sensitive files
location ~ /config/ {
    deny all;
}

location ~ /database/ {
    deny all;
}
```

---

## Features Overview

### Admin Dashboard
- **Dashboard:** Overview statistics, charts, recent activity
- **Quotations:** Manage quote requests with status tracking
- **Messages:** Handle contact form submissions
- **Applications:** Manage career applications
- **Portfolio:** CRUD operations for projects
- **Pricing:** Manage pricing packages by category
- **Testimonials:** Add/edit client testimonials
- **Settings:** Configure site settings
- **Statistics:** Detailed analytics and reports

### Frontend Features
- Responsive design with Tailwind CSS
- Multi-step quotation form
- Dynamic pricing page
- SEO optimized
- Fast loading

### API Endpoints
- `POST /api/submit-quotation.php` - Submit quotation
- `POST /api/submit-contact.php` - Submit contact form
- `GET /api/get-portfolio.php` - Fetch portfolio projects
- `GET /api/get-pricing.php` - Fetch pricing packages
- `POST /api/submit-newsletter.php` - Newsletter subscription

---

## Email Configuration

The system uses PHP's `mail()` function by default. For better delivery:

### Option 1: SMTP (Recommended)

Install PHPMailer and update `config/functions.php`:

```bash
composer require phpmailer/phpmailer
```

### Option 2: Hosting Email Settings

Most shared hosting automatically configures email. Check your hosting panel for SMTP settings if emails aren't sending.

### Email Recipients

All form submissions are sent to:
- Primary: `info@zurihub.co.ke` (configurable in Settings)
- CC: `mwangidennis546@gmail.com` (configurable in Settings)

---

## Security Recommendations

1. **Change default password** immediately
2. **Use HTTPS** with SSL certificate
3. **Update PHP** to latest stable version
4. **Regular backups** of database and files
5. **Keep config files** outside web root if possible
6. **Monitor** admin login attempts

---

## Troubleshooting

### Blank Page / 500 Error
- Check PHP error logs
- Verify database credentials in `config/config.php`
- Ensure PHP version is 7.4+

### Forms Not Submitting
- Check if `uploads/` folder is writable
- Verify PHP mail configuration
- Check browser console for JavaScript errors

### Images Not Loading
- Verify file paths in `/assets/`
- Check file permissions (644 for files, 755 for folders)

### Admin Login Issues
- Clear browser cookies
- Check database connection
- Verify `admin_users` table has records

---

## Database Structure

### Main Tables
- `admin_users` - Admin accounts
- `quotation_requests` - Quote submissions
- `contact_messages` - Contact form entries
- `career_applications` - Job applications
- `portfolio_projects` - Portfolio items
- `pricing_packages` - Pricing plans
- `service_categories` - Service categories
- `testimonials` - Client testimonials
- `site_settings` - Configuration
- `activity_logs` - Admin activity
- `email_logs` - Email history
- `newsletter_subscribers` - Newsletter list

---

## Support

For technical support:
- Email: info@zurihub.co.ke
- Phone: +254 758 256 440
- WhatsApp: +254 758 256 440

---

## Changelog

### Version 1.0.0
- Initial release
- Full admin dashboard
- Quotation management
- Portfolio CRUD
- Pricing management
- Email notifications
- Statistics & analytics
