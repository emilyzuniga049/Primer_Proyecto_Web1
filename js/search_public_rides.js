document.addEventListener('DOMContentLoaded', () => { 
  const fromSel  = document.getElementById('from');
  const toSel    = document.getElementById('to');
  const findBtn  = document.querySelector('.find-btn');
  const tbody    = document.querySelector('.rides-table tbody');
  const resultEl = document.querySelector('.search-result');

  clearTable(tbody);
  updateResultMessage(resultEl, '', '');

  // Carga inicial
  fetchRidesAndRender('', '', [], true);

  // Búsqueda con filtros
  findBtn.addEventListener('click', () => {
    const selectedFrom = (fromSel.value || '').trim();
    const selectedTo   = (toSel.value || '').trim();
    const selectedDays = getSelectedDays(); // ej: ["mon","wed"]

    fetchRidesAndRender(selectedFrom, selectedTo, selectedDays, false);
  });

  async function fetchRidesAndRender(from, to, days, isFirstLoad) {
    try {
      const params = new URLSearchParams();
      if (from) params.set('from', from);
      if (to)   params.set('to', to);
      if (days && days.length) params.set('days', days.join(','));

      // OJO: desde php/Passenger/ hacia php/DAO/ es ../DAO/
      const url = `../DAO/search_rides_db.php?${params.toString()}`;
      console.log('[rides] fetching:', url);

      const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const text = await res.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error('La respuesta no es JSON. Respuesta fue:', text);
        throw new Error('Endpoint no devolvió JSON');
      }

      if (!res.ok || !data || data.ok === false) {
        console.error('Error de backend:', data && data.error ? data.error : 'Respuesta no OK', data);
        throw new Error(data && data.error ? data.error : 'Respuesta no OK del backend');
      }

      const rides = Array.isArray(data.rides) ? data.rides : [];

      if (isFirstLoad) {
        initLocationSelects(rides, fromSel, toSel);
      }

      renderResults(rides, tbody);
      resultEl.style.display = "block";
      updateResultMessage(resultEl, from, to);
      updateMap(from, to);
    } catch (err) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7" style="text-align:center; opacity:.8;">
            Error loading rides...
          </td>
        </tr>`;
      console.error('[rides] fetch error:', err);
    }
  }
});

function initLocationSelects(rides, from, to) {
  const fromSet = new Set();
  const toSet   = new Set();

  rides.forEach(r => {
    if (r.from) fromSet.add(r.from);
    if (r.to)   toSet.add(r.to);
  });

  // Si no hay datos, no borres lo que tenga el HTML
  if (fromSet.size === 0 && toSet.size === 0) {
    return;
  }

  from.innerHTML = '';
  to.innerHTML   = '';
  from.appendChild(new Option('- Select origin -', ''));
  to.appendChild(new Option('- Select destination -', ''));

  Array.from(fromSet).sort().forEach(f => from.appendChild(new Option(f, f)));
  Array.from(toSet).sort().forEach(t => to.appendChild(new Option(t, t)));
}

function getSelectedDays() {
  const labels = document.querySelectorAll('.days-checkboxes label');
  const out = [];
  labels.forEach(l => {
    const input = l.querySelector('input[type="checkbox"]');
    if (input && input.checked) out.push(l.dataset.day?.toLowerCase() || l.textContent.trim().toLowerCase());
  });
  return out.map(d => d.substring(0,3)); // mon,tue,wed...
}

function clearTable(tbody) {
  tbody.innerHTML = `
    <tr>
      <td colspan="7" style="text-align:center; opacity:.8;">
      </td>
    </tr>`;
}

function renderResults(rows, tbody) {
  tbody.innerHTML = '';
  if (!rows.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" style="text-align:center; opacity:.8;">
          No rides found...
        </td>
      </tr>`;
    return;
  }

  rows.forEach(ride => {
    const driverEmail = ride.userEmail || 'driver';
    const from  = ride.from || '';
    const to    = ride.to   || '';
    const seats = ride.seats ?? '';
    const fee   = (ride.fee === 0 || ride.fee) ? `$${ride.fee}` : '--';
    const carMake  = ride.vehicle?.make || '';
    const carModel = ride.vehicle?.model || '';
    const carYear  = ride.vehicle?.year || '';
    const carText  = [carMake, carModel, carYear].filter(Boolean).join(' ');

    // Desde php/Passenger/ hacia /RideDetails/ (en raíz del proyecto) es ../../
    //const detailsHref = `../../RideDetails/Index.html?id=${ride.id}`;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><img src="../../Img/user_icon.png" class="small-icon" alt="User"> ${driverEmail}</td>
      <td>${from}</td>
      <td>${to}</td>
      <td>${seats}</td>
      <td>${carText}</td>
      <td>${fee}</td>
      <td><a href="../../Index.html">For Request please Login</a></td>
    `;
    tbody.appendChild(tr);
  });
}

function updateResultMessage(el, from, to) {
  if (!el) return;
  const f = from ? `<b>${from}</b>` : '<b>Any</b>';
  const t = to   ? `<b>${to}</b>`   : '<b>Any</b>';
  el.innerHTML = `Rides found from ${f} to ${t}`;
}

function updateMap(from, to) {
  const iframe = document.querySelector('.map-iframe');
  if (!iframe) return;
  if (from && to) {
    iframe.src = `https://www.google.com/maps?q=${encodeURIComponent(from + ' to ' + to)}&z=11&output=embed`;
  } else if (from || to) {
    const place = from || to;
    iframe.src = `https://www.google.com/maps?q=${encodeURIComponent(place)}&z=12&output=embed`;
  } else {
    iframe.src = `https://www.google.com/maps?q=Costa%20Rica&z=7&output=embed`;
  }
}
