document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("add-user-modal");
  const openBtn = document.getElementById("add-user-btn");
  const closeBtn = document.getElementById("close-modal-btn");
  const cancelBtn = document.getElementById("cancel-btn");
  const roleSelect = document.getElementById("um_role");
  const form = document.getElementById("add-user-form");

  const roleSection = document.getElementById("role-specific-section");
  const studentSection = document.getElementById("student-section");
  const teacherSection = document.getElementById("teacher-section");
  const msgBox = document.getElementById("um_msg");

  /* ---------------- OPEN / CLOSE MODAL ---------------- */

  function openModal() {
    modal.classList.remove("modal-hidden");
    document.body.style.overflow = "hidden";
    msgBox.textContent = "";
  }

  function closeModal() {
    modal.classList.add("modal-hidden");
    document.body.style.overflow = "";

    stopRFIDAutofill?.(); // stop RFID polling if active

    form.reset();
    delete form.dataset.editId;

    roleSelect.disabled = false;
    document.querySelector("#add-user-modal h3").textContent = "Add New User";
    roleSection.classList.add("hidden");
    studentSection.classList.add("hidden");
    teacherSection.classList.add("hidden");
    msgBox.textContent = "";
  }

  openBtn.addEventListener("click", openModal);
  closeBtn.addEventListener("click", closeModal);
  cancelBtn.addEventListener("click", closeModal);

  modal.addEventListener("click", e => {
    if (e.target === modal) closeModal();
  });

  /* ---------------- ROLE TOGGLE ---------------- */

  roleSelect.addEventListener("change", e => {
    roleSection.classList.remove("hidden");
    studentSection.classList.add("hidden");
    teacherSection.classList.add("hidden");

    stopRFIDAutofill?.(); // always stop first

    if (e.target.value === "student") {
      studentSection.classList.remove("hidden");
      startRFIDAutofill?.(); // start RFID only for students
    }

    if (e.target.value === "teacher") {
      teacherSection.classList.remove("hidden");
    }
  });

  /* ---------------- FORM SUBMIT ---------------- */

  form.addEventListener("submit", e => {
    e.preventDefault();
    msgBox.textContent = "";

    const formData = new FormData(form);
    const role = formData.get("role");

    /* ---- BASIC VALIDATION ---- */
    if (!formData.get("username") || !formData.get("password") || !role) {
      msgBox.textContent = "Please complete account fields.";
      return;
    }

    /* ---- STUDENT VALIDATION ---- */
    if (role === "student") {
      if (
        !formData.get("student_id") ||
        !formData.get("student_full_name") ||
        !formData.get("guardian_full_name") ||
        !formData.get("guardian_email")
      ) {
        msgBox.textContent = "Please complete all student and guardian fields.";
        return;
      }

      if (!formData.get("card_uid")) {
        msgBox.textContent = "Please scan RFID card before saving.";
        return;
      }
    }

    msgBox.textContent = "Saving...";

    fetch("../../backend/api/user_create.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.text())
      .then(msg => {
        if (msg === "success") {
          closeModal();
          loadUsers(); // from user_management.js
        } else {
          msgBox.textContent = msg || "Save failed.";
        }
      })
      .catch(() => {
        msgBox.textContent = "Server error. Please try again.";
      });
  });
});
