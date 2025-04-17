document.addEventListener("MpCustomerJobAreaReady", async (e) => {
    // Configura MutationObserver
    const observer = new MutationObserver((mutationsList) => {
        for (const mutation of mutationsList) {
            if (mutation.type === "childList") {
                // Reinizializza Tippy.js sui nuovi elementi
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1 && node.classList.contains("mpcustomerjobs")) {
                        tippy(node);
                    }
                });
            }
        }
    });

    const jobAreaButtons = document.querySelectorAll(".mpcustomerjobs");
    jobAreaButtons.forEach((button) => {
        bindButton(button);
    });

    tippy(".eurosolutionId");

    const customerCard = document.querySelector(".card.customer-personal-informations-card");
    const eurosolutionRow = document.getElementById("mpcustomerjobs-personal-info");
    if (customerCard && eurosolutionRow) {
        const templateContent = eurosolutionRow.content;
        const cardBody = customerCard.querySelector(".card-body");
        const childNode = templateContent.cloneNode(true);

        cardBody.appendChild(childNode);
    }
});

document.addEventListener("DOMContentLoaded", async (e) => {
    const selectArea = document.querySelector("select[name='customer[id_customer_job_area]']");
    if (!selectArea) return;

    const selectName = document.querySelector("select[name='customer[id_customer_job_name]']");
    if (!selectName) return;

    selectArea.addEventListener("change", async () => {
        const idArea = selectArea.value;
        if (!idArea) return;

        const response = await fetch(mpCustomerJobAjaxUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: `id_area=${idArea}`
        });
        const data = await response.json();
        selectName.innerHTML = "<option value=''>Seleziona una professione</option>";
        data.forEach((item) => {
            const option = document.createElement("option");
            option.value = item.id;
            option.textContent = item.name;
            selectName.appendChild(option);
        });
    });
});
