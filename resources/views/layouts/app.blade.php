<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Plateforme Juridique') — Entraide Nationale</title>

    {{-- Bootstrap 5 + Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #1a3a5c;
            --accent: #c8a84b;
        }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

        /* Sidebar */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--primary);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: width .25s;
            overflow-x: hidden;
        }
        #sidebar .sidebar-brand {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        #sidebar .sidebar-brand img { height: 40px; }
        #sidebar .sidebar-brand span {
            color: var(--accent);
            font-weight: 700;
            font-size: .95rem;
            white-space: nowrap;
        }
        #sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: .6rem 1.2rem;
            border-radius: 6px;
            margin: 2px 8px;
            font-size: .88rem;
            transition: all .2s;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(200,168,75,.2);
            color: var(--accent);
        }
        #sidebar .nav-link i { width: 20px; margin-right: 8px; }
        #sidebar .nav-section {
            color: rgba(255,255,255,.4);
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: .8rem 1.2rem .2rem;
        }

        /* Main */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        #topbar {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: .6rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* Content */
        .content-wrapper { padding: 1.5rem; flex: 1; }

        /* Cards */
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .card-header { border-bottom: 1px solid #f0f0f0; background: #fff; border-radius: 12px 12px 0 0 !important; }

        /* Stat cards */
        .stat-card { border-radius: 12px; padding: 1.2rem; color: #fff; }
        .stat-card .stat-icon { font-size: 2rem; opacity: .8; }
        .stat-card .stat-value { font-size: 2rem; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { font-size: .8rem; opacity: .85; }

        /* Status badges */
        .badge-statut { font-size: .75rem; padding: .35em .7em; border-radius: 20px; }

        /* Alerts */
        .alert { border-radius: 10px; }

        /* Table */
        .table th { background: #f8f9fa; font-size: .82rem; text-transform: uppercase; letter-spacing: .5px; }
        .table td { vertical-align: middle; font-size: .9rem; }
        .table-hover tbody tr:hover { background: #f0f4ff; }

        /* RTL Arabic text helper */
        .ar { direction: rtl; font-family: 'Segoe UI', Tahoma, sans-serif; }

        @media (max-width: 768px) {
            #sidebar { width: 0; }
            #main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<nav id="sidebar">
    <div class="sidebar-brand d-flex align-items-center gap-2">
        <i class="bi bi-bank2 fs-4 text-warning"></i>
        <span>Plateforme Juridique</span>
    </div>

    <ul class="nav flex-column mt-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
        </li>

        <div class="nav-section">Dossiers</div>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dossiers.*') ? 'active' : '' }}"
               href="{{ route('dossiers.index') }}">
                <i class="bi bi-folder2-open"></i> Dossiers judiciaires
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('audiences.*') ? 'active' : '' }}"
               href="{{ route('audiences.index') }}">
                <i class="bi bi-calendar-event"></i> Audiences
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('jugements.*') ? 'active' : '' }}"
               href="{{ route('jugements.index') }}">
                <i class="bi bi-hammer"></i> Jugements
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('executions.*') ? 'active' : '' }}"
               href="{{ route('executions.index') }}">
                <i class="bi bi-check2-circle"></i> Exécutions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('finances.*') ? 'active' : '' }}"
               href="{{ route('finances.index') }}">
                <i class="bi bi-check2-circle"></i> Finances
            </a>
        </li>

        <div class="nav-section">Réclamations</div>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reclamations.*') ? 'active' : '' }}"
               href="{{ route('reclamations.index') }}">
                <i class="bi bi-envelope-exclamation"></i> Réclamations
                @php $countRecl = \App\Models\Reclamation::enAttente()->count(); @endphp
                @if($countRecl > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $countRecl }}</span>
                @endif
            </a>
        </li>

        <div class="nav-section">Référentiels</div>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('parties.*') ? 'active' : '' }}"
               href="{{ route('parties.index') }}">
                <i class="bi bi-people"></i> Parties
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('avocats.*') ? 'active' : '' }}"
               href="{{ route('avocats.index') }}">
                <i class="bi bi-person-badge"></i> Avocats
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tribunaux.*') ? 'active' : '' }}"
               href="{{ route('tribunaux.index') }}">
                <i class="bi bi-building"></i> Tribunaux
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('juges.*') ? 'active' : '' }}"
               href="{{ route('juges.index') }}">
                <i class="bi bi-person-workspace"></i> Juges
            </a>
        </li>

        @can('manage users')
        <div class="nav-section">Administration</div>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
               href="{{ route('admin.users.index') }}">
                <i class="bi bi-shield-person"></i> Utilisateurs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.structures.*') ? 'active' : '' }}"
               href="{{ route('admin.structures.index') }}">
                <i class="bi bi-diagram-3"></i> Structures
            </a>
        </li>
        @endcan
    </ul>
</nav>

{{-- Main --}}
<div id="main-content">

    {{-- Topbar --}}
    <div id="topbar" class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-light d-md-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <nav aria-label="breadcrumb" class="mb-0">
                <ol class="breadcrumb mb-0 small">
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-3">            
            {{-- User --}}
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span class="d-none d-md-inline small">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="content-wrapper">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Erreurs de validation :</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle sidebar mobile
    document.getElementById('sidebarToggle')?.addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.style.width = sidebar.style.width === '260px' ? '0' : '260px';
    });
</script>
@stack('scripts')
</body>
</html>
