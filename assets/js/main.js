const BASE_URL = window.location.origin + "/";
// ============================
// SIDEBAR TOGGLE
// ============================
const sidebar = document.querySelector(".sidebar");
const overlay = document.getElementById("sidebarOverlay");
const rod = document.getElementById("sidebarRod");

let startX = 0;

// Open by click
rod?.addEventListener("click", () => {
  sidebar.classList.add("active");
  overlay.classList.add("active");
});

// Close by overlay click
overlay?.addEventListener("click", () => {
  sidebar.classList.remove("active");
  overlay.classList.remove("active");
});

// Swipe from rod only
rod?.addEventListener("touchstart", (e) => {
  startX = e.touches[0].clientX;
});

rod?.addEventListener("touchend", (e) => {
  const endX = e.changedTouches[0].clientX;

  if (endX > startX + 40) {
    sidebar.classList.add("active");
    overlay.classList.add("active");
  }
});

function animateCounters() {
  $(".counter").each(function () {
    const $this = $(this);
    const target = parseInt($this.data("target"), 10);
    let count = 0;
    const increment = Math.ceil(target / 50);

    const interval = setInterval(function () {
      count += increment;
      if (count >= target) {
        count = target;
        clearInterval(interval);
      }
      $this.text(count);
    }, 20);
  });
}

// ================ Employee ================
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
// Employees and Pagination start
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
        <td data-label="Name">${emp.name}</td>
        <td data-label="Email">${emp.email}</td>
        <td data-label="Department">${emp.department ?? "—"}</td>
        <td data-label="Status">${statusBadge}</td>
        <td data-label="Actions">
        <div class="action-buttons">
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
        </div>
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

  const maxVisible = 2; // pages before & after current

  // PREV BUTTON
  pagination.append(`
    <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
      <a class="page-link" href="#" data-page="${currentPage - 1}">Prev</a>
    </li>
  `);

  function addPage(page) {
    pagination.append(`
      <li class="page-item ${page === currentPage ? "active" : ""}">
        <a class="page-link" href="#" data-page="${page}">${page}</a>
      </li>
    `);
  }

  function addDots() {
    pagination.append(`
      <li class="page-item disabled">
        <span class="page-link">...</span>
      </li>
    `);
  }

  // ALWAYS show first page
  addPage(1);

  // LEFT DOTS
  if (currentPage - maxVisible > 2) {
    addDots();
  }

  // MIDDLE PAGES
  const start = Math.max(2, currentPage - maxVisible);
  const end = Math.min(totalPages - 1, currentPage + maxVisible);

  for (let i = start; i <= end; i++) {
    addPage(i);
  }

  // RIGHT DOTS
  if (currentPage + maxVisible < totalPages - 1) {
    addDots();
  }

  // ALWAYS show last page (if more than 1 page)
  if (totalPages > 1) {
    addPage(totalPages);
  }

  // NEXT BUTTON
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

// $(document).ready(loadEmployees);
// --------------------------------------------------------
// Pagination end
// --------------------------------------------------------

// ----------------------------------------DEPARTMENT---------------------
// add department
$(document).on("submit", "#addDepartmentForm", function (e) {
  e.preventDefault();

  const form = $(this);
  const alertBox = $("#departmentAlert");
  const btn = form.find("button[type=submit]");

  alertBox.html("");
  btn.prop("disabled", true).text("Saving...");

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: form.serialize() + "&action=add_department",
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
      btn.prop("disabled", false).text("Create Department");
    },
  });
});

// edit department
$(document).on("submit", "#editDepartmentForm", function (e) {
  e.preventDefault();

  const form = $(this);
  const alertBox = $("#departmentAlert");
  const btn = form.find("button[type=submit]");

  alertBox.html("");
  btn.prop("disabled", true).text("Updating...");

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: form.serialize() + "&action=update_department",
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
      btn.prop("disabled", false).text("Update Department");
    },
  });
});

// transfer department
$(document).on("submit", "#transferDepartmentForm", function (e) {
  e.preventDefault();

  const form = $(this);
  const btn = form.find("button[type=submit]");
  const alertBox = $("#transferAlert");

  alertBox.html("");
  btn.prop("disabled", true).text("Processing...");

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: form.serialize() + "&action=transfer_and_delete_department",
    success: function (res) {
      if (res.status) {
        window.location.href = res.data.redirect;
      } else {
        alertBox.html(`<div class="alert alert-danger">${res.message}</div>`);
      }
    },
    complete: function () {
      btn.prop("disabled", false).text("Transfer & Delete");
    },
  });
});
let transferPage = 1;
const transferLimit = 10;
const departmentId = new URLSearchParams(window.location.search).get("id");

