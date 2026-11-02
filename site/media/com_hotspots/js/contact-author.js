/**
 * Hotspots: show and hide the contact-the-author form on the marker page.
 */
document.addEventListener('DOMContentLoaded', () => {
  const box = document.getElementById('hs-contact-author');

  if (!box) {
    return;
  }

  const open = () => {
    box.classList.remove('d-none');
    box.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  document.querySelectorAll('.hs-btn-contact-author').forEach((button) => {
    button.addEventListener('click', () => (box.classList.contains('d-none') ? open() : box.classList.add('d-none')));
  });

  document.querySelectorAll('.hs-btn-contact-author-cancel').forEach((button) => {
    button.addEventListener('click', () => box.classList.add('d-none'));
  });

  if (window.location.hash.indexOf('hs-contact') !== -1) {
    open();
  }
});
