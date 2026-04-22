# Attendance & GPS Tracking System

A comprehensive employee attendance management system with GPS tracking and geofencing capabilities built with Laravel 10.

## 📋 Table of Contents
- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation Guide](#installation-guide)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [Default Credentials](#default-credentials)
- [Troubleshooting](#troubleshooting)
- [Folder Structure](#folder-structure)

## ✨ Features

### For Employees
- Clock in/out with location verification
- View personal attendance history
- Request attendance corrections
- GPS tracking (if enabled)
- Mobile responsive dashboard


### For Admin/Super Admin
- Complete employee management
- Location/geofence management
- GPS route tracking on Google Maps
- Attendance reports with export
- Manage correction requests
- Assign locations to employees
- Enable/disable GPS tracking per employee

### Technical Features
- Role-based access control (Super Admin, Admin, Manager, Employee)
- Geofence validation (100m radius)
- Automatic working hours calculation
- Late arrival & half-day calculation
- Real-time GPS location tracking
- Google Maps integration

## 💻 System Requirements

### Required Software
- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **MySQL**: 5.7 or higher / MariaDB 10.2 or higher
- **Web Server**: Apache/Nginx or Laravel Development Server
- **Node.js**: 16.x or higher (optional, for frontend assets)
- **Git**: Latest version

### PHP Extensions Required
- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

### Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (Chrome, Safari)

## 🚀 Installation Guide

### Step 1: Install Prerequisites

#### Windows (XAMPP/WAMP)
```bash
# Download and install XAMPP from https://www.apachefriends.org/
# OR install individually:
# 1. Install PHP 8.1+
# 2. Install MySQL
# 3. Install Composer from https://getcomposer.org/
# 4. Install Git from https://git-scm.com/


# admin login
# userid : admin@example.com
#password : password


#employee login
# userid : employee@example.com 
# password : password


# Project Run

# php artisan migrate --seed
# php artisan db:seed --class=RolesAndPermissionsSeeder
# php artisan serve