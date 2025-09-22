<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Gestion des Intérêts Moratoires')</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('storage/Icône_financière.png') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.3/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.0/css/buttons.dataTables.css" />

    <!-- Custom CSS -->
    <style>
    :root {
        --primary-color: #2563eb;
        --primary-dark: #1d4ed8;
        --secondary-color: #64748b;
        --success-color: #059669;
        --warning-color: #d97706;
        --danger-color: #dc2626;
        --light-bg: #f8fafc;
        --dark-bg: #0f172a;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        line-height: 1.6;
    }

    /* Modern Navbar */
    .navbar-custom {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        padding: 1rem 0;
        transition: all 0.3s ease;
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--primary-color) !important;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-brand i {
        font-size: 1.8rem;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .nav-link {
        color: var(--secondary-color) !important;
        font-weight: 500;
        padding: 0.75rem 1.25rem !important;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        position: relative;
        margin: 0 0.25rem;
    }

    .nav-link:hover {
        color: var(--primary-color) !important;
        background: rgba(37, 99, 235, 0.1);
        transform: translateY(-2px);
    }

    .nav-link.active {
        color: white !important;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        box-shadow: var(--card-shadow);
    }

    /* Main Content Area */
    .main-content {
        background: var(--light-bg);
        min-height: calc(100vh - 100px);
        border-radius: 2rem 2rem 0 0;
        margin-top: 2rem;
        padding: 2rem 0;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
    }

    /* Card Enhancements */
    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        background: white;
    }

    .card:hover {
        box-shadow: var(--card-shadow-hover);
        transform: translateY(-4px);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
        border: none;
    }

    .card-body {
        padding: 2rem;
    }

    /* Button Enhancements */
    .btn {
        border-radius: 0.75rem;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-color), #047857);
        box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning-color), #b45309);
        box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #b91c1c);
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    }

    /* Form Enhancements */
    .form-control,
    .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        transform: translateY(-1px);
    }

    .form-label {
        font-weight: 600;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    /* Table Enhancements */
    .table {
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        background: white;
    }

    .table thead th {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        font-weight: 600;
        border: none;
        padding: 1rem;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: rgba(37, 99, 235, 0.05);
        transform: scale(1.01);
    }

    /* Alert Enhancements */
    .alert {
        border: none;
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        box-shadow: var(--card-shadow);
    }

    /* Loading Animation */
    .loading {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .main-content {
            margin-top: 1rem;
            border-radius: 1rem 1rem 0 0;
            padding: 1rem 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        .navbar-brand {
            font-size: 1.25rem;
        }
    }

    /* Smooth Scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--primary-dark);
    }

    /* User Authentication Styles */
    .user-dropdown {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem !important;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    .user-dropdown:hover {
        background: rgba(37, 99, 235, 0.1);
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.875rem;
    }

    .user-name {
        font-weight: 500;
        color: var(--secondary-color);
    }

    .user-menu {
        border: none;
        border-radius: 1rem;
        box-shadow: var(--card-shadow);
        padding: 0.5rem;
        margin-top: 0.5rem;
        min-width: 200px;
    }

    .user-menu .dropdown-item {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        color: var(--secondary-color);
        font-weight: 500;
    }

    .user-menu .dropdown-item:hover {
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary-color);
        transform: translateX(4px);
    }

    .logout-btn {
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        color: var(--danger-color) !important;
    }

    .logout-btn:hover {
        background: rgba(220, 38, 38, 0.1) !important;
        color: var(--danger-color) !important;
    }

    .dropdown-divider {
        margin: 0.5rem 0;
        border-color: #e2e8f0;
    }

    /* Mobile responsive for user menu */
    @media (max-width: 768px) {
        .user-dropdown {
            justify-content: center;
            padding: 0.75rem 1rem !important;
        }

        .user-name {
            display: none;
        }

        .user-menu {
            min-width: 180px;
            right: 1rem !important;
            left: auto !important;
        }
    }
    </style>

    @livewireStyles
</head>

<body>
    <!-- Enhanced Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-chart-line"></i>
                Intérêts Moratoires
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clients') ? 'active' : '' }}"
                            href="{{ route('clients') }}">
                            <i class="fas fa-users me-2"></i>
                            Gestion Clients
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('factures.creer') ? 'active' : '' }}"
                            href="{{ route('factures.creer') }}">
                            <i class="fas fa-plus-circle me-2"></i>
                            Créer Facture
                        </a>
                    </li> --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('releves.creer') ? 'active' : '' }}"
                            href="{{ route('releves.creer') }}">
                            <i class="fas fa-plus-circle me-2"></i>
                            Créer Releve
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('releves') ? 'active' : '' }}"
                            href="{{ route('releves') }}">
                            <i class="fas fa-file-invoice me-2"></i>
                            Liste Releves
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('factures.tableau') ? 'active' : '' }}"
                            href="{{ route('factures.tableau') }}">
                            <i class="fas fa-table me-2"></i>
                            Tableau avec Filtres
                        </a>
                    </li> -->
                </ul>

                <!-- Authentication Section -->
                @auth
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-dropdown" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="user-name">{{ Auth::user()->name ?? 'Utilisateur' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end user-menu" aria-labelledby="navbarDropdown">
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user-circle me-2"></i>
                                    Mon Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cog me-2"></i>
                                    Paramètres
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item logout-btn">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        Se déconnecter
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
                @else
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Se connecter
                        </a>
                    </li>
                </ul>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content" style="margin-top: 80px;">
        <div class="container">
            {{ $slot }}
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.3.3/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <!-- Libraries for Exporting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    @livewireScripts
    <!-- Custom JavaScript -->

</body>

</html>