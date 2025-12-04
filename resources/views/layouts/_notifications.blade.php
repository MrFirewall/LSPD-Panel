{{-- Style Block --}}
<style>
    /* Erzwinge Dark Mode Styles für das Dropdown */
    .notification-dropdown-content {
        background-color: #2d3748 !important; /* Card Dark Color */
        color: #e2e8f0 !important;
        border-radius: 12px !important; /* Global Radius */
        overflow: hidden; /* Damit Ecken nicht von Kindern überlappt werden */
        border: 1px solid rgba(255,255,255,0.08); /* Glass Border */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
    }

    .notification-list-scroll {
        max-height: 350px;
        overflow-y: auto;
        overflow-x: hidden;
        background-color: #2d3748;
    }
    
    /* Scrollbar */
    .notification-list-scroll::-webkit-scrollbar { width: 4px; }
    .notification-list-scroll::-webkit-scrollbar-track { background: #1a202c; }
    .notification-list-scroll::-webkit-scrollbar-thumb { background-color: #4a5568; border-radius: 2px; }

    .notification-content {
        white-space: normal;
        overflow-wrap: break-word;
        line-height: 1.4;
        color: #e2e8f0; 
    }
    
    /* Notification Row Styling */
    .notification-row {
        border-bottom: 1px solid rgba(255,255,255,0.08) !important;
        transition: background-color 0.2s;
        /* Kein Margin, flache Liste */
    }
    .notification-row:hover {
        background-color: rgba(255,255,255,0.05) !important;
    }
    .notification-row:last-child {
        border-bottom: none !important;
    }

    /* Buttons */
    .btn-text-wrapper {
        text-align: left;
        width: 100%;
        padding: 0;
        border: none;
        background: transparent;
        color: inherit;
    }
    
    /* Header Styles */
    .custom-header {
        background: linear-gradient(135deg, #1f1c2c 0%, #2d3748 100%) !important;
        color: white !important;
        border-bottom: 1px solid rgba(255,255,255,0.1) !important;
    }

    /* Mark Read Button */
    .mark-read-ajax-btn {
        color: #718096 !important;
        border-left: 1px solid rgba(255,255,255,0.05);
    }
    .mark-read-ajax-btn:hover {
        color: #4ade80 !important; /* Green */
        background-color: rgba(74, 222, 128, 0.1) !important;
    }

    /* Footer */
    .custom-footer {
        background-color: #1a202c !important;
        border-top: 1px solid rgba(255,255,255,0.1) !important;
    }
</style>

{{-- Wrapper um alles --}}
<div class="notification-dropdown-content">

    {{-- 1. HEADER --}}
    <span class="dropdown-item dropdown-header d-flex justify-content-between align-items-center custom-header py-3 px-3">
        <span class="font-weight-bold" style="font-size: 1rem;">
            <i class="far fa-bell mr-2"></i> {{ $totalCount ?? 0 }} Benachrichtigungen
        </span>
        
        @if(($totalCount ?? 0) > 0)
            <form action="{{ route('notifications.markAllRead') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-xs btn-outline-light rounded-pill px-3" title="Alle als gelesen markieren">
                    Alle lesen
                </button>
            </form>
        @endif
    </span>

    {{-- 2. SCROLLBARE LISTE (FLACH) --}}
    <div class="notification-list-scroll">

        {{-- Wir iterieren zwar noch durch die Gruppen-Struktur (falls Controller diese liefert), rendern aber keine Header --}}
        @forelse ($groupedNotifications as $group)
            @foreach ($group['items'] as $notification)
                
                <div class="d-flex notification-row" id="notif-row-{{ $notification['id'] }}">

                    {{-- Linker Teil: Text & Zeit --}}
                    <form action="{{ route('notifications.markAsRead', $notification['id']) }}" method="POST" class="flex-grow-1">
                        @csrf
                        <input type="hidden" name="redirect_to_target" value="1">
                        
                        <button type="submit" class="btn-text-wrapper p-3 h-100">
                            <div class="d-flex flex-column">
                                <span class="notification-content text-sm">
                                    {{-- Optional: Kategorie-Icon dezent anzeigen --}}
                                    <i class="{{ $group['group_icon'] ?? 'fas fa-info-circle' }} mr-1 opacity-50" style="font-size: 0.8em;"></i>
                                    {{ $notification['text'] ?? '...' }}
                                </span>
                                <small class="text-muted mt-2 d-flex align-items-center" style="color: #718096 !important;">
                                    <i class="far fa-clock mr-1 text-xs"></i> {{ $notification['time'] }}
                                </small>
                            </div>
                        </button>
                    </form>
                    
                    {{-- Rechter Teil: Check-Button --}}
                    <button type="button" 
                            class="btn btn-link mark-read-ajax-btn d-flex align-items-center justify-content-center px-3" 
                            style="text-decoration: none; border-radius: 0; min-width: 50px;" 
                            title="Gelesen"
                            data-url="{{ route('notifications.markAsRead', $notification['id']) }}"
                            data-id="{{ $notification['id'] }}">
                        <i class="fas fa-check"></i>
                    </button>

                </div>

            @endforeach
        @empty
            <div class="p-5 text-center text-muted">
                <i class="far fa-bell-slash mb-3" style="font-size: 2.5rem; opacity: 0.3;"></i><br>
                <span style="opacity: 0.7;">Keine neuen Meldungen</span>
            </div>
        @endforelse

    </div>

    {{-- 3. FOOTER --}}
    <div class="custom-footer">
        <a href="{{ route('notifications.index') }}" class="dropdown-item text-center py-2 text-primary font-weight-bold bg-transparent">
            Archiv ansehen
        </a>
        <div class="text-center py-2 bg-transparent border-top" style="border-color: rgba(255,255,255,0.05) !important;">
            <button type="button" id="enable-push" class="btn btn-xs btn-info shadow-none rounded-pill px-3" style="display: none;">
                <i class="fas fa-bell mr-1"></i> Push aktivieren
            </button>
            <button type="button" id="disable-push" class="btn btn-xs btn-secondary shadow-none rounded-pill px-3" style="display: none;">
                <i class="fas fa-bell-slash mr-1"></i> Push aus
            </button>
        </div>
    </div>

</div>