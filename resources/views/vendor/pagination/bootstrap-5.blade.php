@if ($paginator->hasPages())
<nav aria-label="Pagination" class="entraide-pagination-nav">
    <ul class="entraide-pagination">

        {{-- Bouton Précédent --}}
        @if ($paginator->onFirstPage())
            <li class="entraide-page-item disabled">
                <span class="entraide-page-link entraide-page-prev">
                    <i class="bi bi-chevron-left"></i>
                    <span>Précédent</span>
                </span>
            </li>
        @else
            <li class="entraide-page-item">
                <a class="entraide-page-link entraide-page-prev" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <i class="bi bi-chevron-left"></i>
                    <span>Précédent</span>
                </a>
            </li>
        @endif

        {{-- Numéros de page --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="entraide-page-item entraide-ellipsis">
                    <span class="entraide-page-link">{{ $element }}</span>
                </li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="entraide-page-item active">
                            <span class="entraide-page-link" aria-current="page">{{ $page }}</span>
                        </li>
                    @else
                        <li class="entraide-page-item">
                            <a class="entraide-page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Bouton Suivant --}}
        @if ($paginator->hasMorePages())
            <li class="entraide-page-item">
                <a class="entraide-page-link entraide-page-next" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    <span>Suivant</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="entraide-page-item disabled">
                <span class="entraide-page-link entraide-page-next">
                    <span>Suivant</span>
                    <i class="bi bi-chevron-right"></i>
                </span>
            </li>
        @endif

    </ul>

    {{-- Infos résultats --}}
    <div class="entraide-pagination-info">
        Affichage
        <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong>
        sur
        <strong>{{ $paginator->total() }}</strong>
        résultats
    </div>
</nav>

<style>
/* ═══════════════════════════════════════════════════════════════
   PAGINATION — Plateforme Juridique Entraide Nationale
   Palette : --primary #1a3a5c  |  --accent #c8a84b
   ═══════════════════════════════════════════════════════════════ */

.entraide-pagination-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 4px 0;
}

/* ── Liste ---------------------------------------------- */
.entraide-pagination {
    display: flex;
    align-items: center;
    gap: 4px;
    list-style: none;
    margin: 0;
    padding: 0;
}

/* ── Lien / bouton de base ----------------------------- */
.entraide-page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border-radius: 8px;
    font-size: .82rem;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    cursor: pointer;
    transition: background .18s, color .18s, box-shadow .18s, transform .12s;
    user-select: none;

    /* couleur par défaut */
    background: #ffffff;
    color: #1a3a5c;
    border: 1.5px solid #e0e6ef;
    box-shadow: 0 1px 3px rgba(26, 58, 92, 0.06);
}

.entraide-page-link:hover {
    background: #eef3f9;
    border-color: #c8a84b;
    color: #1a3a5c;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(200, 168, 75, 0.18);
}

/* ── Page active --------------------------------------- */
.entraide-page-item.active .entraide-page-link {
    background: linear-gradient(135deg, #1a3a5c 0%, #24527f 100%);
    color: #c8a84b;
    border-color: #1a3a5c;
    box-shadow: 0 3px 10px rgba(26, 58, 92, 0.30);
    transform: none;
    pointer-events: none;
    min-width: 40px;
    height: 40px;
    font-size: .88rem;
}

/* ── Désactivé (Précédent / Suivant aux extrémités) ---- */
.entraide-page-item.disabled .entraide-page-link {
    opacity: .42;
    cursor: not-allowed;
    pointer-events: none;
    transform: none;
    box-shadow: none;
}

/* ── Ellipsis ------------------------------------------ */
.entraide-page-item.entraide-ellipsis .entraide-page-link {
    background: transparent;
    border-color: transparent;
    box-shadow: none;
    color: #94a3b8;
    cursor: default;
    letter-spacing: .1em;
    font-size: .9rem;
}
.entraide-page-item.entraide-ellipsis .entraide-page-link:hover {
    transform: none;
    background: transparent;
    border-color: transparent;
    box-shadow: none;
}

/* ── Boutons Précédent / Suivant (plus larges) --------- */
.entraide-page-prev,
.entraide-page-next {
    padding: 0 14px;
    gap: 6px;
    font-size: .8rem;
    letter-spacing: .01em;
}

.entraide-page-prev i,
.entraide-page-next i {
    font-size: .78rem;
    transition: transform .15s;
}

a.entraide-page-prev:hover i  { transform: translateX(-2px); }
a.entraide-page-next:hover i  { transform: translateX(2px); }

/* ── Texte d'info (affichage X–Y sur N) --------------- */
.entraide-pagination-info {
    font-size: .8rem;
    color: #64748b;
    letter-spacing: .01em;
}

.entraide-pagination-info strong {
    color: #1a3a5c;
    font-weight: 700;
}

/* ── Responsive ---------------------------------------- */
@media (max-width: 480px) {
    .entraide-pagination-nav {
        justify-content: center;
    }
    .entraide-page-prev span,
    .entraide-page-next span {
        display: none;  /* cacher le texte, garder l'icône */
    }
    .entraide-page-prev,
    .entraide-page-next {
        padding: 0 10px;
    }
}
</style>
@endif