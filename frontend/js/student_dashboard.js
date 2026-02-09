document.addEventListener("DOMContentLoaded", () => {
    loadAttendance();
});

let allRecords = [];
let currentPage = 1;
const rowsPerPage = 5;


function loadAttendance() {
    fetch("../../backend/api/attendance_feed_student.php")
        .then(res => res.json())
        .then(data => {
            allRecords = data;
            currentPage = 1;
            renderPage();
            setupPagination();
        })
        .catch(err => console.error(err));
}
function renderPage() {
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = allRecords.slice(start, end);

    renderDesktopTable(pageData);
    renderMobileCards(pageData);
}
function setupPagination() {
    const pageNumbers = document.getElementById("pageNumbers");
    const totalPages = Math.ceil(allRecords.length / rowsPerPage);

    pageNumbers.innerHTML = "";

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement("button");
        btn.textContent = i;
        btn.className = i === currentPage
            ? "btn btn-primary h-10 w-10"
            : "btn-outline h-10 w-10";

        btn.onclick = () => {
            currentPage = i;
            renderPage();
            setupPagination();
        };

        pageNumbers.appendChild(btn);
    }

    document.getElementById("prevPage").onclick = () => {
        if (currentPage > 1) {
            currentPage--;
            renderPage();
            setupPagination();
        }
    };

    document.getElementById("nextPage").onclick = () => {
        if (currentPage < totalPages) {
            currentPage++;
            renderPage();
            setupPagination();
        }
    };
}


/* ================= DESKTOP TABLE ================= */
function renderDesktopTable(records) {
    const tbody = document.getElementById("attendanceTableBody");
    tbody.innerHTML = "";

    if (!records.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-text-secondary">
                    No attendance records found
                </td>
            </tr>
        `;
        return;
    }

    records.forEach(record => {
        const timeIn = record.time_in ? formatTime(record.time_in) : "—";
        const timeOut = record.time_out ? formatTime(record.time_out) : "—";
        const badge = getStatusBadge(record.status);

        tbody.innerHTML += `
            <tr>
                <td class="data-text text-text-primary">${timeIn}</td>
                <td class="data-text text-text-primary">${timeOut}</td>
                <td>${badge}</td>
            </tr>
        `;
    });
}

/* ================= MOBILE CARDS ================= */
function renderMobileCards(records) {
    const container = document.getElementById("attendanceMobileCards");
    container.innerHTML = "";

    records.forEach(record => {
        const timeIn = record.time_in ? formatTime(record.time_in) : "—";
        const timeOut = record.time_out ? formatTime(record.time_out) : "—";

        container.innerHTML += `
            <div class="card p-4 hover:shadow-card-hover">
                <span class="badge ${getBadgeClass(record.status)}">
                    ${capitalize(record.status)}
                </span>

                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <p class="text-text-secondary">Time In</p>
                        <p class="data-text font-semibold">${timeIn}</p>
                    </div>
                    <div>
                        <p class="text-text-secondary">Time Out</p>
                        <p class="data-text font-semibold">${timeOut}</p>
                    </div>
                </div>
            </div>
        `;
    });
}

/* ================= HELPERS ================= */
function formatTime(datetime) {
    return new Date(datetime).toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit"
    });
}

function getStatusBadge(status) {
    if (status === "present") return `<span class="badge badge-success">Present</span>`;
    if (status === "late") return `<span class="badge badge-warning">Late</span>`;
    return `<span class="badge badge-error">Absent</span>`;
}

function getBadgeClass(status) {
    if (status === "present") return "badge-success";
    if (status === "late") return "badge-warning";
    return "badge-error";
}

function capitalize(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
}
