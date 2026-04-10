<?php

namespace App\Services;

/**
 * Serviço de envio de e-mails via API Resend
 * 
 * Substitui o PHPMailer/SMTP por API REST mais simples e confiável
 */
class ResendService
{
    private string $apiKey;
    private string $fromEmail;
    private string $fromName;
    private ?string $lastError = null;

    public function __construct()
    {
        // Priorizar .env para API Key
        $this->apiKey = $this->env('RESEND_API_KEY', '');

        // Email do domínio verificado no Resend
        $this->fromEmail = $this->env('RESEND_FROM_EMAIL', 'suporte@tiuai.com.br');
        $this->fromName = $this->env('MAIL_FROM_NAME', 'SGI ATLAS');
    }

    /**
     * Obter variável de ambiente
     */
    private function env(string $key, $default = null)
    {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
        return $default;
    }

    /**
     * Enviar email via API Resend
     * 
     * @param string|array $to Destinatário(s)
     * @param string $subject Assunto
     * @param string $html Corpo HTML
     * @param string|null $text Corpo texto plano (opcional)
     * @param array $attachments Array de caminhos de arquivos
     * @return bool
     */
    public function send($to, string $subject, string $html, ?string $text = null, array $attachments = []): bool
    {
        $this->lastError = null;

        try {
            error_log("=== ENVIANDO EMAIL VIA RESEND API ===");
            error_log("Para: " . (is_array($to) ? implode(', ', $to) : $to));
            error_log("Assunto: " . $subject);

            // Preparar destinatários
            $recipients = is_array($to) ? $to : [$to];
            $recipients = array_values(array_filter($recipients)); // Remove vazios
            $this->logResend('send:start', [
                'subject' => $subject,
                'recipient_count' => count($recipients),
                'recipients' => $recipients,
                'attachment_count' => count($attachments),
                'api_key_configured' => $this->apiKey !== '',
                'from_email' => $this->fromEmail,
            ]);

            if (empty($recipients)) {
                $this->lastError = 'Nenhum destinatário válido';
                error_log("❌ Erro: " . $this->lastError);
                return false;
            }

            if ($this->apiKey === '') {
                $this->lastError = 'RESEND_API_KEY nao configurada';
                $this->logResend('send:config_missing', [
                    'subject' => $subject,
                    'from_email' => $this->fromEmail,
                ]);
                error_log('[Resend] ' . $this->lastError);
                return false;
            }

            // Montar payload da API
            $payload = [
                'from' => "{$this->fromName} <{$this->fromEmail}>",
                'to' => $recipients,
                'subject' => $subject,
                'html' => $html,
            ];

            if ($text) {
                $payload['text'] = $text;
            }

            // Adicionar anexos se houver
            if (!empty($attachments)) {
                $payload['attachments'] = [];
                foreach ($attachments as $filePath) {
                    if (file_exists($filePath)) {
                        $content = base64_encode(file_get_contents($filePath));
                        $payload['attachments'][] = [
                            'filename' => basename($filePath),
                            'content' => $content
                        ];
                    }
                }
            }

            // Fazer requisição para API Resend
            if (!function_exists('curl_init')) {
                $this->lastError = 'Extensao cURL nao disponivel no servidor';
                $this->logResend('send:curl_unavailable', [
                    'subject' => $subject,
                    'recipient_count' => count($recipients),
                ]);
                error_log("âŒ " . $this->lastError);
                return false;
            }

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://api.resend.com/emails',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            curl_close($ch);

            if ($curlError) {
                $this->lastError = "Erro cURL: " . $curlError;
                $this->logResend('send:curl_error', [
                    'subject' => $subject,
                    'http_code' => $httpCode,
                    'error' => $curlError,
                ]);
                error_log("❌ " . $this->lastError);
                return false;
            }

            $responseData = json_decode($response, true);
            $this->logResend('send:response', [
                'subject' => $subject,
                'http_code' => $httpCode,
                'response_id' => $responseData['id'] ?? null,
                'message' => $responseData['message'] ?? $responseData['error'] ?? null,
            ]);

            error_log("Resposta API (HTTP {$httpCode}): " . $response);

            if ($httpCode >= 200 && $httpCode < 300) {
                error_log("✅ Email enviado com sucesso via Resend! ID: " . ($responseData['id'] ?? 'N/A'));
                $this->logResend('send:success', [
                    'subject' => $subject,
                    'http_code' => $httpCode,
                    'recipient_count' => count($recipients),
                    'response_id' => $responseData['id'] ?? null,
                ]);
                return true;
            }

            // Erro na API
            $this->lastError = $responseData['message'] ?? $responseData['error'] ?? "Erro HTTP {$httpCode}";
            $this->logResend('send:api_error', [
                'subject' => $subject,
                'http_code' => $httpCode,
                'message' => $this->lastError,
                'response' => $responseData,
            ]);
            error_log("❌ Erro Resend API: " . $this->lastError);
            return false;

        }
        catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            $this->logResend('send:exception', [
                'subject' => $subject,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            error_log("❌ Exceção ao enviar email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter último erro
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Testar conexão com API
     */
    public function testConnection(): array
    {
        try {
            $this->logResend('connection:test_start', [
                'api_key_configured' => $this->apiKey !== '',
                'from_email' => $this->fromEmail,
            ]);

            if ($this->apiKey === '') {
                $this->logResend('connection:config_missing', []);
                return [
                    'success' => false,
                    'message' => 'RESEND_API_KEY nao configurada'
                ];
            }

            if (!function_exists('curl_init')) {
                $this->logResend('connection:curl_unavailable', []);
                return [
                    'success' => false,
                    'message' => 'Extensao cURL nao disponivel no servidor'
                ];
            }

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://api.resend.com/domains',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 10,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            curl_close($ch);

            if ($curlError) {
                $this->logResend('connection:curl_error', [
                    'error' => $curlError,
                    'http_code' => $httpCode,
                ]);
                return [
                    'success' => false,
                    'message' => 'Erro de conexão: ' . $curlError
                ];
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                $this->logResend('connection:success', [
                    'http_code' => $httpCode,
                ]);
                return [
                    'success' => true,
                    'message' => 'Conexão com API Resend OK!'
                ];
            }

            $responseData = json_decode($response, true);
            $this->logResend('connection:api_error', [
                'http_code' => $httpCode,
                'response' => $responseData,
            ]);
            return [
                'success' => false,
                'message' => 'Erro API: ' . ($responseData['message'] ?? "HTTP {$httpCode}")
            ];

        }
        catch (\Throwable $e) {
            $this->logResend('connection:exception', [
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return [
                'success' => false,
                'message' => 'Exceção: ' . $e->getMessage()
            ];
        }
    }

    private function logResend(string $event, array $context = []): void
    {
        $normalized = [];
        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $normalized[$key] = $value;
                continue;
            }

            if (is_object($value)) {
                $normalized[$key] = get_class($value);
                continue;
            }

            $normalized[$key] = $value;
        }

        $flags = 0;
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $flags |= JSON_UNESCAPED_UNICODE;
        }
        if (defined('JSON_UNESCAPED_SLASHES')) {
            $flags |= JSON_UNESCAPED_SLASHES;
        }

        $payload = json_encode($normalized, $flags);
        if ($payload === false) {
            $payload = json_encode(['json_error' => json_last_error_msg()]);
        }

        error_log('[Resend][' . $event . '] ' . $payload);
    }
}
