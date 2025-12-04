<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * Mostrar la página para abrir WhatsApp.
     */
    public function index()
    {
        return view('whatsapp.index');
    }

    /**
     * Mostrar la vista que intenta embeber WhatsApp Web.
     * Nota: Muchos navegadores y web.whatsapp.com usan cabeceras que
     * impiden ser embebidos en iframes (X-Frame-Options). Esta vista
     * provee un intento y un fallback claro para abrir en nueva pestaña.
     */
    public function view()
    {
        return view('whatsapp.view');
    }

    /**
     * Redirige al usuario a wa.me con número y mensaje opcional.
     */
    public function open(Request $request)
    {
        $phone = $request->input('phone');
        $message = $request->input('message', '');

        // Normalizar: dejar solo dígitos
        $phoneDigits = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phoneDigits)) {
            return redirect()->route('whatsapp.index')->with('error', 'Número de teléfono inválido.');
        }

        $encoded = rawurlencode($message);
        $url = "https://wa.me/{$phoneDigits}";
        if (!empty($encoded)) {
            $url .= "?text={$encoded}";
        }

        return redirect()->away($url);
    }
}
