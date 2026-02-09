document.addEventListener("DOMContentLoaded", () => {
  loadUsers();

  document.getElementById("search-input").addEventListener("input", loadUsers);
  document.getElementById("role-filter").addEventListener("change", loadUsers);
  document.getElementById("status-filter").addEventListener("change", loadUsers);
});

function badge(role) {
  if (role === "admin") return "bg-primary-100 text-primary-700";
  if (role === "teacher") return "bg-secondary-100 text-secondary-700";
  return "bg-success-100 text-success-700";
}

function loadUsers() {
  const search = document.getElementById("search-input").value;
  const role   = document.getElementById("role-filter").value;
  const status = document.getElementById("status-filter").value;

  fetch(
    `../../backend/api/user_list.php?search=${encodeURIComponent(search)}&role=${encodeURIComponent(role)}&status=${encodeURIComponent(status)}`
  )
    .then(res => {
      if (!res.ok) throw new Error("API error");
      return res.json();
    })
    .then(data => {
      const tbody = document.getElementById("user-table-body");
      const cards = document.getElementById("user-cards-section");

      tbody.innerHTML = "";
      cards.innerHTML = "";

      data.users.forEach(u => {
        /* TABLE */
        tbody.innerHTML += `
          <tr>
            <td>${u.full_name}</td>
            <td>${u.email}</td>
            <td>${u.role}</td>
            <td>${u.status}</td>
            <td>${u.last_login ?? "—"}</td>
            <td>${u.assoc}</td>
            <td class="text-right">
                <button 
                class="btn-outline text-sm edit-user-btn"
                data-user-id="${u.id}"
                data-role="${u.role}">
                Edit
                </button>
            </td>
          </tr>
        `;

        /* MOBILE CARD */
        cards.innerHTML += `
          <div class="card">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="font-semibold text-text-primary">${u.full_name}</p>
                <p class="text-sm text-text-secondary">${u.assoc}</p>
              </div>
              <span class="badge ${badge(u.role)}">${u.role}</span>
            </div>

            <div class="space-y-2 text-sm text-text-secondary">
              <p>${u.email}</p>
              <p>Last login: ${u.last_login ?? "—"}</p>
              <span class="badge badge-success">${u.status}</span>
            </div>
          </div>
        `;
      });

      document.getElementById("user-count").textContent =
        `Showing ${data.count} users`;

      document.getElementById("pagination-info").textContent =
        `Showing ${data.count} of ${data.count} users`;
    })
    .catch(err => {
      console.error(err);
      alert("Users failed to load. Check console.");
    });
}

document.addEventListener("click", e => {
  const btn = e.target.closest(".edit-user-btn");
  if (!btn) return;

  const userId = btn.dataset.userId;
  openEditModal(userId);
});

function openEditModal(userId) {
  fetch(`../../backend/api/user_get.php?id=${userId}`)
    .then(res => res.json())
    .then(user => {
      // Open modal
      const modal = document.getElementById("add-user-modal");
      modal.classList.remove("hidden");

      // Change title
      document.querySelector("#add-user-modal h3").textContent = "Edit User";

      // Store editing ID
      document.getElementById("add-user-form").dataset.editId = user.id;

      // Base fields
      document.getElementById("um_username").value = user.username;
      document.getElementById("um_role").value = user.role;
      document.getElementById("um_status").value = user.status;

      // Disable role change
      document.getElementById("um_role").disabled = true;

      // Reset sections
      document.getElementById("role-specific-section").classList.remove("hidden");
      document.getElementById("student-section").classList.add("hidden");
      document.getElementById("teacher-section").classList.add("hidden");

      if (user.role === "student") {
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
      }

      if (user.role === "teacher") {
        const t = user.teacher;
        document.getElementById("teacher-section").classList.remove("hidden");

        document.getElementById("um_teacher_id").value = t.teacher_id;
        document.getElementById("um_teacher_fullname").value = t.full_name;
        document.getElementById("um_teacher_email").value = t.email ?? "";
      }
    });
}

document.getElementById("add-user-form").addEventListener("submit", e => {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  const isEdit = form.dataset.editId;
  let url = "../../backend/api/user_create.php";

  if (isEdit) {
    formData.append("user_id", isEdit);
    url = "../../backend/api/user_update.php";
  }

  fetch(url, {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(msg => {
    if (msg === "success") {
      form.reset();
      delete form.dataset.editId;
      document.getElementById("add-user-modal").classList.add("hidden");
      loadUsers();
    } else {
      document.getElementById("um_msg").textContent = msg;
    }
  });
});

function openEditModal(userId) {
  fetch(`../../backend/api/user_get.php?id=${userId}`)
    .then(res => res.json())
    .then(user => {
      const modal = document.getElementById("add-user-modal");

      // ✅ SHOW MODAL (FIX)
      modal.classList.remove("modal-hidden");
      document.body.style.overflow = "hidden";

      // Title
      document.querySelector("#add-user-modal h3").textContent = "Edit User";

      // Store edit ID
      const form = document.getElementById("add-user-form");
      form.dataset.editId = user.id;

      // Base fields
      document.getElementById("um_username").value = user.username;
      document.getElementById("um_role").value = user.role;
      document.getElementById("um_status").value = user.status;

      document.getElementById("um_role").disabled = true;

      // Reset sections
      document.getElementById("role-specific-section").classList.remove("hidden");
      document.getElementById("student-section").classList.add("hidden");
      document.getElementById("teacher-section").classList.add("hidden");

      if (user.role === "student") {
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
      }

      if (user.role === "teacher") {
        const t = user.teacher;
        document.getElementById("teacher-section").classList.remove("hidden");

        document.getElementById("um_teacher_id").value = t.teacher_id;
        document.getElementById("um_teacher_fullname").value = t.full_name;
        document.getElementById("um_teacher_email").value = t.email ?? "";
      }
    });
}
