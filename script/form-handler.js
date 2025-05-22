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
    }
    
    // Create form data object
    const formData = new FormData(form);
    const formValues = Object.fromEntries(formData.entries());
    
    // Send as regular form data instead of JSON
    fetch('/script/send-contact.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server returned an error response');
        }
        return response.json();
    })
    .then(data => {
        // Reset button state
        if (buttonText) {
            buttonText.style.display = 'inline-block';
        }
        if (buttonLoader) {
            buttonLoader.style.display = 'none';
        }
        form.querySelector('button[type="submit"]').disabled = false;
        
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
    })
    .catch(error => {
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
