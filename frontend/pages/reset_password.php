<?php
session_start();
if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reset Password</title>
  <link rel="stylesheet" href="../css/main.css">
</head>
<body class="login-bg">
  <div class="min-h-screen flex items-center justify-center p-4">
    <div class="card p-6 w-full max-w-md">
      <h2 class="text-xl font-bold mb-2">Set your new password</h2>
      <p class="text-sm text-text-secondary mb-4">This is required on your first login.</p>

      <form id="resetForm" class="space-y-3">
        <div>
          <label class="label">New Password</label>
          <input id="new_password" name="new_password" type="password" class="input w-full" required minlength="6">
        </div>
        <div>
          <label class="label">Confirm Password</label>
          <input id="confirm_password" type="password" class="input w-full" required minlength="6">
        </div>

        <div id="resetMsg" class="text-sm"></div>

        <button class="btn btn-primary w-full" type="submit">Save Password</button>
      </form>
    </div>
  </div>

  <script>
    document.getElementById("resetForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      const msg = document.getElementById("resetMsg");

      const pw = document.getElementById("new_password").value;
      const cpw = document.getElementById("confirm_password").value;

      if (pw !== cpw) {
        msg.textContent = "Passwords do not match.";
        msg.className = "text-sm text-error";
        return;
      }

      const fd = new FormData();
      fd.append("new_password", pw);

      try {
        const res = await fetch("../../backend/api/reset_password.php", { method: "POST", body: fd });
        const data = await res.json();
        if (!res.ok || data.success === false) throw new Error(data.message || "Failed");

        // redirect based on role
        if (data.role === "admin") location.href = "admin_dashboard.php";
        else if (data.role === "teacher") location.href = "teacher_dashboard.php";
        else location.href = "student_dashboard.php";
      } catch (err) {
        msg.textContent = err.message;
        msg.className = "text-sm text-error";
      }
    });
  </script>
</body>
</html>
