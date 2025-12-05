<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;

class EmailController extends Controller
{
    public function index(Request $request)
    {
        $emails = [];
        $imapEnabled = extension_loaded('imap');

        return view('emails.index', compact('emails', 'imapEnabled'));
    }

    public function fetch(Request $request)
    {
        $emailComercial = DB::table('correo_comercial')->where('idUser', Auth::user()->id)->first();
        // Verificar si la extensión IMAP está habilitada
        if (!extension_loaded('imap')) {
            return redirect()->route('emails.index')
                ->with('error', 'La extensión IMAP no está habilitada en tu servidor PHP. Por favor, contacta a tu administrador o habilítala en php.ini.');
        }

        try {
            $email = $emailComercial->email_comercial;
            $password = $emailComercial->clave;
            switch ($emailComercial->proveedor) {
                case 'Google':
                    $imapServer = '{imap.gmail.com:993/imap/ssl}INBOX';
                    break;
                case 'Outlook':
                    $imapServer = '{imap-mail.outlook.com:993/imap/ssl}INBOX';
                    break;
                case 'Yahoo':
                    $imapServer = ' {imap.mail.yahoo.com:993/imap/ssl}INBOX';
                    break;
            }


            // Protegemos contra múltiples intentos rápidos que puedan bloquear la cuenta
            $attempts = session()->get('imap_attempts', 0);
            $lastAttempt = session()->get('imap_last_attempt', 0);
            $now = time();

            // Si hay más de 5 intentos en los últimos 15 minutos, bloqueamos temporalmente
            if ($attempts >= 5 && ($now - $lastAttempt) < 15 * 60) {
                return redirect()->route('emails.index')
                    ->with('error', 'Demasiados intentos recientes. Espera 15 minutos antes de intentar de nuevo para evitar bloqueo por seguridad.');
            }

            // Intentar conectar con IMAP
            $mailbox = @imap_open($imapServer, $email, $password);

            if (!$mailbox) {
                $imapErrors = imap_errors() ?: [];
                $imapAlerts = imap_alerts() ?: [];
                $lastImap = imap_last_error() ?: '';

                \Log::warning('IMAP connection failed', [
                    'email' => $email,
                    'server' => $imapServer,
                    'errors' => $imapErrors,
                    'alerts' => $imapAlerts,
                    'last' => $lastImap,
                ]);

                // Incrementar contador de intentos en sesión
                session()->put('imap_attempts', $attempts + 1);
                session()->put('imap_last_attempt', $now);

                $combined = strtolower(implode(' ; ', $imapErrors ?: [$lastImap]));

                // Mensajes amigables según el error
                $userMessage = 'No se pudo conectar. Verifica tus credenciales y servidor IMAP.';

                if (str_contains($combined, 'authenticationfailed') || str_contains($combined, 'invalid credentials') || str_contains($combined, 'auth')) {
                    $userMessage = 'Autenticación fallida: credenciales inválidas. Si usas Gmail/Outlook/Yahoo, revisa que IMAP esté habilitado y considera crear una <strong>contraseña de aplicación</strong> (app password). También revisa alertas de seguridad en la cuenta.';
                } elseif (str_contains($combined, 'too many') || str_contains($combined, 'login failed') || str_contains($combined, 'too many login')) {
                    $userMessage = 'Demasiados intentos de inicio de sesión: la cuenta puede estar temporalmente bloqueada por el proveedor. Espera unos minutos y verifica la seguridad de la cuenta.';
                } elseif (str_contains($combined, 'certificate') || str_contains($combined, 'ssl') || str_contains($combined, 'tls')) {
                    $userMessage = 'Error SSL/TLS al conectar: verifica la cadena IMAP (usa /imap/ssl o puerto 993) y que tu servidor soporte TLS/SSL.';
                } elseif (str_contains($combined, 'timed out') || str_contains($combined, 'could not connect')) {
                    $userMessage = 'Tiempo de conexión agotado: verifica host y puerto, y que no haya un firewall bloqueando el acceso al servidor IMAP.';
                }

                // No exponer detalles sensibles en la interfaz; los dejamos en logs.
                return redirect()->route('emails.index')
                    ->with('error', $userMessage);
            }

            // Obtener información del buzón
            $mailCheck = imap_mailboxmsginfo($mailbox);
      
            $messageCount = $mailCheck->Nmsgs;

            // Obtener últimos 20 correos
            $emails = [];
            $start = max(1, $messageCount - 19);

            for ($i = $messageCount; $i >= $start; $i--) {
                try {
                    $header = imap_headerinfo($mailbox, $i);
                    $fromEmail = '';
                    $fromName = '';
                    if (isset($header->from) && is_array($header->from) && count($header->from) > 0) {
                        $from = $header->from[0];
                        $fromEmail = ($from->mailbox ?? '') . '@' . ($from->host ?? '');
                        $fromName = $from->personal ?? '';
                    }

                    $emails[] = [
                        'id' => $i,
                        'from' => $fromName ?: $fromEmail,
                        'from_email' => $fromEmail,
                        'subject' => html_entity_decode($header->subject ?? '(Sin asunto)'),
                        'date' => $header->date,
                        'size' => $header->Size ?? 0,
                    ];
                } catch (\Exception $e) {
                    \Log::warning("Error reading email $i: " . $e->getMessage());
                    continue;
                }
            }

            imap_close($mailbox);

            return view('emails.index', [
                'emails' => $emails,
                'message' => 'Correos cargados exitosamente. Mostrando últimos ' . count($emails) . ' correos.',
                'email' => $email,
                'imap_server' => $imapServer,
                'imapEnabled' => true,
            ]);

        } catch (\Exception $e) {
            \Log::error('Email Fetch Error: ' . $e->getMessage());
            return redirect()->route('emails.index')
                ->with('error', 'Error al conectar: ' . $e->getMessage());
        }
    }
}