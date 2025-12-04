<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Öffentlich') | Hansestadt Hamburg</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Theme style (AdminLTE) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <style>
        /* Dark Mode Custom Public Styles */
        .public-wrapper {
            /* Hintergrund wird automatisch von AdminLTE Dark Mode gesetzt (#343a40) */
            background-color: transparent !important; 
        }
        /* Hero Header - Für mehr Kontrast im Dark Mode */
        .hero-header {
            /* Dunkler, kontrastreicher Blue-Gradient */
            background: linear-gradient(135deg, #0d47a1 0%, #1565c0 100%); 
            color: white;
            padding: 4rem 0;
            margin-bottom: 2rem;
            /* Dezenter Schatten im Dark Mode */
            box-shadow: 0 4px 10px rgba(0,0,0,0.5); 
        }
        .hero-title {
            font-weight: 700;
            letter-spacing: 1px;
        }
        .hero-subtitle {
            font-weight: 300;
            opacity: 0.9; 
            font-size: 1.2rem;
        }
        .content-container {
            max-width: 1140px;
            margin: 0 auto;
        }
        .card {
            border: none;
            /* Kartenschatten im Dark Mode dezenter */
            box-shadow: 0 0 10px rgba(0,0,0,0.4); 
            /* Hintergrundfarbe wird durch AdminLTE dark-mode gesetzt */
        }
        /* Akzentfarbe für den Universitäts-Icon im Dark Mode */
        .navbar-brand .text-navy {
            color: #42a5f5 !important; /* Helles Blau */
        }
    </style>
    @stack('styles')
</head>
<body class="hold-transition layout-top-nav dark-mode">
<div class="wrapper">

  <!-- Navbar - explizit 'navbar-dark' für besseren Kontrast im Dark Mode -->
  <nav class="main-header navbar navbar-expand-md navbar-dark shadow">
    <div class="container">
      <a href="{{ url('/') }}" class="navbar-brand">
        <!-- Logo hier einfügen falls vorhanden -->
        <i class="fas fa-university text-navy mr-2"></i>
        <span class="brand-text font-weight-light">Hansestadt <strong>Hamburg</strong></span>
      </a>

      <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse order-3" id="navbarCollapse">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a href="{{ route('laws.index') }}" class="nav-link {{ request()->routeIs('laws.*') ? 'active font-weight-bold' : '' }}">Gesetze</a>
          </li>
          <li class="nav-item">
            <a href="{{ route('catalog.index') }}" class="nav-link {{ request()->routeIs('catalog.*') ? 'active font-weight-bold' : '' }}">Bußgeldkatalog</a>
          </li>
        </ul>
      </div>

      <!-- Right navbar links (Leer, da Links in den Footer verschoben wurden) -->
      <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
        {{-- Alle Auth/Guest Links wurden in den Footer verschoben --}}
      </ul>
    </div>
  </nav>
  <!-- /.navbar -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper public-wrapper">
    @yield('content')
  </div>
  <!-- /.content-wrapper -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <div class="container">
        <div class="float-right d-none d-sm-inline">
            Offizielles Dokument
        </div>
        
        @auth
            <a href="{{ route('reports.index') }}" class="text-secondary mr-3 small">
                <i class="fas fa-user-shield mr-1"></i> Dienst-Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="text-secondary mr-3 small">Dienst-Login</a>
        @endauth
        
        <strong>Copyright &copy; {{ date('Y') }} <a href="#" class="text-primary">Justizbehörde Hamburg</a>.</strong> Alle Rechte vorbehalten.
    </div>
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@stack('scripts')
</body>
</html>