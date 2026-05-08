{{--
    resources/views/components/modal-delete.blade.php
    ─────────────────────────────────────────────────────
    Composant modal de confirmation de suppression réutilisable.

    USAGE MINIMAL :
        <x-modal-delete
            :action="route('dossiers.destroy', $dossier)"
            trigger-label="Supprimer"
        />

    USAGE COMPLET :
        <x-modal-delete
            :action="route('audiences.destroy', $audience)"
            modal-id="deleteAudience{{ $audience->id }}"
            trigger-label="Supprimer"
            trigger-class="btn btn-sm btn-outline-danger"
            trigger-icon="bi-trash"
            title="Supprimer l'audience"
            :description="'Audience du ' . $audience->date_audience->format('d/m/Y')"
            warning="Cette action est irréversible."
            confirm-label="Oui, supprimer"
        />

    PROPS :
        action          (string)   — URL de l'action (route DELETE)                  [requis]
        modal-id        (string)   — ID unique du modal (défaut : uuid auto)
        trigger-label   (string)   — Texte du bouton déclencheur    (défaut : "Supprimer")
        trigger-class   (string)   — Classes CSS du bouton          (défaut : "btn btn-sm btn-outline-danger")
        trigger-icon    (string)   — Icône Bootstrap Icons           (défaut : "bi-trash")
        title           (string)   — Titre du modal                  (défaut : "Confirmer la suppression")
        description     (string)   — Élément ciblé (ex : nom du dossier)
        warning         (string)   — Message d'avertissement secondaire
        confirm-label   (string)   — Texte du bouton de confirmation (défaut : "Oui, supprimer")
--}}

@props([
    'action',
    'modalId'      => 'deleteModal_' . \Illuminate\Support\Str::uuid(),
    'triggerLabel' => 'Supprimer',
    'triggerClass' => 'btn btn-sm btn-outline-danger',
    'triggerIcon'  => 'bi-trash',
    'title'        => 'Confirmer la suppression',
    'description'  => null,
    'warning'      => 'Cette action est irréversible et les données ne pourront pas être récupérées.',
    'confirmLabel' => 'Oui, supprimer',
])

{{-- ── Bouton déclencheur ─────────────────────────────────────────── --}}
<button type="button"
        class="{{ $triggerClass }}"
        data-bs-toggle="modal"
        data-bs-target="#{{ $modalId }}">
    <i class="bi {{ $triggerIcon }}"></i>
    @if($triggerLabel)
        <span class="ms-1">{{ $triggerLabel }}</span>
    @endif
</button>

{{-- ── Modal ──────────────────────────────────────────────────────── --}}
<div class="modal fade"
     id="{{ $modalId }}"
     tabindex="-1"
     aria-labelledby="{{ $modalId }}_label"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <div class="modal-content border-0 overflow-hidden" style="border-radius:16px; box-shadow:0 24px 64px rgba(0,0,0,.18)">

            {{-- Bande décorative rouge ──────────────────────────────── --}}
            <div style="height:4px; background:linear-gradient(90deg,#dc3545 0%,#ff6b6b 100%)"></div>

            {{-- En-tête ─────────────────────────────────────────────── --}}
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-3 w-100">

                    {{-- Icône centrale ──────────────────────────────── --}}
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3"
                         style="width:52px;height:52px;background:#fff1f1;border:2px solid #ffd0d0">
                        <i class="bi bi-exclamation-triangle-fill"
                           style="font-size:1.4rem;color:#dc3545"></i>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <h5 class="modal-title fw-bold mb-0"
                            id="{{ $modalId }}_label"
                            style="color:#1a3a5c;font-size:1.05rem">
                            {{ $title }}
                        </h5>
                        @if($description)
                            <div class="text-muted small mt-1 text-truncate"
                                 title="{{ $description }}">
                                <i class="bi bi-tag me-1 opacity-50"></i>{{ $description }}
                            </div>
                        @endif
                    </div>

                    <button type="button"
                            class="btn-close ms-auto flex-shrink-0"
                            data-bs-dismiss="modal"
                            aria-label="Fermer"></button>
                </div>
            </div>

            {{-- Corps ───────────────────────────────────────────────── --}}
            <div class="modal-body px-4 pt-3 pb-2">

                {{-- Bloc avertissement ──────────────────────────────── --}}
                @if($warning)
                <div class="d-flex align-items-start gap-2 rounded-3 px-3 py-2 mb-1"
                     style="background:#fff8f0;border:1px solid #ffe4c4">
                    <i class="bi bi-shield-exclamation flex-shrink-0 mt-1"
                       style="color:#e07b00;font-size:.9rem"></i>
                    <p class="mb-0 small" style="color:#7c3a00;line-height:1.5">
                        {{ $warning }}
                    </p>
                </div>
                @endif

            </div>

            {{-- Pied ────────────────────────────────────────────────── --}}
            <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">

                {{-- Annuler ─────────────────────────────────────────── --}}
                <button type="button"
                        class="btn btn-outline-secondary flex-fill"
                        data-bs-dismiss="modal"
                        style="border-radius:10px;font-size:.88rem;padding:.55rem 1rem">
                    <i class="bi bi-x-lg me-1"></i>Annuler
                </button>

                {{-- Confirmer ───────────────────────────────────────── --}}
                <form action="{{ $action }}" method="POST" class="flex-fill">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn btn-danger w-100"
                            style="border-radius:10px;font-size:.88rem;padding:.55rem 1rem;
                                   background:linear-gradient(135deg,#dc3545 0%,#c82333 100%);
                                   border:none;box-shadow:0 4px 12px rgba(220,53,69,.35)">
                        <i class="bi bi-trash3-fill me-1"></i>{{ $confirmLabel }}
                    </button>
                </form>

            </div>

        </div>
    </div>
</div>