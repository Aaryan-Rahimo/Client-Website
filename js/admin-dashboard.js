/*
 * Author: Aaryan, Kissan, Inderbir, Angad
 * Date Created: 2026-04-05
 * Description: Frontend interactions for the admin dashboard page.
 */

(function () {
  var profileTrigger = document.getElementById("admin-profile-trigger");
  var profileMenu = document.getElementById("admin-profile-menu");
  var searchInput = document.getElementById("admin-search");
  var appointmentsTbody = document.getElementById("appointments-tbody");

  document.querySelectorAll('.js-admin-decline-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var note = window.prompt('Add a declination note (optional):', '');
      if (note === null) {
        return;
      }
      var noteField = form.querySelector('input[name="notes"]');
      if (noteField) {
        noteField.value = note.trim();
      }
      form.submit();
    });
  });

  function closeProfileMenu() {
    if (profileMenu && profileTrigger) {
      profileMenu.classList.remove("is-open");
      profileTrigger.setAttribute("aria-expanded", "false");
    }
  }

  if (profileTrigger && profileMenu) {
    profileTrigger.addEventListener("click", function (e) {
      e.stopPropagation();
      var open = !profileMenu.classList.contains("is-open");
      profileMenu.classList.toggle("is-open", open);
      profileTrigger.setAttribute("aria-expanded", open ? "true" : "false");
    });
    document.addEventListener("click", closeProfileMenu);
    profileMenu.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }

  if (searchInput && appointmentsTbody) {
    searchInput.addEventListener("input", function () {
      var q = searchInput.value.trim().toLowerCase();
      var rows = appointmentsTbody.querySelectorAll("tr");
      rows.forEach(function (tr) {
        var nameCell = tr.querySelector("[data-patient-name]");
        var text = nameCell ? nameCell.textContent.trim().toLowerCase() : "";
        var hide = q !== "" && text.indexOf(q) === -1;
        tr.classList.toggle("row-hidden", hide);
      });
    });
  }
})();
