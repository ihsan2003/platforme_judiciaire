{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'لوحة التحكم')


@push('styles')
    @vite('resources/css/dashboard.css')
@endpush

@section('content')

{{-- ══ HERO BANNER ══ --}}
<div class="hero-banner mb-4" style="direction: rtl; background-image: url('{{ asset('images/dashboard-bg.jpg') }}');">
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
            @if($alertes['reclamations_en_cours'] > 0)
            <span style="background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.2);padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:600">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ $alertes['reclamations_en_cours'] }} شكاية قيد المعالجة
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
    @php
        $upTotal = $dossiers['croissance_pct'] > 0 ? true : ($dossiers['croissance_pct'] < 0 ? false : null);
    @endphp
    @foreach([
        ['label'=>'إجمالي الملفات',    'value'=>$dossiers['total'],         'icon'=>'bi-folder2-open',     'bg'=>'#e0f2fe','ic'=>'#0369a1', 'trend_sign'=>($dossiers['croissance_pct'] >= 0 ? '+' : '-'), 'trend_num'=>abs($dossiers['croissance_pct']),     'trend_suffix'=>'% هذا الشهر',   'up'=>$upTotal,                    'arrow'=>$upTotal],
        ['label'=>'الملفات النشطة',   'value'=>$dossiers['actifs'],        'icon'=>'bi-activity',         'bg'=>'#dcfce7','ic'=>'#15803d', 'trend_sign'=>null, 'trend_num'=>$dossiers['actifs_ce_mois'],       'trend_suffix'=>' هذا الشهر',    'up'=>$dossiers['up_actifs'],      'arrow'=>$dossiers['up_actifs']],
        ['label'=>'قيد النظر',          'value'=>$dossiers['en_cours'],      'icon'=>'bi-hourglass-split',  'bg'=>'#fef3c7','ic'=>'#b45309', 'trend_sign'=>null, 'trend_num'=>$dossiers['en_cours_ce_mois'],     'trend_suffix'=>' هذا الشهر',    'up'=>$dossiers['up_en_cours'],    'arrow'=>$dossiers['up_en_cours']],
        ['label'=>'المحكومة',             'value'=>$dossiers['juges'],         'icon'=>'bi-journal-text',     'bg'=>'#ede9fe','ic'=>'#7e22ce', 'trend_sign'=>null, 'trend_num'=>$dossiers['jugements_semaine'],    'trend_suffix'=>' هذا الأسبوع',  'up'=>$dossiers['up_jugements'],   'arrow'=>$dossiers['up_jugements']],
        ['label'=>'الشكايات',      'value'=>$reclamations['total'],     'icon'=>'bi-chat-left-text',   'bg'=>'#fce7f3','ic'=>'#9d174d', 'trend_sign'=>null, 'trend_num'=>$reclamations['ce_mois'],          'trend_suffix'=>' هذا الشهر',    'up'=>$reclamations['up_pct'],     'arrow'=>$reclamations['arrow_pct']],
        ['label'=>'ملفات التنفيذ',        'value'=>$dossiers['executions'],    'icon'=>'bi-shield-check',     'bg'=>'#dcfce7','ic'=>'#15803d', 'trend_sign'=>null, 'trend_num'=>$dossiers['executions_ce_mois'],   'trend_suffix'=>' هذا الشهر',    'up'=>$dossiers['up_executions'],  'arrow'=>$dossiers['up_executions']],
    ] as $s)
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card-new text-start">
            <div class="d-flex justify-content-between align-items-start">
                <div class="stat-icon-box" style="background:{{ $s['bg'] }};color:{{ $s['ic'] }}">
                    <i class="bi {{ $s['icon'] }}"></i>
                </div>
            </div>
            <div class="stat-val-big js-counter mt-2" style="text-align: right;" data-count-to="{{ $s['value'] }}">
                <span class="counter-num fade-val">0</span>
                <span class="flip-num" style="display:none"></span>
            </div>
            <div class="stat-lbl" style="text-align: right;">{{ $s['label'] }}</div>
            <div class="stat-trend justify-content-end {{ $s['up'] === true ? 'trend-up' : ($s['up'] === false ? 'trend-dn' : 'trend-n') }}">
                @if(!is_null($s['trend_sign']))<span class="trend-sign">{{ $s['trend_sign'] }}</span>@endif
                <span class="js-counter trend-counter" data-count-to="{{ $s['trend_num'] }}">
                    <span class="counter-num fade-val">0</span>
                    <span class="flip-num" style="display:none"></span>
                </span>{{ $s['trend_suffix'] }}
                @if($s['arrow'] === true)<i class="bi bi-arrow-up-short" style="font-size:14px"></i>
                @elseif($s['arrow'] === false)<i class="bi bi-arrow-down-short" style="font-size:14px"></i>
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
    <div class="col-md-6 col-lg-4">
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
                        ['نشطة',$dossiers['actifs'],'#378ADD'],
                        ['محكومة',$dossiers['juges'],'#639922'],
                        ['محفوظة',max(0, $dossiers['total']-$dossiers['actifs']-$dossiers['juges']),'#888780'],
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
    <div class="col-md-6 col-lg-4">
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
                        <span class="text-muted" style="flex:1; text-align: right;">لصالح المؤسسة (مع)</span>
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
    <div class="col-md-6 col-lg-4">
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