function loadTransferEmployees() {
  $.post(
    BASE_URL + "includes/functions.php",
    {
      action: "fetch_department_employees",
      department_id: departmentId,
      page: transferPage,
    },
    function (res) {
      if (!res.status) return;

      renderTransferEmployees(res.data.rows);
      renderTransferPagination(res.data.total);
    },
    "json",
  );
}

function renderTransferEmployees(rows) {
  const tbody = $("#transferEmployeeTableBody");
  tbody.empty();

  if (!rows.length) {
    tbody.html(
      `<tr><td colspan="3" class="text-center">No employees</td></tr>`,
    );
    return;
  }

  rows.forEach((emp) => {
    tbody.append(`
      <tr>
        <td data-label="Name">${emp.name}</td>
        <td data-label="Email">${emp.email}</td>
        <td data-label="Designation">${emp.designation ?? "-"}</td>
      </tr>
    `);
  });
}

function renderTransferPagination(total) {
  const pagination = $("#transferPagination");
  pagination.empty();

  const totalPages = Math.ceil(total / transferLimit);
  if (totalPages <= 1) return;

  for (let i = 1; i <= totalPages; i++) {
    pagination.append(`
      <li class="page-item ${i === transferPage ? "active" : ""}">
        <a class="page-link" href="#" data-page="${i}">${i}</a>
      </li>
    `);
  }
}

$(document).on("click", "#transferPagination .page-link", function (e) {
  e.preventDefault();
  transferPage = parseInt($(this).data("page"));
  loadTransferEmployees();
});

$(document).on("click", "#confirmTransferBtn", function () {
  if (!$("select[name='to_department_id']").val()) {
    alert("Please select target department.");
    return;
  }

  const modal = new bootstrap.Modal(
    document.getElementById("confirmTransferModal"),
  );
  modal.show();
});

$(document).on("click", "#finalTransferBtn", function () {
  $("#transferDepartmentForm").submit();
});

let deptCurrentPage = 1;
let deptRowsPerPage = 10;
let deptSearchQuery = "";
function loadDepartments() {
  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "fetch_departments",
      page: deptCurrentPage,
      limit: deptRowsPerPage,
      search: deptSearchQuery,
    },
    success: function (res) {
      if (!res.status) return alert(res.message);
      renderDepartmentTable(res.data.rows);
      renderDepartmentPagination(res.data.total);
    },
  });
}

function renderDepartmentTable(rows) {
  const tbody = $("#departmentTableBody");
  tbody.empty();

  if (!rows.length) {
    tbody.html(`
      <tr>
        <td colspan="4" class="text-center py-4">No departments found</td>
      </tr>
    `);
    return;
  }

  rows.forEach((dept) => {
    const badge = dept.status
      ? `<span class="badge bg-success">Active</span>`
      : `<span class="badge bg-danger">Disabled</span>`;

    const btnClass = dept.status ? "btn-outline-danger" : "btn-outline-success";
    const btnText = dept.status ? "Disable" : "Enable";

    const disableBtn = dept.employee_count > 0 && dept.status ? "disabled" : "";

    tbody.append(`
      <tr>
        <td data-label="Name">${dept.name}</td>
        <td data-label="Employee Count">
          <span class="badge bg-info text-dark">
            ${dept.employee_count}
          </span>
        </td>
        <td data-label="Status">${badge}</td>
        <td data-label="Actions">
        <div class="action-buttons">
          <a href="${BASE_URL}admin/departments/edit?id=${dept.id}"
             class="btn btn-sm btn-outline-primary">Edit</a>

          <button 
            class="btn btn-sm ${btnClass} toggleDepartmentStatus"
            data-id="${dept.id}"
            data-status="${dept.status}"
            ${disableBtn}>
            ${btnText}
          </button>
          <a href="${BASE_URL}admin/departments/transfer?id=${dept.id}"
            class="btn btn-sm btn-outline-danger">
              <i class="fa fa-exchange"></i>
          </a>
        </div>

        </td>
      </tr>
    `);
  });
}

