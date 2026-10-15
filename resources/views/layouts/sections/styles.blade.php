<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

@vite(['resources/assets/vendor/fonts/tabler-icons.scss',
'resources/assets/vendor/fonts/fontawesome.scss',
'resources/assets/vendor/fonts/flag-icons.scss'])
<!-- Core CSS -->
@vite(['resources/assets/vendor/scss'.$configData['rtlSupport'].'/core' .($configData['style'] !== 'light' ? '-' . $configData['style'] : '') .'.scss',
'resources/assets/vendor/scss'.$configData['rtlSupport'].'/' .$configData['theme'] .($configData['style'] !== 'light' ? '-' . $configData['style'] : '') .'.scss',
'resources/assets/css/demo.css'])

@vite([
  'resources/assets/vendor/libs/node-waves/node-waves.scss',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
  'resources/assets/vendor/libs/typeahead-js/typeahead.scss'
])

<!-- Vendor Styles -->
@yield('vendor-style')

<!-- Page Styles -->
@yield('page-style')
@stack('styles')

@if (!str_contains(url()->current(), 'admin') && (str_contains(url()->current(), 'center_user') || auth('center_user')->check()))
<link rel="stylesheet" href="{{ asset('assets/css/luzori-center.css') }}?v=35">
<style>
  body.luzori-center .luzori-page::before,
  body.luzori-center .luzori-dashboard::before {
    background-image: url('{{ asset('assets/img/branding/corner-leaf-beige.png') }}') !important;
  }

  /* Mobile: force navbar flush to top (beats theme margin: $spacer auto 0) */
  @media (max-width: 1199.98px) {
    body.luzori-center .layout-page,
    body.luzori-center .layout-container,
    body.luzori-center .layout-wrapper {
      padding-top: 0 !important;
      margin-top: 0 !important;
    }
    body.luzori-center #layout-navbar,
    body.luzori-center .layout-navbar.navbar-detached {
      position: relative !important;
      top: 0 !important;
      margin: 0 !important;
      margin-top: 0 !important;
      width: 100% !important;
      max-width: 100% !important;
      border-radius: 0 !important;
      background: #fff !important;
      background-color: #fff !important;
    }
  }
</style>
@endif
