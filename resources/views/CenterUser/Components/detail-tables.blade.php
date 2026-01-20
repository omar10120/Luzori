<!-- Detail Tables Section -->
<div class="container mt-4" id="detailTablesSection" style="display: none;">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0" id="detailTableTitle"></h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hideDetailTables()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="card-body">
                    <div id="detailTableContent">
                        <!-- Table content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.clickable-stat-card {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
}

.clickable-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.clickable-stat-card:hover::after {
    content: '';
    position: absolute;
    top: 10px;
    right: 10px;
    width: 20px;
    height: 20px;
    background: rgba(0,0,0,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 1;
    transition: opacity 0.2s ease;
}

.clickable-stat-card:hover::before {
    content: '';
    position: absolute;
    top: 10px;
    right: 10px;
    width: 20px;
    height: 20px;
    background: rgba(0,0,0,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    z-index: 10;
    opacity: 1;
    transition: opacity 0.2s ease;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z'/%3E%3Cpath d='M13 13l6 6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 12px;
}

.clickable-stat-card:active {
    transform: translateY(0);
}

.clickable-stat-card::after,
.clickable-stat-card::before {
    opacity: 0;
    transition: opacity 0.2s ease;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize clickable statistics
    initClickableStatistics();
});

function initClickableStatistics() {
    // Add click event listeners to all clickable cards
    const clickableCards = document.querySelectorAll('.clickable-stat-card');
    
    clickableCards.forEach(card => {
        card.addEventListener('click', function() {
            const type = this.getAttribute('data-type');
            showDetailTable(type);
        });
    });
}

function showDetailTable(type) {
    const section = document.getElementById('detailTablesSection');
    const title = document.getElementById('detailTableTitle');
    const content = document.getElementById('detailTableContent');
    
    // Show loading
    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p class="mt-2">Loading...</p></div>';
    section.style.display = 'block';
    
    // Set title based on type
    const titles = {
        'services': '{{ __("field.services") }} {{ __("locale.list") }}',
        'customers': '{{ __("field.customers") }} {{ __("locale.list") }}',
        'bookings': '{{ __("field.today_bookings") }} {{ __("locale.list") }}',
        'revenue': '{{ __("field.today_revenue") }} {{ __("locale.details") }}',
        'coupons': '{{ __("field.active_coupons") }} {{ __("locale.list") }}',
        'workers': '{{ __("field.active_workers") }} {{ __("locale.list") }}',
        'products': '{{ __("field.available_products") }} {{ __("locale.list") }}'
    };
    
    title.innerHTML = titles[type] || 'Details';
    
    // Fetch data via AJAX
    fetch(`/center_user/dashboard/details/${type}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            content.innerHTML = data.html;
            // Re-initialize pagination click handlers
            initPaginationHandlers(type);
            // Scroll to the table section
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            content.innerHTML = '<div class="alert alert-danger">Error loading data: ' + (data.message || 'Unknown error') + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<div class="alert alert-danger">Error loading data. Please try again.</div>';
    });
}

function hideDetailTables() {
    document.getElementById('detailTablesSection').style.display = 'none';
}

function initPaginationHandlers(type) {
    // Handle pagination links
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            if (url) {
                // Extract page parameter from URL
                const urlParams = new URLSearchParams(url.split('?')[1]);
                const page = urlParams.get('page') || 1;
                
                // Show loading
                const content = document.getElementById('detailTableContent');
                content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p class="mt-2">Loading...</p></div>';
                
                // Fetch data with page parameter
                fetch(`/center_user/dashboard/details/${type}?page=${page}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        content.innerHTML = data.html;
                        // Re-initialize pagination click handlers for the new content
                        initPaginationHandlers(type);
                        // Scroll to the table section
                        const section = document.getElementById('detailTablesSection');
                        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else {
                        content.innerHTML = '<div class="alert alert-danger">Error loading data: ' + (data.message || 'Unknown error') + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<div class="alert alert-danger">Error loading data. Please try again.</div>';
                });
            }
        });
    });
}
</script>
@endpush
