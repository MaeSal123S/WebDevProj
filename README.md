# AutoRepair Management System

## Overview

The AutoRepair Management System is a web-based application built to digitize and streamline daily operations of an automobile repair shop. The system handles customer records, vehicle management, repair orders, appointment booking, inventory tracking, and user access control through a role-based multi-portal platform.

Built with **Laravel 12** and **MySQL**, styled with a custom dark red/black metallic theme.

---

## User Roles

| Role | Portal URL | Description |
|---|---|---|
| **Admin** | `/admin/dashboard` | Full system access, manages all data and user permissions |
| **Service Advisor** | `/advisor/dashboard` | Manages own appointments, repair orders, and customers |
| **Customer** | `/customer/dashboard` | Books appointments and manages own vehicles |

---

## Features

### Admin Portal

#### Dashboard
- Summary stat cards: customer count, vehicle count, repair orders, service types
- Revenue this month vs last month with trend indicator
- Orders this month vs last month
- Most availed service type
- Low stock supply alert
- Today's appointments list
- Recent repair orders (last 5)
- Recent activity feed (audit log)
- Monthly repair orders bar chart (last 6 months)
- Appointment status breakdown doughnut chart

#### Customer Management
- Add, edit, soft-delete customers
- Shows linked username if customer has a portal account

#### Vehicle Management
- Add, edit, soft-delete vehicles
- Associate vehicles with customers

#### Repair Order Management
- Create repair orders with multiple service types (checklist)
- When selecting a customer, auto-fills vehicle and pre-checks service types from their latest pending/confirmed appointment
- Assign service advisors
- Track all repair history

#### Appointment Management
- Full CRUD with status management (pending / confirmed / cancelled / completed)
- Multiple service types per appointment (checklist selection)
- View Calendar (FullCalendar integration, color-coded by status)
- Calendar accessible from appointments list via "View Calendar" button

#### Inventory (Auto Supplies)
- Add, edit, delete supplies
- Track current stock vs minimum stock threshold
- Restock modal — add quantity to existing stock
- Record Usage modal — deduct stock, optionally link to a repair order
- Low stock alert banner

#### Service Type Management
- Configure service names, predetermined labor hours, and book rates

#### Advisors Management
- Manage service advisor records
- Link advisor accounts to user logins

#### User Management
- Create users with roles: Admin / Service Advisor / Customer
- Fine-grained per-user permission control (35 permissions across 10 modules)
- Admin can grant/revoke individual permissions at any time
- Changes apply immediately on the user's next page load

#### System Logs
- **Audit Logs** — all INSERT, UPDATE, DELETE, LOGIN, LOGOUT, LOGIN_FAILED, LOGIN_LOCKED, PASSWORD_RESET events
- **Login Logs** — filtered view of login-related events only
- **Database Viewer** — browse raw table contents

---

### Service Advisor Portal

#### Dashboard
- 4 stat cards: My Repair Orders, Orders Today, Pending Appointments, Appointments Today
- Today's Appointments panel
- Upcoming Appointments panel (next 5 future pending/confirmed)
- My Recent Repair Orders

#### Appointments (Accept/Decline Workflow)
- **Pending Bookings** section — shows all unassigned customer bookings waiting for an advisor
  - Accept → assigns this advisor, sets status to Confirmed
  - Decline → sets status to Cancelled
- **My Appointments** section — all appointments assigned to this advisor
  - Edit, Delete, Status update (if permission granted by admin)

#### Repair Orders
- Create and manage repair orders assigned to this advisor
- Multiple service types via checklist
- Auto-fills vehicle + service types from customer's latest appointment

#### Customers & Vehicles
- View, add, edit customers and their vehicles

---

### Customer Portal

#### Registration
- Register at `/register` with first/last name, username, and password
- Automatically creates a linked customer record

#### Dashboard
- Welcome message with appointment stats (Pending / Confirmed / Completed)
- Upcoming appointments list

#### My Appointments
- View full appointment history with status badges
- Book new appointment via modal:
  - Select own vehicle
  - Select multiple service types (checklist)
  - Choose date and time
  - Optional notes
- Cancel pending appointments

#### My Profile & Vehicles
- Update own first and last name
- Add, edit, delete own vehicles (plate number + model)

