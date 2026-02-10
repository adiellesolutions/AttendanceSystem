(() => {
    const container = document.getElementById("recent-scans-list");
    if (!container) return;
  
    let lastTopId = null;
  
    function escapeHtml(str) {
      return String(str ?? "").replace(/[&<>"']/g, m => ({
        "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"
      }[m]));
    }
  
    function formatTime(dtString) {
      // dtString is like "2026-02-10 07:12:00"
      // Show as "7:12 AM"
      const d = new Date(dtString.replace(" ", "T"));
      if (isNaN(d)) return dtString;
  
      return d.toLocaleTimeString([], { hour: "numeric", minute: "2-digit" });
    }
  
    function getCardStyle(scanType, status) {
      // you can customize these to match your tailwind theme
      if (scanType === "exit") {
        return {
          bg: "bg-primary-50 border border-primary-200",
          badge: "badge badge-error",
          label: "Exit"
        };
      }
  
      if (status === "late") {
        return {
          bg: "bg-warning-50 border border-warning-200",
          badge: "badge badge-warning",
          label: "Late Entry"
        };
      }
  
      return {
        bg: "bg-success-50 border border-success-200",
        badge: "badge badge-success",
        label: "Entry"
      };
    }
  
    function render(scans) {
      if (!Array.isArray(scans) || scans.length === 0) {
        container.innerHTML = `<div class="text-sm text-text-secondary">No recent scans yet.</div>`;
        return;
      }
  
      container.innerHTML = scans.map(s => {
        const fullName = escapeHtml(s.full_name);
        const studId   = escapeHtml(s.student_id);
        const timeTxt  = escapeHtml(formatTime(s.scan_time));
  
        const style = getCardStyle(s.scan_type, s.status);
  
        return `
          <div class="flex items-center justify-between p-3 rounded-xl ${style.bg}">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 rounded-full flex items-center justify-center">
                <!-- optional icon placeholder -->
                <div class="w-2 h-2 rounded-full"></div>
              </div>
              <div>
                <p class="font-semibold text-text-primary text-sm">${fullName}</p>
                <p class="text-xs text-text-secondary">${studId}</p>
              </div>
            </div>
            <div class="text-right">
              <span class="${style.badge} text-xs">${escapeHtml(style.label)}</span>
              <p class="text-xs text-text-secondary mt-1">${timeTxt}</p>
            </div>
          </div>
        `;
      }).join("");
    }
  
    async function loadRecentScans() {
      try {
        const res = await fetch(`../../backend/api/recent_scans.php?limit=8`, { cache: "no-store" });
        if (!res.ok) throw new Error("API failed");
        const data = await res.json();
  
        if (!data?.success) throw new Error(data?.message || "Failed");
  
        const scans = data.scans || [];
  
        // Optional: only re-render if top item changed
        const topId = scans[0]?.id ?? null;
        if (topId && topId === lastTopId) return;
  
        lastTopId = topId;
        render(scans);
      } catch (e) {
        console.error("Recent scans load error:", e);
        container.innerHTML = `<div class="text-sm text-text-secondary">Failed to load scans.</div>`;
      }
    }
  
    // initial + polling
    loadRecentScans();
    setInterval(loadRecentScans, 2000);
  })();
  