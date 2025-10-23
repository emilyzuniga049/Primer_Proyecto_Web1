document.addEventListener('DOMContentLoaded', function() {
  document.querySelector('.new-ride-form')
    .addEventListener('submit', function(event) {
      event.preventDefault(); 
      storeRideInputs(); 
    });
});

function storeRideInputs() {
  const from = document.getElementById('from').value;
  const to = document.getElementById('to').value;
  const time = document.getElementById('time').value;
  const seats = document.getElementById('seats').value;
  const fee = document.getElementById('fee').value;
  const make = document.getElementById('make').value;
  const model = document.getElementById('model').value;
  const year = document.getElementById('year').value;

  // Obtener usuario actual
  const currentUser = JSON.parse(sessionStorage.getItem("currentUser"));
  if (!currentUser) {
    alert("Debes iniciar sesión para crear un ride.");
    window.location.href = "../Login/index.html";
    return;
  }

  const days = Array.from(document.querySelectorAll(".days-options input:checked"))
                    .map(day => day.parentElement.textContent.trim());

  const rideData = {
    id: Date.now(), 
    userEmail: currentUser.email,
    from: from,
    to: to,
    days: days,
    time: time,
    seats: seats,
    fee: fee,
    vehicle: {
      make: make,
      model: model,
      year: year
    }
  };

  // Guardar en localStorage
  let rides = JSON.parse(localStorage.getItem('rides'));
  if (rides) {
    rides.push(rideData);
  } else {
    rides = [rideData];
  }
  localStorage.setItem('rides', JSON.stringify(rides));
  document.querySelector('.new-ride-form').reset();
  window.location.href = "../Myrides/Index.html";
}
