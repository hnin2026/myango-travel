<footer class="bg-light text-white py-5" id="contact">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <img src="{{ asset('images/MyanGo_Logo.png') }}"
                     alt="MyanGo Travel"
                     style="height: 50px; padding: 4px 8px; border-radius: 6px;">
                <p class="mt-3 text-muted small">
                    Your trusted partner for Myanmar travel experiences.
                </p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-1">
                        <a href="{{ route('home') }}"
                           class="text-muted text-decoration-none">Home</a>
                    </li>
                    <li class="mb-1">
                        <a href="{{ route('home') }}#tours"
                           class="text-muted text-decoration-none">Tours</a>
                    </li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Contact Us</h6>
                <p class="text-muted small mb-1">
                    <i class="bi bi-geo-alt"></i> Yangon, Myanmar
                </p>
                <p class="text-muted small mb-1">
                    <i class="bi bi-envelope"></i> info@myango.com
                </p>
                <p class="text-muted small">
                    <i class="bi bi-telephone"></i> +95 9 123 456 789
                </p>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <p class="text-center text-muted small mb-0">
            © {{ date('Y') }} MyanGo Travel. All rights reserved.
        </p>
    </div>
</footer>