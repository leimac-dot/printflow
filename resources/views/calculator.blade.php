<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrintFlow - 3D Printing Cost Calculator</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans antialiased" x-data="printCalculator()">

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8 border-b border-slate-800 pb-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                    <span class="bg-indigo-600 text-white p-2 rounded-lg text-xl">🖨️</span> PrintFlow
                </h1>
                <p class="text-sm text-slate-400 mt-1">Calculadora de costos profesional para impresión 3D</p>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    ● Sistema Activo
                </span>
            </div>
        </header>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Columna 1 & 2: Formulario de Parámetros -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Datos del Proyecto -->
                <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-lg font-semibold text-indigo-400 mb-4 flex items-center gap-2">
                        <span>📝</span> Detalles del Proyecto
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Nombre del Proyecto</label>
                            <input type="text" x-model="projectName" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Ej. Soporte para audífonos">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Tiempo de Impresión (Horas)</label>
                            <input type="number" step="0.1" x-model.number="printHours" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="5.5">
                        </div>
                    </div>
                </div>

                <!-- Costo de Filamento -->
                <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-lg font-semibold text-indigo-400 mb-4 flex items-center gap-2">
                        <span>🧵</span> Material (Filamento)
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Precio por Bobina ($ / kg)</label>
                            <input type="number" step="0.01" x-model.number="filamentPricePerKg" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Peso Utilizado (Gramos)</label>
                            <input type="number" step="1" x-model.number="filamentGrams" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Impresora y Electricidad -->
                <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-lg font-semibold text-indigo-400 mb-4 flex items-center gap-2">
                        <span>⚡</span> Impresora y Consumo Eléctrico
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Consumo (Watts)</label>
                            <input type="number" x-model.number="printerWatts" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Costo kWh ($)</label>
                            <input type="number" step="0.01" x-model.number="electricityCostPerKwh" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Mantenimiento / Desgaste ($/hora)</label>
                            <input type="number" step="0.01" x-model.number="wearCostPerHour" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Márgenes y Comercialización -->
                <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-lg font-semibold text-indigo-400 mb-4 flex items-center gap-2">
                        <span>📈</span> Márgenes y Ganancia
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Margen de Ganancia (%)</label>
                            <input type="number" x-model.number="profitMargin" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Tarifa por Hora de Operación ($/h)</label>
                            <input type="number" step="0.01" x-model.number="laborRatePerHour" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna 3: Resultados y Desglose en Tiempo Real -->
            <div class="lg:col-span-1">
                <div class="bg-gradient-to-br from-slate-800 to-indigo-950 border border-indigo-500/30 rounded-2xl p-6 shadow-2xl sticky top-8">
                    <h2 class="text-xl font-bold text-white mb-6 border-b border-indigo-500/20 pb-3 flex items-center gap-2">
                        <span>📊</span> Resumen Financiero
                    </h2>

                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between items-center text-slate-300">
                            <span>Costo de Filamento:</span>
                            <span class="font-semibold text-white" x-text="formatCurrency(filamentCost)">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-300">
                            <span>Costo de Electricidad:</span>
                            <span class="font-semibold text-white" x-text="formatCurrency(electricityCost)">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-300">
                            <span>Desgaste de Impresora:</span>
                            <span class="font-semibold text-white" x-text="formatCurrency(wearCost)">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-300">
                            <span>Mano de Obra / Operación:</span>
                            <span class="font-semibold text-white" x-text="formatCurrency(laborCost)">$0.00</span>
                        </div>

                        <div class="border-t border-indigo-500/20 pt-4 flex justify-between items-center text-base font-medium text-slate-200">
                            <span>Costo Total Base:</span>
                            <span class="font-bold text-indigo-300" x-text="formatCurrency(totalCost)">$0.00</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-300">
                            <span>Ganancia (<span x-text="profitMargin"></span>%):</span>
                            <span class="font-semibold text-emerald-400" x-text="formatCurrency(profitAmount)">$0.00</span>
                        </div>

                        <div class="border-t border-indigo-500/20 pt-4 mt-4 bg-indigo-900/40 p-4 rounded-xl border border-indigo-500/30 text-center">
                            <span class="block text-xs uppercase tracking-wider text-indigo-300 font-semibold mb-1">Precio Final Sugerido</span>
                            <span class="text-3xl font-extrabold text-white" x-text="formatCurrency(finalPrice)">$0.00</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button @click="alert('Cotización calculada con éxito. Próximamente guardada en base de datos.')" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 px-4 rounded-xl shadow-lg transition duration-200 flex items-center justify-center gap-2">
                            <span>💾</span> Guardar Cotización
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Alpine.js Logic -->
    <script>
        function printCalculator() {
            return {
                projectName: 'Mi Impresión 3D',
                printHours: 5,
                filamentPricePerKg: 450,
                filamentGrams: 75,
                printerWatts: 120,
                electricityCostPerKwh: 3.50,
                wearCostPerHour: 2.00,
                profitMargin: 40,
                laborRatePerHour: 25.00,

                get filamentCost() {
                    return (this.filamentPricePerKg / 1000) * this.filamentGrams;
                },
                get electricityCost() {
                    return (this.printerWatts / 1000) * this.printHours * this.electricityCostPerKwh;
                },
                get wearCost() {
                    return this.printHours * this.wearCostPerHour;
                },
                get laborCost() {
                    return this.printHours * this.laborRatePerHour;
                },
                get totalCost() {
                    return this.filamentCost + this.electricityCost + this.wearCost + this.laborCost;
                },
                get profitAmount() {
                    return this.totalCost * (this.profitMargin / 100);
                },
                get finalPrice() {
                    return this.totalCost + this.profitAmount;
                },
                formatCurrency(value) {
                    return '$' + (isNaN(value) ? 0 : value).toFixed(2);
                }
            }
        }
    </script>
</body>
</html>