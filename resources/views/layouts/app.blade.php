<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'LSPD Panel')</title>

    {{-- AdminLTE & FONT DEPENDENCIES --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
    {{-- DATATABLES DEPENDENCIES --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap4.min.css">

    {{-- SELECT2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

    {{-- GLOBAL MODERN DARK THEME STYLES --}}
    <style>
        :root {
            --bg-dark: #1a202c;       /* Deep Blue/Grey Background */
            --card-dark: #2d3748;     /* Lighter Card Background */
            --text-light: #e2e8f0;    /* Soft White Text */
            --accent-gradient: linear-gradient(135deg, #1f1c2c 0%, #928dab 100%); /* Purple/Grey Hero */
            --glass-border: rgba(255,255,255,0.08);
        }

        /* 1. Global Backgrounds & Text */
        body, .content-wrapper {
            background-color: var(--bg-dark) !important;
            color: var(--text-light) !important;
        }
        .dark-mode .content-wrapper { background-color: var(--bg-dark) !important; }

        /* 2. The Hero Header (Global Transformation) */
        .content-header, .hero-header-internal {
            background: var(--accent-gradient);
            color: white;
            padding: 2.5rem 1.5rem 5rem 1.5rem; /* Viel Padding unten für Overlap */
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            margin-bottom: -3rem !important; /* Zieht den Content nach oben */
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            position: relative;
            z-index: 1;
        }
        .content-header h1 { font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .breadcrumb-item a { color: rgba(255,255,255,0.8); }
        .breadcrumb-item.active { color: white; font-weight: bold; }

        /* 3. Modern Cards (Glass-Like Look for all standard cards) */
        .card {
            background-color: var(--card-dark);
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 15px;
            margin-bottom: 1.5rem;
            color: var(--text-light);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        /* Hover Effekt nur für Info-Boxen oder interaktive Cards */
        .small-box:hover, .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .card-header {
            background-color: rgba(0,0,0,0.2);
            border-bottom: 1px solid var(--glass-border);
            border-radius: 15px 15px 0 0 !important;
        }
        
        /* 4. Inputs & Tables Dark Mode Overrides */
        .form-control, .select2-selection {
            background-color: #171923 !important; /* Very Dark input bg */
            border: 1px solid var(--glass-border) !important;
            color: white !important;
            border-radius: 8px;
        }
        .form-control:focus {
            background-color: #1a202c !important;
            border-color: #6366f1 !important; /* Indigo Focus */
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        .table {
            color: var(--text-light) !important;
            margin-bottom: 0;
        }
        .table thead th {
            border-bottom: 2px solid var(--glass-border);
            border-top: none;
            background-color: rgba(0,0,0,0.2);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .table td, .table th { border-top: 1px solid var(--glass-border); }
        .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(255,255,255,0.02); }
        .table-hover tbody tr:hover { background-color: rgba(255,255,255,0.05); }

        /* 5. Sidebar & Navbar Tweaks */
        .main-sidebar { 
            background-color: #0f111a !important; 
            box-shadow: 5px 0 15px rgba(0,0,0,0.3);
        }
        .main-header {
            border-bottom: none;
            background-color: var(--bg-dark) !important; /* Nahtloser Übergang */
            box-shadow: none;
        }
        .nav-link { color: rgba(255,255,255,0.8) !important; }
        .nav-link.active { 
            background-color: rgba(255,255,255,0.1) !important; 
            color: white !important; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
        }
        
        /* Navbar: Notification Badge Size */
        .main-header .navbar-badge { font-size: 0.75rem; padding: 3px 6px; top: 6px; right: 3px; font-weight: 700; }

        /* 6. Utility Helper */
        .glass-card {
            background: rgba(45, 45, 45, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
        }
        
        /* Preloader */
        .preloader { background-color: var(--bg-dark); }
        .ekg-loader { width: 20vw; height: 10vw; max-width: 300px; max-height: 150px; min-width: 120px; min-height: 60px; }
        .ekg-loader path { stroke: #928dab; stroke-width: 4; stroke-dasharray: 1000; stroke-dashoffset: 1000; animation: draw 2s linear infinite; }
        @keyframes draw { to { stroke-dashoffset: 0; } }

        /* Dark Mode: Select2 Overrides */
        .select2-dropdown { background-color: var(--card-dark); border: 1px solid var(--glass-border); }
        .dark-mode .select2-container--bootstrap4 .select2-selection,
        .dark-mode .select2-dropdown { background-color: #343a40; border-color: #6c757d; }
        .dark-mode .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { color: #fff; }
        .dark-mode .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b { border-color: #fff transparent transparent transparent; }
        .dark-mode .select2-search--dropdown .select2-search__field { background-color: #454d55; color: #fff; }
        .dark-mode .select2-container--bootstrap4 .select2-results__option--highlighted { background-color: #6366f1; color: #fff; }
        .dark-mode .select2-container--bootstrap4 .select2-results__option { color: #dee2e6; }
        
        /* Select2: Multi-Select Tag Styling */
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice { background-color: #007bff; color: #fff !important; margin-top: 2px !important; margin-bottom: 2px !important; float: left; }
        .dark-mode .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice { background-color: #3f6791; }
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove { color: #fff !important; text-shadow: 0 1px 0 #495057; font-size: 1.5rem; line-height: 1; opacity: .5; background-color: transparent; border: 0; float: left; padding: 0 3px; margin: 0 1px 0 3px; font-weight: 700; }
        .dark-mode .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover { color: #fff; text-decoration: none; }
        
        /* Select2 Multi Height Fixes */
        .select2-container--bootstrap4 .select2-selection--multiple { min-height: 38px; height: auto !important; padding-top: 5px !important; padding-bottom: 5px !important; }
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered { line-height: normal; display: block; padding: 0; margin: 0; }
        .select2-container--bootstrap4 .select2-selection--multiple .select2-search--inline { float: none !important; display: inline-block; width: 100%; }
        .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field { min-width: 100px !important; }

        /* List Group */
        .list-group-item { background-color: var(--card-dark); border-color: var(--glass-border); color: white; }
    </style>

    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed dark-mode">
<div class="wrapper">

    {{-- PRELOADER --}}
    <div class="preloader flex-column justify-content-center align-items-center">
        <svg class="ekg-loader" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130 65">
            <path fill="none" d="M0,32.5 h20 l5,-20 l5,40 l5,-30 l5,10 h60"/>
        </svg>
    </div>

    {{-- NAVBAR --}}
    <nav class="main-header navbar navbar-expand navbar-dark" id="mainNavbar">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            @if(session('is_remembered') === false)
            <li class="nav-item d-flex align-items-center px-2">
                <span class="text-muted small d-none d-sm-inline mr-1">Sitzung endet in:</span>
                <span class="badge badge-info" id="session-timer">--:--</span>
            </li>
            @endif

            {{-- Notification Dropdown --}}
            <li class="nav-item dropdown" id="notification-dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge" id="notification-count" style="display: none;">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notification-list" style="left: inherit; right: 0px;">
                    <span class="dropdown-item dropdown-header">Lade Benachrichtigungen...</span>
                </div>
            </li>

            {{-- User Dropdown --}}
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    @if(Auth::check())
                        <img src="{{ Auth::user()->avatar }}" class="user-image img-circle elevation-1" alt="User Image">
                        <span class="d-none d-md-inline font-weight-bold">{{ Auth::user()->name }}</span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="background-color: var(--card-dark); border: 1px solid var(--glass-border);">
                    @if(Auth::check())
                        <li class="user-header" style="background: var(--accent-gradient); color: white;">
                            <img src="{{ Auth::user()->avatar }}" class="img-circle elevation-2" alt="User Image">
                            <p>
                                {{ Auth::user()->name }}
                                <small>{{ Auth::user()->rank ?? 'Mitarbeiter' }}</small>
                            </p>
                        </li>
                        <li class="user-footer" style="background-color: var(--card-dark);">
                             <a href="{{ route('profile.show') }}" class="btn btn-secondary btn-flat">Profil</a>
                            <a href="#" class="btn btn-secondary btn-flat float-right"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Abmelden
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    @endif
                </ul>
            </li>
        </ul>
    </nav>

    {{-- MAIN SIDEBAR --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4" id="mainSidebar">
        <a href="{{ route('dashboard') }}" class="brand-link d-flex align-items-center justify-content-center" style="border-bottom: 1px solid var(--glass-border);">
            <i class="fas fa-user-shield fa-lg elevation-3 mr-3 text-white" style="opacity: .9"></i>            
            <span class="brand-text font-weight-bold">LSPD Panel</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                @include('layouts.navigation')
            </nav>
        </div>
    </aside>

    {{-- CONTENT WRAPPER --}}
    <div class="content-wrapper">
        {{-- Hier rendern wir den Content. Der 'content-header' im Child-View wird durch CSS transformiert. --}}
        @yield('content')
    </div>

    {{-- FOOTER --}}
    <footer class="main-footer" style="background-color: var(--bg-dark); color: #888; border-top: 1px solid var(--glass-border);">        
        <div class="float-right d-none d-sm-inline">Version 1.0</div>
        <strong>Copyright &copy; 2025 LSPD Panel.</strong> All rights reserved.
    </footer>
</div>

{{-- JAVASCRIPT --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

{{-- AJAX CSRF Setup --}}
<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

{{-- DATATABLES JS --}}
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap4.min.js"></script>

{{-- SWEETALERT2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- SELECT2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- ECHO & PUSHER DEPENDENCIES --}}
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.0/echo.iife.min.js"></script>

{{-- ===================================================================== --}}
{{-- === HAUPT-SKRIPTBLOCK (Theme, Notifications & Push Logic Combined) === --}}
{{-- ===================================================================== --}}
<script>
    $(document).ready(function() {
        
        // --- Select2 Init ---
        $('.select2').select2({ theme: 'bootstrap4' });

        // ==========================================
        // 2. HELPER: SWEETALERT
        // ==========================================
        function decodeHtml(str) {
             if (!str) return '';
             const doc = new DOMParser().parseFromString(str, "text/html");
             return doc.documentElement.textContent;
        }
        function showSweetAlert(type, message) {
            setTimeout(() => {
                if (typeof Swal === 'undefined') return;
                let title = type === 'success' ? 'Erfolg!' : 'Fehler!';
                Swal.fire({
                    toast: true, position: 'top-end', icon: type,
                    title: title, text: decodeHtml(message),
                    showConfirmButton: false, timer: 3000,
                    background: '#2d3748', color: '#fff' // Dark Mode Anpassung
                });
            }, 50);
        }
        // Flash Messages anzeigen
        const successMessage = ('{{ session("success") }}' || '').trim();
        const errorMessage = ('{{ session("error") }}' || '').trim();
        if (successMessage.length > 0) showSweetAlert('success', successMessage);
        else if (errorMessage.length > 0) showSweetAlert('error', errorMessage);

        // ==========================================
        // 3. ECHO / WEBSOCKET SETUP
        // ==========================================
        if (typeof Pusher !== 'undefined' && typeof Echo !== 'undefined') {
            window.Pusher = Pusher;
            const useTls = window.location.protocol === 'https:' || '{{ env("VITE_REVERB_SCHEME") }}' === 'https';

            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ env("REVERB_APP_KEY") }}',
                wsHost: '{{ env("VITE_REVERB_HOST", "lspd.geeknetz.de") }}',
                wsPort: {{ env("VITE_REVERB_PORT") ?? 80 }},
                wssPort: {{ env("VITE_REVERB_PORT") ?? 443 }},
                forceTLS: useTls,
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
                authorizer: (channel, options) => {
                    return {
                        authorize: (socketId, callback) => {
                            $.post('/broadcasting/auth', {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                socket_id: socketId,
                                channel_name: channel.name
                            })
                            .done(response => { callback(false, response); })
                            .fail(error => { callback(true, error); });
                        }
                    };
                },
            });
        }

        // ==========================================
        // 4. PUSH NOTIFICATION LOGIK (VAPID)
        // ==========================================
        const VAPID_PUBLIC_KEY = '{{ config('webpush.vapid.public_key') }}';

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) { outputArray[i] = rawData.charCodeAt(i); }
            return outputArray;
        }

        // Diese Funktion prüft den Status und zeigt die Buttons im Dropdown an
        function checkPushStatus() {
            if (!VAPID_PUBLIC_KEY || !('serviceWorker' in navigator) || !('PushManager' in window)) return;

            const $enableBtn = $('#enable-push');
            const $disableBtn = $('#disable-push');

            // Reset
            $enableBtn.hide(); 
            $disableBtn.hide();

            if (Notification.permission === 'denied') {
                return;
            }

            navigator.serviceWorker.ready.then(reg => {
                reg.pushManager.getSubscription().then(sub => {
                    if (sub) {
                        $disableBtn.show();
                    } else {
                        $enableBtn.show();
                    }
                }).catch(err => console.error('Push Subscription Error:', err));
            });
        }

        // Click Handler (Event Delegation für dynamische Buttons)
        $(document).on('click', '#enable-push', function(e) {
            e.stopPropagation(); // Dropdown offen lassen
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    navigator.serviceWorker.ready.then(reg => {
                        const subscribeOptions = {
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
                        };
                        return reg.pushManager.subscribe(subscribeOptions);
                    }).then(sub => {
                        // Abo an Server senden
                        $.ajax({
                            url: '{{ route('push.subscribe') }}',
                            method: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify(sub),
                            success: function() {
                                showSweetAlert('success', 'Desktop-Benachrichtigungen aktiviert!');
                                checkPushStatus(); 
                            }
                        });
                    }).catch(err => {
                        console.error(err);
                        alert('Fehler bei der Aktivierung.');
                    });
                }
            });
        });

        $(document).on('click', '#disable-push', function(e) {
            e.stopPropagation(); // Dropdown offen lassen
            navigator.serviceWorker.ready.then(reg => {
                reg.pushManager.getSubscription().then(sub => {
                    if (sub) {
                        sub.unsubscribe().then(() => {
                            // Server informieren
                            $.ajax({
                                url: '{{ route('push.unsubscribe') }}',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({ endpoint: sub.endpoint }),
                                success: function() {
                                    showSweetAlert('success', 'Deaktiviert.');
                                    checkPushStatus();
                                }
                            });
                        });
                    }
                });
            });
        });

        // Service Worker registrieren (einmalig)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }


        // ==========================================
        // 5. BENACHRICHTIGUNGEN LADEN
        // ==========================================
        function fetchNotifications() {
            const notificationCount = $('#notification-count');
            const notificationList = $('#notification-list');
            const fetchUrl = '{{ route("api.notifications.fetch") }}';

            // Merken, welche Gruppen offen waren
            let openGroups = [];
            $('#notification-list .collapse.show').each(function() {
                openGroups.push($(this).attr('id'));
            });

            $.ajax({
                url: fetchUrl, method: 'GET', dataType: 'json',
                success: function(response) {
                    const htmlContent = response.items_html;
                    
                    // Counter Update
                    if (response.count > 0) { notificationCount.text(response.count).show(); }
                    else { notificationCount.hide(); }

                    // HTML Inject
                    if (htmlContent) {
                        notificationList.html(htmlContent);
                        // Offene Gruppen wiederherstellen
                        openGroups.forEach(function(id) { $(`#${id}`).collapse('show'); });
                        
                        // Buttons prüfen
                        checkPushStatus();
                    } else {
                        notificationList.html('<a href="#" class="dropdown-item"><span class="text-muted">Keine Meldungen.</span></a>');
                    }
                },
                error: function(xhr) {
                    console.error('Notification Load Error:', xhr);
                }
            });
        }
        
        // Initial laden
        fetchNotifications();

        // Echo Listener für neue Notifications
        @auth
        if (typeof window.Echo !== 'undefined') {
             window.Echo.private(`users.{{ Auth::id() }}`)
                .listen('.new.ems.notification', (e) => {
                     fetchNotifications();
                     $('#notification-dropdown .fa-bell').addClass('text-warning').delay(500).queue(function(next){ $(this).removeClass('text-warning'); next(); });
                });
        }
        @endauth

        // Dropdown Click-Handling (Offen halten)
        $(document).on('click', '#notification-dropdown .dropdown-menu', function (e) {
            const isToggle = $(e.target).closest('a[data-toggle="collapse"]').length > 0;
            const isButton = $(e.target).closest('button').length > 0;
            const isForm = $(e.target).closest('form').length > 0;
            
            if (isToggle || isButton || isForm) { e.stopPropagation(); }
        });

        // AJAX Mark Read Handler
        $(document).on('click', '.mark-read-ajax-btn', function(e) {
            e.preventDefault(); e.stopPropagation();
            var $btn = $(this);
            var url = $btn.data('url');
            var $row = $('#notif-row-' + $btn.data('id'));
            var originalContent = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin text-muted"></i>').prop('disabled', true);

            $.ajax({
                url: url, type: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    if (res.success) {
                        $row.slideUp(200, function() { $(this).remove(); });
                        if (res.remaining_count !== undefined) {
                            $('#notification-count').text(res.remaining_count);
                            if (res.remaining_count <= 0) $('#notification-count').hide();
                        }
                    }
                },
                error: function() {
                    $btn.html(originalContent).prop('disabled', false);
                }
            });
        });

    });
</script>

{{-- FINALER SESSION-TIMER (SERVER-GESTEUERT MIT PING-RESET & DEBUGGING) --}}
@if(session('is_remembered') === false)
<script>
 (function() {
  // === DEBUGGING: Globale Log-Funktion ===
  const DEBUG = false; 
  function log(message, ...args) {
      if (DEBUG) {
          console.log(`%c[TIMER_DEBUG] %c${message}`, 'color: #1e90ff; font-weight: bold;', 'color: black;', ...args);
      }
  }
  log('Skript wird initialisiert.');

  const puffer = 10; // 10 Sekunden Puffer
  
  const timerElement = document.getElementById('session-timer');
  const timerTextElement = document.querySelector('.d-sm-inline.mr-1');
  if(!timerElement) {
      log('%cFEHLER: Timer-Element #session-timer nicht gefunden. Skript gestoppt.', 'color: red;');
      return; 
  }

  let timerInterval;
  let expiryTimestamp; // Hält den ZIEL-Timestamp (in Sekunden)
  let lastDisplayedMinutes; 
  let isResetting = false; // Sperre, um Ping-Spam zu verhindern

  function redirectToLockscreen() {
   log('%c[ACTION] redirectToLockscreen() wird aufgerufen! Umleitung zu /lock.', 'color: red; font-weight: bold;');
   clearInterval(timerInterval); // Stoppe alle weiteren Timer
   window.location.href = '{{ route('lock') }}';
  }

  function displayTime(remainingSeconds) {
    let totalMinutes = Math.ceil(remainingSeconds / 60);
    
    // Nur aktualisieren, wenn sich die Minute ändert
    if (totalMinutes === lastDisplayedMinutes && remainingSeconds > 0) {
        return; 
    }
    
    log(`[DISPLAY] displayTime() wird aufgerufen. Verbleibend: ${remainingSeconds.toFixed(0)}s. Zeige ${totalMinutes}min an.`);
    lastDisplayedMinutes = totalMinutes;

    if (totalMinutes <= 0) { 
        totalMinutes = 1; // Falls wir im Puffer-Bereich sind, zeige 1 Min.
    }
    
    if (totalMinutes < 60) {
      timerElement.textContent = totalMinutes + ' min';
    } else {
      let hours = Math.floor(totalMinutes / 60);
      let minutes = totalMinutes % 60;
      minutes = minutes < 10 ? '0' + minutes : minutes;
      timerElement.textContent = hours + 'h ' + minutes + ' min';
    }
    
    // Farblogik
    if(totalMinutes < 5) { 
      log('[DISPLAY] Status: Warnung (< 5 min)');
      timerElement.classList.remove('badge-primary');
      timerElement.classList.add('badge-warning');
      if(timerTextElement) timerTextElement.textContent = 'Sitzung endet bald:';
    } else {
      log('[DISPLAY] Status: Normal (> 5 min)');
      timerElement.classList.remove('badge-warning');
      timerElement.classList.add('badge-primary');
      if(timerTextElement) timerTextElement.textContent = 'Sitzung endet in:';
    }
  }

  function checkTimer() {
    const nowTimestamp = Math.floor(Date.now() / 1000);
    
    // Log alle 30 Sekunden, um die Konsole nicht zu fluten
    if (nowTimestamp % 30 === 0) {
        log(`[CHECK] Timer-Prüfung... Verbleibend: ${(expiryTimestamp - puffer) - nowTimestamp}s`);
    }
    
    // PRÜFUNG: Ist die Zeit abgelaufen?
    if (nowTimestamp >= (expiryTimestamp - puffer)) {
      log(`[CHECK] Zeit abgelaufen! now: ${nowTimestamp}, expiry: ${expiryTimestamp} (mit Puffer)`);
      clearInterval(timerInterval);
      redirectToLockscreen();
      return;
    }
    
    const remainingSeconds = (expiryTimestamp - puffer) - nowTimestamp;
    displayTime(remainingSeconds);
  }

  // STARTPUNKT: Holt die "Wahrheit" vom Server
  function fetchSessionExpiry() {
    log('%c[FETCH] fetchSessionExpiry() startet...', 'color: blue;');
    clearInterval(timerInterval);

    $.ajax({
        url: '{{ route("api.session.expiry") }}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            expiryTimestamp = data.expiry_timestamp;
            const localTime = new Date(expiryTimestamp * 1000).toLocaleString();
            log(`%c[FETCH] Erfolg! Ziel-Timestamp: ${expiryTimestamp} (Lokal: ${localTime})`, 'color: green;');
            
            checkTimer(); // Einmal sofort anzeigen
            // Starte den sekundengenauen Prüfer
            timerInterval = setInterval(checkTimer, 1000); 
        },
        error: function(xhr) {
            log(`%c[FETCH] FEHLER!`, 'color: red;', xhr.responseText);
            console.error("Konnte Session-Ablaufzeit nicht vom Server laden.");
            timerElement.textContent = 'FEHLER';
            timerElement.classList.add('badge-danger');
        }
    });
  }

  // "ORDENTLICHER" RESET-TIMER
  function resetTimer() {
    if (isResetting) {
        log('[RESET] Reset-Anfrage ignoriert (Spam-Sperre aktiv).');
        return; 
    }
    isResetting = true;
    log('%c[RESET] resetTimer() startet... Pinge Server.', 'color: orange; font-weight: bold;');
    clearInterval(timerInterval);

    $.ajax({
        url: '{{ route("api.session.ping") }}',
        type: 'GET',
        dataType: 'json'
    }).done(function() {
        log('[RESET] Ping erfolgreich. Starte fetchSessionExpiry() neu.');
        // fetchSessionExpiry startet den Timer-Loop (setInterval) neu.
        fetchSessionExpiry();
    }).fail(function(xhr) {
        log(`%c[RESET] PING FEHLGESCHLAGEN!`, 'color: red;', xhr.responseText);
    }).always(function() {
        // Sperre nach 5 Sekunden wieder freigeben
        setTimeout(() => { 
            log('[RESET] Spam-Sperre aufgehoben.');
            isResetting = false; 
        }, 5000);
    });
  }
  
  // ===================================================================
  // === DER "WAKE UP" FIX GEGEN BACKGROUND THROTTLING ===
  // ===================================================================
  window.addEventListener('focus', function() {
      log('%c[FOCUS] Tab ist wieder im Fokus. Prüfe Zeit...', 'color: purple; font-weight: bold;');
      if (expiryTimestamp) { 
         checkTimer();
      } else {
         log('[FOCUS] Kein Ziel-Timestamp gefunden. Starte fetchSessionExpiry()');
         fetchSessionExpiry();
      }
  });
  // ===================================================================

  // Events, die den Timer zurücksetzen (und neu fetchen)
  $(window).on('mousedown keydown', resetTimer);
  
  // Timer initial starten
  fetchSessionExpiry();

 })();
</script>
@endif

@impersonating
    <div style="position: fixed; bottom: 0; width: 100%; z-index: 9999; background-color: #dc3545; color: white; text-align: center; padding: 10px; font-weight: bold;">
        Achtung: Du bist gerade als {{ auth()->user()->name }} eingeloggt.
        <a href="{{ route('impersonate.leave') }}" style="color: white; text-decoration: underline; margin-left: 20px;">Zurück zu meinem Account</a>
    </div>
@endImpersonating

@stack('scripts')
</body>
</html>