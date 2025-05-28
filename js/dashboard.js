/**
 * FutureLaunch Dashboard JavaScript
 * Client-side dashboard that works without requiring PHP/XAMPP
 */

// Constants
const STORAGE_KEY_AUTH = 'futurelaunch_auth';
const STORAGE_KEY_SUBSCRIBERS = 'futurelaunch_subscribers';
const DEFAULT_CREDENTIALS = {
    username: 'gibmirdeinGeld',
    // This is just a frontend check, not secure - use a backend if true security is needed
    password: '!Dome_Jojo2025' // Using direct password for compatibility with old system
};

// DOM Elements
const loginForm = document.getElementById('login-form');
const loginContainer = document.getElementById('login-container');
const dashboardContainer = document.getElementById('dashboard-container');
const loginAlert = document.getElementById('login-alert');
const logoutBtn = document.getElementById('logout-btn');
const subscribersTable = document.getElementById('subscribers-table');
const totalSubscribersEl = document.getElementById('total-subscribers');
const thisMonthEl = document.getElementById('this-month');
const thisWeekEl = document.getElementById('this-week');
const exportCsvBtn = document.getElementById('export-csv');

// Check Authentication Status on Page Load
document.addEventListener('DOMContentLoaded', () => {
    checkAuthStatus();
    setupEventListeners();
});

// Setup Event Listeners
function setupEventListeners() {
    loginForm.addEventListener('submit', handleLogin);
    logoutBtn.addEventListener('click', handleLogout);
    exportCsvBtn.addEventListener('click', exportSubscribersToCSV);
}

// Authentication Functions
function checkAuthStatus() {
    const authData = localStorage.getItem(STORAGE_KEY_AUTH);
    
    if (authData) {
        try {
            const { isLoggedIn, expiresAt } = JSON.parse(authData);
            
            if (isLoggedIn && expiresAt > Date.now()) {
                showDashboard();
                loadSubscribers();
                return;
            }
        } catch (e) {
            console.error('Error parsing auth data:', e);
        }
    }
    
    // Default to login screen
    showLogin();
}

function handleLogin(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    // Simple client-side authentication
    // In a real app, this would be server-side
    if (validateCredentials(username, password)) {
        // Set authentication in localStorage with expiration (1 hour)
        const authData = {
            isLoggedIn: true,
            expiresAt: Date.now() + (60 * 60 * 1000) // 1 hour in milliseconds
        };
        
        localStorage.setItem(STORAGE_KEY_AUTH, JSON.stringify(authData));
        showDashboard();
        loadSubscribers();
    } else {
        showLoginError('Ungültige Anmeldedaten. Bitte versuchen Sie es erneut.');
    }
}

function validateCredentials(username, password) {
    // Simple direct credential validation
    // In a real app, this would be done server-side with proper security
    return username === DEFAULT_CREDENTIALS.username && 
           password === DEFAULT_CREDENTIALS.password;
}

function handleLogout() {
    localStorage.removeItem(STORAGE_KEY_AUTH);
    showLogin();
}

// UI Helper Functions
function showLogin() {
    loginContainer.style.display = 'block';
    dashboardContainer.style.display = 'none';
}

function showDashboard() {
    loginContainer.style.display = 'none';
    dashboardContainer.style.display = 'block';
}

function showLoginError(message) {
    loginAlert.textContent = message;
    loginAlert.classList.remove('d-none');
    
    // Auto-hide error after 3 seconds
    setTimeout(() => {
        loginAlert.classList.add('d-none');
    }, 3000);
}

