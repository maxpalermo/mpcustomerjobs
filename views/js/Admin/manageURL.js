/**
 * Gestisce il cambio di URL aggiungendo la pagina
 */
document.addEventListener("DOMContentLoaded", (e) => {
    // Intercetta tutti i form
    const forms = document.querySelectorAll("form");
    console.log("Trovati " + forms.length + " form.");
    forms.forEach((form) => {
        form.addEventListener("submit", (e) => {
            console.log("Modifico il submit form per ", form.id ?? "");
            e.preventDefault();
            // Ottieni il pagetype dall'URL
            const urlParams = new URLSearchParams(window.location.search);
            const pagetype = urlParams.get("pagetype") || "main";

            // Aggiungi un campo nascosto se non esiste
            if (!form.querySelector('input[name="pagetype"]')) {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "pagetype";
                input.value = pagetype;
                form.appendChild(input);
            }

            // Controlla se è stato premuto il submit che ha per nome che inizia con submitReset
            const activeElement = document.activeElement;
            if (activeElement.name.startsWith("submitReset")) {
                // Aggiungi un campo nascosto per il reset
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = activeElement.name;
                input.value = 1;
                form.appendChild(input);
            }

            form.submit(); // Invia il form
        });
    });
});
