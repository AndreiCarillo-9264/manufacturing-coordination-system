# 🎓 Manufacturing Coordination System Project

> A Laravel-based manufacturing coordination platform built as a capstone project for role-based operations, reporting, and inventory workflows.

---

## 📌 Description

This project is a manufacturing coordination system using Laravel 12, Livewire, Tailwind CSS, and role-based access control. It supports workflows for sales, production, inventory, logistics, and administration, including job order processing, inventory transfers, delivery scheduling, and reporting.

---

## 👥 Team Members

- Carillo, Andrei Christopher
- Nebreja, Brad Josh
- Prades, Justine James

---

## 🛠️ Features Implemented

- User authentication and session management via Laravel Fortify
- Role-based access control for admin, sales, production, inventory, and logistics users
- Dashboard overview for sales, production, inventory, and logistics
- Product master data management with import/export support
- User management and activation/deactivation for admin users
- Job order creation, approval, cancellation, status updates, and details retrieval
- Inventory transfer workflows for production
- Actual inventory / cycle count management with verify actions
- Finished goods stock records and aging updates
- Delivery schedule and logistics endorsement workflows
- Import/export endpoints for products, job orders, inventory transfers, finished goods, delivery schedules, and logistics endorsements
- Reports with web and PDF views for job orders, inventory, production, deliveries, logistics, and aging
- Notifications and activity logs for auditability
- AI assistant chat interface with conversation history
- Settings and profile management via Livewire pages

---

## 🧩 Technologies Used

- **Frontend:** Tailwind CSS, Vite, Livewire, Axios
- **Backend:** Laravel 12, PHP 8.2, Laravel Fortify, Laravel Livewire Flux
- **Database:** SQLite by default (`DB_CONNECTION=sqlite`), compatible with MySQL or other supported drivers
- **Authentication:** Laravel Fortify
- **PDF generation:** barryvdh/laravel-dompdf
- **Activity logging:** spatie/laravel-activitylog
- **Realtime / push:** Laravel Echo, Pusher JS (optional)

---

## ⚙️ Local Setup

1. Install Composer dependencies:
   ```bash
   composer install
   ```
2. Install Node dependencies:
   ```bash
   npm install
   ```
3. Copy environment example and generate app key:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```
4. Create the SQLite database file if needed:
   ```bash
   if not exist database\database.sqlite type nul > database\database.sqlite
   ```
5. Run database migrations:
   ```bash
   php artisan migrate
   ```
6. Build frontend assets:
   ```bash
   npm run build
   ```

---

## 🚀 Development Commands

- Start the Vite dev server:
  ```bash
  npm run dev
  ```
- Run the Laravel application locally:
  ```bash
  php artisan serve
  ```
- Run PHP unit tests:
  ```bash
  php artisan test
  ```
- Run Pint code style checks:
  ```bash
  pint --parallel
  ```

---

## 📁 Project Structure

- `app/` – application code, controllers, models, Livewire components, services
- `routes/` – web routes and settings route definitions
- `resources/` – front-end assets, views, and Livewire views
- `database/` – migrations, seeders, and factories
- `tests/` – feature and unit tests

---

## 📌 Notes

- Default environment uses SQLite, but the application supports other Laravel-supported database connections.
- The AI Assistant module is available under `/ai-assistant`.
- Role-specific access is enforced across product, job order, inventory, finished goods, logistics, and reporting features.
