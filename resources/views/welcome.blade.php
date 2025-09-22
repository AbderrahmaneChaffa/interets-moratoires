<x-app-layout>
    @section('title', 'Accueil - Gestion des Intérêts Moratoires')

    <!-- Hero Section -->
    <div class="hero-section mb-5">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title mb-4">
                        <span class="gradient-text">Système de Gestion</span><br>
                        des Intérêts Moratoires
                    </h1>
                    <p class="hero-subtitle mb-4">
                        Gérez efficacement vos clients, factures et calculs d'intérêts moratoires avec notre plateforme moderne et intuitive.
                    </p>
                    <div class="hero-stats d-flex gap-4 mb-4">
                        <div class="stat-item">
                            <div class="stat-number">{{ $totalClients ?? '0' }}</div>
                            <div class="stat-label">Clients</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ $totalReleves ?? '0' }}</div>
                            <div class="stat-label">Relevés</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ $totalFacturesInReleves ?? '0' }}</div>
                            <div class="stat-label">Factures dans relevés</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ number_format($totalInterets ?? 0, 2) }} DA</div>
                            <div class="stat-label">Intérêts totaux</div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="{{ route('releves.creer') }}" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-plus-circle me-2"></i>
                            Créer un Relevé
                        </a>
                        <a href="{{ route('releves') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-list me-2"></i>
                            Voir les Relevés
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image">
                    <div class="floating-card">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Cards -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="feature-card h-100">
                <div class="feature-icon bg-primary">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div class="feature-content">
                    <h5 class="feature-title">Créer Relevé</h5>
                    <p class="feature-description">
                        Créez rapidement de nouveaux relevés avec factures et calcul automatique des intérêts moratoires.
                    </p>
                    <div class="feature-stats mb-3">
                        <small class="text-muted">
                            <i class="fas fa-calculator me-1"></i>
                            Calcul automatique des intérêts
                        </small>
                    </div>
                    <a href="{{ route('releves.creer') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>
                        Créer
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="feature-card h-100">
                <div class="feature-icon bg-success">
                    <i class="fas fa-users"></i>
                </div>
                <div class="feature-content">
                    <h5 class="feature-title">Gestion Clients</h5>
                    <p class="feature-description">
                        Ajoutez, modifiez et gérez vos clients en toute simplicité avec notre interface intuitive.
                    </p>
                    <div class="feature-stats mb-3">
                        <small class="text-muted">
                            <i class="fas fa-user-plus me-1"></i>
                            Dernière création: {{ $lastClientDate ?? 'Aucune' }}
                        </small>
                    </div>
                    <a href="{{ route('clients') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-arrow-right me-1"></i>
                        Accéder
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="feature-card h-100">
                <div class="feature-icon bg-info">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="feature-content">
                    <h5 class="feature-title">Gérer Factures</h5>
                    <p class="feature-description">
                        Consultez et gérez les factures existantes avec upload PDF et gestion des intérêts.
                    </p>
                    <div class="feature-stats mb-3">
                        <small class="text-muted">
                            <i class="fas fa-list me-1"></i>
                            Vue d'ensemble complète
                        </small>
                    </div>
                    <a href="{{ route('factures') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-arrow-right me-1"></i>
                        Consulter
                    </a>
                </div>
            </div>
        </div>

        <!-- <div class="col-lg-3 col-md-6">
            <div class="feature-card h-100">
                <div class="feature-icon bg-warning">
                    <i class="fas fa-table"></i>
                </div>
                <div class="feature-content">
                    <h5 class="feature-title">Tableau & Filtres</h5>
                    <p class="feature-description">
                        Analysez vos données avec des filtres avancés et exportez en PDF ou Excel.
                    </p>
                    <div class="feature-stats mb-3">
                        <small class="text-muted">
                            <i class="fas fa-download me-1"></i>
                            Export PDF & Excel disponible
                        </small>
                    </div>
                    <a href="{{ route('factures.tableau') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-filter me-1"></i>
                        Analyser
                    </a>
                </div>
            </div>
        </div> -->
    </div>

    <!-- Recent Activity Section -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card activity-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Activité Récente
                    </h5>
                    <a href="{{ route('factures') }}" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentActivities) && count($recentActivities) > 0)
                        <div class="activity-timeline">
                            @foreach($recentActivities as $activity)
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-{{ $activity['icon'] }}"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">{{ $activity['title'] }}</div>
                                        <div class="activity-description">{{ $activity['description'] }}</div>
                                        <div class="activity-time">{{ $activity['time'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Aucune activité récente</h6>
                            <p class="text-muted small">Commencez par créer votre premier relevé</p>
                            <a href="{{ route('releves.creer') }}" class="btn btn-primary btn-sm">
                                Créer un relevé
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card summary-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Résumé Mensuel
                    </h5>
                </div>
                <div class="card-body">
                    <div class="summary-item">
                        <div class="summary-label">Relevés ce mois</div>
                        <div class="summary-value text-primary">{{ $monthlyReleves ?? '0' }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Intérêts calculés</div>
                        <div class="summary-value text-success">{{ number_format($monthlyInterests ?? 0, 2) }} DA</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Nouveaux clients</div>
                        <div class="summary-value text-info">{{ $monthlyClients ?? '0' }}</div>
                    </div>
                    <div class="summary-item border-0">
                        <div class="summary-label">Taux moyen</div>
                        <div class="summary-value text-warning">{{ number_format($averageRate ?? 0, 2) }}%</div>
                    </div>
                </div>
            </div>

            <!-- Quick Tips Card -->
            <!-- <div class="card tips-card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Conseil du Jour
                    </h5>
                </div>
                <div class="card-body">
                    <div class="tip-content">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        <span>Utilisez les filtres avancés pour analyser vos factures par période et optimiser vos relances.</span>
                    </div>
                </div>
            </div>
        </div> -->
    </div>

    <!-- Custom Styles for Welcome Page -->
    <style>
        .hero-section {
            padding: 2rem 0;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--secondary-color);
            line-height: 1.6;
        }

        .hero-stats {
            display: flex;
            gap: 1.5rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--secondary-color);
            font-weight: 500;
        }

        .hero-image {
            position: relative;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .floating-card {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.3);
            animation: float 6s ease-in-out infinite;
        }

        .floating-card i {
            font-size: 4rem;
            color: white;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .feature-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-shadow-hover);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            font-size: 1.5rem;
        }

        .feature-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        .feature-description {
            color: var(--secondary-color);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .feature-stats {
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 0.5rem;
            border-left: 3px solid var(--primary-color);
        }

        .activity-card, .summary-card, .tips-card {
            border: none;
            box-shadow: var(--card-shadow);
        }

        .activity-timeline {
            position: relative;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .activity-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 19px;
            top: 40px;
            bottom: -24px;
            width: 2px;
            background: #e2e8f0;
        }

        .activity-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .activity-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .activity-description {
            color: var(--secondary-color);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .activity-time {
            color: #94a3b8;
            font-size: 0.75rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-label {
            color: var(--secondary-color);
            font-size: 0.875rem;
        }

        .summary-value {
            font-weight: 600;
            font-size: 1.125rem;
        }

        .tip-content {
            display: flex;
            align-items: flex-start;
            font-size: 0.875rem;
            line-height: 1.6;
            color: var(--secondary-color);
        }

        .empty-state i {
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .hero-actions {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            
            .hero-actions .btn {
                width: 100%;
            }
            
            .floating-card {
                width: 150px;
                height: 150px;
            }
            
            .floating-card i {
                font-size: 3rem;
            }
        }
    </style>
</x-app-layout>