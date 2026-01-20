<!-- Contact Us Component -->
<section class="py-5" style="background-color: #212529;" id="contact">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Contact Header -->
                <div class="text-center mb-5">
                    <h2 class="display-8 fw-bold text-white mb-4">
                        {!! __('website.contact_title') !!}
                    </h2>
                    <p class="fs-5 text-light ">{{ __('website.contact_subtitle') }}</p>
                </div>
                
                <div class="row g-4">
                    <!-- Contact Information Panel -->
                    <div class="col-lg-4">
                        <div class="contact-info-card h-100 p-4 rounded-4 position-relative" style="background: linear-gradient(135deg, #343a40 0%, #495057 100%); border: 2px solid rgba(255, 193, 7, 0.3); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);">
                            <!-- Glow Effect -->
                            <div class="position-absolute top-0 start-0 w-100 h-100 rounded-4" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%); pointer-events: none;"></div>
                            
                            <div class="position-relative">
                                <h4 class="text-white fw-bold mb-4">{{ __('website.contact_info') }}</h4>
                                
                                <!-- Location -->
                                <div class="contact-item d-flex align-items-center mb-4">
                                    <div class="contact-icon me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; background-color: rgba(255, 193, 7, 0.2);">
                                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24" class="text-warning">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                    </div>
                                    <div class="me-4">
                                        <h6 class="text-white fw-bold mb-1">{{ __('website.location') }}</h6>
                                        <p class="text-light mb-0">UAE - Dubai</p>
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div class="contact-item d-flex align-items-center mb-4">
                                    <div class="contact-icon me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; background-color: rgba(255, 193, 7, 0.2);">
                                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24" class="text-warning">
                                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                        </svg>
                                    </div>
                                    <div class="me-4">
                                        <h6 class="text-white fw-bold mb-1">{{ __('website.email') }}</h6>
                                        <p class="text-light mb-0">support@etechnocode.com</p>
                                    </div>
                                </div>
                                
                                <!-- Phone -->
                                <div class="contact-item d-flex align-items-center mb-4">
                                    <div class="contact-icon me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; background-color: rgba(255, 193, 7, 0.2);">
                                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24" class="text-warning">
                                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                        </svg>
                                    </div>
                                    <div class="me-4">
                                        <h6 class="text-white fw-bold mb-1">{{ __('website.phone') }}</h6>
                                        <p class="text-light mb-0"><span class="ltr-number">+(971) 50-314-0232</span></p>
                                    </div>
                                </div>

                                <!-- WhatsApp -->
                                <div class="contact-item d-flex align-items-center">
                                    <div class="contact-icon me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px; background-color: rgba(255, 193, 7, 0.2);">
                                        <svg width="24" height="24" viewBox="0 0 32 32" fill="currentColor" class="text-warning">
                                            <path d="M19.11 17.44c-.29-.14-1.69-.83-1.95-.92-.26-.1-.45-.14-.64.14-.19.29-.74.92-.9 1.11-.17.19-.33.22-.62.07-.29-.14-1.23-.45-2.35-1.44-.87-.77-1.45-1.71-1.62-2-.17-.29-.02-.45.12-.59.12-.12.29-.33.43-.5.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.64-1.55-.88-2.12-.23-.55-.47-.48-.64-.48-.17 0-.36-.02-.55-.02-.19 0-.5.07-.76.36-.26.29-1 1-1 2.45s1.02 2.84 1.16 3.04c.14.19 2 3.05 4.85 4.28.68.29 1.21.46 1.63.59.68.22 1.29.19 1.78.12.54-.08 1.69-.69 1.93-1.36.24-.67.24-1.24.17-1.36-.07-.12-.26-.19-.55-.33z"/>
                                            <path d="M16.02 3.2C9.31 3.2 3.84 8.67 3.84 15.38c0 2.4.69 4.63 1.9 6.5L4 28.8l7.12-1.86c1.81 1.11 3.95 1.75 6.26 1.75 6.71 0 12.18-5.46 12.18-12.18S22.73 3.2 16.02 3.2zm0 21.97c-2.15 0-4.15-.67-5.8-1.81l-.41-.27-4.22 1.11 1.13-4.11-.29-.42a9.848 9.848 0 01-1.65-5.7c0-5.45 4.43-9.89 9.89-9.89 5.45 0 9.89 4.43 9.89 9.89 0 5.46-4.44 9.9-9.89 9.9z"/>
                                        </svg>
                                    </div>
                                    <div class="me-4">
                                        <h6 class="text-white fw-bold mb-1">{{ __('website.whatsapp') }}</h6>
                                        <p class="text-light mb-0"><a href="https://wa.me/971503140232" class="text-light text-decoration-none ltr-number" target="_blank">+(971) 50-314-0232</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="col-lg-8">
                        <div class="contact-form-card p-4 rounded-4" style="background-color: #343a40; border: 1px solid rgba(255, 193, 7, 0.1);">
                            <form id="contactForm" class="needs-validation" novalidate action="{{ route('contact.send') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <!-- Name Field -->
                                    <div class="col-md-6">
                                        <label for="name" class="form-label text-white fw-bold">{{ __('website.name') }}</label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="{{ __('website.your_name') }}" required style="background-color: #495057; border: 1px solid #6c757d; color: white;" onfocus="this.style.borderColor='#ffc107'" onblur="this.style.borderColor='#6c757d'">
                                        <div class="invalid-feedback">
                                            Please provide your name.
                                        </div>
                                    </div>
                                    
                                    <!-- Email Field -->
                                    <div class="col-md-6">
                                        <label for="email" class="form-label text-white fw-bold">{{ __('website.email') }}</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="{{ __('website.your_email') }}" required style="background-color: #495057; border: 1px solid #6c757d; color: white;" onfocus="this.style.borderColor='#ffc107'" onblur="this.style.borderColor='#6c757d'">
                                        <div class="invalid-feedback">
                                            Please provide a valid email address.
                                        </div>
                                    </div>
                                    
                                    <!-- Phone Field with Country Code -->
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label text-white fw-bold">{{ __('website.phone_number') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-secondary">
                                                <img id="countryFlag" src="https://flagcdn.com/w20/ae.png" alt="flag" width="20" height="14" style="border-radius: 2px;">
                                            </span>
                                            <select class="form-select flex-grow-0" id="country_code" name="country_code" aria-label="Country code" style="max-width: 120px; background-color: #495057; border: 1px solid #6c757d; color: white;">  
                                                <option value="+971" selected>+971</option>
                                                <option value="+966">+966</option>
                                                <option value="+974">+974</option>
                                                <option value="+968">+968</option>
                                                <option value="+965">+965</option>
                                                <option value="+973">+973</option>
                                                <option value="+20">+20</option>
                                                <option value="+963">+963</option>
                                                <option value="+962">+962</option>
                                                <option value="+970">+970</option>
                                                <option value="+961">+961</option>
                                                <option value="+90">+90</option>
                                                <option value="+212">+212</option>
                                                <option value="+216">+216</option>
                                                <option value="+213">+213</option>
                                                <option value="+44">+44</option>
                                                <option value="+1">+1</option>
                                                <option value="+49">+49</option>
                                                <option value="+33">+33</option>
                                                <option value="+91">+91</option>
                                                <option value="+92">+92</option>
                                            </select>
                                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="{{ __('website.phone_number') }}" style="background-color: #495057; border: 1px solid #6c757d; color: white;" onfocus="this.style.borderColor='#ffc107'" onblur="this.style.borderColor='#6c757d'">
                                        </div>
                                    </div>
                                    
                                    <!-- Subject Field -->
                                    <div class="col-md-6">
                                        <label for="subject" class="form-label text-white fw-bold">{{ __('website.subject') }}</label>
                                        <input type="text" class="form-control" id="subject" name="subject" placeholder="{{ __('website.project_subject') }}" style="background-color: #495057; border: 1px solid #6c757d; color: white;" onfocus="this.style.borderColor='#ffc107'" onblur="this.style.borderColor='#6c757d'">
                                    </div>
                                    
                                    <!-- Message Field -->
                                    <div class="col-12">
                                        <label for="message" class="form-label text-white fw-bold">{{ __('website.message') }} <span class="text-warning">*</span></label>
                                        <textarea class="form-control" id="message" name="message" rows="4" placeholder="{{ __('website.write_message') }}" required style="background-color: #495057; border: 1px solid #6c757d; color: white; resize: vertical;" onfocus="this.style.borderColor='#ffc107'" onblur="this.style.borderColor='#6c757d'"></textarea>
                                        <div class="invalid-feedback">
                                            Please provide your message.
                                        </div>
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-warning px-4 py-2 fw-bold rounded-pill" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); border: none; box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(255, 193, 7, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(255, 193, 7, 0.3)'">
                                            {{ __('website.send_message') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Form validation and submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        if (form.checkValidity()) {
            // Show loading state
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
            submitBtn.disabled = true;
            
            // Prepare form data
            const formData = new FormData(form);
            
            // Send AJAX request
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message);
                    form.reset();
                    form.classList.remove('was-validated');
                } else {
                    showErrorMessage(data.message || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('Network error. Please check your connection and try again.');
            })
            .finally(() => {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        } else {
            form.classList.add('was-validated');
        }
    });
    
    // Real-time validation
    const inputs = form.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });
});

