<x-app-layout>
    <div x-data="{
        editModalOpen: false,
        editUrl: '',
        price: '',
        liters: '',
        date: '',
        odometer: '',
        location_name: '',
        fuel_type: '',
        filterStartDate: '',
        filterEndDate: '',
        showFilter: false,


        get total() { return (this.price && this.liters) ? Math.round(this.price * this.liters) : '' },

        clearFilter() {
            this.filterStartDate = '';
            this.filterEndDate = '';
        },

        get filteredEntries() {
            let entries = [];
            // Ambil semua elemen entry
            const entryElements = document.querySelectorAll('[data-entry-id]');
            let visibleCount = 0;

            entryElements.forEach(el => {
                const entryDate = el.getAttribute('data-entry-date');
                const isVisible = (!this.filterStartDate || entryDate >= this.filterStartDate) &&
                                 (!this.filterEndDate || entryDate <= this.filterEndDate);
                el.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleCount++;
            });

            // Tampilkan pesan jika tidak ada
            const noDataMsg = document.getElementById('no-data-message');
            if (noDataMsg) {
                noDataMsg.style.display = visibleCount === 0 && (this.filterStartDate || this.filterEndDate) ? 'block' : 'none';
            }

            return visibleCount;
        }
    }">

        <section class="p-6 space-y-6 pb-24 relative">

            <header class="flex justify-between items-end mb-4">
                <div>
                    <h1 class="text-2xl font-black text-gas-black">Riwayat Pengisian</h1>
                    <p class="text-xs text-gray-500 font-bold mt-1">
                        {{ $vehicle ? $vehicle->name . ' (' . $vehicle->license_plate . ')' : 'Belum ada kendaraan' }}
                    </p>
                </div>
                @if ($entries && count($entries) > 0)
                    <span
                        class="text-[10px] font-bold text-gas-green uppercase tracking-widest bg-green-50 px-3 py-1 rounded-full">
                        {{ count($entries) }} Transaksi
                    </span>
                @endif
            </header>

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                    class="bg-green-50 text-green-600 text-xs font-bold p-3 rounded-2xl text-center shadow-sm mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filter Section -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <button @click="showFilter = !showFilter"
                    class="w-full px-4 py-3 flex items-center justify-between text-left">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gas-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span class="text-sm font-bold text-gas-black">Filter Tanggal</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': showFilter }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="showFilter" x-cloak class="px-4 pb-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Dari Tanggal</label>
                            <input type="date" x-model="filterStartDate" @change="filteredEntries"
                                class="w-full mt-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:outline-none focus:border-gas-green">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sampai Tanggal</label>
                            <input type="date" x-model="filterEndDate" @change="filteredEntries"
                                class="w-full mt-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:outline-none focus:border-gas-green">
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button @click="clearFilter(); filteredEntries"
                            class="flex-1 px-3 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition-colors">
                            Reset Filter
                        </button>
                        <button @click="showFilter = false"
                            class="flex-1 px-3 py-2 bg-gas-green text-white text-xs font-bold rounded-xl hover:bg-green-600 transition-colors">
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Badge Filter Aktif -->
            <div x-show="filterStartDate || filterEndDate" x-cloak class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-gas-green bg-green-50 px-2 py-1 rounded-full">
                    Filter aktif
                </span>
                <button @click="clearFilter(); filteredEntries" class="text-[10px] text-gray-400 hover:text-red-500">
                    ✕ Hapus filter
                </button>
            </div>

            <div class="space-y-4">
                @if (!$vehicle)
                    <div class="bg-red-50 border border-red-100 rounded-3xl p-6 text-center shadow-sm">
                        <p class="text-xs font-bold text-red-600">Pilih atau tambahkan kendaraan terlebih dahulu di Garasi.</p>
                    </div>
                @else
                    @forelse ($entries as $entry)
                        <div data-entry-id="{{ $entry->id }}"
                             data-entry-date="{{ $entry->date }}"
                             class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group hover:border-gas-green transition-colors">

                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center text-gas-green shrink-0">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                                    </svg>
                                </div>

                                <div class="flex-grow">
                                    <h4 class="font-bold text-sm text-gas-black line-clamp-1">
                                        {{ $entry->location_name ?: 'SPBU Tidak Diketahui' }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase">
                                            {{ \Carbon\Carbon::parse($entry->date)->translatedFormat('d M Y') }}
                                        </p>
                                        <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                        <p class="text-[10px] font-bold text-gas-green uppercase">
                                            {{ $entry->fuel_type }}</p>
                                    </div>
                                    <p class="text-[10px] font-bold text-gray-400 mt-1">KM:
                                        {{ number_format($entry->odometer, 0, ',', '.') }}</p>
                                </div>

                                <div class="text-right shrink-0">
                                    <p class="font-black text-sm text-gas-black">Rp
                                        {{ number_format($entry->total_price, 0, ',', '.') }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 mt-0.5">
                                        {{ number_format($entry->liters, 1, ',', '.') }} Liter</p>

                                    @if ($entry->kml > 0)
                                        <p class="text-[9px] font-black text-blue-500 bg-blue-50 inline-block px-2 py-0.5 rounded-full mt-1">
                                            {{ $entry->kml }} KM/L
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-50 flex justify-end gap-4 items-center">
                                <button type="button"
                                    @click="
                                        editModalOpen = true;
                                        editUrl = '{{ route('fuel.update', $entry->id) }}';
                                        date = '{{ $entry->date }}';
                                        odometer = '{{ $entry->odometer }}';
                                        location_name = '{{ $entry->location_name }}';
                                        fuel_type = '{{ $entry->fuel_type }}';
                                        price = '{{ $entry->price_per_liter }}';
                                        liters = '{{ $entry->liters }}';
                                    "
                                    class="text-xs font-bold text-blue-500 hover:text-blue-700 transition-colors uppercase tracking-wider flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                        </path>
                                    </svg>
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('fuel.destroy', $entry->id) }}"
                                    onsubmit="return confirm('Yakin ingin menghapus riwayat ini secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-xs font-bold text-red-500 hover:text-red-700 transition-colors uppercase tracking-wider flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl p-8 text-center">
                            <p class="text-sm font-bold text-gas-black">Belum ada riwayat</p>
                            <p class="text-[10px] font-medium text-gray-400 mt-1">Coba input bensin pertamamu di halaman Home.</p>
                        </div>
                    @endforelse

                    <!-- Pesan ketika tidak ada data dalam range filter -->
                    <div id="no-data-message"
                         x-show="false"
                         style="display: none;"
                         class="text-center py-8 bg-gray-50 rounded-2xl">
                        <p class="text-sm text-gray-500">Tidak ada transaksi dalam rentang tanggal yang dipilih</p>
                    </div>
                @endif
            </div>
        </section>


