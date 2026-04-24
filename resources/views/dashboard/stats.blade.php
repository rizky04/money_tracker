<x-app-layout>
    <section class="p-6 space-y-6 pb-24">

        <header class="mb-2">
            <h1 class="text-2xl font-black text-gas-black">Statistik Kendaraan</h1>
            <p class="text-xs text-gray-500 font-bold mt-1">
                {{ $vehicle ? $vehicle->name . ' (' . $vehicle->license_plate . ')' : 'Belum ada kendaraan' }}
            </p>
        </header>

        @if(!$vehicle)
            <div class="bg-red-50 border border-red-100 rounded-3xl p-6 text-center shadow-sm">
                <p class="text-xs font-bold text-red-600">Pilih atau tambahkan kendaraan terlebih dahulu di Garasi.</p>
            </div>
        @else
           <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase mb-6">Pengeluaran 7 Hari Terakhir</p>

                <div class="flex justify-between h-32 gap-2">
                    @foreach($chartData as $data)

                        <div class="w-full h-full relative group flex items-end justify-center">

                            <div class="absolute -top-8 bg-gas-black text-white text-[8px] font-bold py-1 px-2 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                Rp {{ number_format($data['total'], 0, ',', '.') }}
                            </div>

                            <div class="{{ $data['total'] > 0 ? 'bg-gas-green' : 'bg-gray-100' }} w-full rounded-t-lg transition-all duration-700 ease-out hover:brightness-95 cursor-pointer"
                                 style="height: {{ $data['percentage'] }}%;">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between mt-3 text-[10px] font-bold text-gray-400 uppercase">
                    @foreach($chartData as $data)
                        <span class="w-full text-center">{{ $data['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 p-6 rounded-[2rem] shadow-sm">
                    <p class="text-[10px] font-bold text-blue-400 uppercase tracking-wide">Efisiensi Rata-rata</p>
                    <h4 class="text-xl font-black text-blue-900 mt-1">
                        {{ number_format($avgKml, 1, ',', '.') }} <span class="text-xs font-bold opacity-60">km/l</span>
                    </h4>
                </div>

                <div class="bg-purple-50 p-6 rounded-[2rem] shadow-sm">
                    <p class="text-[10px] font-bold text-purple-400 uppercase tracking-wide">Biaya per KM</p>
                    <h4 class="text-xl font-black text-purple-900 mt-1">
                        Rp {{ number_format($costPerKm, 0, ',', '.') }}
                    </h4>
                </div>
            </div>

            @if($avgKml == 0)
                <p class="text-[10px] text-gray-400 text-center px-4 font-medium">
                    *Input minimal 2x riwayat bensin yang berbeda kilometernya untuk memunculkan data efisiensi.
                </p>
            @endif
        @endif

    </section>

    <x-bottom-nav />
</x-app-layout>
