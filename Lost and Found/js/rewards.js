/* ==========================================
   Campus Lost & Found Portal - Rewards JS
   Redemption confirmation, insufficient points,
   and success modal flows. Vanilla JS only.
   ========================================== */

(function() {
  'use strict';

  function setText(id, value) {
    var el = document.getElementById(id);
    if (el) el.textContent = value;
  }

  function remainingPoints(balance, cost) {
    return Math.max(0, Number(balance) - Number(cost));
  }

  // --- Redeem confirmation ---
  var pendingForm = null;

  document.querySelectorAll('.js-open-redeem').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var form = btn.closest('form.redeem-form');
      if (!form || !window.openModal) return;
      pendingForm = form;
      setText('redeemConfirmName', form.dataset.rewardName || 'this reward');
      setText('redeemConfirmCost', form.dataset.cost || '0');
      setText('redeemConfirmRemaining', remainingPoints(form.dataset.balance, form.dataset.cost));
      window.openModal('redeemConfirmModal');
    });
  });

  // --- Insufficient points ---
  document.querySelectorAll('.js-open-insufficient').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var form = btn.closest('form.redeem-form');
      if (!form || !window.openModal) return;
      setText('insufficientName', form.dataset.rewardName || 'this reward');
      setText('insufficientCost', form.dataset.cost || '0');
      setText('insufficientBalance', form.dataset.balance || '0');
      window.openModal('insufficientModal');
    });
  });

  // --- Confirm submit ---
  var confirmSubmit = document.getElementById('redeemConfirmSubmit');
  if (confirmSubmit) {
    confirmSubmit.addEventListener('click', function() {
      if (pendingForm) pendingForm.submit();
    });
  }

  // --- Success modal: populate from server-provided data attributes ---
  var successModal = document.getElementById('redeemSuccessModal');
  if (successModal) {
    if (successModal.dataset.rewardName) setText('redeemSuccessName', successModal.dataset.rewardName);
    if (successModal.dataset.balance) setText('redeemSuccessBalance', successModal.dataset.balance);
  }

  // --- Escape closes any open reward modal ---
  document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    ['redeemConfirmModal', 'insufficientModal', 'redeemSuccessModal'].forEach(function(id) {
      var m = document.getElementById(id);
      if (m && m.classList.contains('active') && window.closeModal) window.closeModal(id);
    });
  });

})();