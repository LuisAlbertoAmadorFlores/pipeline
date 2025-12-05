@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div>
            <div class="flex justify-between aling-items-center">
                <h1 class="text-3xl font-bold mb-6">Bandeja de Entrada</h1>
                <form method="POST" action="{{ route('emails.fetch') }}">
                    @csrf
                    <div class="">
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 "
                            {{ !($imapEnabled ?? false) ? 'disabled' : '' }}>
                            <i class="fa-solid fa-rotate me-1"></i> Sincronizar
                        </button>
                    </div>
                </form>
            </div>
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

        </div>

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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($emails as $email)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $email['from'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $email['subject'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ date('d-m-Y h:m', strtotime($email['date'])) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ round($email['size'] / 1024, 2) }} KB</td>
                                <td>
                                    <button onclick="toggleModal('nuevo-lead-modal')"
                                        class="bg-yellow-600 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded shadow-lg transition me-2">Leer
                                    </button>

                                    <div id="nuevo-lead-modal" class="hidden relative z-50" aria-labelledby="modal-title"
                                        role="dialog" aria-modal="true">

                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                                        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                            <div
                                                class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                                                <div
                                                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                                                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                                        <div class="sm:flex sm:items-start">
                                                            <div
                                                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                                                <svg class="h-6 w-6 text-blue-600" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.5"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                                                                </svg>
                                                            </div>
                                                            <div
                                                                class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                                                <h3 class="text-base font-semibold leading-6 text-gray-900"
                                                                    id="modal-title">Agregar Nuevo Lead</h3>
                                                                <div class="mt-2">
                                                                    <p class="text-sm text-gray-500">Ingresa los datos del
                                                                        cliente potencial para agregarlo al Pipeline.</p>
                                                                    <input type="text" placeholder="Nombre del Cliente"
                                                                        class="mt-3 w-full border border-gray-300 rounded p-2 text-sm">
                                                                    <input type="email" placeholder="Correo Electrónico"
                                                                        class="mt-2 w-full border border-gray-300 rounded p-2 text-sm">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                                        <button type="button"
                                                            class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto"
                                                            onclick="toggleModal('nuevo-lead-modal')">
                                                            Guardar
                                                        </button>
                                                        <button type="button"
                                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                                            onclick="toggleModal('nuevo-lead-modal')">
                                                            Cancelar
                                                        </button>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <script>
                                        function toggleModal(modalID) {
                                            const modal = document.getElementById(modalID);
                                            // Alterna la clase 'hidden' para mostrar/ocultar
                                            modal.classList.toggle('hidden');
                                        }
                                    </script>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($imapEnabled ?? false)
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <p class="text-gray-600">Configura tus accessos de correo comercial en <a href="{{ route('profile.edit') }}"
                        class="link">Mi perfil</a>, despues solo sincroniza tu bandeja.</p>
            </div>
        @endif
    </div>
@endsection
