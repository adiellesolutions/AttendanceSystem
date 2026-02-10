(() => {
    const tbody = document.getElementById("today-attendance-tbody");
    const mobile = document.getElementById("today-attendance-mobile");
    if (!tbody || !mobile) return;
    const datePicker = document.getElementById("date-picker");
    const searchInput = document.getElementById("student-search");
    let currentPage = 1;
const PAGE_SIZE = 8;

const paginationInfo = document.getElementById("attendance-pagination-info");
const paginationEl   = document.getElementById("attendance-pagination");

    // set default date = today if empty
    if (datePicker && !datePicker.value) {
      const now = new Date();
      const yyyy = now.getFullYear();
      const mm = String(now.getMonth() + 1).padStart(2, "0");
      const dd = String(now.getDate()).padStart(2, "0");
      datePicker.value = `${yyyy}-${mm}-${dd}`;
    }
    
    function escapeHtml(str) {
      return String(str ?? "").replace(/[&<>"']/g, m => ({
        "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"
      }[m]));
    }
  
    function formatTime(dtString) {
      if (!dtString) return "—";
      const d = new Date(dtString.replace(" ", "T"));
      if (isNaN(d)) return dtString;
      return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
    }
  
    function badgeFor(status) {
      if (status === "present") return { cls: "badge badge-success", label: "Present" };
      if (status === "late") return { cls: "badge badge-warning", label: "Late" };
      return { cls: "badge badge-error", label: "Absent" };
    }
  
    function timeClass(status, type) {
      // Make late time-in orange like your mock
      if (status === "late" && type === "in") return "data-text text-warning";
      return "data-text text-text-primary";
    }
  
    function photoSrc(path) {
      // If you store profile_photo path in users.profile_photo, use it here.
      // Otherwise fallback.
      if (path && String(path).trim() !== "") return path;
      return "https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop";
    }
  
    function renderDesktop(records) {
      if (!records.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-text-secondary">No records</td></tr>`;
        return;
      }
  
      tbody.innerHTML = records.map(r => {
        const b = badgeFor(r.day_status);
        const name = escapeHtml(r.full_name);
        const sid = escapeHtml(r.student_id);
        const tIn = formatTime(r.time_in);
        const tOut = formatTime(r.time_out);
        const img = escapeHtml(photoSrc(r.profile_photo));
  
        return `
          <tr>
            <td>
            </td>
            <td>
              <div class="flex items-center space-x-3">
                <img
                  src="${img}"
                  alt="${name} student profile photo"
                  class="w-10 h-10 rounded-full object-cover"
                  onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop'; this.onerror=null;">
                <span class="font-semibold text-text-primary">${name}</span>
              </div>
            </td>
            <td class="data-text text-text-secondary">${sid}</td>
            <td class="${timeClass(r.day_status,'in')}">${escapeHtml(tIn)}</td>
            <td class="data-text text-text-secondary">${escapeHtml(tOut)}</td>
            <td><span class="${b.cls}">${b.label}</span></td>
          </tr>
        `;
      }).join("");
    }
  
    function renderMobile(records) {
      if (!records.length) {
        mobile.innerHTML = `<div class="text-sm text-text-secondary">No records</div>`;
        return;
      }
  
      mobile.innerHTML = records.map(r => {
        const b = badgeFor(r.day_status);
        const name = escapeHtml(r.full_name);
        const sid = escapeHtml(r.student_id);
        const tIn = formatTime(r.time_in);
        const tOut = formatTime(r.time_out);
        const img = escapeHtml(photoSrc(r.profile_photo));
  
        return `
          <div class="card p-4 hover:shadow-card-hover">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center space-x-3">
                <img
                  src="${img}"
                  alt="${name} student profile photo"
                  class="w-12 h-12 rounded-full object-cover"
                  onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop'; this.onerror=null;">
                <div>
                  <p class="font-semibold text-text-primary">${name}</p>
                  <p class="text-sm data-text text-text-secondary">${sid}</p>
                </div>
              </div>
              <span class="${b.cls}">${b.label}</span>
            </div>
  
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-text-secondary mb-1">Time In</p>
                <p class="data-text font-semibold ${r.day_status === 'late' ? 'text-warning' : 'text-text-primary'}">
                  ${escapeHtml(tIn)}
                </p>
              </div>
              <div>
                <p class="text-text-secondary mb-1">Time Out</p>
                <p class="data-text text-text-secondary">${escapeHtml(tOut)}</p>
              </div>
            </div>
          </div>
        `;
      }).join("");
    }
  
    async function loadTodayAttendance() {
        try {
          const date = datePicker?.value || "";
          const search = searchInput?.value || "";
      
          const url =
          `../../backend/api/today_attendance.php` +
          `?date=${encodeURIComponent(date)}` +
          `&search=${encodeURIComponent(search)}` +
          `&page=${currentPage}` +
          `&limit=${PAGE_SIZE}`;
              
          const res = await fetch(url, { cache: "no-store" });
          if (!res.ok) throw new Error("API failed");
          const data = await res.json();
          if (!data?.success) throw new Error(data?.message || "Failed");
      
          const records = data.records || [];
          renderDesktop(records);
          renderMobile(records);
          renderPagination(data.pagination);

        } catch (e) {
          console.error("today_attendance error:", e);
          tbody.innerHTML = `<tr><td colspan="6" class="text-center text-text-secondary">Failed to load.</td></tr>`;
          mobile.innerHTML = `<div class="text-sm text-text-secondary">Failed to load.</div>`;
        }
      }
      
      let searchTimer = null;

      datePicker?.addEventListener("change", () => {
        currentPage = 1;
        loadTodayAttendance();
      });
      
      searchInput?.addEventListener("input", () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
          currentPage = 1;
          loadTodayAttendance();
        }, 250);
      });
      
      function renderPagination(p) {
        if (!p || p.pages <= 1) {
          paginationInfo.textContent = `Showing ${p.total} students`;
          paginationEl.innerHTML = "";
          return;
        }
      
        const start = (p.page - 1) * p.limit + 1;
        const end   = Math.min(p.page * p.limit, p.total);
      
        paginationInfo.textContent =
          `Showing ${start}–${end} of ${p.total} students`;
      
        let html = "";
      
        html += `
          <button class="btn-outline h-10 w-10"
            ${p.page === 1 ? "disabled" : ""}
            onclick="goToPage(${p.page - 1})">
            ‹
          </button>
        `;
      
        for (let i = 1; i <= p.pages; i++) {
          html += `
            <button
              class="${i === p.page ? "btn btn-primary" : "btn-outline"} h-10 w-10"
              onclick="goToPage(${i})">
              ${i}
            </button>
          `;
        }
      
        html += `
          <button class="btn-outline h-10 w-10"
            ${p.page === p.pages ? "disabled" : ""}
            onclick="goToPage(${p.page + 1})">
            ›
          </button>
        `;
      
        paginationEl.innerHTML = html;
      }
      
      window.goToPage = function (page) {
        if (page < 1) return;
        currentPage = page;
        loadTodayAttendance();
      };
      
    // Load now + refresh every 10 seconds
    loadTodayAttendance();
    setInterval(loadTodayAttendance, 10000);
  })();
  