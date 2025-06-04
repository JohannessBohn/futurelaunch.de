/**
 * FutureLaunch Subscription Handling
 * Client-side implementation that doesn't require PHP/XAMPP
 */

document.addEventListener('DOMContentLoaded', () => {
    const subscriptionForm = document.querySelector('#subscription-form');
    
    if (subscriptionForm) {
        subscriptionForm.addEventListener('submit', handleSubscription);
    }
});

/**
 * Handle the subscription form submission
 */
function handleSubscription(e) {
    e.preventDefault();
    
    const form = e.target;
    const emailInput = form.querySelector('input[type="email"]');
    const submitButton = form.querySelector('button[type="submit"]');
    
    if (!emailInput) {
        console.error('Email input not found');
        return;
    }
    
    const email = emailInput.value.trim();
    
    // Validate email
    if (!validateEmail(email)) {
        showMessage('Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'error');
        emailInput.focus();
        return;
    }
    
    // Show loading state
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Wird verarbeitet...';
    
    try {
        // Check if already subscribed
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
        const isAlreadySubscribed = subscribers.some(sub => sub.email.toLowerCase() === email.toLowerCase());
        
        if (isAlreadySubscribed) {
            showMessage('Diese E-Mail ist bereits angemeldet.', 'info');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            return;
        }
        
        // Add new subscriber
        subscribers.push({
            email: email,
            date: new Date().toISOString(),
            source: 'website'
        });
        
        // Save to localStorage
        localStorage.setItem(STORAGE_KEY, JSON.stringify(subscribers));
        
        // Show success message
        showMessage('Vielen Dank für Ihre Anmeldung!', 'success');
        
        // Reset form
        form.reset();
        
        // Trigger confetti effect if available
        if (typeof confetti === 'function') {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
        }
        
    } catch (error) {
        console.error('Subscription error:', error);
        showMessage('Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.', 'error');
    } finally {
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    }
}

/**
 * Validate email format
 */
function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Save subscription to localStorage
 */
function saveSubscription(email) {
    const STORAGE_KEY = 'futurelaunch_subscribers';
    
    // Get existing subscribers
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
    if (subscribers.some(sub => sub.email === email)) {
        showMessage('Diese E-Mail ist bereits angemeldet.', 'warning');
        return;
    }
    
    // Add new subscriber
    subscribers.push({
        email: email,
        date: new Date().toISOString()
    });
    
    // Save to localStorage
    localStorage.setItem(STORAGE_KEY, JSON.stringify(subscribers));
}

/**
 * Show message to user with improved styling and animations
 */
function showMessage(message, type = 'info') {
    try {
        // Create message element with better styling
        const messageEl = document.createElement('div');
        messageEl.className = `subscription-message alert alert-${type} d-flex align-items-center`;
        messageEl.setAttribute('role', 'alert');
        messageEl.style.cssText = `
            margin: 1rem 0;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        `;
        
        // Add icon based on message type
        let icon = '';
        switch(type) {
            case 'success':
                icon = '<i class="fas fa-check-circle me-2"></i>';
                messageEl.style.backgroundColor = '#d4edda';
                messageEl.style.color = '#155724';
                messageEl.style.border = '1px solid #c3e6cb';
                break;
            case 'error':
                icon = '<i class="fas fa-exclamation-circle me-2"></i>';
                messageEl.style.backgroundColor = '#f8d7da';
                messageEl.style.color = '#721c24';
                messageEl.style.border = '1px solid #f5c6cb';
                break;
            case 'warning':
            case 'info':
            default:
                icon = '<i class="fas fa-info-circle me-2"></i>';
                messageEl.style.backgroundColor = '#e2e3e5';
                messageEl.style.color = '#383d41';
                messageEl.style.border = '1px solid #d6d8db';
        }
        
        messageEl.innerHTML = `
            <div style="display: flex; align-items: center;">
                ${icon}
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close" aria-label="Close" style="font-size: 0.75rem;"></button>
        `;
        
        // Find target container (try to find a form or use body as fallback)
        let targetContainer = document.querySelector('#subscription-form, form, body');
        if (!targetContainer) {
            console.error('Could not find target container for message');
            return;
        }
        
        // Insert message at the beginning of the container
        targetContainer.insertBefore(messageEl, targetContainer.firstChild);
        
        // Animate in
        setTimeout(() => {
            messageEl.style.opacity = '1';
            messageEl.style.transform = 'translateY(0)';
        }, 10);
        
        // Add close button functionality
        const closeButton = messageEl.querySelector('.btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                hideMessage(messageEl);
            });
        }
        
        // Auto-hide after 8 seconds (longer for better readability)
        const autoHideTimer = setTimeout(() => {
            hideMessage(messageEl);
        }, 8000);
        
        // Pause auto-hide on hover
        messageEl.addEventListener('mouseenter', () => {
            clearTimeout(autoHideTimer);
        });
        
        messageEl.addEventListener('mouseleave', () => {
            setTimeout(() => hideMessage(messageEl), 2000);
        });
        
    } catch (error) {
        console.error('Error showing message:', error);
    }
}

/**
 * Hide message with animation
 */
function hideMessage(messageEl) {
    if (!messageEl) return;
    
    messageEl.style.opacity = '0';
    messageEl.style.transform = 'translateY(-10px)';
    
    // Remove from DOM after animation completes
    setTimeout(() => {
        if (messageEl && messageEl.parentNode) {
            messageEl.parentNode.removeChild(messageEl);
        }
    }, 300);
}