function renderDepartmentPagination(totalRows) {
  const pagination = $("#departmentPagination");
  pagination.empty();

  if (deptRowsPerPage === "all") return;

  const totalPages = Math.ceil(totalRows / deptRowsPerPage);
  if (totalPages <= 1) return;

  const maxVisible = 2;

  function addPage(p) {
    pagination.append(`
      <li class="page-item ${p === deptCurrentPage ? "active" : ""}">
        <a class="page-link" href="#" data-page="${p}">${p}</a>
      </li>
    `);
  }

  function addDots() {
    pagination.append(`
      <li class="page-item disabled"><span class="page-link">...</span></li>
    `);
  }

  addPage(1);

  if (deptCurrentPage - maxVisible > 2) addDots();

  const start = Math.max(2, deptCurrentPage - maxVisible);
  const end = Math.min(totalPages - 1, deptCurrentPage + maxVisible);

  for (let i = start; i <= end; i++) addPage(i);

  if (deptCurrentPage + maxVisible < totalPages - 1) addDots();

  if (totalPages > 1) addPage(totalPages);
}
$(document).on("click", "#departmentPagination .page-link", function (e) {
  e.preventDefault();
  const page = $(this).data("page");
  if (!isNaN(page)) {
    deptCurrentPage = page;
    loadDepartments();
  }
});

$(document).on("click", ".toggleDepartmentStatus", function () {
  const btn = $(this);
  const id = btn.data("id");
  const status = btn.data("status");

  if (!confirm("Change department status?")) return;

  $.post(
    BASE_URL + "includes/functions.php",
    {
      action: "toggle_department_status",
      id: id,
      current_status: status,
    },
    function (res) {
      if (res.status) loadDepartments();
      else alert(res.message);
    },
    "json",
  );
});

// ------------------------------------TASKS managment--------------------------------

$(document).on("submit", "#createTaskForm", function (e) {
  e.preventDefault();

  const form = $(this);
  const alertBox = $("#taskAlert");
  const btn = form.find("button[type=submit]");

  alertBox.html("");
  btn.prop("disabled", true).text("Creating...");

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: form.serialize() + "&action=create_task",
    success: function (res) {
      if (res.status) {
        window.location.href = res.data.redirect;
      } else {
        alertBox.html(`<div class="alert alert-danger">${res.message}</div>`);
      }
    },
    error: function () {
      alertBox.html(`<div class="alert alert-danger">Server error.</div>`);
    },
    complete: function () {
      btn.prop("disabled", false).text("Create Task");
    },
  });
});

$(document).on("change", "#departmentSelect", function () {
  const deptId = $(this).val();
  const container = $("#employeeCheckboxContainer");

  container.html("<p>Loading employees...</p>");

  if (!deptId) {
    container.html("<p class='text-muted'>Select department first</p>");
    return;
  }

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "fetch_task_department_employees",
      department_id: deptId,
      task_id: $("input[name='task_id']").val(),
    },
    success: function (res) {
      if (!res.status) {
        container.html("<p>No employees found</p>");
        return;
      }

      let html = "";

      res.data.forEach((emp) => {
        const checked = emp.assigned ? "checked" : "";

        html += `
          <label class="employee-card">
            <input type="checkbox" 
                   name="assigned_users[]" 
                   value="${emp.id}" 
                   ${checked}>
            ${emp.name}
          </label>
        `;
      });

      container.html(html || "<p>No active employees</p>");
    },
  });
});

// ============================
// TASK MANAGEMENT
// ============================

let taskPage = 1;
let taskLimit = 6;
let taskSearch = "";
let taskStatus = "all";
let taskPriority = "all";

