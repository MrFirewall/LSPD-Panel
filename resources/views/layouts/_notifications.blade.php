{{-- Style direkt für das Dropdown (kann auch in CSS Datei) --}}
<style>
    /* Begrenzt die Höhe und erlaubt Scrollen */
    .notification-list-scroll {
        max-height: 350px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    /* Verschönert die Scrollbar (optional, wirkt moderner) */
    .notification-list-scroll::-webkit-scrollbar { width: 6px; }
    .notification-list-scroll::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.2); border-radius: 3px; }

    /* Text-Wrap Logik */
    .notification-content {
        white-space: normal;
        overflow-wrap: break-word;
        line-height: 1.3;
    }
</style>

{{-- 1. HEADER --}}
<span class="dropdown-item dropdown-header d-flex justify-content-between align-items-center">
    <span>{{ $totalCount ?? 0 }} Benachrichtigungen</span>
    
    @if(($totalCount ?? 0) > 0)
        <form action="{{ route('notifications.markAllRead') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-xs btn-outline-success" title="Alle als gelesen markieren">
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
        
            // Farbe basierend auf Gruppe (Optional, passt sich AdminLTE an)
            $iconColor = match($group['group_title']) {
                'System' => 'text-danger',
                'Anträge' => 'text-warning',
                default => 'text-info'
            };
        @endphp

        <div class="dropdown-divider"></div>

        {{-- GRUPPEN TITEL --}}
        {{-- WICHTIG: onclick="event.stopPropagation()" verhindert, dass das Dropdown schließt beim Klicken --}}
        <a href="#{{ $collapseId }}" 
           class="dropdown-item font-weight-bold bg-light d-flex justify-content-between align-items-center"
           data-toggle="collapse" 
           role="button" 
           aria-expanded="false"
           onclick="event.stopPropagation();">
            <span>
                <i class="{{ $group['group_icon'] }} {{ $iconColor }} mr-2"></i> 
                {{ $group['group_title'] }}
            </span>
            <i class="fas fa-chevron-down text-muted text-xs"></i>
        </a>

        {{-- ITEMS IN GRUPPE --}}
        <div class="collapse show" id="{{ $collapseId }}"> {{-- 'show' entfernen, wenn standardmäßig zugeklappt sein soll --}}
            @foreach ($group['items'] as $notification)
                <div class="dropdown-divider my-0"></div>

                <form action="{{ route('notifications.markAsRead', $notification['id']) }}" method="POST" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="dropdown-item p-2 d-flex align-items-start text-left bg-white" style="border: none; width: 100%;">
                        
                        {{-- Icon --}}
                        <div class="mr-2 mt-1">
                             <i class="far fa-circle text-xs text-secondary"></i>
                        </div>

                        {{-- Text Content --}}
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="notification-content text-sm text-dark">
                                {{ $notification['text'] ?? '...' }}
                            </div>
                            <small class="text-muted float-right mt-1">
                                <i class="far fa-clock mr-1"></i> {{ $notification['time'] }}
                            </small>
                        </div>
                    </button>
                </form>
            @endforeach
        </div>

    @empty
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item dropdown-footer text-center text-muted">
            Keine neuen Meldungen
        </a>
    @endforelse

</div>

{{-- 3. FOOTER --}}
<div class="dropdown-divider"></div>
<a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer text-center">
    <strong>Alle Benachrichtigungen anzeigen</strong>
</a>