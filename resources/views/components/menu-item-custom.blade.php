
@props([
    'link' => '#',
    'activeClass' => 'active',
    'title' => '',
    'logo_url' => ''
])

@php
    $currentUrl = request()->fullUrl();
    $expectedBase = $link;
    $active = false;

    if (Str::startsWith($currentUrl, $expectedBase)) {
        $active = true;
    }
@endphp

<li class="nav-item nav-root {{ $active ? 'active':'' }}">
    <a class="nav-link" href="{{$link}}">
        <i class="dash-micon">
            <img src="{{$logo_url}}" alt="{{$title}}" width="18px" height="18px">
        </i> <span>{{$title}}</span>
    </a>
</li>
