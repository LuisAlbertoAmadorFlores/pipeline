@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">Abrir WhatsApp</h1>

        @if ($message = session('message'))
            <div class="mb-4 bg-green-100 border border-green-200 text-green-800 px-4 py-2 rounded">
                {{ $message }}
            </div>
        @endif

        @if ($message = session('error'))
            <div class="mb-4 bg-red-100 border border-red-200 text-red-800 px-4 py-2 rounded">
                {{ $message }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form id="waForm" method="POST" action="{{ route('whatsapp.open') }}">
                @csrf

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Número (sin +)</label>
                        <input id="phone" name="phone" type="tel" placeholder="5215512345678" required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2" />
                        <p class="text-xs text-gray-500 mt-1">Incluye código de país y clave (ej: 5215512345678 para
                            México).</p>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Mensaje (opcional)</label>
                        <textarea id="message" name="message" rows="3"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2" placeholder="Hola, quiero consultar..."></textarea>
                    </div>
                </div>

                <div class="mt-4 flex gap-3">
                    <button type="button" id="openClient"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Abrir en WhatsApp Web / App
                    </button>

                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Abrir (redirigir desde servidor)
                    </button>

                    <a target="_blank" rel="noopener" href="https://web.whatsapp.com/"
                        class="ml-auto text-sm text-gray-600 hover:underline">
                        Ir a WhatsApp Web</a>
                    <a href="{{ route('whatsapp.view') }}"
                        class="ml-4 text-sm px-3 py-2 bg-gray-100 rounded hover:bg-gray-200">Ver embebido</a>
                </div>
            </form>
        </div>

        <div class="text-sm text-gray-600">
            <p>Nota: Si estás en dispositivo móvil el enlace abrirá la app de WhatsApp si está instalada.</p>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('openClient').addEventListener('click', function() {
                const phone = document.getElementById('phone').value.replace(/[^0-9]/g, '');
                const message = document.getElementById('message').value;
                if (!phone) {
                    alert('Por favor ingresa un número de teléfono válido.');
                    return;
                }
                const encoded = encodeURIComponent(message || '');
                let url = `https://wa.me/${phone}`;
                if (encoded) url += `?text=${encoded}`;
                window.open(url, '_blank');
            });
        </script>
    @endpush
@endsection
