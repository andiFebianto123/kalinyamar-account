{{-- This file is used for menu items by any Backpack v6 theme --}}
{{-- <li class="nav-item nav-root active"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li> --}}
<x-backpack::menu-item title="Dashboard" icon="la la-home" :link="backpack_url('dashboard')" />

<x-backpack::menu-dropdown title="Vendor(Subkon)" icon="la la-group" :link="backpack_url('vendor')">
    <x-backpack::menu-dropdown-item title="Daftar Subkon" icon="la la-circle-notch" :link="backpack_url('vendor/subkon')" />
    <x-backpack::menu-dropdown-item title="PO" icon="la la-circle-notch" :link="backpack_url('vendor/purchase-order')" />
</x-backpack::menu-dropdown>


<x-backpack::menu-dropdown title="Authentication" icon="la la-group" :link="backpack_url('auth')">
    <x-backpack::menu-dropdown-item title="Users" icon="la la-circle-notch" :link="backpack_url('auth/user')" />
    <x-backpack::menu-dropdown-item title="Roles" icon="la la-circle-notch" :link="backpack_url('auth/role')" />
    <x-backpack::menu-dropdown-item title="Permissions" icon="la la-circle-notch" :link="backpack_url('auth/permission')" />

    {{-- <x-backpack::menu-dropdown title="Authentication Sub" icon="la la-group">
        <x-backpack::menu-dropdown-item title="Users" icon="la la-user" :link="backpack_url('auth/user')" />
        <x-backpack::menu-dropdown-item title="Roles" icon="la la-group" :link="backpack_url('role')" />
    </x-backpack::menu-dropdown> --}}
</x-backpack::menu-dropdown>

{{-- <li class="nav-group" aria-expanded="false"><a class="nav-link nav-group-toggle" href="#">
    <i class="la la-home nav-icon"></i> Hallo andi </a>
  <ul class="nav-group-items compact" style="height: 0px;">
    <li class="nav-item"><a class="nav-link" href="base/accordion.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Accordion</a></li>
    <li class="nav-item"><a class="nav-link" href="base/breadcrumb.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Breadcrumb</a></li>
    <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/calendar/" target="_blank"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Calendar
        <svg class="icon icon-sm ms-2">
          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-external-link"></use>
        </svg><span class="badge badge-sm bg-danger ms-auto">PRO</span></a></li>
    <li class="nav-item"><a class="nav-link" href="base/cards.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Cards</a></li>
    <li class="nav-item"><a class="nav-link" href="base/carousel.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Carousel</a></li>
    <li class="nav-item"><a class="nav-link" href="base/collapse.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Collapse</a></li>
    <li class="nav-item"><a class="nav-link" href="base/list-group.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> List group</a></li>
    <li class="nav-item"><a class="nav-link" href="base/navs-tabs.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Navs &amp; Tabs</a></li>
    <li class="nav-item"><a class="nav-link" href="base/pagination.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Pagination</a></li>
    <li class="nav-item"><a class="nav-link" href="base/placeholders.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Placeholders</a></li>
    <li class="nav-item"><a class="nav-link" href="base/popovers.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Popovers</a></li>
    <li class="nav-item"><a class="nav-link" href="base/progress.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Progress</a></li>
    <li class="nav-item"><a class="nav-link" href="base/spinners.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Spinners</a></li>

    <li class="nav-group" aria-expanded="false"><a class="nav-link nav-group-toggle" href="#">
        <i class="nav-icon la la-puzzle-piece"></i> Icons</a>
      <ul class="nav-group-items compact" style="height: 0px;">
        <li class="nav-item"><a class="nav-link" href="icons/coreui-icons-free.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> CoreUI Free</a></li>
        <li class="nav-item"><a class="nav-link" href="icons/coreui-icons-brand.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> CoreUI Brand</a></li>
        <li class="nav-item"><a class="nav-link" href="icons/coreui-icons-flag.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> CoreUI Flag</a></li>

        <li class="nav-group" aria-expanded="false"><a class="nav-link nav-group-toggle" href="#">
            <i class="nav-icon la la-puzzle-piece"></i> Icons</a>
            <ul class="nav-group-items compact" style="height: 0px;">
                <li class="nav-item"><a class="nav-link" href="icons/coreui-icons-free.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> CoreUI Free</a></li>
                <li class="nav-item"><a class="nav-link" href="icons/coreui-icons-brand.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> CoreUI Brand</a></li>
            </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="icons/coreui-icons-flag.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> CoreUI Flag</a></li>
        <li class="nav-item"><a class="nav-link" href="icons/brands.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Brands</a></li>
      </ul>
    </li>

    <li class="nav-item"><a class="nav-link" href="base/tables.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Tables</a></li>
    <li class="nav-item"><a class="nav-link" href="base/tooltips.html"><span class="nav-icon"><span class="nav-icon-bullet"></span></span> Tooltips</a></li>
  </ul>
</li>

<x-backpack::menu-item title="Tags" icon="la la-question" :link="backpack_url('tag')" />
<x-backpack::menu-item title="Roles" icon="la la-question" :link="backpack_url('role')" /> --}}
