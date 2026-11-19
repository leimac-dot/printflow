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
    <!-- Token CSRF de Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Estilos específicos para impresión / PDF -->
    <style>
        @media print {
            body { background: white !important; color: black !important; }
            .no-print { display: none !important; }
            .print-container { box-shadow: none !important; border: none !important; }
        }
    </style>
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
            <div class="text-right no-print">
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Nombre del Proyecto</label>
                            <input type="text" x-model="projectName" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Ej. Soporte para audífonos">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Tiempo de Impresión (Horas)</label>
                            <input type="number" step="0.1" x-model.number="printHours" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="5.5">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Cantidad de Piezas</label>
                            <input type="number" min="1" step="1" x-model.number="quantity" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
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

                <!-- Desglose Operativo Avanzado (Diseño, Post-proceso, Empaque) -->
                <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-lg font-semibold text-indigo-400 mb-4 flex items-center gap-2">
                        <span>🛠️</span> Costos Operativos y Extra (Avanzado)
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Costo de Diseño ($)</label>
                            <input type="number" step="0.01" x-model.number="designCost" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Post-Procesado ($)</label>
                            <input type="number" step="0.01" x-model.number="postProcessCost" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Empaque y Caja ($)</label>
                            <input type="number" step="0.01" x-model.number="packagingCost" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <!-- Márgenes y Comercialización -->
                <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-lg font-semibold text-indigo-400 mb-4 flex items-center gap-2">
                        <span>📈</span> Márgenes y Comercialización
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
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Impuestos (%)</label>
                            <input type="number" step="0.1" min="0" x-model.number="taxRate" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="16">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Comisión de Plataforma (%)</label>
                            <input type="number" step="0.1" min="0" x-model.number="platformFeeRate" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="2.9">
                        </div>
                    </div>
                </div>

                <!-- Reglas de Precio: Mínimo, Redondeo y Volumen -->
                <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl no-print">
                    <h2 class="text-lg font-semibold text-indigo-400 mb-4 flex items-center gap-2">
                        <span>🏷️</span> Reglas de Precio y Volumen
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Precio Mínimo ($)</label>
                            <input type="number" step="1" min="0" x-model.number="minPrice" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Redondeo Unitario</label>
                            <select x-model.number="roundStep" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <option value="0">Sin redondeo</option>
                                <option value="5">A múltiplos de $5</option>
                                <option value="10">A múltiplos de $10</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">A partir de (Piezas)</label>
                            <input type="number" min="1" step="1" x-model.number="volumeMinQty" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Descuento Volumen (%)</label>
                            <input type="number" min="0" max="90" step="1" x-model.number="volumeDiscountRate" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>
                    <p class="text-xs text-emerald-400 mt-3" x-show="volumeDiscountApplied">
                        ✓ Descuento por volumen activo (<span x-text="volumeDiscountRate"></span>%) por pedir <span x-text="quantity"></span> piezas.
                    </p>
                </div>

                <!-- Metas Mensuales y Planeación de Producción -->
                <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl no-print">
                    <h2 class="text-lg font-semibold text-indigo-400 mb-4 flex items-center gap-2">
                        <span>🎯</span> Metas Mensuales de Ganancia
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-1">Meta Mensual ($)</label>
                            <input type="number" step="100" min="0" x-model.number="monthlyGoal" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div class="bg-slate-900/70 border border-slate-700/60 rounded-xl p-3">
                            <span class="block text-xs text-slate-400">Piezas a Vender</span>
                            <span class="text-xl font-bold text-white" x-text="monthlyUnitsNeeded">0</span>
                        </div>
                        <div class="bg-slate-900/70 border border-slate-700/60 rounded-xl p-3">
                            <span class="block text-xs text-slate-400">Filamento Necesario</span>
                            <span class="text-xl font-bold text-indigo-300" x-text="monthlyFilamentKg.toFixed(2) + ' kg'">0.00 kg</span>
                        </div>
                        <div class="bg-slate-900/70 border border-slate-700/60 rounded-xl p-3">
                            <span class="block text-xs text-slate-400">Inversión en Material</span>
                            <span class="text-xl font-bold text-emerald-400" x-text="formatCurrency(monthlyMaterialInvestment)">$0.00</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-3" x-show="profitAmount <= 0">
                        Ajusta un margen de ganancia mayor a 0% para calcular cuántas piezas necesitas vender.
                    </p>
                </div>

                <!-- Historial de Cotizaciones -->
                <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 shadow-xl no-print">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <h2 class="text-lg font-semibold text-indigo-400 flex items-center gap-2">
                            <span>📋</span> Historial de Cotizaciones Recientes
                        </h2>
                        <input type="text" x-model="searchQuery" placeholder="Buscar proyecto..." class="bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-300">
                            <thead class="bg-slate-900/60 text-xs uppercase text-slate-400 border-b border-slate-700">
                                <tr>
                                    <th class="px-4 py-3">Proyecto</th>
                                    <th class="px-4 py-3">Tiempo (h)</th>
                                    <th class="px-4 py-3">Precio Final</th>
                                    <th class="px-4 py-3">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                <template x-if="filteredQuotes.length === 0">
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-slate-500">No hay cotizaciones que coincidan.</td>
                                    </tr>
                                </template>
                                <template x-for="quote in filteredQuotes" :key="quote.id">
                                    <tr class="hover:bg-slate-900/30 transition">
                                        <td class="px-4 py-3 font-medium text-white" x-text="quote.project_name"></td>
                                        <td class="px-4 py-3" x-text="quote.print_time_hours + ' hrs'"></td>
                                        <td class="px-4 py-3 font-bold text-emerald-400" x-text="'$' + Number(quote.final_price).toFixed(2)"></td>
                                        <td class="px-4 py-3 text-xs text-slate-400" x-text="new Date(quote.created_at).toLocaleDateString()"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Columna 3: Resultados, Desglose y Acciones -->
            <div class="lg:col-span-1">
                <div class="bg-gradient-to-br from-slate-800 to-indigo-950 border border-indigo-500/30 rounded-2xl p-6 shadow-2xl sticky top-8">
                    <h2 class="text-xl font-bold text-white mb-6 border-b border-indigo-500/20 pb-3 flex items-center gap-2">
                        <span>📊</span> Resumen Financiero
                    </h2>

                    <div class="space-y-3 text-sm">
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
                        <div class="flex justify-between items-center text-slate-300">
                            <span>Diseño / Post-proceso:</span>
                            <span class="font-semibold text-white" x-text="formatCurrency(designCost + postProcessCost)">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-300">
                            <span>Empaque y Caja:</span>
                            <span class="font-semibold text-white" x-text="formatCurrency(packagingCost)">$0.00</span>
                        </div>

                        <div class="border-t border-indigo-500/20 pt-3 flex justify-between items-center text-base font-medium text-slate-200">
                            <span>Costo Total Base:</span>
                            <span class="font-bold text-indigo-300" x-text="formatCurrency(totalCost)">$0.00</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-300">
                            <span>Ganancia (<span x-text="profitMargin"></span>%):</span>
                            <span class="font-semibold text-emerald-400" x-text="formatCurrency(profitAmount)">$0.00</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-300">
                            <span>Impuestos (<span x-text="taxRate"></span>%):</span>
                            <span class="font-semibold text-amber-300" x-text="formatCurrency(taxAmount)">$0.00</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-300">
                            <span>Comisión Plataforma (<span x-text="platformFeeRate"></span>%):</span>
                            <span class="font-semibold text-amber-300" x-text="formatCurrency(platformFeeAmount)">$0.00</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-300" x-show="quantity > 1">
                            <span>Precio Unitario Ajustado:</span>
                            <span class="font-semibold text-indigo-200" x-text="formatCurrency(unitSuggestedPrice)">$0.00</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-300" x-show="volumeDiscountApplied">
                            <span>Descuento Volumen (<span x-text="volumeDiscountRate"></span>%):</span>
                            <span class="font-semibold text-emerald-400" x-text="'-' + formatCurrency(volumeDiscountAmount)">$0.00</span>
                        </div>

                        <div class="border-t border-indigo-500/20 pt-4 mt-4 bg-indigo-900/40 p-4 rounded-xl border border-indigo-500/30 text-center">
                            <span class="block text-xs uppercase tracking-wider text-indigo-300 font-semibold mb-1">
                                Precio Final Sugerido (<span x-text="quantity"></span> <span x-text="quantity === 1 ? 'pieza' : 'piezas'"></span>)
                            </span>
                            <span class="text-3xl font-extrabold text-white" x-text="formatCurrency(finalPrice)">$0.00</span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <button @click="saveQuote()" :disabled="loading" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 px-4 rounded-xl shadow-lg transition duration-200 flex items-center justify-center gap-2 disabled:opacity-50">
                            <span x-show="!loading">💾 Guardar Cotización</span>
                            <span x-show="loading">Guardando...</span>
                        </button>

                        <button @click="exportPDF()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 px-4 rounded-xl shadow-lg transition duration-200 flex items-center justify-center gap-2 no-print">
                            <span>📄 Descargar / Imprimir PDF</span>
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
                quantity: 1,
                filamentPricePerKg: 450,
                filamentGrams: 75,
                printerWatts: 120,
                electricityCostPerKwh: 3.50,
                wearCostPerHour: 2.00,
                designCost: 0,
                postProcessCost: 0,
                packagingCost: 0,
                profitMargin: 40,
                laborRatePerHour: 25.00,
                taxRate: 16,
                platformFeeRate: 2.9,
                minPrice: 50,
                roundStep: 5,
                volumeMinQty: 10,
                volumeDiscountRate: 10,
                monthlyGoal: 10000,
                searchQuery: '',
                loading: false,
                
                quotes: @json($quotes),

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
                    return this.filamentCost + 
                           this.electricityCost + 
                           this.wearCost + 
                           this.laborCost + 
                           (Number(this.designCost) || 0) + 
                           (Number(this.postProcessCost) || 0) + 
                           (Number(this.packagingCost) || 0);
                },
                get profitAmount() {
                    return this.totalCost * (this.profitMargin / 100);
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
                get rawUnitPrice() {
                    return this.commercialSubtotal + this.taxAmount + this.platformFeeAmount;
                },
                get unitSuggestedPrice() {
                    let price = Math.max(this.rawUnitPrice, Number(this.minPrice) || 0);
                    const step = Number(this.roundStep) || 0;
                    if (step > 0) {
                        price = Math.ceil(price / step) * step;
                    }
                    return price;
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
                get finalPrice() {
                    return this.subtotalBeforeDiscount - this.volumeDiscountAmount;
                },
                get monthlyUnitsNeeded() {
                    const goal = Number(this.monthlyGoal) || 0;
                    if (goal <= 0 || this.profitAmount <= 0) return 0;
                    return Math.ceil(goal / this.profitAmount);
                },
                get monthlyFilamentGrams() {
                    return this.monthlyUnitsNeeded * (Number(this.filamentGrams) || 0);
                },
                get monthlyFilamentKg() {
                    return this.monthlyFilamentGrams / 1000;
                },
                get monthlyMaterialInvestment() {
                    return this.monthlyFilamentKg * (Number(this.filamentPricePerKg) || 0);
                },
                get filteredQuotes() {
                    const q = this.searchQuery.trim().toLowerCase();
                    if (!q) return this.quotes;
                    return this.quotes.filter(item =>
                        (item.project_name || '').toLowerCase().includes(q)
                    );
                },
                formatCurrency(value) {
                    return '$' + (isNaN(value) ? 0 : value).toFixed(2);
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
                                project_name: this.projectName,
                                print_time: this.printHours,
                                design_cost: Number(this.designCost) || 0,
                                post_process_cost: Number(this.postProcessCost) || 0,
                                packaging_cost: Number(this.packagingCost) || 0,
                                total_price: this.finalPrice
                            })
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            alert(data.message);
                            this.quotes.unshift(data.quote);
                        } else {
                            alert('Error: ' + (data.message || 'Error desconocido al guardar.'));
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Error de conexión con el servidor.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>