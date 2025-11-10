// js/configuration.js
document.addEventListener('DOMContentLoaded', () => {
  const form       = document.querySelector('.configuration-form');
  const publicName = document.getElementById('public-name');
  const publicBio  = document.getElementById('public-bio');
  const saveBtn    = form?.querySelector('.save-btn');

  // 1) Cargar datos actuales desde la BD
  (async function loadProfile() {
    try {
      const res  = await fetch('../DAO/profile_get_bio.php', { headers: { 'Accept': 'application/json' } });
      const text = await res.text();
      let data;
      try { data = JSON.parse(text); } catch (_) { throw new Error('Respuesta no válida del servidor'); }

      if (!res.ok || !data?.ok) {
        const msg = data?.error || 'No se pudo cargar tu perfil.';
        throw new Error(msg);
      }

      publicName.value = data.first_name || '';
      publicBio.value  = data.bio ?? '';
    } catch (err) {
      console.error('[configuration] load error:', err);
      alert(err.message || 'Error cargando perfil');
    }
  })();

  // 2) Guardar cambios (solo BIO) vía BD
  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      saveBtn && (saveBtn.disabled = true);

      const payload = { bio: (publicBio.value || '').trim() };

      const res  = await fetch('../DAO/profile_update_bio.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      });
      const text = await res.text();
      let data;
      try { data = JSON.parse(text); } catch (_) { throw new Error('Respuesta no válida del servidor'); }

      if (!res.ok || !data?.ok) {
        const msg = data?.error || 'No se pudo guardar la bio.';
        throw new Error(msg);
      }

      alert('Changes saved successfully!');

    } catch (err) {
      console.error('[configuration] save error:', err);
      alert(err.message || 'Error guardando cambios');
    } finally {
      saveBtn && (saveBtn.disabled = false);
    }
  });
});
