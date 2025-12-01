{{-- Style Block --}}
<style>
    .notification-list-scroll {
        max-height: 350px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .notification-list-scroll::-webkit-scrollbar { width: 4px; }
    .notification-list-scroll::-webkit-scrollbar-thumb { background-color: rgba(128,128,128,0.3); border-radius: 2px; }

    .notification-content {
        white-space: normal;
        overflow-wrap: break-word;
        line-height: 1.3;
        color: inherit; 
    }
    
    .mark-read-btn:hover {
        color: #28a745 !important; 
        background-color: rgba(40, 167, 69, 0.1);
    }

    .btn-text-wrapper {
        text-align: left;
        width: 100%;
        padding: 0;
        border: none;
        background: transparent;
        color: inherit;
    }
    .btn-text-wrapper:hover { background-color: rgba(0,0,0,0.05); }
    .dark-mode .btn-text-wrapper:hover { background-color: rgba(255,255,255,0.05); }
</style>

{{-- 1. HEADER --}}
<span class="dropdown-item dropdown-header d-flex justify-content-between align-items-center border-bottom">
    <span class="font-weight-bold">{{ $totalCount ?? 0 }} Benachrichtigungen</span>
    
    @if(($totalCount ?? 0) > 0)
        <form action="{{ route('notifications.markAllRead') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-xs btn-outline-primary" title="Alle als gelesen markieren">
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
            $iconColor = match($group['group_title']) {
                'System' => 'text-danger',
                'Anträge' => 'text-warning',
                default => 'text-info'
            };
        @endphp

        {{-- GRUPPEN TITEL (Bleibt gleich) --}}
        <a href="#" 
           class="dropdown-item dropdown-header font-weight-bold d-flex justify-content-between align-items-center border-bottom bg-light"
           onclick="event.preventDefault(); event.stopPropagation(); $('#{{ $collapseId }}').collapse('toggle'); return false;">
            <span>
                <i class="{{ $group['group_icon'] }} {{ $iconColor }} mr-2"></i> 
                {{ $group['group_title'] }}
            </span>
            <i class="fas fa-chevron-down text-xs opacity-50"></i>
        </a>

        {{-- ITEMS IN GRUPPE --}}
        <div class="collapse" id="{{ $collapseId }}">
            @foreach ($group['items'] as $notification)
                
                {{-- WICHTIG: Klasse 'notification-row' hinzugefügt --}}
                <div class="d-flex border-bottom bg-white notification-row" id="notif-row-{{ $notification['id'] }}">
                    
                    {{-- A) BUTTON: AJAX MARK AS READ --}}
                    {{-- Kein Formular mehr! Nur ein Button mit data-Attributen --}}
                    <button type="button" 
                            class="btn btn-link text-muted mark-read-ajax-btn d-flex align-items-center justify-content-center px-3 border-right h-100" 
                            style="text-decoration: none; border-radius: 0;" 
                            title="Als gelesen markieren"
                            data-url="{{ route('notifications.markAsRead', $notification['id']) }}"
                            data-id="{{ $notification['id'] }}">
                        <i class="fas fa-check"></i>
                    </button>

                    {{-- B) BUTTON: TEXT KLICKEN (Redirect bleibt wie gehabt) --}}
                    <form action="{{ route('notifications.markAsRead', $notification['id']) }}" method="POST" class="flex-grow-1">
                        @csrf
                        <input type="hidden" name="redirect_to_target" value="1">
                        
                        <button type="submit" class="btn-text-wrapper p-2 h-100">
                            <div class="d-flex flex-column">
                                <span class="notification-content text-sm">
                                    {{ $notification['text'] ?? '...' }}
                                </span>
                                <small class="text-muted mt-1">
                                    <i class="far fa-clock mr-1"></i> {{ $notification['time'] }}
                                </small>
                            </div>
                        </button>
                    </form>
                </div>

            @endforeach
        </div>

    @empty
        <div class="p-4 text-center text-muted">
            <i class="far fa-bell-slash mb-2" style="font-size: 2rem;"></i><br>
            Keine neuen Meldungen
        </div>
    @endforelse

</div>

{{-- 3. FOOTER --}}
<a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer text-center border-top">
    Alle Benachrichtigungen anzeigen
</a>