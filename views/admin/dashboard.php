<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .safe-pb { padding-bottom: env(safe-area-inset-bottom); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none;  scrollbar-width: none; }
        
        /* Toast Animation */
        .toast-enter-active, .toast-leave-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        .toast-enter-start, .toast-leave-end { opacity: 0; transform: translateY(20px) scale(0.95); }
        .toast-enter-end, .toast-leave-start { opacity: 1; transform: translateY(0) scale(1); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-900 font-sans antialiased selection:bg-black selection:text-white" x-data="dashboardApp()">

    <!-- Header -->
    <header class="bg-white/90 backdrop-blur-md border-b border-gray-200 sticky top-0 z-30 px-4 py-3 flex justify-between items-center safe-pt">
        <div class="flex items-center space-x-2">
            <div class="bg-black text-white w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs">PE</div>
            <div>
                <h1 class="text-sm font-bold tracking-tight leading-tight">Panel Admin</h1>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wide" x-text="formatDateHeader(filterDate)"></p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
             <!-- Calendar Trigger -->
             <div class="relative">
                <input type="date" x-model="filterDate" @change="onDateChange" class="absolute inset-0 opacity-0 w-8 h-8 cursor-pointer z-10">
                <button class="p-2 text-gray-400 hover:text-black transition-colors rounded-full hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </button>
            </div>
            <button @click="logout" class="p-2 text-gray-400 hover:text-red-600 transition-colors rounded-full hover:bg-red-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>
        </div>
    </header>

    <!-- Date Ribbon (Nav) - Fixed Alignment -->
    <div class="bg-white border-b border-gray-100 sticky top-[57px] z-20">
        <div class="flex justify-between items-center px-2 py-2 overflow-x-auto no-scrollbar snap-x">
            <template x-for="day in dateRibbon" :key="day.dateStr">
                <button @click="setDate(day.dateStr)" 
                        class="flex-shrink-0 flex flex-col items-center justify-center w-[14.28%] min-w-[50px] h-14 rounded-xl transition-all duration-200 snap-center"
                        :class="day.isToday ? 'bg-black text-white' : 'text-gray-400 hover:bg-gray-50'">
                    <span class="text-[9px] uppercase font-bold tracking-wider mb-0.5 opacity-80" x-text="day.dayName"></span>
                    <span class="text-lg font-bold leading-none" x-text="day.dayNum"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Main Content -->
    <main class="p-4 pb-32 max-w-lg mx-auto min-h-[80vh]">
        
        <!-- Summary Header -->
        <div class="flex justify-between items-center mb-6 mt-2">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Agenda</h2>
            <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md" x-text="(appointments.length || 0) + ' Citas'"></span>
        </div>

        <!-- Appointments List -->
        <div class="space-y-3">
            <template x-if="loading">
                <div class="space-y-3">
                    <div class="animate-pulse flex space-x-4 bg-white p-5 rounded-2xl border border-gray-100">
                        <div class="h-4 bg-gray-200 rounded w-12 my-auto"></div>
                        <div class="flex-1 space-y-2 py-1 border-l border-gray-100 pl-4">
                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="!loading && appointments.length === 0">
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="text-gray-900 font-bold">Día Libre</p>
                    <p class="text-sm text-gray-500 mt-1">No hay citas programadas.</p>
                </div>
            </template>

            <template x-for="appt in appointments" :key="appt.id">
                <div @click="openActionSheet(appt)" 
                     class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 active:scale-[0.98] transition-all duration-200 cursor-pointer group hover:shadow-md relative overflow-hidden"
                     :class="{'opacity-60': appt.status === 'cancelled'}"> <!-- Muted if cancelled -->
                    
                    <!-- Status Color Line (Left) -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 transition-colors duration-300"
                         :class="{
                            'bg-gray-900': appt.status === 'booked',
                            'bg-green-500': appt.status === 'attended',
                            'bg-red-500': appt.status === 'cancelled'
                         }"></div>

                    <div class="flex items-center pl-3 py-1">
                        
                        <!-- 1. Time (Naked & Bold) -->
                        <div class="w-14 flex-shrink-0 text-xl font-bold text-gray-900 tracking-tight" x-text="appt.time"></div>
                        
                        <!-- 2. Info -->
                        <div class="flex-grow pl-2 border-l border-gray-100 ml-2 py-1">
                            <h3 class="text-base font-bold text-gray-900 leading-tight" x-text="appt.customer_name"></h3>
                            <p class="text-xs text-gray-500 font-medium mt-0.5" x-text="appt.customer_phone"></p>
                        </div>

                        <!-- 3. Status Badge (Fixed Right Position) -->
                        <div class="mr-3 flex-shrink-0">
                             <span x-show="appt.status !== 'booked'" 
                                  class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded border"
                                  :class="{
                                    'bg-green-50 text-green-700 border-green-100': appt.status === 'attended',
                                    'bg-red-50 text-red-700 border-red-100': appt.status === 'cancelled'
                                  }" x-text="getStatusLabel(appt.status)"></span>
                        </div>
                        
                        <!-- 4. WhatsApp Button -->
                        <a :href="getWhatsAppLink(appt)" @click.stop class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-500 hover:text-white transition-all shadow-sm border border-green-100 flex-shrink-0">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.017-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </main>

    <!-- FAB (Floating Action Button) -->
    <button @click="openCreateSheet" 
            class="fixed bottom-6 right-6 w-14 h-14 bg-black text-white rounded-full shadow-2xl flex items-center justify-center z-40 hover:scale-105 active:scale-95 transition-all duration-300">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
    </button>

    <!-- Create Appointment Sheet -->
    <div x-show="createSheetOpen" class="fixed inset-0 z-50 flex items-end justify-center" x-cloak>
        <div x-show="createSheetOpen" x-transition.opacity @click="createSheetOpen = false" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        
        <div x-show="createSheetOpen" 
             x-transition:enter="transition cubic-bezier(0.32, 0.72, 0, 1) duration-400 transform" 
             x-transition:enter-start="translate-y-full" 
             x-transition:enter-end="translate-y-0" 
             x-transition:leave="transition ease-in duration-200 transform" 
             x-transition:leave-start="translate-y-0" 
             x-transition:leave-end="translate-y-full" 
             class="relative w-full max-w-lg bg-white rounded-t-3xl p-6 pb-10 shadow-2xl safe-pb max-h-[90vh] overflow-y-auto">
            
            <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto mb-6"></div>
            
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-900">Nueva Cita</h2>
                    
                    <!-- Interactive Date Picker -->
                    <div class="relative">
                        <input type="date" x-model="newBooking.date" @change="fetchCreateSlots" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                        <button class="flex items-center space-x-2 bg-gray-100 px-3 py-1.5 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-200 transition-colors">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span x-text="formatDateHeader(newBooking.date)"></span>
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- 1. Service Selection -->
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Servicio</label>
                    <div class="grid grid-cols-1 gap-2">
                        <template x-for="service in services" :key="service.id">
                            <button @click="newBooking.service = service" 
                                    class="text-left px-4 py-3 rounded-xl border transition-all flex justify-between items-center group"
                                    :class="newBooking.service.id === service.id ? 'bg-black text-white border-black' : 'bg-white text-gray-900 border-gray-200 hover:border-gray-400'">
                                <div class="flex items-center">
                                    <span class="text-xl mr-3" x-text="service.icon"></span>
                                    <span class="font-bold text-sm" x-text="service.name"></span>
                                </div>
                                <span class="text-xs font-medium" :class="newBooking.service.id === service.id ? 'text-gray-300' : 'text-gray-500'" x-text="service.duration + ' min'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 2. Time Slots -->
                <div>
                    <div class="flex justify-between items-center mb-3">
                         <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest">Hora</label>
                         <span x-show="loadingSlots" class="text-xs text-gray-400 animate-pulse">Buscando huecos...</span>
                    </div>
                    
                    <div class="grid grid-cols-4 gap-2 max-h-40 overflow-y-auto">
                        <template x-for="slot in createSlots" :key="slot">
                            <button @click="newBooking.time = slot" 
                                    class="py-2 rounded-lg text-sm font-bold border transition-all duration-200"
                                    :class="newBooking.time === slot ? 'bg-black text-white border-black shadow-md' : 'bg-white text-gray-700 border-gray-100 hover:border-black'">
                                <span x-text="slot"></span>
                            </button>
                        </template>
                        
                        <!-- Empty State + Action -->
                        <div x-show="createSlots.length === 0 && !loadingSlots" class="col-span-4 py-6 text-center bg-gray-50 rounded-xl border border-gray-100 border-dashed">
                            <p class="text-sm font-medium text-gray-500 mb-3">Agenda completa para hoy</p>
                            <button @click="advanceDay()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm text-sm font-bold text-black hover:bg-gray-50 transition-colors">
                                <span>Ver huecos de mañana</span>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3. Client Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nombre</label>
                        <input type="text" x-model="newBooking.name" class="w-full p-3 rounded-xl border border-gray-200 focus:border-black outline-none font-bold bg-gray-50 focus:bg-white transition-colors" placeholder="Cliente">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Teléfono</label>
                        <input type="tel" x-model="newBooking.phone" class="w-full p-3 rounded-xl border border-gray-200 focus:border-black outline-none font-bold bg-gray-50 focus:bg-white transition-colors" placeholder="600...">
                    </div>
                </div>

                <!-- Submit -->
                <button @click="submitCreate" 
                        :disabled="!newBooking.service || !newBooking.time || !newBooking.name || !newBooking.phone"
                        class="w-full bg-black text-white py-4 rounded-xl font-bold text-lg shadow-xl hover:shadow-2xl hover:scale-[1.01] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    Crear Reserva
                </button>
            </div>
        </div>
    </div>

    <!-- Super Action Sheet (Existing) -->
    <div x-show="sheetOpen" class="fixed inset-0 z-50 flex items-end justify-center" x-cloak>
        <div x-show="sheetOpen" x-transition.opacity @click="closeSheet" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        
        <div x-show="sheetOpen" x-transition:enter="transition cubic-bezier(0.32, 0.72, 0, 1) duration-400 transform" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" class="relative w-full max-w-lg bg-white rounded-t-3xl p-6 pb-10 shadow-2xl safe-pb max-h-[90vh] overflow-y-auto">
            
            <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto mb-8"></div>

            <template x-if="selectedAppt">
                <div class="space-y-8">
                    
                    <!-- View Mode -->
                    <div x-show="!editing">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900" x-text="selectedAppt.customer_name"></h2>
                                <p class="text-gray-500 font-medium" x-text="selectedAppt.customer_phone"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-extrabold text-gray-900" x-text="selectedAppt.time"></p>
                                <p class="text-xs text-gray-400 uppercase tracking-widest font-bold">Hora</p>
                            </div>
                        </div>

                        <!-- Status Switcher -->
                        <div class="mb-8">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Estado</label>
                            <div class="bg-gray-100 p-1 rounded-xl flex">
                                <button @click="updateStatus(selectedAppt.id, 'booked')" class="flex-1 py-3 rounded-lg text-sm font-bold transition-all" :class="selectedAppt.status === 'booked' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-400'">Pendiente</button>
                                <button @click="updateStatus(selectedAppt.id, 'attended')" class="flex-1 py-3 rounded-lg text-sm font-bold transition-all" :class="selectedAppt.status === 'attended' ? 'bg-green-500 text-white shadow-md' : 'text-gray-400'">Asistido</button>
                                <button @click="updateStatus(selectedAppt.id, 'cancelled')" class="flex-1 py-3 rounded-lg text-sm font-bold transition-all" :class="selectedAppt.status === 'cancelled' ? 'bg-red-500 text-white shadow-md' : 'text-gray-400'">Cancelado</button>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                             <button @click="startEditing()" class="w-full py-4 rounded-xl border-2 border-gray-100 text-gray-900 font-bold hover:bg-gray-50 transition-colors flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Reprogramar Cita
                            </button>
                        </div>
                        
                        <button @click="closeSheet" class="block w-full text-center text-sm text-gray-400 font-medium p-2 hover:text-gray-900 mt-2">Cerrar</button>
                    </div>

                    <!-- Edit Mode (Premium UI) -->
                    <div x-show="editing" class="space-y-6">
                        <div class="flex items-center justify-between">
                             <h3 class="text-lg font-bold">Reprogramar</h3>
                             <button @click="editing = false" class="text-sm font-bold text-gray-400 hover:text-black">Cancelar</button>
                        </div>

                        <!-- 1. Date Scroller -->
                        <div>
                             <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Elige nueva fecha</label>
                             <div class="flex overflow-x-auto no-scrollbar gap-3 pb-4 -mx-6 px-6 snap-x">
                                <template x-for="day in editDays" :key="day.dateStr">
                                    <button @click="selectEditDate(day.dateStr)" 
                                            :class="{'bg-black text-white shadow-lg scale-105': newDate === day.dateStr, 'bg-white text-gray-500 shadow-sm border border-gray-100': newDate !== day.dateStr}"
                                            class="flex-shrink-0 w-16 h-20 rounded-2xl flex flex-col items-center justify-center transition-all duration-300 snap-center active:scale-95">
                                        <span class="text-[10px] uppercase font-bold tracking-widest mb-1 opacity-80" x-text="day.dayName"></span>
                                        <span class="text-2xl font-bold" x-text="day.dayNum"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- 2. Time Slots -->
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest">Horas Disponibles</label>
                                <span x-show="loadingSlots" class="text-xs text-gray-400 animate-pulse">Cargando...</span>
                            </div>
                            
                            <div class="grid grid-cols-4 gap-2 max-h-48 overflow-y-auto">
                                <template x-for="slot in editSlots" :key="slot">
                                    <button @click="newTime = slot" 
                                            class="py-2 rounded-lg text-sm font-bold border transition-all duration-200"
                                            :class="{'bg-black text-white border-black shadow-md': newTime === slot, 'bg-white text-gray-700 border-gray-100 hover:border-gray-300': newTime !== slot}">
                                        <span x-text="slot"></span>
                                    </button>
                                </template>
                                <!-- Show current time if not in list but selected (consistency) -->
                                <template x-if="newTime && !editSlots.includes(newTime)">
                                     <button class="py-2 rounded-lg text-sm font-bold border bg-black text-white border-black shadow-md opacity-80" x-text="newTime"></button>
                                </template>
                            </div>
                            <div x-show="editSlots.length === 0 && !loadingSlots" class="text-center py-4 text-sm text-gray-400 bg-gray-50 rounded-lg">
                                No hay más huecos hoy
                            </div>
                        </div>

                        <button @click="saveReschedule" 
                                :disabled="!newDate || !newTime || loadingSlots"
                                class="w-full bg-black text-white py-4 rounded-xl font-bold text-lg shadow-xl hover:shadow-2xl disabled:opacity-50 disabled:cursor-not-allowed transition-all mt-4">
                            Confirmar Cambios
                        </button>
                    </div>

                </div>
            </template>
        </div>
    </div>

    <!-- Toast Component -->
    <div x-show="toast.visible" 
         x-transition:enter="toast-enter-active" x-transition:enter-start="toast-enter-start" x-transition:enter-end="toast-enter-end"
         x-transition:leave="toast-leave-active" x-transition:leave-start="toast-leave-start" x-transition:leave-end="toast-leave-end"
         class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-[60] flex items-center bg-gray-900 text-white px-6 py-3 rounded-full shadow-2xl space-x-3 min-w-[300px] justify-center" 
         x-cloak>
        <div x-show="toast.type === 'success'" class="text-green-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div x-show="toast.type === 'error'" class="text-red-400">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <span class="font-medium text-sm" x-text="toast.message"></span>
    </div>

    <script>
        function dashboardApp() {
            return {
                filterDate: new Date().toISOString().split('T')[0],
                appointments: [],
                dateRibbon: [],
                loading: false,
                sheetOpen: false,
                selectedAppt: null,
                
                // Edit Mode State
                editing: false,
                newDate: '',
                newTime: '',
                editDays: [],
                editSlots: [],
                loadingSlots: false,

                // Create Mode State
                createSheetOpen: false,
                createSlots: [],
                services: [
                    { id: 1, name: 'Corte de Caballero', duration: 30, icon: '✂️' },
                    { id: 2, name: 'Corte + Barba', duration: 45, icon: '🧔' },
                    { id: 3, name: 'Afeitado Clásico', duration: 30, icon: '🪒' }
                ],
                newBooking: {
                    service: null,
                    date: '', // Added date field
                    time: null,
                    name: '',
                    phone: ''
                },

                // Toast State
                toast: { visible: false, message: '', type: 'success' },

                init() {
                    this.generateRibbon();
                    this.fetchAppointments();
                },

                // ---- Create Logic (FAB) ----
                openCreateSheet() {
                    this.createSheetOpen = true;
                    this.newBooking = { 
                        service: this.services[0], 
                        date: this.filterDate, // Init with current view date
                        time: null, 
                        name: '', 
                        phone: '' 
                    }; 
                    this.fetchCreateSlots();
                },

                async fetchCreateSlots() {
                    this.loadingSlots = true;
                    this.createSlots = [];
                    try {
                        const response = await fetch(`./api/v1/availability?date=${this.newBooking.date}`);
                        const data = await response.json();
                        this.createSlots = data.slots;
                    } catch (e) {
                        this.showToast('Error cargando disponibilidad', 'error');
                    } finally {
                        this.loadingSlots = false;
                    }
                },
                
                advanceDay() {
                    const d = new Date(this.newBooking.date);
                    d.setDate(d.getDate() + 1);
                    this.newBooking.date = d.toISOString().split('T')[0];
                    this.fetchCreateSlots();
                },

                async submitCreate() {
                    try {
                        const payload = {
                            name: this.newBooking.name,
                            phone: this.newBooking.phone,
                            date: this.newBooking.date, // Use internal date, not filterDate
                            time: this.newBooking.time,
                            service_id: this.newBooking.service.id
                        };

                        const response = await fetch('./api/v1/appointments', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();
                        if (!response.ok) throw new Error(data.error || 'Error al crear cita');

                        this.showToast('Cita creada con éxito');
                        this.createSheetOpen = false;
                        this.fetchAppointments(); // Refresh list

                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                // ---- Helpers ----
                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 3000);
                },

                // ---- Date Logic (View) ----
                onDateChange() { this.setDate(this.filterDate); },
                setDate(dateStr) {
                    this.filterDate = dateStr;
                    this.generateRibbon();
                    this.fetchAppointments();
                },
                generateRibbon() {
                    const center = new Date(this.filterDate);
                    const list = [];
                    for (let i = -3; i <= 3; i++) {
                        const d = new Date(center);
                        d.setDate(center.getDate() + i);
                        const dStr = d.toISOString().split('T')[0];
                        list.push({
                            dateStr: dStr,
                            dayName: d.toLocaleDateString('es-ES', { weekday: 'short' }).replace('.',''),
                            dayNum: d.getDate(),
                            isToday: dStr === this.filterDate
                        });
                    }
                    this.dateRibbon = list;
                },
                formatDateHeader(dateStr) {
                    if (!dateStr) return '';
                    return new Date(dateStr).toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                },

                // ---- Data Fetching ----
                async fetchAppointments() {
                    this.loading = true;
                    try {
                        const response = await fetch(`./api/v1/admin/appointments?date=${this.filterDate}`);
                        if (response.ok) {
                            this.appointments = await response.json();
                        } else if (response.status === 401) {
                            window.location.reload();
                        }
                    } catch (e) { console.error(e); } 
                    finally { this.loading = false; }
                },

                // ---- Action Sheet Logic ----
                openActionSheet(appt) {
                    this.selectedAppt = { ...appt }; 
                    this.editing = false; // Reset edit mode
                    this.sheetOpen = true;
                },
                closeSheet() {
                    this.sheetOpen = false;
                    setTimeout(() => { this.selectedAppt = null; this.editing = false; }, 300);
                },

                // ---- Edit Mode Logic (Premium UI) ----
                startEditing() {
                    this.editing = true;
                    this.newDate = this.selectedAppt.date;
                    this.newTime = this.selectedAppt.time;
                    this.generateEditDays();
                    this.selectEditDate(this.newDate);
                },

                generateEditDays() {
                    const days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                    const today = new Date(); // Start from today, not from appointment date (usually)
                    const list = [];
                    for (let i = 0; i < 14; i++) {
                        const d = new Date(today);
                        d.setDate(today.getDate() + i);
                        const dateStr = d.toISOString().split('T')[0];
                        list.push({
                            dateStr: dateStr,
                            dayName: days[d.getDay()],
                            dayNum: d.getDate()
                        });
                    }
                    this.editDays = list;
                },

                async selectEditDate(dateStr) {
                    this.newDate = dateStr;
                    // If changing date, clear time unless it matches exactly the cached one (UX decision: Force pick new time usually)
                    if (dateStr !== this.selectedAppt.date) {
                         this.newTime = null; 
                    } else {
                        this.newTime = this.selectedAppt.time;
                    }
                    
                    this.loadingSlots = true;
                    this.editSlots = [];
                    try {
                        const response = await fetch(`./api/v1/availability?date=${dateStr}`);
                        const data = await response.json();
                        this.editSlots = data.slots;
                    } catch (e) {
                        this.showToast('Error cargando horas', 'error');
                    } finally {
                        this.loadingSlots = false;
                    }
                },

                async saveReschedule() {
                    if (!this.newDate || !this.newTime) return;
                    
                    try {
                        const response = await fetch(`./api/v1/admin/appointments/${this.selectedAppt.id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ date: this.newDate, time: this.newTime })
                        });

                        const data = await response.json();
                        if (!response.ok) throw new Error(data.error);

                        // Success
                        this.showToast('Cita reprogramada correctamente');
                        
                        // Update UI
                        if (this.newDate !== this.filterDate) {
                            this.appointments = this.appointments.filter(a => a.id !== this.selectedAppt.id);
                        } else {
                            const index = this.appointments.findIndex(a => a.id === this.selectedAppt.id);
                            if (index !== -1) {
                                this.appointments[index].date = this.newDate;
                                this.appointments[index].time = this.newTime;
                                this.appointments.sort((a, b) => a.time.localeCompare(b.time));
                            }
                        }
                        this.closeSheet();

                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                // ---- Status Logic ----
                async updateStatus(id, newStatus) {
                    const oldStatus = this.selectedAppt.status;
                    this.selectedAppt.status = newStatus;
                    
                    const index = this.appointments.findIndex(a => a.id === id);
                    if (index !== -1) this.appointments[index].status = newStatus;

                    try {
                        const response = await fetch(`./api/v1/admin/appointments/${id}/status`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ status: newStatus })
                        });
                        if (!response.ok) throw new Error('Failed');
                        // Toast removed for cleaner UX on status change
                    } catch (e) {
                        this.selectedAppt.status = oldStatus;
                        if (index !== -1) this.appointments[index].status = oldStatus;
                        this.showToast('Error de conexión', 'error');
                    }
                },
                
                getStatusLabel(status) {
                    const map = { 'booked': 'Pendiente', 'attended': 'Asistió', 'cancelled': 'Cancelado' };
                    return map[status] || status;
                },
                getWhatsAppLink(appt) {
                    return appt.whatsapp_link || '#';
                },
                async logout() {
                    await fetch('./api/v1/auth/logout', { method: 'POST' });
                    window.location.reload();
                }
            }
        }
    </script>
</body>
</html>