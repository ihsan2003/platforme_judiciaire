@extends('layouts.app')

@section('title', 'Utilisateurs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Utilisateurs</li>
@endsection

@section('content')

{{-- ══ STATS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-people fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $users->total() }}</div>
                    <div class="text-muted small">Total utilisateurs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-shield-fill-check fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ $users->getCollection()->filter(fn($u) => $u->hasRole('admin'))->count() }}
                    </div>
                    <div class="text-muted small">Administrateurs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-person-check fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ $users->getCollection()->filter(fn($u) => !$u->hasRole('admin'))->count() }}
                    </div>
                    <div class="text-muted small">Utilisateurs standard</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ TABLE ══ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-people me-2 text-primary"></i>Gestion des utilisateurs
            <span class="badge bg-primary ms-2">{{ $users->total() }}</span>
        </h5>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus me-1"></i>Nouvel utilisateur
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">#</th>
                    <th class="text-muted small fw-semibold">Nom</th>
                    <th class="text-muted small fw-semibold">E-mail</th>
                    <th class="text-muted small fw-semibold">Rôle(s)</th>
                    <th class="text-muted small fw-semibold">Créé le</th>
                    <th class="text-muted small fw-semibold">Statut e-mail</th>
                    <th class="text-end pe-3 text-muted small fw-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="{{ $user->id === auth()->id() ? 'table-primary bg-opacity-25' : '' }}">
                    <td class="ps-3 text-muted small font-monospace">{{ $user->id }}</td>
                    <td>
                        @php
                            $initials = collect(explode(' ', $user->name))
                                ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                                ->take(2)->implode('');
                        @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center fw-bold text-primary"
                                 style="width:34px;height:34px;font-size:.78rem;flex-shrink:0">
                                {{ $initials }}
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                    <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25" style="font-size:.65rem">
                                        <i class="bi bi-person-check me-1"></i>Moi
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-muted small">
                        <i class="bi bi-envelope me-1 opacity-50"></i>{{ $user->email }}
                    </td>
                    <td>
                        @forelse($user->roles as $role)
                            @php
                                $roleColor = match($role->name) {
                                    'admin'   => ['bg-warning text-dark', 'shield-fill-check'],
                                    'manager' => ['bg-primary bg-opacity-15 text-primary', 'person-gear'],
                                    default   => ['bg-secondary bg-opacity-10 text-secondary', 'person'],
                                };
                            @endphp
                            <span class="badge {{ $roleColor[0] }} border border-opacity-25 me-1">
                                <i class="bi bi-{{ $roleColor[1] }} me-1"></i>
                                {{ ucfirst($role->name) }}
                            </span>
                        @empty
                            <span class="text-muted small">—</span>
                        @endforelse
                    </td>
                    <td class="text-muted small">
                        {{ $user->created_at->format('d/m/Y') }}
                    </td>
                    <td>
                        @if($user->email_verified_at)
                            <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                                <i class="bi bi-patch-check me-1"></i>Vérifié
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-15 text-white border border-danger border-opacity-25">
                                <i class="bi bi-patch-exclamation me-1"></i>Non vérifié
                            </span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                  onsubmit="return confirm('Supprimer l\'utilisateur « {{ $user->name }} » ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @else
                            <button class="btn btn-sm btn-outline-danger" disabled title="Impossible de se supprimer soi-même">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                        Aucun utilisateur trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            Affichage {{ $users->firstItem() }}–{{ $users->lastItem() }}
            sur {{ $users->total() }} utilisateurs
        </span>
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection