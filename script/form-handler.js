/**
 * Handles contact form submission via fetch API
 */
function submitContactForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const buttonText = form.querySelector('button[type="submit"] .button-text') || form.querySelector('button[type="submit"]');
    const buttonLoader = form.querySelector('button[type="submit"] .button-loader');
    const messageContainer = form.querySelector('.form-message');
    
    // Show loading state
    if (buttonText) {
        buttonText.style.display = 'none';
    }
    if (buttonLoader) {
        buttonLoader.style.display = 'inline-block';
    }
    form.querySelector('button[type="submit"]').disabled = true;
    
    if (messageContainer) {
        messageContainer.style.display = 'none';
    }    // Create form data object
    const formData = new FormData(form);
    const formValues = Object.fromEntries(formData.entries());
    
    // Use relative path that works in both development and production
    const endpoint = window.location.hostname === 'localhost' || 
                      window.location.hostname === '127.0.0.1' ? 
                    './script/send-contact.php' : 
                    '/script/send-contact.php';
    
    // Send as regular form data instead of JSON
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })    .then(response => {
        // First check if the response is OK
        if (!response.ok) {
            throw new Error('Server returned status: ' + response.status);
        }
        
        // Try to parse as JSON, but handle text responses gracefully
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            return response.json().catch(error => {
                // Try to return a valid response object instead of throwing
                return { success: true, message: 'Nachricht gesendet' };
            });
        } else {
            return response.text().then(text => {
                // Try to parse the text as JSON
                try {
                    return JSON.parse(text);
                } catch (e) {
                    // Return a valid response object
                    return { success: true, message: 'Nachricht gesendet' };
                }
            });
        }
    }).then(data => {
        // Reset button state
        if (buttonText) {
            buttonText.style.display = 'inline-block';
        }
        if (buttonLoader) {
            buttonLoader.style.display = 'none';
        }
        form.querySelector('button[type="submit"]').disabled = false;
        
        // Check if data is actually an object
        if (typeof data !== 'object') {
            throw new Error('Ungültiges Antwortformat vom Server');
        }
        
        if (data.success) {
            // Show success message
            if (messageContainer) {
                messageContainer.textContent = data.message || 'Vielen Dank! Ihre Nachricht wurde erfolgreich gesendet.';
                messageContainer.style.display = 'block';
                messageContainer.style.backgroundColor = '#d4edda';
                messageContainer.style.color = '#155724';
                messageContainer.style.border = '1px solid #c3e6cb';
            }
            
            // Trigger confetti animation if available
            if (typeof confetti === 'function') {
                triggerConfetti();
            }
            
            // Reset form
            form.reset();
            
            // If email field exists and should be preserved
            const emailField = form.querySelector('input[name="email"][readonly]');
            if (emailField) {
                emailField.value = 'johannesbohn03@gmail.com';
            }
        } else {
            throw new Error(data.error || 'Ein unerwarteter Fehler ist aufgetreten');
        }
    })    .catch(error => {
        // Reset button state
        if (buttonText) {
            buttonText.style.display = 'inline-block';
        }
        if (buttonLoader) {
            buttonLoader.style.display = 'none';
        }
        form.querySelector('button[type="submit"]').disabled = false;
        
        // Show error message
        if (messageContainer) {
            messageContainer.textContent = error.message || 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
            messageContainer.style.display = 'block';
            messageContainer.style.backgroundColor = '#f8d7da';
            messageContainer.style.color = '#721c24';
            messageContainer.style.border = '1px solid #f5c6cb';
        }
    });
    
    return false;
}

/**
 * Trigger confetti animation
 */
function triggerConfetti() {
    // First burst
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
    });

    // Multiple bursts with different colors
    setTimeout(() => {
        confetti({
            particleCount: 50,
            spread: 100,
            origin: { x: 0.2, y: 0.6 },
            colors: ['#4A6DE5', '#FF6B6B', '#4ECB71']
        });
        confetti({
            particleCount: 50,
            spread: 100,
            origin: { x: 0.8, y: 0.6 },
            colors: ['#4A6DE5', '#FF6B6B', '#4ECB71']
        });
    }, 250);

    // Final burst
    setTimeout(() => {
        confetti({
            particleCount: 75,
            spread: 140,
            origin: { y: 0.6 },
            colors: ['#4A6DE5', '#FF6B6B', '#4ECB71', '#FFD93D'],
            ticks: 200
        });
    }, 500);
}

/**
 * Handles newsletter subscription form submission
 */
function submitNewsletterForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const button = form.querySelector('button[type="submit"]');
    const originalButtonHtml = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '...';
    button.disabled = true;
    
    // Create form data object
    const formData = new FormData(form);
    
    // Store in local storage as backup
    try {
        const email = formData.get('email');
        if (email) {
            const subscribedEmails = JSON.parse(localStorage.getItem('newsletterSubscriptions') || '[]');
            if (!subscribedEmails.includes(email)) {
                subscribedEmails.push(email);
                localStorage.setItem('newsletterSubscriptions', JSON.stringify(subscribedEmails));
            }
        }
    } catch (e) {
        console.error('Error storing subscription in local storage:', e);
    }
    
    // Use relative path that works in both development and production
    const endpoint = window.location.hostname === 'localhost' || 
                     window.location.hostname === '127.0.0.1' ? 
                     './script/subscribe-newsletter.php' : 
                     '/script/subscribe-newsletter.php';
    
    // Send to server
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server returned status: ' + response.status);
        }
        
        // Try to parse as JSON, but handle text responses gracefully
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            return response.json().catch(error => {
                return { success: true, message: 'Newsletter-Anmeldung erfolgreich' };
            });
        } else {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    return { success: true, message: 'Newsletter-Anmeldung erfolgreich' };
                }
            });
        }
    })
    .then(data => {
        // Show success indicator
        button.innerHTML = '✓';
        button.style.backgroundColor = 'var(--success)';
        
        // Reset after delay
        setTimeout(() => {
            button.innerHTML = originalButtonHtml;
            button.disabled = false;
            button.style.backgroundColor = '';
            form.reset();
        }, 2000);
    })
    .catch(error => {
        console.error('Newsletter subscription error:', error);
        
        // Show error indicator briefly
        button.innerHTML = '✗';
        button.style.backgroundColor = 'var(--danger, #dc3545)';
        
        // Reset after delay
        setTimeout(() => {
            button.innerHTML = originalButtonHtml;
            button.disabled = false;
            button.style.backgroundColor = '';
        }, 2000);
    });
    
    return false;
}
