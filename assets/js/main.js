const BASE_URL = window.location.origin + "/";
$(document).on("submit", "#addEmployeeForm", function (e) {
  e.preventDefault();

  const form = $(this);
  const alertBox = $("#employeeAlert");
  const btn = form.find("button[type=submit]");

  alertBox.html("");
  btn.prop("disabled", true).text("Saving...");

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: form.serialize() + "&action=add_employee",
    success: function (res) {
      if (res.status) {
        window.location.href = res.data.redirect;
      } else {
        alertBox.html(`<div class="alert alert-danger">${res.message}</div>`);
      }
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      alertBox.html(`<div class="alert alert-danger">Server error.</div>`);
    },
    complete: function () {
      btn.prop("disabled", false).text("Create Employee");
    },
  });
});

$(document).on("submit", "#editEmployeeForm", function (e) {
  e.preventDefault();

  const form = $(this);
  const alertBox = $("#employeeAlert");
  const btn = form.find("button[type=submit]");

  alertBox.html("");
  btn.prop("disabled", true).text("Updating...");

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: form.serialize() + "&action=update_employee",
    success: function (res) {
      if (res.status) {
        window.location.href = res.data.redirect;
      } else {
        alertBox.html(`<div class="alert alert-danger">${res.message}</div>`);
      }
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      alertBox.html(`<div class="alert alert-danger">Server error.</div>`);
    },
    complete: function () {
      btn.prop("disabled", false).text("Update Employee");
    },
  });
});
// alert("MAIN.JS LOADED");

// console.log("🔥 main.js file loaded");
// console.log("jQuery exists:", typeof $);

$(document).on("click", ".toggleEmployeeStatus", function () {
  const btn = $(this);
  const userId = btn.data("id");
  const currentStatus = btn.data("status");

  if (!confirm("Are you sure you want to change this employee's status?")) {
    return;
  }

  btn.prop("disabled", true).text("Processing...");

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "toggle_employee_status",
      user_id: userId,
      current_status: currentStatus,
    },
    success: function (res) {
      if (res.status) {
        // ✅ IMPORTANT: reload from server
        loadEmployees();
      } else {
        alert(res.message);
      }
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      alert("Server error.");
    },
    complete: function () {
      btn.prop("disabled", false);
    },
  });
});

// ===============================
// EXPORT EMPLOYEE TABLE TO CSV
// ===============================
$(document).on("click", "#exportCsvBtn", function () {
  const rows = [];

  // Table headers
  const headers = [];
  $("table thead th").each(function () {
    headers.push($(this).text().trim());
  });
  rows.push(headers);

  // Table body (only visible rows)
  $("table tbody tr:visible").each(function () {
    const row = [];
    $(this)
      .find("td")
      .each(function () {
        // Remove extra spaces / line breaks
        row.push($(this).text().trim().replace(/\s+/g, " "));
      });
    rows.push(row);
  });

  if (rows.length === 1) {
    alert("No data to export.");
    return;
  }

  // Convert to CSV
  let csvContent = "";
  rows.forEach(function (row) {
    csvContent +=
      row.map((value) => `"${value.replace(/"/g, '""')}"`).join(",") + "\n";
  });

  // Create download
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);

  const link = document.createElement("a");
  link.setAttribute("href", url);
  link.setAttribute("download", "employees.csv");
  link.style.display = "none";

  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
});

// --------------------------------------------------------
// Pagination start
// --------------------------------------------------------
let currentPage = 1;
let rowsPerPage = 10;
let activeEmployeeFilter = "all";
let activeStatusFilter = "all";
let activeDepartmentFilter = "all";
let searchQuery = "";

function loadEmployees() {
  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "fetch_employees",
      page: currentPage,
      limit: rowsPerPage,
      search: searchQuery,
      filterBy: activeEmployeeFilter,
      status: activeStatusFilter,
      department: activeDepartmentFilter,
    },
    success: function (res) {
      if (!res.status) return alert(res.message);

      renderTable(res.data.rows);
      renderPagination(res.data.total);
    },
  });
}

function renderTable(rows) {
  const tbody = $("#employeeTableBody");
  tbody.empty();

  if (!rows.length) {
    tbody.html(`
      <tr>
        <td colspan="5" class="text-center py-4">No employees found</td>
      </tr>
    `);
    return;
  }

  rows.forEach((emp) => {
    const statusBadge = emp.status
      ? `<span class="badge bg-success">Active</span>`
      : `<span class="badge bg-danger">Inactive</span>`;

    const toggleBtnClass = emp.status
      ? "btn-outline-danger"
      : "btn-outline-success";

    const toggleBtnText = emp.status ? "Deactivate" : "Activate";

    tbody.append(`
      <tr>
        <td>${emp.name}</td>
        <td>${emp.email}</td>
        <td>${emp.department ?? "—"}</td>
        <td>${statusBadge}</td>
        <td>
          <a href="${BASE_URL}admin/employees/view?id=${emp.id}"
             class="btn btn-sm btn-outline-secondary">View</a>

          <a href="${BASE_URL}admin/employees/edit?id=${emp.id}"
             class="btn btn-sm btn-outline-primary">Edit</a>

          <button
            type="button"
            class="btn btn-sm ${toggleBtnClass} toggleEmployeeStatus"
            data-id="${emp.id}"
            data-status="${emp.status}">
            ${toggleBtnText}
          </button>
        </td>
      </tr>
    `);
  });
}

function renderPagination(totalRows) {
  const pagination = $("#pagination");
  pagination.empty();

  if (rowsPerPage === "all") return;

  const totalPages = Math.ceil(totalRows / rowsPerPage);
  if (totalPages <= 1) return;

  pagination.append(`
    <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
      <a class="page-link" href="#" data-page="${currentPage - 1}">Prev</a>
    </li>
  `);

  for (let i = 1; i <= totalPages; i++) {
    pagination.append(`
      <li class="page-item ${i === currentPage ? "active" : ""}">
        <a class="page-link" href="#" data-page="${i}">${i}</a>
      </li>
    `);
  }

  pagination.append(`
    <li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
      <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
    </li>
  `);
}
$(document).on("keyup", "#employeeSearch", function () {
  searchQuery = $(this).val();
  currentPage = 1;
  loadEmployees();
});

$(document).on("change", "#statusFilter", function () {
  activeStatusFilter = $(this).val();
  currentPage = 1;
  loadEmployees();
});

$(document).on("change", "#departmentFilter", function () {
  activeDepartmentFilter = $(this).val();
  currentPage = 1;
  loadEmployees();
});

$(document).on("click", "#employeeFilterTabs .nav-link", function () {
  $("#employeeFilterTabs .nav-link").removeClass("active");
  $(this).addClass("active");
  activeEmployeeFilter = $(this).data("filter");
  currentPage = 1;
  loadEmployees();
});

$(document).on("change", "#rowsPerPage", function () {
  rowsPerPage = $(this).val();
  currentPage = 1;
  loadEmployees();
});

$(document).on("click", "#pagination .page-link", function (e) {
  e.preventDefault();
  const page = $(this).data("page");
  if (!isNaN(page)) {
    currentPage = page;
    loadEmployees();
  }
});

$(document).ready(loadEmployees);
// --------------------------------------------------------
// Pagination end
// --------------------------------------------------------
