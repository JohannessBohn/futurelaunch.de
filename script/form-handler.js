/**
 * Handles contact form submission via fetch API
 */
function submitContactForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('contactForm');
    const submitButton = document.getElementById('submitButton');
    const formResponse = document.getElementById('formResponse');
    
    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Senden...';
    
    // Get form data
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Send data to server
    fetch('/script/send-contact.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Show success message with animation
            formResponse.className = 'form-response success';
            formResponse.textContent = result.message || 'Vielen Dank für Ihre Nachricht! Wir werden uns in Kürze bei Ihnen melden.';
            formResponse.style.display = 'block';
            
            // Trigger confetti effect
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
            
            // Reset form
            form.reset();
            
            // Add success animation to form
            form.style.transform = 'scale(0.98)';
            setTimeout(() => {
                form.style.transform = 'scale(1)';
            }, 200);
        } else {
            throw new Error(result.message || result.error || 'Ein Fehler ist aufgetreten');
        }
    })
    .catch(error => {
        console.error('Form submission error:', error);
        // Show error message
        formResponse.className = 'form-response error';
        formResponse.textContent = error.message || 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
        formResponse.style.display = 'block';
    })
    .finally(() => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = 'Senden';
        
        // Clear message after 5 seconds
        setTimeout(() => {
            formResponse.style.display = 'none';
        }, 5000);
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
    
    // Show success indicator
    button.innerHTML = '✓';
    button.style.backgroundColor = '#4CAF50';
    
    // Reset after delay
    setTimeout(() => {
        button.innerHTML = originalButtonHtml;
        button.disabled = false;
        button.style.backgroundColor = '';
        form.reset();
    }, 2000);
    
    return false;
}

// Add privacy policy checkbox
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const privacyDiv = document.querySelector('.privacy-consent');
    
    if (privacyDiv && !privacyDiv.querySelector('input[type="checkbox"]')) {
        privacyDiv.innerHTML = `
            <div class="checkbox-group">
                <input type="checkbox" id="privacy" name="privacy" required>
                <label for="privacy">
                    Ich habe die <a href="/Datenschutz.html" target="_blank">Datenschutzerklärung</a> gelesen und stimme der Verarbeitung meiner Daten zu.
                </label>
            </div>
        `;
    }
});
async function submitContactForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('contactForm');
    const submitButton = form.querySelector('button[type="submit"]') || document.getElementById('submitButton');
    const formResponse = document.getElementById('formResponse') || document.getElementById('formResponse');
    
    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Senden...';
    
    try {
        // Sammle Formulardaten
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        console.log('Kontaktformulardaten:', data);
        
        // Simuliere eine erfolgreiche Antwort für lokale Entwicklung
        // In einer Produktionsumgebung würde hier ein echter API-Aufruf stehen
        
        // Simuliere Netzwerklatenz
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Simuliere eine erfolgreiche Antwort
        const result = {
            success: true,
            message: 'Vielen Dank für Ihre Nachricht! Wir werden uns in Kürze bei Ihnen melden.'
        };
        
        // Zeige Erfolgsmeldung an
        formResponse.className = 'form-response success';
        formResponse.textContent = result.message;
        formResponse.style.display = 'block';
        
        // Setze das Formular zurück
        form.reset();
        
    } catch (error) {
        console.error('Form submission error:', error);
        // Zeige Fehlermeldung an
        formResponse.className = 'form-response error';
        formResponse.textContent = error.message || 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
        formResponse.style.display = 'block';
    } finally {
        // Setze den Button-Status zurück
        submitButton.disabled = false;
        submitButton.innerHTML = 'Senden';
        
        // Blende die Nachricht nach 5 Sekunden aus
        setTimeout(() => {
            if (formResponse) {
                formResponse.style.display = 'none';
            }
        }, 5000);
    }
    
    return false;
}