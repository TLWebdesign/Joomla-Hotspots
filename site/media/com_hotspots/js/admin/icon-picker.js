/**
 * Hotspots icon pickers: category icon (upload or sample) and marker icon (category icon or sample).
 * Markup comes from CategorymarkericonField and MarkerimageField.
 */
(() => {
  'use strict';

  const root = () => (Joomla.getOptions('system.paths') || {}).root || '';

  const setPreview = (picker, src) => {
    const preview = picker.querySelector('.hs-icon-preview');
    preview.innerHTML = '';

    if (src) {
      const img = document.createElement('img');
      img.src = src;
      img.alt = '';
      img.style.maxHeight = '48px';
      preview.appendChild(img);
    }
  };

  const uploadGroup = (uploadId) => {
    const upload = uploadId ? document.getElementById(uploadId) : null;

    return upload ? upload.closest('.control-group') : null;
  };

  const init = (picker) => {
    const input = document.getElementById(picker.dataset.input);
    const source = picker.querySelector('.hs-icon-source');
    const samples = picker.querySelector('.hs-sample-icons');
    const isCategory = picker.dataset.mode === 'category';
    const group = isCategory ? uploadGroup(picker.dataset.upload) : null;

    const update = () => {
      samples.classList.toggle('d-none', source.value !== 'sample');

      if (group) {
        group.classList.toggle('d-none', source.value !== 'new');
      }

      if (!isCategory && source.value === '0') {
        input.value = '';
        setPreview(picker, '');
      }
    };

    samples.addEventListener('click', (event) => {
      const button = event.target.closest('.hs-sample-icon');

      if (!button) {
        return;
      }

      const icon = button.dataset.icon;
      input.value = isCategory ? `[sample]/sample/${icon}` : `/sample/${icon}`;
      setPreview(picker, button.querySelector('img').src);
    });

    if (group) {
      const upload = document.getElementById(picker.dataset.upload);

      upload.addEventListener('change', () => {
        // The J5 media field value is "images/x.png#joomlaImage://...": keep the path only.
        const path = (upload.value || '').split('#')[0];

        if (path) {
          input.value = `[upload]${path}`;
          setPreview(picker, `${root()}/${path}`);
        }
      });
    }

    source.addEventListener('change', update);
    update();
  };

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.hs-icon-picker').forEach(init);
  });
})();
