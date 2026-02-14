<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reserva tu Cita - Peluquería Minimal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        /* Custom scrollbar for time slots */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Smooth fade */
        .fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
        .fade-enter, .fade-leave-to { opacity: 0; }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased selection:bg-black selection:text-white" 
      x-data="bookingApp()">

    <div class="max-w-md mx-auto min-h-screen flex flex-col bg-white sm:bg-transparent sm:shadow-none shadow-2xl relative">
        
        <!-- Branding Accent -->
        <div class="h-1.5 w-full bg-black sticky top-0 z-20"></div>

        <!-- Loading Overlay -->
        <div x-show="loading" class="absolute inset-0 z-50 bg-white/80 flex items-center justify-center backdrop-blur-sm rounded-xl">
            <div class="flex flex-col items-center">
                <svg class="animate-spin h-8 w-8 text-black mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cargando...</span>
            </div>
        </div>

        <!-- Header & Progress -->
        <header class="px-6 pt-8 pb-4 text-center sm:mt-4">
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Peluquería Estilo</h1>
            
            <!-- Progress Bar -->
            <div class="mt-6 mb-2 flex items-center justify-between text-xs font-medium text-gray-400 uppercase tracking-wider">
                <span x-text="'Paso ' + step + ' de 3'"></span>
                <span x-text="getStepName(step)"></span>
            </div>
            <div class="h-1 w-full bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-black transition-all duration-500 ease-out" :style="'width: ' + ((step/3)*100) + '%'"></div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow px-6 pb-8 w-full max-w-md mx-auto relative overflow-hidden">
            
            <!-- Success Message -->
            <div x-show="success" x-cloak class="absolute inset-0 bg-white z-40 flex flex-col items-center justify-center text-center p-6 animate-fade-in-up">
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mb-6 shadow-inner">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-3xl font-bold mb-2 text-gray-900">¡Confirmado!</h2>
                <p class="text-gray-500 mb-8 text-lg">Tu cita ha sido reservada.</p>
                
                <div class="bg-gray-50 p-6 rounded-2xl w-full mb-8 text-left border border-gray-100 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-black"></div>
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Servicio</p>
                            <p class="font-bold text-lg" x-text="selectedService.name"></p>
                        </div>
                        <div class="text-2xl" x-text="selectedService.icon"></div>
                    </div>
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Fecha</p>
                            <p class="font-medium text-gray-900"><span x-text="formatDate(selectedDate)"></span> • <span x-text="selectedTime"></span></p>
                        </div>
                    </div>
                </div>
                
                <button @click="reset()" class="w-full bg-black text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-gray-900 hover:shadow-xl transform hover:-translate-y-0.5 transition-all">Nueva Reserva</button>
            </div>

            <!-- Error Message -->
             <div x-show="error" x-cloak class="mb-6 p-4 bg-red-50 text-red-600 rounded-xl border border-red-100 text-sm flex items-start shadow-sm">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-text="error" class="font-medium"></span>
            </div>

            <!-- Step 1: Service Selection -->
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-4">
                <h2 class="text-xl font-bold mb-6 text-gray-900">¿Qué te hacemos hoy?</h2>
                <div class="space-y-4">
                    <template x-for="service in services" :key="service.id">
                        <button @click="selectService(service)" 
                                class="w-full text-left p-4 bg-white rounded-2xl shadow-[0_2px_8px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_16px_rgba(0,0,0,0.08)] border border-transparent hover:border-gray-100 transition-all duration-300 flex items-center group active:scale-[0.98] active:bg-gray-50">
                            
                            <!-- Icon -->
                            <div class="w-12 h-12 rounded-xl bg-gray-50 text-2xl flex items-center justify-center mr-4 group-hover:bg-black group-hover:text-white transition-colors duration-300 shadow-inner" x-text="service.icon"></div>
                            
                            <!-- Info -->
                            <div class="flex-grow">
                                <span class="block font-bold text-gray-900 text-base mb-1" x-text="service.name"></span>
                                <div class="flex items-center text-gray-400 text-sm font-medium">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span x-text="service.duration + ' min'"></span>
                                </div>
                            </div>

                            <!-- Price Pill -->
                            <div class="bg-gray-100 px-3 py-1.5 rounded-lg group-hover:bg-black group-hover:text-white transition-colors duration-300">
                                <span class="font-bold text-sm" x-text="service.price + '€'"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Step 2: Date & Time -->
            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="flex items-center mb-6">
                    <button @click="step = 1" class="mr-4 p-2 -ml-2 text-gray-400 hover:text-black rounded-full hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <h2 class="text-xl font-bold">Fecha y Hora</h2>
                </div>
                
                <!-- Date Scroller -->
                <div class="flex overflow-x-auto no-scrollbar gap-3 mb-8 pb-4 -mx-6 px-6 snap-x">
                    <template x-for="day in nextDays" :key="day.dateStr">
                        <button @click="selectDate(day.dateStr)" 
                                :class="{'bg-black text-white shadow-lg scale-105': selectedDate === day.dateStr, 'bg-white text-gray-500 shadow-sm border border-gray-100': selectedDate !== day.dateStr}"
                                class="flex-shrink-0 w-16 h-20 rounded-2xl flex flex-col items-center justify-center transition-all duration-300 snap-center active:scale-95">
                            <span class="text-[10px] uppercase font-bold tracking-widest mb-1 opacity-80" x-text="day.dayName"></span>
                            <span class="text-2xl font-bold" x-text="day.dayNum"></span>
                        </button>
                    </template>
                </div>

                <!-- Slots -->
                <div x-show="selectedDate" class="animate-fade-in">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                        Disponibilidad <div class="h-px bg-gray-100 flex-grow ml-3"></div>
                    </h3>
                    
                    <div x-show="slots.length > 0" class="grid grid-cols-3 gap-3 max-h-60 overflow-y-auto pr-1 pb-2">
                        <template x-for="slot in slots" :key="slot">
                            <button @click="selectTime(slot)" 
                                    class="py-3 px-2 rounded-xl text-sm font-bold border transition-all duration-200"
                                    :class="{'bg-black text-white border-black shadow-md': selectedTime === slot, 'bg-white text-gray-700 border-gray-100 hover:border-gray-300 shadow-sm': selectedTime !== slot}">
                                <span x-text="slot"></span>
                            </button>
                        </template>
                    </div>

                    <div x-show="slots.length === 0 && !loading" class="text-center py-10">
                         <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3 text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                         </div>
                        <p class="text-gray-500 font-medium">Completo</p>
                        <p class="text-xs text-gray-400 mt-1">Prueba otro día</p>
                    </div>
                </div>

                <!-- Floating Bottom Bar -->
                <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/90 backdrop-blur-md border-t border-gray-100" x-show="selectedTime" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0">
                    <div class="max-w-md mx-auto">
                        <button @click="step = 3" class="w-full bg-black text-white py-4 rounded-xl font-bold text-lg shadow-xl hover:shadow-2xl hover:bg-gray-900 transform active:scale-[0.98] transition-all flex items-center justify-center">
                            <span>Siguiente</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Customer Details -->
             <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="flex items-center mb-6">
                    <button @click="step = 2" class="mr-4 p-2 -ml-2 text-gray-400 hover:text-black rounded-full hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <h2 class="text-xl font-bold">Datos de contacto</h2>
                </div>

                <form @submit.prevent="submitBooking" class="space-y-5">
                    
                    <div class="space-y-4">
                        <div class="group">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Nombre</label>
                            <input type="text" x-model="form.name" required class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition-all font-medium" placeholder="Ej: Carlos Pérez">
                        </div>
                        <div class="group">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Teléfono</label>
                            <input type="tel" x-model="form.phone" required pattern="[0-9]{9,}" class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition-all font-medium" placeholder="Ej: 600123456">
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 space-y-3 mt-8">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-gray-500 text-sm">Servicio</span>
                            <span class="font-bold text-gray-900" x-text="selectedService.name"></span>
                        </div>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-gray-500 text-sm">Horario</span>
                            <span class="font-bold text-gray-900" x-text="formatDate(selectedDate) + ', ' + selectedTime"></span>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <span class="font-bold text-lg text-gray-900">Total</span>
                            <span class="font-extrabold text-2xl text-gray-900" x-text="selectedService.price + '€'"></span>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-bold text-lg shadow-xl hover:shadow-2xl hover:bg-gray-900 transform active:scale-[0.98] transition-all mt-6">
                        Confirmar Reserva
                    </button>
                    
                    <p class="text-center text-xs text-gray-400 mt-4">Al reservar aceptas nuestra política de privacidad.</p>
                </form>
            </div>

        </main>
    </div>

    <script>
        function bookingApp() {
            return {
                step: 1,
                loading: false,
                error: null,
                success: false,
                appointmentId: '',
                
                // DATA ENRICHED WITH ICONS
                services: [
                    { id: 1, name: 'Corte de Caballero', duration: 30, price: 15, icon: '✂️' },
                    { id: 2, name: 'Corte + Barba', duration: 45, price: 22, icon: '🧔' },
                    { id: 3, name: 'Afeitado Clásico', duration: 30, price: 12, icon: '🪒' }
                ],
                
                selectedService: {},
                selectedDate: null,
                selectedTime: null,
                slots: [],
                nextDays: [],
                
                form: {
                    name: '',
                    phone: ''
                },

                init() {
                    this.generateNextDays();
                },

                getStepName(step) {
                    return ['Servicio', 'Fecha y Hora', 'Confirmar'][step - 1];
                },

                selectService(service) {
                    this.selectedService = service;
                    this.step = 2;
                    // Auto select today if not selected
                    if (!this.selectedDate) {
                        this.selectDate(this.nextDays[0].dateStr);
                    }
                },

                generateNextDays() {
                    const days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                    const today = new Date();
                    const list = [];
                    
                    for (let i = 0; i < 14; i++) {
                        const d = new Date(today);
                        d.setDate(today.getDate() + i);
                        
                        // Optional: Skip Sundays
                        // if (d.getDay() === 0) continue; 

                        const dateStr = d.toISOString().split('T')[0];
                        list.push({
                            dateStr: dateStr,
                            dayName: days[d.getDay()],
                            dayNum: d.getDate()
                        });
                    }
                    this.nextDays = list;
                },

                async selectDate(dateStr) {
                    this.selectedDate = dateStr;
                    this.selectedTime = null;
                    // Removed this.slots = [] to prevent layout collapse (flicker)
                    this.error = null;
                    await this.fetchSlots(dateStr);
                },

                selectTime(time) {
                    this.selectedTime = time;
                },

                async fetchSlots(date) {
                    // Removed this.loading = true to prevent spinner overlay (Instant UI)
                    try {
                        const response = await fetch(`./api/v1/availability?date=${date}`);
                        if (!response.ok) throw new Error('Error al cargar horarios');
                        const data = await response.json();
                        this.slots = data.slots;
                    } catch (e) {
                        this.error = "No se pudieron cargar los horarios.";
                    } finally {
                        // loading is not used here anymore
                    }
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const options = { weekday: 'long', day: 'numeric', month: 'short' };
                    return new Date(dateStr).toLocaleDateString('es-ES', options);
                },

                async submitBooking() {
                    this.loading = true;
                    this.error = null;
                    
                    const payload = {
                        name: this.form.name,
                        phone: this.form.phone,
                        date: this.selectedDate,
                        time: this.selectedTime,
                        service_id: this.selectedService.id
                    };

                    try {
                        const response = await fetch('./api/v1/appointments', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.error || 'Error al reservar');
                        }

                        this.success = true;
                        this.appointmentId = data.appointment_id;
                        this.step = 1; // Reset step so it's ready behind modal
                        
                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.loading = false;
                    }
                },

                reset() {
                    this.step = 1;
                    this.selectedDate = null;
                    this.selectedTime = null;
                    this.form.name = '';
                    this.form.phone = '';
                    this.success = false;
                    this.error = null;
                    this.generateNextDays(); 
                }
            }
        }
    </script>
</body>
</html>