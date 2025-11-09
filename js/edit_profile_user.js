document.addEventListener('DOMContentLoaded', () => {
  const fileInput = document.getElementById('photo') || document.querySelector('input[name="photo"]');
  const avatarImg = document.querySelector('.avatar');
  const allowed = ['image/jpeg', 'image/png', 'image/webp'];
  let currentObjectURL = null;

  if (!fileInput || !avatarImg) return;

  fileInput.addEventListener('change', () => {
    const f = fileInput.files && fileInput.files[0];
    if (!f) return;

    if (!allowed.includes(f.type)) {
      alert('Please select a JPG, PNG or WEBP image.');
      fileInput.value = '';
      return;
    }
    if (f.size > 2 * 1024 * 1024) { 
      alert('Image too large (max 2MB).');
      fileInput.value = '';
      return;
    }

    if (currentObjectURL) {
      URL.revokeObjectURL(currentObjectURL);
      currentObjectURL = null;
    }

    currentObjectURL = URL.createObjectURL(f);
    avatarImg.src = currentObjectURL;
  });

  window.addEventListener('beforeunload', () => {
    if (currentObjectURL) URL.revokeObjectURL(currentObjectURL);
  });
});
