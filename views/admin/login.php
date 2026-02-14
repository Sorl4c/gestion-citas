<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Peluquería Minimal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen" x-data="loginApp()">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-sm border border-gray-200">
        <h1 class="text-xl font-bold mb-6 text-center">Acceso Admin</h1>
        
        <form @submit.prevent="login" class="space-y-4">
            <div x-show="error" class="p-3 bg-red-50 text-red-600 rounded-lg text-sm" x-text="error"></div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <input type="text" x-model="username" class="w-full px-4 py-2 rounded-lg border focus:ring-1 focus:ring-black outline-none" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" x-model="password" class="w-full px-4 py-2 rounded-lg border focus:ring-1 focus:ring-black outline-none" required>
            </div>
            
            <button type="submit" class="w-full bg-black text-white py-3 rounded-lg font-medium hover:bg-gray-800 transition-colors" :disabled="loading">
                <span x-show="!loading">Entrar</span>
                <span x-show="loading">...</span>
            </button>
        </form>
    </div>

    <script>
        function loginApp() {
            return {
                username: '',
                password: '',
                loading: false,
                error: null,
                
                async login() {
                    this.loading = true;
                    this.error = null;
                    
                    try {
                        const response = await fetch('./api/v1/auth/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ username: this.username, password: this.password })
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok) {
                            window.location.reload();
                        } else {
                            this.error = data.error || 'Login fallido';
                        }
                    } catch (e) {
                        this.error = 'Error de conexión';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>