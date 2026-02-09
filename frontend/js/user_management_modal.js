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

  if (!modal || !openBtn || !closeBtn || !cancelBtn || !roleSelect || !form) {
    console.error("Modal elements not found");
    return;
  }

  /* OPEN / CLOSE MODAL */
  function openModal() {
    modal.classList.remove("modal-hidden");
    document.body.style.overflow = "hidden";
  }

    function closeModal() {
    const modal = document.getElementById("add-user-modal");
    modal.classList.add("modal-hidden");
    document.body.style.overflow = "";

    const form = document.getElementById("add-user-form");
    form.reset();
    delete form.dataset.editId;

    document.getElementById("um_role").disabled = false;
    document.querySelector("#add-user-modal h3").textContent = "Add New User";
    }


  openBtn.addEventListener("click", openModal);
  closeBtn.addEventListener("click", closeModal);
  cancelBtn.addEventListener("click", closeModal);

  modal.addEventListener("click", e => {
    if (e.target === modal) closeModal();
  });

  /* ROLE TOGGLE */
  roleSelect.addEventListener("change", e => {
    roleSection.classList.remove("hidden");
    studentSection.classList.add("hidden");
    teacherSection.classList.add("hidden");

    if (e.target.value === "student") {
      studentSection.classList.remove("hidden");
    }

    if (e.target.value === "teacher") {
      teacherSection.classList.remove("hidden");
    }
  });

  /* FORM SUBMIT */
  form.addEventListener("submit", e => {
    e.preventDefault();
    msgBox.textContent = "Saving...";

    const formData = new FormData(form);

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
          msgBox.textContent = msg;
        }
      })
      .catch(() => {
        msgBox.textContent = "Server error. Try again.";
      });
  });
});
