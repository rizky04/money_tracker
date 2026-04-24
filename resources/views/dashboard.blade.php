<x-app-layout>

    <section id="home" class="tab-content p-6 space-y-6">
        <header class="flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Selamat Datang</p>
                <h1 class="text-2xl font-black">Halo, {{ explode(' ', Auth::user()->name)[0] }} 👋</h1>
            </div>
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=1DB954&color=fff" class="w-12 h-12 rounded-2xl shadow-sm border-2 border-white">
        </header>

        {{-- <div class="bg-gas-black text-white p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs opacity-60 font-medium">Odometer Saat Ini</p>
                <h2 class="text-4xl font-black mt-1 tracking-tighter">125.500 <span class="text-sm font-normal opacity-60">KM</span></h2>
                <div class="mt-6 flex gap-2">
                    <span class="bg-gas-green text-[10px] px-3 py-1 rounded-full font-bold uppercase">Avanza - B 1234 GAI</span>
                </div>
            </div>
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-gas-green opacity-20 rounded-full"></div>
        </div> --}}
        <div class="bg-gas-black text-white p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs opacity-60 font-medium">Odometer Saat Ini</p>

                @if($vehicle)
                    <h2 class="text-4xl font-black mt-1 tracking-tighter">{{ number_format($vehicle->odometer_initial, 0, ',', '.') }} <span class="text-sm font-normal opacity-60">KM</span></h2>
                    <div class="mt-6 flex gap-2">
                        <span class="bg-gas-green text-gas-black text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider">
                            {{ $vehicle->name }} - {{ $vehicle->license_plate }}
                        </span>
                    </div>
                @else
                    <h2 class="text-4xl font-black mt-1 tracking-tighter">0 <span class="text-sm font-normal opacity-60">KM</span></h2>
                    <div class="mt-6 flex gap-2">
                        <a href="{{ route('vehicles.index') }}" class="bg-red-500 text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider">
                            + Tambah Kendaraan Dulu
                        </a>
                    </div>
                @endif

            </div>
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-gas-green opacity-20 rounded-full"></div>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm space-y-4">
            <h3 class="font-bold text-lg">Input Manual</h3>
            <form action="#" method="POST" class="space-y-3">
                @csrf
                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Jenis Bahan Bakar</label>
                    <select name="fuel_type" class="w-full bg-transparent font-bold text-sm focus:outline-none appearance-none">
                        <option>Pertamax</option>
                        <option>Pertalite</option>
                        <option>Dexlite</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Liter</label>
                        <input type="number" step="0.01" name="liters" placeholder="0.0" class="w-full bg-transparent font-bold text-lg focus:outline-none">
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Total Harga</label>
                        <input type="number" name="total_price" placeholder="Rp" class="w-full bg-transparent font-bold text-lg focus:outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full bg-gas-green text-white font-black py-4 rounded-2xl shadow-lg shadow-green-100 active:scale-[0.98] transition-transform">
                    SIMPAN DATA
                </button>
            </form>
        </div>
    </section>

    <section id="riwayat" class="tab-content hidden p-6 space-y-6">
        <h1 class="text-2xl font-black">Riwayat Pengisian</h1>
        <div class="space-y-4">
            <div class="bg-white p-4 rounded-3xl border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center text-gas-green">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path></svg>
                </div>
                <div class="flex-grow">
                    <h4 class="font-bold text-sm">SPBU COCO Sudirman</h4>
                    <p class="text-xs text-gray-400">Hari ini, 14:20</p>
                </div>
                <div class="text-right">
                    <p class="font-black text-sm">Rp 350.000</p>
                    <p class="text-[10px] text-gray-400">25.4 Liter</p>
                </div>
            </div>
        </div>
    </section>

    <section id="stats" class="tab-content hidden p-6 space-y-6">
        <h1 class="text-2xl font-black">Statistik Kendaraan</h1>
        <div class="bg-white p-6 rounded-[2rem] border border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase mb-4">Pengeluaran 7 Hari Terakhir</p>
            <div class="flex items-end justify-between h-32 gap-2">
                <div class="bg-gray-100 w-full rounded-t-lg h-1/2"></div>
                <div class="bg-gray-100 w-full rounded-t-lg h-3/4"></div>
                <div class="bg-gas-green w-full rounded-t-lg h-full"></div>
                <div class="bg-gray-100 w-full rounded-t-lg h-2/3"></div>
                <div class="bg-gray-100 w-full rounded-t-lg h-1/3"></div>
                <div class="bg-gray-100 w-full rounded-t-lg h-1/2"></div>
                <div class="bg-gray-100 w-full rounded-t-lg h-4/5"></div>
            </div>
            <div class="flex justify-between mt-2 text-[10px] font-bold text-gray-400 uppercase">
                <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 p-6 rounded-[2rem]">
                <p class="text-[10px] font-bold text-blue-400 uppercase">Efisiensi</p>
                <h4 class="text-xl font-black text-blue-900">14.5 <span class="text-xs">km/l</span></h4>
            </div>
            <div class="bg-purple-50 p-6 rounded-[2rem]">
                <p class="text-[10px] font-bold text-purple-400 uppercase">Biaya/km</p>
                <h4 class="text-xl font-black text-purple-900">Rp 850</h4>
            </div>
        </div>
    </section>

    <section id="profil" class="tab-content hidden p-6 space-y-6 text-center">
        <div class="py-8">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=1DB954&color=fff&size=150" class="w-32 h-32 rounded-[3rem] mx-auto border-4 border-white shadow-xl">
            <h2 class="text-2xl font-black mt-4">{{ Auth::user()->name }}</h2>
            <p class="text-sm text-gray-400 font-medium">{{ Auth::user()->email }}</p>
        </div>

        <div class="space-y-2">
            <a href="{{ route('profile.edit') }}" class="w-full bg-white p-5 rounded-3xl border border-gray-100 flex items-center justify-between hover:bg-gray-50 transition-colors text-left">
                <span class="font-bold text-gas-black">Edit Profil Akun</span>
                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>

         <a href="{{ route('vehicles.index') }}" class="w-full bg-white p-5 rounded-3xl border border-gray-100 flex items-center justify-between hover:bg-gray-50 transition-colors text-left">
    <span class="font-bold text-gas-black">Pengaturan Kendaraan</span>
    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full bg-white p-5 rounded-3xl border border-gray-100 flex items-center justify-between text-red-500 hover:bg-red-50 transition-colors active:scale-[0.98]">
                    <span class="font-bold">Keluar Aplikasi</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </section>

    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 px-8 py-3 z-50 flex justify-between items-center shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
        <button onclick="switchTab('home')" class="nav-item flex flex-col items-center gap-1 text-gas-green font-bold">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px]">Home</span>
        </button>

        <button onclick="switchTab('riwayat')" class="nav-item flex flex-col items-center gap-1 text-gray-400">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
            <span class="text-[10px]">Riwayat</span>
        </button>

        <div class="relative -top-6">
            <button class="bg-gas-black text-white p-4 rounded-full shadow-lg border-4 border-gray-50 active:scale-95 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </button>
        </div>

        <button onclick="switchTab('stats')" class="nav-item flex flex-col items-center gap-1 text-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2a2 2 0 01-2-2zm0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span class="text-[10px]">Stats</span>
        </button>

        <button onclick="switchTab('profil')" class="nav-item flex flex-col items-center gap-1 text-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-[10px]">Profil</span>
        </button>
    </div>

</x-app-layout>
