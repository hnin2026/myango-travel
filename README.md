# MyanGo Travel - Tour Reservation & Management System
**University of Greenwich (UoG) Final Submission**

---

## 1. Project Overview

**MyanGo Travel** is a full-featured Laravel web application engineered for booking and managing guided cultural tours, multi-tier hotel accommodations, and customer reservations across Myanmar destinations (Yangon, Bagan, Mandalay, Inle, etc.).

### Key Features & Workflows
- **Customer Tour Discovery & Booking**:
  - Filter tours by destination with real-time seat availability checks.
  - Multi-tier dynamic pricing calculation (adult and child seat counting rules, hotel upgrades, seasonal rates).
  - Secure booking submission with automated email confirmations and unique cancellation/payment tokens.
- **Payment & Receipt Verification Workflow**:
  - 7-business-day payment deadline calculation (excluding Saturdays and Sundays).
  - Dedicated self-service portal for customers to upload bank transfer / mobile wallet payment receipts.
  - Administrative review workflow allowing admins to inspect uploaded receipt images/PDFs, approve payments, or reject receipts with reasons.
  - Self-service booking cancellation with mandatory cancellation reason logging.
- **Automated Scheduling & Notifications**:
  - `bookings:send-payment-reminders`: Dispatches polite email reminders exactly 2 calendar days before the payment deadline expires.
  - `bookings:cancel-expired`: Automatically cancels unconfirmed / unpaid bookings that have exceeded their payment deadline, restoring seat capacity.
  - `bookings:complete-past`: Automatically marks paid tours whose checkout date has passed as completed.
- **Role-Based Administration**:
  - Multi-role dashboard (Admin vs. Staff) with real-time metrics, booking status distribution, and quick action queues.
  - Full management of Tours, Hotels, Travel Periods, Seasonal Prices, Blackout Dates, and User Accounts.
  - Customer Inquiry management system with status tracking and responses.

---

## 2. Technology Stack & Prerequisites

| Layer | Technologies & Minimum Versions |
|---|---|
| **Backend Framework** | PHP ^8.3, Laravel framework ^11.0 / ^12.0 (Composer 2.x) |
| **Frontend Architecture**| Blade Templates, Vite ^8.0, Bootstrap ^5.3, Tailwind CSS ^3.1, Alpine.js ^3.4 |
| **Database** | MySQL 8.0+ (or SQLite 3 for quick local evaluation) |
| **Testing** | PHPUnit ^12.0, RefreshDatabase trait, Mail fake |
| **Runtime Environment** | Apache / Nginx / Laragon / PHP built-in CLI server |
| **Node.js Environment** | Node.js v18.x or v20.x, npm v9.x or v10.x |

---

## 3. Installation & Setup Guide

### Step 1: Extract or Clone the Project
Navigate into the extracted project directory:
```bash
cd myango
```

### Step 2: Install Composer Dependencies
Install all required PHP packages using Composer:
```bash
composer install
```

### Step 3: Install Frontend Dependencies
Install JavaScript and CSS packages using npm:
```bash
npm install
```

### Step 4: Configure the Environment (`.env`)
Create your local environment configuration file from `.env.example`:

**On Windows (PowerShell / Command Prompt):**
```powershell
copy .env.example .env
```
**On Linux / macOS:**
```bash
cp .env.example .env
```

### Step 5: Generate Application Key
Generate a secure encryption key for the Laravel application:
```bash
php artisan key:generate
```

### Step 6: Create Storage Symlink
Link the `storage/app/public` directory to `public/storage` so that tour photos and uploaded payment receipts are publicly accessible:
```bash
php artisan storage:link
```

---

## 4. Database Setup

You can choose between two setup methods depending on your assessment environment:

### Option A: Using the Provided Database Dump (Recommended for Assessment)
The project includes a complete, realistic database dump located at `database/myango_dump.sql` featuring tours, accommodations, pricing tiers, and existing sample bookings.

