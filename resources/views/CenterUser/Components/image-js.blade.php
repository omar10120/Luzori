<script>
    (function() {
        // Find image input and related elements
        const imageInput = document.getElementById('image');
        if (!imageInput) return;
        
        const dropzone = document.getElementById('dropzone-image');
        const previewContainer = document.getElementById('preview-container-image');
        const previewImage = document.getElementById('show_image_image');
        const removeBtn = document.getElementById('remove-image-image');
        const infoWrapper = document.getElementById('image-info-image');
        const sizeDisplay = document.getElementById('image-size-image');
        
        if (!dropzone || !previewContainer || !previewImage || !removeBtn) return;
        
        // Check if there's an existing image from the item
        const existingImageSrc = previewImage.src || '';
        const defaultImagePath = '{{ asset("assets/img/avatars/1.png") }}';
        const hasValidImage = existingImageSrc && 
                              existingImageSrc.trim() !== '' && 
                              existingImageSrc !== window.location.href &&
                              !existingImageSrc.includes('avatars/1.png');
        
        if (hasValidImage) {
            previewContainer.style.display = 'block';
            dropzone.style.display = 'none';
        } else {
            previewContainer.style.display = 'none';
            dropzone.style.display = 'flex';
        }
        
        // Format file size
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
        
        // Click to select file
        dropzone.addEventListener('click', function() {
            imageInput.click();
        });
        
        // File input change
        imageInput.addEventListener('change', function(e) {
            handleFile(e.target.files[0]);
        });
        
        // Drag and drop handlers
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        });
        
        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        });
        
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    // Create a new FileList-like object
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    imageInput.files = dataTransfer.files;
                    handleFile(file);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Please select an image file');
                    } else {
                        alert('Please select an image file');
                    }
                }
            }
        });
        
        // Handle file preview
        function handleFile(file) {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = 'block';
                    dropzone.style.display = 'none';
                    
                    // Show file size
                    if (sizeDisplay && infoWrapper) {
                        sizeDisplay.textContent = formatBytes(file.size);
                        infoWrapper.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        }
        
        // Remove image
        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            imageInput.value = '';
            previewImage.src = '';
            previewContainer.style.display = 'none';
            dropzone.style.display = 'flex';
            
            // Hide info
            if (infoWrapper) {
                infoWrapper.style.display = 'none';
            }
        });
    })();
</script>
