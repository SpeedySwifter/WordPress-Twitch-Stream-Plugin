# 🎮 SpeedySwifter Stream Integrator para Twitch v1.7.2

<div align="center">

![WordPress](https://img.shields.io/badge/WordPress-6.8-21759B?style=for-the-badge&logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Twitch](https://img.shields.io/badge/Twitch_API-9146FF?style=for-the-badge&logo=twitch&logoColor=white)
![License](https://img.shields.io/badge/License-GPL_v2+-green?style=for-the-badge)

[![GitHub Stars](https://img.shields.io/github/stars/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/SpeedySwifter/WordPress-Twitch-Stream-Plugin?style=social)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/forks)
[![GitHub Issues](https://img.shields.io/github/issues/SpeedySwifter/WordPress-Twitch-Stream-Plugin)](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/issues)

**Um plugin WordPress para integração de streams Twitch com suporte de aplicação móvel, agendamento, análise e suporte multi-idioma.**

[🚀 Funcionalidades](#-funcionalidades) • [📦 Instalação](#-instalação) • [🧩 Utilização](#-utilização) • [📋 Shortcodes](#-shortcodes) • [⚙️ Admin](#-configurações-admin) • [🌍 Idiomas](#-idiomas)

</div>

---

## 📌 O que é isso?

O **SpeedySwifter Stream Integrator para Twitch v1.7.2** fornece uma solução para integrar streams Twitch em sites WordPress. Oferece funcionalidades como integração de aplicação móvel, agendamento de streams, análise e suporte multi-idioma.

### ✨ Funcionalidades principais

- ✅ **Shortcodes simples** – `[twitch_stream channel="seucanal"]`
- 🔴 **Detecção de estado ao vivo** – Verificação automática se o stream está online
- 📺 **Player responsivo** – Integração Twitch adapta-se a todos os tamanhos de ecrã
- ⚙️ **Painel de administração** – Página de configurações confortável para credenciais API
- 🔐 **Integração API segura** – Utiliza API oficial Twitch Helix
- 💾 **Cache de tokens** – Reduz chamadas API graças a cache inteligente
- 🎨 **Personalizável** – Classes CSS para estilo individual
- 🧩 **Compatível com WordPress 6.9.1** – Testado com versão atual WP
- 🎯 **Informação de streams** – Título, jogo, espetadores, avatar, emblema ao vivo
- 📱 **Grelha de streams múltiplos** – Múltiplos streams em layout grelha
- 🧩 **Blocos Gutenberg** – Integração nativa do editor de blocos WordPress
- 🔧 **Suporte para construtores de páginas** – Elementor, Oxygen, Divi, Beaver Builder & mais
- 🍪 **Integração de banners cookie** – Conforme RGPD com 6 sistemas de cookies

---

## 🚀 Funcionalidades avançadas (v1.7.0)

### 📱 **Integração de aplicação móvel**
- **Progressive Web App (PWA)** com manifesto completo
- **Service Worker** para funcionalidade offline e cache
- **Notificações push** com suporte de chaves VAPID
- **Interface otimizada para mobile** com gestos tácteis
- **Pedidos de instalação de app** e banners inteligentes
- **Detecção offline** e sincronização

### 📅 **Agendador visual de streams**
- **Calendário interativo** com integração FullCalendar.js
- **Agendamento por arrastar e soltar** e reagendamento
- **Múltiplos modos de visualização** (Calendário, Lista, Timeline)
- **Rastreamento de estado em tempo real** (Agendado/Ao vivo/Concluído)
- **Padrões de streams recorrentes** (Diariamente/Semanalmente/Mensalmente)
- **Filtragem avançada** por data, estado, categoria

### 🛠️ **Construtor avançado de shortcodes**
- **GUI interativo** para criar shortcodes Twitch
- **Pré-visualização ao vivo** com auto-refresh
- **Suporte para todos os 13+ shortcodes do plugin**
- **Organização por categorias**
- **Modelos predefinidos** para início rápido
- **Funcionalidade copiar-colar**

### 🔒 **Integração de plugins de associação**
- **Suporte para 6 plugins de associação principais**
- **MemberPress, RCP, PMPro, WooCommerce Memberships**
- **Ultimate Member, integração s2Member**
- **Sistema de associação 4 níveis** (Grátis/Básico/Prémium/VIP)
- **Restrições de conteúdo** baseadas no nível de associação
- **Emblemas de associação** e indicadores visuais

### 🌍 **Suporte multi-idioma (7 idiomas)**
- **🇺🇸 English (en_US)**
- **🇩🇪 Deutsch (de_DE)**
- **🇫🇷 Français (fr_FR)**
- **🇪🇸 Español (es_ES)**
- **🇷🇺 Русский (ru_RU)**
- **🇵🇹 Português (pt_PT)**
- **🇯🇵 日本語 (ja_JP)**

### 💰 **Integração de donativos**
- **Botões Buy Me a Coffee** e PayPal
- **Formulários de donativos personalizáveis**
- **Objetivos de donativos e rastreamento de progresso**
- **Design responsivo** com modo escuro
- **Estatísticas de donativos** e análise

### 💬 **Integração do chat Twitch**
- **Integração de chat avançada** com seletor de emojis
- **Moderação de mensagens** e processamento de comandos
- **Temas de chat** e opções de personalização
- **Sondagem de mensagens em tempo real**
- **Exibição de emblemas e funções de utilizador**

### 📥 **Transferência de gravação de streams**
- **Funcionalidade de transferência VOD**
- **Gestão de gravações de streams**
- **Rastreamento do progresso de transferência**
- **Controlos do leitor de vídeo**
- **Permissões de transferência** e controlo de acesso

### 📊 **Painel avançado de análise**
- **Análise de streams** e métricas de desempenho
- **Estatísticas de espetadores** e rastreamento de envolvimento
- **Visualização de dados em tempo real**
- **Gráficos personalizáveis** e relatórios
- **Funcionalidade de exportação** para análise de dados

### 🛒 **Integração WooCommerce**
- **Produtos ligados a streams**
- **Acesso a streams ativado por compra**
- **Integração de e-commerce** para associações
- **Sincronização do estado de encomendas**
- **Rastreamento de receitas** e análise

---

## 🎯 Casos de utilização

### 📡 Perfeito para

- 🎮 **Sites de jogos** – Mostrar o seu próprio stream Twitch no site
- 🏆 **Equipas eSports** – Incorporar jogos ao vivo diretamente
- 🎥 **Criadores de conteúdo** – Integração de streams no blog WordPress
- 📰 **Portais de notícias** – Transmitir streams de eventos ao vivo
- 🎪 **Sites de eventos** – Transmitir conferências & torneios
- 📱 **Aplicações móveis** – PWA com capacidades offline
- 🔒 **Sites de associação** – Restrições de conteúdo e controlo de acesso
- 📅 **Redes de streams** – Agendar e gerir múltiplos streams

### 🔧 O que faz

```text
✓ Verificar automaticamente se o stream está ao vivo
✓ Mostrar player Twitch apenas para streams ao vivo
✓ Mostrar mensagem offline quando o stream não está ativo
✓ Totalmente responsivo para todos os dispositivos
✓ Aplicação móvel com notificações push
✓ Agendamento visual com interface de calendário
✓ Restrições de conteúdo baseadas em associação
✓ Suporte multi-idioma (7 idiomas)
✓ Análise avançada e relatórios
✓ PWA com funcionalidade offline
```

---

## 📦 Instalação

### Opção 1: Manual (Carregamento ZIP)

1. **Descarregar plugin** como ZIP
2. No WordPress: **Plugins → Instalar → Carregar plugin**
3. Selecionar ficheiro ZIP e instalar
4. **Ativar** plugin

### Opção 2: FTP/SFTP

```bash
# Clonar repositório
git clone https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin.git

# Mover pasta para wp-content/plugins/
mv WordPress-Twitch-Stream-Plugin /caminho/para/wordpress/wp-content/plugins/
```

Depois ativar na administração WordPress em **Plugins**.

---

## 🔑 Configuração da API Twitch

### 1️⃣ Criar aplicação Twitch

Precisa de uma **Aplicação de programador Twitch** para aceder à API:

1. Ir para: [https://dev.twitch.tv/console/apps](https://dev.twitch.tv/console/apps)
2. Clicar em **"Register Your Application"**
3. Preencher o formulário:

```
Name:                 O seu site WordPress
OAuth Redirect URLs:  https://o-seu-domínio.com
Category:             Website Integration
```

4. **Guardar** e anotar:
   - ✅ **Client ID**
   - ✅ **Client Secret** (mostrado apenas uma vez!)

### 2️⃣ Inserir credenciais no WordPress

1. Na administração WordPress: **Definições → API Twitch**
2. Inserir **Client ID**
3. Inserir **Client Secret**
4. **Guardar alterações**

✅ Concluído! O plugin está agora pronto para utilização.

---

## 🧩 Utilização

### Shortcode básico

```text
[twitch_stream channel="shroud"]
```

### Com opções

```text
[twitch_stream channel="shroud" width="100%" height="480"]
```

### Aplicação móvel avançada

```text
[twitch_mobile_app theme="dark" show_notifications="true"]
```

### Agendador de streams

```text
[twitch_stream_scheduler channel="seucanal" view="calendar"]
```

### Conteúdo de associação

```text
[twitch_membership_content level="premium"]
O seu conteúdo premium aqui
[/twitch_membership_content]
```

---

## 📋 Referência de shortcodes

### Shortcodes principais

| Shortcode | Descrição | Exemplo |
|-----------|-------------|---------|
| `[twitch_stream]` | Integração básica de stream | `[twitch_stream channel="shroud"]` |
| `[twitch_chat]` | Chat independente | `[twitch_chat channel="shroud"]` |
| `[twitch_follow_button]` | Botão de seguir | `[twitch_follow_button channel="shroud"]` |
| `[twitch_subscribe_button]` | Botão de subscrever | `[twitch_subscribe_button channel="shroud"]` |
| `[twitch_clips]` | Clips do canal | `[twitch_clips channel="shroud" limit="10"]` |
| `[twitch_vod]` | Transmissões passadas | `[twitch_vod channel="shroud" type="archive"]` |

### Shortcodes avançados

| Shortcode | Descrição | Exemplo |
|-----------|-------------|---------|
| `[twitch_mobile_app]` | Interface de aplicação móvel | `[twitch_mobile_app theme="dark"]` |
| `[twitch_stream_scheduler]` | Agendador visual | `[twitch_stream_scheduler view="calendar"]` |
| `[twitch_shortcode_builder]` | GUI do construtor de shortcodes | `[twitch_shortcode_builder show_preview="true"]` |
| `[twitch_membership_content]` | Conteúdo restrito | `[twitch_membership_content level="vip"]` |
| `[twitch_donations]` | Integração de donativos | `[twitch_donations type="both"]` |
| `[twitch_chat_integration]` | Chat avançado | `[twitch_chat_integration theme="dark"]` |
| `[twitch_recording_download]` | Transferências VOD | `[twitch_recording_download limit="10"]` |
| `[twitch_analytics]` | Painel de análise | `[twitch_analytics time_range="7d"]` |

### Shortcodes utilitários

| Shortcode | Descrição | Exemplo |
|-----------|-------------|---------|
| `[twitch_pwa_install]` | Botão de instalação PWA | `[twitch_pwa_install text="Instalar app"]` |
| `[twitch_mobile_menu]` | Navegação móvel | `[twitch_mobile_menu position="left"]` |
| `[twitch_mobile_streams]` | Grelha de streams móvel | `[twitch_mobile_streams limit="10"]` |
| `[twitch_push_notifications]` | Definições de notificações | `[twitch_push_notifications show_settings="true"]` |
| `[twitch_upcoming_streams]` | Streams próximos | `[twitch_upcoming_streams limit="5"]` |
| `[twitch_stream_schedule]` | Horário semanal | `[twitch_stream_schedule days="7"]` |

---

## ⚙️ Configurações admin

### Página de configurações principal
**Administração WordPress → Definições → API Twitch**

- **Client ID & Secret** – Credenciais API Twitch
- **Opções de cache** – Configurações de cache de tokens e dados
- **Opções de visualização** – Dimensões de player padrão e temas

### Configurações de aplicação móvel
**Administração WordPress → Dashboard Twitch → Aplicação móvel**

- **Configuração PWA** – Configurações do manifesto da app e service worker
- **Notificações push** – Chaves VAPID e preferências de notificações
- **Configurações de tema** – Personalização do aspeto da aplicação móvel

### Agendador de streams
**Administração WordPress → Dashboard Twitch → Agendador de streams**

- **Configurações de calendário** – Vista padrão e fuso horário
- **Configurações de notificações** – Preferências de email e notificações push
- **Padrões recorrentes** – Agendamento automatizado de streams

### Integração de associação
**Administração WordPress → Dashboard Twitch → Associação**

- **Deteção de plugins** – Deteção automática de plugins de associação
- **Mapeamento de níveis** – Mapear níveis de associação para níveis de acesso
- **Restrições de conteúdo** – Configurar regras de controlo de acesso

---

## 📂 Estrutura do plugin

```
WordPress-Twitch-Stream-Plugin/
│
├── 📄 wp-twitch-stream.php                    # Ficheiro plugin principal
├── 📄 README.md                               # Documentação (7 idiomas)
├── 📄 LICENSE                                 # Licença MIT
│
├── 📁 admin/
│   ├── 📄 settings-page.php                   # Página configurações admin
│   └── 📄 admin-styles.css                    # Estilos admin
│
├── 📁 includes/
│   ├── 📄 twitch-api.php                      # Gestor API
│   ├── 📄 shortcode.php                       # Lógica shortcodes
│   ├── 📄 token-manager.php                   # Cache de tokens
│   ├── 📄 gutenberg-block.php                 # Blocos Gutenberg
│   ├── 📄 page-builder-compatibility.php      # Integração construtores de páginas
│   ├── 📄 cookie-integration.php              # Integração banners de cookies
│   ├── 📄 sidebar-widgets.php                 # Widgets VOD & clips
│   ├── 📄 donation-integration.php            # Sistema de donativos
│   ├── 📄 twitch-chat-integration.php         # Chat avançado
│   ├── 📄 stream-recording-download.php       # Transferências VOD
│   ├── 📄 advanced-analytics-dashboard.php    # Sistema de análise
│   ├── 📄 multi-language-support.php          # Suporte i18n
│   ├── 📄 woocommerce-integration.php         # Integração eCommerce
│   ├── 📄 membership-plugin-integration.php   # Sistema de associação
│   ├── 📄 advanced-shortcode-builder.php      # Construtor de shortcodes
│   ├── 📄 visual-stream-scheduler.php         # Agendador calendário
│   └── 📄 mobile-app-integration.php          # PWA & aplicação móvel
│
├── 📁 assets/
│   ├── 📁 css/
│   │   ├── 📄 frontend.css                     # Estilos frontend
│   │   ├── 📄 block.css                        # Estilos blocos Gutenberg
│   │   ├── 📄 page-builder-compatibility.css   # Estilos construtores de páginas
│   │   ├── 📄 cookie-integration.css           # Estilos integração cookies
│   │   ├── 📄 vod-clips.css                    # Estilos VOD & clips
│   │   ├── 📄 donations.css                    # Estilos sistema de donativos
│   │   ├── 📄 twitch-chat.css                  # Estilos integração chat
│   │   ├── 📄 recording-download.css           # Estilos sistema de transferência
│   │   ├── 📄 analytics-dashboard.css          # Estilos análise
│   │   ├── 📄 language-support.css             # Estilos multi-idioma
│   │   ├── 📄 woocommerce-integration.css      # Estilos eCommerce
│   │   ├── 📄 membership-integration.css       # Estilos associação
│   │   ├── 📄 shortcode-builder.css            # Estilos interface construtor
│   │   ├── 📄 stream-scheduler.css             # Estilos calendário
│   │   └── 📄 mobile-app.css                   # Estilos aplicação móvel
│   └── 📁 js/
│       ├── 📄 player.js                        # Funções player
│       ├── 📄 block.js                         # JavaScript blocos Gutenberg
│       ├── 📄 oxygen-builder.js                # JS construtor Oxygen
│       ├── 📄 divi-builder.js                  # JS construtor Divi
│       ├── 📄 donations.js                     # JS sistema de donativos
│       ├── 📄 twitch-chat.js                   # JS integração chat
│       ├── 📄 recording-download.js            # JS sistema de transferência
│       ├── 📄 analytics-dashboard.js           # JS análise
│       ├── 📄 language-support.js              # JS multi-idioma
│       ├── 📄 woocommerce-integration.js       # JS eCommerce
│       ├── 📄 membership-integration.js        # JS associação
│       ├── 📄 shortcode-builder.js             # JS interface construtor
│       ├── 📄 stream-scheduler.js              # JS calendário
│       └── 📄 mobile-app.js                    # JS aplicação móvel
│
├── 📁 docs/
│   ├── 📄 cookie-banner-integration.md        # Tutorial integração cookies
│   ├── 📄 membership-plugin-integration.md    # Guia configuração associação
│   ├── 📄 mobile-app-setup.md                 # Configuração PWA
│   └── 📄 api-reference.md                    # Referência API completa
│
├── 📁 languages/
│   ├── 📄 wp-twitch-stream-en_US.po
│   ├── 📄 wp-twitch-stream-en_US.mo
│   ├── 📄 wp-twitch-stream-de_DE.po
│   ├── 📄 wp-twitch-stream-de_DE.mo
│   ├── 📄 wp-twitch-stream-fr_FR.po
│   ├── 📄 wp-twitch-stream-fr_FR.mo
│   ├── 📄 wp-twitch-stream-es_ES.po
│   ├── 📄 wp-twitch-stream-es_ES.mo
│   ├── 📄 wp-twitch-stream-ru_RU.po
│   ├── 📄 wp-twitch-stream-ru_RU.mo
│   ├── 📄 wp-twitch-stream-pt_PT.po
│   ├── 📄 wp-twitch-stream-pt_PT.mo
│   ├── 📄 wp-twitch-stream-ja_JP.po
│   └── 📄 wp-twitch-stream-ja_JP.mo
│
└── 📁 templates/
    ├── 📄 offline-page.html                   # Página offline PWA
    └── 📄 mobile-app-manifest.json            # Modelo manifesto PWA
```

---

## 🌍 Idiomas / Languages

O plugin suporta **7 idiomas** com traduções completas:

### 🇺🇸 English (en_US) - Predefinição
- Documentação e interface de utilizador completas em inglês

### 🇩🇪 Deutsch (de_DE)
- Vollständige deutsche Dokumentation und Benutzeroberfläche

### 🇫🇷 Français (fr_FR)
- Documentation et interface utilisateur complètes en français

### 🇪🇸 Español (es_ES)
- Documentación e interfaz de usuario completas en español

### 🇷🇺 Русский (ru_RU)
- Полная документация и пользовательский интерфейс на русском

### 🇵🇹 Português (pt_PT)
- Documentação e interface do usuário completas em português

### 🇯🇵 日本語 (ja_JP)
- 完全な日本語のドキュメントとユーザーインターフェース

---

## 📊 Histórico de versões

### v1.7.0 - Integração de aplicação móvel 🚀
- 📱 Progressive Web App (PWA) com suporte offline
- 🔔 Notificações push com configuração de chaves VAPID
- 👆 Gestos tácteis e interface otimizada para mobile
- 📅 Agendador visual de streams com interface de calendário
- 🛠️ Construtor avançado de shortcodes GUI
- 🔒 Integração de plugins de associação (6 plugins suportados)
- 🌍 Suporte multi-idioma (7 idiomas)

### v1.6.0 - Agendador visual de streams 📅
- 📅 Calendário interativo com FullCalendar.js
- 🖱️ Agendamento por arrastar e soltar
- 📋 Múltiplos modos de visualização (Calendário/Lista/Timeline)
- 🔄 Rastreamento de estado em tempo real e atualizações
- 🔁 Suporte de padrões de streams recorrentes
- 🎯 Funcionalidades avançadas de filtragem e pesquisa

### v1.5.0 - Construtor avançado de shortcodes 🛠️
- 🎨 GUI interativo para criar shortcodes
- 👀 Pré-visualização ao vivo com auto-refresh
- 📋 Suporte para todos os shortcodes do plugin (13+)
- 📂 Organização por categorias
- 💾 Modelos predefinidos e opções de início rápido
- 📋 Funcionalidade copiar-colar

### v1.4.0 - Integração de plugins de associação 🔒
- 👥 Suporte para 6 plugins de associação principais
- 🏆 Sistema de associação 4 níveis (Grátis/Básico/Prémium/VIP)
- 🚫 Restrições de conteúdo baseadas no nível de associação
- 🏷️ Emblemas de associação e indicadores visuais
- 🔐 Gestão de controlos de acesso e permissões

### v1.3.0 - Conjunto de funcionalidades avançadas 💎
- 💰 Integração de donativos (Buy Me a Coffee + PayPal)
- 💬 Integração do chat Twitch com suporte de emojis
- 📥 Funcionalidade de transferência de gravação de streams
- 📊 Painel avançado de análise com gráficos
- 🌍 Suporte multi-idioma (EN/DE/FR/ES/RU/PT/JA)

### v1.2.0 - Integração WooCommerce 🛒
- 🛒 Integração de e-commerce para produtos ligados a streams
- 💳 Acesso a streams ativado por compra
- 📈 Rastreamento de receitas e sincronização de encomendas
- 🏪 Suporte de associações e subscrições WooCommerce

### v1.1.0 - Suporte de conteúdo expandido 🎬
- 🎬 Suporte VOD (Video on Demand) com arquivos
- 🎞️ Integração e incorporação de clips Twitch
- 📱 Widgets de sidebar para VOD e clips
- 🧩 Compatibilidade expandida de construtores de páginas

### v1.0.0 - Versão principal 🎯
- ✅ Integração básica de stream Twitch
- 🔴 Detecção de estado ao vivo
- 📺 Integração de player responsivo
- ⚙️ Painel de parâmetros admin
- 🔐 Integração API segura

---

## 🗺️ Roadmap

### ✅ Versão 1.7.0 (Concluída - Versão atual)
- [x] **Integração de aplicação móvel** - Progressive Web App (PWA) com suporte offline
- [x] **Notificações push** - Configuração de chaves VAPID e notificações de navegador
- [x] **Gestos tácteis** - Interface otimizada para mobile com controlo por deslizamento
- [x] **Agendador visual de streams** - Interface de calendário para planeamento de streams
- [x] **Construtor avançado de shortcodes** - GUI para criar shortcodes personalizados
- [x] **Integração de plugins de associação** - Suporte para 6 plugins de associação principais
- [x] **Suporte multi-idioma** - Traduções completas em 7 idiomas
- [x] **Integração de donativos** - Botões Buy Me a Coffee e PayPal
- [x] **Integração do chat Twitch** - Chat avançado com suporte de emojis
- [x] **Transferência de gravação de streams** - Funcionalidade de transferência VOD
- [x] **Painel avançado de análise** - Métricas em tempo real e gráficos
- [x] **Integração WooCommerce** - Integração e-commerce para associações
- [x] **Integração de banners de cookie** - Conforme RGPD com 6 sistemas de cookies
- [x] **Suporte VOD** - Video on Demand com arquivos e destaques
- [x] **Integração de clips** - Integração e gestão de clips Twitch
- [x] **Widgets de sidebar** - Widgets VOD e clips para sidebars
- [x] **Suporte para construtores de páginas** - Elementor, Oxygen, Divi, Beaver Builder & mais
- [x] **Blocos Gutenberg** - Integração nativa do editor de blocos WordPress
- [x] **Pontos finais REST API** - Acesso programático às funcionalidades do plugin
- [x] **Suporte webhook** - Integração EventSub para atualizações em tempo real
- [x] **Painel multi-canal** - Gerir múltiplos canais Twitch
- [x] **Construtor CSS personalizado** - Interface de ajuste CSS visual
- [x] **Cache avançado** - Otimização de desempenho e opções de cache
- [x] **Suporte do modo escuro** - Implementação completa do tema escuro
- [x] **Design responsivo** - Layouts responsivos mobile-first
- [x] **Integração de stream básica** - Funcionalidade de integração Twitch básica
- [x] **Detecção de estado ao vivo** - Verificação automática do estado do stream
- [x] **Painel de parâmetros admin** - Interface de configuração completa
- [x] **Sistema de cache de tokens** - Gestão inteligente de tokens API

### 🚀 Versão 1.8.0 (Planeada - Próxima versão)
- [ ] **Funcionalidades alimentadas por IA** - Recomendações de streams inteligentes e análises
- [ ] **Monetização avançada** - Modelos de subscrição e funcionalidades premium
- [ ] **Integração multi-plataforma** - Suporte YouTube, Facebook Gaming
- [ ] **Funcionalidades empresariais** - Soluções white-label e segurança avançada
- [ ] **Melhorias de desempenho** - Cache e otimização avançados
- [ ] **Ferramentas de desenvolvimento** - API e capacidades webhook melhoradas

### 🔮 Versão 2.0.0 (Planeada a longo prazo)
- [ ] **Assistente IA de stream** - Gestão e otimização de streams alimentados por IA
- [ ] **Suite de análises avançada** - Relatórios e insights de nível empresarial
- [ ] **Desenvolvimento de aplicação móvel** - Aplicações móveis dedicadas
- [ ] **Integração cloud** - Suporte avançado de armazenamento cloud e CDN
- [ ] **Limitação da taxa API** - Gestão avançada de quotas e escalabilidade
- [ ] **Soluções white-label** - Branding personalizado e opções de licenciamento

Aceitamos contribuições! Veja o nosso [Guia de contribuição](CONTRIBUTING.md) para detalhes.

1. Bifurcar o repositório
2. Criar ramo de funcionalidade (`git checkout -b feature/amazing-feature`)
3. Confirmar alterações (`git commit -m 'Add amazing feature'`)
4. Enviar para o ramo (`git push origin feature/amazing-feature`)
5. Abrir Pull Request

---

## 📄 Licença

Este projeto está licenciado sob a **MIT License** - veja o ficheiro [LICENSE](LICENSE) para detalhes.

---

## 🙏 Agradecimentos

- **Twitch** pela incrível plataforma de streaming e API
- **WordPress** pela incrível base CMS
- **FullCalendar.js** pela funcionalidade de calendário
- **Todos os contribuidores** que ajudam a melhorar este plugin

---

## 📞 Suporte

- 📧 **Email**: support@speedyswifter.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/issues)
- 📖 **Documentação**: [Wiki](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/wiki)
- 💬 **Discussões**: [GitHub Discussions](https://github.com/SpeedySwifter/WordPress-Twitch-Stream-Plugin/discussions)

---

<div align="center">

**Criado com ❤️ por [SpeedySwifter](https://github.com/SpeedySwifter)**

⭐ Se encontrou este plugin útil, por favor dê-lhe uma estrela!

</div>
