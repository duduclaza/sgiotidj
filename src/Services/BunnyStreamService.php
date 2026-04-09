<?php

namespace App\Services;

use RuntimeException;

class BunnyStreamService
{
    private const API_BASE = 'https://video.bunnycdn.com';

    private string $apiKey;
    private int $libraryId;
    private string $cdnHost;
    private bool $verifySsl;
    private string $caBundlePath;

    public function __construct()
    {
        $this->apiKey = trim((string) ($_ENV['BUNNY_STREAM_API_KEY'] ?? getenv('BUNNY_STREAM_API_KEY') ?: ''));
        $this->libraryId = (int) trim((string) ($_ENV['BUNNY_STREAM_LIBRARY_ID'] ?? getenv('BUNNY_STREAM_LIBRARY_ID') ?: '0'));
        $cdnHost = trim((string) ($_ENV['BUNNY_STREAM_CDN_HOST'] ?? getenv('BUNNY_STREAM_CDN_HOST') ?: ''));
        $cdnHost = preg_replace('~^https?://~i', '', $cdnHost) ?? $cdnHost;
        $this->cdnHost = rtrim($cdnHost, '/');
        $verifySsl = trim((string) ($_ENV['BUNNY_STREAM_VERIFY_SSL'] ?? getenv('BUNNY_STREAM_VERIFY_SSL') ?: ''));
        $verifyFlag = filter_var($verifySsl, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $this->verifySsl = $verifyFlag ?? (PHP_SAPI !== 'cli-server');
        $caBundle = trim((string) ($_ENV['BUNNY_STREAM_CA_BUNDLE'] ?? getenv('BUNNY_STREAM_CA_BUNDLE') ?: ''));
        $this->caBundlePath = ($caBundle !== '' && is_file($caBundle)) ? $caBundle : '';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== ''
            && $this->libraryId > 0
            && $this->cdnHost !== '';
    }

    public function assertConfigured(): void
    {
        if ($this->isConfigured()) {
            return;
        }

        throw new RuntimeException('A integra��o com Bunny Stream ainda n�o foi configurada neste ambiente.');
    }

    public function libraryId(): int
    {
        return $this->libraryId;
    }

    public function cdnHost(): string
    {
        return $this->cdnHost;
    }

    public function createVideo(string $title): array
    {
        $payload = $this->request('POST', "/library/{$this->libraryId}/videos", [
            'title' => $title,
        ]);

        return $this->normalizeVideoPayload($payload);
    }

    public function uploadVideoBinary(string $videoId, string $filePath): void
    {
        $this->requestBinary('PUT', "/library/{$this->libraryId}/videos/{$videoId}", $filePath);
    }

    public function getVideo(string $videoId): array
    {
        $payload = $this->request('GET', "/library/{$this->libraryId}/videos/{$videoId}");

        return $this->normalizeVideoPayload($payload);
    }

    public function uploadVideo(string $title, string $filePath): array
    {
        $created = $this->createVideo($title);
        $videoId = (string) ($created['video_id'] ?? '');
        if ($videoId === '') {
            throw new RuntimeException('O Bunny Stream n�o retornou o identificador do v�deo criado.');
        }

        try {
            $this->extendExecutionTime(660);
            $this->uploadVideoBinary($videoId, $filePath);
            return $created;
        } catch (\Throwable $exception) {
            try {
                $this->deleteVideo($videoId);
            } catch (\Throwable) {
                // Best effort cleanup for half-uploaded assets.
            }
            throw $exception;
        }
    }

    public function deleteVideo(string $videoId): void
    {
        if ($videoId === '' || !$this->isConfigured()) {
            return;
        }

        try {
            $this->request('DELETE', "/library/{$this->libraryId}/videos/{$videoId}", null, false);
        } catch (RuntimeException $exception) {
            if (!str_contains($exception->getMessage(), '[404]')) {
                throw $exception;
            }
        }
    }

    public function embedUrl(string $videoId): string
    {
        return 'https://iframe.mediadelivery.net/embed/' . $this->libraryId . '/' . $videoId;
    }

    public function playbackUrl(string $videoId): string
    {
        return self::API_BASE . '/play/' . $this->libraryId . '/' . $videoId;
    }

    public function playlistUrl(string $videoId): string
    {
        return 'https://' . $this->cdnHost . '/' . $videoId . '/playlist.m3u8';
    }

    public function mp4Url(string $videoId, int $resolutionHeight = 720): string
    {
        return 'https://' . $this->cdnHost . '/' . $videoId . '/play_' . max(240, $resolutionHeight) . 'p.mp4';
    }

    private function request(string $method, string $path, ?array $payload = null, bool $expectsJson = true): array
    {
        $this->assertConfigured();

        $ch = curl_init(self::API_BASE . $path);
        if ($ch === false) {
            throw new RuntimeException('N�o foi poss�vel iniciar a requisi��o ao Bunny Stream.');
        }

        $headers = [
            'AccessKey: ' . $this->apiKey,
            'Accept: application/json',
        ];

        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
        ]);
        $this->applyTlsOptions($ch);

