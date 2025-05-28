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
    
    const emailInput = document.querySelector('#email');
    if (!emailInput) return;
    
    const email = emailInput.value.trim();
    
    // Validate email
    if (!validateEmail(email)) {
        showMessage('Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'error');
        return;
    }
    
    // Save to localStorage (in a real app, this would send to a server)
    saveSubscription(email);
    
    // Show success message
    showMessage('Vielen Dank für Ihre Anmeldung!', 'success');
    
    // Reset form
    emailInput.value = '';
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
 * Show message to user
 */
function showMessage(message, type = 'info') {
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `alert alert-${type} subscription-message`;
    messageEl.textContent = message;
    
    // Find form parent
    const form = document.querySelector('#subscription-form');
    if (!form) return;
    
    // Remove any existing messages
    const existingMessage = document.querySelector('.subscription-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Insert message before form
    form.parentNode.insertBefore(messageEl, form);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageEl.remove();
    }, 5000);
}
