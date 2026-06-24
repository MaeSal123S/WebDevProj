# AutoRepair Management System

## Overview
The AutoRepair Management System is a web-based application designed to digitize and streamline the daily operations of an automobile repair shop. The system manages customer information, vehicle records, repair orders, appointments, inventory, service advisors, and system users in a centralized platform.

The project was developed using Laravel and MySQL to improve efficiency, reduce paperwork, and provide accurate record keeping for repair shop operations.

---

## Features

### Customer Management
- Register and maintain customer records
- Update customer information
- Search and view customer history

### Vehicle Management
- Store vehicle information including:
  - Make
  - Model
  - Year
  - Plate Number
  - VIN Number
- Associate vehicles with customers

### Repair Order Management
- Create repair orders
- Assign service advisors
- Track repair status
- Record services performed

### Appointment Scheduling
- Schedule customer appointments
- Manage appointment statuses
- Prevent scheduling conflicts

### Service Type Management
- Maintain a list of repair and maintenance services
- Configure labor hours
- Set book rates per hour

### Inventory Management
- Manage automobile supplies and consumables
- Monitor stock levels
- Record inventory usage
- Receive low stock notifications

### Service Advisor Management
- Manage service advisor information
- Assign advisors to repair orders

### User Management
- Create and manage system users
- Assign user roles and permissions

### Audit Logging
- Track user activities throughout the system
- Maintain accountability and security

### Login Security
- Failed login attempt tracking
- Temporary account lockout after multiple failed attempts
- Login activity monitoring

---

## System Modules

- Dashboard
- Customers
- Vehicles
- Repair Orders
- Appointments
- Service Types
- Inventory
- Service Advisors
- User Management
- Audit Logs
- Login Logs

---

## Technologies Used

### Backend
- PHP 8.x
- Laravel 12

### Frontend
- Blade Templates
- HTML
- CSS
- JavaScript

### Database
- MySQL

### Development Environment
- WAMP Server
- Visual Studio Code

---

## Installation

### Clone the repository

```bash
git clone https://github.com/MaeSal123S/WebDevProj.git
```

### Navigate to the project directory

```bash
cd WebDevProj
```

### Install dependencies

```bash
composer install
```

### Create environment file

```bash
copy .env.example .env
```

### Generate application key

```bash
php artisan key:generate
```

### Configure database credentials in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=autoshop_db
DB_USERNAME=root
DB_PASSWORD=
```

### Run migrations

```bash
php artisan migrate
```

### Start the development server

```bash
php artisan serve
```

---

## Default Roles

- Administrator
- Service Advisor
- Customer

---

## Project Structure

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Helpers/

resources/
├── views/
├── css/

routes/
├── web.php

database/
├── migrations/
```

---

## Future Improvements

- SMS notifications
- Email notifications
- Barcode inventory tracking
- Online payment integration
- Service progress tracking
- Customer portal
- Reports and analytics dashboard

---

## Developers

Developed as an academic project for Information Technology studies focusing on web development, database management, and information management systems.

---

## License

This project is intended for educational purposes.
