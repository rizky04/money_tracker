<x-app-layout>
    <div class="max-w-md mx-auto pb-20">

        <div class="bg-white border-b border-gray-100 px-4 py-4 sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <a href="{{ route('money.index') }}" class="p-2 -ml-2 rounded-full hover:bg-gray-100 transition">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-gas-black">Riwayat Belanja</h1>
                    <p class="text-xs text-gray-400">Filter & lihat semua struk</p>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4" x-data="{ showCustom: {{ $preset === 'custom' ? 'true' : 'false' }} }">

            <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-hide">
                @php
                    $presets = [
                        'today' => 'Hari Ini',
                        'last_7_days' => '7 Hari',
                        'this_month' => 'Bulan Ini',
                        'last_month' => 'Bulan Lalu',
                        'all' => 'Semua',
                    ];
                @endphp
                @foreach($presets as $key => $label)
                    <a href="{{ route('money.history', ['preset' => $key]) }}"
                       class="flex-shrink-0 px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition
                       {{ $preset === $key ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'bg-white text-gray-500 border border-gray-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <button type="button" @click="showCustom = !showCustom"
                    class="flex-shrink-0 px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition flex items-center gap-1
                    {{ $preset === 'custom' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'bg-white text-gray-500 border border-gray-200' }}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Custom
                </button>
            </div>

            <form x-show="showCustom" x-transition method="GET" action="{{ route('money.history') }}"
                  class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm space-y-3" style="display: none;">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Dari</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="w-full mt-1 bg-gray-50 rounded-xl border-gray-200 text-sm font-bold py-2 px-3 focus:ring-1 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sampai</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="w-full mt-1 bg-gray-50 rounded-xl border-gray-200 text-sm font-bold py-2 px-3 focus:ring-1 focus:ring-blue-400">
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white text-xs font-bold py-3 rounded-xl">
                    Terapkan Filter
                </button>
            </form>

            <div class="bg-gradient-to-br from-blue-600 to-blue-900 text-white p-5 rounded-3xl shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-[10px] opacity-70 font-bold uppercase tracking-widest">
                        {{ $presetLabels[$preset] ?? 'Filter' }}
                        @if($preset === 'custom' && $startDate && $endDate)
                            <span class="opacity-80">• {{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                        @endif
                    </p>
                    <h2 class="text-2xl font-black mt-1 tracking-tight">
                        Rp {{ number_format($summary['total'], 0, ',', '.') }}
                    </h2>
                    <div class="flex gap-4 mt-3 text-[11px]">
                        <div>
                            <p class="opacity-70">Struk</p>
                            <p class="font-bold">{{ $summary['count'] }}</p>
                        </div>
                        <div>
                            <p class="opacity-70">Rata-rata</p>
                            <p class="font-bold">Rp {{ number_format($summary['avg'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full"></div>
            </div>

            @if($expenses->count() > 0)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50"
                     x-data="{ openId: null }">
                    @foreach($expenses as $expense)
                        <div>
                            <button @click="openId = openId === {{ $expense->id }} ? null : {{ $expense->id }}"
                                    class="w-full flex justify-between items-center gap-3 p-4 text-left">
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
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs font-black text-gas-black whitespace-nowrap">
                                        Rp {{ number_format($expense->total_amount, 0, ',', '.') }}
                                    </p>
                                    <svg class="w-3 h-3 text-gray-300 inline-block mt-1 transition-transform"
                                         :class="openId === {{ $expense->id }} ? 'rotate-180' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </button>

                            <div x-show="openId === {{ $expense->id }}" x-transition
                                 class="bg-gray-50 mx-4 mb-3 rounded-xl p-3 space-y-2" style="display: none;">
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

                @if($expenses->hasPages())
                    <div class="pt-2">
                        {{ $expenses->links() }}
                    </div>
                @endif
            @else
                <div class="bg-blue-50 border border-blue-100 rounded-3xl p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-blue-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm font-bold text-blue-600">Tidak ada struk di periode ini</p>
                    <p class="text-xs text-gray-500 mt-1">Coba ubah filter atau tambah struk baru</p>
                    <a href="{{ route('money.create') }}" class="inline-block mt-3 bg-blue-500 text-white text-xs font-bold px-4 py-2 rounded-full">
                        + Tambah Struk
                    </a>
                </div>
            @endif

        </div>

        <x-bottom-nav />
    </div>
</x-app-layout>
