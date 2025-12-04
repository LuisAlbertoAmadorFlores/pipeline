@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4">
        <h1 class="text-3xl font-bold mb-6">Correos Electrónicos</h1>

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

        @if (!$imapEnabled ?? false)
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-6">
                <p class="font-bold">⚠️ Extensión IMAP no habilitada</p>
                <p class="text-sm mt-1">La extensión PHP IMAP no está habilitada en tu servidor. Para usar esta
                    funcionalidad:</p>
                <ol class="text-sm mt-2 ml-4 list-decimal">
                    <li>Abre el archivo <code class="bg-yellow-50 px-1">php.ini</code></li>
                    <li>Busca la línea <code class="bg-yellow-50 px-1">;extension=php_imap.dll</code> (Windows) o <code
                            class="bg-yellow-50 px-1">; extension=imap.so</code> (Linux)</li>
                    <li>Descomenta la línea (elimina el <code class="bg-yellow-50 px-1">;</code> al inicio)</li>
                    <li>Reinicia tu servidor web</li>
                </ol>
            </div>
        @endif

        <!-- Formulario de conexión -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Conectar a tu correo</h2>

            <form method="POST" action="{{ route('emails.fetch') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                        <input type="email" name="email" id="email"
                            class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            placeholder="tu@correo.com" required>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            placeholder="Contraseña o contraseña de aplicación" required>
                    </div>

                    <div class="md:col-span-2">
                        <label for="imap_server" class="block text-sm font-medium text-gray-700">Servidor IMAP</label>
                        <input type="text" name="imap_server" id="imap_server"
                            class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            placeholder="Ej: {imap.gmail.com:993/imap/ssl}INBOX"
                            value="{{ old('imap_server', '{imap.gmail.com:993/imap/ssl}INBOX') }}" required>
                        <p class="mt-1 text-xs text-gray-500">
                            <strong>Gmail:</strong> {imap.gmail.com:993/imap/ssl}INBOX |
                            <strong>Outlook:</strong> {imap-mail.outlook.com:993/imap/ssl}INBOX |
                            <strong>Yahoo:</strong> {imap.mail.yahoo.com:993/imap/ssl}INBOX
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 font-medium"
                        {{ !($imapEnabled ?? false) ? 'disabled' : '' }}>
                        Cargar Correos
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de correos -->
        @if (count($emails ?? []) > 0)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">De</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asunto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tamaño</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($emails as $email)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $email['from'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $email['subject'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $email['date'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ round($email['size'] / 1024, 2) }} KB</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($imapEnabled ?? false)
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <p class="text-gray-600">Conecta tu correo para ver los mensajes.</p>
            </div>
        @endif
    </div>
@endsection
