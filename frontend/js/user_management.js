document.addEventListener("DOMContentLoaded", () => {
  loadUsers();

  document.getElementById("search-input")?.addEventListener("input", loadUsers);
  document.getElementById("role-filter")?.addEventListener("change", loadUsers);
  document.getElementById("status-filter")?.addEventListener("change", loadUsers);
});

/* ===============================
   UTILITIES
================================ */
function badge(role) {
  if (role === "admin") return "bg-primary-100 text-primary-700";
  if (role === "teacher") return "bg-secondary-100 text-secondary-700";
  return "bg-success-100 text-success-700";
}

/* ===============================
   LOAD USERS
================================ */
function loadUsers() {
  const search = document.getElementById("search-input")?.value || "";
  const role   = document.getElementById("role-filter")?.value || "";
  const status = document.getElementById("status-filter")?.value || "";

  fetch(`../../backend/api/user_list.php?search=${encodeURIComponent(search)}&role=${encodeURIComponent(role)}&status=${encodeURIComponent(status)}`)
    .then(res => {
      if (!res.ok) throw new Error("API error");
      return res.json();
    })
    .then(data => {
      // 🔧 FIX: support both array and object responses
      const users = Array.isArray(data) ? data : data.users || [];

      const tbody = document.getElementById("user-table-body");
      const cards = document.getElementById("user-cards-section");

      if (!tbody || !cards) return;

      tbody.innerHTML = "";
      cards.innerHTML = "";

      if (!users.length) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" class="text-center text-text-secondary">
              No users found
            </td>
          </tr>
        `;
        return;
      }

      users.forEach(u => {
        /* DESKTOP TABLE */
        tbody.insertAdjacentHTML("beforeend", `
          <tr>
            <td>${u.full_name ?? "—"}</td>
            <td>${u.email ?? "—"}</td>
            <td>${u.role}</td>
            <td>${u.status}</td>
            <td>${u.last_login ?? "—"}</td>
            <td>${u.assoc ?? "—"}</td>
            <td class="text-right">
              <button 
                class="btn-outline text-sm edit-user-btn"
                data-user-id="${u.id}">
                Edit
              </button>
            </td>
          </tr>
        `);

        /* MOBILE CARD */
        cards.insertAdjacentHTML("beforeend", `
          <div class="card">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="font-semibold text-text-primary">${u.full_name ?? "—"}</p>
                <p class="text-sm text-text-secondary">${u.assoc ?? ""}</p>
              </div>
              <span class="badge ${badge(u.role)}">${u.role}</span>
            </div>

            <div class="space-y-2 text-sm text-text-secondary">
              <p>${u.email ?? "—"}</p>
              <p>Last login: ${u.last_login ?? "—"}</p>
              <span class="badge badge-success">${u.status}</span>
            </div>
          </div>
        `);
      });

      document.getElementById("user-count") &&
        (document.getElementById("user-count").textContent = `Showing ${users.length} users`);

      document.getElementById("pagination-info") &&
        (document.getElementById("pagination-info").textContent = `Showing ${users.length} users`);
    })
    .catch(err => {
      console.error("Load users error:", err);
      alert("Users failed to load. Check console.");
    });
}

/* ===============================
   EDIT BUTTON HANDLER
================================ */
document.addEventListener("click", e => {
  const btn = e.target.closest(".edit-user-btn");
  if (!btn) return;

  openEditModal(btn.dataset.userId);
});

/* ===============================
   OPEN EDIT MODAL
================================ */
function openEditModal(userId) {
  fetch(`../../backend/api/user_get.php?id=${userId}`)
    .then(res => {
      if (!res.ok) throw new Error("Failed to fetch user");
      return res.json();
    })
    .then(user => {
      const modal = document.getElementById("add-user-modal");
      const form  = document.getElementById("add-user-form");

      if (!modal || !form) return;

      modal.classList.remove("modal-hidden");
      document.body.style.overflow = "hidden";

      modal.querySelector("h3").textContent = "Edit User";
      form.dataset.editId = user.id;

      document.getElementById("um_username").value = user.username;
      document.getElementById("um_role").value = user.role;
      toggleRoleSections(user.role);

      document.getElementById("um_status").value = user.status;
      document.getElementById("um_role").disabled = true;

      document.getElementById("role-specific-section").classList.remove("hidden");
      document.getElementById("student-section").classList.add("hidden");
      document.getElementById("teacher-section").classList.add("hidden");

      stopRFIDAutofill();

      if (user.role === "student" && user.student) {
        const s = user.student;
        document.getElementById("student-section").classList.remove("hidden");

        document.getElementById("um_student_id").value = s.student_id;
        document.getElementById("um_student_fullname").value = s.full_name;
        document.getElementById("um_student_email").value = s.email ?? "";

        document.getElementById("um_guardian_fullname").value = s.guardian_name ?? "";
        document.getElementById("um_guardian_email").value = s.guardian_email ?? "";
        document.getElementById("um_guardian_contact").value = s.contact_no ?? "";

        document.getElementById("um_card_uid").value = s.card_uid ?? "";
        document.getElementById("um_card_status").value = s.card_status ?? "active";

        startRFIDAutofill();
      }

      if (user.role === "teacher" && user.teacher) {
        const t = user.teacher;
        document.getElementById("teacher-section").classList.remove("hidden");

        document.getElementById("um_teacher_id").value = t.teacher_id;
        document.getElementById("um_teacher_fullname").value = t.full_name;
        document.getElementById("um_teacher_email").value = t.email ?? "";
      }
    })
    .catch(err => {
      console.error("Edit modal error:", err);
      alert("Failed to load user details.");
    });
}

/* ===============================
   RFID AUTO-FILL
================================ */
let rfidAutofillInterval = null;

function startRFIDAutofill() {
  if (rfidAutofillInterval) return;

  const rfidInput = document.getElementById("um_card_uid");
  if (!rfidInput) return;

  rfidAutofillInterval = setInterval(() => {
    if (rfidInput.value) return;

    fetch("../../backend/api/rfid_latest.php")
      .then(res => res.json())
      .then(data => {
        if (data?.uid) {
          rfidInput.value = data.uid;
        }
      })
      .catch(() => {});
  }, 1000);
}

function stopRFIDAutofill() {
  if (rfidAutofillInterval) {
    clearInterval(rfidAutofillInterval);
    rfidAutofillInterval = null;
  }
}
