document.addEventListener("DOMContentLoaded", () => {
  loadUsers();

  document.getElementById("search-input")?.addEventListener("input", loadUsers);
  document.getElementById("role-filter")?.addEventListener("change", loadUsers);
  document.getElementById("status-filter")?.addEventListener("change", loadUsers);

  // ✅ Show role sections when choosing role in ADD mode
  document.getElementById("um_role")?.addEventListener("change", (e) => {
    const role = e.target.value;
    showRoleSection(role);

    // ✅ RFID autofill only when student role selected (ADD mode)
    // If role select is disabled, it means EDIT mode; don't autofill.
    if (role === "student" && !qs("um_role").disabled) startRFIDAutofill();
    else stopRFIDAutofill();
  });

  // ✅ Submit handler (Create or Update)
  const form = document.getElementById("add-user-form");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const isEdit = !!form.dataset.editId;
      const role = document.getElementById("um_role").value;

      const payload = collectFormPayload(form, role, isEdit);
      if (isEdit) payload.append("id", form.dataset.editId);

      const url = isEdit
        ? "../../backend/api/user_update.php"
        : "../../backend/api/user_create.php";

      try {
        const res = await fetch(url, { method: "POST", body: payload });

        // ✅ handle BOTH JSON and text responses
        const ct = res.headers.get("content-type") || "";
        let ok = false;
        let message = "";

        if (ct.includes("application/json")) {
          const data = await res.json();
          ok = res.ok && data.success !== false;
          message = data.message || (ok ? "Success" : "Failed");
        } else {
          const text = await res.text();
          ok = res.ok && text.trim().toLowerCase() === "success";
          message = text;
        }

        if (!ok) throw new Error(message || "Request failed");

        document.getElementById("um_msg").textContent = "Saved successfully!";
        loadUsers();
        closeModal();
      } catch (err) {
        console.error("Save failed:", err);
        document.getElementById("um_msg").textContent = err.message || "Failed to save.";
      }
    });
  }

  // ✅ Close buttons if present
  qs("close-modal-btn")?.addEventListener("click", closeModal);
  qs("cancel-btn")?.addEventListener("click", closeModal);
});

/* ===============================
   UTILITIES
================================ */
function badge(role) {
  if (role === "admin") return "bg-primary-100 text-primary-700";
  if (role === "teacher") return "bg-secondary-100 text-secondary-700";
  return "bg-success-100 text-success-700";
}

function qs(id) { return document.getElementById(id); }

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
  fetch(`../../backend/api/user_get.php?id=${encodeURIComponent(userId)}`)
    .then(res => {
      if (!res.ok) throw new Error("Failed to fetch user");
      return res.json();
    })
    .then(user => {
      const modal = qs("add-user-modal");
      const form  = qs("add-user-form");
      if (!modal || !form) return;

      openModal();

      modal.querySelector("h3").textContent = "Edit User";
      form.dataset.editId = user.id;

      // Password optional when editing
      qs("um_password").required = false;
      qs("um_password").value = "";

      // Role cannot be changed
      qs("um_role").disabled = true;

      // Fill account fields
      qs("um_username").value = user.username ?? "";
      qs("um_role").value     = user.role ?? "";
      qs("um_status").value   = user.status ?? "active";

      // Show correct section
      showRoleSection(user.role);

      // Stop RFID autofill in edit mode
      stopRFIDAutofill();

      if (user.role === "student" && user.student) {
        const s = user.student;

        qs("um_student_id").value        = s.student_id ?? "";
        qs("um_student_fullname").value  = s.full_name ?? "";
        qs("um_student_email").value     = s.email ?? "";

        qs("um_guardian_fullname").value = s.guardian_name ?? "";
        qs("um_guardian_email").value    = s.guardian_email ?? "";
        qs("um_guardian_contact").value  = s.contact_no ?? "";

        qs("um_card_uid").value          = s.card_uid ?? "";
        qs("um_card_status").value       = s.card_status ?? "active";
      }

      if (user.role === "teacher" && user.teacher) {
        const t = user.teacher;

        qs("um_teacher_id").value        = t.teacher_id ?? "";
        qs("um_teacher_fullname").value  = t.full_name ?? "";
        qs("um_teacher_email").value     = t.email ?? "";
      }
    })
    .catch(err => {
      console.error("Edit modal error:", err);
      alert("Failed to load user details.");
    });
}

