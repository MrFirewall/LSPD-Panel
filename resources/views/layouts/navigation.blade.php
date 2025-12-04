{{-- Design-Anpassungen speziell für die Sidebar --}}
<style>
    /* Header (z.B. EINSATZWESEN) dezenter gestalten */
    .nav-sidebar .nav-header {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255,255,255,0.4) !important;
        margin-top: 15px;
        padding-bottom: 5px;
        font-weight: 600;
    }
    
    /* Icons etwas aufhübschen */
    .nav-sidebar .nav-icon {
        width: 1.6rem;
        text-align: center;
        opacity: 0.75;
        transition: all 0.3s ease;
    }
    .nav-sidebar .nav-link:hover .nav-icon,
    .nav-sidebar .nav-link.active .nav-icon {
        opacity: 1;
        color: #fff;
        transform: translateX(2px);
        text-shadow: 0 0 10px rgba(255,255,255,0.3);
    }

    /* Sub-Menü Links (eingerückt & feinerer Active-State) */
    .nav-treeview > .nav-item > .nav-link {
        padding-left: 2.2rem;
        font-size: 0.9rem;
    }
    /* Sub-Menü Active State: Nur leichter Hintergrund + Border links */
    .nav-treeview > .nav-item > .nav-link.active {
        background-color: rgba(255,255,255,0.05) !important;
        box-shadow: none !important;
        border-left: 3px solid #6366f1; /* Indigo Accent passend zum Theme */
        border-radius: 0 4px 4px 0;
        color: #fff !important;
    }
</style>

@php
    /*
    |--------------------------------------------------------------------------
    | USER-BEREICH HELPER
    |--------------------------------------------------------------------------
    */
    
    // NEU: Für den Link zum Benachrichtigungs-Archiv
    $isNotificationsActive = Request::routeIs('notifications.*');

    // Dropdown: Ausbildung (User)
    $isAusbildungAnmeldungActive = Request::routeIs('forms.evaluations.modulAnmeldung', 'forms.evaluations.pruefungsAnmeldung');
    $isAusbildungUserActive = $isAusbildungAnmeldungActive;

    // Dropdown: Formulare (User)
    $isEvaluationsActive = Request::routeIs('forms.evaluations.azubi', 'forms.evaluations.praktikant', 'forms.evaluations.mitarbeiter', 'forms.evaluations.leitstelle');
    $isFormsUserActive = $isEvaluationsActive || Request::routeIs('vacations.create');

    /*
    |--------------------------------------------------------------------------
    | ADMIN-BEREICH HELPER
    |--------------------------------------------------------------------------
    */

    // Dropdown: Personalverwaltung (Admin)
    $isAdminPersonalActive = Request::routeIs('admin.users.*') || 
                             Request::routeIs('admin.vacations.*') || 
                             Request::routeIs('admin.roles.*');

    // Dropdown: Ausbildungsleitung (Admin)
    $isExamManagementActive = Request::routeIs('admin.exams.*') || Request::routeIs('admin.exams.attempts.*');
    $isAdminAusbildungActive = Request::routeIs('forms.evaluations.index') || 
                               Request::routeIs('modules.*') || 
                               $isExamManagementActive;

    // Dropdown: System & Konfiguration (Admin)
    $isNotificationRulesActive = Request::routeIs('admin.notification-rules.*');
    // Optimiert: admin.permissions.* (statt .index) und admin.logs.index (da nur index existiert)
    $isAdminSystemActive = Request::routeIs('admin.announcements.*') ||
                           Request::routeIs('admin.permissions.*') ||
                           Request::routeIs('admin.logs.index') ||
                           $isNotificationRulesActive;

@endphp

