document.addEventListener("DOMContentLoaded", () => {
  loadFeed();
  loadStudentCount();

  document.getElementById("apply-filters")?.addEventListener("click", e => {
    e.preventDefault();
    loadFeed();
  });

  document.getElementById("reset-filters")?.addEventListener("click", e => {
    e.preventDefault();
    resetFilters();
  });
});

/* ================================
   ATTENDANCE FEED
================================ */
function loadFeed() {
  const container = document.getElementById("attendance-feed-body");
  if (!container) return;

  const from = document.getElementById("filter-from")?.value || "";
  const to = document.getElementById("filter-to")?.value || "";
  const search = document.getElementById("filter-student")?.value.trim() || "";

  const params = new URLSearchParams();
  if (from) params.append("from", from);
  if (to) params.append("to", to);
  if (search) params.append("student", search); // ✅ FIX

  container.innerHTML = `
    <p class="text-sm text-text-secondary text-center">
      Loading attendance...
    </p>
  `;

  fetch(`../../backend/api/attendance_feed_filtered.php?${params.toString()}`)
    .then(res => {
      if (!res.ok) throw new Error("Failed to load feed");
      return res.json();
    })
    .then(rows => {
      container.innerHTML = "";

      // ✅ FIX — API returns ARRAY, not {feeds:[]}
      if (!Array.isArray(rows) || rows.length === 0) {
        container.innerHTML = `
          <p class="text-sm text-text-secondary text-center">
            No attendance records found.
          </p>
        `;
        return;
      }

      rows.forEach(f => {
        let badge = "badge-success";
        let label = "IN";
        let bg = "bg-success-50 border-success-200";

        if (f.scan_type === "exit") {
          badge = "badge-error";
          label = "OUT";
          bg = "bg-primary-50 border-primary-200";
        }

        if (f.scan_type === "entry" && f.status === "late") {
          badge = "badge-warning";
          label = "LATE";
          bg = "bg-warning-50 border-warning-200";
        }

        const time = new Date(f.scan_time).toLocaleTimeString([], {
          hour: "2-digit",
          minute: "2-digit"
        });

        container.insertAdjacentHTML("beforeend", `
          <div class="flex items-start space-x-4 p-4 rounded-xl border ${bg}">
            <div class="flex-1">
              <div class="flex justify-between mb-1">
                <p class="font-semibold">${f.full_name}</p>
                <span class="badge ${badge}">${label}</span>
              </div>
              <p class="text-sm text-text-secondary">
                Student ID: ${f.student_id}
              </p>
              <p class="text-xs text-text-secondary">${time}</p>
            </div>
          </div>
        `);
      });
    })
    .catch(err => {
      console.error("Attendance feed error:", err);
      container.innerHTML = `
        <p class="text-sm text-error text-center">
          Failed to load attendance feed.
        </p>
      `;
    });
}

/* ================================
   RESET FILTERS
================================ */
function resetFilters() {
  document.getElementById("filter-from").value = "";
  document.getElementById("filter-to").value = "";
  document.getElementById("filter-student").value = "";
  loadFeed();
}

/* ================================
   TOTAL STUDENT COUNT
================================ */
function loadStudentCount() {
  const countEl = document.getElementById("total-students-count");
  if (!countEl) return;

  countEl.textContent = "—";

  fetch("../../backend/api/students_count.php")
    .then(res => res.json())
    .then(data => {
      countEl.textContent =
        typeof data.total === "number" ? data.total : "0";
    })
    .catch(() => {
      countEl.textContent = "—";
    });
}
