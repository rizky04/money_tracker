<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.1/dist/browser-image-compression.js"></script>

    <div class="max-w-md mx-auto pb-20" x-data="{
            merchant: '',
            date: '{{ date('Y-m-d') }}',
            items: [],
            total: 0,
            isScanning: false,
            isAiGenerated: false,
            scanError: null,
            toast: { show: false, message: '', type: 'success' },

            showNotification(message, type = 'success') {
                this.toast = { show: true, message: message, type: type };
                setTimeout(() => { this.toast.show = false; }, 3000);
            },

            recalculateTotal() {
                this.total = this.items.reduce((sum, item) => sum + (parseFloat(item.qty || 0) * parseFloat(item.price || 0)), 0);
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

                    const response = await fetch('{{ route('money.scan') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Gagal scan struk');
                    }

                    this.isAiGenerated = true;

                    if (data.date) this.date = data.date;
                    if (data.merchant) this.merchant = data.merchant;
                    if (data.items && data.items.length > 0) {
                        this.items = data.items;
                    }
                    if (data.total_amount) this.total = data.total_amount;

                    this.showNotification(`✓ Berhasil menemukan ${this.items.length} barang!`, 'success');

                } catch (e) {
                    console.error('Scan error:', e);
                    this.scanError = e.message;
                    this.showNotification(e.message, 'error');
                    this.isAiGenerated = false;
                } finally {
                    this.isScanning = false;
                    event.target.value = '';
                }
            },

            async submitData() {
                if(this.items.length === 0) {
                    this.showNotification('Scan struk atau tambah barang terlebih dahulu!', 'error');
                    return;
                }

                try {
                    const response = await fetch('{{ route('money.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            date: this.date,
                            merchant: this.merchant,
                            total_amount: this.total,
                            items: this.items
                        })
                    });

                    const result = await response.json();

                    if(!response.ok) throw new Error(result.error || 'Gagal menyimpan data');

                    this.showNotification('✨ Data berhasil disimpan!', 'success');

                    setTimeout(() => { window.location.href = '{{ route('money.index') }}'; }, 1500);

                } catch (e) {
                    this.showNotification(e.message, 'error');
                }
            }
        }">

        <div class="bg-white border-b border-gray-100 px-4 py-4 sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <a href="{{ route('money.index') }}" class="p-2 -ml-2 rounded-full hover:bg-gray-100 transition">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-gas-black">Catat Belanjaan</h1>
                    <p class="text-xs text-gray-400">Ekstrak otomatis isi struk belanja</p>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <div x-show="toast.show" x-transition class="fixed bottom-24 left-4 right-4 z-50" style="display: none;">
                <div class="rounded-2xl px-4 py-3 shadow-xl flex items-center gap-3 backdrop-blur-md"
                    :class="toast.type === 'success' ? 'bg-blue-500' : 'bg-red-500'">
                    <p class="flex-1 text-sm font-medium text-white" x-text="toast.message"></p>
                </div>
            </div>

            <div x-show="isScanning" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex flex-col items-center justify-center text-white" style="display: none;">
                <div class="w-14 h-14 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="font-semibold tracking-wide animate-pulse">Membongkar Isi Struk...</p>
            </div>

            <input type="file" x-ref="fileInput" accept="image/*" capture="environment" class="hidden" @change="scanReceipt">

            <button type="button" @click="$refs.fileInput.click()"
                class="w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-200 active:scale-[0.98] transition-transform flex items-center justify-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Foto Struk Belanja</span>
            </button>
            <p class="text-center text-[10px] text-gray-400 -mt-2">Scan struk Indomaret, Supermarket, dll</p>

            <div x-show="items.length === 0" class="flex items-center gap-3 my-2">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">atau</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <button type="button" x-show="items.length === 0"
                @click="items.push({name: '', qty: 1, price: 0, subtotal: 0}); isAiGenerated = false"
                class="w-full bg-white border-2 border-dashed border-gray-300 text-gas-black font-bold py-4 rounded-2xl active:scale-[0.98] transition-transform flex items-center justify-center gap-2 hover:border-blue-400 hover:text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Input Manual (tanpa struk)</span>
            </button>

            <div x-show="items.length > 0" x-transition class="space-y-4 mt-6">

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 p-4 rounded-2xl border" :class="isAiGenerated ? 'border-blue-300 bg-blue-50' : 'border-gray-100'">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</label>
                        <input type="date" x-model="date" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1">
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl border" :class="isAiGenerated ? 'border-blue-300 bg-blue-50' : 'border-gray-100'">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Toko</label>
                        <input type="text" x-model="merchant" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1" placeholder="Nama Merchant">
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">Daftar Barang</h3>

                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex flex-col gap-2 border-b border-dashed border-gray-100 pb-3 relative">
                                <button @click="items.splice(index, 1); recalculateTotal()" class="absolute top-0 right-0 text-red-400 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>

                                <input type="text" x-model="item.name" class="font-bold text-sm bg-transparent border-none focus:ring-0 p-0 pr-6 text-gas-black" placeholder="Nama Barang">

                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <input type="number" x-model.number="item.qty" @input="item.subtotal = item.qty * item.price; recalculateTotal()" class="w-12 bg-gray-50 text-center text-xs font-bold rounded-lg border-none py-1 focus:ring-1 focus:ring-blue-300" placeholder="Qty">
                                        <span class="text-xs text-gray-400">x</span>
                                        <input type="number" x-model.number="item.price" @input="item.subtotal = item.qty * item.price; recalculateTotal()" class="w-24 bg-gray-50 text-xs font-bold rounded-lg border-none py-1 focus:ring-1 focus:ring-blue-300" placeholder="Harga">
                                    </div>
                                    <p class="font-black text-sm text-blue-600">Rp <span x-text="item.subtotal ? item.subtotal.toLocaleString('id-ID') : '0'"></span></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button @click="items.push({name: '', qty: 1, price: 0, subtotal: 0})" class="mt-4 text-xs font-bold text-blue-500 flex items-center gap-1 w-full justify-center py-2 bg-blue-50 rounded-xl">
                        + Tambah Barang Manual
                    </button>
                </div>

                <div class="bg-gray-100 p-4 rounded-2xl border border-gray-200 flex justify-between items-center">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total Belanja</label>
                    <p class="text-2xl font-black text-gray-800">Rp <span x-text="total ? total.toLocaleString('id-ID') : '0'"></span></p>
                </div>

                <button @click="submitData()" class="w-full bg-gas-black text-white font-black py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-transform text-lg mt-4">
                    SIMPAN KE DATABASE
                </button>
            </div>

            <div x-show="items.length === 0 && !isScanning" class="bg-blue-50 border border-blue-100 rounded-2xl p-6 text-center mt-4">
                <p class="text-xs font-bold text-blue-600 mb-1">Cara Pakai</p>
                <p class="text-[11px] text-gray-500 leading-relaxed">
                    <span class="font-semibold text-blue-600">Foto struk</span> untuk baca otomatis, atau
                    <span class="font-semibold text-blue-600">input manual</span> kalau struk hilang / belanja tanpa struk.
                </p>
            </div>

        </div>

        <x-bottom-nav />
    </div>
</x-app-layout>
