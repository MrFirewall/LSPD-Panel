{{-- Style Block --}}
<style>
    /* Begrenzt die Höhe und erlaubt Scrollen */
    .notification-list-scroll {
        max-height: 350px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Scrollbar Styling */
    .notification-list-scroll::-webkit-scrollbar { width: 5px; }
    .notification-list-scroll::-webkit-scrollbar-track { background: transparent; }
    .notification-list-scroll::-webkit-scrollbar-thumb { background-color: rgba(150,150,150,0.3); border-radius: 3px; }

    /* Text-Wrap Logik */
    .notification-content {
        white-space: normal;
        overflow-wrap: break-word;
        line-height: 1.3;
        color: inherit; /* WICHTIG für Dark Mode */
    }
    
    /* Hover-Effekt für den Haken-Button */
    .mark-read-btn:hover {
        color: #28a745 !important; /* Grün beim Hover */
        background-color: rgba(40, 167, 69, 0.1);
    }
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
            
            // Icon Farbe (angepasst an Bootstrap Standard)
            $iconColor = match($group['group_title']) {
                'System' => 'text-danger',
                'Anträge' => 'text-warning',
                default => 'text-info'
            };
        @endphp

        {{-- GRUPPEN TITEL --}}
        {{-- Wir nutzen dropdown-header für Styling, aber machen es klickbar --}}
        <a href="#{{ $collapseId }}" 
           class="dropdown-item dropdown-header font-weight-bold d-flex justify-content-between align-items-center border-bottom"
           data-toggle="collapse" 
           role="button" 
           aria-expanded="true" {{-- Standardmäßig aufgeklappt --}}
           onclick="event.stopPropagation();">
            <span>
                <i class="{{ $group['group_icon'] }} {{ $iconColor }} mr-2"></i> 
                {{ $group['group_title'] }}
            </span>
            <i class="fas fa-chevron-down text-xs opacity-50"></i>
        </a>

        {{-- ITEMS IN GRUPPE --}}
        <div class="collapse show" id="{{ $collapseId }}">
            @foreach ($group['items'] as $notification)
                
                {{-- Container für die Zweiteilung (Flexbox) --}}
                <div class="dropdown-item p-0 d-flex border-bottom align-items-stretch">
                    
                    {{-- TEIL A: Button zum NUR als gelesen markieren (Linke Seite) --}}
                    <form action="{{ route('notifications.markAsRead', $notification['id']) }}" method="POST" class="d-flex">
                        @csrf
                        {{-- Kein Redirect Input -> Controller bleibt auf der Seite --}}
                        <button type="submit" class="btn btn-link text-muted mark-read-btn d-flex align-items-center justify-content-center px-3 border-right h-100" style="text-decoration: none;" title="Als gelesen markieren">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>

                    {{-- TEIL B: Der Text-Inhalt (Rechte Seite) -> Redirect + Mark Read --}}
                    <form action="{{ route('notifications.markAsRead', $notification['id']) }}" method="POST" class="flex-grow-1">
                        @csrf
                        {{-- WICHTIG: Dieses Feld muss im Controller abgefragt werden für den Redirect! --}}
                        <input type="hidden" name="redirect_to_target" value="1">
                        
                        <button type="submit" class="btn btn-link text-left w-100 h-100 p-2 d-block" style="text-decoration: none; color: inherit;">
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