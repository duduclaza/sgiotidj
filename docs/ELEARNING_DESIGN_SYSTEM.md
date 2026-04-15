# 🎨 E-Learning Module - Modern Design System Guide

## Overview
Este é o novo sistema de design moderno para o módulo E-Learning, substituindo o estilo "brutalist" anterior por um design limpo, profissional e educacional.

## 🎯 Princípios de Design

### Cores Principais
- **Primary (Azul)**: #0ea5e9 - Ação, hover, destaque
- **Secondary (Roxo)**: #7c3aed - Complementar, variações
- **Success (Verde)**: #22c55e - Aprovação, conclusão
- **Warning (Amarelo)**: #eab308 - Alerta, atenção
- **Error (Vermelho)**: #ef4444 - Erros, perigos
- **Neutral**: Cinzas escalados para acessibilidade

### Tipografia
- **Headings**: Font-weight 700-800, tracking controlado
- **Body**: Font-weight 400-600, line-height 1.5-1.6
- **Labels**: Font-weight 600, uppercase com tracking controlado

### Espacemento
- Consistente com múltiplos de 0.5rem
- margin e padding padronizados
- Gaps entre elementos bem definidos

## 📦 Componentes Principais

### 1. Buttons
```html
<!-- Primary Button -->
<button class="el-btn el-btn-primary">Ação Primária</button>

<!-- Secondary Button -->
<button class="el-btn el-btn-secondary">Ação Secundária</button>

<!-- Variables -->
.el-btn-sm    /* Pequeno */
.el-btn-lg    /* Grande */
.el-btn-block /* Width 100% */
```

### 2. Cards
```html
<!-- Standard Card -->
<div class="el-card">
    <img src="..." class="el-card-img">
    <div class="el-card-header">
        <h3 class="el-card-title">Título</h3>
    </div>
    <div class="el-card-body">Conteúdo</div>
</div>

<!-- Course Card (Especializado) -->
<div class="el-course-card">
    <div class="el-course-image">📖</div>
    <div class="el-course-content">
        <h3 class="el-course-title">Título do Curso</h3>
        <p class="el-course-teacher">Professor</p>
        <div class="el-course-stats">
            <div class="el-course-stat">🎓 N aulas</div>
        </div>
        <div class="el-course-progress">
            <div class="el-progress-container">
                <div class="el-progress-bar" style="width: 75%"></div>
            </div>
        </div>
        <div class="el-course-actions">
            <a href="#" class="el-btn el-btn-sm el-btn-primary">Ação</a>
        </div>
    </div>
</div>
```

### 3. Progress Bars
```html
<!-- Default (Blue) -->
<div class="el-progress-container">
    <div class="el-progress-bar" style="width: 75%"></div>
</div>

<!-- Success -->
<div class="el-progress-container">
    <div class="el-progress-bar success" style="width: 90%"></div>
</div>

<!-- Warning -->
<div class="el-progress-container">
    <div class="el-progress-bar warning" style="width: 85%"></div>
</div>

<!-- Error -->
<div class="el-progress-container">
    <div class="el-progress-bar error" style="width: 100%"></div>
</div>
```

### 4. Badges & Pills
```html
<!-- Primary Badge -->
<span class="el-badge el-badge-primary">Label</span>

<!-- Success Badge -->
<span class="el-badge el-badge-success">Concluído</span>

<!-- Warning Badge -->
<span class="el-badge el-badge-warning">Pendente</span>

<!-- Error Badge -->
<span class="el-badge el-badge-error">Erro</span>
```

### 5. Forms
```html
<div class="el-form-group">
    <label class="el-label">Campo Obrigatório *</label>
    <input type="text" class="el-input" placeholder="Digite...">
    <p class="el-form-help">Ajuda do campo</p>
</div>

<div class="el-form-group">
    <label class="el-label">Área de Texto</label>
    <textarea class="el-textarea" rows="4"></textarea>
</div>

<div class="el-form-group">
    <label class="el-label">Seleção</label>
    <select class="el-select">
        <option>Opção 1</option>
    </select>
</div>
```

### 6. Alerts
```html
<!-- Info Alert -->
<div class="el-alert el-alert-info">
    <div class="el-alert-icon">ℹ️</div>
    <div class="el-alert-content">
        <div class="el-alert-title">Informação</div>
        <p>Descrição da informação</p>
    </div>
</div>

<!-- Success Alert -->
<div class="el-alert el-alert-success">
    <div class="el-alert-icon">✓</div>
    <div class="el-alert-content">
        <div class="el-alert-title">Sucesso</div>
        <p>Operação completada</p>
    </div>
</div>

<!-- Warning Alert -->
<div class="el-alert el-alert-warning">
    <div class="el-alert-icon">⚠️</div>
    <div class="el-alert-content">
        <div class="el-alert-title">Atenção</div>
        <p>Verifique esta informação</p>
    </div>
</div>

<!-- Error Alert -->
<div class="el-alert el-alert-error">
    <div class="el-alert-icon">✕</div>
    <div class="el-alert-content">
        <div class="el-alert-title">Erro</div>
        <p>Descrição do erro</p>
    </div>
</div>
```

