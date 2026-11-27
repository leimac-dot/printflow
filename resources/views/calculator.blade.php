<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrintFlow - Calculadora de Costos 3D</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Token CSRF de Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak] { display: none !important; }
        .num { font-variant-numeric: tabular-nums; }
        .print-only-pages { display: none; }
        @media print {
            @page { margin: 12mm; size: A4 portrait; }
            body { background: #ffffff !important; color: #111827 !important; margin: 0 !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .print-main-grid { display: block !important; }
            .print-aside { width: 100% !important; max-width: 100% !important; }
            .print-sheet {
                position: static !important;
                width: 100% !important;
                max-width: 100% !important;
                background: #ffffff !important;
                color: #111827 !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            .print-only-pages { display: block !important; }
            .print-page {
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                padding: 24px 28px;
                background: #ffffff;
                color: #0f172a;
                min-height: 245mm;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .print-page-break {
                page-break-before: always;
                break-before: page;
            }
            .print-accent-bar {
                height: 5px;
                width: 100%;
                background: linear-gradient(90deg, #ea580c 0%, #f59e0b 55%, #0891b2 100%);
                border-radius: 999px;
                margin-bottom: 16px;
            }
            .print-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px;
            }
            .print-table th {
                background: #f1f5f9;
                color: #334155;
                text-transform: uppercase;
                font-size: 10px;
                letter-spacing: 0.05em;
                padding: 8px 10px;
                border-bottom: 1px solid #cbd5e1;
                text-align: left;
            }
            .print-table td {
                padding: 9px 10px;
                border-bottom: 1px solid #e2e8f0;
                color: #0f172a;
            }
        }
    </style>
</head>
<body class="bg-[#0f1115] text-zinc-100 min-h-screen font-sans antialiased" x-data="printCalculator()">

    <!-- Franja superior de identidad técnica -->
    <div class="h-1 w-full bg-gradient-to-r from-orange-500 via-amber-500 to-cyan-500 no-print"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 print:p-3">
        <!-- Encabezado con personalidad de taller/ingeniería -->
        <header class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 pb-5 mb-6 border-b border-zinc-800/90 no-print">
            <div>
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-6 rounded-xs bg-orange-500 inline-block"></span>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white" x-text="businessName || 'PrintFlow'">PrintFlow</h1>
                    <span class="text-[10px] uppercase tracking-widest font-semibold px-2.5 py-0.5 rounded border border-cyan-500/30 bg-cyan-950/40 text-cyan-300">
                        3D Cost Engine
                    </span>
                </div>
                <p class="text-xs text-zinc-400 mt-1 pl-5">Control de costos directos, márgenes comerciales y cotizaciones de impresión 3D</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @auth
                    <div class="flex items-center gap-2 bg-[#161920] border border-zinc-800 px-3 py-1.5 rounded text-xs">
                        <span class="text-zinc-200 font-medium">{{ auth()->user()->name }}</span>
                        <span class="text-zinc-600">|</span>
                        <span class="uppercase text-[10px] font-bold tracking-wider text-orange-400">{{ auth()->user()->role }}</span>
                    </div>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="bg-[#161920] hover:bg-zinc-800 border border-zinc-700 text-zinc-300 text-xs font-medium px-3.5 py-2 rounded transition">
                            Cerrar sesión
                        </button>
                    </form>
                @else
                    <button type="button" @click="authModal = true; authTab = 'login'" class="bg-[#161920] hover:bg-zinc-800 border border-zinc-700 text-zinc-200 text-xs font-medium px-3.5 py-2 rounded transition">
                        Iniciar sesión
                    </button>
                    <button type="button" @click="authModal = true; authTab = 'register'" class="bg-orange-600 hover:bg-orange-500 text-white text-xs font-semibold px-4 py-2 rounded transition">
                        Crear cuenta
                    </button>
                @endauth
            </div>
        </header>

        <!-- Notificación dinámica única -->
        <div x-show="statusBanner.message" x-cloak x-transition
            :class="statusBanner.type === 'error'
                ? 'bg-rose-950/60 border-rose-700/80 text-rose-200'
                : 'bg-[#161920] border-l-4 border-l-cyan-400 border-y border-r border-zinc-800 text-zinc-100'"
            class="mb-5 px-4 py-3 rounded text-xs sm:text-sm flex items-center justify-between gap-3 no-print">
            <span x-text="statusBanner.message"></span>
            <button type="button" @click="statusBanner.message = ''" class="text-xs text-zinc-400 hover:text-white px-1">Cerrar</button>
        </div>

        <!-- Mensajes de sesión / validación -->
        @if(session('success'))
            <div x-show="!statusBanner.message" class="mb-5 bg-[#161920] border-l-4 border-l-cyan-400 border-y border-r border-zinc-800 text-zinc-100 px-4 py-3 rounded text-xs sm:text-sm no-print">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 bg-rose-950/60 border border-rose-700/80 text-rose-200 px-4 py-3 rounded text-xs sm:text-sm no-print">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Modal Cuenta / Iniciar Sesión -->
        <div x-show="authModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 no-print">
            <div @click.away="authModal = false" class="bg-[#161920] border border-zinc-700 rounded-lg max-w-md w-full p-6">
                <div class="flex justify-between items-center border-b border-zinc-800 pb-3 mb-4">
                    <div class="flex gap-2">
                        <button type="button" @click="authTab = 'login'" :class="authTab === 'login' ? 'bg-orange-600 text-white' : 'bg-[#0f1115] text-zinc-400 border border-zinc-800'" class="px-3 py-1.5 rounded text-xs font-semibold transition">
                            Iniciar sesión
                        </button>
                        <button type="button" @click="authTab = 'register'" :class="authTab === 'register' ? 'bg-orange-600 text-white' : 'bg-[#0f1115] text-zinc-400 border border-zinc-800'" class="px-3 py-1.5 rounded text-xs font-semibold transition">
                            Crear cuenta
                        </button>
                    </div>
                    <button type="button" @click="authModal = false" class="text-xs text-zinc-400 hover:text-white">Cerrar</button>
                </div>

                <!-- Login -->
                <form x-show="authTab === 'login'" method="POST" action="/login" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Correo electrónico</label>
                        <input type="email" name="email" required class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Contraseña</label>
                        <input type="password" name="password" required class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                    </div>
                    <button type="submit" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-semibold py-2.5 rounded text-sm transition">
                        Entrar
                    </button>
                </form>

                <!-- Registro -->
                <form x-show="authTab === 'register'" method="POST" action="/register" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Nombre completo</label>
                        <input type="text" name="name" required class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Correo electrónico</label>
                        <input type="email" name="email" required class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Contraseña (mín. 6 caracteres)</label>
                        <input type="password" name="password" required minlength="6" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" required minlength="6" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                    </div>
                    <button type="submit" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-semibold py-2.5 rounded text-sm transition">
                        Registrarme
                    </button>
                </form>
            </div>
        </div>

        <!-- Barra de pestañas con acento naranja -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6 bg-[#161920] border border-zinc-800 rounded-lg p-1.5 no-print">
            <div class="flex flex-wrap gap-1">
                <button type="button" @click="activeTab = 'quick'"
                    :class="activeTab === 'quick' ? 'bg-orange-600 text-white' : 'bg-transparent text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800/60'"
                    class="px-3.5 py-2 rounded text-xs sm:text-sm font-semibold transition">
                    Cotización rápida
                </button>
                <button type="button" @click="activeTab = 'advanced'"
                    :class="activeTab === 'advanced' ? 'bg-orange-600 text-white' : 'bg-transparent text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800/60'"
                    class="px-3.5 py-2 rounded text-xs sm:text-sm font-semibold transition">
                    Costos, márgenes y reglas
                </button>
                <button type="button" @click="activeTab = 'quotes'"
                    :class="activeTab === 'quotes' ? 'bg-orange-600 text-white' : 'bg-transparent text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800/60'"
                    class="px-3.5 py-2 rounded text-xs sm:text-sm font-semibold transition flex items-center gap-2">
                    <span x-text="isAdmin ? 'Metas y cotizaciones' : 'Mis cotizaciones'"></span>
                    <span x-show="!isAdmin && pendingSentCount > 0"
                        class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-400 text-zinc-950 num"
                        x-text="pendingSentCount"></span>
                </button>
            </div>

            <div x-show="selectedModelId" class="flex items-center gap-2 bg-[#0f1115] border border-cyan-500/30 rounded px-3 py-1.5 text-xs">
                <span class="text-zinc-400">Modelo: <strong class="text-cyan-300 font-semibold" x-text="selectedModelName"></strong></span>
                <button type="button" @click="selectedModelId = null; selectedModelName = '';" class="text-zinc-400 hover:text-white ml-1">Quitar</button>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="print-main-grid grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <!-- Columna principal (2/3) -->
            <div class="lg:col-span-2 space-y-6 no-print">

                <!-- ========================================================= -->
                <!-- PESTAÑA 1: COTIZACIÓN RÁPIDA                                -->
                <!-- ========================================================= -->
                <div x-show="activeTab === 'quick'" class="space-y-6">

                    <!-- Catálogo de Modelos -->
                    <section class="bg-[#161920] border border-zinc-800 rounded-lg p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-zinc-800/80">
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-widest text-orange-400">Catálogo de modelos</h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Selecciona una pieza para cargar gramos y tiempo de impresión.</p>
                            </div>
                            <template x-if="isAdmin">
                                <button type="button" @click="toggleNewModelForm()" class="bg-[#0f1115] hover:bg-zinc-800 border border-orange-500/40 text-orange-300 text-xs font-semibold px-3 py-1.5 rounded transition shrink-0">
                                    <span x-text="showModelForm ? 'Cerrar formulario' : 'Nuevo modelo'"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Alta de modelo -->
                        <form x-show="isAdmin && showModelForm" @submit.prevent="saveModel()" class="bg-[#0f1115] border border-zinc-800 rounded p-4 sm:p-5 mb-5 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Nombre del modelo</label>
                                    <input type="text" required x-model="newModel.name" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none" placeholder="Ej. Soporte articulado">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Imagen del modelo</label>
                                    <input type="file" accept="image/*" @change="onModelImageChange($event)" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-1.5 text-xs text-zinc-300 file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-orange-600 file:text-white hover:file:bg-orange-500">
                                    <div x-show="newModelPreview" class="mt-2 flex items-center gap-2.5">
                                        <img :src="newModelPreview" alt="Vista previa" class="w-12 h-12 rounded object-cover border border-zinc-700">
                                        <span class="text-[11px] text-zinc-400">Vista previa seleccionada</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Descripción</label>
                                <textarea rows="2" x-model="newModel.description" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none" placeholder="Notas técnicas o acabado"></textarea>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Gramos</label>
                                    <input type="number" min="0" required x-model.number="newModel.filament_grams" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Horas</label>
                                    <input type="number" min="0" required x-model.number="newModel.print_hours" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Minutos</label>
                                    <input type="number" min="0" max="59" required x-model.number="newModel.print_minutes" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" :disabled="savingModel" class="bg-orange-600 hover:bg-orange-500 text-white text-xs font-semibold px-4 py-2 rounded transition disabled:opacity-50">
                                    <span x-show="!savingModel">Guardar en catálogo</span>
                                    <span x-show="savingModel">Guardando...</span>
                                </button>
                            </div>
                        </form>

                        <!-- Edición de modelo -->
                        <form x-show="isAdmin && editingModel" @submit.prevent="updateModel()" class="bg-[#0f1115] border border-amber-500/40 rounded p-4 sm:p-5 mb-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400">Editar modelo</h3>
                                <button type="button" @click="cancelEditModel()" class="text-xs text-zinc-400 hover:text-white">Cancelar</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Nombre del modelo</label>
                                    <input type="text" required x-model="editForm.name" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-amber-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Cambiar imagen (opcional)</label>
                                    <input type="file" accept="image/*" @change="onEditModelImageChange($event)" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-1.5 text-xs text-zinc-300 file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-500">
                                    <div x-show="editModelPreview" class="mt-2 flex items-center gap-2.5">
                                        <img :src="editModelPreview" alt="Vista previa" class="w-12 h-12 rounded object-cover border border-zinc-700">
                                        <span class="text-[11px] text-amber-300">Nueva imagen lista</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Descripción</label>
                                <textarea rows="2" x-model="editForm.description" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-amber-500 focus:outline-none"></textarea>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Gramos</label>
                                    <input type="number" min="0" required x-model.number="editForm.filament_grams" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-amber-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Horas</label>
                                    <input type="number" min="0" required x-model.number="editForm.print_hours" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-amber-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Minutos</label>
                                    <input type="number" min="0" max="59" required x-model.number="editForm.print_minutes" class="w-full bg-[#161920] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-amber-500 focus:outline-none">
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="cancelEditModel()" class="bg-[#161920] hover:bg-zinc-800 border border-zinc-700 text-zinc-300 text-xs font-medium px-3.5 py-2 rounded transition">
                                    Cancelar
                                </button>
                                <button type="submit" :disabled="updatingModel" class="bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold px-4 py-2 rounded transition disabled:opacity-50">
                                    <span x-show="!updatingModel">Guardar cambios</span>
                                    <span x-show="updatingModel">Guardando...</span>
                                </button>
                            </div>
                        </form>

                        <template x-if="models.length === 0">
                            <p class="text-xs text-zinc-400 bg-[#0f1115] border border-zinc-800 rounded p-4 text-center">
                                Sin modelos registrados en el catálogo.
                            </p>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" x-show="models.length > 0">
                            <template x-for="item in models" :key="item.id">
                                <div :class="selectedModelId === item.id ? 'border-orange-500' : 'border-zinc-800 hover:border-zinc-700'"
                                    class="bg-[#0f1115] border rounded overflow-hidden flex flex-col justify-between transition">
                                    <div>
                                        <div class="h-32 bg-zinc-950 border-b border-zinc-800/80 flex items-center justify-center overflow-hidden">
                                            <template x-if="item.image_path">
                                                <img :src="'/storage/' + item.image_path + '?v=' + (item.updated_at || '')" :alt="item.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!item.image_path">
                                                <span class="text-xs uppercase tracking-wider text-zinc-600">Sin imagen</span>
                                            </template>
                                        </div>
                                        <div class="p-3.5">
                                            <h3 class="font-semibold text-white text-sm truncate" x-text="item.name"></h3>
                                            <p class="text-xs text-zinc-400 mt-1 line-clamp-2" x-text="item.description || 'Sin descripción'"></p>
                                            <div class="flex items-center gap-2 mt-2.5 text-[11px] num border-t border-zinc-800/70 pt-2">
                                                <span class="text-cyan-300 font-medium" x-text="item.filament_grams + ' g'"></span>
                                                <span class="text-zinc-600">/</span>
                                                <span class="text-orange-300 font-medium" x-text="item.print_hours + 'h ' + item.print_minutes + 'm'"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-3.5 pb-3.5 space-y-1.5">
                                        <button type="button" @click="selectModel(item)"
                                            :class="selectedModelId === item.id ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/50' : 'bg-orange-600 hover:bg-orange-500 text-white'"
                                            class="w-full text-xs font-semibold py-2 rounded transition">
                                            <span x-text="selectedModelId === item.id ? 'Seleccionado' : 'Cotizar modelo'"></span>
                                        </button>
                                        <div class="grid grid-cols-2 gap-1.5" x-show="isAdmin">
                                            <button type="button" @click="startEditModel(item)" class="bg-[#161920] hover:bg-zinc-800 border border-zinc-800 text-amber-300 text-[11px] font-medium py-1.5 rounded transition">
                                                Editar
                                            </button>
                                            <button type="button" @click="deleteModel(item)" class="bg-[#161920] hover:bg-rose-950/50 border border-zinc-800 text-rose-300 text-[11px] font-medium py-1.5 rounded transition">
                                                Borrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>

                    <!-- Proyecto y Tiempo -->
                    <section class="bg-[#161920] border border-zinc-800 rounded-lg p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4 pb-2 border-b border-zinc-800/80">
                            <h2 class="text-xs font-bold uppercase tracking-widest text-orange-400">Proyecto, divisa y tiempo de impresión</h2>
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                <span class="text-zinc-400">Preajuste rápido:</span>
                                <button type="button" @click="applyMaterialPreset('pla')" class="bg-[#0f1115] hover:bg-zinc-800 border border-zinc-700 text-cyan-300 px-2 py-0.5 rounded transition">PLA</button>
                                <button type="button" @click="applyMaterialPreset('petg')" class="bg-[#0f1115] hover:bg-zinc-800 border border-zinc-700 text-orange-300 px-2 py-0.5 rounded transition">PETG</button>
                                <button type="button" @click="applyMaterialPreset('tpu')" class="bg-[#0f1115] hover:bg-zinc-800 border border-zinc-700 text-amber-300 px-2 py-0.5 rounded transition">TPU</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Negocio / Taller</label>
                                <input type="text" x-model="businessName" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Nombre del proyecto</label>
                                <input type="text" x-model="projectName" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Correo del cliente</label>
                                <input type="email" x-model="clientEmail" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Divisa</label>
                                <select x-model="currency" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                                    <option value="MXN">MXN - México ($)</option>
                                    <option value="USD">USD - Dólar ($)</option>
                                    <option value="EUR">EUR - Euro (€)</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Horas</label>
                                <input type="number" min="0" step="1" x-model.number="printHours" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Minutos</label>
                                <input type="number" min="0" max="59" step="1" x-model.number="printMinutes" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Cantidad (piezas)</label>
                                <input type="number" min="1" step="1" x-model.number="quantity" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div class="bg-[#0f1115] border border-cyan-500/30 rounded px-3 py-2 flex flex-col justify-center">
                                <span class="text-[10px] text-zinc-400 uppercase tracking-wider">Tiempo total</span>
                                <span class="text-sm font-bold text-cyan-300 num mt-0.5" x-text="totalPrintHours.toFixed(2) + ' hrs'">5.00 hrs</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Nota rápida / seguimiento</label>
                            <input type="text" x-model="quickNote" placeholder="Ej. Enviar audio con detalles de entrega o tolerancias..." class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                        </div>
                    </section>

                    <!-- Material e Impresora -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <section class="bg-[#161920] border border-zinc-800 rounded-lg p-5">
                            <h2 class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Material (filamento)</h2>
                            <p class="text-xs text-zinc-400 mb-4 pb-2 border-b border-zinc-800/80">Peso por carrete, gramos, merma y multicolor.</p>
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">$/Carrete</label>
                                        <input type="number" step="0.01" x-model.number="filamentPricePerKg" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-cyan-400 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">g/Carrete</label>
                                        <input type="number" step="50" min="50" x-model.number="spoolGrams" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-cyan-400 focus:outline-none">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Gramos</label>
                                        <input type="number" step="1" x-model.number="filamentGrams" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-cyan-400 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Merma base (%)</label>
                                        <input type="number" step="1" min="0" max="100" x-model.number="filamentWasteRate" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-cyan-400 focus:outline-none">
                                    </div>
                                </div>

                                <!-- Impresión multicolor -->
                                <div class="bg-[#0f1115] border border-zinc-800 rounded p-3 space-y-2">
                                    <label class="flex items-center justify-between cursor-pointer text-xs text-zinc-200">
                                        <span class="font-semibold">Impresión multicolor</span>
                                        <input type="checkbox" x-model="multicolorEnabled" class="rounded border-zinc-700 bg-[#161920] text-orange-600 focus:ring-0">
                                    </label>
                                    <div x-show="multicolorEnabled" class="pt-1">
                                        <label class="block text-[10px] text-zinc-400 uppercase mb-1">Merma / purgas multicolor (%)</label>
                                        <input type="number" min="0" max="200" step="1" x-model.number="multicolorWasteRate" class="w-full bg-[#161920] border border-zinc-700 rounded px-2.5 py-1.5 text-xs text-white num focus:border-cyan-400 focus:outline-none">
                                    </div>
                                </div>

                                <div class="bg-[#0f1115] border border-zinc-800 rounded px-3 py-2 flex items-center justify-between text-[11px] num">
                                    <span class="text-zinc-400">Gramos efectivos:</span>
                                    <span class="text-cyan-300 font-semibold" x-text="effectiveFilamentGrams.toFixed(1) + ' g'">75.0 g</span>
                                </div>
                            </div>
                        </section>

                        <section class="bg-[#161920] border border-zinc-800 rounded-lg p-5">
                            <h2 class="text-xs font-bold uppercase tracking-widest text-orange-400 mb-1">Impresora y electricidad</h2>
                            <p class="text-xs text-zinc-400 mb-4 pb-2 border-b border-zinc-800/80">Consumo energético y desgaste por hora.</p>
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Potencia (Watts)</label>
                                        <input type="number" x-model.number="printerWatts" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Tarifa kWh</label>
                                        <input type="number" step="0.01" x-model.number="electricityCostPerKwh" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                                    </div>
                                </div>
                                <div class="bg-[#0f1115] border border-zinc-800 rounded px-3 py-2 flex items-center justify-between text-[11px] num">
                                    <span class="text-zinc-400">Consumo energía:</span>
                                    <span class="text-cyan-300 font-semibold" x-text="energyKwh.toFixed(3) + ' kWh'">0.600 kWh</span>
                                </div>

                                <!-- Estimador rápido de depreciación por vida útil -->
                                <div class="bg-[#0f1115] border border-zinc-800 rounded p-3 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Depreciación por vida útil</span>
                                        <span class="text-[11px] font-semibold text-cyan-300 num" x-text="formatCurrency(estimatedWearPerHour) + '/h'">$0.00/h</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-[10px] text-zinc-500 uppercase mb-1">Valor ($)</label>
                                            <input type="number" step="100" min="0" x-model.number="printerPrice" class="w-full bg-[#161920] border border-zinc-700 rounded px-2 py-1.5 text-xs text-white num focus:border-cyan-400 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-zinc-500 uppercase mb-1">Vida (hrs)</label>
                                            <input type="number" step="100" min="1" x-model.number="printerLifespanHours" class="w-full bg-[#161920] border border-zinc-700 rounded px-2 py-1.5 text-xs text-white num focus:border-cyan-400 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-zinc-500 uppercase mb-1">Desgaste/h</label>
                                            <input type="number" step="0.01" x-model.number="wearCostPerHour" class="w-full bg-[#161920] border border-zinc-700 rounded px-2 py-1.5 text-xs text-white num focus:border-orange-500 focus:outline-none">
                                        </div>
                                    </div>
                                    <button type="button" @click="applyEstimatedWear()" class="w-full bg-[#161920] hover:bg-zinc-800 border border-cyan-500/30 text-cyan-300 text-[11px] font-semibold py-1.5 rounded transition">
                                        Aplicar desgaste calculado
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- PESTAÑA 2: COSTOS OPERATIVOS, MÁRGENES Y REGLAS           -->
                <!-- ========================================================= -->
                <div x-show="activeTab === 'advanced'" class="space-y-6">

                    <!-- Mano de obra detallada + Trabajador extra -->
                    <section class="bg-[#161920] border border-zinc-800 rounded-lg p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4 pb-2 border-b border-zinc-800/80">
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-widest text-orange-400">Mano de obra y equipo</h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Post-procesado, empaque, soporte al cliente y trabajador extra.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-[11px] text-zinc-400 uppercase">Unidad:</label>
                                <select x-model="laborUnit" class="bg-[#0f1115] border border-zinc-700 rounded px-2.5 py-1 text-xs text-white focus:border-orange-500 focus:outline-none">
                                    <option value="min">Minutos</option>
                                    <option value="hrs">Horas</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Tarifa / hr</label>
                                <input type="number" step="0.01" x-model.number="laborRatePerHour" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-cyan-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1" x-text="laborUnit === 'hrs' ? 'Post-procesado (hrs)' : 'Post-procesado (min)'"></label>
                                <input type="number" step="any" min="0" x-model.number="prepMinutes" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1" x-text="laborUnit === 'hrs' ? 'Empaque (hrs)' : 'Empaque (min)'"></label>
                                <input type="number" step="any" min="0" x-model.number="handlingMinutes" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1" x-text="laborUnit === 'hrs' ? 'Soporte cliente (hrs)' : 'Soporte cliente (min)'"></label>
                                <input type="number" step="any" min="0" x-model.number="supportMinutes" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Base de mano de obra</label>
                                <select x-model="laborBasis" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-cyan-400 focus:outline-none">
                                    <option value="manual">Tiempo manual (post-proceso + empaque + soporte)</option>
                                    <option value="total">Tiempo total (impresión + tiempos manuales)</option>
                                </select>
                            </div>
                            <div class="bg-[#0f1115] border border-zinc-800 rounded px-3.5 py-2 flex items-center justify-between text-xs num">
                                <span class="text-zinc-400">Tiempo manual: <strong class="text-cyan-300" x-text="manualLaborMinutesDisplay + ' min'"></strong></span>
                                <span class="text-white font-semibold" x-text="formatCurrency(manualLaborCost)">$0.00</span>
                            </div>
                        </div>

                        <!-- Trabajador extra -->
                        <div class="bg-[#0f1115] border border-zinc-800 rounded p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-cyan-300">Trabajador extra</span>
                                <button type="button" @click="toggleExtraWorker()" class="bg-[#161920] hover:bg-zinc-800 border border-cyan-500/30 text-cyan-300 text-xs font-semibold px-3 py-1 rounded transition">
                                    <span x-text="extraWorker.enabled ? 'Quitar trabajador extra' : 'Agregar trabajador extra'"></span>
                                </button>
                            </div>
                            <div x-show="extraWorker.enabled" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                <div>
                                    <label class="block text-[10px] text-zinc-400 uppercase mb-1">Nombre / rol</label>
                                    <input type="text" x-model="extraWorker.name" placeholder="Ej. Acabados / pintura" class="w-full bg-[#161920] border border-zinc-700 rounded px-2.5 py-1.5 text-xs text-white focus:border-cyan-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-zinc-400 uppercase mb-1">Tarifa ($/hr)</label>
                                    <input type="number" step="0.01" min="0" x-model.number="extraWorker.ratePerHour" class="w-full bg-[#161920] border border-zinc-700 rounded px-2.5 py-1.5 text-xs text-white num focus:border-cyan-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-zinc-400 uppercase mb-1" x-text="laborUnit === 'hrs' ? 'Tiempo (hrs)' : 'Tiempo (min)'"></label>
                                    <input type="number" step="any" min="0" x-model.number="extraWorker.timeValue" class="w-full bg-[#161920] border border-zinc-700 rounded px-2.5 py-1.5 text-xs text-white num focus:border-cyan-400 focus:outline-none">
                                </div>
                            </div>
                            <div x-show="extraWorker.enabled" class="flex justify-between text-xs num border-t border-zinc-800/80 pt-2">
                                <span class="text-zinc-400" x-text="'Subtotal trabajador extra (' + (extraWorker.name || 'Apoyo') + '):'"></span>
                                <span class="text-orange-300 font-semibold" x-text="formatCurrency(extraWorkerCost)">$0.00</span>
                            </div>
                        </div>
                    </section>

                    <section class="bg-[#161920] border border-zinc-800 rounded-lg p-5 sm:p-6">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-orange-400 mb-1">Costos operativos, envío y otros costos</h2>
                        <p class="text-xs text-zinc-400 mb-4 pb-2 border-b border-zinc-800/80">Diseño, empaque, gastos fijos, envío y conceptos extra.</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Costo de diseño</label>
                                <input type="number" step="0.01" x-model.number="designCost" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Empaque y caja</label>
                                <input type="number" step="0.01" x-model.number="packagingCost" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Costo de envío</label>
                                <input type="number" step="1" min="0" x-model.number="shippingCost" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Gastos fijos mensuales</label>
                                <input type="number" step="50" min="0" x-model.number="monthlyFixedCost" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Piezas estimadas / mes</label>
                                <input type="number" step="1" min="1" x-model.number="monthlyEstimatedPieces" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Margen de fallos (%)</label>
                                <input type="number" step="0.5" min="0" max="100" x-model.number="failureMarginRate" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">¿Quién paga / cómo se trata el envío?</label>
                                <select x-model="shippingMode" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                                    <option value="reference">Solo referencia (no suma al total)</option>
                                    <option value="included">Incluido en el total a cobrar</option>
                                </select>
                            </div>
                        </div>

                        <!-- Otros costos: lista dinámica con Agregar -->
                        <div class="bg-[#0f1115] border border-zinc-800 rounded p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-cyan-300">Otros costos (tornillos, insertos, imanes)</span>
                                <span class="text-xs font-semibold text-white num" x-text="formatCurrency(suppliesAmount)">$0.00</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-6 gap-2">
                                <input type="text" x-model="newExtraItem.label" placeholder="Concepto (ej. Insertos M3)" class="sm:col-span-3 bg-[#161920] border border-zinc-700 rounded px-3 py-1.5 text-xs text-white focus:border-cyan-400 focus:outline-none">
                                <input type="number" step="0.01" min="0" x-model.number="newExtraItem.amount" placeholder="Monto" class="sm:col-span-2 bg-[#161920] border border-zinc-700 rounded px-3 py-1.5 text-xs text-white num focus:border-cyan-400 focus:outline-none">
                                <button type="button" @click="addExtraCostItem()" class="bg-orange-600 hover:bg-orange-500 text-white text-xs font-semibold py-1.5 px-3 rounded transition">
                                    Agregar
                                </button>
                            </div>
                            <div class="space-y-1.5" x-show="extraCostItems.length > 0">
                                <template x-for="(item, index) in extraCostItems" :key="index">
                                    <div class="flex items-center justify-between bg-[#161920] border border-zinc-800 rounded px-3 py-1.5 text-xs num">
                                        <span class="text-zinc-200" x-text="item.label"></span>
                                        <div class="flex items-center gap-3">
                                            <span class="text-cyan-300 font-semibold" x-text="formatCurrency(item.amount)"></span>
                                            <button type="button" @click="removeExtraCostItem(index)" class="text-rose-400 hover:text-rose-300 text-[11px]">Quitar</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </section>

                    <section class="bg-[#161920] border border-zinc-800 rounded-lg p-5 sm:p-6">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-4 pb-2 border-b border-zinc-800/80">Márgenes y comercialización</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Tipo de cliente</label>
                                <select x-model="clientType" @change="applyClientPreset()" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-cyan-400 focus:outline-none">
                                    <option value="normal">Normal (40%)</option>
                                    <option value="wholesale">Mayoreo (25%)</option>
                                    <option value="school">Escuela (20%)</option>
                                    <option value="friends">Amigos (15%)</option>
                                    <option value="custom">Personalizado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Margen de ganancia (%)</label>
                                <input type="number" x-model.number="profitMargin" @input="clientType = 'custom'" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-cyan-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Impuestos (%)</label>
                                <input type="number" step="0.1" min="0" x-model.number="taxRate" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-cyan-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Comisión plataforma (%)</label>
                                <input type="number" step="0.1" min="0" x-model.number="platformFeeRate" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-cyan-400 focus:outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Recargo adicional (%)</label>
                                <input type="number" step="1" min="0" max="200" x-model.number="marketplaceExtraRate" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-cyan-400 focus:outline-none">
                            </div>
                        </div>
                        <p class="text-xs text-amber-400 mt-3" x-show="(Number(profitMargin) || 0) < 20">
                            Margen menor al 20%: considera si cubre imprevistos operativos.
                        </p>
                    </section>

                    <section class="bg-[#161920] border border-zinc-800 rounded-lg p-5 sm:p-6">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-orange-400 mb-4 pb-2 border-b border-zinc-800/80">Reglas de precio y volumen</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Precio mínimo</label>
                                <input type="number" step="1" min="0" x-model.number="minPrice" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Redondeo unitario</label>
                                <select x-model.number="roundStep" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                                    <option value="0">Sin redondeo</option>
                                    <option value="5">Múltiplos de 5</option>
                                    <option value="10">Múltiplos de 10</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Desde (piezas)</label>
                                <input type="number" min="1" step="1" x-model.number="volumeMinQty" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Descuento (%)</label>
                                <input type="number" min="0" max="90" step="1" x-model.number="volumeDiscountRate" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-orange-500 focus:outline-none">
                            </div>
                        </div>
                        <p class="text-xs text-cyan-300 mt-3" x-show="volumeDiscountApplied">
                            Descuento por volumen aplicado (<span x-text="volumeDiscountRate"></span>%) para <span x-text="quantity"></span> piezas.
                        </p>
                    </section>
                </div>

                <!-- ========================================================= -->
                <!-- PESTAÑA 3: METAS Y COTIZACIONES                           -->
                <!-- ========================================================= -->
                <div x-show="activeTab === 'quotes'" class="space-y-6">

                    <section class="bg-[#161920] border border-zinc-800 rounded-lg p-5 sm:p-6">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-4 pb-2 border-b border-zinc-800/80">Metas mensuales de ganancia</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Meta mensual</label>
                                <input type="number" step="100" min="0" x-model.number="monthlyGoal" class="w-full bg-[#0f1115] border border-zinc-700 rounded px-3 py-2 text-sm text-white num focus:border-cyan-400 focus:outline-none">
                            </div>
                            <div class="bg-[#0f1115] border border-zinc-800 rounded p-3">
                                <span class="block text-[10px] uppercase tracking-wider text-zinc-400">Piezas necesarias</span>
                                <span class="text-lg font-bold text-white num" x-text="monthlyUnitsNeeded">0</span>
                            </div>
                            <div class="bg-[#0f1115] border border-zinc-800 rounded p-3">
                                <span class="block text-[10px] uppercase tracking-wider text-zinc-400">Filamento estimado</span>
                                <span class="text-lg font-bold text-cyan-300 num" x-text="monthlyFilamentKg.toFixed(2) + ' kg'">0.00 kg</span>
                            </div>
                            <div class="bg-[#0f1115] border border-zinc-800 rounded p-3">
                                <span class="block text-[10px] uppercase tracking-wider text-zinc-400">Inversión material</span>
                                <span class="text-lg font-bold text-orange-400 num" x-text="formatCurrency(monthlyMaterialInvestment)">$0.00</span>
                            </div>
                        </div>
                        <p x-show="goalAlertText" x-text="goalAlertText" class="text-xs text-amber-300 bg-[#0f1115] border border-amber-500/30 rounded px-3 py-2 mt-3"></p>
                    </section>

                    <section class="bg-[#161920] border border-zinc-800 rounded-lg p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-2 border-b border-zinc-800/80">
                            <h2 class="text-xs font-bold uppercase tracking-widest text-orange-400" x-text="isAdmin ? 'Historial de cotizaciones' : 'Mis cotizaciones'"></h2>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" x-show="!isAdmin && pendingSentCount > 0"
                                    @click="onlySentFilter = !onlySentFilter"
                                    :class="onlySentFilter ? 'bg-cyan-500 text-zinc-950 border-cyan-400 font-bold' : 'bg-[#0f1115] text-cyan-300 border-cyan-500/30'"
                                    class="border text-xs font-semibold px-3 py-1.5 rounded transition">
                                    <span x-text="onlySentFilter ? 'Mostrar todas' : 'Pendientes (' + pendingSentCount + ')'"></span>
                                </button>
                                <input type="text" x-model="searchQuery" placeholder="Buscar proyecto o modelo..." class="bg-[#0f1115] border border-zinc-700 rounded px-3 py-1.5 text-xs sm:text-sm text-white focus:border-orange-500 focus:outline-none">
                            </div>
                        </div>

                        <div x-show="!isAdmin && pendingSentCount > 0" class="mb-4 bg-[#0f1115] border-l-4 border-l-cyan-400 border-y border-r border-zinc-800 text-zinc-300 rounded px-4 py-2.5 text-xs">
                            Tienes <strong class="text-white num" x-text="pendingSentCount"></strong> cotización(es) en estado <strong class="text-cyan-300">Enviada</strong> pendientes de aceptar.
                        </div>

                        <div class="overflow-x-auto border border-zinc-800 rounded">
                            <table class="w-full text-left text-sm text-zinc-300">
                                <thead class="bg-[#0f1115] text-[11px] uppercase tracking-wider text-zinc-400 border-b border-zinc-800">
                                    <tr>
                                        <th class="px-4 py-2.5">Proyecto / Modelo</th>
                                        <th class="px-4 py-2.5">Estado</th>
                                        <th class="px-4 py-2.5">Total</th>
                                        <th class="px-4 py-2.5">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800/80">
                                    <template x-if="filteredQuotes.length === 0">
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-zinc-500 text-xs">No hay cotizaciones registradas.</td>
                                        </tr>
                                    </template>
                                    <template x-for="quote in filteredQuotes" :key="quote.id">
                                        <tr class="hover:bg-[#0f1115]/70 transition">
                                            <td class="px-4 py-3">
                                                <div class="flex items-start gap-3">
                                                    <template x-if="quote.print_model && quote.print_model.image_path">
                                                        <img :src="'/storage/' + quote.print_model.image_path + '?v=' + (quote.print_model.updated_at || '')" :alt="quote.print_model.name" class="w-10 h-10 rounded object-cover border border-zinc-700 shrink-0 mt-0.5">
                                                    </template>
                                                    <div>
                                                        <div class="font-semibold text-white" x-text="quote.project_name"></div>
                                                        <div class="text-xs text-zinc-400 num" x-text="quoteDetailsText(quote)"></div>
                                                        <div class="text-[11px] text-cyan-300/90 mt-0.5" x-show="quote.print_model" x-text="'Modelo: ' + (quote.print_model ? quote.print_model.name : '')"></div>
                                                        <div class="text-[11px] text-zinc-500" x-text="'Cliente: ' + (quote.user ? quote.user.name : 'Invitado')"></div>
                                                        <div class="text-[11px] text-zinc-400" x-show="quote.client_email" x-text="'Correo: ' + quote.client_email"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold border"
                                                    :class="{
                                                        'bg-zinc-900 text-zinc-300 border-zinc-700': (quote.status || 'draft') === 'draft',
                                                        'bg-cyan-950/70 text-cyan-300 border-cyan-700/60': quote.status === 'sent',
                                                        'bg-amber-950/60 text-amber-300 border-amber-700/60': quote.status === 'accepted',
                                                        'bg-emerald-950/60 text-emerald-300 border-emerald-700/60': quote.status === 'paid'
                                                    }"
                                                    x-text="statusLabel(quote.status || 'draft')">
                                                </span>
                                                <div class="text-[10px] text-zinc-500 mt-1 num" x-show="quote.sent_at" x-text="'Enviada: ' + quote.sent_at"></div>
                                            </td>
                                            <td class="px-4 py-3 font-bold text-orange-400 num" x-text="formatCurrency(Number(quote.final_price || 0))"></td>
                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-1.5">
                                                    <button type="button" @click="loadQuoteIntoCalculator(quote)" class="bg-[#0f1115] hover:bg-zinc-800 border border-zinc-700 text-zinc-200 text-xs font-medium px-2.5 py-1 rounded transition">
                                                        Cargar
                                                    </button>
                                                    <button type="button" x-show="isAdmin && (quote.status || 'draft') === 'draft'" @click="changeQuoteStatus(quote, 'sent')" class="bg-cyan-600 hover:bg-cyan-500 text-zinc-950 text-xs font-bold px-2.5 py-1 rounded transition">
                                                        Enviar
                                                    </button>
                                                    <button type="button" x-show="(quote.status || 'draft') !== 'accepted' && quote.status !== 'paid'" @click="changeQuoteStatus(quote, 'accepted')" class="bg-orange-600 hover:bg-orange-500 text-white text-xs font-semibold px-2.5 py-1 rounded transition">
                                                        Aceptar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

            </div>

            <!-- Columna derecha (1/3): Resumen en pantalla + PDF de 2 páginas al imprimir -->
            <aside class="lg:col-span-1 print-aside">
                <div class="print-sheet bg-[#161920] border border-zinc-800 rounded-lg p-5 sticky top-6">

                    <!-- Vista en pantalla -->
                    <div class="no-print">
                        <div class="flex items-center justify-between border-b border-zinc-800 pb-3 mb-4">
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-widest text-orange-400">Resumen</h2>
                                <span class="text-[10px] text-zinc-400 num" x-text="'Divisa: ' + currency"></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="flex items-center bg-[#0f1115] border border-zinc-800 rounded px-1 py-0.5 text-[11px] num">
                                    <button type="button" @click="stepQuantity(-1)" class="px-1.5 text-zinc-300 hover:text-white font-bold">-</button>
                                    <span class="px-1.5 text-white font-semibold" x-text="qty + ' pz'"></span>
                                    <button type="button" @click="stepQuantity(1)" class="px-1.5 text-zinc-300 hover:text-white font-bold">+</button>
                                </div>
                                <div class="flex bg-[#0f1115] border border-zinc-800 rounded p-0.5 text-[11px]">
                                    <button type="button" @click="summaryView = 'workshop'" :class="summaryView === 'workshop' ? 'bg-orange-600 text-white' : 'text-zinc-400 hover:text-zinc-200'" class="px-2.5 py-1 rounded font-semibold transition">
                                        Desglose
                                    </button>
                                    <button type="button" @click="summaryView = 'ticket'" :class="summaryView === 'ticket' ? 'bg-orange-600 text-white' : 'text-zinc-400 hover:text-zinc-200'" class="px-2.5 py-1 rounded font-semibold transition">
                                        Ticket
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Vista Ticket -->
                        <div x-show="summaryView === 'ticket'" class="space-y-3 text-xs">
                            <div class="bg-[#0f1115] border border-zinc-800 rounded p-3.5 space-y-2 num">
                                <div class="flex justify-between">
                                    <span class="text-zinc-400">Taller:</span>
                                    <span class="font-semibold text-white" x-text="businessName || 'PrintFlow'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-400">Proyecto:</span>
                                    <span class="font-semibold text-white truncate max-w-[175px]" x-text="projectName"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-400">Cantidad:</span>
                                    <span class="font-semibold text-white" x-text="qty + (qty === 1 ? ' pieza' : ' piezas')"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-400">Tiempo estimado:</span>
                                    <span class="font-semibold text-cyan-300" x-text="totalPrintHours.toFixed(2) + ' hrs'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-400">Material:</span>
                                    <span class="font-semibold text-cyan-300" x-text="effectiveFilamentGrams.toFixed(0) + ' g'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-400">Precio base:</span>
                                    <span class="font-semibold text-white" x-text="formatCurrency(commercialSubtotal * qty)"></span>
                                </div>
                                <div class="flex justify-between" x-show="(taxAmount + platformFeeAmount + marketplaceExtraAmount) > 0">
                                    <span class="text-zinc-400">Impuestos / cargos:</span>
                                    <span class="font-semibold text-zinc-200" x-text="formatCurrency((taxAmount + platformFeeAmount + marketplaceExtraAmount) * qty)"></span>
                                </div>
                                <div class="flex justify-between" x-show="qty > 1">
                                    <span class="text-zinc-400">Unitario:</span>
                                    <span class="font-semibold text-white" x-text="formatCurrency(unitSuggestedPrice)"></span>
                                </div>
                                <div class="flex justify-between" x-show="volumeDiscountApplied">
                                    <span class="text-zinc-400">Descuento volumen:</span>
                                    <span class="font-semibold text-cyan-300" x-text="'-' + formatCurrency(volumeDiscountAmount)"></span>
                                </div>
                                <div class="flex justify-between" x-show="shippingAmount > 0">
                                    <span class="text-zinc-400" x-text="shippingMode === 'included' ? 'Envío incluido:' : 'Envío (referencia):'"></span>
                                    <span class="font-semibold text-zinc-200" x-text="formatCurrency(shippingAmount)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Vista Desglose -->
                        <div x-show="summaryView === 'workshop'" class="space-y-3 text-xs num">
                            <div class="grid grid-cols-2 gap-2 mb-3 text-center">
                                <div class="bg-[#0f1115] border border-cyan-500/25 rounded p-2">
                                    <span class="block text-[10px] uppercase tracking-wider text-zinc-400">Costo / g</span>
                                    <span class="text-xs font-bold text-cyan-300" x-text="formatCurrency(costPerGram)">$0.00</span>
                                </div>
                                <div class="bg-[#0f1115] border border-orange-500/25 rounded p-2">
                                    <span class="block text-[10px] uppercase tracking-wider text-zinc-400">Costo / h</span>
                                    <span class="text-xs font-bold text-orange-300" x-text="formatCurrency(costPerHour)">$0.00</span>
                                </div>
                            </div>

                            <!-- Distribución porcentual del costo base -->
                            <div class="bg-[#0f1115] border border-zinc-800 rounded p-3 space-y-2.5">
                                <div class="flex items-center justify-between text-[10px] uppercase tracking-wider text-zinc-400">
                                    <span>Distribución costo base</span>
                                    <span class="text-zinc-300" x-text="formatCurrency(totalCost)"></span>
                                </div>
                                <div class="h-2 w-full bg-zinc-900 rounded-full overflow-hidden flex">
                                    <div class="bg-cyan-400 h-full transition-all duration-200" :style="'width: ' + costBreakdownPct.material + '%'"></div>
                                    <div class="bg-orange-500 h-full transition-all duration-200" :style="'width: ' + costBreakdownPct.machine + '%'"></div>
                                    <div class="bg-amber-400 h-full transition-all duration-200" :style="'width: ' + costBreakdownPct.overhead + '%'"></div>
                                </div>
                                <div class="space-y-1 text-[11px]">
                                    <div class="flex items-center justify-between">
                                        <span class="flex items-center gap-2 text-cyan-300">
                                            <span class="w-2 h-2 rounded-full bg-cyan-400 inline-block"></span>
                                            Material (<span x-text="costBreakdownPct.material.toFixed(0)"></span>%)
                                        </span>
                                        <span class="text-zinc-200" x-text="formatCurrency(filamentCost)"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="flex items-center gap-2 text-orange-300">
                                            <span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span>
                                            Máquina y luz (<span x-text="costBreakdownPct.machine.toFixed(0)"></span>%)
                                        </span>
                                        <span class="text-zinc-200" x-text="formatCurrency(electricityCost + wearCost)"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="flex items-center gap-2 text-amber-300">
                                            <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
                                            Operación y fallos (<span x-text="costBreakdownPct.overhead.toFixed(0)"></span>%)
                                        </span>
                                        <span class="text-zinc-200" x-text="formatCurrency(Math.max(0, totalCost - filamentCost - electricityCost - wearCost))"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1.5 text-zinc-300">
                                <div class="flex justify-between">
                                    <span>Filamento (<span x-text="effectiveFilamentGrams.toFixed(0)"></span>g)</span>
                                    <span class="text-white" x-text="formatCurrency(filamentCost)">$0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Electricidad (<span x-text="energyKwh.toFixed(2)"></span> kWh)</span>
                                    <span class="text-white" x-text="formatCurrency(electricityCost)">$0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Depreciación equipo</span>
                                    <span class="text-white" x-text="formatCurrency(wearCost)">$0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Mano de obra (<span x-text="laborBasis === 'manual' ? 'manual' : 'total'"></span>)</span>
                                    <span class="text-white" x-text="formatCurrency(laborCost)">$0.00</span>
                                </div>
                                <div class="flex justify-between" x-show="extraWorker.enabled && extraWorkerCost > 0">
                                    <span x-text="'Trabajador extra (' + (extraWorker.name || 'Apoyo') + ')'"></span>
                                    <span class="text-white" x-text="formatCurrency(extraWorkerCost)">$0.00</span>
                                </div>
                                <div class="flex justify-between" x-show="(Number(designCost) || 0) > 0">
                                    <span>Diseño</span>
                                    <span class="text-white" x-text="formatCurrency(Number(designCost) || 0)">$0.00</span>
                                </div>
                                <div class="flex justify-between" x-show="(Number(packagingCost) || 0) > 0">
                                    <span>Empaque</span>
                                    <span class="text-white" x-text="formatCurrency(Number(packagingCost) || 0)">$0.00</span>
                                </div>
                                <div class="flex justify-between" x-show="fixedCostPerPiece > 0">
                                    <span>Gastos fijos</span>
                                    <span class="text-white" x-text="formatCurrency(fixedCostPerPiece)">$0.00</span>
                                </div>
                                <div class="flex justify-between" x-show="suppliesAmount > 0">
                                    <span>Otros costos</span>
                                    <span class="text-white" x-text="formatCurrency(suppliesAmount)">$0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Margen fallos (<span x-text="failureMarginRate"></span>%)</span>
                                    <span class="text-amber-300" x-text="formatCurrency(failureAmount)">$0.00</span>
                                </div>

                                <div class="border-t border-zinc-800 pt-2 flex justify-between font-semibold text-white">
                                    <span>Subtotal costos</span>
                                    <span class="text-cyan-300" x-text="formatCurrency(totalCost)">$0.00</span>
                                </div>

                                <div class="flex justify-between">
                                    <span>Ganancia (<span x-text="profitMargin"></span>%)</span>
                                    <span class="text-orange-400 font-semibold" x-text="formatCurrency(profitAmount)">$0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Impuestos (<span x-text="taxRate"></span>%)</span>
                                    <span class="text-zinc-200" x-text="formatCurrency(taxAmount)">$0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Comisión (<span x-text="platformFeeRate"></span>%)</span>
                                    <span class="text-zinc-200" x-text="formatCurrency(platformFeeAmount)">$0.00</span>
                                </div>
                                <div class="flex justify-between" x-show="(Number(marketplaceExtraRate) || 0) > 0">
                                    <span>Recargo (<span x-text="marketplaceExtraRate"></span>%)</span>
                                    <span class="text-zinc-200" x-text="formatCurrency(marketplaceExtraAmount)">$0.00</span>
                                </div>
                                <div class="flex justify-between" x-show="qty > 1">
                                    <span>Unitario</span>
                                    <span class="text-zinc-200" x-text="formatCurrency(unitSuggestedPrice)">$0.00</span>
                                </div>
                                <div class="flex justify-between" x-show="volumeDiscountApplied">
                                    <span>Desc. volumen (<span x-text="volumeDiscountRate"></span>%)</span>
                                    <span class="text-cyan-300" x-text="'-' + formatCurrency(volumeDiscountAmount)">$0.00</span>
                                </div>
                                <div class="flex justify-between" x-show="shippingAmount > 0">
                                    <span x-text="shippingMode === 'included' ? 'Envío (incluido)' : 'Envío (referencia)'"></span>
                                    <span class="text-zinc-200" x-text="formatCurrency(shippingAmount)">$0.00</span>
                                </div>
                                <div class="border-t border-zinc-800 pt-2 flex justify-between font-semibold text-cyan-300">
                                    <span>Total que gano yo</span>
                                    <span x-text="formatCurrency(netProfitTotal)">$0.00</span>
                                </div>
                            </div>

                            <p class="text-[11px] text-zinc-400 bg-[#0f1115] border border-zinc-800/90 rounded px-2.5 py-1.5" x-show="pricingRuleNote" x-text="pricingRuleNote"></p>
                        </div>

                        <!-- Total -->
                        <div class="mt-4 bg-[#0f1115] p-4 rounded border border-orange-500/40 text-center">
                            <span class="block text-[10px] uppercase tracking-widest text-orange-300 font-semibold mb-1">
                                Total sugerido (<span class="num" x-text="qty"></span> <span x-text="qty === 1 ? 'pieza' : 'piezas'"></span>)
                            </span>
                            <span class="text-2xl sm:text-3xl font-extrabold text-white num" x-text="formatCurrency(finalPrice)">$0.00</span>
                        </div>

                        <!-- Botones -->
                        <div class="mt-4 space-y-2">
                            <button @click="saveQuote()" :disabled="loading" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-semibold py-2.5 px-4 rounded transition disabled:opacity-50 text-xs sm:text-sm">
                                <span x-show="!loading">Guardar cotización</span>
                                <span x-show="loading">Guardando...</span>
                            </button>

                            <div class="grid grid-cols-2 gap-2">
                                <button @click="copySummary()" class="bg-[#0f1115] hover:bg-zinc-800 border border-cyan-500/30 text-cyan-200 font-medium py-2 px-3 rounded transition text-xs">
                                    <span x-text="copied ? 'Copiado' : 'Copiar resumen'"></span>
                                </button>
                                <button @click="resetForm()" class="bg-[#0f1115] hover:bg-zinc-800 border border-zinc-700 text-zinc-300 font-medium py-2 px-3 rounded transition text-xs">
                                    Reiniciar
                                </button>
                            </div>

                            <button @click="exportPDF()" class="w-full bg-[#0f1115] hover:bg-zinc-800 border border-zinc-700 text-zinc-200 font-medium py-2 px-4 rounded transition text-xs sm:text-sm">
                                Descargar / Imprimir PDF
                            </button>
                        </div>
                    </div>

                    <!-- Vista exclusiva de impresión / PDF (2 páginas: Cliente + Uso interno) -->
                    <div class="print-only-pages num">
                        <!-- Página 1: Cotización para el cliente -->
                        <div class="print-page">
                            <div>
                                <div class="print-accent-bar"></div>

                                <div class="flex justify-between items-start border-b border-slate-200 pb-4 mb-5">
                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-orange-600">Cotización formal</div>
                                        <h2 class="text-2xl font-extrabold text-slate-900 mt-0.5" x-text="businessName || 'PrintFlow'"></h2>
                                        <p class="text-xs text-slate-600 mt-0.5">Fabricación aditiva / Servicio de impresión 3D</p>
                                    </div>
                                    <div class="text-right text-xs text-slate-600 space-y-1">
                                        <div><strong class="text-slate-800">Fecha:</strong> <span x-text="currentDateLabel"></span></div>
                                        <div><strong class="text-slate-800">Divisa:</strong> <span x-text="currency"></span></div>
                                        <div><strong class="text-slate-800">Vigencia:</strong> 15 días naturales</div>
                                    </div>
                                </div>

                                <!-- Ficha del proyecto y cliente -->
                                <div class="grid grid-cols-2 gap-4 bg-slate-50 border border-slate-200 rounded-md p-3.5 mb-5 text-xs">
                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Proyecto / Pieza</div>
                                        <div class="text-sm font-bold text-slate-900 mt-0.5" x-text="projectName"></div>
                                        <div class="text-slate-600 mt-0.5" x-show="selectedModelName" x-text="'Modelo base: ' + selectedModelName"></div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Datos del cliente</div>
                                        <div class="text-slate-800 font-medium mt-0.5" x-text="clientEmail || 'Cliente general'"></div>
                                        <div class="text-slate-600 mt-0.5" x-text="'Tipo de atención: ' + clientType.toUpperCase()"></div>
                                    </div>
                                </div>

                                <!-- Tabla de conceptos -->
                                <table class="print-table mb-5">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th style="text-align: center;">Cantidad</th>
                                            <th style="text-align: right;">P. Unitario</th>
                                            <th style="text-align: right;">Importe</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="font-semibold text-slate-900" x-text="'Impresión 3D — ' + projectName"></div>
                                                <div class="text-[11px] text-slate-500" x-text="'Tiempo estimado de máquina: ' + totalPrintHours.toFixed(2) + ' hrs · Acabado y revisión incluidos'"></div>
                                            </td>
                                            <td style="text-align: center;" x-text="qty"></td>
                                            <td style="text-align: right;" x-text="formatCurrency(unitSuggestedPrice)"></td>
                                            <td style="text-align: right; font-weight: 600;" x-text="formatCurrency(subtotalBeforeDiscount)"></td>
                                        </tr>
                                        <tr x-show="volumeDiscountApplied">
                                            <td>
                                                <div class="font-medium text-emerald-700" x-text="'Descuento por volumen (' + volumeDiscountRate + '%)'"></div>
                                                <div class="text-[11px] text-slate-500" x-text="'Aplicado a partir de ' + volumeMinQty + ' piezas'"></div>
                                            </td>
                                            <td style="text-align: center;">—</td>
                                            <td style="text-align: right;">—</td>
                                            <td style="text-align: right; font-weight: 600; color: #047857;" x-text="'-' + formatCurrency(volumeDiscountAmount)"></td>
                                        </tr>
                                        <tr x-show="shippingAmount > 0 && shippingMode === 'included'">
                                            <td>
                                                <div class="font-medium text-slate-900">Envío / entrega</div>
                                                <div class="text-[11px] text-slate-500">Incluido en el total de la cotización</div>
                                            </td>
                                            <td style="text-align: center;">1</td>
                                            <td style="text-align: right;" x-text="formatCurrency(shippingAmount)"></td>
                                            <td style="text-align: right; font-weight: 600;" x-text="formatCurrency(shippingAmount)"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Totales y referencia de envío -->
                                <div class="flex justify-end mb-5">
                                    <div class="w-full max-w-xs border border-slate-200 rounded-md p-4 bg-slate-50 space-y-2 text-xs">
                                        <div class="flex justify-between text-slate-600">
                                            <span>Subtotal piezas:</span>
                                            <span class="font-semibold text-slate-900" x-text="formatCurrency(subtotalBeforeDiscount)"></span>
                                        </div>
                                        <div class="flex justify-between text-emerald-700" x-show="volumeDiscountApplied">
                                            <span>Descuento volumen:</span>
                                            <span class="font-semibold" x-text="'-' + formatCurrency(volumeDiscountAmount)"></span>
                                        </div>
                                        <div class="flex justify-between text-slate-600" x-show="shippingAmount > 0 && shippingMode === 'included'">
                                            <span>Envío incluido:</span>
                                            <span class="font-semibold text-slate-900" x-text="formatCurrency(shippingAmount)"></span>
                                        </div>
                                        <div class="flex justify-between text-slate-500 border-t border-slate-200 pt-2" x-show="shippingAmount > 0 && shippingMode === 'reference'">
                                            <span>Envío estimado (referencia):</span>
                                            <span class="font-medium" x-text="formatCurrency(shippingAmount)"></span>
                                        </div>
                                        <div class="flex justify-between items-baseline border-t-2 border-slate-900 pt-2.5 text-slate-900">
                                            <span class="text-xs font-bold uppercase tracking-wider">Total a pagar</span>
                                            <span class="text-2xl font-extrabold" x-text="formatCurrency(finalPrice)"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notas y condiciones -->
                                <div class="border border-slate-200 rounded-md p-3.5 text-xs space-y-1.5 bg-white">
                                    <div class="font-bold uppercase tracking-wider text-[10px] text-slate-500">Notas y condiciones de servicio</div>
                                    <p class="text-slate-800" x-show="quickNote" x-text="quickNote"></p>
                                    <p class="text-slate-600">Las piezas impresas en 3D pueden presentar ligeras líneas de capa propias del proceso aditivo y tolerancias dimensionales estándar.</p>
                                    <p class="text-slate-600" x-show="shippingAmount > 0 && shippingMode === 'reference'">El costo de envío mostrado es de referencia y se confirma al coordinar la entrega.</p>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 pt-3 mt-6 flex justify-between text-[11px] text-slate-500">
                                <span x-text="(businessName || 'PrintFlow') + ' · Cotización generada automáticamente'"></span>
                                <span>Página 1 de 2 · Cliente</span>
                            </div>
                        </div>

                        <!-- Página 2: Desglose de costos para uso interno -->
                        <div class="print-page print-page-break">
                            <div>
                                <div class="flex justify-between items-center border-b border-slate-200 pb-3 mb-4">
                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-cyan-700">Control de taller</div>
                                        <h3 class="text-lg font-extrabold text-slate-900">Desglose de costos — Solo para uso interno</h3>
                                    </div>
                                    <div class="text-right text-xs text-slate-600">
                                        <div x-text="'Proyecto: ' + projectName"></div>
                                        <div x-text="'Fecha: ' + currentDateLabel"></div>
                                    </div>
                                </div>

                                <!-- Parámetros técnicos de cálculo -->
                                <div class="grid grid-cols-3 gap-3 bg-slate-50 border border-slate-200 rounded-md p-3 mb-4 text-[11px]">
                                    <div>
                                        <span class="text-slate-500 block">Filamento efectivo</span>
                                        <strong class="text-slate-900" x-text="effectiveFilamentGrams.toFixed(1) + ' g (' + formatCurrency(filamentPricePerKg) + '/' + spoolGrams + 'g)'"></strong>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 block">Tiempo / Energía</span>
                                        <strong class="text-slate-900" x-text="totalPrintHours.toFixed(2) + ' hrs · ' + energyKwh.toFixed(2) + ' kWh'"></strong>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 block">Mano de obra</span>
                                        <strong class="text-slate-900" x-text="totalLaborHours.toFixed(2) + ' hrs (' + laborBasis + ')'"></strong>
                                    </div>
                                </div>

                                <!-- Tabla de costos internos -->
                                <table class="print-table mb-4">
                                    <thead>
                                        <tr>
                                            <th>Rubro interno</th>
                                            <th>Detalle / Regla</th>
                                            <th style="text-align: right;">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Material (filamento)</td>
                                            <td class="text-slate-500" x-text="effectiveFilamentGrams.toFixed(1) + ' g' + (multicolorEnabled ? ' (multicolor +' + multicolorWasteRate + '%)' : '')"></td>
                                            <td style="text-align: right;" x-text="formatCurrency(filamentCost)"></td>
                                        </tr>
                                        <tr>
                                            <td>Electricidad</td>
                                            <td class="text-slate-500" x-text="printerWatts + ' W · ' + energyKwh.toFixed(2) + ' kWh · ' + formatCurrency(electricityCostPerKwh) + '/kWh'"></td>
                                            <td style="text-align: right;" x-text="formatCurrency(electricityCost)"></td>
                                        </tr>
                                        <tr>
                                            <td>Depreciación / desgaste</td>
                                            <td class="text-slate-500" x-text="totalPrintHours.toFixed(2) + ' hrs · ' + formatCurrency(wearCostPerHour) + '/hr'"></td>
                                            <td style="text-align: right;" x-text="formatCurrency(wearCost)"></td>
                                        </tr>
                                        <tr>
                                            <td>Mano de obra</td>
                                            <td class="text-slate-500" x-text="'Base ' + laborBasis + ' · ' + formatCurrency(laborRatePerHour) + '/hr'"></td>
                                            <td style="text-align: right;" x-text="formatCurrency(laborCost)"></td>
                                        </tr>
                                        <tr x-show="extraWorker.enabled && extraWorkerCost > 0">
                                            <td>Trabajador extra</td>
                                            <td class="text-slate-500" x-text="extraWorker.name || 'Apoyo adicional'"></td>
                                            <td style="text-align: right;" x-text="formatCurrency(extraWorkerCost)"></td>
                                        </tr>
                                        <tr>
                                            <td>Diseño, empaque y fijos</td>
                                            <td class="text-slate-500">Diseño + empaque + prorrateo fijo mensual</td>
                                            <td style="text-align: right;" x-text="formatCurrency((Number(designCost) || 0) + (Number(packagingCost) || 0) + fixedCostPerPiece)"></td>
                                        </tr>
                                        <tr>
                                            <td>Otros costos / insumos</td>
                                            <td class="text-slate-500" x-text="extraCostItems.length + ' concepto(s) extra'"></td>
                                            <td style="text-align: right;" x-text="formatCurrency(suppliesAmount)"></td>
                                        </tr>
                                        <tr>
                                            <td>Margen de fallos</td>
                                            <td class="text-slate-500" x-text="failureMarginRate + '% sobre subtotal operativo'"></td>
                                            <td style="text-align: right;" x-text="formatCurrency(failureAmount)"></td>
                                        </tr>
                                        <tr style="background: #f8fafc; font-weight: 700;">
                                            <td>Subtotal (Costos base)</td>
                                            <td class="text-slate-500" style="font-weight: 400;">Costo unitario de producción</td>
                                            <td style="text-align: right;" x-text="formatCurrency(totalCost)"></td>
                                        </tr>
                                        <tr>
                                            <td>Margen de ganancia</td>
                                            <td class="text-slate-500" x-text="profitMargin + '% sobre costo base'"></td>
                                            <td style="text-align: right;" x-text="'+' + formatCurrency(profitAmount)"></td>
                                        </tr>
                                        <tr>
                                            <td>Impuestos y comisiones</td>
                                            <td class="text-slate-500" x-text="'IVA ' + taxRate + '% · Plat. ' + platformFeeRate + '% · Recargo ' + marketplaceExtraRate + '%'"></td>
                                            <td style="text-align: right;" x-text="formatCurrency(taxAmount + platformFeeAmount + marketplaceExtraAmount)"></td>
                                        </tr>
                                        <tr x-show="shippingAmount > 0">
                                            <td>Envío</td>
                                            <td class="text-slate-500" x-text="shippingMode === 'included' ? 'Incluido en total' : 'Solo referencia'"></td>
                                            <td style="text-align: right;" x-text="formatCurrency(shippingAmount)"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Resumen financiero interno -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="border border-slate-200 rounded-md p-3.5 bg-slate-50">
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total cliente</div>
                                        <div class="text-xl font-extrabold text-slate-900 mt-0.5" x-text="formatCurrency(finalPrice)"></div>
                                    </div>
                                    <div class="border border-emerald-200 rounded-md p-3.5 bg-emerald-50/60">
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Ganancia neta estimada</div>
                                        <div class="text-xl font-extrabold text-emerald-800 mt-0.5" x-text="formatCurrency(netProfitTotal)"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 pt-3 mt-6 flex justify-between text-[11px] text-slate-500">
                                <span>Documento confidencial — Uso interno del taller</span>
                                <span>Página 2 de 2 · Interno</span>
                            </div>
                        </div>
                    </div>

                </div>
            </aside>

        </div>
    </div>

    <!-- Alpine.js Logic -->
    <script>
        function printCalculator() {
            return {
                activeTab: 'quick',
                summaryView: 'workshop',
                isAdmin: @json(auth()->user()?->role === 'admin'),
                businessName: 'PrintFlow',
                projectName: 'Mi Impresión 3D',
                clientEmail: @json(auth()->user()?->email ?? ''),
                currency: 'MXN',
                quickNote: '',
                selectedModelId: null,
                selectedModelName: '',
                printHours: 5,
                printMinutes: 0,
                quantity: 1,
                filamentPricePerKg: 450,
                spoolGrams: 1000,
                filamentGrams: 75,
                filamentWasteRate: 0,
                multicolorEnabled: false,
                multicolorWasteRate: 15,
                printerWatts: 120,
                electricityCostPerKwh: 3.50,
                wearCostPerHour: 2.00,
                printerPrice: 8000,
                printerLifespanHours: 4000,
                designCost: 0,
                postProcessCost: 0,
                packagingCost: 0,
                suppliesCost: 0,
                newExtraItem: { label: '', amount: 0 },
                extraCostItems: [],
                laborUnit: 'min',
                prepMinutes: 0,
                handlingMinutes: 0,
                supportMinutes: 0,
                laborBasis: 'total',
                extraWorker: {
                    enabled: false,
                    name: '',
                    ratePerHour: 20,
                    timeValue: 0
                },
                monthlyFixedCost: 0,
                monthlyEstimatedPieces: 100,
                failureMarginRate: 8,
                shippingCost: 0,
                shippingMode: 'reference',
                clientType: 'normal',
                profitMargin: 40,
                laborRatePerHour: 25.00,
                taxRate: 16,
                platformFeeRate: 2.9,
                marketplaceExtraRate: 0,
                minPrice: 50,
                roundStep: 5,
                volumeMinQty: 10,
                volumeDiscountRate: 10,
                monthlyGoal: 10000,
                searchQuery: '',
                onlySentFilter: false,
                loading: false,
                copied: false,
                authModal: false,
                authTab: 'login',
                showModelForm: false,
                savingModel: false,
                editingModel: null,
                updatingModel: false,
                newModelPreview: null,
                editModelPreview: null,
                bannerTimeout: null,
                statusBanner: {
                    message: '',
                    type: 'success'
                },
                newModel: {
                    name: '',
                    description: '',
                    image: null,
                    filament_grams: 50,
                    print_hours: 2,
                    print_minutes: 0
                },
                editForm: {
                    id: null,
                    name: '',
                    description: '',
                    image: null,
                    filament_grams: 0,
                    print_hours: 0,
                    print_minutes: 0
                },

                quotes: @json($quotes),
                models: @json($models),

                get currencySymbol() {
                    const map = { MXN: '$', USD: '$', EUR: '€' };
                    return map[this.currency] || '$';
                },
                get currentDateLabel() {
                    return new Date().toLocaleDateString('es-MX', {
                        year: 'numeric',
                        month: 'short',
                        day: '2-digit'
                    });
                },
                get pendingSentCount() {
                    return this.quotes.filter(q => (q.status || 'draft') === 'sent').length;
                },
                stepQuantity(delta) {
                    const next = (Number(this.quantity) || 1) + delta;
                    this.quantity = Math.max(1, next);
                },
                applyMaterialPreset(type) {
                    const presets = {
                        pla: { price: 450, spool: 1000, waste: 0, watts: 120, failure: 8 },
                        petg: { price: 520, spool: 1000, waste: 3, watts: 150, failure: 10 },
                        tpu: { price: 650, spool: 500, waste: 5, watts: 135, failure: 12 }
                    };
                    const p = presets[type];
                    if (!p) return;
                    this.filamentPricePerKg = p.price;
                    this.spoolGrams = p.spool;
                    this.filamentWasteRate = p.waste;
                    this.printerWatts = p.watts;
                    this.failureMarginRate = p.failure;
                    this.notify(`Preajuste ${type.toUpperCase()} aplicado.`, 'success');
                },
                get goalAlertText() {
                    const units = this.monthlyUnitsNeeded;
                    const estimatedMonthlyCap = Math.max(1, Number(this.monthlyEstimatedPieces) || 100);
                    if ((Number(this.monthlyGoal) || 0) > 0 && this.profitAmount <= 0) {
                        return 'La ganancia por pieza es $0.00; aumenta el margen o reduce costos para alcanzar tu meta.';
                    }
                    if (units > estimatedMonthlyCap) {
                        return `Meta exigente (${units} piezas vs. ${estimatedMonthlyCap} estimadas/mes): considera subir el margen o aumentar volumen.`;
                    }
                    return '';
                },
                quoteDetailsText(quote) {
                    const parts = [`${quote.print_time_hours || 0} hrs`];
                    if (quote.filament_grams) {
                        parts.push(`${quote.filament_grams} g`);
                    }
                    const extras = (Number(quote.design_cost) || 0) + (Number(quote.post_process_cost) || 0) + (Number(quote.packaging_cost) || 0);
                    if (extras > 0) {
                        parts.push(`Extras ${this.formatCurrency(extras)}`);
                    }
                    return parts.join(' · ');
                },
                notify(message, type = 'success') {
                    this.statusBanner.message = message;
                    this.statusBanner.type = type;
                    if (this.bannerTimeout) {
                        clearTimeout(this.bannerTimeout);
                    }
                    this.bannerTimeout = setTimeout(() => {
                        this.statusBanner.message = '';
                    }, 5000);
                },
                toggleExtraWorker() {
                    this.extraWorker.enabled = !this.extraWorker.enabled;
                },
                addExtraCostItem() {
                    const label = (this.newExtraItem.label || '').trim();
                    const amount = Number(this.newExtraItem.amount) || 0;
                    if (!label || amount <= 0) {
                        this.notify('Escribe un concepto y un monto mayor a 0.', 'error');
                        return;
                    }
                    this.extraCostItems.push({ label, amount });
                    this.newExtraItem = { label: '', amount: 0 };
                    this.notify(`Agregado "${label}" a otros costos.`, 'success');
                },
                removeExtraCostItem(index) {
                    this.extraCostItems.splice(index, 1);
                },
                loadQuoteIntoCalculator(quote) {
                    const totalHoursFloat = Math.max(0, Number(quote.print_time_hours) || 0);
                    const wholeHours = Math.floor(totalHoursFloat);
                    const mins = Math.round((totalHoursFloat - wholeHours) * 60);

                    this.projectName = quote.project_name || 'Mi Impresión 3D';
                    this.printHours = mins === 60 ? wholeHours + 1 : wholeHours;
                    this.printMinutes = mins === 60 ? 0 : mins;
                    this.filamentGrams = Number(quote.filament_grams) || 0;
                    this.filamentWasteRate = 0;
                    this.multicolorEnabled = false;
                    this.designCost = Number(quote.design_cost) || 0;
                    this.postProcessCost = Number(quote.post_process_cost) || 0;
                    this.packagingCost = Number(quote.packaging_cost) || 0;
                    this.selectedModelId = quote.print_model_id || quote.print_model?.id || null;
                    this.selectedModelName = quote.print_model?.name || '';
                    if (quote.client_email) {
                        this.clientEmail = quote.client_email;
                    }

                    this.activeTab = 'quick';
                    const summary = `${this.projectName} (${this.totalPrintHours.toFixed(2)} hrs, ${this.filamentGrams} g, ${this.formatCurrency(Number(quote.final_price || 0))})`;
                    this.notify(`Cotización cargada para revisión: ${summary}.`, 'success');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                statusLabel(status) {
                    const labels = {
                        draft: 'Borrador',
                        sent: 'Enviada',
                        accepted: 'Aceptada',
                        paid: 'Pagada'
                    };
                    return labels[status] || 'Borrador';
                },
                async changeQuoteStatus(quote, nextStatus) {
                    try {
                        const payload = { status: nextStatus };
                        if (nextStatus === 'sent' && this.clientEmail) {
                            payload.client_email = this.clientEmail;
                        }

                        const response = await fetch(`/quotes/${quote.id}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await response.json();
                        if (response.ok && data.success) {
                            quote.status = data.quote.status;
                            quote.client_email = data.quote.client_email;
                            quote.sent_at = data.quote.sent_at;
                            const extraMailInfo = nextStatus === 'sent' && data.mail_sent ? ' (correo enviado)' : '';
                            this.notify((data.message || 'Estado actualizado.') + extraMailInfo, 'success');
                        } else {
                            this.notify(data.message || 'No se pudo actualizar el estado.', 'error');
                        }
                    } catch (e) {
                        console.error(e);
                        this.notify('Error de conexión al actualizar estado.', 'error');
                    }
                },
                clearNewModelPreview() {
                    if (this.newModelPreview) {
                        URL.revokeObjectURL(this.newModelPreview);
                        this.newModelPreview = null;
                    }
                },
                clearEditModelPreview() {
                    if (this.editModelPreview) {
                        URL.revokeObjectURL(this.editModelPreview);
                        this.editModelPreview = null;
                    }
                },
                toggleNewModelForm() {
                    this.showModelForm = !this.showModelForm;
                    if (this.showModelForm) {
                        this.cancelEditModel();
                    } else {
                        this.clearNewModelPreview();
                    }
                },
                onModelImageChange(event) {
                    const file = event.target.files[0] || null;
                    this.newModel.image = file;
                    this.clearNewModelPreview();
                    if (file) {
                        this.newModelPreview = URL.createObjectURL(file);
                    }
                },
                selectModel(item) {
                    this.selectedModelId = item.id;
                    this.selectedModelName = item.name;
                    this.projectName = item.name;
                    this.filamentGrams = Number(item.filament_grams) || 0;
                    this.printHours = Number(item.print_hours) || 0;
                    this.printMinutes = Number(item.print_minutes) || 0;
                    this.notify(`Modelo "${item.name}" seleccionado para cotizar.`, 'success');
                },
                async saveModel() {
                    this.savingModel = true;
                    try {
                        const formData = new FormData();
                        formData.append('name', this.newModel.name);
                        formData.append('description', this.newModel.description || '');
                        formData.append('filament_grams', Number(this.newModel.filament_grams) || 0);
                        formData.append('print_hours', Number(this.newModel.print_hours) || 0);
                        formData.append('print_minutes', Number(this.newModel.print_minutes) || 0);
                        if (this.newModel.image) {
                            formData.append('image', this.newModel.image);
                        }

                        const response = await fetch('/models', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.models.unshift(data.model);
                            this.newModel = {
                                name: '',
                                description: '',
                                image: null,
                                filament_grams: 50,
                                print_hours: 2,
                                print_minutes: 0
                            };
                            this.clearNewModelPreview();
                            this.showModelForm = false;
                            this.notify(data.message || 'Modelo guardado.', 'success');
                        } else {
                            this.notify('Error: ' + (data.message || 'No se pudo guardar el modelo.'), 'error');
                        }
                    } catch (error) {
                        console.error(error);
                        this.notify('Error de conexión al subir el modelo.', 'error');
                    } finally {
                        this.savingModel = false;
                    }
                },
                startEditModel(item) {
                    this.showModelForm = false;
                    this.clearNewModelPreview();
                    this.clearEditModelPreview();
                    this.editingModel = item;
                    this.editForm = {
                        id: item.id,
                        name: item.name,
                        description: item.description || '',
                        image: null,
                        filament_grams: Number(item.filament_grams) || 0,
                        print_hours: Number(item.print_hours) || 0,
                        print_minutes: Number(item.print_minutes) || 0
                    };
                },
                cancelEditModel() {
                    this.editingModel = null;
                    this.clearEditModelPreview();
                    this.editForm = {
                        id: null,
                        name: '',
                        description: '',
                        image: null,
                        filament_grams: 0,
                        print_hours: 0,
                        print_minutes: 0
                    };
                },
                onEditModelImageChange(event) {
                    const file = event.target.files[0] || null;
                    this.editForm.image = file;
                    this.clearEditModelPreview();
                    if (file) {
                        this.editModelPreview = URL.createObjectURL(file);
                    }
                },
                async updateModel() {
                    if (!this.editForm.id) return;
                    this.updatingModel = true;
                    try {
                        const formData = new FormData();
                        formData.append('name', this.editForm.name);
                        formData.append('description', this.editForm.description || '');
                        formData.append('filament_grams', Number(this.editForm.filament_grams) || 0);
                        formData.append('print_hours', Number(this.editForm.print_hours) || 0);
                        formData.append('print_minutes', Number(this.editForm.print_minutes) || 0);
                        if (this.editForm.image) {
                            formData.append('image', this.editForm.image);
                        }

                        const response = await fetch(`/models/${this.editForm.id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            const idx = this.models.findIndex(m => m.id === this.editForm.id);
                            if (idx !== -1) {
                                this.models[idx] = data.model;
                            }
                            this.cancelEditModel();
                            this.notify(data.message || 'Modelo actualizado.', 'success');
                        } else {
                            this.notify('Error: ' + (data.message || 'No se pudo actualizar el modelo.'), 'error');
                        }
                    } catch (error) {
                        console.error(error);
                        this.notify('Error de conexión al actualizar el modelo.', 'error');
                    } finally {
                        this.updatingModel = false;
                    }
                },
                async deleteModel(item) {
                    if (!confirm(`¿Eliminar "${item.name}" del catálogo?`)) return;
                    try {
                        const response = await fetch(`/models/${item.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.models = this.models.filter(m => m.id !== item.id);
                            if (this.editingModel && this.editingModel.id === item.id) {
                                this.cancelEditModel();
                            }
                            this.notify(data.message || 'Modelo eliminado.', 'success');
                        } else {
                            this.notify('Error: ' + (data.message || 'No se pudo eliminar el modelo.'), 'error');
                        }
                    } catch (error) {
                        console.error(error);
                        this.notify('Error de conexión al eliminar el modelo.', 'error');
                    }
                },
                applyClientPreset() {
                    const presets = {
                        normal: 40,
                        wholesale: 25,
                        school: 20,
                        friends: 15
                    };
                    if (presets[this.clientType] !== undefined) {
                        this.profitMargin = presets[this.clientType];
                    }
                },
                get estimatedWearPerHour() {
                    const price = Math.max(0, Number(this.printerPrice) || 0);
                    const hours = Math.max(1, Number(this.printerLifespanHours) || 4000);
                    return price / hours;
                },
                applyEstimatedWear() {
                    this.wearCostPerHour = Number(this.estimatedWearPerHour.toFixed(2));
                    this.notify(`Desgaste actualizado a ${this.formatCurrency(this.wearCostPerHour)}/h.`, 'success');
                },
                resetForm() {
                    if (!confirm('¿Reiniciar todos los campos de la calculadora a sus valores iniciales?')) return;
                    this.businessName = 'PrintFlow';
                    this.projectName = 'Mi Impresión 3D';
                    this.currency = 'MXN';
                    this.quickNote = '';
                    this.selectedModelId = null;
                    this.selectedModelName = '';
                    this.printHours = 5;
                    this.printMinutes = 0;
                    this.quantity = 1;
                    this.filamentPricePerKg = 450;
                    this.spoolGrams = 1000;
                    this.filamentGrams = 75;
                    this.filamentWasteRate = 0;
                    this.multicolorEnabled = false;
                    this.multicolorWasteRate = 15;
                    this.printerWatts = 120;
                    this.electricityCostPerKwh = 3.50;
                    this.wearCostPerHour = 2.00;
                    this.printerPrice = 8000;
                    this.printerLifespanHours = 4000;
                    this.designCost = 0;
                    this.postProcessCost = 0;
                    this.packagingCost = 0;
                    this.suppliesCost = 0;
                    this.newExtraItem = { label: '', amount: 0 };
                    this.extraCostItems = [];
                    this.laborUnit = 'min';
                    this.prepMinutes = 0;
                    this.handlingMinutes = 0;
                    this.supportMinutes = 0;
                    this.laborBasis = 'total';
                    this.extraWorker = { enabled: false, name: '', ratePerHour: 20, timeValue: 0 };
                    this.monthlyFixedCost = 0;
                    this.monthlyEstimatedPieces = 100;
                    this.failureMarginRate = 8;
                    this.shippingCost = 0;
                    this.shippingMode = 'reference';
                    this.clientType = 'normal';
                    this.profitMargin = 40;
                    this.laborRatePerHour = 25.00;
                    this.taxRate = 16;
                    this.platformFeeRate = 2.9;
                    this.marketplaceExtraRate = 0;
                    this.minPrice = 50;
                    this.roundStep = 5;
                    this.volumeMinQty = 10;
                    this.volumeDiscountRate = 10;
                    this.notify('Calculadora reiniciada.', 'success');
                },
                toHours(val) {
                    const num = Math.max(0, Number(val) || 0);
                    return this.laborUnit === 'hrs' ? num : num / 60;
                },
                get totalPrintHours() {
                    const h = Math.max(0, Number(this.printHours) || 0);
                    const m = Math.max(0, Number(this.printMinutes) || 0);
                    return h + (m / 60);
                },
                get manualLaborHours() {
                    return this.toHours(this.prepMinutes) +
                           this.toHours(this.handlingMinutes) +
                           this.toHours(this.supportMinutes);
                },
                get manualLaborMinutesDisplay() {
                    return Math.round(this.manualLaborHours * 60);
                },
                get manualLaborCost() {
                    return this.manualLaborHours * (Number(this.laborRatePerHour) || 0);
                },
                get extraWorkerCost() {
                    if (!this.extraWorker.enabled) return 0;
                    return this.toHours(this.extraWorker.timeValue) * (Number(this.extraWorker.ratePerHour) || 0);
                },
                get totalLaborHours() {
                    return this.laborBasis === 'manual'
                        ? this.manualLaborHours
                        : this.totalPrintHours + this.manualLaborHours;
                },
                get effectiveFilamentGrams() {
                    const grams = Math.max(0, Number(this.filamentGrams) || 0);
                    const baseWaste = Math.max(0, Number(this.filamentWasteRate) || 0);
                    const multiWaste = this.multicolorEnabled ? Math.max(0, Number(this.multicolorWasteRate) || 0) : 0;
                    return grams * (1 + ((baseWaste + multiWaste) / 100));
                },
                get filamentCost() {
                    const spool = Math.max(1, Number(this.spoolGrams) || 1000);
                    return ((Number(this.filamentPricePerKg) || 0) / spool) * this.effectiveFilamentGrams;
                },
                get energyKwh() {
                    return ((Number(this.printerWatts) || 0) / 1000) * this.totalPrintHours;
                },
                get electricityCost() {
                    return this.energyKwh * (Number(this.electricityCostPerKwh) || 0);
                },
                get wearCost() {
                    return this.totalPrintHours * (Number(this.wearCostPerHour) || 0);
                },
                get laborCost() {
                    return (this.totalLaborHours * (Number(this.laborRatePerHour) || 0)) + this.extraWorkerCost;
                },
                get fixedCostPerPiece() {
                    const fixed = Math.max(0, Number(this.monthlyFixedCost) || 0);
                    const pieces = Math.max(1, Number(this.monthlyEstimatedPieces) || 100);
                    return fixed / pieces;
                },
                get suppliesAmount() {
                    const itemsSum = this.extraCostItems.reduce((acc, item) => acc + (Number(item.amount) || 0), 0);
                    return (Number(this.suppliesCost) || 0) + itemsSum;
                },
                get operatingSubtotal() {
                    return this.filamentCost +
                           this.electricityCost +
                           this.wearCost +
                           this.laborCost +
                           this.fixedCostPerPiece +
                           (Number(this.designCost) || 0) +
                           (Number(this.postProcessCost) || 0) +
                           (Number(this.packagingCost) || 0) +
                           this.suppliesAmount;
                },
                get failureAmount() {
                    return this.operatingSubtotal * ((Number(this.failureMarginRate) || 0) / 100);
                },
                get totalCost() {
                    return this.operatingSubtotal + this.failureAmount;
                },
                get costBreakdownPct() {
                    const base = this.totalCost;
                    if (base <= 0) {
                        return { material: 0, machine: 0, overhead: 0 };
                    }
                    const material = this.filamentCost;
                    const machine = this.electricityCost + this.wearCost;
                    const overhead = Math.max(0, base - material - machine);
                    return {
                        material: (material / base) * 100,
                        machine: (machine / base) * 100,
                        overhead: (overhead / base) * 100
                    };
                },
                get costPerGram() {
                    if (this.effectiveFilamentGrams <= 0) return 0;
                    return this.totalCost / this.effectiveFilamentGrams;
                },
                get costPerHour() {
                    if (this.totalPrintHours <= 0) return 0;
                    return this.totalCost / this.totalPrintHours;
                },
                get profitAmount() {
                    return this.totalCost * ((Number(this.profitMargin) || 0) / 100);
                },
                get commercialSubtotal() {
                    return this.totalCost + this.profitAmount;
                },
                get taxAmount() {
                    return this.commercialSubtotal * ((Number(this.taxRate) || 0) / 100);
                },
                get platformFeeAmount() {
                    return this.commercialSubtotal * ((Number(this.platformFeeRate) || 0) / 100);
                },
                get marketplaceExtraAmount() {
                    return this.commercialSubtotal * ((Number(this.marketplaceExtraRate) || 0) / 100);
                },
                get rawUnitPrice() {
                    return this.commercialSubtotal + this.taxAmount + this.platformFeeAmount + this.marketplaceExtraAmount;
                },
                get unitSuggestedPrice() {
                    let price = Math.max(this.rawUnitPrice, Number(this.minPrice) || 0);
                    const step = Number(this.roundStep) || 0;
                    if (step > 0) {
                        price = Math.ceil(price / step) * step;
                    }
                    return price;
                },
                get pricingRuleNote() {
                    const notes = [];
                    const floor = Number(this.minPrice) || 0;
                    if (floor > 0 && this.rawUnitPrice < floor) {
                        notes.push(`Ajustado a precio mínimo de ${this.formatCurrency(floor)}`);
                    }
                    const step = Number(this.roundStep) || 0;
                    const preRound = Math.max(this.rawUnitPrice, floor);
                    if (step > 0 && Math.abs(this.unitSuggestedPrice - preRound) > 0.001) {
                        notes.push(`Redondeado hacia arriba a múltiplos de ${this.currencySymbol}${step}`);
                    }
                    return notes.join(' · ');
                },
                get qty() {
                    return Math.max(1, Number(this.quantity) || 1);
                },
                get subtotalBeforeDiscount() {
                    return this.unitSuggestedPrice * this.qty;
                },
                get volumeDiscountApplied() {
                    return this.qty >= (Number(this.volumeMinQty) || 10) && (Number(this.volumeDiscountRate) || 0) > 0;
                },
                get volumeDiscountAmount() {
                    if (!this.volumeDiscountApplied) return 0;
                    return this.subtotalBeforeDiscount * ((Number(this.volumeDiscountRate) || 0) / 100);
                },
                get shippingAmount() {
                    return Math.max(0, Number(this.shippingCost) || 0);
                },
                get finalPrice() {
                    const baseTotal = this.subtotalBeforeDiscount - this.volumeDiscountAmount;
                    return this.shippingMode === 'included' ? baseTotal + this.shippingAmount : baseTotal;
                },
                get netProfitTotal() {
                    return Math.max(0, (this.profitAmount * this.qty) - this.volumeDiscountAmount);
                },
                get monthlyUnitsNeeded() {
                    const goal = Number(this.monthlyGoal) || 0;
                    if (goal <= 0 || this.profitAmount <= 0) return 0;
                    return Math.ceil(goal / this.profitAmount);
                },
                get monthlyFilamentGrams() {
                    return this.monthlyUnitsNeeded * this.effectiveFilamentGrams;
                },
                get monthlyFilamentKg() {
                    return this.monthlyFilamentGrams / 1000;
                },
                get monthlyMaterialInvestment() {
                    const spool = Math.max(1, Number(this.spoolGrams) || 1000);
                    return (this.monthlyFilamentGrams / spool) * (Number(this.filamentPricePerKg) || 0);
                },
                get filteredQuotes() {
                    const q = this.searchQuery.trim().toLowerCase();
                    return this.quotes.filter(item => {
                        if (!this.isAdmin && this.onlySentFilter && (item.status || 'draft') !== 'sent') {
                            return false;
                        }
                        if (!q) return true;
                        const project = (item.project_name || '').toLowerCase();
                        const modelName = (item.print_model?.name || '').toLowerCase();
                        const clientName = (item.user?.name || '').toLowerCase();
                        const email = (item.client_email || '').toLowerCase();
                        return project.includes(q) || modelName.includes(q) || clientName.includes(q) || email.includes(q);
                    });
                },
                formatCurrency(value) {
                    return this.currencySymbol + (isNaN(value) ? 0 : value).toFixed(2);
                },
                async copySummary() {
                    const lines = [
                        `${this.businessName || 'PrintFlow'} - ${this.projectName} (${this.currency})`,
                        `Cantidad: ${this.qty} ${this.qty === 1 ? 'pieza' : 'piezas'}`,
                        `Tiempo de impresión: ${this.totalPrintHours.toFixed(2)} hrs`,
                        `Material efectivo: ${this.effectiveFilamentGrams.toFixed(0)} g`,
                        `Precio unitario sugerido: ${this.formatCurrency(this.unitSuggestedPrice)}`,
                        `Total final: ${this.formatCurrency(this.finalPrice)}`
                    ];
                    if (this.shippingAmount > 0 && this.shippingMode === 'reference') {
                        lines.push(`Envío estimado (referencia): ${this.formatCurrency(this.shippingAmount)}`);
                    }
                    if (this.quickNote) {
                        lines.push(`Nota: ${this.quickNote}`);
                    }
                    const text = lines.join('\n');
                    try {
                        if (navigator.clipboard && window.isSecureContext) {
                            await navigator.clipboard.writeText(text);
                        } else {
                            const area = document.createElement('textarea');
                            area.value = text;
                            document.body.appendChild(area);
                            area.select();
                            document.execCommand('copy');
                            document.body.removeChild(area);
                        }
                        this.copied = true;
                        setTimeout(() => { this.copied = false; }, 2000);
                    } catch (e) {
                        console.error(e);
                        this.notify('No se pudo copiar automáticamente.', 'error');
                    }
                },
                exportPDF() {
                    window.print();
                },
                async saveQuote() {
                    this.loading = true;
                    try {
                        const response = await fetch('/quotes', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                print_model_id: this.selectedModelId,
                                client_email: this.clientEmail || null,
                                project_name: this.projectName,
                                print_time: Number(this.totalPrintHours.toFixed(2)),
                                filament_grams: Number(this.effectiveFilamentGrams.toFixed(2)),
                                design_cost: Number(this.designCost) || 0,
                                post_process_cost: Number(this.postProcessCost) || 0,
                                packaging_cost: Number(this.packagingCost) || 0,
                                total_cost: Number(this.totalCost.toFixed(2)),
                                total_price: this.finalPrice
                            })
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.notify(data.message, 'success');
                            this.quotes.unshift(data.quote);
                        } else {
                            this.notify('Error: ' + (data.message || 'Error desconocido al guardar.'), 'error');
                        }
                    } catch (error) {
                        console.error(error);
                        this.notify('Error de conexión con el servidor.', 'error');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>