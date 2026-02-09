document.addEventListener("DOMContentLoaded", () => {
    loadCards();

    document.getElementById("card-search")
        .addEventListener("input", loadCards);

    document.getElementById("card-status-filter")
        .addEventListener("change", loadCards);

    document.getElementById("select-all-cards")
        .addEventListener("change", toggleAll);
});

/* =========================
   LOAD RFID CARDS
========================= */
function loadCards() {
    const search = document.getElementById("card-search").value;
    const status = document.getElementById("card-status-filter").value;

    fetch(`../../backend/api/rfid_cards_list.php?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`)
        .then(res => {
            if (!res.ok) throw new Error("Failed to load cards");
            return res.json();
        })
        .then(data => {
            const tbody = document.getElementById("rfid-table-body");
            tbody.innerHTML = "";

            if (!data.cards.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-text-secondary py-6">
                            No cards found
                        </td>
                    </tr>`;
                document.getElementById("card-count").textContent = "Showing 0 cards";
                return;
            }

            data.cards.forEach(c => {
                tbody.innerHTML += `
                <tr class="${c.status !== 'active' ? 'opacity-60' : ''}">
                    <td>
                    </td>

                    <td>
                        <p class="font-semibold text-text-primary">${c.full_name}</p>
                        <p class="text-xs text-text-secondary">${c.student_id}</p>
                    </td>

                    <td class="text-sm font-medium">${c.card_uid}</td>

                    <td>
                        <select
                            class="input text-sm"
                            data-card-id="${c.id}"
                            onchange="updateCardStatus(this)"
                        >
                            <option value="active"   ${c.status === 'active' ? 'selected' : ''}>Active</option>
                            <option value="inactive" ${c.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                            <option value="lost"     ${c.status === 'lost' ? 'selected' : ''}>Lost / Deactivated</option>
                        </select>
                    </td>

                    <td class="text-sm text-text-secondary">
                        ${c.issue_date ?? "—"}
                    </td>

                    <td class="text-sm text-text-secondary">
                        
                    </td>
                </tr>`;
            });

            document.getElementById("card-count").textContent =
                `Showing ${data.count} cards`;
        })
        .catch(err => {
            console.error(err);
            document.getElementById("card-count").textContent =
                "Failed to load cards";
        });
}

/* =========================
   UPDATE CARD STATUS
========================= */
function updateCardStatus(selectEl) {
    const cardId = selectEl.dataset.cardId;
    const newStatus = selectEl.value;

    if (!confirm(`Change card status to "${newStatus}"?`)) {
        loadCards(); // revert UI
        return;
    }

    const formData = new FormData();
    formData.append("id", cardId);
    formData.append("status", newStatus);

    fetch("../../backend/api/rfid_card_update_status.php", {
        method: "POST",
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error("Update failed");
        return res.text();
    })
    .then(() => loadCards())
    .catch(err => {
        alert(err.message);
        loadCards();
    });
}

/* =========================
   BULK DEACTIVATE (LOST)
========================= */
function bulkDeactivate() {
    const ids = [...document.querySelectorAll(".card-check:checked")]
        .map(cb => cb.value);

    if (!ids.length) {
        alert("No cards selected");
        return;
    }

    if (!confirm("Mark selected cards as LOST / DEACTIVATED?")) return;

    const formData = new FormData();
    ids.forEach(id => formData.append("ids[]", id));

    fetch("../../backend/api/rfid_cards_bulk_deactivate.php", {
        method: "POST",
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error("Bulk update failed");
        return res.text();
    })
    .then(() => loadCards())
    .catch(err => alert(err.message));
}

/* =========================
   SELECT ALL CHECKBOX
========================= */
function toggleAll(e) {
    document.querySelectorAll(".card-check")
        .forEach(cb => cb.checked = e.target.checked);
}