<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    
    {{-- ================================================================= --}}
    {{-- ALLGEMEINER BEREICH (USER)
    {{-- ================================================================= --}}
    
    <li class="nav-item">
        <a href="{{ route('dashboard') }}" class="nav-link {{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>
    
    @can('profile.view')
    <li class="nav-item">
        <a href="{{ route('profile.show') }}" class="nav-link {{ Request::routeIs('profile.show') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user"></i>
            <p>Profil</p>
        </a>
    </li>
    @endcan

    <li class="nav-item">
        <a href="{{ route('notifications.index') }}" class="nav-link {{ $isNotificationsActive ? 'active' : '' }}">
            <i class="nav-icon fas fa-bell"></i>
            <p>Benachrichtigungen</p>
        </a>
    </li>
    
    {{-- EINSATZWESEN GRUPPE --}}
    @canany(['reports.view', 'citizens.view'])
    <li class="nav-header">EINSATZWESEN</li>
    @can('reports.view')
    <li class="nav-item">
        <a href="{{ route('reports.index') }}" class="nav-link {{ Request::routeIs('reports.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-contract"></i> {{-- Icon geändert zu Akte --}}
            <p>Einsatzberichte</p>
        </a>
    </li>
    @endcan
     @can('citizens.view')
     <li class="nav-item">
        <a href="{{ route('citizens.index') }}" class="nav-link {{ Request::routeIs('citizens.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i> {{-- Icon geändert zu Users --}}
            <p>Patientenakten</p>
        </a>
    </li>
    @endcan
    @endcanany

    {{-- AUSBILDUNG GRUPPE (USER) --}}
    @can('training.view') 
    <li class="nav-item has-treeview {{ $isAusbildungUserActive ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isAusbildungUserActive ? 'active' : '' }}">
            <i class="nav-icon fas fa-graduation-cap"></i>
            <p>
                Meine Ausbildung
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            @can('evaluations.create') 
            <li class="nav-item"><a href="{{ route('forms.evaluations.modulAnmeldung') }}" class="nav-link {{ Request::routeIs('forms.evaluations.modulAnmeldung') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Modul-Anmeldung</p></a></li>
            <li class="nav-item"><a href="{{ route('forms.evaluations.pruefungsAnmeldung') }}" class="nav-link {{ Request::routeIs('forms.evaluations.pruefungsAnmeldung') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Prüfungs-Anmeldung</p></a></li>
            @endcan
        </ul>
    </li>
    @endcan

    {{-- FORMULARE GRUPPE (USER) --}}
    @canany(['evaluations.create', 'vacations.create'])
    <li class="nav-item has-treeview {{ $isFormsUserActive ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isFormsUserActive ? 'active' : '' }}">
            <i class="nav-icon fas fa-paste"></i> {{-- Icon geändert zu Paste --}}
            <p>
                Formulare & Anträge
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            
            {{-- 1. NESTED DROPDOWN: BEWERTUNGEN --}}
            @can('evaluations.create')
            <li class="nav-item has-treeview {{ $isEvaluationsActive ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isEvaluationsActive ? 'active' : '' }}">
                    <i class="far fa-star nav-icon"></i> {{-- Icon geändert zu Star --}}
                    <p>
                        Bewertungen
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item"><a href="{{ route('forms.evaluations.azubi') }}" class="nav-link {{ Request::routeIs('forms.evaluations.azubi') ? 'active' : '' }}"><i class="far fa-dot-circle nav-icon"></i><p>Azubibewertung</p></a></li>
                    <li class="nav-item"><a href="{{ route('forms.evaluations.praktikant') }}" class="nav-link {{ Request::routeIs('forms.evaluations.praktikant') ? 'active' : '' }}"><i class="far fa-dot-circle nav-icon"></i><p>Praktikantenbewertung</p></a></li>
                    <li class="nav-item"><a href="{{ route('forms.evaluations.mitarbeiter') }}" class="nav-link {{ Request::routeIs('forms.evaluations.mitarbeiter') ? 'active' : '' }}"><i class="far fa-dot-circle nav-icon"></i><p>Mitarbeiterbewertung</p></a></li>
                    <li class="nav-item"><a href="{{ route('forms.evaluations.leitstelle') }}" class="nav-link {{ Request::routeIs('forms.evaluations.leitstelle') ? 'active' : '' }}"><i class="far fa-dot-circle nav-icon"></i><p>Leitstellenbewertung</p></a></li>
                </ul>
            </li>
            @endcan

            {{-- 2. STANDALONE LINK: URLAUBSANTRAG --}}
            @can('vacations.create')
            <li class="nav-item">
                <a href="{{ route('vacations.create') }}" class="nav-link {{ Request::routeIs('vacations.create') ? 'active' : '' }}">
                    <i class="far fa-calendar-alt nav-icon"></i> {{-- Icon geändert --}}
                    <p>Urlaubsantrag</p>
                </a>
            </li>
            @endcan
        </ul>
    </li>
    @endcanany


    {{-- ================================================================= --}}
    {{-- ADMINISTRATIONS-BEREICH (ADMIN)
    {{-- ================================================================= --}}
    @can('admin.access') 
    <li class="nav-header">ADMINISTRATION</li>
    
        {{-- PERSONALVERWALTUNG (Personalabteilung) --}}
        @canany(['users.view', 'vacations.manage', 'roles.view'])
        <li class="nav-item has-treeview {{ $isAdminPersonalActive ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $isAdminPersonalActive ? 'active' : '' }}">
                <i class="nav-icon fas fa-id-card-alt"></i> {{-- Icon geändert --}}
                <p>Personalverwaltung<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @can('users.view')
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ Request::routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i><p>Mitarbeiter</p>
                    </a>
                </li>
                @endcan
                @can('vacations.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.vacations.index') }}" class="nav-link {{ Request::routeIs('admin.vacations.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i><p>Urlaubsanträge</p>
                    </a>
                </li>
                @endcan
                @can('roles.view')
                <li class="nav-item">
                    <a href="{{ route('admin.roles.index') }}" class="nav-link {{ Request::routeIs('admin.roles.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i><p>Rollen & Abteilungen</p>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- AUSBILDUNGSLEITUNG (Ausbildungsabteilung) --}}
        @canany(['evaluations.view.all', 'exams.manage', 'training.view']) 
        <li class="nav-item has-treeview {{ $isAdminAusbildungActive ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $isAdminAusbildungActive ? 'active' : '' }}">
                <i class="nav-icon fas fa-chalkboard-teacher"></i> {{-- Icon geändert --}}
                <p>Ausbildungsleitung<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @can('evaluations.view.all')
                <li class="nav-item">
                    <a href="{{ route('forms.evaluations.index') }}" class="nav-link {{ Request::routeIs('forms.evaluations.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i><p>Eing. Formulare</p>
                    </a>
                </li>
                @endcan
                
                @can('training.view') 
                <li class="nav-item">
                    <a href="{{ route('modules.index') }}" class="nav-link {{ Request::routeIs('modules.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Ausbildungsmodule</p>
                    </a>
                </li>
                @endcan

                @can('exams.manage')
                <li class="nav-item has-treeview {{ $isExamManagementActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $isExamManagementActive ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Prüfungsmanagement<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.exams.index') }}" class="nav-link {{ Request::routeIs('admin.exams.index', 'admin.exams.show', 'admin.exams.create', 'admin.exams.edit') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i><p>Prüfungsdefinitionen</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.exams.attempts.index') }}" class="nav-link {{ Request::routeIs('admin.exams.attempts.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i><p>Prüfungsversuche</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- SYSTEM & KONFIGURATION (Rechtsabteilung / IT) --}}
        @canany(['announcements.view', 'permissions.view', 'logs.view', 'notification.rules.manage'])
        <li class="nav-item has-treeview {{ $isAdminSystemActive ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $isAdminSystemActive ? 'active' : '' }}">
                <i class="nav-icon fas fa-server"></i> {{-- Icon geändert --}}
                <p>System & Konfiguration<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @can('announcements.view')
                <li class="nav-item">
                    <a href="{{ route('admin.announcements.index') }}" class="nav-link {{ Request::routeIs('admin.announcements.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i><p>Ankündigungen</p>
                    </a>
                </li>
                @endcan
                @can('permissions.view')
                <li class="nav-item">
                    <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ Request::routeIs('admin.permissions.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i><p>Berechtigungen</p>
                    </a>
                </li>
                @endcan
                @can('logs.view')
                <li class="nav-item">
                    <a href="{{ route('admin.logs.index') }}" class="nav-link {{ Request::routeIs('admin.logs.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i><p>Aktivitäten-Log</p>
                    </a>
                </li>
                @endcan
                @can('notification.rules.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.notification-rules.index') }}" class="nav-link {{ $isNotificationRulesActive ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i><p>Benachrichtigungsregeln</p>
                    </a>
                </li>
                @endcan
                @can('discord.settings')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.discord.index') ? 'active' : '' }}" 
                    href="{{ route('admin.discord.index') }}">
                        <i class="fab fa-discord nav-icon"></i>
                        <p>Discord Einstellungen</p>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

    @endcan
</ul>