{{-- ══ AVOCAT & RÉCLAMATIONS ROW ══ --}}
<div class="row g-3 mb-4" style="direction: rtl;">

    {{-- حضور المحامي في الجلسات --}}
    <div class="col-md-6 col-lg-4">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#dcfce7;color:#15803d"><i class="bi bi-person-check"></i></div>
                    حضور محامي المؤسسة في الجلسات                
                    </div>
            </div>
            <div class="card-modern-body">
                <div class="donut-wrap" style="height:170px">
                    <canvas id="chartAvocatPresence"></canvas>
                    <div class="donut-center">
                        <div class="dc-val" style="color:#15803d">{{ $statsPresenceAvocat['pct_present'] }}%</div>
                        <div class="dc-lab">نسبة الحضور</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2 mt-3">
                    <div class="d-flex align-items-center gap-2" style="font-size:.78rem">
                        <div class="legend-dot-sm" style="background:#639922"></div>
                        <span class="text-muted" style="flex:1; text-align: right;">حاضر</span>
                        <span class="fw-bold text-success">{{ $statsPresenceAvocat['present'] }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2" style="font-size:.78rem">
                        <div class="legend-dot-sm" style="background:#E24B4A"></div>
                        <span class="text-muted" style="flex:1; text-align: right;">غائب</span>
                        <span class="fw-bold text-danger">{{ $statsPresenceAvocat['absent'] }}</span>
                    </div>
                </div>
                <div class="text-muted mt-2" style="font-size:.68rem">
                    (الجلسات المنعقدة فقط — {{ $statsPresenceAvocat['total'] }} جلسة)
                </div>
            </div>
        </div>
    </div>

    {{-- الشكايات حسب الحالة --}}
    <div class="col-md-6 col-lg-4">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#fce7f3;color:#9d174d"><i class="bi bi-chat-left-text"></i></div>
                    الشكايات حسب الحالة
                </div>
            </div>
            <div class="card-modern-body">
                <div class="donut-wrap" style="height:170px">
                    <canvas id="chartReclamationsStatut"></canvas>
                    <div class="donut-center">
                        <div class="dc-val">{{ $reclamations['total'] }}</div>
                        <div class="dc-lab">الإجمالي</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2 mt-3">
                    @foreach([
                        ['قيد المعالجة',$reclamations['en_cours'],'#BA7517'],
                        ['تمت المعالجة',$reclamations['traitees'],'#639922'],
                        ['مغلقة',$reclamations['cloturees'],'#888780'],
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

    {{-- الشكايات حسب النوع --}}
    <div class="col-md-6 col-lg-4">
        <div class="card-modern h-100">
            <div class="card-modern-hd">
                <div class="card-modern-title">
                    <div class="card-icon-sm ms-2" style="background:#e0f2fe;color:#0369a1"><i class="bi bi-tags"></i></div>
                    الشكايات حسب النوع
                </div>
            </div>
            <div class="card-modern-body">
                <div style="position:relative;height:220px"><canvas id="chartReclamationsType"></canvas></div>
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
                    <div style="flex:1;font-size:.82rem; text-align: right;"><i class="bi bi-chat-dots text-danger me-1"></i>شكايات قيد المعالجة</div>
                    <span class="badge rounded-pill" style="background:#fce7f3;color:#9d174d;font-size:.7rem">{{ $alertes['reclamations_en_cours'] }}</span>
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
                                    {{ $aud->dossierTribunal->dossier->numero_dossier_tribunal }}
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
                                    {{ $d->numero_dossier_tribunal }}
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

<div class="card-modern mb-4">
    <div class="card-modern-hd">
        <div class="card-modern-title">
            <div class="card-icon-sm ms-2" style="background:rgba(200,168,75,.15);color:var(--accent)"><i class="bi bi-map"></i></div>
            توزيع الملفات حسب الجهة
        </div>
        {{-- Légende --}}
        <div id="map-legend" class="d-flex align-items-center gap-2" style="font-size:.72rem;color:#64748b"></div>
    </div>

    <div style="padding:0">
        <div id="morocco-map-wrapper" dir="ltr"
             style="position:relative; width:100%; height:460px; background:#f8fafd; overflow:hidden;">

            {{-- Tooltip --}}
            <div id="map-tooltip"
                 style="
                    position:absolute; pointer-events:none; z-index:20;
                    background:rgba(15,23,42,.92); color:#fff;
                    padding:10px 14px; border-radius:8px;
                    font-size:13px; min-width:190px;
                    box-shadow:0 4px 16px rgba(0,0,0,.25);
                    display:none;
                 ">
            </div>

            {{-- Spinner pendant le chargement --}}
            <div id="map-loader"
                 class="d-flex align-items-center justify-content-center h-100 w-100 position-absolute top-0 start-0"
                 style="z-index:10; background:#f8fafd;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">تحميل...</span>
                </div>
            </div>

            <svg id="morocco-map" width="100%" height="100%"></svg>
        </div>

        {{-- Tableau récapitulatif --}}
        <div class="px-3 py-3" style="border-top:1px solid var(--border)">
            <div class="table-responsive">
                <table class="table table-hover align-middle mini-tbl mb-0">
                    <thead>
                        <tr>
                            <th>الجهة</th>
                            <th class="text-center">عدد الملفات</th>
                            <th class="text-center">المحاكم</th>
                            <th style="min-width:120px">النسبة</th>
                        </tr>
                    </thead>
                    <tbody id="map-table-body">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                جاري التحميل...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
<script>
    window.dashboardChartData = {
        evoLabels: {!! json_encode($evolutionMois['labels'] ?? []) !!},
        evoVals:   {!! json_encode($evolutionMois['values'] ?? []) !!},
        affLabels: {!! json_encode($dossiersParAffaire['labels']) !!},
        affVals:   {!! json_encode($dossiersParAffaire['values']) !!},
        finLabels: {!! json_encode($statsFinancesGraphe['mensuel_labels']) !!},
        finVals:   {!! json_encode($statsFinancesGraphe['mensuel_values']) !!},
        dossActifs: {{ $dossiers['actifs'] }},
        dossJuges:  {{ $dossiers['juges'] }},
        dossTotal:  {{ $dossiers['total'] }},
        pourVal:    {{ $resultatsJugements['pour'] }},
        contreVal:  {{ $resultatsJugements['contre'] }},
        avocatPresentVal: {{ $statsPresenceAvocat['present'] }},
        avocatAbsentVal:  {{ $statsPresenceAvocat['absent'] }},
        reclamEnCoursVal:  {{ $reclamations['en_cours'] }},
        reclamTraiteesVal: {{ $reclamations['traitees'] }},
        reclamClotureesVal:{{ $reclamations['cloturees'] }},
        reclamTypeLabels: {!! json_encode($reclamationsParType['labels']) !!},
        reclamTypeVals:   {!! json_encode($reclamationsParType['values']) !!},
    };
</script>
@vite('resources/js/dashboard.js')

@endpush

@once
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
<script>
    window.dashboardMapData = {
        apiUrl: "{{ route('dashboard.map.data') }}",
    };
</script>
@vite('resources/js/dashboard-map.js')
@endpush
@endonce