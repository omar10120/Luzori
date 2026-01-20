@php
    $name = $name ?? 'image';
    $model = $model ?? 'Product';
    $collection = $collection ?? 'Product';
    
    // Get existing images
    $allImages = $item ? $item->getMedia($collection)->sortBy('order_column') : collect();
    $mainImage = $allImages->where('order_column', 0)->first() ?? $allImages->first();
    $mainImageId = $mainImage?->id ?? '';
    $secondaryImages = $allImages->where('id', '!=', $mainImageId);
    
    // Build initial order
    $initialOrder = [];
    if ($mainImageId) {
        $initialOrder[] = $mainImageId;
    }
    foreach ($secondaryImages as $img) {
        $initialOrder[] = $img->id;
    }
@endphp

<div class="multi-image-uploader" id="multi-image-uploader-{{ $name }}">


    <!-- Main Photo Section -->
    <div class="main-photo-wrapper">
        <div class="main-photo-label">
            <i class="ti ti-star-filled"></i>
            <span>Main Photo</span>
        </div>
        <div class="main-photo-container" id="main-photo-container-{{ $name }}">
            @if($mainImage)
                <div class="main-photo-preview">
                    <img src="{{ $mainImage->getUrl() }}" alt="Main photo" id="main-photo-img-{{ $name }}" />
                    <button type="button" class="btn-remove-main" id="btn-remove-main-{{ $name }}" data-media-id="{{ $mainImage->id }}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            @else
                <div class="main-photo-placeholder" id="main-photo-placeholder-{{ $name }}">
                    <div class="placeholder-content">
                        <i class="ti ti-camera-plus"></i>
                        <p>Add main photo</p>
                        <small>Click or drag & drop</small>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Secondary Photos Section -->
    <div class="secondary-photos-wrapper">
        <div class="secondary-photos-label">
            <i class="ti ti-photo"></i>
            <span>Additional Photos</span>
        </div>
        <div class="secondary-photos-grid" id="secondary-photos-grid-{{ $name }}">
            @foreach($secondaryImages as $media)
                <div class="photo-item" data-media-id="{{ $media->id }}" draggable="true">
                    <div class="photo-item-inner">
                        <img src="{{ $media->getUrl() }}" alt="Product photo" />
                        <div class="photo-actions">
                            <button type="button" class="btn-action btn-set-main" data-media-id="{{ $media->id }}" title="Set as main">
                                <i class="ti ti-star"></i>
                            </button>
                            <button type="button" class="btn-action btn-remove" data-media-id="{{ $media->id }}" title="Remove">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Add Photo Button -->
            <div class="photo-item photo-item-add" id="photo-add-btn-{{ $name }}">
                <div class="photo-item-inner">
                    <button type="button" class="btn-add-photo">
                        <i class="ti ti-plus"></i>
                        <span>Add Photo</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Inputs -->
    <input type="file" class="d-none" id="file-input-{{ $name }}" name="{{ $name }}[]" accept="image/*" multiple />
    <input type="hidden" name="image_order" id="image-order-{{ $name }}" value="{{ json_encode($initialOrder) }}" />
    <input type="hidden" name="deleted_images" id="deleted-images-{{ $name }}" value="[]" />
    <input type="hidden" name="main_image_id" id="main-image-id-{{ $name }}" value="{{ $mainImageId }}" />
</div>

<style>
    .multi-image-uploader {
        width: 100%;
    }

    .multi-image-header {
        margin-bottom: 1.5rem;
    }

    .multi-image-header .form-label {
        font-weight: 600;
        color: #566a7f;
        margin-bottom: 0.5rem;
    }

    .main-photo-wrapper {
        margin-bottom: 2rem;
    }

    .main-photo-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #696cff;
        margin-bottom: 0.75rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #f3f0ff 0%, #ede9ff 100%);
        border-radius: 8px;
    }

    .main-photo-label i {
        font-size: 1rem;
    }

    .main-photo-container {
        position: relative;
        width: 100%;
        height:45%;
        min-height: 180px;
        border: 2px dashed #e0e0e0;
        border-radius: 12px;
        background: #fafafa;
        overflow: hidden;
        transition: all 0.3s ease;
        z-index: 5;
    }

    .main-photo-container:hover {
        border-color: #696cff;
        background: #f8f7ff;
    }

    .main-photo-preview {
        position: relative;
        width: 100%;
        height: 100%;
        min-height: 180px;
    }

    .main-photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .btn-remove-main {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.95);
        border: none;
        color: #ff3e1d;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        transition: all 0.2s ease;
        z-index: 10;
    }

    .btn-remove-main:hover {
        background: #ff3e1d;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(255, 62, 29, 0.3);
    }

    .main-photo-placeholder {
        width: 100%;
        height: 100%;
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .main-photo-placeholder:hover {
        background: #f0f0f0;
    }

    .placeholder-content {
        text-align: center;
        color: #696cff;
    }

    .placeholder-content i {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        display: block;
        opacity: 0.7;
    }

    .placeholder-content p {
        font-size: 0.875rem;
        font-weight: 500;
        margin: 0.25rem 0;
        color: #566a7f;
    }

    .placeholder-content small {
        font-size: 0.75rem;
        color: #a8b2bd;
    }

    .secondary-photos-wrapper {
        margin-top: 2rem;
        position: relative;
        z-index: 5;
    }

    .secondary-photos-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #566a7f;
        margin-bottom: 0.75rem;
    }

    .secondary-photos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 1rem;
    }

    .photo-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        cursor: move;
        transition: all 0.2s ease;
    }

    .photo-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .photo-item.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }

    .photo-item-inner {
        position: relative;
        width: 100%;
        height: 100%;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        background: #fafafa;
    }

    .photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .photo-actions {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .photo-item:hover .photo-actions {
        opacity: 1;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background: rgba(255, 255, 255, 0.95);
        color: #566a7f;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1rem;
    }

    .btn-action:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-set-main:hover {
        background: #696cff;
        color: white;
    }

    .btn-remove:hover {
        background: #ff3e1d;
        color: white;
    }

    .photo-item-add {
        cursor: pointer;
    }

    .photo-item-add .photo-item-inner {
        border: 2px dashed #d0d0d0;
        background: #fafafa;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .photo-item-add:hover .photo-item-inner {
        border-color: #696cff;
        background: #f8f7ff;
    }

    .btn-add-photo {
        border: none;
        background: transparent;
        color: #696cff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        padding: 1rem;
        width: 100%;
        height: 100%;
    }

    .btn-add-photo i {
        font-size: 2rem;
    }

    .btn-add-photo span {
        font-size: 0.875rem;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .secondary-photos-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
        }

        .main-photo-container {
            min-height: 150px;
        }

        .main-photo-preview {
            min-height: 150px;
        }

        .main-photo-placeholder {
            min-height: 150px;
        }

        .placeholder-content i {
            font-size: 2rem;
        }

        .placeholder-content p {
            font-size: 0.75rem;
        }

        .placeholder-content small {
            font-size: 0.625rem;
        }
    }
</style>

