{{-- =================================================== --}}
{{-- ========== Top menu items (ordered left) ========== --}}
{{-- =================================================== --}}
<ul class="header-nav d-none d-lg-flex">

    @if (backpack_auth()->check())
        {{-- Topbar. Contains the left part --}}
        @include(backpack_view('inc.topbar_left_content'))
    @endif

</ul>

<ul class="header-nav custom-flex">
    <li class="nav-item">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" style="overflow:hidden; border-radius: 9px;" data-coreui-toggle="dropdown" aria-expanded="false">
                <a class="nav-link p-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" style="position: relative;width: 35px;height: 35px; float:left;">
                    <img class="avatar-img" src="{{ backpack_avatar_url(backpack_auth()->user()) }}" alt="{{ backpack_auth()->user()->name }}" onerror="this.style.display='none'" style="margin: 0;position: absolute;left: 0;z-index: 1;">
                    <span class="backpack-avatar-menu-container text-center" style="position: absolute;left: 0;width: 100%;background-color: #00a65a;border-radius: 50%;color: #FFF;line-height: 35px;font-size: 85%;font-weight: 300;">
                    {{backpack_user()->getAttribute('name') ? mb_substr(backpack_user()->name, 0, 1, 'UTF-8') : 'A'}}
                    </span>
                </a>
                <span style="float: left; margin-left: 10px; margin-right: 10px; line-height: 32px;">Hi, {{ backpack_user()->name }}</span>
            </button>
            <ul class="dropdown-menu">
                @if(config('backpack.base.setup_my_account_routes'))
                    <li><a class="dropdown-item" href="{{ route('backpack.account.info') }}"><i class="la la-user"></i> Edit Profile</a></li>
                    <div class="dropdown-divider"></div>
                @endif
                <li><a class="dropdown-item" href="{{ backpack_url('logout') }}"><i class="la la-lock"></i> {{ trans('backpack::base.logout') }}</a></li>
            </ul>
        </div>
    </li>
</ul>
{{-- ========== End of top menu left items ========== --}}



{{-- ========================================================= --}}
{{-- ========= Top menu right items (ordered right) ========== --}}
{{-- ========================================================= --}}
<ul class="header-nav ms-auto @if(backpack_theme_config('html_direction') == 'rtl') mr-0 @endif">
    @if (backpack_auth()->guest())
        <li class="nav-item"><a class="nav-link" href="{{ route('backpack.auth.login') }}">{{ trans('backpack::base.login') }}</a>
        </li>
        @if (config('backpack.base.registration_open'))
            <li class="nav-item"><a class="nav-link" href="{{ route('backpack.auth.register') }}">{{ trans('backpack::base.register') }}</a></li>
        @endif
    @else
        {{-- Topbar. Contains the right part --}}
        @include(backpack_view('inc.topbar_right_content'))
        {{-- @include(backpack_view('inc.menu_user_dropdown')) --}}
    @endif
</ul>
{{-- ========== End of top menu right items ========== --}}
