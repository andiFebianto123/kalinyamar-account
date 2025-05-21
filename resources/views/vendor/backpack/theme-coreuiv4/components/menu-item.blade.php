<li class="nav-item nav-root">
    <a {{ $attributes->merge(['class' => 'nav-link', 'href' => $link]) }}>
        @if ($icon != null)<i class="dash-micon {{ $icon }}"></i>@endif
        @if ($title != null)<span>{{ $title }}</span>@endif
    </a>
</li>
