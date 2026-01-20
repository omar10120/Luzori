<!-- Our Work Component -->
 




<style>
/* Device Animation */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

.device-container {
    animation: float 3s ease-in-out infinite;
}

.device-container:nth-child(1) {
    animation-delay: 0s;
}

.device-container:nth-child(2) {
    animation-delay: 1s;
}

.device-container:nth-child(3) {
    animation-delay: 2s;
}

/* Device Hover Effects */
.device-container:hover {
    animation-play-state: paused;
}

/* Button Glow Effect */
.btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    border: none;
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

/* Responsive Design */
@media (max-width: 768px) {
    .device-container {
        margin-bottom: 2rem;
    }
    
    .device-container img {
        max-width: 150px !important;
    }
    
    .device-container svg {
        width: 60px !important;
        height: 120px !important;
    }
}

/* Text Animation */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.text-center > * {
    animation: slideInUp 0.8s ease-out;
}

.text-center > *:nth-child(1) { animation-delay: 0.1s; }
.text-center > *:nth-child(2) { animation-delay: 0.2s; }
.text-center > *:nth-child(3) { animation-delay: 0.3s; }
</style>
