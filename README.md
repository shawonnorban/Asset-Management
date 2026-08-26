<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://datastateltd.com/Logo.png" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


# 🧾 Enterprise Asset Management System

> 🚧 **Under Construction** — The system is actively being developed. Features, screens, and database structure may change between releases.

Enterprise Asset Management System (EAMS) is a web-based application developed using Laravel 10 to manage company office assets efficiently and securely.

This system provides comprehensive asset registration, QR code tracking, depreciation calculation, audit trail logging, and role-based access control. It is designed to support asset lifecycle management, compliance monitoring, and structured reporting within an organization.

---

## 🚀 Core Features

### 🏷️ Asset Management
- Create, update, delete, and search asset records.
- Assets are categorized by **category**, **location**, and **assigned employee**.
- Each asset is assigned a unique **Asset Code**.
- Full asset lifecycle management from acquisition to disposal.
- Export asset data and full reports to **Excel** and **PDF**.
- Track technical specifications for computers, peripherals, printers, and network devices.

### 🤝 Asset Assignment & IT Management
- Assign assets to employees and process asset returns.
- Track assets currently in use.
- Manage software licenses and asset-to-license assignments.

### 📋 Stock Take
- Create and finalize stock-taking sessions.
- Scan asset QR codes to verify physical inventory.
- Review stock-take details and export stock-take reports to PDF.

---

### 📱 QR Code Integration
- Automatic QR Code generation based on `asset_code`.
- QR codes stored in `storage/app/public/qr/` or generated dynamically.
- Built-in QR Scanner feature to instantly display asset details.
- Quick asset verification using device camera.

---

### 📉 Asset Depreciation
- Automatic depreciation calculation using:
  - **Straight-Line Method**
  - **Declining Balance Method**
- Based on Indonesian Tax Office (DJP) asset classification.
- Monthly depreciation records stored in `monthly_depreciations` table.
- Tracks asset book value over time.
- Supports asset disposal (damaged, sold, donated, lost, etc.).

---

### 🧍 Employee Management
- Store employee information (code, department, position).
- Assets can be assigned/unassigned to employees (nullable relationship).
- If an employee is deleted, related asset data remains safe (`ON DELETE SET NULL`).

---

### 🧠 Audit Trail (Activity Logging)
- Implemented using **Spatie Laravel Activitylog**.
- Logs:
  - User who performed the action
  - Timestamp
  - Data before and after modification
  - URL, IP address, HTTP method
- Primary logs stored in `activity_log`.
- Mirrored into `audit_logs` for reporting and UI display.
- Ensures transparency and accountability.

---

### 🧾 Reporting & Feedback
- Asset damage reporting module.
- Report status: **Pending** / **Resolved**.
- Admin repair feedback system.
- Review, complete, and track incoming issue reports.
- Export completed reports to PDF.
- Dashboard summary & monitoring.

### 👥 Organization & Account Administration
- Manage employees, departments, and positions.
- Manage user accounts and account status.
- Restrict modules and actions with role-based middleware.

---

## 🔐 Roles & Access Control

The system uses custom `CheckRole` middleware for role-based authorization.

### 👑 Admin
- Full system access
- Manage assets, employees, users, and roles
- Configure depreciation settings
- View and export audit trail logs
- Manage reports and feedback

### 👨‍💼 Manager
- View asset data and summary reports
- Monitor depreciation values
- Review damage reports
- Access dashboard analytics

### 👨‍🔧 Staff
- Register new assets
- Update asset status
- Scan QR codes
- Submit damage reports
- View assigned assets

### 🔎 Auditor
- Read-only access to:
  - Asset data
  - Depreciation reports
  - Audit trail logs
- Generate audit reports
- Monitor compliance and asset changes

---

## 🛠️ Teknologi yang Digunakan

| Kebutuhan   | Teknologi                         |
|-------------|-----------------------------------|
| Framework   | Laravel 10                        |
| Frontend    | React, Inertia.js, Bootstrap 5, Vite, Axios |
| Database    | MySQL 8                           |
| QR Code     | endroid/qr-code                   |
| UI & Utilities | Tailwind CSS, Radix UI, Lucide React, html5-qrcode |
| PDF Export  | barryvdh/laravel-dompdf           |
| Notifikasi  | realrashid/sweet-alert            |
| Audit Trail | spatie/laravel-activitylog        |
| Auth & Role | Laravel UI + CheckRole Middleware |
| Dev Tools   | Laravel Pint, Collision, Ignition |

---

## ⚙️ Installation

### Requirements

- PHP 8.1 or newer
- Composer
- Node.js and npm
- MySQL 8 or compatible MySQL/MariaDB server

### Setup

```bash
git clone https://github.com/shawonnorban/Asset-Management.git
cd Asset-Management
composer install
copy .env.example .env
php artisan key:generate
```

Update the database values in `.env`, then run:

```bash
php artisan migrate
php artisan storage:link
npm install
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000` in your browser. For local development with Vite, use `npm run dev` in a separate terminal.

> **Note:** Never commit `.env` or other environment secrets. If the target database already contains tables, review its migration history before running migrations.

---

👨‍💻 Author

Betran Arya Pramuja
Backend Developer | Laravel Enthusiast

---


<=========================================================================>
<=========================================================================>

