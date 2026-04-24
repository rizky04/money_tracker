<x-app-layout>
    <div class="max-w-md mx-auto pb-20">
        <div class="p-6 space-y-6">

            <header class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Dompet Digital</p>
                    <h1 class="text-2xl font-black">Catatan Belanja</h1>
                </div>
                <a href="{{ route('dashboard') }}" class="p-2 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>
            </header>

            <div class="bg-gradient-to-br from-blue-600 to-blue-900 text-white p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-[10px] opacity-70 font-bold uppercase tracking-widest">{{ $thisMonthLabel }}</p>
                    <h2 class="text-3xl font-black mt-2 tracking-tight">
                        Rp {{ number_format($totalThisMonth, 0, ',', '.') }}
                    </h2>
                    <div class="flex items-center gap-2 mt-3 flex-wrap">
                        <span class="text-[10px] opacity-80 font-medium">{{ $countThisMonth }} struk bulan ini</span>
                        @if($totalLastMonth > 0)
                            @if($diffPercent > 0)
                                <span class="text-[10px] bg-red-500/30 text-white px-2 py-0.5 rounded-full font-bold">
                                    ↑ {{ number_format($diffPercent, 0) }}% vs bulan lalu
                                </span>
                            @elseif($diffPercent < 0)
                                <span class="text-[10px] bg-green-500/30 text-white px-2 py-0.5 rounded-full font-bold">
                                    ↓ {{ number_format(abs($diffPercent), 0) }}% vs bulan lalu
                                </span>
                            @else
                                <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full font-bold">sama persis</span>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-white/10 rounded-full"></div>
                <div class="absolute -right-4 -top-8 w-24 h-24 bg-white/5 rounded-full"></div>
            </div>

            <a href="{{ route('money.create') }}"
               class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-3xl p-6 text-white block shadow-lg active:scale-[0.98] transition-transform relative overflow-hidden">
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90 font-medium">Habis belanja?</p>
                        <h3 class="text-xl font-black mt-1">Scan Struk Sekarang</h3>
                        <p class="text-xs opacity-80 mt-1">AI baca isi struk otomatis</p>
                    </div>
                    <div class="bg-white text-blue-600 p-4 rounded-full shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-white/10 rounded-full"></div>
            </a>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Rata-rata / Struk</p>
                    <p class="text-lg font-black text-gas-black mt-1">
                        Rp {{ number_format($avgThisMonth, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Semua</p>
                    <p class="text-lg font-black text-gas-black mt-1">
                        {{ $totalExpenses }} <span class="text-[10px] text-gray-400 font-normal">struk</span>
                    </p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pengeluaran 7 Hari</h3>
                    @if($chartData->sum('total') > 0)
                        <span class="text-[10px] text-blue-500 font-bold">
                            Total Rp {{ number_format($chartData->sum('total'), 0, ',', '.') }}
                        </span>
                    @endif
                </div>
                <div class="flex items-end justify-between gap-2 h-28">
                    @foreach($chartData as $day)
                        <div class="flex-1 flex flex-col items-center gap-2 group relative">
                            <div class="w-full flex flex-col justify-end" style="height: 90px;">
                                <div class="w-full bg-gradient-to-t from-blue-500 to-blue-300 rounded-t-lg transition-all"
                                     style="height: {{ $day['percentage'] }}%;"
                                     title="Rp {{ number_format($day['total'], 0, ',', '.') }}"></div>
                            </div>
                            <span class="text-[9px] font-bold text-gray-400">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($recentExpenses->count() > 0)
                <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm" x-data="{ openId: null }">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Struk Terbaru</h3>
                        <a href="{{ route('money.history') }}" class="text-[10px] text-blue-500 font-bold">Lihat Semua →</a>
                    </div>
                    <div class="space-y-2">
                        @foreach($recentExpenses as $expense)
                            <div class="border-b border-gray-50 last:border-0 pb-2 last:pb-0">
                                <button @click="openId = openId === {{ $expense->id }} ? null : {{ $expense->id }}"
                                        class="w-full flex justify-between items-center gap-3 py-2 text-left">
                                    <div class="flex gap-3 items-center min-w-0 flex-1">
                                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-gas-black truncate">
                                                {{ $expense->merchant ?: 'Tanpa nama toko' }}
                                            </p>
                                            <p class="text-[10px] text-gray-400">
                                                {{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}
                                                • {{ $expense->items->count() }} barang
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-xs font-black text-gas-black whitespace-nowrap">
                                        Rp {{ number_format($expense->total_amount, 0, ',', '.') }}
                                    </p>
                                </button>

                                <div x-show="openId === {{ $expense->id }}" x-transition
                                     class="bg-gray-50 rounded-xl p-3 mt-1 space-y-2" style="display: none;">
                                    @foreach($expense->items as $item)
                                        <div class="flex justify-between items-start text-[11px]">
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gas-black truncate">{{ $item->name }}</p>
                                                <p class="text-gray-400">
                                                    {{ $item->qty }} × Rp {{ number_format($item->price, 0, ',', '.') }}
                                                </p>
                                            </div>
                                            <p class="font-bold text-blue-600 whitespace-nowrap">
                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-blue-50 border border-blue-100 rounded-3xl p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-blue-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm font-bold text-blue-600">Belum ada struk</p>
                    <p class="text-xs text-gray-500 mt-1">Scan struk pertamamu untuk mulai mencatat belanja</p>
                    <a href="{{ route('money.create') }}" class="inline-block mt-3 bg-blue-500 text-white text-xs font-bold px-4 py-2 rounded-full">
                        + Scan Struk
                    </a>
                </div>
            @endif

        </div>

        <x-bottom-nav />
    </div>
</x-app-layout>
