@php
    $name = $name ?? 'image';
    $model = $model ?? 'Product';
    $collection = $collection ?? 'Product';
    
    
    $allImages = $item ? $item->getMedia($collection)->sortBy('order_column') : collect();
    $mainImage = $allImages->where('order_column', 0)->first() ?? $allImages->first();
    $mainImageId = $mainImage?->id ?? '';
    $secondaryImages = $allImages->where('id', '!=', $mainImageId);
    
    $initialOrder = [];
    if ($mainImageId) {
        $initialOrder[] = $mainImageId;
    }
    foreach ($secondaryImages as $img) {
        $initialOrder[] = $img->id;
    }
    
    $config = [
        'name' => $name,
        'containerId' => 'multi-image-uploader-' . $name,
        'mainPhotoContainerId' => 'main-photo-container-' . $name,
        'mainPhotoImgId' => 'main-photo-img-' . $name,
        'mainPhotoPlaceholderId' => 'main-photo-placeholder-' . $name,
        'secondaryGridId' => 'secondary-photos-grid-' . $name,
        'fileInputId' => 'file-input-' . $name,
        'addPhotoBtnId' => 'photo-add-btn-' . $name,
        'imageOrderInputId' => 'image-order-' . $name,
        'deletedImagesInputId' => 'deleted-images-' . $name,
        'mainImageIdInputId' => 'main-image-id-' . $name,
        'removeMainBtnId' => 'btn-remove-main-' . $name,
    ];
@endphp

