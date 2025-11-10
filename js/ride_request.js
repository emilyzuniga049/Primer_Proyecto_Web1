// js/ride_details.js (versión simplificada)
document.addEventListener("DOMContentLoaded", () => {
  const requestBtn = document.getElementById("request-btn");
  if (!requestBtn) return;

  const params = new URLSearchParams(window.location.search);
  const rideId = parseInt(params.get("id"), 10);

  if (!rideId || rideId <= 0) {
    console.warn("Ride ID inválido en la URL");
    return;
  }

  const seatsInput = document.getElementById("seats-request");

  requestBtn.addEventListener("click", async () => {
    const seats = seatsInput ? Math.max(1, parseInt(seatsInput.value, 10) || 1) : 1;

    try {
      const res = await fetch(`../DAO/create_reservation.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ride_id: rideId, seats })
      });

      const text = await res.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error('Respuesta no es JSON:', text);
        throw new Error('El backend no devolvió JSON válido');
      }

      if (!res.ok || !data || data.ok === false) {
        throw new Error(data && data.error ? data.error : 'No se pudo crear la reservación.');
      }

      alert('Request sent successfully!');
      // Ajusta esta ruta según donde esté tu listado de rides
      window.location.href = 'search_rides.php';
    } catch (err) {
      console.error('[reservation] error:', err);
      alert(`Error: ${err.message}`);
    }
  });
});
