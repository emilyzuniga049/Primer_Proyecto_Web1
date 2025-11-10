// js/ride_details.js
document.addEventListener('DOMContentLoaded', async () => {
  const idParam = new URLSearchParams(window.location.search).get('id');
  const rideId = Number(idParam);

  if (!rideId) {
    alert("ID de ride inválido");
    window.location.href = "search_rides.php";
    return;
  }

  try {
    // IMPORTANTE: esta ruta es RELATIVA A LA PÁGINA php/Passenger/ride_details.php
    const url = `../DAO/get_ride.php?id=${encodeURIComponent(rideId)}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const text = await res.text();

    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      console.error('La respuesta no es JSON. Respuesta fue:', text);
      throw new Error('El backend no devolvió JSON válido');
    }

    if (!res.ok || !data || data.ok === false) {
      throw new Error(data && data.error ? data.error : 'Respuesta no OK del backend');
    }

    if (!data.ride) {
      alert("Ride no encontrado");
      window.location.href = "search_rides.php";
      return;
    }

    fillRideDetails(data.ride);
  } catch (err) {
    console.error('[ride-details] error:', err);
    alert("No se pudo cargar el ride.");
    window.location.href = "search_rides.php";
  }
});

function fillRideDetails(ride) {
  const usernameEl = document.querySelector('.ride-profile .username');
  if (usernameEl) {
    usernameEl.textContent = ride.userEmail || 'Sin user';
  }

  const spans = document.querySelectorAll('.route-info label span');
  if (spans[0]) spans[0].textContent = ride.from || '';
  if (spans[1]) spans[1].textContent = ride.to || '';

  // Días (normaliza por mon/tue/wed...)
  const daysSet = new Set((ride.days || []).map(d => d.trim().toLowerCase().slice(0,3)));
  document.querySelectorAll('.days-checkboxes label').forEach(label => {
    const txt = label.textContent.trim().toLowerCase().slice(0,3);
    const input = label.querySelector('input[type="checkbox"]');
    if (input) input.checked = daysSet.has(txt);
  });

  // Hora, asientos, tarifa
  const timeInput    = document.querySelector('.ride-fields input[type="time"]');
  const numberInputs = document.querySelectorAll('.ride-fields input[type="number"]');
  const seatsInput   = numberInputs[0];
  const feeInput     = numberInputs[1];

  if (timeInput)  timeInput.value  = toTimeValue(ride.time || '');
  if (seatsInput) seatsInput.value = ride.seats ?? 1;
  if (feeInput)   feeInput.value   = ride.fee ?? 0;

  // Vehículo
  const makeEl  = document.getElementById('make');
  const modelEl = document.getElementById('model');
  const yearEl  = document.getElementById('year');

  if (makeEl)  makeEl.value  = ride.vehicle?.make  || '';
  if (modelEl) modelEl.value = ride.vehicle?.model || '';
  if (yearEl)  yearEl.value  = ride.vehicle?.year  || '';

  // Solo lectura
  document.querySelectorAll('input, select, textarea').forEach(el => {
    el.setAttribute('disabled', 'disabled');
  });
}

function toTimeValue(str) {
  if (/^\d{2}:\d{2}$/.test(str)) return str;               // HH:MM
  const hhmmss = String(str).trim().match(/^(\d{2}):(\d{2}):\d{2}$/);
  if (hhmmss) return `${hhmmss[1]}:${hhmmss[2]}`;          // HH:MM:SS -> HH:MM

  // 10am / 10:30 pm
  const m = String(str).trim().match(/^(\d{1,2}):?(\d{2})?\s*([ap]\.?m\.?)$/i);
  if (!m) return '10:00';
  let hour = parseInt(m[1], 10);
  const min = m[2] || '00';
  const mer = m[3].toLowerCase();
  if (mer.startsWith('p') && hour < 12) hour += 12;
  if (mer.startsWith('a') && hour === 12) hour = 0;
  return `${String(hour).padStart(2, '0')}:${min}`;
}
