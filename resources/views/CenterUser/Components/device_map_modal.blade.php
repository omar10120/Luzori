<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div class="modal fade" id="deviceLocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('field.device_location') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="deviceLocationMap" style="height: 420px; width: 100%;"></div>
                <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted" id="deviceLocationCoords">—</small>
                    <a href="#" id="deviceLocationOsmLink" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                        OpenStreetMap
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var map = null;
    var marker = null;

    function openDeviceLocation(lat, lng) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (!isFinite(lat) || !isFinite(lng)) {
            alert(@json(__('field.no_location')));
            return;
        }

        var modalEl = document.getElementById('deviceLocationModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        document.getElementById('deviceLocationCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        var osmUrl = 'https://www.openstreetmap.org/?mlat=' + lat + '&mlon=' + lng + '#map=16/' + lat + '/' + lng;
        document.getElementById('deviceLocationOsmLink').href = osmUrl;

        setTimeout(function () {
            if (!map) {
                map = L.map('deviceLocationMap');
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
            }
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker([lat, lng]).addTo(map);
            map.setView([lat, lng], 15);
            map.invalidateSize();
        }, 250);
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-show-location]');
        if (!btn) return;
        e.preventDefault();
        openDeviceLocation(btn.getAttribute('data-lat'), btn.getAttribute('data-lng'));
    });

    window.openDeviceLocation = openDeviceLocation;
})();
</script>
