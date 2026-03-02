<?php

namespace App\Controllers;

use App\Config\Database;

class ChatController
{
    private const AI_BOT_ID = -1000;
    private const AI_BOT_NAME = 'Eduardo do Suporte';
    private const AI_BOT_EMAIL = 'eduardo.suporte.ai@sgq.local';
    private const AI_BOT_AVATAR_URL = '/assets/daniel-suporte.svg';

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureChatTables();
        $this->purgeOldMessages();
    }

    private function ensureChatTables(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS chat_messages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message TEXT NOT NULL,
            payload_json LONGTEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_chat_pair (sender_id, receiver_id),
            INDEX idx_chat_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM chat_messages LIKE 'payload_json'");
            if (!$checkColumn || $checkColumn->rowCount() === 0) {
                $this->db->exec("ALTER TABLE chat_messages ADD COLUMN payload_json LONGTEXT NULL AFTER message");
            }
        } catch (\Throwable $e) {
            // manter compatibilidade sem interromper o chat
        }

        $this->db->exec("CREATE TABLE IF NOT EXISTS chat_user_presence (
            user_id INT PRIMARY KEY,
            last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_presence_last_seen (last_seen)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function purgeOldMessages(): void
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM chat_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute();
        } catch (\Throwable $e) {
            // não bloquear o uso do chat por falha de limpeza
        }
    }

    private function requireAuthJson(): ?int
    {
        header('Content-Type: application/json');

        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
            return null;
        }

        return $userId;
    }

    public function heartbeat(): void
    {
        $userId = $this->requireAuthJson();
        if ($userId === null) {
            return;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO chat_user_presence (user_id, last_seen)
                VALUES (?, NOW())
                ON DUPLICATE KEY UPDATE last_seen = NOW()");
            $stmt->execute([$userId]);

            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar presença']);
        }
    }

    public function contacts(): void
    {
        $userId = $this->requireAuthJson();
        if ($userId === null) {
            return;
        }

        try {
            $this->touchPresence($userId);

            $sql = "SELECT
                        u.id,
                        u.name,
                        u.email,
                        CASE WHEN u.profile_photo IS NOT NULL THEN 1 ELSE 0 END AS has_photo,
                        CASE
                            WHEN p.last_seen IS NOT NULL AND p.last_seen >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)
                            THEN 1
                            ELSE 0
                        END AS is_online,
                        COALESCE(unread.total_unread, 0) AS unread_count,
                        p.last_seen
                    FROM users u
                    LEFT JOIN chat_user_presence p ON p.user_id = u.id
                    LEFT JOIN (
                        SELECT sender_id, COUNT(*) AS total_unread
                        FROM chat_messages
                        WHERE receiver_id = ? AND read_at IS NULL
                        GROUP BY sender_id
                    ) unread ON unread.sender_id = u.id
                    WHERE u.id <> ?
                    ORDER BY is_online DESC, unread_count DESC, u.name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $userId]);
            $contacts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($contacts as &$contact) {
                $contact['is_ai'] = 0;
                $contact['avatar_url'] = null;
            }
            unset($contact);

            $botUnreadStmt = $this->db->prepare("SELECT COUNT(*) FROM chat_messages WHERE sender_id = ? AND receiver_id = ? AND read_at IS NULL");
            $botUnreadStmt->execute([self::AI_BOT_ID, $userId]);
            $botUnreadCount = (int)$botUnreadStmt->fetchColumn();

            array_unshift($contacts, [
                'id' => self::AI_BOT_ID,
                'name' => self::AI_BOT_NAME,
                'email' => self::AI_BOT_EMAIL,
                'has_photo' => 1,
                'is_online' => 1,
                'unread_count' => $botUnreadCount,
                'last_seen' => date('Y-m-d H:i:s'),
                'is_ai' => 1,
                'avatar_url' => self::AI_BOT_AVATAR_URL
            ]);

            echo json_encode([
                'success' => true,
                'contacts' => $contacts,
                'me' => [
                    'id' => $userId,
                    'name' => $_SESSION['user_name'] ?? 'Você'
                ]
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar contatos']);
        }
    }

    public function getMessages($contactId): void
    {
        $userId = $this->requireAuthJson();
        if ($userId === null) {
            return;
        }

        $contactId = (int)$contactId;
        if ($contactId <= 0 && $contactId !== self::AI_BOT_ID) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Contato inválido']);
            return;
        }

        try {
            $this->touchPresence($userId);

            $markReadStmt = $this->db->prepare("UPDATE chat_messages
                SET read_at = NOW()
                WHERE sender_id = ?
                  AND receiver_id = ?
                  AND read_at IS NULL");
            $markReadStmt->execute([$contactId, $userId]);

            $stmt = $this->db->prepare("SELECT
                    id,
                    sender_id,
                    receiver_id,
                    message,
                    payload_json,
                    created_at,
                    read_at
                FROM chat_messages
                WHERE (sender_id = ? AND receiver_id = ?)
                   OR (sender_id = ? AND receiver_id = ?)
                ORDER BY id DESC
                LIMIT 100");
            $stmt->execute([$userId, $contactId, $contactId, $userId]);
            $messages = array_reverse($stmt->fetchAll(\PDO::FETCH_ASSOC));

            foreach ($messages as &$msg) {
                if (!empty($msg['payload_json'])) {
                    $payload = json_decode((string)$msg['payload_json'], true);
                    if (is_array($payload) && isset($payload['text'])) {
                        $msg['message'] = (string)$payload['text'];
                    }
                }
                unset($msg['payload_json']);
            }
            unset($msg);

            echo json_encode(['success' => true, 'messages' => $messages]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar mensagens']);
        }
    }

    public function getGlobalMessages(): void
    {
        $userId = $this->requireAuthJson();
        if ($userId === null) {
            return;
        }

        http_response_code(410);
        echo json_encode(['success' => false, 'message' => 'Sala geral desativada. Use conversas privadas.']);
    }

    public function sendMessage(): void
    {
        $userId = $this->requireAuthJson();
        if ($userId === null) {
            return;
        }

        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $message = trim((string)($_POST['message'] ?? ''));

        if (($receiverId <= 0 && $receiverId !== self::AI_BOT_ID) || $message === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Destinatário e mensagem são obrigatórios']);
            return;
        }

        if (mb_strlen($message) > 2000) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Mensagem muito longa (máximo 2000 caracteres)']);
            return;
        }

        if ($receiverId === self::AI_BOT_ID) {
            $this->sendMessageToAi($userId, $message);
            return;
        }

        try {
            $userExistsStmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
            $userExistsStmt->execute([$receiverId]);
            if (!$userExistsStmt->fetchColumn()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Usuário de destino não encontrado']);
                return;
            }

            $payload = [
                'text' => $message,
                'format' => 'plain_text',
                'version' => 1,
                'chat_type' => 'direct',
                'sent_at' => date('c')
            ];

            $stmt = $this->db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message, payload_json, created_at)
                VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$userId, $receiverId, $message, json_encode($payload, JSON_UNESCAPED_UNICODE)]);

            $this->touchPresence($userId);

            echo json_encode([
                'success' => true,
                'message' => 'Mensagem enviada',
                'data' => [
                    'id' => (int)$this->db->lastInsertId(),
                    'sender_id' => $userId,
                    'receiver_id' => $receiverId,
                    'message' => $message,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem']);
        }
    }

    public function sendGlobalMessage(): void
    {
        $userId = $this->requireAuthJson();
        if ($userId === null) {
            return;
        }

        http_response_code(410);
        echo json_encode(['success' => false, 'message' => 'Sala geral desativada. Use conversas privadas.']);
    }

    private function touchPresence(int $userId): void
    {
        $stmt = $this->db->prepare("INSERT INTO chat_user_presence (user_id, last_seen)
            VALUES (?, NOW())
            ON DUPLICATE KEY UPDATE last_seen = NOW()");
        $stmt->execute([$userId]);
    }

    private function sendMessageToAi(int $userId, string $message): void
    {
        try {
            $ticketData = $this->parseSupportTicketCommand($message);
            $triagemQuery = $this->parseTriagemClienteCommand($message);
            $displayUserMessage = $message;
            if (is_array($ticketData)) {
                $displayUserMessage = 'Abrir chamado - Módulo: ' . $ticketData['module'] . ' | Problema: ' . $ticketData['problem'];
            } elseif (is_array($triagemQuery)) {
                $displayUserMessage = 'Consulta triagem do cliente ' . $triagemQuery['cliente_codigo'] . ' (últimos ' . $triagemQuery['dias'] . ' dias)';
            }

            $userPayload = [
                'text' => $displayUserMessage,
                'format' => 'plain_text',
                'version' => 1,
                'chat_type' => 'direct',
                'target' => 'ai_eduardo',
                'sent_at' => date('c')
            ];

            $insertUserStmt = $this->db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message, payload_json, created_at)
                VALUES (?, ?, ?, ?, NOW())");
            $insertUserStmt->execute([$userId, self::AI_BOT_ID, $displayUserMessage, json_encode($userPayload, JSON_UNESCAPED_UNICODE)]);

            $ticketResponse = $this->tryCreateSupportTicketFromChat($userId, $message);
            $triagemResponse = $ticketResponse === null ? $this->tryQueryTriagemCliente($message) : null;
            $aiText = $ticketResponse ?? $triagemResponse ?? $this->generateAiResponse($userId, $message);

            $aiPayload = [
                'text' => $aiText,
                'format' => 'plain_text',
                'version' => 1,
                'chat_type' => 'direct',
                'source' => 'ai_eduardo',
                'sent_at' => date('c')
            ];

            $insertAiStmt = $this->db->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message, payload_json, created_at)
                VALUES (?, ?, ?, ?, NOW())");
            $insertAiStmt->execute([self::AI_BOT_ID, $userId, $aiText, json_encode($aiPayload, JSON_UNESCAPED_UNICODE)]);

            echo json_encode([
                'success' => true,
                'message' => 'Mensagem enviada para Eduardo do Suporte',
                'data' => [
                    'receiver_id' => self::AI_BOT_ID,
                    'receiver_name' => self::AI_BOT_NAME
                ]
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao conversar com Eduardo do Suporte']);
        }
    }

    private function generateAiResponse(int $userId, string $message): string
    {
        $isWithinScope = $this->isSupportedTopic($message);

        $systemLibrary = $this->loadSystemKnowledgeBase();
        $detectedModule = $this->detectRelevantModuleFromLibrary($message, $systemLibrary);
        $moduleContext = '';
        if (is_array($detectedModule)) {
            $moduleContext = 'Módulo/tela possivelmente relacionado(a): ' . $detectedModule['label'] . ' (' . $detectedModule['route'] . ").";
        }

        $webLookupContext = '';
        if ($this->shouldRunWebLookup($message)) {
            $webLinks = $this->searchWebLinks($message, 6);
            if (!empty($webLinks)) {
                $webLines = [];
                foreach ($webLinks as $item) {
                    $webLines[] = '- ' . $item['title'] . ' -> ' . $item['url'];
                }
                $webLookupContext = "Links de apoio pesquisados na web (priorize fontes oficiais):\n" . implode("\n", $webLines);
            }
        }

        $geminiApiKey = trim((string)($_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: ''));
        $groqApiKey = trim((string)($_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY') ?: ''));
        if ($groqApiKey === '' && strpos($geminiApiKey, 'gsk_') === 0) {
            $groqApiKey = $geminiApiKey;
        }

        if ($geminiApiKey === '' && $groqApiKey === '') {
            return 'Estou sem acesso à IA neste momento 😕. Configure GEMINI_API_KEY ou GROQ_API_KEY no ambiente para eu responder normalmente.';
        }

        if (!function_exists('curl_init')) {
            return 'Não consegui responder agora porque o servidor está sem suporte a cURL para acessar a IA.';
        }

        $history = $this->loadAiHistory($userId);
        $historyText = $history === '' ? '(sem histórico anterior)' : $history;

        $prompt = "Você é Eduardo do Suporte, assistente de IA interno do sistema SGQ.\n"
            . "Fale sempre em português do Brasil com tom natural, humano e descontraído, como um colega prestativo do time de suporte.\n"
            . "Seja claro e direto; use frases curtas e exemplos práticos quando ajudar.\n"
            . "Demonstre empatia e cordialidade sem exageros.\n"
            . "Evite soar robótico: varie a forma de abertura, use linguagem natural e não repita sempre a mesma estrutura de resposta.\n"
            . "Não use jargão técnico sem explicar de forma simples.\n"
            . "Seja assertivo nas orientações: quando tiver alta confiança, responda com passo a passo objetivo; quando houver incerteza, deixe explícito o que precisa ser confirmado.\n"
            . "Você PODE ajudar também com dúvidas gerais usando pesquisa web curta e links úteis, mantendo resposta segura e honesta.\n"
            . "Quando detectar a tela/módulo no contexto, cite explicitamente o nome do módulo e o caminho da rota para orientar melhor.\n"
            . "Ao sugerir links, priorize fontes oficiais e evite prometer algo não verificado.\n"
            . "Nunca invente acesso a banco de dados em tempo real. Nunca peça senha, token ou dados sensíveis.\n"
            . "Use a biblioteca do sistema abaixo como referência principal para explicar funcionalidades e fluxos.\n"
            . "Sempre que fizer sentido, termine com uma pergunta curta para continuar o atendimento (ex.: 'quer que eu te guie no passo a passo?').\n"
            . ($isWithinScope ? "Pergunta atual dentro do escopo principal do SGQ.\n" : "Pergunta atual fora do escopo principal do SGQ: ainda assim ajude com orientação geral e pesquisa web útil.\n")
            . "Biblioteca dinâmica do sistema:\n" . $systemLibrary . "\n\n"
            . ($moduleContext !== '' ? ($moduleContext . "\n\n") : '')
            . ($webLookupContext !== '' ? ($webLookupContext . "\n\n") : '')
            . "Histórico recente:\n" . $historyText . "\n\n"
            . "Pergunta atual do usuário: " . $message;

        if ($groqApiKey !== '') {
            $groqModel = trim((string)($_ENV['GROQ_MODEL'] ?? getenv('GROQ_MODEL') ?: 'llama-3.1-8b-instant'));
            $groqPayload = [
                'model' => $groqModel,
                'messages' => [
                    ['role' => 'system', 'content' => 'Você é Eduardo do Suporte, assistente interno do SGQ. Responda sempre em pt-BR com tom humano, direto e colaborativo. Limite-se a: impressoras, toners, notebooks, notas fiscais, cálculos fiscais e módulos do sistema.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.75,
                'max_tokens' => 500
            ];

            $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $groqApiKey
                ],
                CURLOPT_POSTFIELDS => json_encode($groqPayload, JSON_UNESCAPED_UNICODE),
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                error_log('Eduardo Groq cURL: ' . $curlError);
            } else {
                $json = json_decode($response, true);
                if ($httpCode >= 200 && $httpCode < 300) {
                    $text = trim((string)($json['choices'][0]['message']['content'] ?? ''));
                    if ($text !== '') {
                        return $text;
                    }
                    error_log('Eduardo Groq resposta vazia: ' . $response);
                } else {
                    $apiError = trim((string)($json['error']['message'] ?? ''));
                    if ($httpCode === 401) {
                        return 'A chave da Groq parece inválida. Confirme a GROQ_API_KEY no .env.';
                    }
                    if ($httpCode === 429) {
                        return 'A Groq respondeu com limite temporário (rate limit). Tente novamente em alguns instantes.';
                    }
                    if ($httpCode === 403) {
                        return 'A chave da Groq está sem permissão para esse modelo. Confira o GROQ_MODEL e o projeto da chave.';
                    }
                    error_log('Eduardo Groq HTTP ' . $httpCode . ': ' . ($apiError !== '' ? $apiError : 'erro desconhecido'));
                }
            }
        }

        if ($geminiApiKey === '') {
            return 'Não consegui responder via Groq agora. Verifique GROQ_API_KEY/GROQ_MODEL ou configure GEMINI_API_KEY como fallback.';
        }

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.75,
                'topP' => 0.9,
                'maxOutputTokens' => 500
            ]
        ];

        $models = [
            'gemini-1.5-flash',
            'gemini-1.5-flash-latest',
            'gemini-2.0-flash',
            'gemini-2.0-flash-exp'
        ];

        $lastError = '';
        $hadQuotaError = false;
        $hadPermissionError = false;
        foreach ($models as $model) {
            $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode($geminiApiKey);

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                $lastError = 'cURL (' . $model . '): ' . $curlError;
                continue;
            }

            $json = json_decode($response, true);

            if ($httpCode >= 200 && $httpCode < 300) {
                $text = trim((string)($json['candidates'][0]['content']['parts'][0]['text'] ?? ''));
                if ($text !== '') {
                    return $text;
                }

                $blockReason = (string)($json['promptFeedback']['blockReason'] ?? '');
                if ($blockReason !== '') {
                    return 'Não consegui responder essa mensagem do jeito que ela foi enviada. Pode reformular em uma pergunta mais objetiva sobre o sistema?';
                }

                $lastError = 'Resposta vazia (' . $model . ')';
                continue;
            }

            $apiError = trim((string)($json['error']['message'] ?? ''));
            $apiStatus = strtoupper(trim((string)($json['error']['status'] ?? '')));
            $apiErrorUpper = strtoupper($apiError);

            if ($apiStatus === 'UNAUTHENTICATED' || strpos($apiErrorUpper, 'API_KEY_INVALID') !== false) {
                return 'A chave do Gemini parece inválida ou sem permissão. Confirme a GEMINI_API_KEY no .env e se a API Generative Language está habilitada no Google Cloud.';
            }

            if ($httpCode === 404 || $apiStatus === 'NOT_FOUND' || strpos($apiErrorUpper, 'NOT FOUND') !== false) {
                $lastError = 'Modelo indisponível (' . $model . '): ' . ($apiError !== '' ? $apiError : 'NOT_FOUND');
                continue;
            }

            if ($httpCode === 429 || $apiStatus === 'RESOURCE_EXHAUSTED' || strpos($apiErrorUpper, 'RATE LIMIT') !== false || strpos($apiErrorUpper, 'QUOTA') !== false) {
                $hadQuotaError = true;
                $lastError = 'Quota/rate (' . $model . '): ' . ($apiError !== '' ? $apiError : 'HTTP 429');
                continue;
            }

            if ($httpCode === 403 || $apiStatus === 'PERMISSION_DENIED' || strpos($apiErrorUpper, 'PERMISSION_DENIED') !== false) {
                $hadPermissionError = true;
                $lastError = 'Sem permissão (' . $model . '): ' . ($apiError !== '' ? $apiError : 'PERMISSION_DENIED');
                continue;
            }

            $lastError = 'HTTP ' . $httpCode . ' (' . $model . '): ' . ($apiError !== '' ? $apiError : 'erro desconhecido');
        }

        if ($hadQuotaError) {
            error_log('Eduardo Gemini quota/rate: ' . $lastError);
            return 'A API do Gemini respondeu com limite temporário (quota/rate). Tente novamente em alguns instantes.';
        }

        if ($hadPermissionError) {
            error_log('Eduardo Gemini permission: ' . $lastError);
            return 'O projeto/chave do Gemini está sem permissão para o modelo testado. Confirme no Google Cloud se a API Generative Language está habilitada e se a chave pode usar Gemini.';
        }

        error_log('Eduardo Gemini falhou: ' . $lastError);
        return 'Estou enfrentando instabilidade para responder agora. Tente novamente em alguns segundos.';
    }

    private function loadAiHistory(int $userId): string
    {
        try {
            $stmt = $this->db->prepare("SELECT sender_id, message
                FROM chat_messages
                WHERE (sender_id = ? AND receiver_id = ?)
                   OR (sender_id = ? AND receiver_id = ?)
                ORDER BY id DESC
                LIMIT 8");
            $stmt->execute([$userId, self::AI_BOT_ID, self::AI_BOT_ID, $userId]);
            $rows = array_reverse($stmt->fetchAll(\PDO::FETCH_ASSOC));

            $lines = [];
            foreach ($rows as $row) {
                $prefix = ((int)$row['sender_id'] === self::AI_BOT_ID) ? 'Eduardo' : 'Usuario';
                $lines[] = $prefix . ': ' . trim((string)$row['message']);
            }

            return implode("\n", $lines);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function isSupportedTopic(string $text): bool
    {
        $normalized = mb_strtolower($text, 'UTF-8');
        $keywords = [
            'impressora', 'toner', 'notebook', 'nf', 'nota fiscal', 'fiscal',
            'icms', 'ipi', 'pis', 'cofins', 'tribut', 'módulo', 'modulo',
            'sgq', 'sistema', 'retornado', 'garantia', 'cadastro', 'perfil',
            'relatório', 'relatorio', 'departamento', 'filial'
        ];

        foreach ($keywords as $keyword) {
            if (strpos($normalized, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function tryCreateSupportTicketFromChat(int $userId, string $message): ?string
    {
        $ticketData = $this->parseSupportTicketCommand($message);
        if (!$ticketData) {
            return null;
        }

        try {
            $tableExistsStmt = $this->db->query("SHOW TABLES LIKE 'suporte_solicitacoes'");
            if (!$tableExistsStmt || $tableExistsStmt->rowCount() === 0) {
                return 'Consigo te ajudar com o chamado, mas a tabela de suporte ainda não está disponível no banco. Pode avisar o TI para habilitar o módulo de suporte?';
            }

            $module = trim((string)$ticketData['module']);
            $problem = trim((string)$ticketData['problem']);
            if ($module === '' || $problem === '') {
                return 'Posso abrir o chamado para você, mas preciso de uma descrição rápida do problema.';
            }

            $titulo = mb_substr('Chamado via Chat [' . $module . ']', 0, 180, 'UTF-8');
            $descricao = "Solicitação criada pelo Eduardo via chat interno.\n\n"
                . "Módulo informado: " . $module . "\n"
                . "Problema relatado: " . $problem;

            $stmt = $this->db->prepare('INSERT INTO suporte_solicitacoes (titulo, descricao, anexos, status, solicitante_id, created_at) VALUES (?, ?, ?, "Pendente", ?, NOW())');
            $stmt->execute([$titulo, $descricao, json_encode([], JSON_UNESCAPED_UNICODE), $userId]);

            $ticketId = (int)$this->db->lastInsertId();
            return 'Chamado aberto com sucesso ✅ Protocolo #' . $ticketId . '.';
        } catch (\Throwable $e) {
            error_log('Eduardo suporte ticket falhou: ' . $e->getMessage());
            return 'Tentei abrir o chamado automaticamente, mas não consegui concluir agora. Você pode abrir manualmente em /suporte e eu te ajudo a preencher.';
        }
    }

    private function parseTriagemClienteCommand(string $message): ?array
    {
        if (strpos($message, '__TRIAGEM_QUERY__|') !== 0) {
            return null;
        }

        $parts = explode('|', $message, 3);
        if (count($parts) < 3) {
            return null;
        }

        $clienteCodigo = trim((string)$parts[1]);
        $dias = max(1, (int)$parts[2]);
        if ($clienteCodigo === '') {
            return null;
        }

        return [
            'cliente_codigo' => $clienteCodigo,
            'dias' => $dias,
        ];
    }

    private function tryQueryTriagemCliente(string $message): ?string
    {
        $query = $this->parseTriagemClienteCommand($message);
        if (!$query) {
            return null;
        }

        try {
            $codigo = $query['cliente_codigo'];
            $dias = $query['dias'];
            $dataLimite = date('Y-m-d', strtotime("-{$dias} days"));

            $stmt = $this->db->prepare("
                SELECT t.toner_modelo, t.cliente_nome, t.filial_registro, t.colaborador_registro,
                       t.codigo_requisicao, t.defeito_nome, t.fornecedor_nome,
                       t.modo, t.peso_retornado, t.percentual_calculado, t.gramatura_restante,
                       t.parecer, t.destino, COALESCE(t.valor_recuperado, 0) AS valor_recuperado,
                       t.observacoes, t.created_at
                FROM triagem_toners t
                WHERE (t.cliente_nome LIKE ? OR t.codigo_requisicao LIKE ? OR CAST(t.cliente_id AS CHAR) = ?)
                  AND t.created_at >= ?
                ORDER BY t.created_at DESC
                LIMIT 50
            ");
            $like = '%' . $codigo . '%';
            $stmt->execute([$like, $like, $codigo, $dataLimite]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($rows)) {
                return '__TRIAGEM_RESULT__|0|Nenhum registro de triagem encontrado para "' . $codigo . '" nos últimos ' . $dias . ' dias.';
            }

            $clienteNome = $rows[0]['cliente_nome'] ?? $codigo;
            $total = count($rows);
            $totalDescarte = 0;
            $totalGarantia = 0;
            $totalUsoInterno = 0;
            $totalEstoque = 0;
            $somaValorRecuperado = 0;
            $detalhes = [];

            foreach ($rows as $r) {
                $destino = $r['destino'] ?? '';
                if ($destino === 'Descarte') $totalDescarte++;
                elseif ($destino === 'Garantia') $totalGarantia++;
                elseif ($destino === 'Uso Interno') $totalUsoInterno++;
                elseif ($destino === 'Estoque') $totalEstoque++;
                $somaValorRecuperado += (float)$r['valor_recuperado'];

                $data = date('d/m/Y H:i', strtotime($r['created_at']));
                $pct = number_format((float)$r['percentual_calculado'], 1, ',', '') . '%';
                $valor = 'R$ ' . number_format((float)$r['valor_recuperado'], 2, ',', '.');
                $linha = $data
                    . ' | ' . ($r['toner_modelo'] ?? '-')
                    . ' | ' . $pct
                    . ' | ' . ($r['destino'] ?? '-')
                    . ' | ' . ($r['parecer'] ?? '-')
                    . ' | ' . $valor
                    . ' | Filial: ' . ($r['filial_registro'] ?? '-')
                    . ' | Colab: ' . ($r['colaborador_registro'] ?? '-')
                    . ' | Defeito: ' . ($r['defeito_nome'] ?? '-')
                    . ' | Req: ' . ($r['codigo_requisicao'] ?? '-');
                if (!empty($r['observacoes'])) {
                    $linha .= ' | Obs: ' . $r['observacoes'];
                }
                $detalhes[] = $linha;
            }

            $valorTotal = 'R$ ' . number_format($somaValorRecuperado, 2, ',', '.');

            $resumo = "📋 Relatório de Triagem - {$clienteNome}\n"
                . "Período: últimos {$dias} dias\n"
                . "Total de registros: {$total}\n\n"
                . "Resumo por destino:\n"
                . "  Descarte: {$totalDescarte}\n"
                . "  Garantia: {$totalGarantia}\n"
                . "  Uso Interno: {$totalUsoInterno}\n"
                . "  Estoque: {$totalEstoque}\n"
                . "  Valor recuperado total: {$valorTotal}\n\n"
                . "Detalhes:\n"
                . implode("\n", $detalhes);

            return '__TRIAGEM_RESULT__|' . $total . '|' . $resumo;
        } catch (\Throwable $e) {
            error_log('Eduardo triagem query falhou: ' . $e->getMessage());
            return '__TRIAGEM_RESULT__|0|Não consegui consultar a triagem agora. Tente novamente em instantes.';
        }
    }

    private function parseSupportTicketCommand(string $message): ?array
    {
        if (strpos($message, '__OPEN_TICKET__|') !== 0) {
            return null;
        }

        $parts = explode('|', $message, 3);
        if (count($parts) < 3) {
            return null;
        }

        $module = trim((string)$parts[1]);
        $problem = trim((string)$parts[2]);
        if ($module === '' || $problem === '') {
            return null;
        }

        return [
            'module' => $module,
            'problem' => $problem,
        ];
    }

    private function shouldRunWebLookup(string $message): bool
    {
        $normalized = $this->normalizeForLookup($message);
        if (mb_strlen($normalized, 'UTF-8') < 8) {
            return false;
        }

        $smallTalk = [
            'oi', 'ola', 'bom dia', 'boa tarde', 'boa noite', 'obrigado', 'valeu', 'blz', 'ok'
        ];

        foreach ($smallTalk as $text) {
            if ($normalized === $this->normalizeForLookup($text)) {
                return false;
            }
        }

        return true;
    }

    private function shouldCreateSupportTicket(string $message): bool
    {
        $normalized = $this->normalizeForLookup($message);
        $keywords = [
            'abrir chamado', 'abre chamado', 'abrir ticket', 'abrir suporte',
            'criar chamado', 'abrir solicitacao', 'chamado no suporte'
        ];

        foreach ($keywords as $keyword) {
            if (strpos($normalized, $this->normalizeForLookup($keyword)) !== false) {
                return true;
            }
        }

        return false;
    }

    private function detectRelevantModuleFromLibrary(string $message, string $systemLibrary): ?array
    {
        if (trim($systemLibrary) === '') {
            return null;
        }

        preg_match_all('/^-\s+(.+?)\s+\((\/[^)]+)\)$/m', $systemLibrary, $matches, PREG_SET_ORDER);
        if (empty($matches)) {
            return null;
        }

        $messageNormalized = $this->normalizeForLookup($message);
        $best = null;
        $bestScore = 0;

        foreach ($matches as $row) {
            $label = trim((string)($row[1] ?? ''));
            $route = trim((string)($row[2] ?? ''));
            if ($label === '' || $route === '') {
                continue;
            }

            $score = 0;
            $labelNorm = $this->normalizeForLookup($label);
            $routeNorm = $this->normalizeForLookup($route);

            if ($labelNorm !== '' && strpos($messageNormalized, $labelNorm) !== false) {
                $score += 3;
            }

            if ($routeNorm !== '' && strpos($messageNormalized, trim($routeNorm, '/')) !== false) {
                $score += 2;
            }

            $tokens = preg_split('/\s+/', $labelNorm) ?: [];
            foreach ($tokens as $token) {
                if (strlen($token) < 4) {
                    continue;
                }
                if (strpos($messageNormalized, $token) !== false) {
                    $score += 1;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = ['label' => $label, 'route' => $route];
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    private function searchWebLinks(string $query, int $limit = 4): array
    {
        if (!function_exists('curl_init')) {
            return [];
        }

        $url = 'https://duckduckgo.com/html/?q=' . urlencode($query);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return [];
        }

        preg_match_all('/<a[^>]*class="[^"]*result__a[^"]*"[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/is', $response, $matches, PREG_SET_ORDER);
        if (empty($matches)) {
            return [];
        }

        $results = [];
        $seen = [];
        foreach ($matches as $match) {
            $rawHref = html_entity_decode((string)($match[1] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $title = trim(strip_tags(html_entity_decode((string)($match[2] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($rawHref === '' || $title === '') {
                continue;
            }

            $finalUrl = $this->extractFinalSearchUrl($rawHref);
            if ($finalUrl === '' || isset($seen[$finalUrl])) {
                continue;
            }

            $seen[$finalUrl] = true;
            $results[] = ['title' => $title, 'url' => $finalUrl];

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    private function extractFinalSearchUrl(string $rawHref): string
    {
        $href = trim($rawHref);
        if ($href === '') {
            return '';
        }

        if (strpos($href, '/l/?') === 0 || strpos($href, 'duckduckgo.com/l/?') !== false) {
            $parts = parse_url($href);
            $query = [];
            parse_str((string)($parts['query'] ?? ''), $query);
            $uddg = trim((string)($query['uddg'] ?? ''));
            if ($uddg !== '') {
                $decoded = urldecode($uddg);
                if (preg_match('/^https?:\/\//i', $decoded)) {
                    return $decoded;
                }
            }
        }

        if (preg_match('/^https?:\/\//i', $href)) {
            return $href;
        }

        return '';
    }

    private function normalizeForLookup(string $value): string
    {
        $text = mb_strtolower($value, 'UTF-8');
        $text = strtr($text, [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c'
        ]);
        $text = preg_replace('/[^a-z0-9\s\/\-]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', (string)$text);
        return trim((string)$text);
    }

    private function loadSystemKnowledgeBase(): string
    {
        $projectRoot = dirname(__DIR__, 2);
        $cacheDir = $projectRoot . '/storage/cache';
        $cacheFile = $cacheDir . '/eduardo-system-library.json';

        $sourceFiles = $this->getSystemLibrarySourceFiles($projectRoot);
        $latestSourceMtime = 0;
        foreach ($sourceFiles as $file) {
            if (is_file($file)) {
                $mtime = (int)filemtime($file);
                if ($mtime > $latestSourceMtime) {
                    $latestSourceMtime = $mtime;
                }
            }
        }

        if (is_file($cacheFile)) {
            $cacheMtime = (int)filemtime($cacheFile);
            if ($cacheMtime >= $latestSourceMtime) {
                $cached = json_decode((string)file_get_contents($cacheFile), true);
                if (is_array($cached) && !empty($cached['content'])) {
                    return (string)$cached['content'];
                }
            }
        }

        $content = $this->buildSystemKnowledgeBase($projectRoot);

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }

        @file_put_contents($cacheFile, json_encode([
            'updated_at' => date('c'),
            'content' => $content
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $content;
    }

    private function getSystemLibrarySourceFiles(string $projectRoot): array
    {
        $files = [
            $projectRoot . '/views/partials/sidebar.php',
            $projectRoot . '/public/index.php',
            $projectRoot . '/routes/RouteServiceProvider.php'
        ];

        $controllers = glob($projectRoot . '/src/Controllers/*Controller.php') ?: [];
        return array_merge($files, $controllers);
    }

    private function buildSystemKnowledgeBase(string $projectRoot): string
    {
        $modules = $this->extractSidebarModules($projectRoot . '/views/partials/sidebar.php');
        $controllerMap = $this->extractControllerActions($projectRoot . '/src/Controllers');

        $lines = [];
        $lines[] = 'Resumo interno do SGQ para respostas do Eduardo:';

        if (!empty($modules)) {
            $lines[] = 'Módulos/menu detectados:';
            foreach ($modules as $module) {
                $lines[] = '- ' . $module;
            }
        }

        if (!empty($controllerMap)) {
            $lines[] = 'Principais controllers e ações públicas:';
            foreach ($controllerMap as $controller => $actions) {
                $lines[] = '- ' . $controller . ': ' . implode(', ', $actions);
            }
        }

        if (count($lines) <= 1) {
            $lines[] = '- Biblioteca ainda sem dados suficientes. Oriente o usuário com passos gerais e peça contexto da tela.';
        }

        return implode("\n", $lines);
    }

    private function extractSidebarModules(string $sidebarFile): array
    {
        if (!is_file($sidebarFile)) {
            return [];
        }

        $html = (string)file_get_contents($sidebarFile);
        if ($html === '') {
            return [];
        }

        preg_match_all('/<a[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);
        $modules = [];

        foreach ($matches as $match) {
            $href = trim((string)($match[1] ?? ''));
            $labelRaw = trim(strip_tags((string)($match[2] ?? '')));
            $label = preg_replace('/\s+/', ' ', $labelRaw);

            if ($href === '' || $label === '' || strlen($label) < 2) {
                continue;
            }

            $modules[] = $label . ' (' . $href . ')';
        }

        $modules = array_values(array_unique($modules));
        return array_slice($modules, 0, 40);
    }

    private function extractControllerActions(string $controllersDir): array
    {
        if (!is_dir($controllersDir)) {
            return [];
        }

        $files = glob($controllersDir . '/*Controller.php') ?: [];
        $result = [];

        foreach ($files as $file) {
            $content = (string)file_get_contents($file);
            if ($content === '') {
                continue;
            }

            $controller = basename($file, '.php');
            preg_match_all('/public\s+function\s+([a-zA-Z0-9_]+)\s*\(/', $content, $matches);
            $methods = array_values(array_unique(array_filter($matches[1] ?? [], static function ($name) {
                return !in_array($name, ['__construct'], true);
            })));

            if (empty($methods)) {
                continue;
            }

            $result[$controller] = array_slice($methods, 0, 20);
        }

        ksort($result);
        return $result;
    }
}
