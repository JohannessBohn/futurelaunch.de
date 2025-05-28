    </main>
    <!-- Hauptinhalt Ende -->
    
    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-light">FutureLaunch</h5>
                    <p>Ihr Partner für digitale B2B-Vertriebslösungen. Wir optimieren Ihren Vertriebsprozess durch innovative Technologien und maßgeschneiderte Web-Lösungen.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-light">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.html" class="text-light text-decoration-none"><i class="fas fa-angle-right me-2"></i>Startseite</a></li>
                        <li><a href="services.html" class="text-light text-decoration-none"><i class="fas fa-angle-right me-2"></i>Leistungen</a></li>
                        <li><a href="about.html" class="text-light text-decoration-none"><i class="fas fa-angle-right me-2"></i>Über uns</a></li>
                        <li><a href="contact.html" class="text-light text-decoration-none"><i class="fas fa-angle-right me-2"></i>Kontakt</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4">
                    <h5 class="text-light">Newsletter</h5>
                    <p>Abonnieren Sie unseren Newsletter für Updates und exklusive Angebote.</p>
                    <form id="footer-newsletter-form" class="mt-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Ihre E-Mail-Adresse" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    <div id="footer-newsletter-success" class="alert alert-success mt-2" style="display: none;">
                        Vielen Dank für Ihr Abonnement!
                    </div>
                </div>
            </div>
            
            <hr class="my-4 bg-light">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> FutureLaunch. Alle Rechte vorbehalten.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <a href="datenschutz.html" class="text-light me-3">Datenschutz</a>
                    <a href="impressum.html" class="text-light">Impressum</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap Bundle mit Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Eigene Scripts -->
    <script src="/assets/js/main.js"></script>
    
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
    
    <!-- Newsletter-Formular-Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const footerNewsletterForm = document.getElementById('footer-newsletter-form');
            const footerNewsletterSuccess = document.getElementById('footer-newsletter-success');
            
            if (footerNewsletterForm) {
                footerNewsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const email = this.querySelector('input[type="email"]').value;
                    if (!email) return;
                    
                    // FormData für den POST-Request erstellen
                    const formData = new FormData();
                    formData.append('email', email);
                    
                    // POST-Request an subscribe.php senden
                    fetch('subscribe.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            footerNewsletterForm.reset();
                            if (footerNewsletterSuccess) {
                                footerNewsletterSuccess.style.display = 'block';
                                setTimeout(() => {
                                    footerNewsletterSuccess.style.display = 'none';
                                }, 5000);
                            }
                        } else {
                            alert(data.message || 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.');
                        }
                    })
                    .catch(error => {
                        console.error('Newsletter-Fehler:', error);
                        alert('Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.');
                    });
                });
            }
        });
    </script>
</body>
</html>