function loadTasks() {
  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "fetch_tasks",
      page: taskPage,
      limit: taskLimit,
      search: taskSearch,
      status: taskStatus,
      priority: taskPriority,
    },
    success: function (res) {
      if (!res.status) return alert(res.message);

      renderTasks(res.data.rows);
      renderTaskPagination(res.data.total);
    },
  });
}
function renderTasks(tasks) {
  const container = $("#taskListContainer");
  container.empty();

  if (!tasks.length) {
    container.html("<p class='text-muted'>No tasks found</p>");
    return;
  }

  tasks.forEach((task) => {
    const completedClass = task.status === "completed" ? "task-completed" : "";
    const isOverdue =
      task.due_date &&
      task.due_date < new Date().toISOString().split("T")[0] &&
      task.status !== "completed";

    const priorityColor =
      task.priority === "high"
        ? "danger"
        : task.priority === "medium"
          ? "warning"
          : "primary";

    container.append(`
      <div class="col-md-6 col-lg-4">
        <div class="card task-card ${completedClass} ${isOverdue ? "border-danger" : ""}">
          <div class="card-body">

            <div class="d-flex justify-content-between mb-2">
              <span class="badge bg-${priorityColor}">
                ${task.priority.toUpperCase()}
              </span>

              <span class="badge bg-secondary">
                ${task.status.replace("_", " ").toUpperCase()}
              </span>
            </div>

            <h5>${task.title}</h5>

            <small class="text-muted">
              Assigned to: ${task.employee_name}
            </small>

            <div class="mt-2">
              <small>
                Due: ${task.due_date ?? "—"}
                ${isOverdue ? `<span class="text-danger">(Overdue)</span>` : ""}
              </small>
            </div>

            <div class="progress mt-3" style="height:6px;">
              <div class="progress-bar bg-success"
                   style="width:${task.progress}%"></div>
            </div>
            <small>${task.progress}% completed</small>

            <div class="mt-3 d-flex gap-2">
              <a href="${BASE_URL}admin/tasks/view?id=${task.id}"
                 class="btn btn-sm btn-outline-secondary">View</a>

              <a href="${BASE_URL}admin/tasks/edit?id=${task.id}"
                 class="btn btn-sm btn-outline-primary">Edit</a>

              <button class="btn btn-sm btn-outline-danger deleteTaskBtn"
                      data-id="${task.id}">
                Delete
              </button>
            </div>

          </div>
        </div>
      </div>
    `);
  });
}
function renderTaskPagination(totalRows) {
  const pagination = $("#taskPagination");
  pagination.empty();

  const totalPages = Math.ceil(totalRows / taskLimit);
  if (totalPages <= 1) return;

  for (let i = 1; i <= totalPages; i++) {
    pagination.append(`
      <li class="page-item ${i === taskPage ? "active" : ""}">
        <a class="page-link" href="#" data-page="${i}">${i}</a>
      </li>
    `);
  }
}

$(document).on("click", "#taskPagination .page-link", function (e) {
  e.preventDefault();
  taskPage = parseInt($(this).data("page"));
  loadTasks();
});

function loadTaskHistory() {
  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "fetch_task_history",
      page: 1,
      limit: 6,
    },
    success: function (res) {
      if (!res.status) return;

      const container = $("#taskHistoryContainer");
      container.empty();

      res.data.rows.forEach((task) => {
        container.append(`
          <div class="col-md-6 col-lg-4">
            <div class="card border-success">
              <div class="card-body">
                <h5>${task.title}</h5>
                <span class="badge bg-success">COMPLETED</span>
                <p class="mt-2">
                  Assigned to: ${task.employee_name}
                </p>
                <a href="${BASE_URL}admin/tasks/view?id=${task.id}" 
                   class="btn btn-sm btn-outline-success">
                   View
                </a>
              </div>
            </div>
          </div>
        `);
      });
    },
  });
}

$(document).on("submit", ".addCommentForm", function (e) {
  e.preventDefault();

  const form = $(this);
  const taskId = form.find('[name="task_id"]').val();
  const comment = form.find('[name="comment"]').val();

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "add_task_comment",
      task_id: taskId,
      comment: comment,
    },
    success: function (res) {
      if (res.status) {
        const commentHtml = `
      <div class="border-bottom pb-2 mb-2">
        <strong>You</strong>
        <div>${comment}</div>
      </div>
    `;
        $(".task-comments").append(commentHtml);
        form[0].reset();
      } else {
        alert(res.message);
      }
    },
  });
});

