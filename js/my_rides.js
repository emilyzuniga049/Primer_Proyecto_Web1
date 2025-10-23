document.addEventListener('DOMContentLoaded', function() {
  const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));
  const tbody = document.querySelector('.rides-table tbody');
  tbody.addEventListener('click', (e) => {
    const btn = e.target.closest('.delete-btn');
    if (!btn) return; 
    e.preventDefault();

    const rideId = Number(btn.dataset.id);
    if (!Number.isFinite(rideId)) return;

    if (confirm('¿Seguro que quieres eliminar este ride?')) {
      deleteRide(rideId, currentUser.email);
    }
  });

  seeMyRides(currentUser.email);
});

function seeMyRides(userEmail) {
  const tbody = document.querySelector('.rides-table tbody');
  const rides = JSON.parse(localStorage.getItem('rides')) || [];


  const myRides = rides.filter(r => r.userEmail === userEmail);

  tbody.innerHTML = '';

  if (myRides.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" style="text-align:center; opacity:.8;">
          No tienes rides creados todavía.
        </td>
      </tr>`;
    return;
  }

  // Generar filas con los rides
  myRides.forEach(ride => {
    const from = ride.from || '';
    const to = ride.to || '';
    const seats = ride.seats ?? '';
    const fee = ride.fee === 0 || ride.fee ? `$${ride.fee}` : '--';
    const carMake = ride.vehicle?.make || '';
    const carModel = ride.vehicle?.model || '';
    const carYear = ride.vehicle?.year || '';
    const carText = [carMake, carModel, carYear].filter(Boolean).join(' ');

    const detailsHref = `../RideDetails/Index.html?id=${ride.id}`;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><a href="${detailsHref}">${from}</a></td>
      <td>${to}</td>
      <td>${seats}</td>
      <td>${carText}</td>
      <td>${fee}</td>
      <td>
        <a href="../EditRide/Index.html?id=${ride.id}">Edit</a> | 
       <a href="#" class="delete-btn" data-id="${ride.id}">Delete</a>
      </td>
    `;
    tbody.appendChild(tr);
  });
}


  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const rideId = Number(btn.dataset.id);
      deleteRide(rideId, userEmail);
    });
  });

  function deleteRide(rideId, userEmail) {

  let rides = JSON.parse(localStorage.getItem('rides')) || [];

  rides = rides.filter(r => r.id !== rideId);

  localStorage.setItem('rides', JSON.stringify(rides));

  seeMyRides(userEmail);
}