1. Ensure MySQL is running (e.g. via Laragon, XAMPP, or native MySQL service).
2. Create the database:
   ```sql
   CREATE DATABASE myango CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. In your `.env` file, configure your MySQL credentials:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=myango
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```
4. Import the SQL dump:
   ```bash
   mysql -u root -p myango < database/myango_dump.sql
   ```

### Option B: Fresh Database Setup with Migrations & Seeders
If you prefer setting up a clean database:
1. Configure `.env` for either SQLite (`DB_CONNECTION=sqlite`) or MySQL.
   - If using SQLite:
     ```powershell
     New-Item -ItemType File -Path database\database.sqlite -Force
     ```
2. Execute migrations and seed default administrative accounts:
   ```bash
   php artisan migrate --seed
   ```

---

## 5. Compiling Frontend Assets

The repository includes pre-built assets in `public/build/`. If you wish to recompile or modify frontend resources:

- **Build for Production (Optimized)**:
  ```bash
  npm run build
  ```
- **Run Vite Development Server (Hot Module Reload)**:
  ```bash
  npm run dev
  ```

---

## 6. Running the Application

Start the local PHP development server:
```bash
php artisan serve
```
Open your browser and navigate to:
```
http://127.0.0.1:8000
```

---

## 7. User Accounts & Default Credentials

When seeded via `php artisan db:seed` or loaded from the database dump:

| Role | Email Address | Password | Privileges |
|---|---|---|---|
| **Administrator** | `test@example.com` | `password` | Full Administrative Access (Tours, Bookings, Users, Reports) |
| **Staff** | `staff@example.com` | `password` | Bookings, Tours, Inquiries Review (User management restricted) |

*Note: In production environments, passwords should be updated immediately via the profile settings.*

---

## 8. Automated Scheduled Commands

MyanGo utilizes automated scheduled jobs configured in `bootstrap/app.php`. To trigger or test them manually:

1. **Send Payment Reminders (2 Days Before Deadline)**:
   ```bash
   php artisan bookings:send-payment-reminders
   ```
2. **Auto-Cancel Expired Unpaid Bookings**:
   ```bash
   php artisan bookings:cancel-expired
   ```
3. **Auto-Complete Concluded Bookings**:
   ```bash
   php artisan bookings:complete-past
   ```
4. **Run the Full Laravel Scheduler Queue**:
   ```bash
   php artisan schedule:run
   ```

---

## 9. Running Automated Tests

The application features a comprehensive automated test suite consisting of **207 test cases** and **953 assertions** covering unit and feature workflows.

Execute the test suite with:
```bash
php artisan test
```

### Key Test Suites Included:
- `Tests\Feature\AdminBookingDisplayTest`: Tests accurate booking seat breakdowns and child ages.
- `Tests\Feature\AdminRoleBasedAccessTest`: Validates access controls between Admin and Staff accounts.
- `Tests\Feature\BookingPriceIntegrityTest`: Server-side price calculation and anti-tamper verification.
- `Tests\Feature\BookingSeatAvailabilityTest`: Real-time seat quota decrementing and overbooking prevention.
- `Tests\Feature\CancelExpiredBookingsTest`: Automated expiry of bookings exceeding payment deadline.
- `Tests\Feature\SendPaymentRemindersTest`: 2-day reminder email dispatch, idempotency, and error handling.
- `Tests\Feature\PaymentDeadlineBusinessDaysTest`: Business-day deadline calculation (excluding weekends).
- `Tests\Feature\ManualPaymentWorkflowTest`: Receipt uploads, file format validation, approval/rejection lifecycle.
- `Tests\Feature\TourStatusTest` & `TourListingTest`: Public visibility vs. administrative deactivation.

---

## 10. University Assessment Declaration

This project was developed and submitted in partial fulfillment of the requirements for the degree at the **University of Greenwich (UoG)**. All architectural patterns, database schemas, and business workflows adhere to academic standards and professional software engineering practices.
