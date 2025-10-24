document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.getElementById('photo');
  const fileName = document.getElementById('file-name');

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      fileName.textContent = fileInput.files[0].name;
    } else {
      fileName.textContent = 'No file selected';
    }
  });
  document.getElementById('register-Form')
    .addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission
    storeInputs(); // Call the function to store inputs
  });
});

function storeDriverInputs() {
  const name = document.getElementById('first-name').value;
  const lastName = document.getElementById('last-name').value;
  const idNumber = document.getElementById('id-number').value;
  const birthdate = document.getElementById('birthdate').value;
  const password = document.getElementById('password').value;
  const password2 = document.getElementById('repeat-password').value;
  const email = document.getElementById('email').value;
  const phone = document.getElementById('phone').value;
  const make = document.getElementById('vehicle-make').value;
  const model = document.getElementById('vehicle-model').value;
  const year = document.getElementById('vehicle-year').value;
  const plate = document.getElementById('license-plate').value;
  if (password == password2) {
    const userData = {
      name: name,
      lastName: lastName,
      idNumber: idNumber,
      birthdate: birthdate,
      email: email,
      phone: phone,
      make: make,
      model: model,
      year: year,
      plate: plate,
      role: 'driver',
      password: password
    };

    let users = JSON.parse(localStorage.getItem('users'));
    if (users) {
      users.push(userData);
    } else {
      users = [userData];
    }
    localStorage.setItem('users', JSON.stringify(users));
    document.getElementById('driver-register-Form').reset();
    window.location.href = '../Index.html'
    return true;
  } else {
    alert('Passwords do not match. Please try again.');
    return false;
  }
}
