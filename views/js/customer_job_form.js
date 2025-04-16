// Vanilla JS for dynamic job name select

document.addEventListener("DOMContentLoaded", function () {
    const areaSelect = document.querySelector("select[name='id_customer_job_area']");
    const nameSelect = document.querySelector("select[name='id_customer_job_name']");
    if (!areaSelect || !nameSelect) return;

    areaSelect.addEventListener("change", function () {
        const idArea = this.value;
        // Remove all options except the first (empty)
        nameSelect.innerHTML = '<option value=""></option>';
        if (!idArea) return;

        fetch(mpCustomerJobAjaxUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: `id_area=${idArea}`
        })
            .then((response) => response.json())
            .then((data) => {
                nameSelect.innerHTML = '<option value="">Seleziona una professione</option>';
                data.forEach(function (item) {
                    const opt = document.createElement("option");
                    opt.value = item.id;
                    opt.textContent = item.name;
                    nameSelect.appendChild(opt);
                });
            });
    });
});
