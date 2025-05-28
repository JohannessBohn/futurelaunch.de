/**
 * Newsletter subscription functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get the newsletter form if it exists
    const newsletterForm = document.getElementById('subscription-form');
    if (!newsletterForm) return;

    // Add event listener for form submission
    newsletterForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const emailInput = document.getElementById('email');
        const email = emailInput?.value.trim();
        const submitButton = event.target.querySelector('button[type="submit"]');
        
        // Validate email
        if (!email || !/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(email)) {
            showResponse('Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'error');
            return false;
        }
        
        // Show loading state
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
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
                    submitButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
                }
            }
        }, 300);
        
        return false;
    });
    
    function showResponse(message, type) {
        // Create response element if it doesn't exist
        let responseDiv = document.getElementById('newsletter-response');
        if (!responseDiv) {
            responseDiv = document.createElement('div');
            responseDiv.id = 'newsletter-response';
            responseDiv.className = 'mt-2';
            newsletterForm.appendChild(responseDiv);
        }
        
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
    
    function saveSubscription(email) {
        try {
            let subscribers = JSON.parse(localStorage.getItem('newsletterSubscribers') || '[]');
            if (!subscribers.includes(email)) {
                subscribers.push(email);
                localStorage.setItem('newsletterSubscribers', JSON.stringify(subscribers));
                
                // Also save to the subscribers storage for the dashboard
                saveToSubscribersList(email);
                
                return true;
            }
            return false;
        } catch (e) {
            console.error('Error saving subscription:', e);
            return false;
        }
    }
    
    function saveToSubscribersList(email) {
        try {
            // Also add to the dashboard subscribers list
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
});