<script>
(function() {
    'use strict';

    
    const CONFIG = @json($config);
    const INITIAL_DATA = {
        mainImageId: @json($mainImageId),
        imageOrder: @json($initialOrder)
    };

    
    const container = document.getElementById(CONFIG.containerId);
    if (!container) return;

    const elements = {
        mainPhotoContainer: document.getElementById(CONFIG.mainPhotoContainerId),
        mainPhotoImg: document.getElementById(CONFIG.mainPhotoImgId),
        mainPhotoPlaceholder: document.getElementById(CONFIG.mainPhotoPlaceholderId),
        secondaryGrid: document.getElementById(CONFIG.secondaryGridId),
        fileInput: document.getElementById(CONFIG.fileInputId),
        addPhotoBtn: document.getElementById(CONFIG.addPhotoBtnId),
        imageOrderInput: document.getElementById(CONFIG.imageOrderInputId),
        deletedImagesInput: document.getElementById(CONFIG.deletedImagesInputId),
        mainImageIdInput: document.getElementById(CONFIG.mainImageIdInputId),
    };

    
    const state = {
        imageOrder: [...INITIAL_DATA.imageOrder],
        deletedImages: [],
        uploadedFiles: new Map(),
        tempIdCounter: 0
    };

    
    const utils = {
        generateTempId() {
            return `temp-${Date.now()}-${++state.tempIdCounter}`;
        },

        isTempId(id) {
            return id && id.startsWith('temp-');
        },

        updateImageOrder() {
            if (elements.imageOrderInput) {
                elements.imageOrderInput.value = JSON.stringify(state.imageOrder);
            }
        },

        updateDeletedImages() {
            if (elements.deletedImagesInput) {
                elements.deletedImagesInput.value = JSON.stringify(state.deletedImages);
            }
        },

        updateFileInput() {
            
            if (!elements.fileInput) return;

            const dataTransfer = new DataTransfer();
            
            
            state.uploadedFiles.forEach((fileData, imageId) => {
                if (fileData.file && utils.isTempId(imageId)) {
                    
                    dataTransfer.items.add(fileData.file);
                }
            });

            elements.fileInput.files = dataTransfer.files;
        },

        hasMainPhoto() {
            
            const hasMainId = elements.mainImageIdInput && elements.mainImageIdInput.value && elements.mainImageIdInput.value.trim() !== '';
            
            
            const hasPreview = elements.mainPhotoContainer && 
                             elements.mainPhotoContainer.querySelector('.main-photo-preview');
            
            
            const mainImg = elements.mainPhotoImg || document.getElementById(CONFIG.mainPhotoImgId);
            const hasMainImg = mainImg && 
                             mainImg.src && 
                             mainImg.style.display !== 'none' &&
                             !mainImg.src.includes('data:image/svg');
            
            return hasMainId || hasPreview || hasMainImg;
        },

        showError(message) {
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                alert(message);
            }
        },

        showSuccess(message) {
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
            }
        }
    };

    // Image management
    const imageManager = {
        setMainPhoto(imageSrc, file, imageId) {
            if (!elements.mainPhotoContainer) return;

            // Remove placeholder
            if (elements.mainPhotoPlaceholder) {
                elements.mainPhotoPlaceholder.remove();
            }

            // Create or update main photo image
            let img = elements.mainPhotoImg;
            if (!img) {
                img = document.createElement('img');
                img.id = CONFIG.mainPhotoImgId;
                img.alt = 'Main photo';
                elements.mainPhotoContainer.appendChild(img);
            }

            // Create preview wrapper
            let preview = elements.mainPhotoContainer.querySelector('.main-photo-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.className = 'main-photo-preview';
                elements.mainPhotoContainer.appendChild(preview);
                preview.appendChild(img);
            }

            img.src = imageSrc;
            img.style.display = 'block';

            // Create or update remove button
            let removeBtn = document.getElementById(CONFIG.removeMainBtnId);
            if (!removeBtn) {
                removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn-remove-main';
                removeBtn.id = CONFIG.removeMainBtnId;
                removeBtn.innerHTML = '<i class="ti ti-x"></i>';
                removeBtn.setAttribute('data-image-id', imageId);
                preview.appendChild(removeBtn);

                removeBtn.addEventListener('click', () => {
                    imageActions.removeMainPhoto();
                });
            } else {
                removeBtn.setAttribute('data-image-id', imageId);
            }

            // Store file
            if (file) {
                state.uploadedFiles.set(imageId, { file, isMain: true });
            }

            // Update main image ID
            if (elements.mainImageIdInput) {
                elements.mainImageIdInput.value = imageId;
            }

            // Update order
            if (!state.imageOrder.includes(imageId)) {
                state.imageOrder.unshift(imageId);
            }
            utils.updateImageOrder();
        },

        addSecondaryPhoto(imageSrc, file, imageId) {
            if (!elements.secondaryGrid) return;

            const photoItem = document.createElement('div');
            photoItem.className = 'photo-item';
            photoItem.setAttribute('data-image-id', imageId);
            photoItem.setAttribute('draggable', 'true');

            photoItem.innerHTML = `
                <div class="photo-item-inner">
                    <img src="${imageSrc}" alt="Product photo" />
                    <div class="photo-actions">
                        <button type="button" class="btn-action btn-set-main" data-image-id="${imageId}" title="Set as main">
                            <i class="ti ti-star"></i>
                        </button>
                        <button type="button" class="btn-action btn-remove" data-image-id="${imageId}" title="Remove">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            // Insert before add button
            if (elements.addPhotoBtn) {
                elements.secondaryGrid.insertBefore(photoItem, elements.addPhotoBtn);
            } else {
                elements.secondaryGrid.appendChild(photoItem);
            }

            // Store file
            if (file) {
                state.uploadedFiles.set(imageId, { file, isMain: false });
            }

            // Add to order
            if (!state.imageOrder.includes(imageId)) {
                state.imageOrder.push(imageId);
            }
            utils.updateImageOrder();

            // Event listeners
            const setMainBtn = photoItem.querySelector('.btn-set-main');
            const removeBtn = photoItem.querySelector('.btn-remove');

            if (setMainBtn) {
                setMainBtn.addEventListener('click', () => {
                    imageActions.setAsMain(imageId, photoItem, imageSrc, file);
                });
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    imageActions.removeSecondary(imageId, photoItem);
                });
            }

            // Drag and drop
            dragDrop.setup(photoItem);
        },

        removeMainPhoto() {
            if (!elements.mainPhotoImg) return;

            const imageId = elements.mainImageIdInput?.value || '';

            // Mark as deleted if existing image
            if (imageId && !utils.isTempId(imageId)) {
                if (!state.deletedImages.includes(imageId)) {
                    state.deletedImages.push(imageId);
                    utils.updateDeletedImages();
                }
            }

            // Remove from state
            state.uploadedFiles.delete(imageId);
            state.imageOrder = state.imageOrder.filter(id => id !== imageId);
            utils.updateImageOrder();
            utils.updateFileInput(); // Update file input after removal

            // Remove preview
            const preview = elements.mainPhotoContainer.querySelector('.main-photo-preview');
            if (preview) {
                preview.remove();
            }

            // Add placeholder
            if (!elements.mainPhotoPlaceholder) {
                const placeholder = document.createElement('div');
                placeholder.className = 'main-photo-placeholder';
                placeholder.id = CONFIG.mainPhotoPlaceholderId;
                placeholder.innerHTML = `
                    <div class="placeholder-content">
                        <i class="ti ti-camera-plus"></i>
                        <p>Add main photo</p>
                        <small>Click or drag & drop</small>
                    </div>
                `;
                elements.mainPhotoContainer.appendChild(placeholder);

                // Make clickable
                placeholder.addEventListener('click', () => {
                    if (elements.fileInput) {
                        elements.fileInput.click();
                    }
                });
            }

            // Clear main image ID
            if (elements.mainImageIdInput) {
                elements.mainImageIdInput.value = '';
            }

            // Promote first secondary to main
            const firstSecondary = elements.secondaryGrid?.querySelector('.photo-item:not(.photo-item-add)');
            if (firstSecondary) {
                const img = firstSecondary.querySelector('img');
                const secondaryId = firstSecondary.getAttribute('data-image-id');
                if (img && secondaryId) {
                    imageActions.setAsMain(secondaryId, firstSecondary, img.src, null);
                }
            }
        },

        removeSecondaryPhoto(imageId, element) {
            // Mark as deleted if existing image
            if (imageId && !utils.isTempId(imageId)) {
                if (!state.deletedImages.includes(imageId)) {
                    state.deletedImages.push(imageId);
                    utils.updateDeletedImages();
                }
            }

            // Remove from state
            state.uploadedFiles.delete(imageId);
            state.imageOrder = state.imageOrder.filter(id => id !== imageId);
            utils.updateImageOrder();
            utils.updateFileInput(); // Update file input after removal

            // Remove from DOM
            if (element) {
                element.remove();
            }
        }
    };

    // Image actions
    const imageActions = {
        handleFileSelection(files) {
            if (!files || files.length === 0) return;

            // Check if main photo exists BEFORE processing files (use reliable check)
            const hasMainPhoto = utils.hasMainPhoto();

            // Track if we've set a main photo in this batch (use object to pass by reference)
            const batchState = { mainPhotoSet: false };

            Array.from(files).forEach((file, index) => {
                if (!file.type.startsWith('image/')) {
                    utils.showError('Please select image files only');
                    return;
                }

                const tempId = utils.generateTempId();
                const reader = new FileReader();

                reader.onload = (e) => {
                    // Check current state when callback executes (in case callbacks execute out of order)
                    // Use reliable check function
                    const currentMainPhoto = utils.hasMainPhoto();
                    
                    // First file goes to main ONLY if no main photo exists AND we haven't set one in this batch
                    // All subsequent files go to additional photos
                    const shouldBeMain = !currentMainPhoto && !batchState.mainPhotoSet && index === 0;

                    if (shouldBeMain) {
                        imageManager.setMainPhoto(e.target.result, file, tempId);
                        batchState.mainPhotoSet = true;
                        
                        // Update elements reference after setting main photo
                        elements.mainPhotoImg = document.getElementById(CONFIG.mainPhotoImgId);
                    } else {
                        imageManager.addSecondaryPhoto(e.target.result, file, tempId);
                    }

                    // Update file input after each file is processed
                    utils.updateFileInput();
                };

                reader.onerror = () => {
                    utils.showError('Error reading file');
                };

                reader.readAsDataURL(file);
            });
        },

        setAsMain(imageId, element, imageSrc, file) {
            const currentMainId = elements.mainImageIdInput?.value || '';

            // Move current main to secondary if exists
            if (currentMainId && elements.mainPhotoImg) {
                const currentMainSrc = elements.mainPhotoImg.src;
                imageManager.addSecondaryPhoto(currentMainSrc, null, currentMainId);
            }

            // Set new main
            imageManager.setMainPhoto(imageSrc, file, imageId);

            // Update file tracking
            const fileData = state.uploadedFiles.get(imageId);
            if (fileData) {
                fileData.isMain = true;
            }

            // Remove from secondary
            if (element) {
                element.remove();
            }
        },

        removeMainPhoto() {
            imageManager.removeMainPhoto();
        },

        removeSecondary(imageId, element) {
            imageManager.removeSecondaryPhoto(imageId, element);
        }
    };

    // Drag and drop
    const dragDrop = {
        setup(element) {
            element.addEventListener('dragstart', (e) => {
                element.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', element.getAttribute('data-image-id'));
            });

            element.addEventListener('dragend', () => {
                element.classList.remove('dragging');
                dragDrop.updateOrder();
            });
        },

        getDragAfterElement(container, x) {
            const draggableElements = [...container.querySelectorAll('.photo-item:not(.dragging):not(.photo-item-add)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = x - box.left - box.width / 2;

                if (offset < 0 && offset > closest.offset) {
                    return { offset, element: child };
                }
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        },

        updateOrder() {
            if (!elements.secondaryGrid) return;

            const items = elements.secondaryGrid.querySelectorAll('.photo-item:not(.photo-item-add)');
            state.imageOrder = [];

            // Add main image first
            const mainId = elements.mainImageIdInput?.value || '';
            if (mainId) {
                state.imageOrder.push(mainId);
            }

            // Add secondary images in order
            items.forEach(item => {
                const imageId = item.getAttribute('data-image-id');
                if (imageId && !state.imageOrder.includes(imageId)) {
                    state.imageOrder.push(imageId);
                }
            });

            utils.updateImageOrder();
        },

        init() {
            if (!elements.secondaryGrid) return;

            elements.secondaryGrid.addEventListener('dragover', (e) => {
                // Only handle photo reordering, not file drops
                const dragging = document.querySelector('.photo-item.dragging');
                if (!dragging || e.dataTransfer.types.includes('Files')) {
                    return; // Let file drop handlers handle it
                }

                e.preventDefault();
                e.stopPropagation();
                e.dataTransfer.dropEffect = 'move';

                const afterElement = dragDrop.getDragAfterElement(elements.secondaryGrid, e.clientX);

                if (afterElement == null) {
                    if (elements.addPhotoBtn) {
                        elements.secondaryGrid.insertBefore(dragging, elements.addPhotoBtn);
                    } else {
                        elements.secondaryGrid.appendChild(dragging);
                    }
                } else {
                    elements.secondaryGrid.insertBefore(dragging, afterElement);
                }
            });
        }
    };

    // Initialization
    const init = {
        setupEventListeners() {
            // Add photo button
            if (elements.addPhotoBtn) {
                elements.addPhotoBtn.addEventListener('click', () => {
                    if (elements.fileInput) {
                        elements.fileInput.click();
                    }
                });
            }

            // File input change
            if (elements.fileInput) {
                elements.fileInput.addEventListener('change', (e) => {
                    imageActions.handleFileSelection(e.target.files);
                });
            }

            // Main photo placeholder click
            if (elements.mainPhotoPlaceholder) {
                elements.mainPhotoPlaceholder.addEventListener('click', () => {
                    if (elements.fileInput) {
                        elements.fileInput.click();
                    }
                });
            }

            // Main photo container drop
            if (elements.mainPhotoContainer) {
                elements.mainPhotoContainer.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    e.dataTransfer.dropEffect = 'copy';
                });

                elements.mainPhotoContainer.addEventListener('drop', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        imageActions.handleFileSelection(files);
                    }
                });
            }

            // Secondary photos grid drop
            if (elements.secondaryGrid) {
                elements.secondaryGrid.addEventListener('dragover', (e) => {
                    // Only handle if it's a file drop, not photo reordering
                    if (e.dataTransfer.types.includes('Files')) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.dataTransfer.dropEffect = 'copy';
                    }
                });

                elements.secondaryGrid.addEventListener('drop', (e) => {
                    // Only handle if it's a file drop, not photo reordering
                    if (e.dataTransfer.types.includes('Files')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const files = e.dataTransfer.files;
                        if (files.length > 0) {
                            imageActions.handleFileSelection(files);
                        }
                    }
                });
            }

            // Container drop (for drag and drop anywhere in the component)
            if (container) {
                container.addEventListener('dragover', (e) => {
                    if (e.dataTransfer.types.includes('Files')) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.dataTransfer.dropEffect = 'copy';
                    }
                });

                container.addEventListener('drop', (e) => {
                    if (e.dataTransfer.types.includes('Files')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const files = e.dataTransfer.files;
                        if (files.length > 0) {
                            imageActions.handleFileSelection(files);
                        }
                    }
                });
            }

            // Existing remove main button
            const existingRemoveBtn = document.getElementById(CONFIG.removeMainBtnId);
            if (existingRemoveBtn) {
                existingRemoveBtn.addEventListener('click', () => {
                    const mediaId = existingRemoveBtn.getAttribute('data-media-id');
                    if (mediaId && !utils.isTempId(mediaId)) {
                        if (!state.deletedImages.includes(mediaId)) {
                            state.deletedImages.push(mediaId);
                            utils.updateDeletedImages();
                        }
                    }
                    imageActions.removeMainPhoto();
                });
            }

            // Existing secondary photos
            if (elements.secondaryGrid) {
                const existingPhotos = elements.secondaryGrid.querySelectorAll('.photo-item[data-media-id]');
                existingPhotos.forEach(photo => {
                    const mediaId = photo.getAttribute('data-media-id');
                    if (mediaId) {
                        // Setup drag and drop
                        photo.setAttribute('draggable', 'true');
                        dragDrop.setup(photo);

                        // Set as main button
                        const setMainBtn = photo.querySelector('.btn-set-main');
                        if (setMainBtn) {
                            setMainBtn.addEventListener('click', () => {
                                const img = photo.querySelector('img');
                                if (img) {
                                    imageActions.setAsMain(mediaId, photo, img.src, null);
                                }
                            });
                        }

                        // Remove button
                        const removeBtn = photo.querySelector('.btn-remove');
                        if (removeBtn) {
                            removeBtn.addEventListener('click', () => {
                                imageActions.removeSecondary(mediaId, photo);
                            });
                        }
                    }
                });
            }
        },

        run() {
            init.setupEventListeners();
            dragDrop.init();
            utils.updateImageOrder();
            
            // Ensure files are in file input before form submission
            const form = container.closest('form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    // Update file input right before submission
                    utils.updateFileInput();
                }, { capture: true });
            }
        }
    };

    // Start
    init.run();
})();
</script>
