/**
 * Main JavaScript File
 * Handles client-side functionality
 */

document.addEventListener("DOMContentLoaded", function () {
  // Toggle sidebar on mobile
  const sidebarToggle = document.getElementById("sidebar-toggle");
  const sidebar = document.querySelector(".sidebar");

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", function () {
      sidebar.classList.toggle("active");
    });
  }

  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert");

  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.opacity = "0";
      setTimeout(function () {
        alert.style.display = "none";
      }, 500);
    }, 5000);
  });

  // Toggle password visibility
  const passwordToggles = document.querySelectorAll(".password-toggle");

  passwordToggles.forEach(function (toggle) {
    toggle.addEventListener("click", function () {
      const input = this.previousElementSibling;
      const icon = this.querySelector("i");

      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    });
  });

  // Form validation
  const forms = document.querySelectorAll(".needs-validation");

  forms.forEach(function (form) {
    form.addEventListener(
      "submit",
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        form.classList.add("was-validated");
      },
      false
    );
  });

  // Confirm dialog for delete actions
  const confirmActions = document.querySelectorAll(".confirm-action");

  confirmActions.forEach(function (action) {
    action.addEventListener("click", function (event) {
      const message =
        this.getAttribute("data-confirm") ||
        "Are you sure you want to perform this action?";

      if (!confirm(message)) {
        event.preventDefault();
      }
    });
  });

  // Data tables with sorting and filtering
  const dataTables = document.querySelectorAll(".data-table");

  dataTables.forEach(function (table) {
    // Get the table header cells
    const headers = table.querySelectorAll("th[data-sort]");

    // Add click event for sorting
    headers.forEach(function (header) {
      header.addEventListener("click", function () {
        const column = this.getAttribute("data-sort");
        const direction = this.getAttribute("data-direction") || "asc";

        // Reset sort direction for all headers
        headers.forEach((h) => h.setAttribute("data-direction", ""));

        // Set sort direction for current header
        this.setAttribute(
          "data-direction",
          direction === "asc" ? "desc" : "asc"
        );

        // Sort the table
        sortTable(table, column, direction);
      });
    });

    // Search/filter functionality
    const searchInput = document.querySelector(`#${table.id}-search`);

    if (searchInput) {
      searchInput.addEventListener("input", function () {
        const searchTerm = this.value.toLowerCase();
        filterTable(table, searchTerm);
      });
    }
  });

  /**
   * Sort table rows based on column
   * @param {HTMLElement} table Table element
   * @param {string} column Column name
   * @param {string} direction Sort direction (asc/desc)
   */
  function sortTable(table, column, direction) {
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    // Sort rows
    rows.sort(function (a, b) {
      const aValue = a
        .querySelector(`td[data-column="${column}"]`)
        .textContent.trim();
      const bValue = b
        .querySelector(`td[data-column="${column}"]`)
        .textContent.trim();

      // Check if values are numbers
      const aNum = parseFloat(aValue);
      const bNum = parseFloat(bValue);

      if (!isNaN(aNum) && !isNaN(bNum)) {
        return direction === "asc" ? aNum - bNum : bNum - aNum;
      }

      // Sort as strings
      return direction === "asc"
        ? aValue.localeCompare(bValue)
        : bValue.localeCompare(aValue);
    });

    // Remove existing rows
    while (tbody.firstChild) {
      tbody.removeChild(tbody.firstChild);
    }

    // Add sorted rows
    rows.forEach(function (row) {
      tbody.appendChild(row);
    });
  }

  /**
   * Filter table rows based on search term
   * @param {HTMLElement} table Table element
   * @param {string} searchTerm Search term
   */
  function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll("tbody tr");

    rows.forEach(function (row) {
      const text = row.textContent.toLowerCase();

      if (text.includes(searchTerm)) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });
  }

  // Initialize any charts
  const charts = document.querySelectorAll(".chart-container");

  charts.forEach(function (container) {
    const canvas = container.querySelector("canvas");
    const type = container.getAttribute("data-chart-type");
    const dataUrl = container.getAttribute("data-chart-url");

    if (canvas && type && dataUrl) {
      fetchChartData(dataUrl, function (data) {
        createChart(canvas, type, data);
      });
    }
  });

  /**
   * Fetch chart data from API
   * @param {string} url API URL
   * @param {function} callback Callback function
   */
  function fetchChartData(url, callback) {
    fetch(url)
      .then((response) => response.json())
      .then((data) => callback(data))
      .catch((error) => console.error("Error fetching chart data:", error));
  }

  /**
   * Create chart using Chart.js
   * @param {HTMLElement} canvas Canvas element
   * @param {string} type Chart type
   * @param {object} data Chart data
   */
  function createChart(canvas, type, data) {
    // This is a placeholder for Chart.js implementation
    // You would need to include Chart.js library to use this
    console.log("Chart created:", { canvas, type, data });
  }

  // Handle file input
  const fileInputs = document.querySelectorAll(".custom-file-input");

  fileInputs.forEach(function (input) {
    input.addEventListener("change", function () {
      const fileName = this.files[0]?.name || "Pilih file...";
      const label = this.nextElementSibling;

      if (label) {
        label.textContent = fileName;
      }
    });
  });

  // Initialize tooltips
  const tooltips = document.querySelectorAll("[data-tooltip]");

  tooltips.forEach(function (element) {
    element.addEventListener("mouseenter", function () {
      const tooltip = document.createElement("div");
      tooltip.classList.add("tooltip");
      tooltip.textContent = this.getAttribute("data-tooltip");

      document.body.appendChild(tooltip);

      const rect = this.getBoundingClientRect();
      const tooltipRect = tooltip.getBoundingClientRect();

      tooltip.style.top = `${rect.top - tooltipRect.height - 10}px`;
      tooltip.style.left = `${
        rect.left + rect.width / 2 - tooltipRect.width / 2
      }px`;

      this.addEventListener(
        "mouseleave",
        function () {
          document.body.removeChild(tooltip);
        },
        { once: true }
      );
    });
  });
});
