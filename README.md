# reservation-system
# Reservation Management System

## Project Overview
A web-based Reservation Management System built with PHP and MySQL. It includes a user-facing website for browsing and booking time slots, and an admin dashboard for managing reservations, users, and time slots.

## Technologies Used
- PHP 8 (OOP)
- MySQL (PDO for database interaction)
- HTML/CSS
- Chart.js (for admin dashboard statistics)

## Installation Steps
1. Clone this repository to your local server (XAMPP/Laragon).
2. Import the `database.sql` file into phpMyAdmin. (Make sure the database name matches the one in `config.php`).
3. Update the database credentials in `config.php` if necessary.
4. Open the project in your browser (e.g., `http://localhost/reservation-system/`).

## Demo Accounts
- **Admin Account:** Register a new account, then go to phpMyAdmin and change the `role` from `user` to `admin`.
- admin@example.com
- admin123
- **Regular User:** Register directly through the UI.
- malak.musameh31@gmail.com
- malak123


## Features
- **Users:** Register, login, view available slots, make a reservation, view their own reservations, and cancel.
- **Admin:** View dashboard stats, view chart of reservations by status, manage (confirm/reject/cancel) reservations, manage users (activate/deactivate), and add/delete time slots.
- **Security:** Passwords are hashed, session-based authentication, role-based access control, and CSRF protection.