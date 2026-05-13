<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'المنصة القانونية') — التعاون الوطني</title>

    {{-- Bootstrap + Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    {{-- Arabic Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #1a3a5c;
            --accent: #c8a84b;
        }

        body {
            background: #f0f2f5;
            font-family: 'Cairo', sans-serif;
        }

        /* Sidebar */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary);
            position: fixed;
            top: 0;
            right: 0;
            z-index: 1000;
            transition: width .25s;
            overflow-x: hidden;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            scrollbar-width: thin;
            scrollbar-color: rgba(200,168,75,0.6) var(--primary);
        }

        #sidebar::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar::-webkit-scrollbar-track {
            background: var(--primary);
        }

        #sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(200,168,75,0.6);
            border-radius: 10px;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,.1);
            background: var(--primary);
        }

        .btn-logout {
            background: rgba(200,168,75,.08);
            color: var(--accent);
            border: 1px solid rgba(200,168,75,.25);
            padding: 10px;
            border-radius: 10px;
            font-size: .9rem;
            transition: .25s;
        }

        .btn-logout:hover {
            background: var(--accent);
            color: var(--primary);
        }

        .sidebar-brand {
            padding: 20px 16px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;
            background: var(--accent);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 17px;
        }

        .sidebar-brand-text {
            color: #fff;
            font-weight: 700;
            font-size: .9rem;
        }

        .sidebar-brand-sub {
            color: rgba(255,255,255,.45);
            font-size: .7rem;
        }

        #sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: .6rem 1.2rem;
            border-radius: 6px;
            margin: 2px 8px;
            font-size: .88rem;
            transition: .2s;
        }

        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(200,168,75,.2);
            color: var(--accent);
        }

        #sidebar .nav-link i {
            margin-left: 8px;
        }

        #sidebar .nav-section {
            color: rgba(255,255,255,.4);
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: .8rem 1.2rem .2rem;
        }

        /* Main */
        #main-content {
            margin-right: var(--sidebar-width);
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

        .breadcrumb {
            direction: rtl;
        }

        .content-wrapper {
            padding: 1.5rem;
            flex: 1;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        .card-header {
            border-bottom: 1px solid #f0f0f0;
            background: #fff;
        }

        .alert {
            border-radius: 10px;
        }

        .table th {
            background: #f8f9fa;
            font-size: .82rem;
        }

        .table td {
            vertical-align: middle;
            font-size: .9rem;
        }

        /* User Dropdown */
        .user-trigger {
            background: #fff;
            border: 1px solid #e0e6ef;
            border-radius: 7px;
            cursor: pointer;
        }

        .user-avatar-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }

        .user-dropdown-panel {
            width: 260px;
            border-radius: 14px !important;
            border: 1px solid #e8ecf4 !important;
            overflow: hidden;
        }

        .user-dropdown-header,
        .user-dropdown-item {
            direction: rtl;
        }

        .user-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            color: #1e293b;
            transition: .15s;
        }

        .user-dropdown-item:hover {
            background: #f4f6fa;
        }

        .user-item-icon {
            width: 28px;
            height: 28px;
            background: #f4f6fa;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {
            #sidebar {
                width: 0;
            }

            #main-content {
                margin-right: 0;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

