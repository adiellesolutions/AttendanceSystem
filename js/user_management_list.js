(() => {
    const API_BASE = "/AttendanceSystem/backend/api";
    const PAGE_SIZE = 5;
  
    const tbody = document.getElementById("user-table-body");
    const userCount = document.getElementById("user-count");
  
    const searchInput = document.getElementById("search-input");
    const roleFilter = document.getElementById("role-filter");
    const statusFilter = document.getElementById("status-filter");
  
    const paginationInfo = document.getElementById("pagination-info");
    const paginationControls = document.getElementById("pagination-controls");
  
    const DEFAULT_AVATAR =
      "https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop";
  
    let currentPage = 1;
    let lastTotal = 0;
  
    function esc(s) {
      return String(s ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }
  
    function badgeRole(role) {
      if (role === "admin") return "badge bg-primary-100 text-primary-700";
      if (role === "teacher") return "badge bg-secondary-100 text-secondary-700";
      return "badge bg-success-100 text-success-700";
    }
  
    function badgeStatus(status) {
      if (status === "active") return "badge badge-success";
      if (status === "inactive") return "badge badge-warning";
      return "badge";
    }
  
    function formatLogin(dt) {
      if (!dt) return "—";
      const d = new Date(String(dt).replace(" ", "T"));
      return isNaN(d) ? String(dt) : d.toLocaleString();
    }
  
    function photoUrl(path) {
      if (!path) return DEFAULT_AVATAR;
      return `/AttendanceSystem/${String(path).replace(/^\/+/, "")}`;
    }
  
    function renderUsers(users) {
      if (!tbody) return;
  
      tbody.innerHTML = (users || [])
        .map((u) => {
          const name = esc(u.full_name);
          const email = esc(u.username);
          const role = esc(u.role);
          const status = esc(u.status);
          const lastLogin = esc(formatLogin(u.last_login_at));
          const assoc = esc(u.associated_label || "—");
          const idLabel = esc(u.display_id || "");
  
          return `
            <tr class="hover:bg-background transition-smooth">
              <td>
                <input type="checkbox"
                  class="user-checkbox w-5 h-5 rounded border-border text-primary focus:ring-accent cursor-pointer"
                  data-user-id="${esc(u.id)}">
              </td>
              <td>
                <div class="flex items-center space-x-3">
                  <img
                    src="${photoUrl(u.profile_photo_path)}"
                    alt="Profile photo of ${name}"
                    class="w-10 h-10 rounded-full object-cover"
                    onerror="this.src='${DEFAULT_AVATAR}'; this.onerror=null;">
                  <div>
                    <p class="font-semibold text-text-primary">${name}</p>
                    <p class="text-sm text-text-secondary">ID: ${idLabel}</p>
                  </div>
                </div>
              </td>
              <td class="text-text-secondary">${email}</td>
              <td><span class="${badgeRole(role)}">${role.charAt(0).toUpperCase() + role.slice(1)}</span></td>
              <td><span class="${badgeStatus(status)}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
              <td class="text-text-secondary text-sm">${lastLogin}</td>
              <td class="text-text-secondary text-sm">${assoc}</td>
              <td>
                <div class="flex items-center justify-end space-x-2">
                  <button class="p-2 rounded-lg hover:bg-primary-50 transition-smooth touch-target"
                    data-action="edit" data-user-id="${esc(u.id)}" title="Edit">✎</button>
                  <button class="p-2 rounded-lg hover:bg-secondary-50 transition-smooth touch-target"
                    data-action="logs" data-user-id="${esc(u.id)}" title="Activity Logs">🕘</button>
                  <button class="p-2 rounded-lg hover:bg-warning-50 transition-smooth touch-target"
                    data-action="reset" data-user-id="${esc(u.id)}" title="Reset Password">↻</button>
                </div>
              </td>
            </tr>
          `;
        })
        .join("");
  
      if (userCount) userCount.textContent = `Showing ${(users || []).length} users`;
    }
  
    function renderPagination(page, total, limit) {
      if (!paginationInfo || !paginationControls) return;
  
      const totalPages = Math.max(1, Math.ceil(total / limit));
      const start = total === 0 ? 0 : (page - 1) * limit + 1;
      const end = Math.min(page * limit, total);
  
      paginationInfo.textContent = `Showing ${start}-${end} of ${total} users`;
  
      // buttons
      const prevDisabled = page <= 1;
      const nextDisabled = page >= totalPages;
  
      // show up to 5 page buttons around current
      const windowSize = 5;
      let startPage = Math.max(1, page - Math.floor(windowSize / 2));
      let endPage = Math.min(totalPages, startPage + windowSize - 1);
      startPage = Math.max(1, endPage - windowSize + 1);
  
      let html = `
        <button class="btn-outline h-10 px-4 text-sm" ${prevDisabled ? "disabled" : ""} data-page="${page - 1}" aria-label="Previous page">
          ‹
        </button>
      `;
  
      for (let p = startPage; p <= endPage; p++) {
        const active = p === page;
        html += active
          ? `<button class="h-10 px-4 rounded-xl bg-primary text-white text-sm font-medium" disabled>${p}</button>`
          : `<button class="btn-outline h-10 px-4 text-sm" data-page="${p}">${p}</button>`;
      }
  
      html += `
        <button class="btn-outline h-10 px-4 text-sm" ${nextDisabled ? "disabled" : ""} data-page="${page + 1}" aria-label="Next page">
          ›
        </button>
      `;
  
      paginationControls.innerHTML = html;
  
      // click handler
      paginationControls.querySelectorAll("button[data-page]").forEach((btn) => {
        btn.addEventListener("click", () => {
          const p = parseInt(btn.getAttribute("data-page"), 10);
          if (!Number.isFinite(p)) return;
          currentPage = p;
          loadUsers().catch(console.error);
        });
      });
    }
  
    async function loadUsers() {
      const q = searchInput?.value.trim() || "";
      const role = roleFilter?.value || "";
      const status = statusFilter?.value || "";
  
      const params = new URLSearchParams();
      if (q) params.set("q", q);
      if (role) params.set("role", role);
      if (status) params.set("status", status);
  
      params.set("page", String(currentPage));
      params.set("limit", String(PAGE_SIZE));
  
      const res = await fetch(`${API_BASE}/users_list.php?${params.toString()}`, {
        credentials: "include",
      });
  
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.message || "Failed to load users");
  
      const users = data.users || [];
      lastTotal = Number(data.total || 0);
  
      // if filters reduced total and page is now out of range, clamp
      const totalPages = Math.max(1, Math.ceil(lastTotal / PAGE_SIZE));
      if (currentPage > totalPages) {
        currentPage = totalPages;
        return loadUsers();
      }
  
      renderUsers(users);
      renderPagination(currentPage, lastTotal, PAGE_SIZE);
    }
  
    function resetToPage1AndLoad() {
      currentPage = 1;
      loadUsers().catch(console.error);
    }
  
    // expose for create-user to refresh list (keeps current filters/page)
    window.reloadUsersTable = () => loadUsers();
  
    let timer;
    const debounce = () => {
      clearTimeout(timer);
      timer = setTimeout(resetToPage1AndLoad, 250);
    };
  
    searchInput?.addEventListener("input", debounce);
    roleFilter?.addEventListener("change", resetToPage1AndLoad);
    statusFilter?.addEventListener("change", resetToPage1AndLoad);
  
    loadUsers().catch(console.error);
  })();
  