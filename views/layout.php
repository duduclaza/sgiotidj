<?php
// Determine the view to load based on the current request
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Map routes to view files
$viewMap = [
    '/melhoria-continua/solicitacoes' => 'melhoria-continua/solicitacoes.php',
    // Add other routes as needed
];

// Find the correct view file
$viewFile = null;
foreach ($viewMap as $route => $view) {
    if ($path === $route) {
        $viewFile = $view;
        break;
    }
}

// If no specific view found, try to determine from path
if (!$viewFile) {
    // Default fallback
    $viewFile = 'dashboard.php';
}

// Function to safely escape output
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGQ OTI DJ - Sistema de Gestão da Qualidade</title>
    <script>if(window.console){const o=console.warn;console.warn=(...a)=>{if(a[0]&&String(a[0]).includes('cdn.tail'))return;o.apply(console,a)}}</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/modal-styles.css?v=<?= file_exists(__DIR__ . '/../public/assets/modal-styles.css') ? filemtime(__DIR__ . '/../public/assets/modal-styles.css') : time() ?>">
    <style>
        .submenu { transition: all 0.3s ease; }
        .submenu.hidden { max-height: 0; opacity: 0; }
        .submenu:not(.hidden) { max-height: 500px; opacity: 1; }
        
        /* Estilos globais para alertas JavaScript */
        .swal2-popup {
            border-radius: 1.5rem !important;
            box-shadow: 0 32px 64px -12px rgba(0, 0, 0, 0.25) !important;
        }
        
        /* Melhorar aparência dos alerts nativos */
        .alert-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(30, 41, 59, 0.9));
            backdrop-filter: blur(8px);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .alert-box {
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            border-radius: 1.5rem;
            box-shadow: 0 32px 64px -12px rgba(0, 0, 0, 0.25);
            padding: 2rem;
            max-width: 28rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .alert-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .alert-message {
            color: #4b5563;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .alert-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }
        
        .alert-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .alert-btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
        }
        
        .alert-btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5);
        }
        
        .alert-btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: #64748b;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .alert-btn-secondary:hover {
            background: rgba(248, 250, 252, 0.95);
            color: #475569;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen w-full overflow-hidden">
        <!-- Sidebar -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 min-w-0 overflow-y-auto pt-14 lg:pt-0">
            <!-- Aviso de migração de email removido - Resend API ativo -->
            
            <div class="p-4 sm:p-6">
                <?php
                $fullPath = __DIR__ . '/' . $viewFile;
                if (file_exists($fullPath)) {
                    include $fullPath;
                } else {
                    echo '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">';
                    echo 'Erro: View não encontrada - ' . e($viewFile);
                    echo '</div>';
                }
                ?>
            </div>
        </main>
    </div>
    
    <!-- Script para modais personalizados -->
    <script>
        // Função para criar alertas personalizados mais bonitos
        window.showAlert = function(message, type = 'info', title = '') {
            return new Promise((resolve) => {
                // Remove alertas existentes
                const existingAlert = document.querySelector('.alert-overlay');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                // Define ícones e títulos baseados no tipo
                const config = {
                    success: { icon: '✅', title: title || 'Sucesso!', class: 'success-modal' },
                    error: { icon: '❌', title: title || 'Erro!', class: 'error-modal' },
                    warning: { icon: '⚠️', title: title || 'Atenção!', class: 'alert-modal' },
                    info: { icon: 'ℹ️', title: title || 'Informação', class: 'alert-modal' }
                };
                
                const alertConfig = config[type] || config.info;
                
                // Cria o overlay
                const overlay = document.createElement('div');
                overlay.className = 'alert-overlay';
                overlay.style.opacity = '0';
                overlay.style.transition = 'opacity 0.3s ease';
                
                // Cria o modal
                overlay.innerHTML = `
                    <div class="alert-box ${alertConfig.class}" style="transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
                        <div class="alert-title">
                            <span style="font-size: 1.5rem;">${alertConfig.icon}</span>
                            ${alertConfig.title}
                        </div>
                        <div class="alert-message">${message}</div>
                        <div class="alert-buttons">
                            <button class="alert-btn alert-btn-primary" onclick="this.closest('.alert-overlay').remove(); resolve(true);">
                                OK
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(overlay);
                
                // Animação de entrada
                requestAnimationFrame(() => {
                    overlay.style.opacity = '1';
                    const box = overlay.querySelector('.alert-box');
                    box.style.transform = 'scale(1)';
                });
                
                // Fechar com ESC
                const handleEsc = (e) => {
                    if (e.key === 'Escape') {
                        overlay.remove();
                        document.removeEventListener('keydown', handleEsc);
                        resolve(true);
                    }
                };
                document.addEventListener('keydown', handleEsc);
                
                // Fechar clicando fora
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) {
                        overlay.remove();
                        resolve(true);
                    }
                });
            });
        };
        
        // Função para confirmações personalizadas
        window.showConfirm = function(message, title = 'Confirmação') {
            return new Promise((resolve) => {
                // Remove alertas existentes
                const existingAlert = document.querySelector('.alert-overlay');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                // Cria o overlay
                const overlay = document.createElement('div');
                overlay.className = 'alert-overlay';
                overlay.style.opacity = '0';
                overlay.style.transition = 'opacity 0.3s ease';
                
                // Cria o modal
                overlay.innerHTML = `
                    <div class="alert-box confirm-modal" style="transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
                        <div class="alert-title">
                            <span style="font-size: 1.5rem;">❓</span>
                            ${title}
                        </div>
                        <div class="alert-message">${message}</div>
                        <div class="alert-buttons">
                            <button class="alert-btn alert-btn-secondary" onclick="this.closest('.alert-overlay').remove(); resolve(false);">
                                Cancelar
                            </button>
                            <button class="alert-btn alert-btn-primary" onclick="this.closest('.alert-overlay').remove(); resolve(true);">
                                OK
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(overlay);
                
                // Animação de entrada
                requestAnimationFrame(() => {
                    overlay.style.opacity = '1';
                    const box = overlay.querySelector('.alert-box');
                    box.style.transform = 'scale(1)';
                });
                
                // Fechar com ESC (cancelar)
                const handleEsc = (e) => {
                    if (e.key === 'Escape') {
                        overlay.remove();
                        document.removeEventListener('keydown', handleEsc);
                        resolve(false);
                    }
                };
                document.addEventListener('keydown', handleEsc);
                
                // Fechar clicando fora (cancelar)
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) {
                        overlay.remove();
                        resolve(false);
                    }
                });
            });
        };
        
        // Substituir alert nativo
        const originalAlert = window.alert;
        window.alert = function(message) {
            return showAlert(message, 'info');
        };
        
        // Substituir confirm nativo
        const originalConfirm = window.confirm;
        window.confirm = function(message) {
            return showConfirm(message);
        };
        
        // Função para mostrar notificações de toast
        window.showToast = function(message, type = 'success', duration = 3000) {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300`;
            
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-black',
                info: 'bg-blue-500 text-white'
            };
            
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            
            toast.className += ` ${colors[type] || colors.info}`;
            toast.innerHTML = `
                <div class="flex items-center gap-3">
                    <span style="font-size: 1.2rem;">${icons[type] || icons.info}</span>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Animação de entrada
            requestAnimationFrame(() => {
                toast.style.transform = 'translateX(0)';
            });
            
            // Remover após duração especificada
            setTimeout(() => {
                toast.style.transform = 'translateX(full)';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        };
    </script>
</body>
</html>
