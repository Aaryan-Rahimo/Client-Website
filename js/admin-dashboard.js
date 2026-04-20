/*
 * Author: Aaryan, Kissan, Inderbir, Angad
 * Date Created: 2026-04-05
 * Description: Frontend interactions for the admin dashboard page.
 */

window.addEventListener("load", function () {
  const profileTrigger = document.getElementById("admin-profile-trigger");
  const profileMenu = document.getElementById("admin-profile-menu");
  const searchInput = document.getElementById("admin-search");
  const appointmentsTbody = document.getElementById("appointments-tbody");

  document.querySelectorAll('.js-admin-decline-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const note = window.prompt('Add a declination note (optional):', '');
      if (note === null) {
        return;
      }
      const noteField = form.querySelector('input[name="notes"]');
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
      const open = !profileMenu.classList.contains("is-open");
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
      const q = searchInput.value.trim().toLowerCase();
      const rows = appointmentsTbody.querySelectorAll("tr");
      rows.forEach(function (tr) {
        const nameCell = tr.querySelector("[data-patient-name]");
        const text = nameCell ? nameCell.textContent.trim().toLowerCase() : "";
        const hide = q !== "" && text.indexOf(q) === -1;
        tr.classList.toggle("row-hidden", hide);
      });
    });
  }
});
