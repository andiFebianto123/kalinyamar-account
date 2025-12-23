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

<li class="nav-item">
    <a class="nav-link {{ $active ? 'active':'' }}" href="{{$link}}">
        <i class="nav-icon la la-circle-notch"></i> <span>{{$title}}</span>
    </a>
</li>
