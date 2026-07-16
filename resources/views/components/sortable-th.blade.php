@props(['column', 'class' => ''])

@php
    $currentSort = request('sort');
    $currentDirection = request('direction', 'desc');
    $isActive = $currentSort === $column;
    $nextDirection = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';

    $queryParams = array_merge(
        request()->except(['sort', 'direction', 'page']),
        ['sort' => $column, 'direction' => $nextDirection]
    );
@endphp

<th {{ $attributes->merge(['class' => $class]) }}>
    <a href="{{ request()->fullUrlWithQuery($queryParams) }}"
       class="d-inline-flex align-items-center gap-1 text-decoration-none text-reset sortable-th {{ $isActive ? 'fw-bold text-primary' : '' }}">
        <span>{{ $slot }}</span>

        @if($isActive)
            <i class="bi bi-caret-{{ $currentDirection === 'asc' ? 'up' : 'down' }}-fill small"></i>
        @else
            <i class="bi bi-arrow-down-up small text-muted opacity-25"></i>
        @endif
    </a>
</th>