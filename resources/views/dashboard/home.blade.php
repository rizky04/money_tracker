<x-app-layout>
    <div class="max-w-md mx-auto pb-20">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <header class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Selamat Datang</p>
                    <h1 class="text-2xl font-black">Halo, {{ explode(' ', Auth::user()->name)[0] }} 👋</h1>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=1DB954&color=fff"
                    class="w-12 h-12 rounded-2xl shadow-sm border-2 border-white">
            </header>

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                    class="bg-green-50 text-green-600 text-xs font-bold p-3 rounded-2xl text-center shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                    class="bg-red-50 text-red-600 text-xs font-bold p-3 rounded-2xl text-center shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Card Odometer -->
            <div class="bg-gas-black text-white p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs opacity-60 font-medium">Odometer</p>
                            @if($vehicle)
                                <h2 class="text-4xl font-black mt-1 tracking-tighter">
                                    {{ number_format($vehicle->odometer_initial, 0, ',', '.') }}
                                    <span class="text-sm font-normal opacity-60">KM</span>
                                </h2>
                            @else
                                <h2 class="text-4xl font-black mt-1 tracking-tighter">0 <span class="text-sm font-normal opacity-60">KM</span></h2>
                            @endif
                        </div>

                        @if($vehicles->count() > 0)
                            <form action="{{ route('dashboard.switch_vehicle') }}" method="POST" x-data="{ open: false }" class="relative">
                                @csrf
                                <button type="button" @click="open = !open"
                                    class="bg-black/30 backdrop-blur-sm border border-white/20 rounded-xl px-3 py-2 text-xs font-bold flex items-center gap-2">
                                    <span>{{ $vehicle ? $vehicle->name : 'Pilih Kendaraan' }}</span>
                                    <svg class="w-3 h-3" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg overflow-hidden z-20">
                                    @foreach($vehicles as $v)
                                        <button type="submit" name="vehicle_id" value="{{ $v->id }}"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $v->is_active ? 'bg-gas-green/10 text-gas-green font-bold' : '' }}">
                                            {{ $v->name }}
                                            @if($v->is_active) ✓ @endif
                                        </button>
                                    @endforeach
                                </div>
                            </form>
                        @endif
                    </div>

                    @if($vehicle)
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="bg-white/10 text-white text-[10px] px-3 py-1 rounded-full font-medium">
                                {{ $vehicle->license_plate }}
                            </span>
                            <span class="bg-gas-green text-gas-black text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider">
                                Aktif
                            </span>
                        </div>
                    @else
                        <a href="{{ route('vehicles.index') }}" class="mt-4 inline-block bg-gas-green text-gas-black text-[10px] px-4 py-2 rounded-full font-bold uppercase">
                            + Daftarkan Kendaraan
                        </a>
                    @endif
                </div>
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-gas-green opacity-20 rounded-full"></div>
            </div>

            <!-- Quick Action: Tombol Tambah Pengisian -->
            @if($vehicle)
            <div class="bg-gradient-to-r from-gas-green to-green-600 rounded-3xl p-6 text-white relative overflow-hidden shadow-lg">
                <div class="relative z-10">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm opacity-90 font-medium">Butuh isi BBM?</p>
                            <h3 class="text-xl font-black mt-1">Catat Pengisian</h3>
                            <p class="text-xs opacity-80 mt-1">Rekam setiap pengisian BBMmu</p>
                        </div>
                        <a href="{{ route('fuel.create') }}"
                           class="bg-white text-gas-green p-4 rounded-full shadow-lg hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full"></div>
            </div>
            @endif

            <!-- Statistik Singkat -->
            @if($stats && $vehicle)
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ringkasan Kendaraan</h3>
                    <span class="text-[10px] text-gas-green font-bold">{{ $vehicle->name }}</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-gray-50 rounded-2xl">
                        <p class="text-2xl font-black text-gas-black">{{ number_format($stats['total_km'], 0, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-400">Total KM</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-2xl">
                        <p class="text-2xl font-black text-gas-black">{{ number_format($stats['total_liter'], 1, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-400">Total Liter</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-2xl">
                        <p class="text-2xl font-black text-gas-black">{{ number_format($stats['avg_km_per_liter'], 1, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-400">KM/Liter</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-2xl">
                        <p class="text-2xl font-black text-gas-black">{{ number_format($stats['entries_count'], 0, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-400">Kali Isi</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Riwayat Terbaru -->
            @if($recentEntries && $recentEntries->count() > 0)
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Riwayat Terbaru</h3>
                    <a href="{{ route('history') }}" class="text-[10px] text-gas-green font-bold">Lihat Semua →</a>
                </div>
                <div class="space-y-3">
                    @foreach($recentEntries as $entry)
                    <div class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
                        <div>
                            <p class="text-xs font-bold text-gas-black">{{ $entry->date->format('d M Y') }}</p>
                            <p class="text-[10px] text-gray-400">{{ $entry->fuel_type }} • {{ number_format($entry->liters, 1, ',', '.') }} L</p>
                            @if($entry->location_name)
                            <p class="text-[9px] text-gray-300">{{ $entry->location_name }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-gas-black">Rp {{ number_format($entry->total_price, 0, ',', '.') }}</p>
                            <p class="text-[10px] text-gray-400">{{ number_format($entry->odometer, 0, ',', '.') }} KM</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @elseif($vehicle)
            <div class="bg-gray-50 p-8 rounded-3xl text-center border border-gray-100">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-sm font-bold text-gray-500">Belum ada riwayat pengisian</p>
                <p class="text-xs text-gray-400 mt-1">Mulai catat pengisian BBM pertamamu</p>
                <a href="{{ route('fuel.create') }}" class="inline-block mt-3 text-gas-green text-xs font-bold">
                    + Tambah Pengisian
                </a>
            </div>
            @endif

            <!-- Tips Hemat BBM -->
            {{-- <div class="bg-blue-50 p-5 rounded-3xl border border-blue-100">
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-blue-800">💡 Tips Hemat BBM</h4>
                        <p class="text-xs text-blue-700 mt-1">Jaga tekanan angin ban, hindari akselerasi mendadak, dan lakukan perawatan rutin untuk efisiensi BBM optimal.</p>
                    </div>
                </div>
            </div> --}}
        </div>

        <x-bottom-nav />
    </div>
</x-app-layout>
