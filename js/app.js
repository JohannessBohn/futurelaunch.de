/**
 * Main application JavaScript for FutureLaunch
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize loading screen
    setTimeout(function() {
        document.body.classList.add('loaded');
        setTimeout(function() {
            const loadingScreen = document.querySelector('.loading-screen');
            if (loadingScreen) loadingScreen.classList.add('fade-out');
        }, 500);
    }, 1500);
    
    // Initialize testimonials if they exist
    initTestimonials();
    
    // Setup scroll animations
    initScrollAnimations();
    
    // Setup form handlers
    initFormHandlers();
    
    // Setup newsletter functionality
    initNewsletterHandlers();
    
    console.log('Site initialized successfully');
});

// Testimonial Slider Functionality
function initTestimonials() {
    const testimonials = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.testimonial-dot');
    
    if (testimonials.length === 0) return;
    
    let currentTestimonial = 0;
    
    function showTestimonial(index) {
        testimonials.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        testimonials[index].classList.add('active');
        dots[index].classList.add('active');
    }
    
    function changeTestimonial(direction) {
        currentTestimonial = (currentTestimonial + direction + testimonials.length) % testimonials.length;
        showTestimonial(currentTestimonial);
    }
    
    window.goToTestimonial = function(index) {
        currentTestimonial = index;
        showTestimonial(currentTestimonial);
    };
    
    window.changeTestimonial = changeTestimonial;
    
    // Auto-rotate testimonials
    setInterval(() => {
        changeTestimonial(1);
    }, 5000);
}

// Scroll animation for process steps
function initScrollAnimations() {
    const processSteps = document.querySelectorAll('#process .step');

    if (processSteps.length > 0) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        processSteps.forEach(step => {
            observer.observe(step);
        });
    }
}

// Form Handling
function initFormHandlers() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', submitContactForm);
    }
}

function submitContactForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const formMessage = document.getElementById('formResponse');
    
    if (!formMessage) return false;
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Wird gesendet...';
    
    // Clear previous messages
    formMessage.className = 'form-response';
    formMessage.style.display = 'none';

    // Get form data
    const formData = new FormData(form);
    
    // Send data to server
    fetch('send_email.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Show success message
        formMessage.textContent = data.message;
        formMessage.className = 'form-response success';
        formMessage.style.display = 'block';
        
        // Reset form on success
        if (data.success) {
            form.reset();
            
            // Scroll to show the success message
            formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    })
    .catch(error => {
        // Show error message
        formMessage.textContent = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut oder kontaktieren Sie uns direkt per E-Mail.';
        formMessage.className = 'form-response error';
        formMessage.style.display = 'block';
        console.error('Error:', error);
    })
    .finally(() => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = 'Nachricht senden';
        
        // Hide message after 10 seconds
        setTimeout(() => {
            formMessage.style.display = 'none';
        }, 10000);
    });

    return false;
}

// Newsletter Functionality
function initNewsletterHandlers() {
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(event) {
            return handleNewsletterSubmit(event);
        });
    }
}

function saveSubscription(email) {
    try {
        let subscribers = JSON.parse(localStorage.getItem('newsletterSubscribers') || '[]');
        if (!subscribers.includes(email)) {
            subscribers.push(email);
            localStorage.setItem('newsletterSubscribers', JSON.stringify(subscribers));
            
            // Also add to dashboard subscribers
            saveToSubscribersDashboard(email);
            return true;
        }
        return false;
    } catch (e) {
        console.error('Error saving subscription:', e);
        return false;
    }
}

function saveToSubscribersDashboard(email) {
    try {
        const STORAGE_KEY = 'futurelaunch_subscribers';
        let subscribers = [];
        
        try {
            const storedData = localStorage.getItem(STORAGE_KEY);
            if (storedData) {
                subscribers = JSON.parse(storedData);
            }
        } catch (e) {
            console.error('Error parsing subscribers data:', e);
        }
        
        // Check if email already exists
        if (!subscribers.some(sub => sub.email === email)) {
            // Add new subscriber
            subscribers.push({
                email: email,
                date: new Date().toISOString()
            });
            
            // Save to localStorage
            localStorage.setItem(STORAGE_KEY, JSON.stringify(subscribers));
        }
    } catch (e) {
        console.error('Error adding to dashboard subscribers:', e);
    }
}

window.handleNewsletterSubmit = function(event) {
    event.preventDefault();
    
    const emailInput = document.getElementById('newsletterEmail');
    if (!emailInput) return false;
    
    const email = emailInput.value.trim();
    const submitButton = event.target.querySelector('button[type="submit"]');
    
    // Validate email
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showResponse('Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'error');
        return false;
    }
    
    // Show loading state
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '';
    }
    
    // Process after a short delay for better UX
    setTimeout(() => {
        try {
            if (saveSubscription(email)) {
                showResponse('Vielen Dank für Ihr Abonnement!', 'success');
                event.target.reset();
                
                // Show confetti effect if available
                if (typeof confetti === 'function') {
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 }
                    });
                }
            } else {
                showResponse('Diese E-Mail ist bereits angemeldet.', 'info');
            }
        } catch (e) {
            console.error('Error:', e);
            showResponse('Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.', 'error');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = '';
            }
        }
    }, 300);
    
    return false;
};

function showResponse(message, type) {
    const responseDiv = document.getElementById('newsletterResponse');
    if (!responseDiv) return;
    
    responseDiv.textContent = message;
    responseDiv.className = 'mt-2';
    responseDiv.classList.add(type === 'error' ? 'text-danger' : 
                            type === 'success' ? 'text-success' : 'text-info');
    responseDiv.style.display = 'block';
    responseDiv.style.opacity = '1';
    responseDiv.style.transition = 'opacity 0.5s ease';
    
    // Hide message after 5 seconds
    setTimeout(() => {
        responseDiv.style.opacity = '0';
        setTimeout(() => {
            responseDiv.style.display = 'none';
        }, 500);
    }, 5000);
}
