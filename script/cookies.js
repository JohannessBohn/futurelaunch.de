// js/cookie-consent.js

window.addEventListener("load", function () {
    window.cookieconsent.initialise({
        palette: {
            popup: { background: "#1c1c1e" },       // Dunkler Hintergrund
            button: { background: "#4ac1f7" }       // Deine Akzentfarbe (z. B. aus Logo/Farbschema)
        },
        theme: "classic",
        content: {
            message: "Diese Website verwendet Cookies, um Ihnen das beste Erlebnis zu bieten.",
            dismiss: "Verstanden",
            link: "Mehr erfahren",
            href: "/html/datenschutz" // Link zur Datenschutzerklärung
        }
    });
});
