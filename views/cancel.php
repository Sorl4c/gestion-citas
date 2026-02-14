<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar Cita - Peluquería Minimal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 font-sans" x-data="cancelApp()">

    <div class="bg-white max-w-md w-full rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        
        <!-- Header -->
        <div class="bg-black p-6 text-white text-center">
            <h1 class="text-xl font-bold">Gestión de Cita</h1>
        </div>

        <div class="p-8">
            <?php if ($error): ?>
                <div class="text-center text-red-500 py-8">
                    <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <p class="font-medium"><?php echo $error; ?></p>
                </div>
            <?php elseif ($appointment): ?>
                
                <!-- Appointment Details -->
                <div class="text-center mb-8" x-show="!cancelled">
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-2">Estás a punto de cancelar tu cita</p>
                    
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                        <div class="text-3xl font-bold text-gray-900 mb-1">
                            <?php echo date('H:i', strtotime($appointment['time'])); ?>
                        </div>
                        <div class="text-gray-600 font-medium mb-4">
                            <?php 
                                $esDays = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
                                $esMonths = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                                $ts = strtotime($appointment['date']);
                                echo $esDays[date('w', $ts)] . ', ' . date('j', $ts) . ' de ' . $esMonths[date('n', $ts)-1];
                            ?>
                        </div>
                        <div class="text-sm text-gray-400">
                            Cliente: <?php echo htmlspecialchars($appointment['customer_name']); ?>
                        </div>
                    </div>

                    <?php if ($appointment['status'] === 'cancelled'): ?>
                         <div class="mt-6 p-4 bg-yellow-50 text-yellow-700 rounded-lg">
                            Esta cita ya está cancelada.
                        </div>
                    <?php else: ?>
                        <div class="mt-8 space-y-3">
                            <button @click="cancelAppointment('<?php echo $appointment['cancel_token']; ?>')" 
                                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02] shadow-lg flex items-center justify-center"
                                    :disabled="loading">
                                <span x-show="loading" class="animate-spin mr-2">⏳</span>
                                Confirmar Cancelación
                            </button>
                            <a href="./" class="block text-center text-gray-400 text-sm hover:text-black mt-4">Volver al inicio</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Success State -->
                <div x-show="cancelled" x-cloak class="text-center py-8">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Cita Cancelada</h2>
                    <p class="text-gray-500 mb-8">Tu cita ha sido eliminada correctamente.</p>
                    <a href="./" class="bg-black text-white px-8 py-3 rounded-xl hover:bg-gray-800 transition-colors">Reservar otra vez</a>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <script>
        function cancelApp() {
            return {
                loading: false,
                cancelled: <?php echo ($appointment && $appointment['status'] === 'cancelled') ? 'true' : 'false'; ?>,
                
                async cancelAppointment(token) {
                    if (!confirm('¿Seguro que quieres cancelar esta cita?')) return;
                    
                    this.loading = true;
                    try {
                        const response = await fetch('./api/v1/appointments/cancel', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ token: token })
                        });
                        
                        if (response.ok) {
                            this.cancelled = true;
                        } else {
                            alert('Error al cancelar. Inténtalo de nuevo.');
                        }
                    } catch (e) {
                        alert('Error de conexión.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>