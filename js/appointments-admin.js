/*
 * Author: Aaryan, Angad, Inderbir, Kissan
 * Date Created: 2026-04-04
 * Description: Frontend interactions for appointment management actions in admin appointments page, including declination note handling, reschedule/complete/notes toggling, and profile menu behavior.
 */

window.addEventListener('load', function () {
  const declineBackdrop = document.getElementById('decline-modal-backdrop');
  const declineCancel = document.getElementById('decline-modal-cancel');
  const declineConfirm = document.getElementById('decline-modal-confirm');
  const declineNoteInput = document.getElementById('decline-note-input');
  let pendingDeclineForm = null;

  function closeDeclineModal() {
    if (!declineBackdrop) {
      return;
    }
    pendingDeclineForm = null;
    if (declineNoteInput) {
      declineNoteInput.value = '';
    }
    declineBackdrop.classList.remove('is-open');
    declineBackdrop.setAttribute('aria-hidden', 'true');
  }

  if (declineBackdrop) {
    document.querySelectorAll('.js-decline-form').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        pendingDeclineForm = form;
        declineBackdrop.classList.add('is-open');
        declineBackdrop.setAttribute('aria-hidden', 'false');
      });
    });

    if (declineCancel) {
      declineCancel.addEventListener('click', closeDeclineModal);
    }

    declineBackdrop.addEventListener('click', function (e) {
      if (e.target === declineBackdrop) {
        closeDeclineModal();
      }
    });

    if (declineConfirm) {
      declineConfirm.addEventListener('click', function () {
        if (pendingDeclineForm) {
          const noteField = pendingDeclineForm.querySelector('input[name="notes"]');
          if (noteField && declineNoteInput) {
            noteField.value = declineNoteInput.value.trim();
          }
          pendingDeclineForm.submit();
        }
      });
    }
  }

  document.querySelectorAll('.js-toggle-reschedule').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const id = btn.getAttribute('data-target');
      const row = document.querySelector('.js-reschedule-' + id);
      if (!row) {
        return;
      }
      row.classList.toggle('subrow-hidden');
    });
  });

  document.querySelectorAll('.js-toggle-complete').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const id = btn.getAttribute('data-target');
      const row = document.querySelector('.js-complete-' + id);
      if (!row) {
        return;
      }
      row.classList.toggle('subrow-hidden');
    });
  });

  document.querySelectorAll('.js-toggle-notes').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const id = btn.getAttribute('data-target');
      const row = document.querySelector('.js-notes-' + id);
      if (!row) {
        return;
      }
      row.classList.toggle('subrow-hidden');
    });
  });
});
