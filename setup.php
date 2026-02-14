<?php

require __DIR__ . '/vendor/autoload.php';

use App\Database;

try {
    $pdo = Database::getInstance();
    
    echo "Connected to database.
";

    // Create appointments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
        id TEXT PRIMARY KEY,
        customer_name TEXT NOT NULL,
        customer_phone TEXT NOT NULL,
        date TEXT NOT NULL,
        time TEXT NOT NULL,
        status TEXT CHECK(status IN ('booked', 'cancelled', 'attended')) DEFAULT 'booked',
        cancel_token TEXT UNIQUE NOT NULL,
        created_at INTEGER NOT NULL
    )");
    
    echo "Created appointments table.
";

    // Create schedule_config table
    $pdo->exec("CREATE TABLE IF NOT EXISTS schedule_config (
        day_of_week INTEGER PRIMARY KEY CHECK(day_of_week BETWEEN 0 AND 6),
        is_open INTEGER DEFAULT 1,
        start_time TEXT NOT NULL,
        end_time TEXT NOT NULL,
        break_start TEXT,
        break_end TEXT
    )");

    echo "Created schedule_config table.
";

    // Create admin_users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        username TEXT PRIMARY KEY,
        password_hash TEXT NOT NULL
    )");
    
    echo "Created admin_users table.
";
    
    // Create default admin user
    // Password: admin (change in production)
    $password = password_hash('admin', PASSWORD_DEFAULT);
    $pdo->exec("INSERT OR IGNORE INTO admin_users (username, password_hash) VALUES ('admin', '$password')");

    echo "Created default admin user.
";

    // Create default schedule config (Mon-Fri 09:00-18:00, Sat 09:00-14:00, Sun Closed)
    // 0=Sun, 1=Mon, ..., 6=Sat
    $defaultSchedule = [
        [1, 1, '09:00', '18:00', '13:00', '14:00'],
        [2, 1, '09:00', '18:00', '13:00', '14:00'],
        [3, 1, '09:00', '18:00', '13:00', '14:00'],
        [4, 1, '09:00', '18:00', '13:00', '14:00'],
        [5, 1, '09:00', '18:00', '13:00', '14:00'],
        [6, 1, '09:00', '14:00', null, null],
        [0, 0, '00:00', '00:00', null, null]
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO schedule_config (day_of_week, is_open, start_time, end_time, break_start, break_end) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($defaultSchedule as $day) {
        $stmt->execute($day);
    }
    
    echo "Database setup completed successfully.
";

} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "
";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "
";
    exit(1);
}