<!-- Modal Edit -->
<div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">

        <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" @click="editModalOpen = false"></div>

        <div x-show="editModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full p-6 z-50">

            <header class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-black text-gas-black">Edit Riwayat</h2>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gas-black transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </header>

            <form method="POST" :action="editUrl" class="space-y-3">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</label>
                        <input type="date" name="date" x-model="date" required class="w-full bg-transparent font-bold text-sm focus:outline-none py-1">
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Odometer (KM)</label>
                        <input type="number" name="odometer" x-model="odometer" required step="1" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lokasi SPBU</label>
                        <input type="text" name="location_name" x-model="location_name" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1" placeholder="Contoh: SPBU Pertamina, Shell, BP">
                    </div>

                    <!-- JENIS BBM - Langsung input teks (tanpa select) -->
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Jenis BBM</label>
                        <input type="text" name="fuel_type" x-model="fuel_type"
                            class="w-full bg-transparent font-bold text-sm focus:outline-none py-1"
                            placeholder="Contoh: Pertalite, Shell Super, BP 92, Diesel">
                        <p class="text-[8px] text-gray-400 mt-1">Isi manual sesuai jenis BBM</p>
                    </div>
                </div>

                <!-- Grid 3 kolom -->
                <div class="grid grid-cols-3 gap-2">
                    <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100">
                        <label class="text-[9px] font-bold text-gray-400 uppercase">Harga/Liter</label>
                        <input type="number" name="price_per_liter" x-model="price" required step="1"
                            class="w-full bg-transparent font-bold text-sm focus:outline-none py-1">
                    </div>
                    <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100">
                        <label class="text-[9px] font-bold text-gray-400 uppercase">Liter</label>
                        <input type="number" step="0.001" name="liters" x-model="liters" required
                            class="w-full bg-transparent font-bold text-sm focus:outline-none py-1">
                    </div>
                    <div class="bg-gray-100 p-3 rounded-2xl border border-gray-200">
                        <label class="text-[9px] font-bold text-gray-500 uppercase">Total (Rp)</label>
                        <input type="number" name="total_price" :value="total" readonly
                            class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 text-gray-600">
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" @click="editModalOpen = false" class="w-full bg-gray-100 text-gray-600 font-black py-4 rounded-2xl hover:bg-gray-200 transition-colors text-sm tracking-wider">
                        BATAL
                    </button>
                    <button type="submit" class="w-full bg-gas-black text-white font-black py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-transform text-sm tracking-wider">
                        SIMPAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    </div>

    <x-bottom-nav />
</x-app-layout>
