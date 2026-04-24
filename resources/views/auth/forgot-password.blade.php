<x-guest-layout>
    <div class="max-w-md w-full mx-auto flex-grow flex flex-col p-8">
        <div class="text-center my-12">
            <div class="inline-flex p-4 bg-gas-black rounded-[2rem] text-gas-green shadow-xl mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-black tracking-tighter">Lupa<span class="text-gas-green"> Sandi?</span></h1>
            <p class="text-gray-500 text-sm mt-2 px-4">
                Masukkan email kamu dan kami akan kirimkan link untuk mengatur ulang kata sandi.
            </p>
        </div>

        @if (session('status'))
            <div class="bg-green-50 border border-green-100 text-green-600 text-xs font-bold p-4 rounded-2xl mb-6 text-center">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf

            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="nama@email.com"
                    class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0 text-gas-black">

                @error('email')
                    <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-gas-black text-white font-black py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-transform uppercase tracking-widest text-sm">
                Kirim Link Reset
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="text-xs font-bold text-gray-400 hover:text-gas-green transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Masuk
            </a>
        </div>
    </div>

    <div class="p-8 text-center mt-auto">
        <p class="text-[10px] text-gray-400 leading-relaxed uppercase font-bold tracking-widest">
            Dompetku Tracker Security System
        </p>
    </div>
</x-guest-layout>