        $body = curl_exec($ch);
        if ($body === false) {
            $message = $this->curlErrorMessage($ch, 'Falha ao comunicar com o Bunny Stream.');
            throw new RuntimeException($message);
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if ($statusCode >= 400) {
            $message = $this->extractErrorMessage($body);
            throw new RuntimeException("Bunny Stream respondeu com erro [{$statusCode}]: {$message}");
        }

        if (!$expectsJson || $body === '' || $body === 'null') {
            return [];
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('O Bunny Stream retornou uma resposta inv�lida.');
        }

        return $decoded;
    }

    private function requestBinary(string $method, string $path, string $filePath): void
    {
        $this->assertConfigured();

        $handle = @fopen($filePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('N�o foi poss�vel abrir o arquivo para envio ao Bunny Stream.');
        }

        $fileSize = (int) (@filesize($filePath) ?: 0);
        $ch = curl_init(self::API_BASE . $path);
        if ($ch === false) {
            fclose($handle);
            throw new RuntimeException('N�o foi poss�vel iniciar o upload no Bunny Stream.');
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_UPLOAD => true,
            CURLOPT_INFILE => $handle,
            CURLOPT_INFILESIZE => $fileSize,
            CURLOPT_HTTPHEADER => [
                'AccessKey: ' . $this->apiKey,
                'Accept: application/json',
                'Content-Type: application/octet-stream',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 600,
        ]);
        $this->applyTlsOptions($ch);
        $this->extendExecutionTime(660);

        $body = curl_exec($ch);
        if ($body === false) {
            $message = $this->curlErrorMessage($ch, 'Falha ao enviar o arquivo para o Bunny Stream.');
            fclose($handle);
            throw new RuntimeException($message);
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        fclose($handle);

        if ($statusCode >= 400) {
            $message = $this->extractErrorMessage($body);
            throw new RuntimeException("Upload para Bunny Stream falhou [{$statusCode}]: {$message}");
        }
    }

    private function normalizeVideoPayload(array $payload): array
    {
        $videoId = (string) ($payload['guid'] ?? $payload['videoGuid'] ?? $payload['id'] ?? '');
        $durationSeconds = (int) round((float) ($payload['length'] ?? $payload['duration'] ?? $payload['duration_seconds'] ?? 0));

        $availableResolutions = [];
        if (isset($payload['availableResolutions']) && is_array($payload['availableResolutions'])) {
            foreach ($payload['availableResolutions'] as $resolution) {
                $value = (int) preg_replace('/\D+/', '', (string) $resolution);
                if ($value > 0) {
                    $availableResolutions[] = $value;
                }
            }
            rsort($availableResolutions);
        }

        $preferredResolution = in_array(720, $availableResolutions, true)
            ? 720
            : ($availableResolutions[0] ?? 720);

        return [
            'video_id' => $videoId,
            'title' => (string) ($payload['title'] ?? ''),
            'status' => (string) ($payload['status'] ?? ''),
            'duration_seconds' => max(0, $durationSeconds),
            'thumbnail_file_name' => (string) ($payload['thumbnailFileName'] ?? ''),
            'available_resolutions' => $availableResolutions,
            'preferred_resolution' => $preferredResolution,
            'embed_url' => $videoId !== '' ? $this->embedUrl($videoId) : null,
            'playback_url' => $videoId !== '' ? $this->playbackUrl($videoId) : null,
            'playlist_url' => $videoId !== '' ? $this->playlistUrl($videoId) : null,
            'mp4_url' => $videoId !== '' ? $this->mp4Url($videoId, $preferredResolution) : null,
            'thumbnail_url' => ($videoId !== '' && !empty($payload['thumbnailFileName']))
                ? 'https://' . $this->cdnHost . '/' . $videoId . '/' . $payload['thumbnailFileName']
                : null,
        ];
    }

    private function extendExecutionTime(int $seconds): void
    {
        if (!function_exists('set_time_limit')) {
            return;
        }

        @set_time_limit($seconds);
    }

    private function applyTlsOptions($ch): void
    {
        $options = [
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
        ];

        if ($this->caBundlePath !== '') {
            $options[CURLOPT_CAINFO] = $this->caBundlePath;
        }

        curl_setopt_array($ch, $options);
    }

    private function curlErrorMessage($ch, string $fallback): string
    {
        $message = trim((string) (curl_error($ch) ?: $fallback));
        $lower = strtolower($message);

        if (
            str_contains($lower, 'ssl certificate')
            || str_contains($lower, 'self-signed')
            || str_contains($lower, 'certificate chain')
        ) {
            $message .= ' Ajuste o CA bundle do Bunny no ambiente ou desative a verificacao SSL apenas no preview local.';
        }

        return $message;
    }

    private function extractErrorMessage(string $body): string
    {
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            foreach (['message', 'Message', 'error', 'Error'] as $key) {
                if (!empty($decoded[$key]) && is_string($decoded[$key])) {
                    return $decoded[$key];
                }
            }
        }

        return trim($body) !== '' ? trim($body) : 'Erro desconhecido.';
    }
}
