@php
    $currentUrl = request()->fullUrl();
    $expectedBase = $link;
    $active = false;

    if (Str::startsWith($currentUrl, $expectedBase)) {
        $active = true;
    }
@endphp
<li class="nav-group nav-root {{ $active ? 'active':'' }} {{ $open ? 'show' : '' }}">
    <a {{ $attributes->merge([ 'class' => 'nav-link nav-group-toggle', 'href' => $link ?? '#' ]) }}>
        @if($icon != null)<span class="dash-micon"><i class="{{ $icon }}"></i></span>@endif
        @if($title != null) <span>{{ $title }}</span>@endif
    </a>
    <ul class="nav-dropdown-items nav-group-items">
        {!! $slot !!}
    </ul>
</li>
