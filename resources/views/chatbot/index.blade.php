<x-app-layout>
    <div x-data="{
            conversationId: null,
            messages: [],
            isLoading: false,

            async sendMessage() {
                const input = this.$refs.messageInput;
                const message = input.value.trim();
                if (!message || this.isLoading) return;

                this.messages.push({
                    role: 'user',
                    content: message,
                    timestamp: new Date()
                });

                input.value = '';
                this.isLoading = true;
                this.scrollToBottom();

                try {
                    const response = await fetch('{{ route('chatbot.ask') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            message: message,
                            conversation_id: this.conversationId
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.messages.push({
                            role: 'assistant',
                            content: data.message,
                            timestamp: new Date()
                        });
                        this.conversationId = data.conversation_id;
                    } else {
                        this.messages.push({
                            role: 'assistant',
                            content: 'Maaf, terjadi kesalahan: ' + data.error,
                            timestamp: new Date(),
                            isError: true
                        });
                    }
                } catch (error) {
                    this.messages.push({
                        role: 'assistant',
                        content: 'Maaf, terjadi kesalahan koneksi. Silakan coba lagi.',
                        timestamp: new Date(),
                        isError: true
                    });
                } finally {
                    this.isLoading = false;
                    this.scrollToBottom();
                }
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const container = this.$refs.chatContainer;
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            },

            formatTime(date) {
                return new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            },

            async askExample(question) {
                this.$refs.messageInput.value = question;
                await this.sendMessage();
            }
        }"
        x-init="scrollToBottom()"
        class="max-w-md w-full mx-auto flex flex-col min-h-screen pb-12 relative">

        <header class="flex items-center justify-between p-6 bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm">
            <div class="flex items-center gap-4">
                <a href="{{ route('money.index') }}" class="p-2 bg-gray-50 rounded-xl text-gray-600 hover:text-gas-black hover:bg-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-gas-black tracking-tight">AI Asisten Belanja</h1>
                    <p class="text-[10px] text-gray-400">Tanya apapun tentang pengeluaranmu</p>
                </div>
            </div>
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
        </header>

        <div x-ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4 min-h-[60vh] max-h-[60vh]">
            <template x-for="(msg, idx) in messages" :key="idx">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="[
                        'max-w-[80%] rounded-2xl px-4 py-2 shadow-sm',
                        msg.role === 'user'
                            ? 'bg-blue-600 text-white'
                            : msg.isError
                                ? 'bg-red-50 text-red-600 border border-red-200'
                                : 'bg-white text-gray-800 border border-gray-100'
                    ]">
                        <div class="text-sm whitespace-pre-wrap" x-text="msg.content"></div>
                        <div class="text-[10px] mt-1" :class="msg.role === 'user' ? 'text-white/70' : 'text-gray-400'" x-text="formatTime(msg.timestamp)"></div>
                    </div>
                </div>
            </template>

            <div x-show="isLoading" class="flex justify-start">
                <div class="bg-white text-gray-800 rounded-2xl px-4 py-2 shadow-sm border border-gray-100">
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                    </div>
                </div>
            </div>

            <div x-show="messages.length === 0 && !isLoading" class="text-center py-8">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-600 mb-2">Halo! 👋</p>
                <p class="text-xs text-gray-400 mb-4">Tanya apapun tentang pengeluaran belanjamu</p>

                <div class="flex flex-wrap gap-2 justify-center">
                    <button @click="askExample('Total pengeluaranku bulan ini berapa?')"
                            class="text-xs bg-gray-100 text-gray-600 px-3 py-2 rounded-full hover:bg-gray-200 transition">
                        💰 Total bulan ini?
                    </button>
                    <button @click="askExample('Toko mana yang paling sering aku belanja?')"
                            class="text-xs bg-gray-100 text-gray-600 px-3 py-2 rounded-full hover:bg-gray-200 transition">
                        🏪 Toko favorit?
                    </button>
                    <button @click="askExample('Bandingkan pengeluaranku bulan ini vs bulan lalu')"
                            class="text-xs bg-gray-100 text-gray-600 px-3 py-2 rounded-full hover:bg-gray-200 transition">
                        📈 Vs bulan lalu?
                    </button>
                    <button @click="askExample('Barang apa yang paling banyak aku beli?')"
                            class="text-xs bg-gray-100 text-gray-600 px-3 py-2 rounded-full hover:bg-gray-200 transition">
                        🛒 Barang terbanyak?
                    </button>
                    <button @click="askExample('Kasih tips hemat belanja dong')"
                            class="text-xs bg-gray-100 text-gray-600 px-3 py-2 rounded-full hover:bg-gray-200 transition">
                        💡 Tips hemat
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white border-t border-gray-100 sticky bottom-0">
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input x-ref="messageInput"
                       type="text"
                       placeholder="Tanya tentang belanjamu..."
                       :disabled="isLoading"
                       class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition disabled:opacity-50">
                <button type="submit"
                        :disabled="isLoading"
                        class="bg-blue-600 text-white px-5 py-3 rounded-xl font-bold hover:bg-blue-700 transition disabled:opacity-50">
                    <svg x-show="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <svg x-show="isLoading" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </form>
            <p class="text-[10px] text-gray-400 text-center mt-2">
                AI menjawab berdasarkan data struk belanjamu
            </p>
        </div>
    </div>

    <style>
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .animate-bounce {
            animation: bounce 1s infinite;
        }
    </style>
</x-app-layout>
