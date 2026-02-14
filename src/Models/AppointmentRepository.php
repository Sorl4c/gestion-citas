<?php

namespace App\Models;

use App\Database;
use PDO;

class AppointmentRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAppointmentsForDate(string $date) {
        $stmt = $this->pdo->prepare("SELECT time FROM appointments WHERE date = ? AND status IN ('booked', 'attended')");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create(array $data) {
        $stmt = $this->pdo->prepare("INSERT INTO appointments (id, customer_name, customer_phone, date, time, status, cancel_token, created_at) VALUES (:id, :name, :phone, :date, :time, :status, :token, :created_at)");
        return $stmt->execute([
            'id' => $data['id'],
            'name' => $data['customer_name'],
            'phone' => $data['customer_phone'],
            'date' => $data['date'],
            'time' => $data['time'],
            'status' => 'booked',
            'token' => $data['cancel_token'],
            'created_at' => time()
        ]);
    }

    public function countWeeklyAppointments(string $phone, string $date) {
        // Calculate start (Monday) and end (Sunday) of the week for the given date
        $timestamp = strtotime($date);
        $startOfWeek = date('Y-m-d', strtotime('monday this week', $timestamp));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week', $timestamp));

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE customer_phone = ? AND date BETWEEN ? AND ? AND status IN ('booked', 'attended')");
        $stmt->execute([$phone, $startOfWeek, $endOfWeek]);
        return $stmt->fetchColumn();
    }

    public function getAllForDate(string $date) {
        $stmt = $this->pdo->prepare("SELECT * FROM appointments WHERE date = ? ORDER BY time ASC");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByToken(string $token) {
        $stmt = $this->pdo->prepare("SELECT * FROM appointments WHERE cancel_token = ? LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus(string $id, string $status) {
        $stmt = $this->pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function updateDateAndTime(string $id, string $date, string $time) {
        $stmt = $this->pdo->prepare("UPDATE appointments SET date = ?, time = ? WHERE id = ?");
        return $stmt->execute([$date, $time, $id]);
    }

    public function checkConflict(string $date, string $time, string $excludeId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE date = ? AND time = ? AND status IN ('booked', 'attended') AND id != ?");
        $stmt->execute([$date, $time, $excludeId]);
        return $stmt->fetchColumn() > 0;
    }

    // Transaction Helpers
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollBack();
    }

    public function inTransaction() {
        return $this->pdo->inTransaction();
    }

    public function isSlotTaken(string $date, string $time) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE date = ? AND time = ? AND status IN ('booked', 'attended')");
        $stmt->execute([$date, $time]);
        return $stmt->fetchColumn() > 0;
    }
}
