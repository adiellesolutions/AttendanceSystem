(() => {
    const API_BASE = "../../backend/api";
  
    const modal = document.getElementById("add-user-modal");
    const openBtn = document.getElementById("add-user-btn");
    const closeBtn = document.getElementById("close-modal-btn");
    const cancelBtn = document.getElementById("cancel-btn");
    const form = document.getElementById("add-user-form");
    const msg = document.getElementById("um_msg");
  
    const roleSel = document.getElementById("um_role");
    const roleSection = document.getElementById("role-specific-section");
  
    const studentSection = document.getElementById("student-section");
    const teacherSection = document.getElementById("teacher-section");
  
    function setMsg(text, isError = false) {
      if (!msg) return;
      msg.textContent = text || "";
      msg.className = "text-sm " + (isError ? "text-red-600" : "text-green-600");
    }
  
    function openModal() {
      modal.classList.remove("hidden");
      document.body.style.overflow = "hidden";
      setMsg("");
    }
  
    function closeModal() {
      modal.classList.add("hidden");
      document.body.style.overflow = "auto";
      form.reset();
  
      roleSection.classList.add("hidden");
      studentSection.classList.add("hidden");
      teacherSection.classList.add("hidden");
      setMsg("");
    }
  
    openBtn?.addEventListener("click", openModal);
    closeBtn?.addEventListener("click", closeModal);
    cancelBtn?.addEventListener("click", closeModal);
  
    modal?.addEventListener("click", (e) => {
      if (e.target === modal) closeModal();
    });
  
    roleSel?.addEventListener("change", function () {
      const r = this.value;
  
      if (r === "student" || r === "teacher") {
        roleSection.classList.remove("hidden");
        studentSection.classList.toggle("hidden", r !== "student");
        teacherSection.classList.toggle("hidden", r !== "teacher");
      } else {
        roleSection.classList.add("hidden");
        studentSection.classList.add("hidden");
        teacherSection.classList.add("hidden");
      }
    });
  
    form?.addEventListener("submit", async (e) => {
      e.preventDefault();
      setMsg("");
  
      const username = document.getElementById("um_username")?.value.trim() || "";
      const password = document.getElementById("um_password")?.value || "";
      const role = roleSel?.value || "";
      const status = document.getElementById("um_status")?.value || "active";
  
      if (!username || !password || !role) {
        setMsg("Please complete all required account fields.", true);
        return;
      }
  
      // ✅ FormData for file upload
      const fd = new FormData();
      fd.append("username", username);
      fd.append("password", password);
      fd.append("role", role);
      fd.append("status", status);
  
      // ✅ Profile photo (optional)
      const photo = document.getElementById("um_profile_photo")?.files?.[0];
      if (photo) {
        fd.append("profile_photo", photo);
      }
  
      // Teacher fields (flat keys)
      if (role === "teacher") {
        const teacher_id = document.getElementById("um_teacher_id")?.value.trim() || "";
        const full_name = document.getElementById("um_teacher_fullname")?.value.trim() || "";
        const email = document.getElementById("um_teacher_email")?.value.trim() || "";
  
        if (!teacher_id || !full_name) {
          setMsg("Teacher role requires Teacher ID and Full Name.", true);
          return;
        }
  
        fd.append("teacher_id", teacher_id);
        fd.append("teacher_full_name", full_name);
        fd.append("teacher_email", email);
      }
  
      // Student fields (flat keys)
      if (role === "student") {
        const student_id = document.getElementById("um_student_id")?.value.trim() || "";
        const full_name = document.getElementById("um_student_fullname")?.value.trim() || "";
        const email = document.getElementById("um_student_email")?.value.trim() || "";
  
        const g_full_name = document.getElementById("um_guardian_fullname")?.value.trim() || "";
        const g_email = document.getElementById("um_guardian_email")?.value.trim() || "";
        const g_contact_no = document.getElementById("um_guardian_contact")?.value.trim() || "";
  
        const card_uid = document.getElementById("um_card_uid")?.value.trim() || "";
        const card_status = document.getElementById("um_card_status")?.value || "active";
  
        if (!student_id || !full_name || !g_full_name || !g_email || !card_uid) {
          setMsg("Student role requires Student info + Guardian info + RFID UID.", true);
          return;
        }
  
        fd.append("student_id", student_id);
        fd.append("student_full_name", full_name);
        fd.append("student_email", email);
  
        fd.append("guardian_full_name", g_full_name);
        fd.append("guardian_email", g_email);
        fd.append("guardian_contact_no", g_contact_no);
  
        fd.append("card_uid", card_uid);
        fd.append("card_status", card_status);
      }
  
      try {
        const res = await fetch(`${API_BASE}/users_create.php`, {
          method: "POST",
          credentials: "include",
          body: fd, // ✅ no JSON, no headers
        });
  
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data?.message || "Request failed");
  
        setMsg("User created successfully!");
        if (window.reloadUsersTable) await window.reloadUsersTable();
        setTimeout(closeModal, 600);
      } catch (err) {
        setMsg(err.message, true);
      }
    });
  })();
  