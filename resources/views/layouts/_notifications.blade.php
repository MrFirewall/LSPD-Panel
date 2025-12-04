{{-- Style Block --}}
<style>
    .notification-list-scroll {
        max-height: 350px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    /* Dark Mode Scrollbar */
    .notification-list-scroll::-webkit-scrollbar { width: 4px; }
    .notification-list-scroll::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); }
    .notification-list-scroll::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.2); border-radius: 2px; }

    .notification-content {
        white-space: normal;
        overflow-wrap: break-word;
        line-height: 1.4;
        color: var(--text-light, #e2e8f0); 
    }
    
    /* Hover Effekt für die ganze Zeile */
    .notification-row:hover {
        background-color: rgba(255,255,255,0.03);
    }

    .btn-text-wrapper {
        text-align: left;
        width: 100%;
        padding: 0;
        border: none;
        background: transparent;
        color: inherit;
    }
    
    /* Border Colors an das Theme anpassen */
    .border-bottom, .border-right, .border-top {
        border-color: var(--glass-border, rgba(255,255,255,0.08)) !important;
    }
    
    /* Text Anpassungen für Dark Mode */
    .dropdown-header { color: rgba(255,255,255,0.8) !important; }
    .text-muted { color: rgba(255,255,255,0.5) !important; }
    
    .mark-read-ajax-btn:hover {
        color: #4ade80 !important; /* Helles Grün */
        background-color: rgba(74, 222, 128, 0.1);
    }
</style>

{{-- 1. HEADER --}}
<span class="dropdown-item dropdown-header d-flex justify-content-between align-items-center border-bottom" style="background-color: rgba(0,0,0,0.1);">
    <span class="font-weight-bold text-white">{{ $totalCount ?? 0 }} Benachrichtigungen</span>
    
    @if(($totalCount ?? 0) > 0)
        <form action="{{ route('notifications.markAllRead') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-xs btn-outline-info" title="Alle als gelesen markieren">
                Alle lesen
            </button>
        </form>
    @endif
</span>

{{-- 2. SCROLLBARE LISTE --}}
<div class="notification-list-scroll">

    @forelse ($groupedNotifications as $group)
        @php
            $collapseId = 'group-collapse-' . $loop->index;
            // Farben für Dark Mode etwas heller/leuchtender wählen
            $iconColor = match($group['group_title']) {
                'System' => 'text-danger', // AdminLTE rot ist meist ok
                'Anträge' => 'text-warning',
                default => 'text-info'
            };
        @endphp

        {{-- GRUPPEN TITEL --}}
        <a href="#" 
           class="dropdown-item dropdown-header font-weight-bold d-flex justify-content-between align-items-center border-bottom"
           style="background-color: rgba(255,255,255,0.02);"
           onclick="event.preventDefault(); event.stopPropagation(); $('#{{ $collapseId }}').collapse('toggle'); return false;">
            <span>
                <i class="{{ $group['group_icon'] }} {{ $iconColor }} mr-2"></i> 
                {{ $group['group_title'] }}
            </span>
            <i class="fas fa-chevron-down text-xs opacity-50"></i>
        </a>

        {{-- ITEMS IN GRUPPE --}}
        <div class="collapse show" id="{{ $collapseId }}"> {{-- Standardmäßig aufgeklappt ('show') ist meist userfreundlicher --}}
            @foreach ($group['items'] as $notification)
                
                <div class="d-flex border-bottom notification-row" id="notif-row-{{ $notification['id'] }}">

                    <form action="{{ route('notifications.markAsRead', $notification['id']) }}" method="POST" class="flex-grow-1">
                        @csrf
                        <input type="hidden" name="redirect_to_target" value="1">
                        
                        <button type="submit" class="btn-text-wrapper p-3 h-100">
                            <div class="d-flex flex-column">
                                <span class="notification-content text-sm">
                                    {{ $notification['text'] ?? '...' }}
                                </span>
                                <small class="text-muted mt-2 d-flex align-items-center">
                                    <i class="far fa-clock mr-1 text-xs"></i> {{ $notification['time'] }}
                                </small>
                            </div>
                        </button>
                    </form>
                    
                    <button type="button" 
                            class="btn btn-link text-muted mark-read-ajax-btn d-flex align-items-center justify-content-center px-3 border-right" 
                            style="text-decoration: none; border-radius: 0; min-width: 50px;" 
                            title="Als gelesen markieren"
                            data-url="{{ route('notifications.markAsRead', $notification['id']) }}"
                            data-id="{{ $notification['id'] }}">
                        <i class="fas fa-check"></i>
                    </button>

                </div>

            @endforeach
        </div>

    @empty
        <div class="p-5 text-center text-muted">
            <i class="far fa-bell-slash mb-3" style="font-size: 2.5rem; opacity: 0.3;"></i><br>
            <span style="opacity: 0.7;">Keine neuen Meldungen</span>
        </div>
    @endforelse

</div>

{{-- 3. FOOTER --}}
<div class="dropdown-footer border-top p-0">
    <a href="{{ route('notifications.index') }}" class="dropdown-item text-center py-2 text-primary font-weight-bold" style="background-color: rgba(0,0,0,0.1);">
        Alle anzeigen
    </a>
</div>
<div class="dropdown-footer border-top p-0">
    <div class="text-center py-2 bg-transparent">
        <button type="button" id="enable-push" class="btn btn-xs btn-info shadow-none" style="display: none;">
            <i class="fas fa-bell mr-1"></i> Push aktivieren
        </button>
        <button type="button" id="disable-push" class="btn btn-xs btn-secondary shadow-none" style="display: none;">
            <i class="fas fa-bell-slash mr-1"></i> Push deaktivieren
        </button>
    </div>
</div>