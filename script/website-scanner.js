/**
 * Website analysis functionality for FutureLaunch
 * Handles scanning and analyzing websites
 */

// Simulate analysis scores with randomized values (for demo purposes)
function getRandomScore(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

// Update the score display with animation
function updateScoreDisplay(elementId, score) {
    const scoreCard = document.getElementById(elementId);
    if (!scoreCard) return;
    
    const scoreValue = scoreCard.querySelector('.score-value');
    const scoreFill = scoreCard.querySelector('.score-fill');
    
    // Set appropriate color based on score
    let scoreColor = 'var(--success)';
    if (score < 50) {
        scoreColor = 'var(--danger)';
    } else if (score < 80) {
        scoreColor = 'var(--warning)';
    }
    
    // Update display
    scoreValue.textContent = score;
    scoreValue.style.color = scoreColor;
    scoreFill.style.stroke = scoreColor;
    scoreFill.style.strokeDasharray = `${score} 100`;
}

// Generate metrics for each score category
function generateMetrics(category) {
    const metrics = {
        performance: [
            { name: 'Ladezeit', value: getRandomScore(50, 98) + '%' },
            { name: 'Zeit bis Interaktiv', value: getRandomScore(1, 5) + ' s' },
            { name: 'First Contentful Paint', value: getRandomScore(1, 3) + ' s' },
            { name: 'Cumulative Layout Shift', value: '0.' + getRandomScore(0, 9) }
        ],
        seo: [
            { name: 'Meta-Tags', value: getRandomScore(50, 100) + '%' },
            { name: 'Seitenstruktur', value: getRandomScore(50, 100) + '%' },
            { name: 'Bildoptimierung', value: getRandomScore(50, 100) + '%' },
            { name: 'Interne Verlinkung', value: getRandomScore(50, 100) + '%' }
        ],
        mobile: [
            { name: 'Viewport Einstellungen', value: getRandomScore(50, 100) + '%' },
            { name: 'Touch-Elemente', value: getRandomScore(50, 100) + '%' },
            { name: 'Responsive Design', value: getRandomScore(50, 100) + '%' },
            { name: 'Lesbarkeit', value: getRandomScore(50, 100) + '%' }
        ],
        security: [
            { name: 'HTTPS', value: getRandomScore(50, 100) + '%' },
            { name: 'Cookie-Sicherheit', value: getRandomScore(50, 100) + '%' },
            { name: 'Aktuelle Software', value: getRandomScore(50, 100) + '%' },
            { name: 'Sicherheitsheader', value: getRandomScore(50, 100) + '%' }
        ]
    };
    
    return metrics[category] || [];
}

// Display metrics in the appropriate container
function displayMetrics(categoryId, metrics) {
    const container = document.getElementById(categoryId + 'Metrics');
    if (!container) return;
    
    container.innerHTML = '';
    
    metrics.forEach(metric => {
        const li = document.createElement('li');
        li.innerHTML = `
            <span>${metric.name}</span>
            <span class="metric-value">${metric.value}</span>
        `;
        container.appendChild(li);
    });
}

// Generate recommendations based on scores
function generateRecommendations(scores) {
    const recommendations = [];
    
    // Performance recommendations
    if (scores.performance < 70) {
        recommendations.push({
            priority: 'hoch',
            title: 'Website-Geschwindigkeit verbessern',
            description: 'Ihre Website lädt langsamer als empfohlen. Dies kann zu einer höheren Absprungrate führen.',
            actions: [
                'Bilder optimieren und komprimieren',
                'Browser-Caching aktivieren',
                'JavaScript und CSS minimieren'
            ]
        });
    }
    
    // SEO recommendations
    if (scores.seo < 80) {
        recommendations.push({
            priority: 'mittel',
            title: 'SEO-Optimierung durchführen',
            description: 'Ihre Website hat Potenzial für bessere Suchmaschinen-Rankings.',
            actions: [
                'Meta-Tags optimieren',
                'Seitenstruktur mit H1-H6 verbessern',
                'Alt-Texte für Bilder hinzufügen'
            ]
        });
    }
    
    // Mobile recommendations
    if (scores.mobile < 75) {
        recommendations.push({
            priority: 'hoch',
            title: 'Mobile-Optimierung verbessern',
            description: 'Ihre Website ist nicht optimal für mobile Geräte angepasst.',
            actions: [
                'Responsive Design implementieren',
                'Touch-Elemente vergrößern',
                'Viewport-Einstellungen prüfen'
            ]
        });
    }
    
    // Security recommendations
    if (scores.security < 70) {
        recommendations.push({
            priority: 'hoch',
            title: 'Sicherheitsmaßnahmen erhöhen',
            description: 'Ihre Website weist Sicherheitslücken auf, die behoben werden sollten.',
            actions: [
                'Auf HTTPS umstellen',
                'Sicherheits-Header implementieren',
                'Software und Plugins aktualisieren'
            ]
        });
    }
    
    // Add some general recommendations
    recommendations.push({
        priority: 'niedrig',
        title: 'Nutzererfahrung verbessern',
        description: 'Kleine Verbesserungen können einen großen Unterschied machen.',
        actions: [
            'Call-to-Action-Elemente deutlicher gestalten',
            'Kontrastfarben für bessere Lesbarkeit verwenden',
            'Formularfelder optimieren'
        ]
    });
    
    return recommendations;
}

// Display recommendations in the container
function displayRecommendations(recommendations) {
    const container = document.querySelector('.recommendations-grid');
    if (!container) return;
    
    container.innerHTML = '';
    
    recommendations.forEach(rec => {
        const item = document.createElement('div');
        item.className = 'recommendation-item';
        item.setAttribute('data-aos', 'fade-up');
        
        let actionsList = '';
        rec.actions.forEach(action => {
            actionsList += `<li><i class="fas fa-check-circle"></i> ${action}</li>`;
        });
        
        item.innerHTML = `
            <span class="priority-badge ${rec.priority}">${rec.priority === 'hoch' ? 'Hohe Priorität' : rec.priority === 'mittel' ? 'Mittlere Priorität' : 'Niedrige Priorität'}</span>
            <h4>${rec.title}</h4>
            <p>${rec.description}</p>
            <ul class="action-list">
                ${actionsList}
            </ul>
        `;
        
        container.appendChild(item);
    });
}

// Update progress bar and status during scanning
function updateProgress(percent, status, step) {
    const progressBar = document.getElementById('scanProgress');
    const statusText = document.getElementById('scanStatus');
    
    if (progressBar) {
        progressBar.style.width = percent + '%';
    }
    
    if (statusText) {
        statusText.textContent = status;
    }
    
    // Update step indicators
    if (step > 0) {
        document.querySelectorAll('.step').forEach(el => {
            if (parseInt(el.getAttribute('data-step')) <= step) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    }
}

// Main scan function that simulates website analysis
async function scanWebsite(event) {
    event.preventDefault();
    
    const form = event.target;
    const urlInput = form.querySelector('input[type="url"]');
    const url = urlInput.value;
    
    // Validate URL
    if (!url) {
        alert('Bitte geben Sie eine gültige URL ein.');
        return;
    }
    
    // Show progress container
    const progressContainer = document.querySelector('.progress-container');
    if (progressContainer) {
        progressContainer.style.display = 'block';
    }
    
    // Hide results if previously shown
    const resultsContainer = document.getElementById('results');
    if (resultsContainer) {
        resultsContainer.style.display = 'none';
    }
    
    // Step 1: Check accessibility
    updateProgress(10, 'Prüfe Erreichbarkeit der Website...', 1);
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    // Step 2: Analyze performance
    updateProgress(35, 'Analysiere Performance...', 2);
    await new Promise(resolve => setTimeout(resolve, 1500));
    
    // Step 3: Check SEO factors
    updateProgress(65, 'Prüfe SEO-Faktoren...', 3);
    await new Promise(resolve => setTimeout(resolve, 1500));
    
    // Step 4: Test mobile compatibility
    updateProgress(90, 'Teste Mobilgeräte-Kompatibilität...', 4);
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    // Complete the scan
    updateProgress(100, 'Analyse abgeschlossen!', 4);
    
    // Generate scores
    const scores = {
        performance: getRandomScore(60, 95),
        seo: getRandomScore(65, 90),
        mobile: getRandomScore(55, 95),
        security: getRandomScore(50, 90)
    };
    
    // Wait a moment before showing results
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Show results container
    if (resultsContainer) {
        resultsContainer.style.display = 'block';
        resultsContainer.classList.add('visible');
    }
    
    // Update score displays
    updateScoreDisplay('performanceScore', scores.performance);
    updateScoreDisplay('seoScore', scores.seo);
    updateScoreDisplay('mobileScore', scores.mobile);
    updateScoreDisplay('securityScore', scores.security);
    
    // Display metrics for each category
    displayMetrics('performance', generateMetrics('performance'));
    displayMetrics('seo', generateMetrics('seo'));
    displayMetrics('mobile', generateMetrics('mobile'));
    displayMetrics('security', generateMetrics('security'));
    
    // Generate and display recommendations
    const recommendations = generateRecommendations(scores);
    displayRecommendations(recommendations);
    
    // Hide progress container
    if (progressContainer) {
        progressContainer.style.display = 'none';
    }
    
    // Reinitialize AOS to animate new elements
    if (typeof AOS !== 'undefined') {
        AOS.refresh();
    }
    
    // Scroll to results
    resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Initialize website scanner on page load
document.addEventListener('DOMContentLoaded', function() {
    const analyzeForm = document.getElementById('analyzeForm');
    if (analyzeForm) {
        analyzeForm.addEventListener('submit', scanWebsite);
    }
});
