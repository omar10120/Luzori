@php
$menuCollapsed = ($configData['menuCollapsed'] === 'layout-menu-collapsed') ? json_encode(true) : false;
@endphp
<!-- laravel style -->
@vite(['resources/assets/vendor/js/helpers.js'])
<script>
  // Ensure theme style is seeded to the same localStorage key the app uses
  (function(){
    try {
      var tpl = document.documentElement.getAttribute('data-template') || 'default';
      var key = 'templateCustomizer-' + tpl + '--Style';
      var serverStyle = '{{ $configData['style'] }}'; // 'dark' or 'light'
      
      // Get current style from localStorage or use server style
      var currentStyle = localStorage.getItem(key) || serverStyle;
      
      // Sync localStorage with server style if localStorage is empty
      if (!localStorage.getItem(key)) {
        localStorage.setItem(key, serverStyle);
        currentStyle = serverStyle;
      }
      
      // Sync cookie with current style (localStorage takes precedence)
      // This ensures the cookie is always in sync with localStorage
      document.cookie = 'mode=' + currentStyle + '; path=/; max-age=31536000; SameSite=Lax';
      
      // Also update the document class to ensure theme is applied immediately
      if (currentStyle === 'light') {
        document.documentElement.classList.remove('dark-style');
      } else if (currentStyle === 'dark') {
        document.documentElement.classList.add('dark-style');
      }
    } catch(e) {}
  })();
  </script>
<!-- beautify ignore:start -->
@if ($configData['hasCustomizer'])
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  @vite(['resources/assets/vendor/js/template-customizer.js'])
@endif

  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  @vite(['resources/assets/js/config.js'])

@if ($configData['hasCustomizer'])
<script type="module">
  window.templateCustomizer = new TemplateCustomizer({
    cssPath: '',
    themesPath: '',
    defaultStyle: "{{$configData['styleOpt']}}",
    defaultShowDropdownOnHover: "{{$configData['showDropdownOnHover']}}", // true/false (for horizontal layout only)
    displayCustomizer: "{{$configData['displayCustomizer']}}",
    lang: '{{ app()->getLocale() }}',
    pathResolver: function(path) {
      var resolvedPaths = {
        // Core stylesheets
        @foreach (['core'] as $name)
          '{{ $name }}.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/'.$name.'.scss') }}',
          '{{ $name }}-dark.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/'.$name.'-dark.scss') }}',
        @endforeach

        // Themes
        @foreach (['default', 'bordered', 'semi-dark'] as $name)
          'theme-{{ $name }}.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/theme-'.$name.'.scss') }}',
          'theme-{{ $name }}-dark.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/theme-'.$name.'-dark.scss') }}',
        @endforeach
      }
      return resolvedPaths[path] || path;
    },
    'controls': <?php echo json_encode($configData['customizerControls']); ?>,
  });
</script>
@endif
