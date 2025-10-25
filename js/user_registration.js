document.addEventListener('DOMContentLoaded', function() {
  // Ensure the DOM is fully loaded before attaching event listeners
  const form = document.getElementById('register-form');
  const fileInput = document.getElementById('photo');
  const fileName = document.getElementById('file-name');

   if (fileInput && fileName) {
    fileInput.addEventListener('change', () => {
      fileName.textContent = fileInput.files.length > 0
        ? fileInput.files[0].name
        : 'No file selected';
    });
  }

  if (form) {
    form.addEventListener('submit', function (event) {
      const password = document.getElementById('password')?.value || '';
      const password2 = document.getElementById('password2')?.value || '';
      if (password !== password2) {
        event.preventDefault(); 
        alert('Passwords do not match. Please try again.');
      }
    });
  }
});