// Subscriber Management Functions
function loadSubscribers() {
    let subscribers = getSubscribers();
    
    // Update statistics
    updateDashboardStats(subscribers);
    
    // Clear table
    subscribersTable.innerHTML = '';
    
    // Add subscribers to table
    subscribers.forEach(sub => {
        const row = document.createElement('tr');
        
        const emailCell = document.createElement('td');
        emailCell.textContent = sub.email;
        
        const dateCell = document.createElement('td');
        dateCell.textContent = new Date(sub.date).toLocaleDateString('de-DE');
        
        const actionsCell = document.createElement('td');
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn btn-sm btn-danger';
        deleteBtn.innerHTML = '<i class="bi bi-trash"></i>';
        deleteBtn.onclick = () => deleteSubscriber(sub.email);
        actionsCell.appendChild(deleteBtn);
        
        row.appendChild(emailCell);
        row.appendChild(dateCell);
        row.appendChild(actionsCell);
        
        subscribersTable.appendChild(row);
    });
}

function getSubscribers() {
    // Get subscribers from localStorage or return empty array
    const storedData = localStorage.getItem(STORAGE_KEY_SUBSCRIBERS);
    
    if (storedData) {
        try {
            return JSON.parse(storedData);
        } catch (e) {
            console.error('Error parsing subscribers data:', e);
        }
    }
    
    return getDefaultSubscribers();
}

function getDefaultSubscribers() {
    // Sample data if none exists
    const defaultSubscribers = [
        { email: 'sample@example.com', date: '2025-05-25T12:00:00Z' }
    ];
    
    // Save to localStorage
    localStorage.setItem(STORAGE_KEY_SUBSCRIBERS, JSON.stringify(defaultSubscribers));
    
    return defaultSubscribers;
}

function updateDashboardStats(subscribers) {
    // Total subscribers
    totalSubscribersEl.textContent = subscribers.length;
    
    // Get current date
    const now = new Date();
    const thisMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    
    // Get start of current week (Monday)
    const dayOfWeek = now.getDay() || 7; // Convert Sunday (0) to 7
    const startOfWeek = new Date(now);
    startOfWeek.setDate(now.getDate() - dayOfWeek + 1);
    startOfWeek.setHours(0, 0, 0, 0);
    
    // Count subscribers this month and this week
    const subscribersThisMonth = subscribers.filter(sub => new Date(sub.date) >= thisMonth);
    const subscribersThisWeek = subscribers.filter(sub => new Date(sub.date) >= startOfWeek);
    
    thisMonthEl.textContent = subscribersThisMonth.length;
    thisWeekEl.textContent = subscribersThisWeek.length;
}

function deleteSubscriber(email) {
    if (confirm(`Möchten Sie den Abonnenten ${email} wirklich löschen?`)) {
        let subscribers = getSubscribers();
        subscribers = subscribers.filter(sub => sub.email !== email);
        
        // Save updated list
        localStorage.setItem(STORAGE_KEY_SUBSCRIBERS, JSON.stringify(subscribers));
        
        // Reload subscribers
        loadSubscribers();
    }
}

function exportSubscribersToCSV() {
    const subscribers = getSubscribers();
    
    if (subscribers.length === 0) {
        alert('Keine Abonnenten zum Exportieren verfügbar.');
        return;
    }
    
    // Create CSV content
    let csvContent = 'data:text/csv;charset=utf-8,Email,Datum\n';
    
    subscribers.forEach(sub => {
        const date = new Date(sub.date).toLocaleDateString('de-DE');
        csvContent += `${sub.email},${date}\n`;
    });
    
    // Create download link
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `futurelaunch-subscribers-${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    
    // Trigger download
    link.click();
    document.body.removeChild(link);
}

// SHA-256 Hash Function for password validation
async function sha256(message) {
    // Encode message as UTF-8
    const msgBuffer = new TextEncoder().encode(message);
    
    // Hash the message
    const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
    
    // Convert hash to hex string
    return Array.from(new Uint8Array(hashBuffer))
        .map(b => b.toString(16).padStart(2, '0'))
        .join('');
}

// Polyfill for older browsers that don't support crypto.subtle
if (!window.crypto || !window.crypto.subtle) {
    // Simple non-secure hash function (for demo purposes only)
    // In production, require modern browsers or use a library
    sha256 = function(message) {
        let hash = 0;
        for (let i = 0; i < message.length; i++) {
            const char = message.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return hash.toString(16).padStart(16, '0');
    };
}
