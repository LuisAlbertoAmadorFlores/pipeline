@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-6 px-4">
        <h1 class="text-2xl font-bold mb-4">WhatsApp embebido</h1>

        <div class="bg-white shadow rounded-lg p-4 mb-4">
            <p class="text-sm text-gray-700 mb-2">Intento de mostrar <strong>WhatsApp Web</strong> embebido en un iframe.</p>
            <p class="text-sm text-gray-600 mb-4">Si no se muestra el contenido, tu navegador o el propio servicio bloquean
                el embedding por razones de seguridad. Usa el botón de abajo para abrir en una nueva pestaña.</p>

            <div class="w-full h-[700px] border">
                <iframe id="waIframe" src="https://web.whatsapp.com/" class="w-full h-full" frameborder="0"
                    sandbox="allow-forms allow-scripts allow-same-origin allow-popups"></iframe>
            </div>

            <div id="embedNotice" class="mt-4 hidden bg-yellow-50 border border-yellow-200 text-yellow-800 p-3 rounded">
                <p class="font-medium">El contenido está bloqueado para ser embebido.</p>
                <p class="text-sm mt-1">Esto es normal: <code>web.whatsapp.com</code> suele enviar cabeceras que impiden
                    iframes. Pulsa el botón para abrir WhatsApp Web en una nueva pestaña.</p>
            </div>

            <div class="mt-4 flex gap-3">
                <a target="_blank" rel="noopener" href="https://web.whatsapp.com/"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Abrir WhatsApp Web</a>
                <a target="_blank" rel="noopener" href="https://web.whatsapp.com/"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Abrir en nueva pestaña</a>
            </div>
        </div>

        <div class="text-sm text-gray-500">
            <p>Consejo: si quieres usar WhatsApp desde el navegador, escanea el código QR en la página de WhatsApp Web con
                tu app móvil.</p>
        </div>
    </div>

    @push('scripts')
        <script>
            // Detectar si el iframe quedó en blanco por bloqueo
            (function() {
                const iframe = document.getElementById('waIframe');
                const notice = document.getElementById('embedNotice');

                // Después de cargar intentamos acceder al contenido; si ocurre un error de cross-origin
                // o el documento está vacío probablemente fue bloqueado.
                function checkIframe() {
                    try {
                        const doc = iframe.contentDocument || iframe.contentWindow.document;
                        // Si el documento está vacío o tiene poco contenido, asumimos bloqueo.
                        if (!doc || !doc.body || doc.body.innerHTML.trim().length < 20) {
                            notice.classList.remove('hidden');
                            return;
                        }
                        // Si llegamos aquí, el iframe cargó contenido (raro por X-Frame-Options).
                    } catch (e) {
                        // Acceso cross-origin falló -> muy probable bloqueo por X-Frame-Options/CSP
                        notice.classList.remove('hidden');
                    }
                }

                // Esperar unos segundos para dar tiempo a la carga/ bloqueo.
                setTimeout(checkIframe, 1500);
            })();
        </script>
    @endpush
@endsection
