/*
 * Author: Aaryan and Kissan
 * Date Created: 2026-04-14
 * Description: Frontend interactions for full-message modal behavior in admin messages page.
 */

(function () {
  var modal = document.getElementById('msg-modal');
  var closeBtn = document.getElementById('msg-modal-close');
  var title = document.getElementById('msg-modal-title');
  var from = document.getElementById('msg-modal-from');
  var email = document.getElementById('msg-modal-email');
  var date = document.getElementById('msg-modal-date');
  var body = document.getElementById('msg-modal-body');

  function closeModal() {
    if (!modal) {
      return;
    }
    modal.hidden = true;
  }

  document.querySelectorAll('.js-view-message').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!title || !from || !email || !date || !body || !modal) {
        return;
      }
      title.textContent = btn.dataset.msgSubject || 'Message';
      from.textContent = btn.dataset.msgName || '-';
      email.textContent = btn.dataset.msgEmail || '-';
      date.textContent = btn.dataset.msgDate || '-';
      body.textContent = btn.dataset.msgBody || '(No content)';
      modal.hidden = false;
    });
  });

  if (closeBtn) {
    closeBtn.addEventListener('click', closeModal);
  }

  if (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) {
        closeModal();
      }
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeModal();
    }
  });
})();
