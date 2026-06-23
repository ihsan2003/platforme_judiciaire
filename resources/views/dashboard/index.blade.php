{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'لوحة التحكم')

@push('styles')
<style>
    :root {
        --accent: #c8a84b;
        --primary: #1a3a5c;
        --bg: #f4f6fa;
        --border: #e8ecf4;
    }

    /* ── Hero banner ───────────────────────────────── */
    .hero-banner {
        background-image: url('{{ asset('images/dashboard-bg.jpg') }}');
        background-size: cover;
        background-position: center;
        height: 220px;
        border-radius: 16px;
        padding: 28px 32px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .hero-banner::before {
        content: '';
        position: absolute;
        left: -40px; top: -40px; /* Adapté pour RTL */
        width: 280px; height: 280px;
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-banner::after {
        content: '';
        position: absolute;
        left: 60px; bottom: -80px; /* Adapté pour RTL */
        width: 200px; height: 200px;
        border-radius: 50%;
        pointer-events: none;
    }
    /* ── Stat cards ──────────────────────────────── */
    .stat-card-new {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        height: 100%;
    }
    .stat-icon-box {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .stat-val-big { font-size: 1.5rem; font-weight: 800; color: #1e293b; line-height: 1; letter-spacing: -.5px; }
    .stat-lbl { font-size: .78rem; color: #64748b; font-weight: 700; }
    .stat-trend { font-size: .69rem; display: flex; align-items: center; gap: 3px; margin-top: 2px; }
    .trend-up { color: #16a34a; }
    .trend-dn { color: #dc2626; }
    .trend-n  { color: #64748b; }

    /* ── Card boxes ──────────────────────────────── */
    .card-modern {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
    }
    .card-modern-hd {
        padding: 14px 18px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--border);
    }
    .card-modern-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        font-size: .88rem;
        color: #1e293b;
    }
    .card-icon-sm {
        width: 28px; height: 28px;
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px;
    }
    .card-modern-body { padding: 14px 18px; }

    /* ── Agenda ──────────────────────────────────── */
    .agenda-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
    }
    .agenda-item:last-child { border-bottom: none; }
    .agenda-date { width: 44px; text-align: center; flex-shrink: 0; }
    .agenda-day { font-size: 1.3rem; font-weight: 800; color: var(--primary); line-height: 1; }
    .agenda-mon { font-size: .65rem; font-weight: 700; color: var(--accent); }
    .agenda-body { flex: 1; min-width: 0; text-align: right; }
    .agenda-title { font-size: .85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .agenda-sub { font-size: .75rem; color: #64748b; margin-top: 2px; }

    /* ── Alert rows ──────────────────────────────── */
    .alert-row-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
    }
    .alert-row-item:last-child { border-bottom: none; }
    .alert-dot-sm { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

    /* ── Finance summary ─────────────────────────── */
    .fin-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
    .fin-label-sm { font-size: .82rem; color: #64748b; display: flex; align-items: center; gap: 6px; }
    .fin-val-sm { font-size: .88rem; font-weight: 700; direction: ltr; }
    .fin-bar { height: 6px; border-radius: 3px; background: #e8ecf4; overflow: hidden; margin-bottom: 14px; }
    .fin-bar-inner { height: 100%; border-radius: 3px; }

    /* ── Mini table ──────────────────────────────── */
    .mini-tbl th { font-size: .72rem; color: #64748b; font-weight: 700; padding: 8px 14px; background: #f8fafd; text-align: right; }
    .mini-tbl td { padding: 9px 14px; font-size: .81rem; text-align: right; }

    /* ── Donut center overlay ─────────────────────  */
    .donut-wrap { position: relative; }
    .donut-center {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }
    .donut-center .dc-val { font-size: 1.2rem; font-weight: 800; color: #1e293b; line-height: 1; }
    .donut-center .dc-lab { font-size: .68rem; color: #64748b; font-weight: bold; }
    .legend-dot-sm { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

    /* ── Progress bar paiement ───────────────────── */
    .pct-bar { height: 6px; border-radius: 3px; background: #e8ecf4; overflow: hidden; margin-top: 4px; }
    .pct-fill { height: 100%; border-radius: 3px; }
</style>
@endpush

@section('content')

{{-- ══ HERO BANNER ══ --}}
<div class="hero-banner mb-4" style="direction: rtl;">
    <div style="position:relative;z-index:1; text-align: right;">
        <h2 class="fw-bold mb-1 text-white" style="font-size:1.25rem">
            مرحباً، {{ auth()->user()->name }} 👋
        </h2>
        <p class="mb-3" style="color:rgba(255,255,255,.55);font-size:.82rem">
            {{ now()->translatedFormat('l d F Y') }}
        </p>
        <div class="d-flex flex-wrap gap-2">
            @if($alertes['audiences_proches'] > 0)
            <span style="background:rgba(200,168,75,.15);color:var(--accent);border:1px solid rgba(200,168,75,.25);padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:600">
                <i class="bi bi-calendar-check me-1"></i>{{ $alertes['audiences_proches'] }} جلسة (جلسات) خلال 7 أيام
            </span>
            @endif
            @if($alertes['reclamations_en_attente'] > 0)
            <span style="background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.2);padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:600">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ $alertes['reclamations_en_attente'] }} شكوى (شكاوى) في الانتظار
            </span>
            @endif
            @if($alertes['jugements_non_definitifs'] > 0)
            <span style="background:rgba(255,255,255,.12);color:#e5e7eb;border:1px solid rgba(255,255,255,.2);padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:600">
                <i class="bi bi-clock me-1"></i>{{ $alertes['jugements_non_definitifs'] }} أحكام غير نهائية
            </span>
            @endif
        </div>
    </div>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="row g-3 mb-4" style="direction: rtl;">
    @foreach([
        ['label'=>'إجمالي الملفات',    'value'=>$dossiers['total'],         'icon'=>'bi-folder2-open',     'bg'=>'#e0f2fe','ic'=>'#0369a1', 'trend'=>'12%+ هذا الشهر',       'up'=>true],
        ['label'=>'الملفات النشطة',   'value'=>$dossiers['actifs'],        'icon'=>'bi-activity',         'bg'=>'#dcfce7','ic'=>'#15803d', 'trend'=>'جارية',           'up'=>null],
        ['label'=>'قيد النظر',          'value'=>$dossiers['en_cours'],      'icon'=>'bi-hourglass-split',  'bg'=>'#fef3c7','ic'=>'#b45309', 'trend'=>'مستقر',             'up'=>null],
        ['label'=>'المحكومة',             'value'=>$dossiers['juges'],         'icon'=>'bi-journal-text',     'bg'=>'#ede9fe','ic'=>'#7e22ce', 'trend'=>'5+ هذا الأسبوع',   'up'=>true],
        ['label'=>'الشكايات',      'value'=>$reclamations['total'],     'icon'=>'bi-chat-left-text',   'bg'=>'#fce7f3','ic'=>'#9d174d', 'trend'=>$reclamations['en_attente'].' في الانتظار', 'up'=>false],
        ['label'=>'ملفات التنفيذ',        'value'=>$dossiers['executes'],      'icon'=>'bi-shield-check',     'bg'=>'#dcfce7','ic'=>'#15803d', 'trend'=>'هذا الشهر: '.$dossiers['ce_mois'], 'up'=>null],
    ] as $s)
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card-new text-start">
            <div class="d-flex justify-content-between align-items-start">
                <div class="stat-icon-box" style="background:{{ $s['bg'] }};color:{{ $s['ic'] }}">
                    <i class="bi {{ $s['icon'] }}"></i>
                </div>
            </div>
            <div class="stat-val-big mt-2" style="text-align: right;">{{ $s['value'] }}</div>
            <div class="stat-lbl" style="text-align: right;">{{ $s['label'] }}</div>
            <div class="stat-trend justify-content-end {{ $s['up'] === true ? 'trend-up' : ($s['up'] === false ? 'trend-dn' : 'trend-n') }}">
                {{ $s['trend'] }}
                @if($s['up'] === true)<i class="bi bi-arrow-up-short" style="font-size:14px"></i>
                @elseif($s['up'] === false)<i class="bi bi-arrow-down-short" style="font-size:14px"></i>
                @else<i class="bi bi-dash" style="font-size:14px"></i>@endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ CHARTS ROW ══ --}}
<div class="row g-3 mb-4" style="direction: rtl;">

    {{-- التطور الشهري --}}
    <div class="col-lg-6">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#e0f2fe;color:#0369a1"><i class="bi bi-graph-up"></i></div>
                    التطور الشهري — القضايا المفتوحة
                </div>
            </div>
            <div class="card-modern-body">
                <div style="position:relative;height:220px"><canvas id="chartEvo"></canvas></div>
            </div>
        </div>
    </div>

    {{-- الملفات حسب نوع القضية --}}
    <div class="col-lg-6">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#ede9fe;color:#7e22ce"><i class="bi bi-diagram-3"></i></div>
                    الملفات حسب نوع القضية
                </div>
            </div>
            <div class="card-modern-body">
                <div style="position:relative;height:220px"><canvas id="chartAffaires"></canvas></div>
            </div>
        </div>
    </div>

</div>

{{-- ══ DONUTS ROW ══ --}}
<div class="row g-3 mb-4" style="direction: rtl;">

    {{-- الحالات --}}
    <div class="col-md-4">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#ede9fe;color:#7e22ce"><i class="bi bi-pie-chart"></i></div>
                    التوزيع حسب الحالة
                </div>
            </div>
            <div class="card-modern-body">
                <div class="donut-wrap" style="height:170px">
                    <canvas id="chartStatut"></canvas>
                    <div class="donut-center">
                        <div class="dc-val">{{ $dossiers['total'] }}</div>
                        <div class="dc-lab">الإجمالي</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2 mt-3">
                    @foreach([
                        ['قيد النظر',$dossiers['en_cours'],'#378ADD'],
                        ['محكومة',$dossiers['juges'],'#639922'],
                        ['منفذة',$dossiers['executes'],'#BA7517'],
                        ['أخرى',$dossiers['total']-$dossiers['en_cours']-$dossiers['juges']-$dossiers['executes'],'#888780'],
                    ] as [$lbl,$val,$col])
                    <div class="d-flex align-items-center gap-2" style="font-size:.78rem">
                        <div class="legend-dot-sm" style="background:{{ $col }}"></div>
                        <span class="text-muted" style="flex:1; text-align: right;">{{ $lbl }}</span>
                        <span class="fw-bold">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- نتائج الأحكام --}}
    <div class="col-md-4">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#dcfce7;color:#15803d"><i class="bi bi-feather"></i></div>
                    نتائج الأحكام
                </div>
            </div>
            <div class="card-modern-body">
                <div class="donut-wrap" style="height:170px">
                    <canvas id="chartPourContre"></canvas>
                    @php $pctPour = $resultatsJugements['total'] > 0 ? round($resultatsJugements['pour']/$resultatsJugements['total']*100) : 0; @endphp
                    <div class="donut-center">
                        <div class="dc-val" style="color:#15803d">{{ $pctPour }}%</div>
                        <div class="dc-lab">الأحكام لصالحنا</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2 mt-3">
                    <div class="d-flex align-items-center gap-2" style="font-size:.78rem">
                        <div class="legend-dot-sm" style="background:#639922"></div>
                        <span class="text-muted" style="flex:1; text-align: right;">لصالح المؤسسة</span>
                        <span class="fw-bold text-success">{{ $resultatsJugements['pour'] }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2" style="font-size:.78rem">
                        <div class="legend-dot-sm" style="background:#E24B4A"></div>
                        <span class="text-muted" style="flex:1; text-align: right;">ضد المؤسسة</span>
                        <span class="fw-bold text-danger">{{ $resultatsJugements['contre'] }}</span>
                    </div>
                </div>
                <div class="pct-bar mt-3">
                    <div class="pct-fill" style="width:{{ $pctPour }}%;background:#639922"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- الخلاصة المالية --}}
    <div class="col-md-4">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#fef3c7;color:#b45309"><i class="bi bi-cash-stack"></i></div>
                    الخلاصة المالية
                </div>
            </div>
            <div class="card-modern-body">
                @php
                    $mTotal   = $statsFinancesGraphe['montant_total'];
                    $mPaye    = $statsFinancesGraphe['montant_paye'];
                    $mRestant = $statsFinancesGraphe['montant_restant'];
                    $mPour    = $statsFinancesGraphe['montant_pour'];
                    $mContre  = $statsFinancesGraphe['montant_contre'];
                    $pctPaye  = $mTotal > 0 ? min(100, round($mPaye/$mTotal*100)) : 0;
                    $fmt = fn($v) => $v >= 1000000
                        ? number_format($v/1000000,2,',',' ').' م.د'
                        : number_format($v,0,',',' ').' درهم';
                @endphp

                <div class="fin-row"><span class="fin-label-sm"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#1a3a5c;margin-left:4px"></span>إجمالي المحكوم به</span><span class="fin-val-sm">{{ $fmt($mTotal) }}</span></div>
                <div class="fin-bar"><div class="fin-bar-inner" style="width:100%;background:#1a3a5c"></div></div>

                <div class="fin-row"><span class="fin-label-sm"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#639922;margin-left:4px"></span>المؤدى</span><span class="fin-val-sm" style="color:#15803d">{{ $fmt($mPaye) }}</span></div>
                <div class="fin-bar"><div class="fin-bar-inner" style="width:{{ $pctPaye }}%;background:#639922"></div></div>
                <div class="mb-2 text-muted" style="font-size:.68rem; text-align: left;">تم تحصيل {{ $pctPaye }}%</div>

                <div class="fin-row"><span class="fin-label-sm"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#ef4444;margin-left:4px"></span>المتبقي</span><span class="fin-val-sm" style="color:#dc2626">{{ $fmt($mRestant) }}</span></div>
                <div class="fin-bar"><div class="fin-bar-inner" style="width:{{ 100-$pctPaye }}%;background:#ef4444"></div></div>

                <div class="mt-3 pt-3" style="border-top:1px solid var(--border)">
                    <div class="fin-row mb-1"><span class="fin-label-sm"><i class="bi bi-arrow-up-circle text-success me-1"></i>لصالح المؤسسة</span><span class="fin-val-sm" style="color:#15803d">{{ $fmt($mPour) }}</span></div>
                    <div class="fin-row"><span class="fin-label-sm"><i class="bi bi-arrow-down-circle text-danger me-1"></i>ضد المؤسسة</span><span class="fin-val-sm" style="color:#dc2626">{{ $fmt($mContre) }}</span></div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ══ BOTTOM ROW : AGENDA + ALERTES + DOSSIERS ══ --}}
<div class="row g-3 mb-4" style="direction: rtl;">

    {{-- التنبيهات --}}
    <div class="col-lg-3">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#fef3c7;color:#b45309"><i class="bi bi-exclamation-triangle"></i></div>
                    التنبيهات
                </div>
            </div>
            <div class="card-modern-body">
                <div class="alert-row-item">
                    <div class="alert-dot-sm" style="background:#3b82f6"></div>
                    <div style="flex:1;font-size:.82rem; text-align: right;"><i class="bi bi-calendar-event text-primary me-1"></i>جلسات (7 أيام)</div>
                    <span class="badge rounded-pill" style="background:#e0f2fe;color:#0369a1;font-size:.7rem">{{ $alertes['audiences_proches'] }}</span>
                </div>
                <div class="alert-row-item">
                    <div class="alert-dot-sm" style="background:#f59e0b"></div>
                    <div style="flex:1;font-size:.82rem; text-align: right;"><i class="bi bi-clock text-warning me-1"></i>أحكام غير نهائية</div>
                    <span class="badge rounded-pill" style="background:#fef3c7;color:#92400e;font-size:.7rem">{{ $alertes['jugements_non_definitifs'] }}</span>
                </div>
                <div class="alert-row-item" style="border:none">
                    <div class="alert-dot-sm" style="background:#ef4444"></div>
                    <div style="flex:1;font-size:.82rem; text-align: right;"><i class="bi bi-chat-dots text-danger me-1"></i>شكايات في الانتظار</div>
                    <span class="badge rounded-pill" style="background:#fce7f3;color:#9d174d;font-size:.7rem">{{ $alertes['reclamations_en_attente'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- جدول الجلسات --}}
    <div class="col-lg-5">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#e0f2fe;color:#0369a1"><i class="bi bi-calendar-week"></i></div>
                    الجلسات القادمة — خلال 7 أيام
                </div>
                <a href="{{ route('audiences.index') }}?periode=semaine" style="font-size:.75rem;color:var(--accent);text-decoration:none;font-weight:600">عرض الكل ←</a>
            </div>
            <div class="card-modern-body" style="padding:0">
                @forelse($audiencesAVenir as $aud)
                <div class="agenda-item" style="padding-left:18px;padding-right:18px">
                    <div class="agenda-date">
                        <div class="agenda-day">{{ $aud->date_audience->format('d') }}</div>
                        <div class="agenda-mon">{{ $aud->date_audience->translatedFormat('M') }}</div>
                    </div>
                    <div class="agenda-body">
                        <div class="agenda-title">
                            @if($aud->dossierTribunal?->dossier)
                                <a href="{{ route('dossiers.show', $aud->dossierTribunal->dossier) }}" style="color:#1a3a5c;text-decoration:none">
                                    {{ $aud->dossierTribunal->dossier->numero_dossier_interne }}
                                </a>
                            @else <span class="text-muted">—</span> @endif
                        </div>
                        <div class="agenda-sub">
                            <i class="bi bi-bank me-1"></i>{{ $aud->dossierTribunal?->tribunal?->nom_tribunal ?? '?' }}
                            @if($aud->juge) · <i class="bi bi-person me-1"></i>{{ $aud->juge->nom_complet }}@endif
                        </div>
                    </div>
                    @if($aud->est_today)
                        <span class="badge rounded-pill" style="background:#fef3c7;color:#92400e;font-size:.64rem;white-space:nowrap">اليوم</span>
                    @else
                        <span class="badge rounded-pill" style="background:#dcfce7;color:#166534;font-size:.64rem;white-space:nowrap">
                            متبقي {{ now()->startOfDay()->diffInDays($aud->date_audience->startOfDay()) }} يوم
                        </span>
                    @endif
                </div>
                @empty
                <div class="text-center py-4 text-muted" style="font-size:.82rem">
                    <i class="bi bi-calendar-check d-block mb-1" style="font-size:1.8rem;opacity:.3"></i>
                    لا توجد أي جلسة خلال السبعة أيام القادمة
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- آخر الملفات --}}
    <div class="col-lg-4">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#f1f5f9;color:#475569"><i class="bi bi-clock-history"></i></div>
                    آخر الملفات المنشأة
                </div>
                <a href="{{ route('dossiers.index') }}" style="font-size:.75rem;color:var(--accent);text-decoration:none;font-weight:600">عرض الكل ←</a>
            </div>
            <div style="overflow:hidden">
                <table class="table table-hover mb-0 mini-tbl">
                    <thead>
                        <tr>
                            <th>الرقم الداخلي</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($derniersDossiers as $d)
                        <tr>
                            <td>
                                <a href="{{ route('dossiers.show', $d) }}" class="fw-semibold text-decoration-none" style="color:#1a3a5c;font-size:.8rem">
                                    {{ $d->numero_dossier_interne }}
                                </a>
                            </td>
                            <td class="text-muted" style="font-size:.75rem">{{ Str::limit($d->typeAffaire?->affaire ?? '—', 12) }}</td>
                            <td>
                                @php
                                    $s = $d->statut?->statut_dossier ?? '—';
                                    
                                    // Adaptations des labels arabes selon le contenu attendu
                                    $s_ar = match(true) {
                                        str_contains($s,'cours')   => 'قيد النظر',
                                        str_contains($s,'Clôturé') => 'مغلق',
                                        str_contains($s,'Jugé')    => 'محكوم',
                                        str_contains($s,'xécut')   => 'منفذ',
                                        default                    => $s,
                                    };

                                    [$bg,$col] = match(true) {
                                        str_contains($s,'cours')   => ['#fef3c7','#92400e'],
                                        str_contains($s,'Clôturé') => ['#f1f5f9','#64748b'],
                                        str_contains($s,'Jugé')    => ['#e0f2fe','#075985'],
                                        str_contains($s,'xécut')   => ['#dcfce7','#166534'],
                                        default                    => ['#ede9fe','#6b21a8'],
                                    };
                                @endphp
                                <span style="background:{{ $bg }};color:{{ $col }};padding:3px 8px;border-radius:20px;font-size:.64rem;font-weight:700;white-space:nowrap">{{ $s_ar }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ══ التطور المالي ══ --}}
<div class="card-modern mb-4" style="direction: rtl;">
    <div class="card-modern-hd">
        <div class="card-modern-title">
            <div class="card-icon-sm ms-2" style="background:#dcfce7;color:#15803d"><i class="bi bi-graph-up-arrow"></i></div>
            التطور المالي — 12 شهراً الأخيرة
        </div>
    </div>
    <div class="card-modern-body">
        <div style="position:relative;height:260px"><canvas id="chartFinancesMensuel"></canvas></div>
    </div>
</div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
<script>
(function () {
    const BLUE   = '#378ADD';
    const GREEN  = '#639922';
    const AMBER  = '#BA7517';
    const GRAY   = '#888780';
    const RED    = '#E24B4A';

    const evoLabels = {!! json_encode($evolutionMois['labels'] ?? []) !!};
    const evoVals   = {!! json_encode($evolutionMois['values'] ?? []) !!};

    const affLabels = {!! json_encode($dossiersParAffaire['labels']) !!};
    const affVals   = {!! json_encode($dossiersParAffaire['values']) !!};

    const finLabels = {!! json_encode($statsFinancesGraphe['mensuel_labels']) !!};
    const finVals   = {!! json_encode($statsFinancesGraphe['mensuel_values']) !!};

    const dossEnCours  = {{ $dossiers['en_cours'] }};
    const dossJuges    = {{ $dossiers['juges'] }};
    const dossExecutes = {{ $dossiers['executes'] }};
    const dossTotal    = {{ $dossiers['total'] }};
    const dossAutres   = Math.max(0, dossTotal - dossEnCours - dossJuges - dossExecutes);

    const pourVal    = {{ $resultatsJugements['pour'] }};
    const contreVal  = {{ $resultatsJugements['contre'] }};

    const defaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
    };

    /* Configuration globale Chart.js pour RTL */
    Chart.defaults.rtl = true;

    /* Évolution mensuelle dossiers */
    new Chart(document.getElementById('chartEvo'), {
        type: 'line',
        data: {
            labels: evoLabels,
            datasets: [{
                data: evoVals,
                borderColor: BLUE,
                backgroundColor: 'rgba(55,138,221,0.08)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: BLUE,
                fill: true,
                tension: .35,
            }]
        },
        options: {
            ...defaults,
            scales: {
                x: { ticks: { font: { size: 10 }, maxRotation: 30 }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }
            }
        }
    });

    /* Dossiers par type d'affaire */
    new Chart(document.getElementById('chartAffaires'), {
        type: 'bar',
        data: {
            labels: affLabels,
            datasets: [{
                data: affVals,
                backgroundColor: BLUE,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            ...defaults,
            scales: {
                x: { ticks: { font: { size: 10 } }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }
            }
        }
    });

    /* Donut statuts */
    new Chart(document.getElementById('chartStatut'), {
        type: 'doughnut',
        data: {
            labels: ['قيد النظر', 'محكومة', 'منفذة', 'أخرى'],
            datasets: [{
                data: [dossEnCours, dossJuges, dossExecutes, dossAutres],
                backgroundColor: [BLUE, GREEN, AMBER, GRAY],
                borderWidth: 0,
                hoverOffset: 5,
            }]
        },
        options: {
            cutout: '72%',
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        }
    });

    /* Donut pour/contre */
    new Chart(document.getElementById('chartPourContre'), {
        type: 'doughnut',
        data: {
            labels: ['لصالح المؤسسة', 'ضد المؤسسة'],
            datasets: [{
                data: [pourVal, contreVal],
                backgroundColor: [GREEN, RED],
                borderWidth: 0,
                hoverOffset: 5,
            }]
        },
        options: {
            cutout: '72%',
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        }
    });

    /* Évolution financière mensuelle */
    new Chart(document.getElementById('chartFinancesMensuel'), {
        type: 'line',
        data: {
            labels: finLabels,
            datasets: [{
                data: finVals,
                borderColor: GREEN,
                backgroundColor: 'rgba(99,153,34,0.08)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: GREEN,
                fill: true,
                tension: .35,
            }]
        },
        options: {
            ...defaults,
            scales: {
                x: { ticks: { font: { size: 10 }, maxRotation: 30 }, grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 10 },
                        callback: v => v >= 1000000 ? (v/1000000).toFixed(1)+'M' : (v >= 1000 ? (v/1000).toFixed(0)+'K' : v)
                    },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => ` ${Number(c.raw).toLocaleString('fr-MA')} درهم` } }
            }
        }
    });
})();
</script>

@endpush