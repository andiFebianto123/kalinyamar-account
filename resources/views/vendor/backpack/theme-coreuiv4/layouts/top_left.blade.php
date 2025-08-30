<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}" dir="{{ backpack_theme_config('html_direction') }}">

<head>
  @include(backpack_view('inc.head'))

</head>

<body class="{{ backpack_theme_config('classes.body') }}">

  @include(backpack_view('inc.sidebar'))

  <div class="wrapper d-flex flex-column min-vh-100 bg-light">

    @include(backpack_view('inc.main_header'))

    <div class="app-body flex-grow-1 px-2">

    <main class="main">

       @yield('before_breadcrumbs_widgets')

       @includeWhen(isset($breadcrumbs), backpack_view('inc.breadcrumbs'))

       @yield('after_breadcrumbs_widgets')

       @yield('header')

        <div class="container-fluid animated fadeIn">

          @yield('before_content_widgets')

          @yield('content')

          @yield('after_content_widgets')

        </div>

    </main>

  </div>{{-- ./app-body --}}

  <footer class="{{ backpack_theme_config('classes.footer') }}">
    @include(backpack_view('inc.footer'))
  </footer>
  </div>

  @yield('before_scripts')
  @stack('before_scripts')

  @include(backpack_view('inc.scripts'))
  @include(backpack_view('inc.theme_scripts'))

  @yield('after_scripts')
  @stack('after_scripts')
  <script>
    $(document).ready(function () {

    function checkMobile() {
        // cek ukuran layar mobile (contoh: <= 768px)
        return $(window).width() <= 768;
    }

      $('.sidebar').on('mouseenter', function () {
        if(!checkMobile()){
            $('body').removeClass('menu_hover_custom');
        }
      });

      $('.sidebar').on('mouseleave', function () {
        if (!checkMobile()) {
            $('body').addClass('menu_hover_custom');
        }
      });

      $(window).on('resize', function () {
        if (checkMobile()) {
          $('body').removeClass('menu_hover_custom');
        }else{
          $('body').addClass('menu_hover_custom');
        }
      });

      if (checkMobile()) {
        $('body').removeClass('menu_hover_custom');
      }

    });
  </script>
</body>
</html>
