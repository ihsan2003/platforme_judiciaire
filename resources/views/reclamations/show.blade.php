{{-- resources/views/reclamations/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Réclamation — ' . Str::limit($reclamation->objet, 40))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reclamations.index') }}">Réclamations</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($reclamation->objet, 40) }}</li>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            {{-- Identité --}}
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px">
                    <i class="bi bi-chat-left-text fs-3 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1">{{ $reclamation->objet }}</h4>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @php
                            $statut = $reclamation->statut?->statut_reclamation ?? '—';
                            $color  = match(true) {
                                $statut === 'قيد المعالجة' => 'warning',
                                $statut === 'تمت المعالجة' => 'success',
                                $statut === 'Reçue'        => 'info',
                                $statut === 'En cours'     => 'warning',
                                $statut === 'Clôturée'     => 'success',
                                default                    => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $color }} bg-opacity-15 text-{{ $color }} border border-{{ $color }} border-opacity-25">
                            <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle"></i>
                            {{ $statut }}
                        </span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                            {{ $reclamation->reclamant?->typeReclamant?->type_reclamant ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Compteurs --}}
            <div class="d-flex flex-wrap gap-4 small text-muted">
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">{{ $reclamation->actions->count() }}</div>
                    <div>Actions</div>
                </div>
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">{{ $reclamation->documents->count() }}</div>
                    <div>Documents</div>
                </div>
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">{{ $reclamation->duree_traitement }} j</div>
                    <div>Durée traitement</div>
                </div>
            </div>

            {{-- Boutons --}}
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('reclamations.edit', $reclamation) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <form action="{{ route('reclamations.destroy', $reclamation) }}" method="POST"
                      onsubmit="return confirm('Supprimer cette réclamation ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>

        {{-- Méta-données --}}
        <hr class="my-3">
        <div class="row g-2 small text-muted">
            <div class="col-sm-3">
                <i class="bi bi-calendar-event me-1"></i>
                <strong>Reçue le :</strong> {{ $reclamation->date_reception?->format('d/m/Y') ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-clock me-1"></i>
                <strong>Mise à jour :</strong> {{ $reclamation->updated_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════
     ONGLETS
══════════════════════════════════════════════════════════ --}}
<ul class="nav nav-tabs mb-0" id="reclamationTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-info">
            <i class="bi bi-info-circle me-1"></i>Informations
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-suivi">
            <i class="bi bi-list-check me-1"></i>Suivi
            <span class="badge bg-primary ms-1">{{ $reclamation->actions->count() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-documents">
            <i class="bi bi-paperclip me-1"></i>Documents
            <span class="badge bg-warning text-dark ms-1">{{ $reclamation->documents->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm p-4" id="reclamationTabContent">

    {{-- ══ ONGLET 1 : INFORMATIONS ══ --}}
    <div class="tab-pane fade show active" id="tab-info">

        <div class="row g-4">

            {{-- Réclamant --}}
            <div class="col-md-6">
                <div class="card border h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-person me-2 text-primary"></i>Réclamant
                        </h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted fw-semibold">Nom</dt>
                            <dd class="col-7 fw-semibold">{{ $reclamation->reclamant?->nom ?? '—' }}</dd>

                            <dt class="col-5 text-muted fw-semibold">Type</dt>
                            <dd class="col-7">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                    {{ $reclamation->reclamant?->typeReclamant?->type_reclamant ?? '—' }}
                                </span>
                            </dd>

                            <dt class="col-5 text-muted fw-semibold">Téléphone</dt>
                            <dd class="col-7">
                                @if($reclamation->reclamant?->telephone)
                                    <a href="tel:{{ $reclamation->reclamant->telephone }}" class="text-decoration-none">
                                        <i class="bi bi-telephone me-1 text-muted"></i>
                                        {{ $reclamation->reclamant->telephone }}
                                    </a>
                                @else
                                    <span class="text-muted fst-italic">Non renseigné</span>
                                @endif
                            </dd>

                            <dt class="col-5 text-muted fw-semibold">Email</dt>
                            <dd class="col-7">
                                @if($reclamation->reclamant?->email)
                                    <a href="mailto:{{ $reclamation->reclamant->email }}" class="text-decoration-none">
                                        <i class="bi bi-envelope me-1 text-muted"></i>
                                        {{ $reclamation->reclamant->email }}
                                    </a>
                                @else
                                    <span class="text-muted fst-italic">Non renseigné</span>
                                @endif
                            </dd>

                            <dt class="col-5 text-muted fw-semibold">Adresse</dt>
                            <dd class="col-7">
                                {{ $reclamation->reclamant?->adresse ?? '—' }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Réclamation --}}
            <div class="col-md-6">
                <div class="card border h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-chat-left-dots me-2 text-primary"></i>Réclamation
                        </h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted fw-semibold">Objet</dt>
                            <dd class="col-7 fw-semibold">{{ $reclamation->objet }}</dd>

                            <dt class="col-5 text-muted fw-semibold">Date réception</dt>
                            <dd class="col-7">{{ $reclamation->date_reception?->format('d/m/Y') ?? '—' }}</dd>

                            <dt class="col-5 text-muted fw-semibold">Statut</dt>
                            <dd class="col-7">
                                <span class="badge bg-{{ $color }} bg-opacity-15 text-{{ $color }} border border-{{ $color }} border-opacity-25">
                                    {{ $statut }}
                                </span>
                            </dd>
                        </dl>

                        {{-- Changer statut rapidement --}}
                        <hr class="my-3">
                        <form action="{{ route('reclamations.update', $reclamation) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="objet" value="{{ $reclamation->objet }}">
                            <input type="hidden" name="date_reception" value="{{ $reclamation->date_reception?->format('Y-m-d') }}">
                            <input type="hidden" name="details" value="{{ $reclamation->details }}">
                            <label class="form-label fw-semibold small text-muted">Changer le statut</label>
                            <div class="input-group input-group-sm">
                                <select name="id_statut_reclamation" class="form-select form-select-sm" required>
                                    @foreach($statuts as $s)
                                        <option value="{{ $s->id }}" @selected($reclamation->id_statut_reclamation == $s->id)>
                                            {{ $s->statut_reclamation }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Détails --}}
            @if($reclamation->details)
            <div class="col-12">
                <div class="card border">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-text-left me-2 text-primary"></i>Description détaillée
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 small lh-lg" style="white-space: pre-wrap;">{{ $reclamation->details }}</p>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>{{-- /tab-info --}}


    {{-- ══ ONGLET 2 : SUIVI / ACTIONS ══ --}}
    <div class="tab-pane fade" id="tab-suivi">

        {{-- ── Formulaire ajouter une action ── --}}
        <div class="card border mb-4" style="border-color: #0d6efd !important; border-width: 2px !important;">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Ajouter une action de suivi
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('reclamations.actions.store', $reclamation) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      id="form-action-suivi">
                @csrf

                    {{-- Passer les données TypeAction en JS --}}
                    @php
                        $typeActionIhala = $typesAction->firstWhere('type_action', 'إحالة')?->id;
                        $typeActionRad   = $typesAction->firstWhere('type_action', 'رد')?->id;
                    @endphp

                    <script>
                        window._typeActionIhala = {{ $typeActionIhala ?? 'null' }};
                        window._typeActionRad   = {{ $typeActionRad   ?? 'null' }};
                    </script>

                    <div class="row g-3">

                        {{-- Type d'action --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">
                                Type d'action <span class="text-danger">*</span>
                            </label>
                            <select name="id_type_action" id="select-type-action" class="form-select" required>
                                <option value="">— Sélectionner —</option>
                                @foreach($typesAction as $type)
                                    <option value="{{ $type->id }}">{{ $type->type_action }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">
                                Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="date_action"
                                   class="form-control"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>

                        {{-- Commentaire --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Commentaire</label>
                            <textarea name="commentaire" class="form-control" rows="2"
                                      placeholder="Notes ou observations…"></textarea>
                        </div>

                        {{-- Bloc Structure — affiché uniquement si type = إحالة --}}
                        <div class="col-12" id="bloc-structure" style="display:none;">
                            <div class="p-3 rounded-3 border border-warning bg-warning bg-opacity-10">
                                <label class="form-label fw-semibold small text-warning-emphasis">
                                    <i class="bi bi-diagram-3 me-1"></i>Structure concernée par l'إحالة
                                </label>
                                <select name="id_structure" class="form-select">
                                    <option value="">— Aucune —</option>
                                    @foreach($structures as $structure)
                                        <option value="{{ $structure->id }}">{{ $structure->nom }}</option>
                                        @foreach($structure->enfants as $enfant)
                                            <option value="{{ $enfant->id }}">&nbsp;&nbsp;↳ {{ $enfant->nom }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Bloc Réponse — affiché uniquement si type = رد --}}
                        <div class="col-12" id="bloc-reponse" style="display:none;">
                            <div class="p-3 rounded-3 border border-success bg-success bg-opacity-10">
                                <label class="form-label fw-semibold small text-success-emphasis">
                                    <i class="bi bi-chat-quote me-1"></i>Contenu de la réponse
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea name="reponse" id="input-reponse" class="form-control" rows="4"
                                          placeholder="Rédigez la réponse apportée au réclamant…"></textarea>
                                <div class="form-text text-success-emphasis mt-1">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Cette réponse sera enregistrée dans l'historique de suivi.
                                </div>
                            </div>
                        </div>

                        {{-- Document joint --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                <i class="bi bi-paperclip me-1"></i>Document joint (optionnel)
                            </label>
                            <input type="file" name="document_action" class="form-control"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <div class="form-text">PDF, Word, Excel, images — max 10 Mo</div>
                        </div>

                        {{-- Soumettre --}}
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-plus-lg me-1"></i>Enregistrer l'action
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Timeline des actions ── --}}
        @if($reclamation->actions->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-list-check fs-1 d-block mb-2 opacity-25"></i>
                Aucune action de suivi enregistrée.
            </div>
        @else
        <h6 class="fw-semibold text-muted mb-3 small text-uppercase" style="letter-spacing:.05em">
            Historique des actions
        </h6>

        <div class="position-relative">
            @if($reclamation->actions->count() > 1)
            <div style="position:absolute; left:23px; top:40px; bottom:40px; width:2px;
                        background: linear-gradient(to bottom, #0d6efd55, #dee2e6); z-index:0;"></div>
            @endif

            @foreach($reclamation->actions as $action)
            @php
                $typeActionLib = $action->typeAction?->type_action ?? '';

                // Déterminer icône et couleur selon le type
                [$iconAction, $colorAction] = match(true) {
                    $typeActionLib === 'إحالة' => ['bi-arrow-right-circle-fill', '#fd7e14'],
                    $typeActionLib === 'رد'    => ['bi-chat-quote-fill',         '#198754'],
                    default                    => ['bi-arrow-right-circle',      '#0d6efd'],
                };

                // Séparer réponse et commentaire si le commentaire contient "**Réponse :**"
                $commentaire = $action->commentaire ?? '';
                $reponse     = null;
                if (str_contains($commentaire, '**Réponse :**')) {
                    $parts       = explode('**Réponse :**', $commentaire, 2);
                    $commentaire = trim($parts[0]);
                    $reponse     = trim($parts[1]);
                }
            @endphp
            <div class="d-flex gap-3 mb-3 position-relative" style="z-index:1;">
                {{-- Icône --}}
                <div class="flex-shrink-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white"
                         style="width:48px;height:48px;background:{{ $colorAction }};
                                box-shadow:0 0 0 4px {{ $colorAction }}33;font-size:.75rem">
                        <i class="bi {{ $iconAction }} fs-5"></i>
                    </div>
                </div>

                {{-- Contenu --}}
                <div class="card border w-100">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                            <div>
                                <div class="fw-bold">
                                    {{ $typeActionLib ?: '—' }}
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ $action->date_action?->format('d/m/Y') ?? '—' }}
                                    @if($action->structure)
                                        &nbsp;·&nbsp;
                                        <i class="bi bi-diagram-3 me-1"></i>
                                        {{ $action->structure->nom }}
                                    @endif
                                    @if($action->createdBy)
                                        &nbsp;·&nbsp;
                                        <i class="bi bi-person me-1"></i>
                                        {{ $action->createdBy->name }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Commentaire --}}
                        @if($commentaire)
                            <p class="small text-muted mb-2 lh-lg" style="white-space: pre-wrap;">{{ $commentaire }}</p>
                        @endif

                        {{-- Réponse (bloc distinct si type = رد) --}}
                        @if($reponse)
                            <div class="border border-success rounded-2 p-2 mt-2 bg-success bg-opacity-10">
                                <div class="small fw-semibold text-success-emphasis mb-1">
                                    <i class="bi bi-chat-quote me-1"></i>Réponse apportée
                                </div>
                                <p class="small mb-0 lh-lg" style="white-space: pre-wrap;">{{ $reponse }}</p>
                            </div>
                        @endif

                        {{-- Structure concernée (إحالة) --}}
                        @if($typeActionLib === 'إحالة' && $action->structure)
                            <div class="border border-warning rounded-2 p-2 mt-2 bg-warning bg-opacity-10">
                                <div class="small fw-semibold text-warning-emphasis mb-1">
                                    <i class="bi bi-diagram-3 me-1"></i>إحالة vers
                                </div>
                                <span class="small">{{ $action->structure->nom }}</span>
                            </div>
                        @endif

                        {{-- Documents --}}
                        {{-- Les documents d'action nécessitent la colonne id_action en base --}}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>{{-- /tab-suivi --}}


    {{-- ══ ONGLET 3 : DOCUMENTS ══ --}}
    <div class="tab-pane fade" id="tab-documents">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0">
                <i class="bi bi-paperclip me-2 text-primary"></i>Documents joints
            </h6>
        </div>

        @if($reclamation->documents->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark fs-1 d-block mb-2 opacity-25"></i>
                Aucun document joint à cette réclamation.<br>
                <span class="small">Vous pouvez joindre des documents via les actions de suivi.</span>
            </div>
        @else
        <div class="row g-3">
            @foreach($reclamation->documents as $doc)
            @php
                $ext  = strtolower(pathinfo($doc->fichier_path ?? '', PATHINFO_EXTENSION));
                $icon = match($ext) {
                    'pdf'         => 'bi-file-earmark-pdf text-danger',
                    'doc','docx'  => 'bi-file-earmark-word text-primary',
                    'xls','xlsx'  => 'bi-file-earmark-excel text-success',
                    'jpg','jpeg','png','gif' => 'bi-file-earmark-image text-warning',
                    default       => 'bi-file-earmark text-secondary',
                };
            @endphp
            <div class="col-md-4 col-lg-3">
                <div class="card border h-100">
                    <div class="card-body d-flex flex-column align-items-center text-center py-4">
                        <i class="bi {{ $icon }} fs-1 mb-2"></i>
                        <div class="small fw-semibold text-truncate w-100" title="{{ $doc->titre_document }}">
                            {{ $doc->titre_document }}
                        </div>
                        @if($doc->typeDocument)
                            <span class="badge bg-light text-secondary border small mt-1">
                                {{ $doc->typeDocument->type_document }}
                            </span>
                        @endif
                        <div class="text-muted mt-1" style="font-size:.7rem">
                            {{ $doc->date_depot?->format('d/m/Y') ?? '—' }}
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top d-flex justify-content-center py-2">
                        <a href="{{ Storage::url($doc->fichier_path) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-download me-1"></i>Télécharger
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>{{-- /tab-documents --}}

</div>{{-- /tab-content --}}

@endsection

@push('scripts')
<script>
(function () {
    // ── Restore tab from URL hash ───────────────────────
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tab) new bootstrap.Tab(tab).show();
    }

    // ── Logique conditionnelle du formulaire de suivi ──
    const selectType   = document.getElementById('select-type-action');
    const blocStruct   = document.getElementById('bloc-structure');
    const blocReponse  = document.getElementById('bloc-reponse');
    const inputReponse = document.getElementById('input-reponse');

    if (!selectType) return;

    function updateBlocs() {
        const val = parseInt(selectType.value, 10);

        const isIhala = window._typeActionIhala && val === window._typeActionIhala;
        const isRad   = window._typeActionRad   && val === window._typeActionRad;

        // Structure : visible seulement pour إحالة
        blocStruct.style.display  = isIhala ? '' : 'none';

        // Réponse : visible seulement pour رد
        blocReponse.style.display = isRad   ? '' : 'none';

        // Rendre la réponse requise uniquement quand visible
        if (inputReponse) {
            inputReponse.required = isRad;
        }
    }

    selectType.addEventListener('change', updateBlocs);
    updateBlocs(); // initialisation
})();
</script>
@endpush