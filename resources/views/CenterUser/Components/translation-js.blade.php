<script>
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    async function translateText(text, from, to, targetId) {
        if (!text || text.trim().length < 2) return;
        
        const targetInput = $(`#${targetId}`);
        if (targetInput.length === 0 || targetInput.val().trim() !== '') return;

        targetInput.parent().find('.translation-loader').remove();
        targetInput.after('<span class="translation-loader text-primary small"><i class="ti ti-rotate-clockwise spinner-border spinner-border-sm"></i> Translating...</span>');

        try {
            const response = await fetch(`https://api.mymemory.translated.net/get?q=${encodeURIComponent(text)}&langpair=${from}|${to}`);
            const data = await response.json();
            if (data.responseData && data.responseData.translatedText) {
                if (targetInput.val().trim() === '') {
                    targetInput.val(data.responseData.translatedText);
                }
            }
        } catch (error) {
            console.error('Translation error:', error);
        } finally {
            targetInput.parent().find('.translation-loader').remove();
        }
    }

    const debouncedTranslate = debounce(translateText, 1000);

    $(document).ready(function() {
        // Generic auto-translation for fields ending in _en
        $(document).on('input', '[id$="_en"]', function() {
            const id = $(this).attr('id');
            const targetId = id.replace('_en', '_ar');
            if ($(`#${targetId}`).length > 0) {
                debouncedTranslate($(this).val(), 'en', 'ar', targetId);
            }
        });

        // Generic auto-translation for fields ending in _ar
        $(document).on('input', '[id$="_ar"]', function() {
            const id = $(this).attr('id');
            const targetId = id.replace('_ar', '_en');
            if ($(`#${targetId}`).length > 0) {
                debouncedTranslate($(this).val(), 'ar', 'en', targetId);
            }
        });
    });
</script>
