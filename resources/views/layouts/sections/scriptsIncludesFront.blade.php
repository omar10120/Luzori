<!-- laravel style -->
@vite(['resources/assets/vendor/js/helpers.js'])
<script>
  // Ensure theme style is seeded to the same localStorage key the app uses (front)
  (function(){
    try {
      var tpl = document.documentElement.getAttribute('data-template') || 'default';
      var key = 'templateCustomizer-' + tpl + '--Style';
      var serverStyle = '{{ $configData['style'] }}';
      if (!localStorage.getItem(key)) {
        localStorage.setItem(key, 'dark');
      }
      document.cookie = 'mode=' + 'dark' + '; path=/; max-age=31536000';
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
  @vite(['resources/assets/js/front-config.js'])

@if ($configData['hasCustomizer'])
<script type="module">
    window.templateCustomizer = new TemplateCustomizer({
      cssPath: '',
      themesPath: '',
      defaultStyle: "{{$configData['styleOpt']}}",
      displayCustomizer: "{{$configData['displayCustomizer']}}",
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
      'controls': <?php echo json_encode(['rtl', 'style']); ?>,

    });
  </script>
@endif
