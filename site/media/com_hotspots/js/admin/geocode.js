/**
 * Geocode the selected markers (Markers list, toolbar Actions > Geocode).
 * Markers without coordinates get them from their address, markers with coordinates get their address.
 */
(() => {
  'use strict';

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.hs-geocode-start');

    if (!button) {
      return;
    }

    const form = document.getElementById('adminForm');
    const output = button.closest('joomla-dialog, .p-3, body').querySelector('.hs-geocode-output')
      || document.querySelector('.hs-geocode-output');
    const data = new FormData();

    form.querySelectorAll('input[name="cid[]"]:checked').forEach((box) => data.append('cid[]', box.value));
    data.append(Joomla.getOptions('csrf.token'), '1');

    button.disabled = true;

    Joomla.request({
      url: 'index.php?option=com_hotspots&task=hotspots.geocode',
      method: 'POST',
      data,
      onSuccess: (text) => {
        let response;

        try {
          response = JSON.parse(text);
        } catch (e) {
          response = { message: text };
        }

        output.textContent = response.message || '';

        if (!response.success) {
          button.disabled = false;

          return;
        }

        setTimeout(() => window.location.reload(), 2500);
      },
      onError: (xhr) => {
        output.textContent = xhr.statusText || 'Error';
        button.disabled = false;
      },
    });
  });
})();
