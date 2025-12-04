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
        /* Custom Public Styles */
        .public-wrapper {
            background-color: #f4f6f9;
        }
        .hero-header {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .hero-title {
            font-weight: 700;
            letter-spacing: 1px;
        }
        .hero-subtitle {
            font-weight: 300;
            opacity: 0.8;
            font-size: 1.2rem;
        }
        .content-container {
            max-width: 1140px; /* Standard Bootstrap Container */
            margin: 0 auto;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
    </style>
    @stack('styles')
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white shadow-sm">
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

      <!-- Right navbar links -->
      <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
        @auth
            <li class="nav-item">
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-user-shield mr-1"></i> Zum Dienst-Dashboard
                </a>
            </li>
        @else
            <li class="nav-item">
                <a href="{{ route('login') }}" class="nav-link">Login</a>
            </li>
        @endauth
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
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">Justizbehörde Hamburg</a>.</strong> Alle Rechte vorbehalten.
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