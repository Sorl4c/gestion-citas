<?php

namespace App\Services;

class WhatsAppService {
    private $baseUrl = "https://wa.me/";
    private $devMode = true; // Set to true to force messages to dev phone
    private $devPhone = "34605084570"; // Hardcoded dev phone

    public function generateLink(string $phone, string $message): string {
        // Clean phone (remove +, spaces, dashes)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add prefix 34 (Spain) if missing. Configurable.
        if (strlen($cleanPhone) == 9) {
            $cleanPhone = '34' . $cleanPhone;
        }

        // DEV MODE OVERRIDE
        if ($this->devMode) {
            $cleanPhone = $this->devPhone;
        }

        // Use urlencode (standard) instead of rawurlencode (RFC 3986) for better desktop app compatibility with spaces/emojis
        return $this->baseUrl . $cleanPhone . "?text=" . urlencode($message);
    }

    public function getConfirmationMessage(array $appointment, string $cancelUrl): string {
        // Format date and time for friendly display
        $esDays = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        $esMonths = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        
        $ts = strtotime($appointment['date']);
        $dateStr = $esDays[date('w', $ts)] . ', ' . date('j', $ts) . ' de ' . $esMonths[date('n', $ts)-1];
        $timeStr = date('H:i', strtotime($appointment['time']));
        
        // Use explicit unicode for emojis to avoid file encoding issues
        return "Hola {$appointment['customer_name']}! \u{2702}\n\n" .
               "Confirmamos tu cita para el *$dateStr a las $timeStr*.\n\n" .
               "\u{1F4CD} Peluquería Estilo\n" .
               "\u{274C} Si no puedes venir, cancela aquí gratis:\n$cancelUrl";
    }
}