$(document).on("click", ".editCommentBtn", function () {
  const container = $(this).closest(".comment-item");
  const textDiv = container.find(".comment-text");
  const oldText = textDiv.text().trim();

  textDiv.html(`
    <textarea class="form-control editCommentTextarea">${oldText}</textarea>
    <button class="btn btn-sm btn-success saveCommentBtn mt-1">Save</button>
  `);
});
// SAVE EDITED COMMENT
$(document).on("click", ".saveCommentBtn", function () {
  const container = $(this).closest(".comment-item");
  const commentId = container.data("id");
  const newText = container.find(".editCommentTextarea").val().trim();

  if (!newText) {
    alert("Comment cannot be empty.");
    return;
  }

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "update_task_comment",
      comment_id: commentId,
      comment: newText,
    },
    success: function (res) {
      if (res.status) {
        container.find(".comment-text").html(newText.replace(/\n/g, "<br>"));
      } else {
        alert(res.message);
      }
    },
  });
});

$(document).on("submit", ".uploadAttachmentForm", function (e) {
  e.preventDefault();

  const form = $(this)[0];
  const formData = new FormData(form);
  formData.append("action", "upload_task_attachment");

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (res) {
      if (res.status) {
        location.reload(); // for now
      } else {
        alert(res.message);
      }
    },
  });
});

$(document).on("input", ".taskProgressSlider", function () {
  const slider = $(this);
  const taskId = slider.data("task");
  const progress = slider.val();

  $(".taskProgressBar").css("width", progress + "%");
  $(".progressText").text(progress + "% Completed");

  $.post(
    BASE_URL + "includes/functions.php",
    {
      action: "update_task_progress",
      task_id: taskId,
      progress: progress,
    },
    function () {},
    "json",
  );
});

$(document).on("change", ".taskStatusDropdown", function () {
  const taskId = $(this).data("task");
  const status = $(this).val();

  $.post(
    BASE_URL + "includes/functions.php",
    {
      action: "update_task_status",
      task_id: taskId,
      status: status,
    },
    function (res) {
      if (!res.status) alert(res.message);
    },
    "json",
  );
});

$(document).on("submit", "#editTaskForm", function (e) {
  e.preventDefault();

  $.post(
    BASE_URL + "includes/functions.php",
    $(this).serialize() + "&action=update_task",
    function (res) {
      if (res.status) {
        window.location = res.data.redirect;
      } else {
        alert(res.message);
      }
    },
    "json",
  );
});

// Open Delete Modal
$(document).on("click", ".deleteTaskBtn", function () {
  const taskId = $(this).data("id");

  $("#deleteTaskId").val(taskId);
  $("#confirmDeleteInput").val("");
  $("#confirmDeleteBtn").prop("disabled", true);

  const modal = new bootstrap.Modal(document.getElementById("deleteTaskModal"));
  modal.show();
});

// Enable button only if user types "Delete"
$(document).on("input", "#confirmDeleteInput", function () {
  const value = $(this).val();
  $("#confirmDeleteBtn").prop("disabled", value !== "Delete");
});

// Confirm Delete
$(document).on("click", "#confirmDeleteBtn", function () {
  const taskId = $("#deleteTaskId").val();

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "delete_task",
      task_id: taskId,
    },
    success: function (res) {
      if (res.status) {
        const card = $(`[data-id='${taskId}']`).closest(".task-card");
        card.addClass("removing");
        setTimeout(() => {
          loadTasks();
        }, 300);
        bootstrap.Modal.getInstance(
          document.getElementById("deleteTaskModal"),
        ).hide();
      } else {
        alert(res.message);
      }
    },
  });
});

// ------------------------------Employee code-------------------------------
// =============================
// EMPLOYEE TASKS
// =============================

let empTaskPage = 1;
let empTaskLimit = 6;
let empTaskStatus = "all";
let empTaskPriority = "all";

function loadEmployeeTasks() {
  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "fetch_employee_tasks",
      page: empTaskPage,
      limit: empTaskLimit,
      status: empTaskStatus,
      priority: empTaskPriority,
    },
    success: function (res) {
      if (!res.status) return alert(res.message);

      renderEmployeeTasks(res.data.rows);
      renderEmployeeTaskPagination(res.data.total);
    },
  });
}

