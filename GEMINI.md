# Gestión de Citas - Peluquería ("Minimal Techno")

## Project Overview

This is a **Single-Tenant Appointment Management System** designed for small businesses (like a hairdresser). It adheres to a "Minimal Techno" philosophy: robust architecture, minimal dependencies, and simple deployment (FTP-able).

*   **Type:** PHP Web Application (Mobile First)
*   **Backend:** PHP 8+ (Vanilla + Composer packages), SQLite 3 (WAL mode).
*   **Frontend:** HTML5, Tailwind CSS (via CDN for dev), Alpine.js.
*   **Routing:** `nikic/fast-route`.
*   **Database:** SQLite (`database/peluqueria.db`).
*   **Architecture:** MVC-like (Controllers, Models, Services, Views).

## Key Features

*   **Public:**
    *   View availability (`GET /api/v1/availability`).
    *   Book appointments (`POST /api/v1/appointments`).
    *   Cancel appointments via token (`POST /api/v1/appointments/cancel`).
*   **Admin:**
    *   Secure Login (`POST /api/v1/auth/login`).
    *   Dashboard with "Mobile First" Card UI.
    *   Smart WhatsApp Link generation.
    *   Change appointment status (Attended/Cancelled) (`PATCH /api/v1/admin/appointments/{id}/status`).
*   **Safety:**
    *   Anti-abuse limits (max 2 appts/week per phone).
    *   Atomic transactions for bookings.
    *   Database file protected from web access.

## Architecture & Directory Structure

*   `public/`: The web root. Contains `index.php` (Front Controller), `dev.php` (Dev Hub), and assets.
*   `src/`: Application logic (PSR-4 `App\`).
    *   `Controllers/`: Handle HTTP requests (`HomeController`, `Api\AvailabilityController`, `Api\AppointmentController`, etc.).
    *   `Models/`: Data access layer (`AppointmentRepository`, `ScheduleRepository`, `AdminRepository`).
    *   `Services/`: Business logic (`AvailabilityService`, `AppointmentService`).
    *   `Middleware/`: Auth and request handling (`AuthMiddleware`).
    *   `Router.php`: Defines routes and dispatches requests via `FastRoute`.
    *   `Database.php`: Singleton PDO wrapper for SQLite.
*   `views/`: HTML Templates (Alpine.js + Tailwind).
    *   `home.php`: Public booking page.
    *   `cancel.php`: Public cancellation page.
    *   `admin/dashboard.php`: Admin panel.
    *   `admin/login.php`: Admin login.
*   `database/`: Contains the SQLite database file (`peluqueria.db`).
*   `setup.php`: Script to initialize the database schema and default data.
*   `router.php`: Helper script for the PHP Built-in Server.
*   `tests/`: Integration tests (`test_flow.php`).

## Building and Running

### Prerequisites

*   PHP 8.0+
*   Composer
*   SQLite 3 enabled in PHP

### Installation

1.  **Install Dependencies:**
    ```bash
    composer install
    ```
2.  **Initialize Database:**
    ```bash
    php setup.php
    ```
    *Creates `database/peluqueria.db` and default admin user (`admin` / `admin`).*

### Running Locally

You have two options:

**Option 1: PHP Built-in Server (Recommended for Dev)**

Use the provided `router.php` to handle static files correctly.

```bash
php -S localhost:8080 router.php
```

Access: `http://localhost:8080/public/` (or `http://localhost:8080/` depending on context).

**Option 2: Apache/XAMPP**

Point your vhost or place the folder in `htdocs`.
Access: `http://localhost/gestion-citas-peluqueria/public/`

### Developer Hub

A helper page is available at `public/dev.php` to quickly navigate between Public View, Admin Dashboard, and API endpoints.

Access: `http://localhost:8080/public/dev.php` (adjust port/path as needed).

## Testing

An integration test script is available to verify the full booking flow (Book -> Cancel -> Admin Check).

```bash
php tests/test_flow.php
```

## Development Conventions

*   **Routing:** All routes are defined in `src/Router.php`.
*   **Database:** Use `App\Database::getInstance()` for PDO connections. Use Prepared Statements for everything.
*   **Frontend:** Use Alpine.js for interactivity. Keep CSS minimal (Tailwind classes).
*   **API:** All API responses must be JSON.
*   **Timezones:** The app enforces `Europe/Madrid`.

## Cloudflare Tunnel (Optional)

To expose the local server securely for mobile testing:

1.  Start PHP Server: `php -S localhost:8080 router.php`
2.  Start Tunnel: `cloudflared tunnel --url http://localhost:8080`
