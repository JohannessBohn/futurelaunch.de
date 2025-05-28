/**
 * Newsletter subscription form handler
 */

// Response display function
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

// Save subscription to localStorage
function saveSubscription(email) {
    try {
        let subscribers = JSON.parse(localStorage.getItem('newsletterSubscribers') || '[]');
        if (!subscribers.includes(email)) {
            subscribers.push(email);
            localStorage.setItem('newsletterSubscribers', JSON.stringify(subscribers));
            return true;
        }
        return false;
    } catch (e) {
        console.error('Error saving subscription:', e);
        return false;
    }
}

// Form submission handler
window.handleNewsletterSubmit = function(event) {
    event.preventDefault();
    
    const emailInput = document.getElementById('newsletterEmail');
    const email = emailInput.value.trim();
    const submitButton = event.target.querySelector('button[type="submit"]');
    
    // Validate email
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showResponse('Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'error');
        return false;
    }
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '';
    
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
            submitButton.disabled = false;
            submitButton.innerHTML = '';
        }
    }, 300);
    
    return false;
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletterSubmit);
    }
});
