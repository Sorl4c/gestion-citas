<?php

namespace App\Models;

use App\Database;
use PDO;

class ScheduleRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getConfigForDay(int $dayOfWeek) {
        $stmt = $this->pdo->prepare("SELECT * FROM schedule_config WHERE day_of_week = ? LIMIT 1");
        $stmt->execute([$dayOfWeek]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
