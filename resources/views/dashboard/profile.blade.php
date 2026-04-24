<x-app-layout>
    <div class="max-w-md mx-auto pb-20">

        <div class="bg-gradient-to-br from-blue-600 to-blue-900 text-white p-6 pt-10 rounded-b-[2.5rem] shadow-xl relative overflow-hidden">
            <div class="relative z-10 text-center">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=2563eb&color=fff&size=150"
                     class="w-28 h-28 rounded-[2rem] mx-auto border-4 border-white/80 shadow-xl">
                <h2 class="text-2xl font-black mt-4 tracking-tight">{{ Auth::user()->name }}</h2>
                <p class="text-sm text-white/70 font-medium">{{ Auth::user()->email }}</p>
            </div>
            <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-white/10 rounded-full"></div>
            <div class="absolute -left-8 -top-8 w-24 h-24 bg-white/5 rounded-full"></div>
        </div>

        <div class="p-6 space-y-3">

            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-2">Akun</p>

            <a href="{{ route('profile.edit') }}"
               class="w-full bg-white p-4 rounded-2xl border border-gray-100 flex items-center gap-3 hover:bg-blue-50 transition-colors shadow-sm">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                <div class="flex-1 text-left">
                    <p class="font-bold text-gas-black text-sm">Edit Profil</p>
                    <p class="text-[10px] text-gray-400">Ubah nama, email & password</p>
                </div>
                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="{{ route('money.history') }}"
               class="w-full bg-white p-4 rounded-2xl border border-gray-100 flex items-center gap-3 hover:bg-blue-50 transition-colors shadow-sm">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1 text-left">
                    <p class="font-bold text-gas-black text-sm">Riwayat Belanja</p>
                    <p class="text-[10px] text-gray-400">Lihat semua struk & filter tanggal</p>
                </div>
                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="{{ route('chatbot.index') }}"
               class="w-full bg-white p-4 rounded-2xl border border-gray-100 flex items-center gap-3 hover:bg-blue-50 transition-colors shadow-sm">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div class="flex-1 text-left">
                    <p class="font-bold text-gas-black text-sm">AI Asisten Belanja</p>
                    <p class="text-[10px] text-gray-400">Tanya soal pengeluaranmu</p>
                </div>
                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <div class="pt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full bg-white p-4 rounded-2xl border border-red-100 flex items-center gap-3 text-red-500 hover:bg-red-50 transition-colors active:scale-[0.98] shadow-sm">
                        <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <span class="flex-1 text-left font-bold text-sm">Keluar Aplikasi</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>
            </div>

            <p class="text-center text-[10px] text-gray-300 pt-6">
                Catatan Dompetku · v1.0
            </p>
        </div>

        <x-bottom-nav />
    </div>
</x-app-layout>