{{-- Sidebar --}}
<nav id="sidebar">

    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-bank2"></i>
        </div>

        <div>
            <div class="sidebar-brand-text">المنصة القانونية</div>
            <div class="sidebar-brand-sub">التعاون الوطني</div>
        </div>
    </div>

    <div class="sidebar-content">

        <ul class="nav flex-column mt-2">

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    لوحة التحكم
                </a>
            </li>

            <div class="nav-section">الملفات</div>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dossiers.*') ? 'active' : '' }}"
                   href="{{ route('dossiers.index') }}">
                    <i class="bi bi-folder2-open"></i>
                    الملفات القضائية
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('audiences.*') ? 'active' : '' }}"
                   href="{{ route('audiences.index') }}">
                    <i class="bi bi-calendar-event"></i>
                    الجلسات
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('jugements.*') ? 'active' : '' }}"
                   href="{{ route('jugements.index') }}">
                    <i class="bi bi-hammer"></i>
                    الأحكام
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('executions.*') ? 'active' : '' }}"
                   href="{{ route('executions.index') }}">
                    <i class="bi bi-check2-circle"></i>
                    التنفيذ
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('finances.*') ? 'active' : '' }}"
                   href="{{ route('finances.index') }}">
                    <i class="bi bi-cash-stack"></i>
                    الشؤون المالية
                </a>
            </li>

            <div class="nav-section">الشكايات</div>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reclamations.*') ? 'active' : '' }}"
                   href="{{ route('reclamations.index') }}">
                    <i class="bi bi-envelope-exclamation"></i>
                    الشكايات
                </a>
            </li>

            <div class="nav-section">المراجع</div>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('parties.*') ? 'active' : '' }}"
                   href="{{ route('parties.index') }}">
                    <i class="bi bi-people"></i>
                    الأطراف
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('avocats.*') ? 'active' : '' }}"
                   href="{{ route('avocats.index') }}">
                    <i class="bi bi-person-badge"></i>
                    المحامون
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('tribunaux.*') ? 'active' : '' }}"
                   href="{{ route('tribunaux.index') }}">
                    <i class="bi bi-building"></i>
                    المحاكم
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('juges.*') ? 'active' : '' }}"
                   href="{{ route('juges.index') }}">
                    <i class="bi bi-person-workspace"></i>
                    القضاة
                </a>
            </li>

            @can('manage users')

            <div class="nav-section">الإدارة</div>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                   href="{{ route('profile.edit') }}">
                    <i class="bi bi-person-circle"></i>
                    ملفي الشخصي
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                   href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people"></i>
                    المستخدمون
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.structures.*') ? 'active' : '' }}"
                   href="{{ route('admin.structures.index') }}">
                    <i class="bi bi-diagram-3"></i>
                    الهيكلة التنظيمية
                </a>
            </li>

            @endcan

        </ul>
    </div>

    {{-- Logout --}}
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn-logout w-100">
                <i class="bi bi-box-arrow-right ms-2"></i>
                تسجيل الخروج
            </button>
        </form>
    </div>
</nav>

{{-- Main --}}
<div id="main-content">

    {{-- Topbar --}}
    <div id="topbar" class="d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center gap-2">

            <button class="btn btn-sm btn-light d-md-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>

        {{-- User --}}
        @php
            $initials = collect(explode(' ', auth()->user()->name))
                ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                ->take(2)
                ->implode('');
        @endphp

        <div class="dropdown">

            <button class="btn p-0"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">

                <div class="d-flex align-items-center gap-2 px-3 py-1 user-trigger">

                    <div class="user-avatar-circle">
                        {{ $initials }}
                    </div>

                    <span class="d-none d-md-inline">
                        {{ auth()->user()->name }}
                    </span>

                    <i class="bi bi-chevron-down"></i>
                </div>
            </button>

            <div class="dropdown-menu dropdown-menu-start user-dropdown-panel shadow-sm">

                <div class="p-3 border-bottom">
                    <strong>{{ auth()->user()->name }}</strong>
                    <div class="text-muted small">
                        {{ auth()->user()->email }}
                    </div>
                </div>

                <div class="p-2">

                    <a href="{{ route('profile.edit') }}"
                       class="user-dropdown-item">

                        <span class="user-item-icon">
                            <i class="bi bi-person"></i>
                        </span>

                        <span>ملفي الشخصي</span>
                    </a>

                    <form method="POST"
                          action="{{ route('logout') }}">

                        @csrf

                        <button type="submit"
                                class="user-dropdown-item border-0 bg-transparent w-100">

                            <span class="user-item-icon">
                                <i class="bi bi-box-arrow-right"></i>
                            </span>

                            <span>تسجيل الخروج</span>
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="content-wrapper">

        {{-- Success --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">

                <i class="bi bi-check-circle ms-2"></i>

                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Error --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">

                <i class="bi bi-exclamation-circle ms-2"></i>

                {{ session('error') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())

            <div class="alert alert-danger alert-dismissible fade show">

                <i class="bi bi-exclamation-triangle ms-2"></i>

                <strong>أخطاء التحقق :</strong>

                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>

        @endif

        @yield('content')

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', function () {

        const sidebar = document.getElementById('sidebar');

        sidebar.style.width =
            sidebar.style.width === '260px'
                ? '0'
                : '260px';
    });
</script>

@stack('scripts')

</body>
</html>