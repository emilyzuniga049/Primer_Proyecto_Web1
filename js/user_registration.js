document.addEventListener('DOMContentLoaded', function() {
  // Ensure the DOM is fully loaded before attaching event listeners
  document.getElementById('register-Form')
    .addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission
    storeInputs(); // Call the function to store inputs
  });
});

function storeInputs() {
  const name = document.getElementById('first-name').value;
  const lastname = document.getElementById('last-name').value;
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  const password2 = document.getElementById('repeat-password').value;
  const address = document.getElementById('address').value;
  const country = document.getElementById('country').value;
  const state = document.getElementById('state').value;
  const city = document.getElementById('city').value;
  const phone = document.getElementById('phone').value;
  if (password === password2) {
    //save to localstorage as a JSON object
    const userData = {
      name: name,
      lastname: lastname,
      address: address,
      country: country,
      state: state,
      city: city,
      phone: phone,
      email: email,
      password: password,
      rol: 'user' 
    }
    let users = JSON.parse(localStorage.getItem('users'));
    if (users) {
      users.push(userData);
    } else {
      users = [userData];
    }
    localStorage.setItem('users', JSON.stringify(users));
    alert('Registration successful!');
    document.getElementById('register-Form').reset();
    window.location.href = '../Index.html'
    return true;
  } else {
    alert('Passwords do not match. Please try again.');
    return false;
  }
}