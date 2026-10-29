# E-Tendering System for MicroProjects in Eswatini

A web-based digital procurement platform designed to enhance transparency, accessibility, and accountability in Eswatini's public procurement for Microprojects.

---

## 🔗 Live Preview & Demonstration

- 🌐 **Interactive Web Demo:** [👉 Click Here to Preview the System Live](https://nzimamaybongwe-sudo.github.io/E-Tendering-system-for-transparent-procurement-in-Eswatini-for-microproject/)
- 🎬 **Video Walkthrough:** [Watch System Demonstration](Presentation1.mp4) *(or add your Google Drive / YouTube link here)*

---

## 📌 System Overview

This PHP-based system digitizes the manual paper-based procurement process for Microprojects in Eswatini. Key features include:

- **Public Transparency Dashboard:** Open access to public procurement statistics, published tender listings, and scoring rubrics without requiring login.
- **Contractor Portal:** Easy online company registration, document upload (CIC certificate, tax clearance, etc.), bid submission with cryptographic hash integrity, and appeal filing.
- **Role-Based Access Control (RBAC):** Distinct dashboards and capabilities for:
  - **Contractors** (submit bids, track outcomes, upload compliance files)
  - **Procurement Officers** (publish tenders, manage deadlines, oversee evaluations, execute awards)
  - **Evaluators** (review assigned bids, enter criterion scores and qualitative feedback)
  - **Administrators** (verify contractor credentials, manage user roles, audit trail inspection)
- **Automated Deadlines & Bid Integrity:** Automatic tender closing at deadline boundary, bid lockout, and SHA-256 tamper-detection hashing.
- **Procurement Guidance Centre:** Embedded guides and tutorials to help local SMEs participate competitively.
- **Audit Logging:** Immutable timestamped activity logs for accountability.

---

## 🛠️ Technology Stack

- **Backend:** Pure PHP (MVC architecture, PDO prepared statements)
- **Database:** MySQL relational database (13 normalized tables)
- **Frontend:** Bootstrap 5 (responsive design), Vanilla JavaScript, HTML5, CSS3
- **Email Delivery:** PHPMailer (SMTP authenticated notifications)
- **Analytics / AI Prototype:** Python (`python/fraud_detection.py` for suspicious bidding pattern checks)

---

## 🚀 Local Installation & Setup

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/nzimamaybongwe-sudo/E-Tendering-system-for-transparent-procurement-in-Eswatini-for-microproject.git
   ```
2. **Database Configuration:**
   - Open phpMyAdmin (or your MySQL client) and create a database named `e_tendering`.
   - Import the database schema from `database/schema.sql`.
3. **Environment Setup:**
   - Configure `.env` or `config/database.php` with your local database credentials (`DB_HOST`, `DB_USER`, `DB_PASS`).
4. **Serve the Application:**
   - Point your local web server (WAMP, XAMPP, or Apache) document root to the project directory or the `public/` directory.
   - Access the platform in your browser at:
     ```text
     http://localhost/E-Tendering system project/public/
     ```

---

## 🔐 Default Demo Accounts

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@etendering.sz` | `Admin@2026` |

 
