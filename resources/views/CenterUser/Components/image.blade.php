<div class="mb-1">
    <label class="form-label">{{__('field.image')}}</label>
    <div class="image-upload-container" id="image-upload-container-{{ $name }}">
        <div class="image-upload-dropzone" id="dropzone-{{ $name }}" style="{{ $item && $item->image && $item->image !== asset('assets/img/avatars/1.png') ? 'display: none;' : '' }}">
            <input type="file" class="d-none" id="{{ $name }}" name="{{ $name }}" accept="image/*" />
            <div class="dropzone-content">
                <div class="dropzone-icon">
                    <i class="ti ti-camera-plus"></i>
                </div>
                <p class="dropzone-text">Add a photo</p>
            </div>
        </div>
        <div class="image-preview-container" id="preview-container-{{ $name }}" style="{{ $item && $item->image && $item->image !== asset('assets/img/avatars/1.png') ? '' : 'display: none;' }}">
            <div class="image-preview-wrapper">
                <img id="show_image_{{ $name }}" class="image-preview" src="{{ $item && $item->image && $item->image !== asset('assets/img/avatars/1.png') ? $item->image : '' }}" alt="{{ $model }} image" />
                <button type="button" class="btn btn-sm btn-danger image-remove-btn" id="remove-image-{{ $name }}">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="image-info-wrapper mt-2 text-center" id="image-info-{{ $name }}" style="display: none;">
                <span class="badge bg-label-secondary">
                    <i class="ti ti-file-description me-1"></i>
                    <span id="image-size-{{ $name }}"></span>
                </span>
            </div>
        </div>
    </div>
</div>

<style>
    .image-upload-container {
        position: relative;
    }
    
    .image-upload-dropzone {
        border: none;
        border-radius: 8px;
        padding: 4rem 2rem;
        text-align: center;
        background-color: #f3f0ff;
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 25%;
    }
    @media (max-width: 768px) {
        .image-upload-dropzone {
            width: 100%;
        }
    }
    
    .image-upload-dropzone:hover {
        background-color: #ede9ff;
    }
    
    .image-upload-dropzone.dragover {
        background-color: #e8e3ff;
        border: 2px dashed #696cff;
    }
    
    .dropzone-content {
        pointer-events: none;
    }
    
    .dropzone-icon {
        margin-bottom: 1rem;
    }
    
    .dropzone-icon i {
        font-size: 3.5rem;
        color: #696cff;
    }
    
    .dropzone-text {
        font-size: 1.1rem;
        font-weight: 500;
        color: #696cff;
        margin: 0.5rem 0 0 0;
    }
    
    .image-preview-container {
        margin-top: 0;
    }
    
    .image-preview-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    .image-preview {
        width: 100%;
        max-width: 100%;
        height: auto;
        max-height: 400px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .image-remove-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 10;
    }
    
    .image-remove-btn:hover {
        transform: scale(1.1);
    }
</style>
