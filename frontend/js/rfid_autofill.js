// safety checks
if (!Array.isArray(records)) {
    console.warn("records is not an array", records);
    return;
}

records.forEach(record => {
    // safe fallbacks
    const status  = record?.status ?? "unknown";
    const timeIn  = record?.time_in ? formatTime(record.time_in) : "—";
    const timeOut = record?.time_out ? formatTime(record.time_out) : "—";

    // role-safe (teacher may not have RFID or same structure)
    const badgeClass =
        typeof getBadgeClass === "function"
            ? getBadgeClass(status)
            : "badge-secondary";

    const displayStatus =
        typeof capitalize === "function"
            ? capitalize(status)
            : status;

    container.innerHTML += `
        <div class="card p-4">
            <span class="badge ${badgeClass}">
                ${displayStatus}
            </span>

            <div class="grid grid-cols-2 gap-3 mt-2">
                <div>
                    <p>Time In</p>
                    <p>${timeIn}</p>
                </div>
                <div>
                    <p>Time Out</p>
                    <p>${timeOut}</p>
                </div>
            </div>
        </div>
    `;
});
