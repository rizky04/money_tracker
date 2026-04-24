<x-app-layout>
    <div class="max-w-md w-full mx-auto flex flex-col min-h-screen pb-12">

        <header class="flex items-center gap-4 p-6 bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm">
            <a href="{{ route('account') }}" class="p-2 bg-gray-50 rounded-xl text-gray-600 hover:text-gas-black hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="text-xl font-black text-gas-black tracking-tight">Pengaturan Profil</h1>
            </div>
        </header>

        <div class="p-6 space-y-8">

            <section class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
                <header class="mb-6">
                    <h2 class="text-lg font-bold text-gas-black">Informasi Akun</h2>
                    <p class="text-xs text-gray-500 mt-1">Perbarui nama dan alamat email kamu.</p>
                </header>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('patch')

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0">
                        @error('name') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0">
                        @error('email') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-2 pt-2 border-t border-gray-200">
                                <p class="text-[10px] text-gray-500 font-bold">Email belum diverifikasi.</p>
                                <button form="send-verification" class="text-xs font-bold text-gas-green hover:underline">Kirim ulang email verifikasi.</button>
                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-1 text-[10px] font-bold text-green-600">Link baru telah dikirim!</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="submit" class="bg-gas-black text-white font-black px-6 py-3 rounded-2xl shadow-lg active:scale-[0.98] transition-transform text-sm tracking-wide">
                            SIMPAN PROFIL
                        </button>

                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-xs font-bold text-gas-green">Berhasil disimpan.</p>
                        @endif
                    </div>
                </form>
            </section>

            <section class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
                <header class="mb-6">
                    <h2 class="text-lg font-bold text-gas-black">Ganti Kata Sandi</h2>
                    <p class="text-xs text-gray-500 mt-1">Pastikan akun menggunakan sandi yang kuat.</p>
                </header>

                <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('put')

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Sandi Saat Ini</label>
                        <input type="password" name="current_password" autocomplete="current-password" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0" placeholder="••••••••">
                        @error('current_password', 'updatePassword') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Sandi Baru</label>
                        <input type="password" name="password" autocomplete="new-password" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0" placeholder="••••••••">
                        @error('password', 'updatePassword') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Konfirmasi Sandi Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0" placeholder="••••••••">
                        @error('password_confirmation', 'updatePassword') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="submit" class="bg-gas-green text-white font-black px-6 py-3 rounded-2xl shadow-lg active:scale-[0.98] transition-transform text-sm tracking-wide">
                            GANTI SANDI
                        </button>

                        @if (session('status') === 'password-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-xs font-bold text-gas-green">Berhasil diganti.</p>
                        @endif
                    </div>
                </form>
            </section>

            <section class="bg-red-50 p-6 rounded-[2rem] border border-red-100 shadow-sm" x-data="{ modalOpen: false }">
                <header class="mb-4">
                    <h2 class="text-lg font-bold text-red-600">Hapus Akun</h2>
                    <p class="text-xs text-red-400 mt-1 font-medium">Semua data riwayat bensin akan terhapus permanen.</p>
                </header>

                <button @click="modalOpen = true" class="bg-white border border-red-200 text-red-600 font-black px-6 py-3 rounded-2xl shadow-sm active:scale-[0.98] transition-transform text-sm tracking-wide">
                    HAPUS AKUN SAYA
                </button>

                <div x-show="modalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>

                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
                        <div x-show="modalOpen"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative bg-white rounded-[2rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-6">

                            <form method="post" action="{{ route('profile.destroy') }}">
                                @csrf
                                @method('delete')

                                <h2 class="text-xl font-black text-gas-black" id="modal-title">Yakin ingin menghapus akun?</h2>
                                <p class="text-xs text-gray-500 mt-2 font-medium">Tindakan ini tidak dapat dibatalkan. Masukkan sandi kamu untuk konfirmasi.</p>

                                <div class="mt-6 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase">Kata Sandi</label>
                                    <input type="password" name="password" required class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0" placeholder="Sandi saat ini">
                                    @error('password', 'userDeletion') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>

                                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                                    <button type="button" @click="modalOpen = false" class="w-full sm:w-auto bg-gray-100 text-gray-600 font-black px-6 py-3 rounded-2xl hover:bg-gray-200 transition-colors text-sm">
                                        BATAL
                                    </button>
                                    <button type="submit" class="w-full sm:w-auto bg-red-600 text-white font-black px-6 py-3 rounded-2xl hover:bg-red-700 shadow-lg shadow-red-200 active:scale-[0.98] transition-transform text-sm">
                                        HAPUS PERMANEN
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>

    @if($errors->userDeletion->isNotEmpty())
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('modalData', () => ({
                    modalOpen: true
                }))
            })
        </script>
    @endif
</x-app-layout>
