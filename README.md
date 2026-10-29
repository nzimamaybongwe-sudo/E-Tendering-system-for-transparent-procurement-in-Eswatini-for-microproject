<<<<<<< HEAD
# E-Tendering System for MicroProjects in Eswatini

## Overview
This PHP MVC starter app digitizes the paper-based procurement workflow used by microproject units in Eswatini. It includes:
- Guest analytics dashboard
- Contractor registration and admin verification
- Tender publishing and bid submission
- Secure bid encryption with OpenSSL
- Audit trail for transparency
- Starter fraud detection module using Python

## Setup
1. Import the database schema:
   - Create a MySQL database named `e_tendering`
   - Run `database/schema.sql`
2. Update `config/database.php` with your database credentials.
3. Configure your web server document root to `public/`.
4. Access the site at `http://localhost/.../public`

## Default admin account
- Email: `admin@etendering.sz`
- Password: `Admin@2026` (replace after import if needed)

## Notes
- The application uses PDO prepared statements and basic RBAC.
- The Python script in `python/fraud_detection.py` is a sample logistic regression model for suspicious bid detection.
- The CMS is built to be extended with evaluation scoring and notification delivery.

# E-Tendering-system-for-transparent-procurement-in-Eswatini-for-microproject
A web-based E-Tendering System for MicroProjects Eswatini designed to improve transparency, accessibility, and efficiency in public procurement. It enables contractors to register, upload documents, view tenders, and submit bids online. Procurement officers and evaluators can manage tenders, review bids, and monitor procurement activities.
 
