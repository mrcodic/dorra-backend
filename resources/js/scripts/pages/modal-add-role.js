// Add new role Modal JS
//------------------------------------------------------------------
(function () {
  var addRoleForm = $('#addRoleForm');

  // add role form validation
  if (addRoleForm.length) {
    addRoleForm.validate({
      rules: {
        modalRoleName: {
          required: true
        }
      }
    });
  }

  // reset form on modal hidden
  $('.modal').on('hidden.bs.modal', function () {
    $(this).find('form')[0].reset();
  });

  // Select All checkbox click
    const selectAllGlobal = document.getElementById('selectAllGlobal');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');

    // Global "Select All"
    selectAllGlobal.addEventListener('change', function () {
      const checked = this.checked;
      rowCheckboxes.forEach(row => row.checked = checked);
      permissionCheckboxes.forEach(cb => cb.checked = checked);
    });

    // Row-level select all
    rowCheckboxes.forEach(rowCb => {
      rowCb.addEventListener('change', function () {
        const prefix = this.getAttribute('data-row');
        const checkboxes = document.querySelectorAll(`.${prefix}-checkbox`);
        checkboxes.forEach(cb => cb.checked = this.checked);
      });
    });
})();
