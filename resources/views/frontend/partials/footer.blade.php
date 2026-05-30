<footer class="front-footer" id="contact">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="footer-logo">
                    <img src="{{ asset('images/MyanGo_Logo.png') }}" alt="MyanGo Travel">
                </div>
                <p>Your trusted partner for Myanmar travel experiences.</p>
            </div>
            <div class="col-md-4">
                <h6>Quick Links</h6>
                <ul>
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('home') }}#tours">Tours</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Contact Us</h6>
                <div class="footer-contact-item">
                    <i class="bi bi-geo-alt"></i>
                    <span>Yangon, Myanmar</span>
                </div>
                <div class="footer-contact-item">
                    <i class="bi bi-envelope"></i>
                    <span>info@myango.com</span>
                </div>
                <div class="footer-contact-item">
                    <i class="bi bi-telephone"></i>
                    <span>+95 9 123 456 789</span>
                </div>
            </div>
        </div>
        <hr class="footer-divider">
        <p class="footer-copyright">
            © {{ date('Y') }} MyanGo Travel. All rights reserved.
        </p>
    </div>
</footer>