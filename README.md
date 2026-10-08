# PAMATI Actual PHP/MySQL System

PAMATI: A Web-Based Anonymous Platform for Reporting and Addressing Gender-Based Concerns Among Students.

## Technology
- PHP
- MySQL / MariaDB
- HTML/CSS
- PDO prepared statements
- XAMPP for local development

## Install in XAMPP
1. Install XAMPP.
2. Start Apache and MySQL.
3. Copy the `PAMATI_Actual_System` folder to:
   `C:\xampp\htdocs\`
4. Open phpMyAdmin:
   `http://localhost/phpmyadmin`
5. Import `database.sql`.
6. Open:
   `http://localhost/PAMATI_Actual_System/`

## Student functions
- Submit anonymous report
- Select gender-based concern category
- Add title and description
- Optional evidence upload
- Receive unique PMT reference code
- Track status
- Send anonymous messages

## Authorized personnel functions
- Login
- Dashboard statistics
- View reports
- Change priority
- Change status
- Add remarks
- Send anonymous responses

## Demo account
Username: admin
Password: admin123

Change this account/password before any real deployment.

## Database mapping
USERS
CONCERN_CATEGORIES
REPORTS
REPORT_ATTACHMENTS
REPORT_MESSAGES
REPORT_STATUS_HISTORY

## Important security limitation
This is a school project prototype, not a production deployment. For production, add HTTPS, stronger password setup, CSRF protection, stricter upload MIME/content validation, access logging, secure file storage, rate limiting, backup, and a formal privacy/data-retention policy.
