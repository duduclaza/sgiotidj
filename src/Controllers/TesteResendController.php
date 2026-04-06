<?php

namespace App\Controllers;

use App\Services\ResendService;

class TesteResendController
{
    public function index()
    {
        echo '<!DOCTYPE html><html><head><title>Teste Resend API</title><style>body{font-family:sans-serif;padding:20px;background:#f0f0f0}.container{background:white;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);max-width:800px;margin:0 auto}h1{margin-top:0}pre{background:#1e1e1e;color:#0f0;padding:15px;overflow:auto;border-radius:5px;max-height:500px;font-size:12px}.input-group{margin-bottom:15px}input{padding:8px;border:1px solid #ddd;border-radius:4px;width:300px}button{padding:10px 20px;background:#28a745;color:white;border:none;border-radius:4px;cursor:pointer}button:hover{background:#218838}</style></head><body>';
        
        echo '<div class="container">';
        echo '<h1>📧 Diagnóstico Resend API</h1>';
        
        $resend = new ResendService();
        $testConn = $resend->testConnection();
        
        echo '<div style="padding:10px; margin-bottom:20px; border-radius:5px; ' . ($testConn['success'] ? 'background:#d4edda;color:#155724;' : 'background:#f8d7da;color:#721c24;') . '">';
        echo '<strong>Status da Conexão:</strong> ' . $testConn['message'];
        echo '</div>';

        $destinatario = $_POST['email'] ?? '';

        echo '<form method="POST">';
        echo '<div class="input-group">';
        echo '<label>Enviar email de teste para: </label>';
        echo '<input type="email" name="email" value="' . htmlspecialchars($destinatario) . '" placeholder="seu-email@exemplo.com" required>';
        echo ' <button type="submit">🚀 Testar Resend</button>';
        echo '</div>';
        echo '</form>';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($destinatario)) {
            echo '<hr><h3>📜 Log de Execução:</h3>';
            echo '<pre>';
            
            $subject = "Teste Diagnóstico Resend - " . date('H:i:s');
            $body = "<h1>Teste Resend OK! ✅</h1><p>Se você recebeu este email, a API do Resend está configurada corretamente.</p><p>Horário: " . date('d/m/Y H:i:s') . "</p>";
            
            $result = $resend->send($destinatario, $subject, $body);
            
            echo '</pre>';

            if ($result) {
                echo '<div style="margin-top:15px;padding:15px;background:#d4edda;color:#155724;border-radius:4px;border:1px solid #c3e6cb"><strong>✅ SUCESSO!</strong> Email enviado via Resend.</div>';
            } else {
                echo '<div style="margin-top:15px;padding:15px;background:#f8d7da;color:#721c24;border-radius:4px;border:1px solid #f5c6cb"><strong>❌ ERRO:</strong> ' . ($resend->getLastError() ?: 'Falha desconhecida') . '</div>';
            }
        }
        
        echo '</div></body></html>';
    }
}