function renderEmployeeTasks(tasks) {
  const container = $("#employeeTaskContainer");
  container.empty();

  if (!tasks.length) {
    container.html("<p class='text-muted'>No tasks found</p>");
    return;
  }

  tasks.forEach((task) => {
    const isOverdue =
      task.due_date &&
      task.due_date < new Date().toISOString().split("T")[0] &&
      task.status !== "completed";

    const priorityColor =
      task.priority === "high"
        ? "danger"
        : task.priority === "medium"
          ? "warning"
          : "primary";

    container.append(`
      <div class="col-md-6 col-lg-4">
        <div class="card task-card ${isOverdue ? "border-danger" : ""}">
          <div class="card-body">

            <div class="d-flex justify-content-between mb-2">
              <span class="badge bg-${priorityColor}">
                ${task.priority.toUpperCase()}
              </span>

              <span class="badge ${
                task.status === "completed" ? "bg-success" : "bg-secondary"
              }">
                ${task.status.replace("_", " ").toUpperCase()}
              </span>
            </div>

            <h5>${task.title}</h5>

            <div class="mt-2">
              <small>
                Due: ${task.due_date ?? "—"}
                ${isOverdue ? `<span class="text-danger">(Overdue)</span>` : ""}
              </small>
            </div>

            <div class="progress mt-3" style="height:6px;">
              <div class="progress-bar bg-success"
                   style="width:${task.progress}%"></div>
            </div>
            <small>${task.progress}% completed</small>

            <div class="mt-3">
              <a href="${BASE_URL}employee/tasks/view?id=${task.id}"
                 class="btn btn-sm btn-outline-primary w-100">
                 View Task
              </a>
            </div>

          </div>
        </div>
      </div>
    `);
  });
}

function renderEmployeeTaskPagination(totalRows) {
  const pagination = $("#employeeTaskPagination");
  pagination.empty();

  const totalPages = Math.ceil(totalRows / empTaskLimit);
  if (totalPages <= 1) return;

  for (let i = 1; i <= totalPages; i++) {
    pagination.append(`
      <li class="page-item ${i === empTaskPage ? "active" : ""}">
        <a class="page-link" href="#" data-page="${i}">${i}</a>
      </li>
    `);
  }
}

$(document).on("click", "#employeeTaskPagination .page-link", function (e) {
  e.preventDefault();
  empTaskPage = parseInt($(this).data("page"));
  loadEmployeeTasks();
});

$(document).on("change", "#employeeTaskStatus", function () {
  empTaskStatus = $(this).val();
  empTaskPage = 1;
  loadEmployeeTasks();
});

$(document).on("change", "#employeeTaskPriority", function () {
  empTaskPriority = $(this).val();
  empTaskPage = 1;
  loadEmployeeTasks();
});

$(document).ready(function () {
  const deptId = $("#departmentSelect").val();

  if (deptId) {
    $("#departmentSelect").trigger("change");
  }
  //------------------------------dashboard code-----------------------------
  if ($(".counter").length) {
    animateCounters();
  }

  // ----------------------------
  // Department Doughnut
  // ----------------------------
  if ($("#departmentChart").length) {
    const labels = $("#departmentChart").data("labels");
    const counts = $("#departmentChart").data("counts");

    new Chart(document.getElementById("departmentChart"), {
      type: "doughnut",
      data: {
        labels: labels,
        datasets: [
          {
            data: counts,
            backgroundColor: [
              getComputedStyle(document.documentElement).getPropertyValue(
                "--primary-color",
              ),
              "#16a34a",
              "#ea580c",
              "#7c3aed",
              "#f59e0b",
              "#dc2626",
            ],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 800,
        },
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              boxWidth: 12,
              padding: 15,
            },
          },
        },
      },
    });
  }

  // ----------------------------
  // Task Status Bar Chart
  // ----------------------------
  if ($("#taskStatusChart").length) {
    const labels = $("#taskStatusChart").data("labels");
    const counts = $("#taskStatusChart").data("counts");

    new Chart(document.getElementById("taskStatusChart"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Tasks",
            data: counts,
            backgroundColor: "#2563eb",
            borderRadius: 8,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 800,
        },
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              boxWidth: 12,
              padding: 15,
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
            },
            grid: {
              color: "rgba(0,0,0,0.05)",
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
      },
    });
  }
  //------------------------------dashboard End-----------------------------

  if ($("#employeeTableBody").length) {
    loadEmployees();
  }

  if ($("#departmentTableBody").length) {
    loadDepartments();
  }

  if ($("#transferEmployeeTableBody").length) {
    loadTransferEmployees();
  }

  if ($("#taskListContainer").length) {
    loadTasks();
  }
  if ($("#taskHistoryContainer").length) {
    loadTaskHistory();
  }

  if ($("#employeeTaskContainer").length) {
    loadEmployeeTasks();
  }
});
