@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #map {
            height: 350px;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .position-relative {
            position: relative;
        }
        #search-results {
            margin-top: 2px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        #search-results .list-group-item {
            cursor: pointer;
            border: none;
            border-bottom: 1px solid #eee;
        }
        #search-results .list-group-item:hover {
            background-color: #f8f9fa;
        }
        #search-results .list-group-item:last-child {
            border-bottom: none;
        }
        .search-loading {
            text-align: center;
            padding: 10px;
            color: #666;
        }
        @media screen and (max-width: 768px) {
            #map  {
                height: 150px;
            }
            #search-results{
                font-size:5px;
            }
            #search-location-btn{
                font-size: 12px;
            }
            #address{
                height: 100px;
         
            }
        }
    </style>
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body"> 
                        <div class="container">
                            @include('CenterUser.Components.languages-tabs')
                            
                            <div class="tab-content">
                                @foreach (Config::get('translatable.locales') as $locale)
                                    <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                        aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <div class="mb-1">
                                                    <label class="form-label">{{__('field.name')}}  <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.enter_the_official_name_of_the_branch')}}</small>
                                                    <input type="text" id="name_{{ $locale }}" class="form-control"
                                                        name="{{ $locale }}[name]" placeholder="{{__('field.name')}}"
                                                        value="{{ $item ? $item->translate($locale)->name : '' }}" />
                                                    
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <div class="mb-1">
                                                    <label class="form-label">{{__('field.city')}}  <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.select_a_city_from_the_list')}}</small>
                                                    <select id="city_{{ $locale }}" class="form-select select2" name="{{ $locale }}[city]">
                                                        <option value="">{{__('general.select_a_city')}}</option>
                                                        @if($locale == 'ar')
                                                            <option value="أبو ظبي" {{ ($item && $item->translate($locale)->city == 'أبو ظبي') ? 'selected' : '' }}>أبو ظبي</option>
                                                            <option value="دبي" {{ ($item && $item->translate($locale)->city == 'دبي') ? 'selected' : '' }}>دبي</option>
                                                            <option value="الشارقة" {{ ($item && $item->translate($locale)->city == 'الشارقة') ? 'selected' : '' }}>الشارقة</option>
                                                            <option value="عجمان" {{ ($item && $item->translate($locale)->city == 'عجمان') ? 'selected' : '' }}>عجمان</option>
                                                            <option value="رأس الخيمة" {{ ($item && $item->translate($locale)->city == 'رأس الخيمة') ? 'selected' : '' }}>رأس الخيمة</option>
                                                            <option value="أم القيوين" {{ ($item && $item->translate($locale)->city == 'أم القيوين') ? 'selected' : '' }}>أم القيوين</option>
                                                            <option value="الفجيرة" {{ ($item && $item->translate($locale)->city == 'الفجيرة') ? 'selected' : '' }}>الفجيرة</option>
                                                        @else
                                                            <option value="Abu Dhabi" {{ ($item && $item->translate($locale)->city == 'Abu Dhabi') ? 'selected' : '' }}>Abu Dhabi</option>
                                                            <option value="Dubai" {{ ($item && $item->translate($locale)->city == 'Dubai') ? 'selected' : '' }}>Dubai</option>
                                                            <option value="Sharjah" {{ ($item && $item->translate($locale)->city == 'Sharjah') ? 'selected' : '' }}>Sharjah</option>
                                                            <option value="Ajman" {{ ($item && $item->translate($locale)->city == 'Ajman') ? 'selected' : '' }}>Ajman</option>
                                                            <option value="Ras Al Khaimah" {{ ($item && $item->translate($locale)->city == 'Ras Al Khaimah') ? 'selected' : '' }}>Ras Al Khaimah</option>
                                                            <option value="Umm Al Quwain" {{ ($item && $item->translate($locale)->city == 'Umm Al Quwain') ? 'selected' : '' }}>Umm Al Quwain</option>
                                                            <option value="Fujairah" {{ ($item && $item->translate($locale)->city == 'Fujairah') ? 'selected' : '' }}>Fujairah</option>
                                                        @endif
                                                    </select>
                                                    
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{__('field.address')}}  <span class="text-danger">*</span></label>
                                                    <small class="text-muted">Provide the physical location details.</small>
                                                    <textarea id="address_{{ $locale }}" name="{{ $locale }}[address]" class="form-control" cols="25" 
                                                        rows="10" placeholder="{{__('field.address')}}">{{ $item ? $item->translate($locale)->address : '' }}</textarea>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">{{ __('general.location_on_map') ?? 'Location on Map' }}  <span class="text-danger">*</span></label>
                                    <small class="text-muted d-block mb-2">{{__('general.select_a_location_on_the_map')}}</small>
                                    <div class="position-relative">
                                        <div class="input-group mb-2">
                                            <input type="text" id="location-search" class="form-control" 
                                                placeholder="{{ __('field.search_location') ?? 'Search for location (e.g., Dubai, Burj Khalifa)' }}" 
                                                autocomplete="off" />
                                            <button class="btn btn-primary" type="button" id="search-location-btn">
                                                <i class="ti ti-search"></i> {{ __('general.search') ?? 'Search' }}
                                            </button>
                                        </div>
                                        <div id="search-results" class="list-group" style="display: none; max-height: 200px; overflow-y: auto; position: absolute; z-index: 1000; width: 100%; top: 100%;"></div>
                                    </div>
                                    <div id="map"></div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="mb-1">
                                        <label for="longitude" class="form-label">{{__('field.longitude')}}  <span class="text-danger">*</span></label>
                                        <input type="text" id="longitude" class="form-control"
                                            name="longitude" placeholder="{{__('field.longitude')}}"
                                            value="{{ $item ? $item->longitude : '' }}" readonly />
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="mb-1">
                                        <label for="latitude" class="form-label">{{__('field.latitude')}}  <span class="text-danger">*</span></label>
                                        <input type="text" id="latitude" class="form-control"
                                            name="latitude" placeholder="{{__('field.latitude')}}"
                                            value="{{ $item ? $item->latitude : '' }}" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-sm submitFrom">
                            <i class="menu-icon tf-icons ti ti-check"></i>
                            <span>{{ __('general.save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endsection

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')
    @include('CenterUser.Components.translation-js')
    <script>
        $(document).ready(function() {
            var lat = $('#latitude').val();
            var lng = $('#longitude').val();
            
            // Default center (e.g., Dubai) if no coordinates
            var defaultLat = lat ? lat : 25.2048;
            var defaultLng = lng ? lng : 55.2708;
            var zoomLevel = lat ? 15 : 10;

            var map = L.map('map').setView([defaultLat, defaultLng], zoomLevel);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var marker;

            if (lat && lng) {
                marker = L.marker([lat, lng], {draggable: true}).addTo(map);
                marker.on('dragend', function(event) {
                    var position = marker.getLatLng();
                    $('#latitude').val(position.lat.toFixed(8));
                    $('#longitude').val(position.lng.toFixed(8));
                });
            }

            map.on('click', function(e) {
                var lat = e.latlng.lat.toFixed(8);
                var lng = e.latlng.lng.toFixed(8);

                $('#latitude').val(lat);
                $('#longitude').val(lng);

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng, {draggable: true}).addTo(map);
                    marker.on('dragend', function(event) {
                        var position = marker.getLatLng();
                        $('#latitude').val(position.lat.toFixed(8));
                        $('#longitude').val(position.lng.toFixed(8));
                    });
                }
            });

            // Location Search Functionality
            var searchTimeout;
            var $searchInput = $('#location-search');
            var $searchResults = $('#search-results');
            var $searchBtn = $('#search-location-btn');

            function performSearch(query) {
                if (!query || query.trim().length < 3) {
                    $searchResults.hide().empty();
                    return;
                }

                $searchResults.html('<div class="search-loading"><i class="ti ti-loader-2"></i> {{ __('admin.searching') ?? "Searching..." }}</div>').show();

                // Use Nominatim API for geocoding
                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/search',
                    method: 'GET',
                    data: {
                        q: query,
                        format: 'json',
                        limit: 5,
                        addressdetails: 1
                    },
                    headers: {
                        'User-Agent': 'Luzori Booking System'
                    },
                    success: function(data) {
                        $searchResults.empty();
                        
                        if (data && data.length > 0) {
                            data.forEach(function(result) {
                                var displayName = result.display_name;
                                // Truncate long names
                                if (displayName.length > 80) {
                                    displayName = displayName.substring(0, 80) + '...';
                                }
                                
                                var $item = $('<a href="#" class="list-group-item list-group-item-action">')
                                    .html('<strong>' + displayName + '</strong><br><small class="text-muted">' + 
                                          parseFloat(result.lat).toFixed(6) + ', ' + 
                                          parseFloat(result.lon).toFixed(6) + '</small>')
                                    .on('click', function(e) {
                                        e.preventDefault();
                                        selectLocation(result.lat, result.lon, result.display_name);
                                    });
                                
                                $searchResults.append($item);
                            });
                        } else {
                            $searchResults.html('<div class="list-group-item text-muted text-center">No results found</div>');
                        }
                    },
                    error: function() {
                        $searchResults.html('<div class="list-group-item text-danger text-center">Error searching. Please try again.</div>');
                    }
                });
            }

            function selectLocation(lat, lng, displayName) {
                var latFloat = parseFloat(lat);
                var lngFloat = parseFloat(lng);
                
                // Update coordinates
                $('#latitude').val(latFloat.toFixed(8));
                $('#longitude').val(lngFloat.toFixed(8));
                
                // Center map on location
                map.setView([latFloat, lngFloat], 15);
                
                // Update or create marker
                if (marker) {
                    marker.setLatLng([latFloat, lngFloat]);
                } else {
                    marker = L.marker([latFloat, lngFloat], {draggable: true}).addTo(map);
                    marker.on('dragend', function(event) {
                        var position = marker.getLatLng();
                        $('#latitude').val(position.lat.toFixed(8));
                        $('#longitude').val(position.lng.toFixed(8));
                    });
                }
                
                // Hide search results
                $searchResults.hide();
                $searchInput.val(displayName);
            }

            // Search on button click
            $searchBtn.on('click', function() {
                var query = $searchInput.val().trim();
                if (query) {
                    performSearch(query);
                }
            });

            // Search on Enter key
            $searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var query = $(this).val().trim();
                    if (query) {
                        performSearch(query);
                    }
                }
            });

            // Search as user types (with debounce)
            $searchInput.on('input', function() {
                clearTimeout(searchTimeout);
                var query = $(this).val().trim();
                
                if (query.length >= 3) {
                    searchTimeout = setTimeout(function() {
                        performSearch(query);
                    }, 500);
                } else {
                    $searchResults.hide().empty();
                }
            });

            // Hide search results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#location-search, #search-results, #search-location-btn').length) {
                    $searchResults.hide();
                }
            });

            // Listeners for Name
            $('#name_en').on('input', function() {
                debouncedTranslate($(this).val(), 'en', 'ar', 'name_ar');
            });
            $('#name_ar').on('input', function() {
                debouncedTranslate($(this).val(), 'ar', 'en', 'name_en');
            });

            // Listeners for City (select element needs 'change' event)
            $('#city_en').on('change', function() {
                debouncedTranslate($(this).val(), 'en', 'ar', 'city_ar');
            });
            $('#city_ar').on('change', function() {
                debouncedTranslate($(this).val(), 'ar', 'en', 'city_en');
            });

            // Listeners for Address (textarea)
            $('#address_en').on('input', function() {
                debouncedTranslate($(this).val(), 'en', 'ar', 'address_ar');
            });
            $('#address_ar').on('input', function() {
                debouncedTranslate($(this).val(), 'ar', 'en', 'address_en');
            });
        });
        
    </script>
@endsection
