<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class ChatBotController extends Controller
{
    private $deepseekUrl = 'https://ia.diinsystem.ddns.net/api/generate';

    public function index()
    {
        // Cargar historial del usuario autenticado (últimos 100 mensajes)
        $userId = Auth::id();
        $messages = ChatMessage::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->take(100)
            ->get();

        return view('chatbot.index', compact('messages'));
    }

    public function sendMessage(Request $request)
    {
        $message = $request->input('message');

        if (!$message) {
            return response()->json(['error' => 'Message is required'], 400);
        }

        try {
            $userId = Auth::id();

            // Guardar el mensaje del usuario en el historial
            try {
                ChatMessage::create([
                    'user_id' => $userId,
                    'role' => 'user',
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to save user chat message: ' . $e->getMessage());
            }

            $response = Http::timeout(60)->withoutVerifying()->post($this->deepseekUrl, [
                'model' => 'diin',
                'prompt' => 'Eres un asistente de soporte técnico especializado en ayudar a resolver problemas técnicos. Responde de manera clara, concisa y profesional en español.No puedes responder cosas de otros temas. Usuario pregunta: ' . $message,
                'stream' => false
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Debug: log the full response
                \Log::info('DeepSeek Response:', $data);

                // La API devuelve la respuesta en el campo 'response'
                $reply = trim($data['response'] ?? '');

                if (!$reply) {
                    // Guardar respuesta vacía/errores como asistente para historial
                    try {
                        ChatMessage::create([
                            'user_id' => $userId,
                            'role' => 'assistant',
                            'message' => 'El modelo no generó respuesta. Intenta de nuevo.',
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to save assistant empty reply: ' . $e->getMessage());
                    }
                    return response()->json(['error' => 'El modelo no generó respuesta. Intenta de nuevo.'], 500);
                }

                // Guardar la respuesta del asistente en el historial
                try {
                    ChatMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'message' => $reply,
                        'metadata' => $data,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to save assistant reply: ' . $e->getMessage());
                }

                return response()->json(['reply' => $reply]);
            } else {
                \Log::error('DeepSeek Error Response:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'Error al conectar con el servicio de IA: ' . $response->status()], 500);
            }
        } catch (\Exception $e) {
            \Log::error('ChatBot Exception:', ['error' => $e->getMessage()]);
            // Guardar el error como mensaje del asistente (para diagnóstico)
            try {
                ChatMessage::create([
                    'user_id' => Auth::id(),
                    'role' => 'assistant',
                    'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
                ]);
            } catch (\Exception $ex) {
                \Log::warning('Failed to save assistant error message: ' . $ex->getMessage());
            }

            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
