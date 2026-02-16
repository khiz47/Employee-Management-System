$(document).on("change", "#departmentSelect", function () {
  const deptId = $(this).val();

  $("#employeeSelect").html("<option>Loading...</option>");

  if (!deptId) {
    $("#employeeSelect").html("");
    return;
  }

  $.ajax({
    url: BASE_URL + "includes/functions.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "fetch_task_department_employees",
      department_id: deptId,
    },
    success: function (res) {
      if (!res.status) {
        alert(res.message);
        return;
      }

      let options = "";
      res.data.forEach((emp) => {
        options += `<option value="${emp.id}">${emp.name}</option>`;
      });

      $("#employeeSelect").html(options);
    },
  });
});