/* ===============================
   MODAL OPEN/CLOSE
================================ */
function openModal() {
  const modal = qs("add-user-modal");
  if (!modal) return;

  modal.classList.remove("modal-hidden");
  document.body.style.overflow = "hidden";

  // If opening for ADD mode, ensure defaults are correct
  const form = qs("add-user-form");
  if (form && !form.dataset.editId) {
    qs("um_role").disabled = false;
    qs("um_password").required = true;
    qs("um_msg").textContent = "";
  }
}

function closeModal() {
  const modal = qs("add-user-modal");
  const form  = qs("add-user-form");
  if (!modal || !form) return;

  modal.classList.add("modal-hidden");
  document.body.style.overflow = "";

  form.reset();
  delete form.dataset.editId;

  qs("um_role").disabled = false;
  qs("um_password").required = true;

  hideRoleSections();
  stopRFIDAutofill();

  modal.querySelector("h3").textContent = "Add New User";
  qs("um_msg").textContent = "";
}

/* ===============================
   ROLE SECTION TOGGLE
================================ */
function hideRoleSections() {
  qs("role-specific-section")?.classList.add("hidden");
  qs("student-section")?.classList.add("hidden");
  qs("teacher-section")?.classList.add("hidden");
}

function showRoleSection(role) {
  hideRoleSections();

  if (role === "student") {
    qs("role-specific-section")?.classList.remove("hidden");
    qs("student-section")?.classList.remove("hidden");
  } else if (role === "teacher") {
    qs("role-specific-section")?.classList.remove("hidden");
    qs("teacher-section")?.classList.remove("hidden");
  }
}

/* ===============================
   RFID AUTO-FILL
================================ */
let rfidAutofillInterval = null;

function startRFIDAutofill() {
  if (rfidAutofillInterval) return;

  const rfidInput = qs("um_card_uid");
  if (!rfidInput) return;

  rfidAutofillInterval = setInterval(() => {
    if (rfidInput.value) return;

    fetch("../../backend/api/rfid_latest.php")
      .then(res => res.json())
      .then(data => {
        if (data?.uid) rfidInput.value = data.uid;
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

/* ===============================
   FORM PAYLOAD BUILDER
================================ */
function collectFormPayload(form, role, isEdit = false) {
  const fd = new FormData(form);
  const payload = new FormData();

  payload.append("username", fd.get("username") || "");

  // ✅ IMPORTANT: role select is disabled during edit, so fd.get("role") becomes null
  payload.append("role", document.getElementById("um_role")?.value || "");

  payload.append("status", fd.get("status") || "active");

  // password: only send if create OR user typed something
  const pw = (fd.get("password") || "").toString();
  if (!isEdit || pw.length > 0) payload.append("password", pw);

  // profile photo (optional)
  const photo = fd.get("profile_photo");
  if (photo && photo instanceof File && photo.size > 0) {
    payload.append("profile_photo", photo);
  }

  // Role-specific
  if (role === "student") {
    payload.append("student_id", fd.get("student_id") || "");
    payload.append("student_full_name", fd.get("student_full_name") || "");
    payload.append("student_email", fd.get("student_email") || "");

    payload.append("guardian_full_name", fd.get("guardian_full_name") || "");
    payload.append("guardian_email", fd.get("guardian_email") || "");
    payload.append("guardian_contact_no", fd.get("guardian_contact_no") || "");

    payload.append("card_uid", fd.get("card_uid") || "");
    payload.append("card_status", fd.get("card_status") || "active");
  }

  if (role === "teacher") {
    payload.append("teacher_id", fd.get("teacher_id") || "");
    payload.append("teacher_full_name", fd.get("teacher_full_name") || "");
    payload.append("teacher_email", fd.get("teacher_email") || "");
  }

  return payload;
}