function showSuccessMessage(message = 'Your message has been sent successfully! We\'ll get back to you soon!') {
    // Create success alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <strong>Success!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function showErrorMessage(message) {
    // Create error alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <strong>Error!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 7 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 7000);
}

// Add focus effects to form controls
document.querySelectorAll('.form-control').forEach(control => {
    control.addEventListener('focus', function() {
        this.style.borderColor = '#ffc107';
        this.style.boxShadow = '0 0 0 0.2rem rgba(255, 193, 7, 0.25)';
    });
    
    control.addEventListener('blur', function() {
        this.style.borderColor = '#6c757d';
        this.style.boxShadow = 'none';
    });
});
</script>

<script>
// Update country flag when country code changes
document.addEventListener('DOMContentLoaded', function(){
    const select = document.getElementById('country_code');
    const flagImg = document.getElementById('countryFlag');
    const map = {
        '+971': 'ae', '+966': 'sa', '+974': 'qa', '+968': 'om', '+965': 'kw', '+973': 'bh',
        '+20': 'eg', '+963': 'sy', '+962': 'jo', '+970': 'ps', '+961': 'lb', '+90': 'tr',
        '+212': 'ma', '+216': 'tn', '+213': 'dz', '+44': 'gb', '+1': 'us', '+49': 'de',
        '+33': 'fr', '+91': 'in', '+92': 'pk'
    };
    function updateFlag(){
        const code = select ? select.value : '+971';
        const cc = map[code] || 'ae';
        if(flagImg){ flagImg.src = `https://flagcdn.com/w20/${cc}.png`; }
    }
    if(select){
        select.addEventListener('change', updateFlag);
        updateFlag();
    }
});
</script>

