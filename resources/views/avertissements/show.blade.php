@extends('layouts.app')

@section('title', 'الإنذار #' . $avertissement->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('avertissements.index') }}">الإنذارات</a></li>
    <li class="breadcrumb-item active">{{ $avertissement->id }}</li>
@endsection

@push('styles')
<style>
    /* ── Même langage visuel que dossiers/show ─────────── */
    :root { --jug: #0f766e; }

    .dossier-header {
        background: #1a3a5c; border-radius: 16px;
        padding: 28px 32px; color: #fff; margin-bottom: 24px;
    }
    .dossier-header-kpi { display: flex; gap: 28px; flex-wrap: wrap; align-items: flex-end; }
    .kpi-item { text-align: center; }
    .kpi-val  { font-size: 1.5rem; font-weight: 800; color: #c8a84b; line-height: 1; }
    .kpi-lab  { font-size: .68rem; opacity: .7; text-transform: uppercase; letter-spacing: .06em; }

    .pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 8px; font-size: .9rem; font-weight: 700; }
    .pill-white   { background: rgba(255,255,255,.18); color: #fff; border: 1px solid rgba(255,255,255,.3); }
    .pill-success { background: #dcfce7; color: #166534; }
    .pill-warning { background: #fef3c7; color: #92400e; }
    .pill-muted   { background: #f1f5f9; color: #64748b; }
    .pill-purple  { background: #f3e8ff; color: #6b21a8; }

    .section-card { border: none; box-shadow: 0 2px 8px rgba(0,0,0,.06); border-radius: 12px; }

    .jug-block {
        border: 2px solid var(--jug); border-radius: 12px;
        background: #f0fdfa; padding: 16px 18px; margin-bottom: 14px;
    }
    .jug-block.prep { border-color: #7c3aed; background: #fdf4ff; }
    .jug-block-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
    .jug-title { font-weight: 800; font-size: .95rem; color: var(--jug); display: flex; align-items: center; gap: 6px; }
    .jug-block.prep .jug-title { color: #6b21a8; }
    .jug-meta  { font-size: .86rem; color: #475569; display: flex; flex-direction: column; gap: 4px; margin-bottom: 10px; }
    .jug-dispositif {
        font-size: .85rem; color: #334155; background: #fff;
        border: 1px solid #ccfbf1; border-right: 3px solid var(--jug);
        padding: 8px 12px; border-radius: 6px; line-height: 1.7; white-space: pre-line;
    }
    .jug-block.prep .jug-dispositif { border-color: #e9d5ff; border-right-color: #7c3aed; }

    .empty-state {
        padding: 20px; border-radius: 10px; background: #f8fafc;
        border: 1px dashed #cbd5e1; text-align: center;
        color: #94a3b8; font-size: .85rem;
    }
</style>
@endpush

@section('content')

@php
    $nbJugements = $avertissement->jugements->count();
@endphp

{{-- ══════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════ --}}
<div class="dossier-header mb-4">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center"
                 style="width:52px;height:52px;background:rgba(255,255,255,.12);flex-shrink:0">
                <i class="bi bi-exclamation-triangle fs-3 text-warning"></i>
            </div>

            <div>
                <h4 class="fw-bold mb-0 text-white">{{ $avertissement->id }}</h4>

                <div class="small" style="opacity:.7">
                    <i class="bi bi-bank me-1"></i>{{ $avertissement->tribunal->nom_tribunal ?? '—' }}
                </div>

                <div class="mt-1 d-flex flex-wrap gap-2">
                    <span class="pill pill-white">
                        <i class="bi bi-circle-fill" style="font-size:.4rem"></i>
                        {{ $avertissement->statut->statut_avertissement ?? '—' }}
                    </span>
                    <span class="pill pill-white">
                        <i class="bi bi-tag"></i>{{ $avertissement->typeAffaire->affaire ?? '—' }}
                    </span>
                    <span class="pill pill-white">
                        <i class="bi bi-geo-alt"></i>{{ $avertissement->province->province ?? '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="dossier-header-kpi">
            <div class="kpi-item">
                <div class="kpi-val">{{ $nbJugements }}</div>
                <div class="kpi-lab">الأحكام</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-val">{{ $avertissement->jugements->where('est_definitif', true)->count() }}</div>
                <div class="kpi-lab">أحكام نهائية</div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex flex-wrap gap-2 align-items-start">
            @can('update', $avertissement)
                <a href="{{ route('avertissements.edit', $avertissement) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>تعديل
                </a>
            @endcan

            @can('delete', $avertissement)
                <form action="{{ route('avertissements.destroy', $avertissement) }}" method="POST"
                      data-confirm="هل تريد حذف هذا الإنذار ؟" data-confirm-variant="warning" data-confirm-label="نعم، حذف">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-light btn-sm">
                        <i class="bi bi-trash me-1"></i>حذف
                    </button>
                </form>
            @endcan

            <a href="{{ route('avertissements.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-right me-1"></i>القائمة
            </a>
        </div>
    </div>

    <hr class="mt-3 mb-2" style="border-color:rgba(255,255,255,.15)">

    <div class="row g-2 small" style="opacity:.75">
        <div class="col-sm-3 text-white">
            <i class="bi bi-globe-europe-africa me-1"></i>
            <strong>الجهة :</strong> {{ $avertissement->province?->region?->region ?? '—' }}
        </div>
        <div class="col-sm-3 text-white">
            <i class="bi bi-person me-1"></i>
            <strong>أنشئ بواسطة :</strong> {{ $avertissement->createdBy->name ?? '—' }}
        </div>
        <div class="col-sm-3 text-white">
            <i class="bi bi-calendar-check me-1"></i>
            <strong>تاريخ الإنذار :</strong> {{ $avertissement->date_avertissement?->format('d/m/Y') ?? '—' }}
        </div>
        <div class="col-sm-3 text-white">
            <i class="bi bi-calendar-event me-1"></i>
            <strong>تاريخ الإنشاء :</strong> {{ $avertissement->created_at?->format('d/m/Y') }}
        </div>
        <div class="col-sm-3 text-white">
            <i class="bi bi-clock me-1"></i>
            <strong>آخر تحديث :</strong> {{ $avertissement->updated_at->diffForHumans() }}
        </div>
    </div>

    @if($avertissement->objet_litige || $avertissement->action_service)
        <div class="row g-3 small mt-1 text-white">
            @if($avertissement->objet_litige)
                <div class="col-md-6">
                    <div style="opacity:.75" class="mb-1">
                        <i class="bi bi-chat-square-text me-1"></i><strong>موضوع النزاع :</strong>
                    </div>
                    <div>{!! nl2br(e($avertissement->objet_litige)) !!}</div>
                </div>
            @endif

            @if($avertissement->action_service)
                <div class="col-md-6">
                    <div style="opacity:.75" class="mb-1">
                        <i class="bi bi-clipboard-check me-1"></i><strong>الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية :</strong>
                    </div>
                    <div>{!! nl2br(e($avertissement->action_service)) !!}</div>
                </div>
            @endif
        </div>
    @endif
</div>

<div class="row g-4">

    {{-- ══ الأطراف + الملاحظات ══ --}}
    <div class="col-12 col-lg-4">
        <div class="card section-card mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-people me-2 text-success"></i>الأطراف</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small mb-1">المدعي</div>
                    <div class="fw-semibold">
                        <i class="bi bi-person-check text-primary me-1"></i>
                        {{ $avertissement->demandeur->nom_partie ?? '—' }}
                    </div>
                </div>
                <div>
                    <div class="text-muted small mb-1">المدعى عليه</div>
                    <div class="fw-semibold">
                        <i class="bi bi-person-x text-danger me-1"></i>
                        {{ $avertissement->defendeur->nom_partie ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card section-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-chat-left-text me-2 text-primary"></i>الملاحظات</h6>
            </div>
            <div class="card-body">
                @if($avertissement->commentaire)
                    <div style="white-space: pre-line;">{{ $avertissement->commentaire }}</div>
                @else
                    <div class="empty-state">لا توجد ملاحظات</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ الأحكام ══ --}}
    <div class="col-12 col-lg-8">
        <div class="card section-card">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-hammer me-2" style="color:var(--jug)"></i>الأحكام
                    <span class="badge bg-secondary ms-1">{{ $nbJugements }}</span>
                </h6>

                @can('update', $avertissement)
                    <button type="button" class="btn btn-sm btn-primary"
                            data-bs-toggle="modal" data-bs-target="#modalAjouterJugement">
                        <i class="bi bi-plus-lg me-1"></i>إضافة حكم
                    </button>
                @endcan
            </div>

            <div class="card-body">
                @forelse($avertissement->jugements as $jugement)
                    @php $prep = $jugement->estPreparatoire(); @endphp

                    <div class="jug-block {{ $prep ? 'prep' : '' }}">
                        <div class="jug-block-header">
                            <div class="jug-title">
                                <i class="bi bi-hammer"></i>
                                حكم بتاريخ {{ $jugement->date_jugement->format('d/m/Y') }}
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="pill {{ $prep ? 'pill-purple' : 'pill-success' }}">
                                    {{ $jugement->typeJugement->type_jugement ?? '—' }}
                                </span>
                                @if($jugement->est_definitif)
                                    <span class="pill pill-warning"><i class="bi bi-lock"></i>نهائي</span>
                                @endif

                                @can('update', $avertissement)
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditJugement{{ $jugement->id }}"
                                            title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @endcan

                                @can('delete', $avertissement)
                                    <x-modal-delete
                                        :action="route('avertissements.jugements.destroy', [$avertissement, $jugement])"
                                        modal-id="deleteJugement{{ $jugement->id }}"
                                        trigger-label=""
                                        trigger-class="btn btn-sm btn-outline-danger"
                                        trigger-icon="bi-trash"
                                        title="حذف الحكم"
                                        :description="'حكم بتاريخ ' . $jugement->date_jugement->format('d/m/Y')"
                                        warning="هل أنت متأكد من حذف هذا الحكم؟"
                                        confirm-label="نعم، حذف"
                                    />
                                @endcan
                            </div>
                        </div>

                        <div class="jug-meta">
                            <span><i class="bi bi-bank me-1"></i>{{ $avertissement->tribunal->nom_tribunal ?? '—' }}</span>
                            <span><i class="bi bi-person-badge me-1"></i>القاضي : {{ $jugement->juge->nom_complet ?? '—' }}</span>
                        </div>

                        <div class="jug-dispositif">{{ $jugement->contenu_dispositif }}</div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-hammer fs-3 d-block mb-2 opacity-50"></i>
                        لم يُسجَّل أي حكم لهذا الإنذار بعد
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     MODALES — الأحكام
══════════════════════════════════════════════ --}}
@can('update', $avertissement)

    @php
        // Formulaire commun (ajout / modification)
        $champsJugement = function ($j = null) use ($typesJugement, $juges) {
            return compact('j', 'typesJugement', 'juges');
        };
    @endphp

    {{-- Ajouter --}}
    <div class="modal fade" id="modalAjouterJugement" tabindex="-1" dir="rtl">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-semibold mb-0">
                        <i class="bi bi-hammer me-1 text-primary"></i>إضافة حكم
                    </h5>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAjouterJugement"
                          action="{{ route('avertissements.jugements.store', $avertissement) }}" method="POST">
                        @csrf
                        @include('avertissements._jugement_fields', $champsJugement(null))
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" form="formAjouterJugement" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>حفظ الحكم
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modifier --}}
    @foreach($avertissement->jugements as $jugement)
        <div class="modal fade" id="modalEditJugement{{ $jugement->id }}" tabindex="-1" dir="rtl">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="modal-title fw-semibold mb-0">
                            <i class="bi bi-pencil me-1 text-warning"></i>
                            تعديل الحكم بتاريخ {{ $jugement->date_jugement->format('d/m/Y') }}
                        </h5>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditJugement{{ $jugement->id }}"
                              action="{{ route('avertissements.jugements.update', [$avertissement, $jugement]) }}"
                              method="POST">
                            @csrf @method('PUT')
                            @include('avertissements._jugement_fields', $champsJugement($jugement))
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" form="formEditJugement{{ $jugement->id }}" class="btn btn-warning">
                            <i class="bi bi-check-lg me-1"></i>حفظ التعديلات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Erreurs de validation : on rouvre la modale concernée --}}
    @if($errors->hasAny(['date_jugement', 'contenu_dispositif', 'id_type_jugement', 'id_juge']))
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const m = document.getElementById('modalAjouterJugement');
                if (m) bootstrap.Modal.getOrCreateInstance(m).show();
            });
        </script>
        @endpush
    @endif

@endcan

@endsection
