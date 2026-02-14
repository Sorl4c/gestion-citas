<?php

namespace App\Models;

use App\Database;
use PDO;

class AdminRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function findByUsername(string $username) {
        $stmt = $this->pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