---

## Appointment Booking Flow

```
Customer books → advisor_id = NULL, status = pending
       ↓
All service advisors see it in "Pending Bookings"
       ↓
Advisor clicks Accept → advisor_id = their ID, status = confirmed
       OR
Advisor clicks Decline → status = cancelled
       ↓
Admin can also manage all appointments independently
```

---

## Permission System

The system has **35 permissions** across 10 modules:

| Module | Actions |
|---|---|
| customer | view, add, edit, delete |
| vehicle | view, add, edit, delete |
| service_type | view, add, edit, delete |
| service_advisor | view, add, edit, delete |
| repair_order | view, add, edit, delete |
| appointment | view, add, edit, delete, **status** |
| inventory | view, add, edit, delete |
| users | view, add, edit, delete |
| audit_log | view |
| login_log | view |
| database | view |

Admin can grant any permission to any user. The sidebar and action buttons update immediately based on current permissions.

---

## Technologies Used

| Layer | Technology |
|---|---|
| Backend | PHP 8.x, Laravel 12 |
| Frontend | Blade Templates, CSS3, JavaScript |
| UI Icons | Tabler Icons |
| Charts | Chart.js |
| Calendar | FullCalendar 6 |
| CSS Framework | Bootstrap 5 (utility only) |
| Database | MySQL 8 |
| Dev Environment | WAMP Server, VS Code |

---

## Database Setup

Import `database/db.sql` into a fresh MySQL instance. It creates the database, all tables, seeds the permissions, and creates the default admin account.

```sql
-- Run in MySQL:
source /path/to/database/db.sql;
```

Default admin credentials:
- **Username:** `admin`
- **Password:** `password`

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/MaeSal123S/WebDevProj.git
cd WebDevProj
```

### 2. Install dependencies

```bash
composer install
```

### 3. Create environment file

```bash
copy .env.example .env
php artisan key:generate
```

### 4. Configure database in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=autorepairwd_db
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Import the database

Import `database/db.sql` via phpMyAdmin or MySQL CLI. This creates all tables and seeds the admin account.

### 6. Start the development server

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` — it redirects to the login page.

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/         ← Admin portal controllers
│   │   ├── Advisor/       ← Service advisor portal controllers
│   │   ├── Customer/      ← Customer portal controllers
│   │   └── AuthController.php
│   ├── Middleware/
│   │   ├── AdminMiddleware.php
│   │   ├── AdvisorMiddleware.php
│   │   └── CustomerMiddleware.php
│   └── Requests/
├── Models/
│   ├── Appointment.php      (has many serviceTypes via pivot)
│   ├── Customer.php
│   ├── Permission.php
│   ├── RepairOrder.php
│   ├── ServiceAdvisor.php
│   ├── ServiceType.php
│   ├── Supply.php
│   ├── SupplyUsage.php
│   ├── User.php
│   ├── UserPermission.php
│   └── Vehicle.php

resources/
└── views/
    ├── admin/             ← Admin portal views
    ├── advisor/           ← Advisor portal views
    ├── customer/          ← Customer portal views
    └── auth/              ← Login, register, forgot password

database/
├── db.sql                 ← Full schema + seed data
└── migrations/

public/
├── css/style.css          ← All styles (dark red/black theme)
└── Images/                ← Background images

routes/
└── web.php
```

---

## Key Database Tables

| Table | Purpose |
|---|---|
| `customer` | Customer records |
| `vehicle` | Vehicles linked to customers |
| `service_type` | Available repair/maintenance services |
| `service_advisor` | Advisor profiles |
| `users` | Login accounts (admin/advisor/customer) |
| `repair_order` | Repair job records |
| `repair_item` | Services per repair order (many-to-many) |
| `appointments` | Appointment bookings |
| `appointment_service_types` | Services per appointment (many-to-many) |
| `supplies` | Inventory items |
| `supply_usage` | Stock deduction history |
| `audit_log` | Full system activity log |
| `login_attempts` | Brute-force protection tracking |
| `permissions` | 35 permission definitions |
| `user_permissions` | Per-user permission grants |

---

## License

Developed as an academic project for Information Technology studies.
Educational use only.
