// ===============================
// EMPLOYEE LIST LIVE FILTER
// ===============================
let activeEmployeeFilter = "all";
let activeStatusFilter = "all";
let activeDepartmentFilter = "all";

// Tab click
$(document).on("click", "#employeeFilterTabs .nav-link", function () {
  $("#employeeFilterTabs .nav-link").removeClass("active");
  $(this).addClass("active");
  currentPage = 1;
  activeEmployeeFilter = $(this).data("filter");
  $("#employeeSearch").trigger("keyup");
});
$(document).on("change", "#statusFilter", function () {
  activeStatusFilter = $(this).val();
  currentPage = 1;

  $("#employeeSearch").trigger("keyup");
});
$(document).on("change", "#departmentFilter", function () {
  activeDepartmentFilter = $(this).val();
  currentPage = 1;
  $("#employeeSearch").trigger("keyup");
});

// Live search
$(document).on("keyup", "#employeeSearch", function () {
  const query = $(this).val().toLowerCase();
  currentPage = 1;
  $("table tbody tr").each(function () {
    const row = $(this);

    const name = row.data("name") || "";
    const email = row.data("email") || "";
    const department = row.data("department") || "";
    const status = row.data("status").toString();

    let matchText = false;
    let matchStatus = false;
    let matchDepartment = false;

    switch (activeEmployeeFilter) {
      case "name":
        matchText = name.includes(query);
        break;
      case "email":
        matchText = email.includes(query);
        break;
      case "department":
        matchText = department.includes(query);
        break;
      default:
        matchText =
          name.includes(query) ||
          email.includes(query) ||
          department.includes(query);
    }

    // STATUS FILTER
    matchStatus = activeStatusFilter === "all" || status === activeStatusFilter;

    // DEPARTMENT FILTER
    matchDepartment =
      activeDepartmentFilter === "all" || department === activeDepartmentFilter;

    // FINAL VISIBILITY
    if (matchText && matchStatus && matchDepartment) {
      row.show().addClass("filtered");
    } else {
      row.hide().removeClass("filtered");
    }
  });
  applyPagination();
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

let currentPage = 1;
let rowsPerPage = 10;
$(document).on("change", "#rowsPerPage", function () {
  const val = $(this).val();
  rowsPerPage = val === "all" ? "all" : parseInt(val, 10);
  currentPage = 1;
  applyPagination();
});
function applyPagination() {
  const filteredRows = $("table tbody tr.filtered");
  const totalRows = filteredRows.length;

  // Show all
  if (rowsPerPage === "all") {
    filteredRows.show();
    $("#pagination").empty();
    $("#paginationInfo").text(`Showing all ${totalRows} entries`);
    return;
  }

  const totalPages = Math.ceil(totalRows / rowsPerPage);
  if (currentPage > totalPages) currentPage = totalPages || 1;

  const start = (currentPage - 1) * rowsPerPage;
  const end = start + rowsPerPage;

  filteredRows.hide().slice(start, end).show();

  const from = totalRows === 0 ? 0 : start + 1;
  const to = Math.min(end, totalRows);

  $("#paginationInfo").text(`Showing ${from} to ${to} of ${totalRows} entries`);

  renderPagination(totalPages);
}

// -------------------------------------- first function renderPagination 10-02-2026
function renderPagination(totalPages) {
  const pagination = $("#pagination");
  pagination.empty();

  if (totalPages <= 1) return;

  // Prev
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

  // Next
  pagination.append(`
    <li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
      <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
    </li>
  `);
}
// -------------------------------------- end function renderPagination
$(document).on("click", "#pagination .page-link", function (e) {
  e.preventDefault();

  const page = parseInt($(this).data("page"), 10);
  if (!isNaN(page)) {
    currentPage = page;
    applyPagination();
  }
});

$(document).ready(function () {
  $("table tbody tr").addClass("filtered");
  applyPagination();
});

// -------------------------------------- second function renderPagination 11-02-2026
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
// -------------------------------------- end function renderPagination
// 🔥 OPTIONAL POLISH (SMOOTHER UX)
// If you want to improve further, we can:
// Add « and » buttons
// Add page jump input
// Add “Showing 101–110 of 982 results”
// Animate pagination transition
// Disable Prev/Next properly when at edges
