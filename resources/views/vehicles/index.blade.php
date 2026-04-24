<x-app-layout>
    <div x-data="{
            editModalOpen: false,
            addModalOpen: false,
            editUrl: '',
            form: { name: '', license_plate: '', fuel_type_default: '', odometer_initial: '' },
            addForm: { name: '', license_plate: '', fuel_type_default: 'Pertalite', odometer_initial: 0 },
            deleteConfirm: false,
            deleteForm: null,
            toast: { show: false, message: '', type: 'success' },

            showNotification(message, type = 'success') {
                this.toast = { show: true, message: message, type: type };
                setTimeout(() => { this.toast.show = false; }, 3000);
            },

            confirmDelete(form) {
                this.deleteForm = form;
                this.deleteConfirm = true;
            },

            deleteVehicle() {
                if (this.deleteForm) {
                    this.deleteForm.submit();
                }
                this.deleteConfirm = false;
            }
         }"
         class="max-w-md w-full mx-auto flex flex-col min-h-screen pb-12 relative">

        <!-- Toast Notification -->
        <div x-show="toast.show"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed bottom-24 left-4 right-4 z-50"
             style="display: none;">
            <div class="rounded-2xl px-4 py-3 shadow-xl flex items-center gap-3 backdrop-blur-md"
                 :class="{
                     'bg-green-500': toast.type === 'success',
                     'bg-red-500': toast.type === 'error'
                 }">
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
                    <svg class="w-4 h-4 text-white/80 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <header class="flex items-center justify-between p-6 bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm">
            <div class="flex items-center gap-4">
                <a href="{{ route('account') }}" class="p-2 bg-gray-50 rounded-xl text-gray-600 hover:text-gas-black hover:bg-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-gas-black tracking-tight">Kendaraan Saya</h1>
                </div>
            </div>
            <button @click="addModalOpen = true"
                class="bg-gas-green text-white p-2 rounded-xl hover:bg-green-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
        </header>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="mx-6 mt-6 bg-green-50 border border-green-100 text-green-600 text-xs font-bold p-4 rounded-2xl text-center shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="p-6 space-y-8">

            <!-- ==================== SWITCH KENDARAAN UTAMA ==================== -->
            @if($vehicles->count() > 0)
            <div class="bg-gas-black text-white p-5 rounded-3xl relative overflow-hidden shadow-lg shadow-gray-200">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <h3 class="text-[10px] font-bold text-gas-green uppercase tracking-widest">Kendaraan Utama</h3>
                        </div>
                        <span class="text-[8px] text-gray-400">Ganti kendaraan aktif</span>
                    </div>

                    <form action="{{ route('dashboard.switch_vehicle') }}" method="POST">
                        @csrf
                        <div class="relative">
                            <select name="vehicle_id" onchange="this.form.submit()"
                                class="w-full bg-white/10 border border-white/20 text-white font-bold text-sm rounded-2xl p-4 focus:outline-none appearance-none cursor-pointer backdrop-blur-sm">
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" {{ $v->is_active ? 'selected' : '' }} class="text-gas-black">
                                        {{ $v->name }} - {{ $v->license_plate }} {{ $v->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gas-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </form>

                    <div class="mt-3 flex items-center justify-center gap-1">
                        <span class="w-1.5 h-1.5 bg-gas-green rounded-full animate-pulse"></span>
                        <p class="text-[8px] text-gray-400">
                            Kendaraan yang dipilih akan menjadi default di halaman utama
                        </p>
                    </div>
                </div>
                <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/5 rounded-full"></div>
            </div>
            @endif
            <!-- ==================== END SWITCH KENDARAAN ==================== -->

            <section class="space-y-4">
                <header class="flex justify-between items-end mb-2">
                    <h2 class="text-lg font-bold text-gas-black">Garasi Kamu</h2>
                    <span class="text-xs font-bold text-gas-green">{{ $vehicles->count() }} Kendaraan</span>
                </header>

                @forelse ($vehicles as $vehicle)
                    <div class="bg-gas-black text-white p-5 rounded-3xl relative overflow-hidden flex flex-col justify-between h-36 shadow-lg shadow-gray-200">
                        <div class="relative z-10 flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-black text-lg">{{ $vehicle->name }}</h3>
                                    @if($vehicle->is_active)
                                        <span class="w-2 h-2 bg-gas-green rounded-full animate-pulse shadow-[0_0_8px_#1DB954]"></span>
                                    @endif
                                </div>
                                <p class="text-xs font-medium text-gray-400 mt-1">Odometer: {{ number_format($vehicle->odometer_initial, 0, ',', '.') }} KM</p>
                            </div>

                            <div class="flex gap-2">
                                <button type="button"
                                    @click="
                                        editModalOpen = true;
                                        editUrl = '{{ route('vehicles.update', $vehicle->id) }}';
                                        form.name = '{{ $vehicle->name }}';
                                        form.license_plate = '{{ $vehicle->license_plate }}';
                                        form.fuel_type_default = '{{ $vehicle->fuel_type_default }}';
                                        form.odometer_initial = '{{ $vehicle->odometer_initial }}';
                                    "
                                    class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm hover:bg-white/20 transition-colors">
                                    <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>

                                <!-- DELETE FORM - Menggunakan form submit biasa -->
                                <form method="POST" action="{{ route('vehicles.destroy', $vehicle->id) }}"
                                      x-ref="deleteForm{{ $vehicle->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        @click="confirmDelete($refs.deleteForm{{ $vehicle->id }})"
                                        class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm hover:bg-red-500/50 transition-colors">
                                        <svg class="w-4 h-4 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="relative z-10 flex gap-2 mt-auto items-center">
                            <span class="bg-white/10 backdrop-blur-sm text-[10px] px-3 py-1.5 rounded-full font-bold uppercase tracking-wider {{ $vehicle->is_active ? 'border-gas-green text-gas-green border' : 'text-white' }}">
                                {{ $vehicle->license_plate }}
                            </span>
                            @if($vehicle->is_active)
                                <span class="bg-gas-green text-[10px] px-3 py-1.5 rounded-full font-bold uppercase tracking-wider text-gas-black">Unit Utama</span>
                            @endif
                        </div>

                        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/5 rounded-full"></div>
                    </div>
                @empty
                    <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl p-8 text-center">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 17l.867 2.1c.228.552-.164 1.15-.765 1.15H4.898c-.601 0-.993-.598-.765-1.15L5 17m14 0c0-1.657-3.134-3-7-3S5 15.343 5 17m14 0V9a2 2 0 00-2-2h-3l-2-2H8L6 7H4a2 2 0 00-2 2v8"></path></svg>
                        <p class="text-sm font-bold text-gray-400">Garasi masih kosong.</p>
                        <p class="text-[10px] text-gray-400 mt-1">Klik tombol + di atas untuk menambahkan kendaraan.</p>
                    </div>
                @endforelse
            </section>
        </div>

        <!-- Modal Add Kendaraan -->
        <div x-show="addModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div x-show="addModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" @click="addModalOpen = false"></div>

                <div x-show="addModalOpen"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full p-6 z-50">

                    <header class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-black text-gas-black">Tambah Kendaraan</h2>
                        <button @click="addModalOpen = false" class="text-gray-400 hover:text-gas-black transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </header>

                    <form method="POST" action="{{ route('vehicles.store') }}" class="space-y-4">
                        @csrf

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Kendaraan</label>
                            <input type="text" name="name" x-model="addForm.name" required
                                class="w-full bg-transparent font-bold text-sm focus:outline-none py-1"
                                placeholder="Cth: Avanza Hitam">
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Plat Nomor</label>
                            <input type="text" name="license_plate" x-model="addForm.license_plate" required
                                class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 uppercase"
                                placeholder="Cth: B 1234 GAI">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">BBM Default</label>
                                <input type="text" name="fuel_type_default" x-model="addForm.fuel_type_default"
                                    class="w-full bg-transparent font-bold text-sm focus:outline-none py-1"
                                    placeholder="Contoh: Pertalite, Shell Super, BP 92">
                            </div>

                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">KM Awal</label>
                                <input type="number" name="odometer_initial" x-model="addForm.odometer_initial" required step="1" min="0"
                                    class="w-full bg-transparent font-bold text-sm focus:outline-none py-1" placeholder="0">
                            </div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="addModalOpen = false"
                                class="w-full bg-gray-100 text-gray-600 font-black py-4 rounded-2xl hover:bg-gray-200 transition-colors text-sm tracking-wider">
                                BATAL
                            </button>
                            <button type="submit"
                                class="w-full bg-gas-green text-white font-black py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-transform text-sm tracking-wider">
                                SIMPAN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Kendaraan -->
        <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" @click="editModalOpen = false"></div>

                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full p-6 z-50">

                    <header class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-black text-gas-black">Edit Kendaraan</h2>
                        <button @click="editModalOpen = false" class="text-gray-400 hover:text-gas-black transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </header>

                    <form method="POST" :action="editUrl" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Kendaraan</label>
                            <input type="text" name="name" x-model="form.name" required
                                class="w-full bg-transparent font-bold text-sm focus:outline-none py-1">
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Plat Nomor</label>
                            <input type="text" name="license_plate" x-model="form.license_plate" required
                                class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 uppercase">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">BBM Default</label>
                                <input type="text" name="fuel_type_default" x-model="form.fuel_type_default"
                                    class="w-full bg-transparent font-bold text-sm focus:outline-none py-1"
                                    placeholder="Contoh: Pertalite, Shell Super, BP 92">
                            </div>

                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">KM Awal</label>
                                <input type="number" name="odometer_initial" x-model="form.odometer_initial" required step="1" min="0"
                                    class="w-full bg-transparent font-bold text-sm focus:outline-none py-1">
                            </div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="editModalOpen = false"
                                class="w-full bg-gray-100 text-gray-600 font-black py-4 rounded-2xl hover:bg-gray-200 transition-colors text-sm tracking-wider">
                                BATAL
                            </button>
                            <button type="submit"
                                class="w-full bg-gas-black text-white font-black py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-transform text-sm tracking-wider">
                                SIMPAN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Confirm Delete -->
        <div x-show="deleteConfirm" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div x-show="deleteConfirm" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" @click="deleteConfirm = false"></div>

                <div x-show="deleteConfirm"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full p-6 z-50">

                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-gas-black mb-2">Hapus Kendaraan?</h3>
                        <p class="text-sm text-gray-500 mb-6">Semua riwayat pengisian kendaraan ini juga akan terhapus secara permanen.</p>
                        <div class="flex gap-3">
                            <button @click="deleteConfirm = false"
                                class="flex-1 bg-gray-100 text-gray-600 font-black py-3 rounded-2xl hover:bg-gray-200 transition-colors">
                                BATAL
                            </button>
                            <button @click="deleteVehicle()"
                                class="flex-1 bg-red-500 text-white font-black py-3 rounded-2xl hover:bg-red-600 transition-colors">
                                HAPUS
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