<style>
/* Contact Section Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Section animations */
.contact-info-card {
    animation: slideInLeft 0.8s ease-out;
}

.contact-form-card {
    animation: slideInRight 0.8s ease-out;
}

/* Contact info card hover effect */
.contact-info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4) !important;
    border-color: rgba(255, 193, 7, 0.5) !important;
}

/* Contact items animation */
.contact-item {
    animation: fadeInUp 0.6s ease-out both;
}

.contact-item:nth-child(2) {
    animation-delay: 0.1s;
}

.contact-item:nth-child(3) {
    animation-delay: 0.2s;
}

.contact-item:nth-child(4) {
    animation-delay: 0.3s;
}

/* Contact icon hover effect */
.contact-icon {
    transition: all 0.3s ease;
}

.contact-item:hover .contact-icon {
    transform: scale(1.1);
    background-color: rgba(255, 193, 7, 0.3) !important;
}

/* Form styling */
.form-control {
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
}

.form-control::placeholder {
    color: #adb5bd;
}

/* RTL alignment for Arabic locale */
[dir="rtl"] input.form-control,
[dir="rtl"] textarea.form-control {
    direction: rtl;
    text-align: right;
}

/* Keep numbers LTR inside RTL layouts */
.ltr-number {
    direction: ltr !important;
    unicode-bidi: bidi-override;
}

[dir="rtl"] input.form-control::placeholder,
[dir="rtl"] textarea.form-control::placeholder {
    direction: rtl;
    text-align: right;
}

/* Button animation */
.btn-warning {
    position: relative;
    overflow: hidden;
}

.btn-warning::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.btn-warning:hover::before {
    left: 100%;
}

/* Responsive design */
@media (max-width: 768px) {
    .contact-info-card,
    .contact-form-card {
        margin-bottom: 2rem;
    }
    
    .contact-item {
        margin-bottom: 1.5rem;
    }
    
    .btn-lg {
        padding: 0.75rem 2rem;
    }
}

/* Phone input group sizing */
.input-group select[name="country_code"] {
    flex: 0 0 110px;
}

@media (max-width: 576px) {
    .input-group select[name="country_code"] {
        flex: 0 0 80px !important;
        font-size: 0.85rem;
        padding: .375rem .5rem;
    }

    .input-group #phone {
        font-size: 0.95rem;
        padding: .375rem .5rem;
    }
}

/* Country flag spacing */
.input-group-text img { display: block; }

/* Success message styling */
.alert-success {
    background-color: rgba(25, 135, 84, 0.9);
    border-color: #198754;
    color: white;
}

/* Form validation styling */
.is-valid {
    border-color: #198754 !important;
}

.is-invalid {
    border-color: #dc3545 !important;
}

/* Smooth transitions */
.contact-info-card,
.contact-form-card,
.contact-item,
.form-control,
.btn-warning {
    transition: all 0.3s ease;
}
</style>
