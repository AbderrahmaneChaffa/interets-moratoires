<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des Intérêts Moratoires</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @livewireStyles
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">Intérêts Moratoires</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('clients') }}">Gestion Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('factures.creer') }}">Créer Facture</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('factures') }}">Liste Factures</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('factures.tableau') }}">Tableau avec Filtres</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">Système de Gestion des Intérêts Moratoires</h1>
                    </div>
                    <div class="card-body">
                        <p class="lead">Bienvenue dans votre application de gestion des intérêts moratoires.</p>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h5 class="card-title">Gestion Clients</h5>
                                        <p class="card-text">Ajouter, modifier et supprimer des clients.</p>
                                        <a href="{{ route('clients') }}" class="btn btn-primary">Accéder</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h5 class="card-title">Créer Facture</h5>
                                        <p class="card-text">Créer une nouvelle facture avec auto-complétion client.</p>
                                        <a href="{{ route('factures.creer') }}" class="btn btn-success">Accéder</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h5 class="card-title">Liste Factures</h5>
                                        <p class="card-text">Consulter les factures et calculer les intérêts.</p>
                                        <a href="{{ route('factures') }}" class="btn btn-info">Accéder</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h5 class="card-title">Tableau avec Filtres</h5>
                                        <p class="card-text">Tableau filtré avec export PDF/Excel.</p>
                                        <a href="{{ route('factures.tableau') }}" class="btn btn-warning">Accéder</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
