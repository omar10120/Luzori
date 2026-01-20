<div class="mb-1">
    <label class="form-label">{{__('field.image')}}</label>
    <input type="file" class="form-control" id="{{ $name }}" name="{{ $name }}" />
</div>
<img id="show_image" src="{{ $item ? $item->image : '' }}"
    style="{{ $item ? '' : 'display:none;' }} width:200px;height:200px;margin:20px;" alt="{{ $model }} image" />
