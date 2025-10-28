
document.addEventListener("DOMContentLoaded", function () {
  initializeAlertFilters();
  initializeFeedControls();
  initializeMarkAsRead();
  initializeLoadMore();
});

// ===============================================
// FILTER SYSTEM
// ===============================================
function initializeAlertFilters() {
  const typeFilter = document.getElementById("typeFilter");
  const severityFilter = document.getElementById("severityFilter");
  const timeFilter = document.getElementById("timeFilter");
  const statusFilter = document.getElementById("statusFilter");
  const resetBtn = document.getElementById("resetAlertFilters");
  const alertsList = document.getElementById("alertsList");
  const emptyState = document.getElementById("emptyState");

  function filterAlerts() {
    const type = typeFilter.value;
    const severity = severityFilter.value;
    const time = timeFilter.value;
    const status = statusFilter.value;

    let visibleCount = 0;
    const alerts = alertsList.querySelectorAll(".alert-card");

    alerts.forEach((alert) => {
      const matchesType = type === "all" || alert.querySelector(".alert-type").textContent.toLowerCase().includes(type);
      const matchesSeverity = severity === "all" || alert.classList.contains(severity);
      const matchesStatus = status === "all" || alert.classList.contains(status);
      // Time filter can be extended with timestamps later

      if (matchesType && matchesSeverity && matchesStatus) {
        alert.style.display = "flex";
        visibleCount++;
      } else {
        alert.style.display = "none";
      }
    });

    emptyState.style.display = visibleCount === 0 ? "block" : "none";
  }

  [typeFilter, severityFilter, timeFilter, statusFilter].forEach((select) =>
    select.addEventListener("change", filterAlerts)
  );

  resetBtn.addEventListener("click", () => {
    [typeFilter, severityFilter, timeFilter, statusFilter].forEach((s) => (s.value = "all"));
    filterAlerts();
  });
}

// ===============================================
// FEED VIEW CONTROLS (All / Unread)
// ===============================================
function initializeFeedControls() {
  const buttons = document.querySelectorAll(".feed-controls .control-btn");
  const alerts = document.querySelectorAll(".alert-card");
  const emptyState = document.getElementById("emptyState");

  buttons.forEach((btn) => {
    btn.addEventListener("click", () => {
      buttons.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");

      const view = btn.dataset.view;
      let visibleCount = 0;

      alerts.forEach((alert) => {
        const isUnread = alert.classList.contains("unread");
        if (view === "all" || (view === "unread" && isUnread)) {
          alert.style.display = "flex";
          visibleCount++;
        } else {
          alert.style.display = "none";
        }
      });

      emptyState.style.display = visibleCount === 0 ? "block" : "none";
    });
  });
}

// ===============================================
// MARK AS READ FUNCTIONALITY
// ===============================================
function initializeMarkAsRead() {
  const alerts = document.querySelectorAll(".alert-card");

  alerts.forEach((alert) => {
    const markBtn = alert.querySelector(".mark-read-btn");
    if (!markBtn) return;

    markBtn.addEventListener("click", () => {
      alert.classList.remove("unread");
      alert.classList.add("read");
      markBtn.classList.add("marked");

      const indicator = alert.querySelector(".alert-indicator");
      if (indicator) indicator.style.opacity = "0.3";

      showToast("Alert marked as read");
    });
  });
}

// ===============================================
// LOAD MORE (Simulated)
// ===============================================
function initializeLoadMore() {
  const loadMoreBtn = document.getElementById("loadMore");
  if (!loadMoreBtn) return;

  loadMoreBtn.addEventListener("click", () => {
    showToast("Loading more alerts...");
    setTimeout(() => {
      showToast("No more alerts to load");
    }, 1200);
  });
}


function showToast(message) {
  let toast = document.querySelector(".toast-message");
  if (!toast) {
    toast = document.createElement("div");
    toast.className = "toast-message";
    document.body.appendChild(toast);
  }

  toast.textContent = message;
  toast.classList.add("visible");

  setTimeout(() => {
    toast.classList.remove("visible");
  }, 2500);
}
