# Appointment Booking System

A complete appointment booking app built with PHP, MySQLi, HTML, CSS, and vanilla JavaScript. No frameworks or paid APIs are required.

## Features

- Responsive dashboard with sidebar, summary cards, and upcoming appointments
- Interactive calendar and available time-slot picker
- Booked slots are disabled and cannot be selected
- Create, edit, and cancel appointments
- Server-side validation plus a MySQL unique key that blocks duplicate active bookings
- CSRF protection, prepared statements, and safe error handling
- Dashboard numbers are calculated from live database records

## Requirements

- XAMPP (Apache, MySQL/MariaDB, PHP)
- phpMyAdmin
- A modern browser

## XAMPP setup

1. Copy this project folder into `C:\xampp\htdocs\`.
   Example folder name: `appointment-booking`
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
4. Click **Import**, choose `database.sql`, then click **Go**.
5. Confirm that the `appointment_booking` database and `appointments` table exist.
6. If your MySQL root password is not empty, edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'appointment_booking');
```

7. Open the app:

- From `htdocs`: [http://localhost/appointment-booking/](http://localhost/appointment-booking/)
- If you keep the project on a shared intern drive, map/copy it into `htdocs` or set an Apache alias. PHP pages must be served by Apache, not opened as local files.

## Default database protection

The `appointments` table includes a generated unique column `booked_slot_key`. It is filled only while an appointment status is `booked`. Cancelled rows do not keep the slot locked, so the time can be booked again.

PHP also checks for an existing booked slot before insert/update. If two requests try to book the same slot at once, MySQL error `1062` is caught and returned as a friendly message.

## Time slots

Weekday hours are 9:00 AM to 4:30 PM in 30-minute steps, with a lunch break at 12:00 PM. Past times on the current day are disabled.

## Project files

- `index.php` — welcome page and live stats
- `dashboard.php` — summary cards, calendar, slots, upcoming table
- `book_appointment.php` — create a booking
- `my_appointments.php` — list, edit, and cancel
- `calendar.php` — month view of booked dates
- `config/database.php` — MySQLi connection
- `includes/` — layout and shared helpers
- `api/` — JSON endpoints used by Fetch
- `assets/css/style.css` and `assets/js/app.js`
- `database.sql` — schema and sample rows

## Usage

1. Open **Book Appointment**, pick a date, then pick a free slot.
2. Save the booking. The slot list refreshes immediately.
3. Review items on **Dashboard** or **My Appointments**.
4. Edit a booking to a new open slot, or cancel it with the confirmation dialog.

## Security notes

- All SQL uses prepared statements
- Output is escaped with `htmlspecialchars`
- API writes require a CSRF token from the session
- Database errors are logged; users see generic messages
