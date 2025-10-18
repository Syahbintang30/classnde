// Payment Method Selection Logic

document.addEventListener('DOMContentLoaded', function() {
  const grid = document.querySelector('.payment-grid-container');
  const detailsDisplay = document.getElementById('payment-details-display');

  if (!grid) return;

  grid.addEventListener('change', function(e) {
    if (e.target && e.target.matches('input[type="radio"][name="payment_method"]')) {
      // Remove .selected from all
      grid.querySelectorAll('.payment-option').forEach(label => label.classList.remove('selected'));
      // Add .selected to current
      e.target.closest('.payment-option').classList.add('selected');

      const name = e.target.dataset.name;
      const details = e.target.dataset.details;
      const value = e.target.value.toLowerCase();

      // Normalize certain bank/VA labels for a cleaner UI: e.g., 'BCA' -> 'Transfer bank'
      const lname = (name || '').toLowerCase();
      const isBank = /\b(bca|bank|va|virtual|mandiri|bni|bri)\b/i.test(lname);
      const displayName = isBank ? 'Transfer bank' : name;

      // All payment methods are handled via Midtrans Snap in the new flow.
      // Show a neutral message directing users to complete payment via Midtrans.
      detailsDisplay.innerHTML = `<h3>${displayName}</h3><p>Payment will be processed securely via Midtrans. No manual bank or e-wallet transfer is required on this page.</p>`;
    }
  });
});
