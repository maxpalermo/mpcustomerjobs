async function getFlag() {
    const response = await fetch(getFlagAction);
    const data = await response.json();

    if (data.status == true && data.record) {
        const flagValue = data.record.id_order_flag;
        const select = document.getElementById("order-flag-combo");
        select.value = flagValue;
        const select2 = $(select).data("select2");
        select2.val(flagValue).trigger("change");
    }
}

function updateSelectFlag() {
    const select = document.getElementById("order-flag-combo");
    const select2 = $(select).data("select2");
    select2.val(currentFlag).trigger("change");
}

async function updateFlag() {
    const value = document.getElementById("order-flag-combo").value;
    const response = await fetch(updateFlagAction, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({ flag: value })
    });
    const data = await response.json();
    if (data.status == true) {
        swalSuccess(data.message);
    } else {
        swalError(data.message);
    }
}

document.addEventListener("updateOrderFlag", async (e) => {
    const orderActions = document.querySelector(".order-actions");

    // Recupera i dati dal tag <script>
    const options = orderFlagOptions;
    const selectStatus = document.createElement("div");
    const selectFlag = document.createElement("select");

    selectFlag.setAttribute("id", "order-flag-combo");
    selectFlag.setAttribute("name", "order-flag-combo");

    selectStatus.classList.add("select-status");
    selectStatus.setAttribute("id", "select-status-flag");
    selectStatus.style.backgroundColor = "#70b580";
    selectStatus.style.color = "#fcfcfc";
    selectStatus.style.display = "flex";
    selectStatus.style.alignItems = "center";
    selectStatus.style.justifyContent = "center";

    selectFlag.innerHTML = "";

    // Aggiungi le opzioni
    options.forEach((option) => {
        selectFlag.innerHTML += `
            <option value="${option.id}" data-color="${option.color}" data-icon="${option.icon}">${option.name}</option>
        `;
    });

    // Crea il div container
    selectStatus.appendChild(selectFlag);

    // Sostituisci o inserisci la select nel DOM (es. in un div con id="container")
    orderActions.insertAdjacentElement("afterbegin", selectStatus);

    // Inizializza Select2
    $(selectFlag).select2({
        templateResult: formatOption,
        templateSelection: formatOption,
        escapeMarkup: (m) => m // Disabilita escape per Material Icons
    });

    // Imposta il background del dropdown select2 a grigio chiaro
    $(selectFlag).on("select2:open", function () {
        $(".select2-results__options").css("background-color", "#FCFCFC").css("color", "#303030");
    });

    // Cambia dinamicamente il colore di background del container quando cambia opzione
    $(selectFlag).on("select2:select", async (e) => {
        const color = $(e.params.data.element).data("color");
        $("#select-status-flag").css("background-color", color || "#70b580");
        await updateFlag();
    });

    function formatOption(option) {
        if (!option.id) return option.text;
        const icon = $(option.element).data("icon");
        const $wrapper = $("<div>");

        if (icon) {
            $wrapper.append($("<i>").addClass("material-icons").text(icon).css({ "margin-right": "8px", "vertical-align": "middle" }));
        }

        $wrapper.append(option.text);
        // Sfondo trasparente, nessun bordo o colore testo
        return $wrapper;
    }

    updateSelectFlag();
});
