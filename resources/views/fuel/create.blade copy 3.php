<x-app-layout>
    <div class="max-w-md mx-auto pb-20" x-data="{
            price: '',
            liters: '',
            date: '{{ date('Y-m-d') }}',
            location_name: '',
            odometer: '',
            fuel_type: '{{ $vehicle ? $vehicle->fuel_type_default : 'Pertalite' }}',
            isScanning: false,
            isAiGenerated: false,
            scanError: null,
            receiptImage: null,
            toast: { show: false, message: '', type: 'success' },

            get total() {
                return (this.price && this.liters) ? Math.round(parseFloat(this.price) * parseFloat(this.liters)) : ''
            },

            get isFormValid() {
                return this.odometer && this.price && this.liters;
            },

            showNotification(message, type = 'success') {
                this.toast = { show: true, message: message, type: type };
                setTimeout(() => { this.toast.show = false; }, 3000);
            },

            async scanReceipt(event) {
                let file = event.target.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    this.showNotification('File harus berupa gambar', 'error');
                    return;
                }

                this.isScanning = true;
                this.scanError = null;

                try {
                    let fileToUpload = file;

                    if (file.size > 1 * 1024 * 1024) {
                        const options = {
                            maxSizeMB: 0.5,
                            maxWidthOrHeight: 1024,
                            useWebWorker: true,
                            fileType: 'image/jpeg',
                            quality: 0.8
                        };
                        fileToUpload = await imageCompression(file, options);
                    }

                    let formData = new FormData();
                    formData.append('receipt', fileToUpload);

                    const response = await fetch('{{ route('ai.scan') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.error || 'Gagal scan struk');
                    }

                    const data = result.data;
                    this.isAiGenerated = true;
                    let filledCount = 0;

                    if (data.date && data.date !== 'null') {
                        this.date = data.date;
                        filledCount++;
                    }
                    if (data.location_name && data.location_name !== 'null') {
                        this.location_name = data.location_name;
                        filledCount++;
                    }
                    if (data.price_per_liter && data.price_per_liter > 0) {
                        this.price = data.price_per_liter;
                        filledCount++;
                    }
                    if (data.liters && data.liters > 0) {
                        this.liters = data.liters;
                        filledCount++;
                    }
                    if (data.odometer && data.odometer > 0) {
                        this.odometer = data.odometer;
                        filledCount++;
                    }

                    if (data.fuel_type) {
                        let ft = data.fuel_type.toLowerCase();
                        const fuelMappings = {
                            'pertalite': 'Pertalite',
                            'pertamax': 'Pertamax',
                            'pertamax turbo': 'Pertamax Turbo',
                            'dexlite': 'Dexlite',
                            'pertamina dex': 'Pertamina Dex',
                            'shell super': 'Shell Super',
                            'shell v-power': 'Shell V-Power',
                            'bp 92': 'BP 92',
                            'bp 95': 'BP 95'
                        };
                        this.fuel_type = fuelMappings[ft] || ft.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                        filledCount++;
                    }

                    this.showNotification(`✓ Berhasil mengisi ${filledCount} field`, 'success');

                } catch (e) {
                    console.error('Scan error:', e);
                    this.scanError = e.message;
                    this.showNotification(e.message, 'error');
                    this.isAiGenerated = false;
                } finally {
                    this.isScanning = false;
                    event.target.value = '';
                    if (this.scanError) {
                        setTimeout(() => { this.scanError = null; }, 5000);
                    }
                }
            }
        }" @buka-kamera.window="$refs.fileInput.click()">

        <!-- Header -->
        <div class="bg-white border-b border-gray-100 px-4 py-4 sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="p-2 -ml-2 rounded-full hover:bg-gray-100 transition">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-gas-black">Tambah Pengisian</h1>
                    <p class="text-xs text-gray-400">Catat pengisian BBM kendaraan</p>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <!-- Toast Notification -->
            <div x-show="toast.show" x-transition class="fixed bottom-24 left-4 right-4 z-50" style="display: none;">
                <div class="rounded-2xl px-4 py-3 shadow-xl flex items-center gap-3 backdrop-blur-md"
                    :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
                    <div class="flex-shrink-0">
                        <svg x-show="toast.type === 'success'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <svg x-show="toast.type === 'error'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <p class="flex-1 text-sm font-medium text-white" x-text="toast.message"></p>
                    <button @click="toast.show = false" class="flex-shrink-0">
                        <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Loading Overlay -->
            <div x-show="isScanning" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex flex-col items-center justify-center text-white" style="display: none;">
                <div class="w-14 h-14 border-4 border-gas-green border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="font-semibold tracking-wide animate-pulse">Menganalisa Struk...</p>
            </div>

            <!-- Info Kendaraan Aktif -->
            <div class="bg-gradient-to-r from-gas-black to-gray-800 text-white p-5 rounded-2xl shadow-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-[10px] opacity-60 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            Kendaraan Aktif
                        </p>
                        <p class="font-bold text-lg">{{ $vehicle ? $vehicle->name : 'Belum ada kendaraan' }}</p>
                        <p class="text-xs opacity-70">{{ $vehicle ? $vehicle->license_plate : '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] opacity-60">Odometer Saat Ini</p>
                        <p class="text-3xl font-black tracking-tight">{{ $vehicle ? number_format($vehicle->odometer_initial, 0, ',', '.') : '0' }} <span class="text-sm font-normal">KM</span></p>
                    </div>
                </div>
            </div>

            @if($vehicle)
                <input type="file" x-ref="fileInput" accept="image/*" capture="environment" class="hidden" @change="scanReceipt">

                <form action="{{ route('fuel.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                    <!-- Tombol Scan Struk -->
                    <button type="button" @click="$refs.fileInput.click()"
                        class="w-full bg-gradient-to-r from-gas-green to-green-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-green-200 active:scale-[0.98] transition-transform flex items-center justify-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>Scan Struk dengan Kamera</span>
                    </button>
                    <p class="text-center text-[10px] text-gray-400 -mt-2">Scan struk SPBU untuk isi otomatis</p>

                    <!-- Form Fields dengan efek disabled look -->
                    <div class="space-y-3">
                        <!-- Tanggal & Odometer -->
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Tanggal -->
                            <div class="relative group">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-gas-green to-green-500 rounded-2xl opacity-0 group-focus-within:opacity-100 transition duration-300"></div>
                                <div class="relative bg-white p-4 rounded-2xl border transition-all duration-200"
                                    :class="{
                                        'border-green-300 bg-green-50/30': isAiGenerated,
                                        'border-gray-200': !isAiGenerated && date,
                                        'border-gray-200 bg-gray-50': !date
                                    }">
                                    <label class="text-[10px] font-bold uppercase tracking-widest flex items-center gap-1"
                                        :class="date ? 'text-gas-green' : 'text-gray-400'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Tanggal
                                    </label>
                                    <input type="date" name="date" x-model="date" required
                                        class="w-full bg-transparent font-bold text-sm focus:outline-none py-1"
                                        :class="!date ? 'text-gray-400' : 'text-gray-700'">
                                </div>
                            </div>

                            <!-- Odometer -->
                            <div class="relative group">
                                <div class="relative bg-white p-4 rounded-2xl border transition-all duration-200"
                                    :class="odometer ? 'border-gray-200' : 'border-gray-200 bg-gray-50'">
                                    <label class="text-[10px] font-bold uppercase tracking-widest flex items-center gap-1"
                                        :class="odometer ? 'text-gas-green' : 'text-gray-400'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        Odometer (KM)
                                    </label>
                                    <input type="number" name="odometer" x-model="odometer" required
                                        placeholder="Masukkan odometer"
                                        class="w-full bg-transparent font-bold text-sm focus:outline-none py-1"
                                        :class="!odometer ? 'text-gray-400 placeholder-gray-300' : 'text-gray-700'">
                                </div>
                            </div>
                        </div>

                        <!-- Lokasi SPBU -->
                        <div class="relative group">
                            <div class="relative bg-white p-4 rounded-2xl border transition-all duration-200"
                                :class="{
                                    'border-green-300 bg-green-50/30': isAiGenerated,
                                    'border-gray-200': !isAiGenerated && location_name,
                                    'border-gray-200 bg-gray-50': !location_name
                                }">
                                <label class="text-[10px] font-bold uppercase tracking-widest flex items-center gap-1"
                                    :class="location_name ? 'text-gas-green' : 'text-gray-400'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Lokasi SPBU
                                </label>
                                <input type="text" name="location_name" x-model="location_name"
                                    placeholder="Contoh: SPBU Pertamina, Shell, BP"
                                    class="w-full bg-transparent font-bold text-sm focus:outline-none py-1"
                                    :class="!location_name ? 'text-gray-400 placeholder-gray-300' : 'text-gray-700'">
                            </div>
                        </div>

                        <!-- Jenis BBM -->
                        <div class="relative group">
                            <div class="relative bg-white p-4 rounded-2xl border transition-all duration-200"
                                :class="{
                                    'border-green-300 bg-green-50/30': isAiGenerated,
                                    'border-gray-200': fuel_type && fuel_type !== 'Pertalite',
                                    'border-gray-200 bg-gray-50': !fuel_type || fuel_type === 'Pertalite'
                                }">
                                <label class="text-[10px] font-bold uppercase tracking-widest flex items-center gap-1"
                                    :class="fuel_type && fuel_type !== 'Pertalite' ? 'text-gas-green' : 'text-gray-400'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Jenis BBM
                                </label>
                                <input type="text" name="fuel_type" x-model="fuel_type"
                                    placeholder="Contoh: Pertalite, Shell Super, BP 92"
                                    class="w-full bg-transparent font-bold text-sm focus:outline-none py-1"
                                    :class="!fuel_type || fuel_type === 'Pertalite' ? 'text-gray-400 placeholder-gray-300' : 'text-gray-700'">
                            </div>
                        </div>

                        <!-- Harga & Liter -->
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Harga/Liter -->
                            <div class="relative group">
                                <div class="relative bg-white p-4 rounded-2xl border transition-all duration-200"
                                    :class="{
                                        'border-green-300 bg-green-50/30': isAiGenerated,
                                        'border-gray-200': price,
                                        'border-gray-200 bg-gray-50': !price
                                    }">
                                    <label class="text-[10px] font-bold uppercase tracking-widest flex items-center gap-1"
                                        :class="price ? 'text-gas-green' : 'text-gray-400'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Harga/Liter
                                    </label>
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-400 text-sm">Rp</span>
                                        <input type="number" name="price_per_liter" x-model="price" required step="1"
                                            placeholder="0"
                                            class="w-full bg-transparent font-bold text-sm focus:outline-none py-1"
                                            :class="!price ? 'text-gray-400 placeholder-gray-300' : 'text-gray-700'">
                                    </div>
                                </div>
                            </div>

                            <!-- Liter -->
                            <div class="relative group">
                                <div class="relative bg-white p-4 rounded-2xl border transition-all duration-200"
                                    :class="{
                                        'border-green-300 bg-green-50/30': isAiGenerated,
                                        'border-gray-200': liters,
                                        'border-gray-200 bg-gray-50': !liters
                                    }">
                                    <label class="text-[10px] font-bold uppercase tracking-widest flex items-center gap-1"
                                        :class="liters ? 'text-gas-green' : 'text-gray-400'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 13H9L8 4z"></path>
                                        </svg>
                                        Liter
                                    </label>
                                    <input type="number" step="0.001" name="liters" x-model="liters" required
                                        placeholder="0,000"
                                        class="w-full bg-transparent font-bold text-sm focus:outline-none py-1"
                                        :class="!liters ? 'text-gray-400 placeholder-gray-300' : 'text-gray-700'">
                                </div>
                            </div>
                        </div>

                        <!-- Total Harga (Readonly) -->
                        <div class="relative">
                            <div class="bg-gradient-to-r from-gray-100 to-gray-50 p-4 rounded-2xl border border-gray-200">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm0 0v-4"></path>
                                    </svg>
                                    Total Harga
                                </label>
                                <div class="flex items-center gap-1 mt-1">
                                    <span class="text-gray-500 text-sm">Rp</span>
                                    <input type="number" name="total_price" :value="total" readonly
                                        class="w-full bg-transparent font-black text-2xl focus:outline-none py-1 text-gas-green"
                                        :placeholder="'0'">
                                </div>
                                <p class="text-[8px] text-gray-400 mt-1">*Terhitung otomatis dari harga x liter</p>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="receipt_image" x-model="receiptImage">
                    <input type="hidden" name="is_ai_generated" x-model="isAiGenerated">

                    <!-- Submit Button -->
                    <button type="submit"
                        :disabled="!isFormValid"
                        class="w-full font-black py-4 rounded-2xl transition-all duration-200 text-lg"
                        :class="isFormValid
                            ? 'bg-gradient-to-r from-gas-green to-green-600 text-white shadow-lg shadow-green-200 active:scale-[0.98] cursor-pointer'
                            : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
                        <span x-show="!isFormValid" class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            Lengkapi Data Terlebih Dahulu
                        </span>
                        <span x-show="isFormValid" class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            SIMPAN DATA
                        </span>
                    </button>
                </form>
            @else
                <div class="bg-red-50 border border-red-100 rounded-2xl p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-red-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p class="text-sm font-bold text-red-600 mb-2">Belum Ada Kendaraan</p>
                    <p class="text-xs text-gray-500 mb-4">Tambahkan kendaraan terlebih dahulu untuk memulai</p>
                    <a href="{{ route('vehicles.index') }}" class="inline-block bg-gas-green text-white px-6 py-2 rounded-xl text-sm font-bold">
                        + Tambah Kendaraan
                    </a>
                </div>
            @endif
        </div>

        <x-bottom-nav />
    </div>
</x-app-layout>