### 7. Stats Card
```html
<div class="el-stat-card">
    <div class="el-stat-icon">📚</div>
    <div class="el-stat-value">42</div>
    <div class="el-stat-label">Cursos</div>
    <div class="el-stat-detail">em andamento</div>
</div>
```

### 8. Hero Section
```html
<div class="el-hero">
    <h1>Título Principal</h1>
    <p>Descrição do contexto</p>
    <div class="flex gap-3">
        <button class="el-btn el-btn-lg">Ação Primária</button>
        <button class="el-btn el-btn-lg el-btn-outline">Ação Secundária</button>
    </div>
</div>
```

### 9. Lesson Card
```html
<div class="el-lesson-card">
    <div class="el-lesson-number">1</div>
    <div class="el-lesson-content">
        <h3 class="el-lesson-title">Título da Aula</h3>
        <p class="el-lesson-description">Descrição breve</p>
        <div class="el-lesson-metadata">
            <span>⏱ 45 min</span>
            <span>📎 3 anexos</span>
        </div>
    </div>
</div>
```

## 🎬 Layouts Recomendados

### Dashboard Professor
```
[Hero Section - Bem-vindo]
[Stats Grid (4 cards)]
[Main Content - 3 colunas]
├── Esquerda (2/3): Cursos Recentes em Grid ou Lista
└── Direita (1/3):
    ├── Widget Armazenamento
    ├── Widget Ações Rápidas
    └── Widget Governança
```

### Página de Cursos
```
[Hero Section - Estrutura de Cursos]
[Filtros/Search]
[Grid/List de Cursos com el-course-card]
[Modal de Criação de Curso]
```

### Página de Aulas
```
[Breadcrumb + Título]
[Button: Nova Aula]
[Lista de Aulas com el-lesson-card]
[Modal de Upload de Vídeo/Anexos]
```

### Dashboard Aluno
```
[Hero Section - O que aprender hoje?]
[Continuar Aprendendo - Cards recentes]
[Catálogo - Grid de Cursos]
[Filtros por Status]
```

### Página de Aula (Assistindo)
```
[Video Player - Tela cheia]
[Player Controls com progresso]
[Informações da Aula]
[Anexos para download]
[Botões: Anterior/Próxima]
```

### Página de Prova
```
[Header com tempo/questões]
[Questões em cards]
[Inputs de resposta]
[Barra de progresso]
[Botão Finalizar/Enviar]
```

## 🔄 Transições & Animações

```css
/* Hover Effect padrão */
transform: translateY(-2px);
box-shadow: elevado;
transition: var(--transition-normal);

/* Cards */
border-color: primary-300;
box-shadow: lg com cor principal;

/* Buttons */
transform: scale(1.02) ou translateY(-2px);
```

## 📱 Responsividade

- **Desktop**: Full layout com sidebars
- **Tablet**: 2 colunas, sidebars movem para baixo
- **Mobile**: 1 coluna, sem sidebars, elementos empilhados

Todos os componentes usam `grid-cols-1 md:grid-cols-2 lg:grid-cols-3` pattern.

## 🚀 Como Implementar

1. **Incluir CSS**: `<link rel="stylesheet" href="/assets/elearning-modern.css">`
2. **Usar Classes**: Verificar documento acima para cada componente
3. **Tailwind**: Continua disponível para ajustes rápidos
4. **Emojis**: Usar no lugar de Phosphor Icons para funcionalidade rápida
5. **Cores**: Usar variáveis CSS (--primary-500, etc) para consistência

## 📋 Checklist de Implementação

- [ ] Dashboard Professor
- [ ] Página Cursos (Professor)
- [ ] Página Aulas (Professor)
- [ ] Página Provas (Professor)
- [ ] Página Reportes (Professor)
- [ ] Dashboard Aluno (Meus Cursos)
- [ ] Página Curso (Aluno - Browsing)
- [ ] Página Aula (Aluno - Assistindo)
- [ ] Página Prova (Aluno - Fazendo)
- [ ] Página Resultado (Aluno)
- [ ] Página Certificados (Aluno)
- [ ] Página Histórico (Aluno)

## 🎨 Paleta de Cores Expandida

| Nome | Hex | Uso |
|------|-----|-----|
| Primary | #0ea5e9 | Ações, hover, destaque |
| Primary-Dark | #0284c7 | Estados ativos |
| Secondary | #7c3aed | Complementar, variações |
| Success | #22c55e | Aprovação, conclusão |
| Success-Dark | #16a34a | Estados ativos success |
| Warning | #eab308 | Avisos, alerta |
| Warning-Dark | #ca8a04 | Estados ativos warning |
| Error | #ef4444 | Erros, destruição |
| Error-Dark | #dc2626 | Estados ativos error |
| Slate-50 | #f8fafc | Background principal |
| Slate-100 | #f1f5f9 | Background secundário |
| Slate-900 | #0f172a | Texto principal |

## 🔧 Customizações Futuras

- [ ] Temas dark mode
- [ ] Animações de carregamento
- [ ] Tooltips customizados
- [ ] Menus dropdown modernos
- [ ] Modals com backdrop blur
- [ ] Notificações toast
- [ ] Loading skeletons

---
**Versão**: 1.0  
**Última atualização**: 2026-04-15  
**Autor**: Design System Team
