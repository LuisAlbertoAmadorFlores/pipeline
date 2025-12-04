@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                <h1 class="text-2xl font-bold text-white">Asistente de Ventas</h1>
                <p class="text-blue-100 text-sm">Powered by DeepSeek</p>
            </div>

            <!-- Chat Messages Container -->
            <div id="messages-container" class="flex flex-col h-96 overflow-y-auto bg-gray-50 p-6 space-y-4">
                @if (isset($messages) && count($messages) > 0)
                    @foreach ($messages as $m)
                        @php
                            $sender = $m->role === 'user' ? 'user' : 'assistant';
                        @endphp
                        <div class="flex {{ $sender === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div
                                class="rounded-lg px-4 py-2 max-w-xs whitespace-pre-wrap break-words {{ $sender === 'user' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                                {!! e($m->message) !!}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="flex justify-center">
                        <div class="bg-gray-300 text-gray-700 rounded-lg px-4 py-2 text-sm">
                            Bienvenido. ¿En qué puedo ayudarte con problemas técnicos?
                        </div>
                    </div>
                @endif
            </div>

            <!-- Input Area -->
            <div class="border-t border-gray-200 bg-white p-6">
                <div class="flex gap-3">
                    <input type="text" id="message-input" placeholder="Escribe tu pregunta de soporte técnico..."
                        class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @keyup.enter="sendMessage()" />
                    <button id="send-btn"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Enviar
                    </button>
                </div>
                <div id="loading-indicator" class="hidden mt-2 text-sm text-gray-500 flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Procesando...
                </div>
            </div>
        </div>
    </div>

    <script>
        const messagesContainer = document.getElementById('messages-container');
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        const loadingIndicator = document.getElementById('loading-indicator');

        sendBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Add user message to chat
            addMessage(message, 'user');
            messageInput.value = '';

            // Show loading indicator
            loadingIndicator.classList.remove('hidden');

            // Send to backend
            fetch('{{ route('chatbot.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        message: message
                    })
                })
                .then(response => response.json())
                .then(data => {
                    loadingIndicator.classList.add('hidden');
                    if (data.reply) {
                        addMessage(data.reply, 'assistant');
                    } else if (data.error) {
                        addMessage('Error: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    loadingIndicator.classList.add('hidden');
                    addMessage('Error de conexión: ' + error.message, 'error');
                });
        }

        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('flex', sender === 'user' ? 'justify-end' : 'justify-start');

            const messageContent = document.createElement('div');
            messageContent.classList.add(
                'rounded-lg',
                'px-4',
                'py-2',
                'max-w-xs',
                'whitespace-pre-wrap',
                'break-words'
            );

            if (sender === 'user') {
                messageContent.classList.add('bg-blue-600', 'text-white');
            } else if (sender === 'assistant') {
                messageContent.classList.add('bg-gray-200', 'text-gray-800');
            } else if (sender === 'error') {
                messageContent.classList.add('bg-red-100', 'text-red-800');
            }

            messageContent.textContent = text;
            messageDiv.appendChild(messageContent);
            messagesContainer.appendChild(messageDiv);

            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    </script>
@endsection
