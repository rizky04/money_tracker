<x-guest-layout>
    <div class="max-w-md w-full mx-auto flex-grow flex flex-col p-8">
        <div class="text-center my-10">
            <div class="inline-flex p-4 bg-gradient-to-br from-blue-500 to-blue-800 rounded-[2rem] text-white shadow-xl shadow-blue-200 mb-4">
                <img src="{{ asset('logo.png') }}" class="w-10 h-10" alt="Logo">
            </div>
            <h1 class="text-3xl font-black tracking-tighter">Dompet<span class="text-blue-600">ku</span></h1>
            <p class="text-gray-500 text-sm mt-2">Catat belanja lebih cerdas.</p>
        </div>

        <div class="flex border-b border-gray-200 mb-8">
            <button id="login-btn-tab" onclick="toggleAuth('login')" class="flex-1 py-3 font-bold text-gas-black border-b-2 border-blue-600 transition-all">Masuk</button>
            <button id="register-btn-tab" onclick="toggleAuth('register')" class="flex-1 py-3 font-bold text-gray-400 transition-all">Daftar</button>
        </div>

        <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                    @error('email') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Kata Sandi</label>
                    <input type="password" name="password" required
                           class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                </div>
            </div>
            <div class="text-right">
                <a href="{{ route('password.request') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lupa Sandi?</a>
            </div>
            <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-200 active:scale-[0.98] transition-transform">
                MASUK SEKARANG
            </button>
        </form>

        <form id="register-form" method="POST" action="{{ route('register') }}" class="hidden space-y-5">
            @csrf
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                    @error('email') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Kata Sandi</label>
                    <input type="password" name="password" required
                           class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Konfirmasi Sandi</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-200 active:scale-[0.98] transition-transform">
                BUAT AKUN
            </button>
        </form>

        <div class="relative my-8 text-center">
            <span class="bg-gray-50 px-4 text-xs text-gray-400 font-bold uppercase relative z-10">Atau gunakan</span>
            <div class="absolute top-1/2 left-0 w-full h-[1px] bg-gray-200"></div>
        </div>
        <div class="grid grid-cols-1 gap-4 mb-8">
            <a href="{{ route('google.login') }}" class="flex w-full items-center justify-center gap-2 bg-white border border-gray-100 p-3 rounded-2xl shadow-sm hover:bg-gray-50 hover:border-blue-200 transition">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" class="w-5 h-5">
                <span class="text-xs font-bold uppercase">Google</span>
            </a>
        </div>
    </div>
    <div class="p-8 text-center pb-12">
        <p class="text-[10px] text-gray-400 leading-relaxed uppercase font-bold tracking-widest">
            Dengan melanjutkan, kamu setuju dengan <br><span class="text-blue-600">Syarat & Ketentuan</span> kami.
        </p>
    </div>
</x-guest-layout>
