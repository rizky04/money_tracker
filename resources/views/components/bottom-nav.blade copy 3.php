<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 px-6 py-3 z-50 flex justify-between items-center shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">

    <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('dashboard') ? 'text-gas-green font-bold' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="{{ request()->routeIs('dashboard') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
        </svg>
        <span class="text-[9px]">Home</span>
    </a>

    <a href="{{ route('history') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('history') ? 'text-gas-green font-bold' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="{{ request()->routeIs('history') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-[9px]">Riwayat</span>
    </a>

    <!-- Tombol AI Assistant -->
    <a href="{{ route('chatbot.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('chatbot.*') ? 'text-gas-green font-bold' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="{{ request()->routeIs('chatbot.*') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span class="text-[9px]">AI</span>
    </a>

    <a href="{{ route('stats') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('stats') ? 'text-gas-green font-bold' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="{{ request()->routeIs('stats') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2a2 2 0 01-2-2zm0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <span class="text-[9px]">Stats</span>
    </a>

    <a href="{{ route('account') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('account') ? 'text-gas-green font-bold' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="{{ request()->routeIs('account') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <span class="text-[9px]">Profil</span>
    </a>
</div>
