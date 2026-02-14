<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Hub - Peluquería</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen font-mono">

    <div class="max-w-4xl mx-auto p-10">
        <header class="mb-10 border-b border-gray-700 pb-4 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-green-400">🛠️ Dev Hub</h1>
                <p class="text-gray-400 mt-2">Navegación rápida para entorno local</p>
            </div>
            <div class="text-right text-sm text-gray-500">
                <p>PHP: <?php echo phpversion(); ?></p>
                <p>Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- App Pública -->
            <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 hover:border-green-500 transition-colors group">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <span class="bg-green-500/20 text-green-400 p-2 rounded-lg mr-3">📱</span>
                    Cliente / Pública
                </h2>
                <div class="space-y-3">
                    <a href="./" target="_blank" class="block w-full text-center bg-gray-700 hover:bg-green-600 text-white py-3 rounded-lg transition-all">
                        Abrir Home (Reserva)
                    </a>
                    <a href="./cancel" target="_blank" class="block w-full text-center bg-gray-700 hover:bg-red-600 text-white py-3 rounded-lg transition-all text-sm">
                        Vista Cancelación (Demo)
                    </a>
                    <p class="text-xs text-gray-400 text-center">La vista que ve el usuario final.</p>
                </div>
            </div>

            <!-- Admin -->
            <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 hover:border-blue-500 transition-colors">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <span class="bg-blue-500/20 text-blue-400 p-2 rounded-lg mr-3">🔐</span>
                    Administración
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    <a href="./admin" target="_blank" class="block text-center bg-blue-600 hover:bg-blue-500 border border-blue-500 text-white py-3 rounded-lg transition-all font-bold col-span-2">
                        Dashboard
                    </a>
                    <a href="./views/admin/login.php" target="_blank" class="block text-center bg-gray-700 hover:bg-gray-600 border border-gray-600 text-gray-200 py-2 rounded-lg transition-all text-xs">
                        Login Page
                    </a>
                    <a href="./api/v1/auth/logout" target="_blank" class="block text-center bg-red-900/20 hover:bg-red-600 border border-red-900/50 text-red-200 py-2 rounded-lg transition-all text-xs flex items-center justify-center">
                        Forzar Logout
                    </a>
                </div>
                <div class="mt-4 p-3 bg-black/30 rounded text-xs font-mono text-gray-400">
                    <p>User: <span class="text-white">admin</span></p>
                    <p>Pass: <span class="text-white">admin</span></p>
                </div>
            </div>

            <!-- APIs & Debug -->
            <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 hover:border-purple-500 transition-colors md:col-span-2">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <span class="bg-purple-500/20 text-purple-400 p-2 rounded-lg mr-3">📡</span>
                    APIs & Debug Directo
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a href="./api/v1/availability?date=<?php echo date('Y-m-d'); ?>" target="_blank" class="flex flex-col items-center justify-center p-4 bg-gray-700 hover:bg-purple-600 rounded-lg transition-all text-sm">
                        <span class="font-bold mb-1">JSON Disponibilidad</span>
                        <span class="text-xs opacity-75">GET /availability (Hoy)</span>
                    </a>
                    <a href="./api/v1/auth/check" target="_blank" class="flex flex-col items-center justify-center p-4 bg-gray-700 hover:bg-purple-600 rounded-lg transition-all text-sm">
                        <span class="font-bold mb-1">Check Session</span>
                        <span class="text-xs opacity-75">GET /auth/check</span>
                    </a>
                     <a href="./api/v1/admin/appointments?date=<?php echo date('Y-m-d'); ?>" target="_blank" class="flex flex-col items-center justify-center p-4 bg-gray-700 hover:bg-purple-600 rounded-lg transition-all text-sm">
                        <span class="font-bold mb-1">JSON Citas Admin</span>
                        <span class="text-xs opacity-75">GET /admin/appointments</span>
                    </a>
                </div>
            </div>

        </div>

        <footer class="mt-10 text-center text-xs text-gray-600">
            <p>⚠️ Este archivo (dev.php) debe ser eliminado antes de subir a producción.</p>
        </footer>
    </div>

</body>
</html>