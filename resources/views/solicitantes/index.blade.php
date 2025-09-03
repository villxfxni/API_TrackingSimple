<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Solicitantes — CRUD simple</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:20px;}
    h1{margin:0 0 16px;}
    .row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
    .card{border:1px solid #ddd;border-radius:12px;padding:16px;flex:1;min-width:280px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
    .actions button{margin-right:6px}
    .muted{color:#666;font-size:.9rem}
    .error{background:#ffe8e8;color:#b00020;padding:8px 10px;border-radius:8px;margin:8px 0;display:none}
    .ok{background:#e8fff0;color:#0a7a2a;padding:8px 10px;border-radius:8px;margin:8px 0;display:none}
    form input,form textarea{padding:8px;border:1px solid #ccc;border-radius:8px;width:100%}
    form label{font-size:.9rem;margin-top:10px;display:block}
    form .row > div{flex:1}
    .btn{padding:8px 12px;border:1px solid #ccc;border-radius:10px;background:#fafafa;cursor:pointer}
    .btn.primary{background:#0d6efd;color:white;border-color:#0d6efd}
    .btn.danger{background:#dc3545;color:white;border-color:#dc3545}
    .btn.secondary{background:#6c757d;color:white;border-color:#6c757d}
    .info-badge{background:#e3f2fd;color:#1976d2;padding:4px 8px;border-radius:4px;font-size:.8rem;display:inline-block}
  </style>
</head>
<body>
  <h1>Solicitantes — CRUD</h1>

  <div id="msg-ok" class="ok"></div>
  <div id="msg-error" class="error"></div>

  <div class="card">
    <h3 id="form-title">Crear solicitante</h3>
    <form id="solicitante-form">
      <input type="hidden" id="id">
      <div class="row">
        <div>
          <label>Nombre <span style="color:red">*</span></label>
          <input id="nombre" placeholder="Nombre completo" required>
        </div>
        <div>
          <label>Teléfono (opcional)</label>
          <input id="telefono" placeholder="Número de teléfono">
        </div>
      </div>
      <div class="row">
        <div>
          <label>Dirección (opcional)</label>
          <textarea id="direccion" placeholder="Dirección completa" rows="3"></textarea>
        </div>
      </div>
      <div class="row" style="margin-top:12px">
        <div>
          <button class="btn primary" id="btn-save" type="submit">Guardar</button>
          <button class="btn secondary" id="btn-reset" type="button">Cancelar</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="row" style="align-items:center;justify-content:space-between">
      <h3 style="margin:0">Listado</h3>
      <button class="btn" id="btn-reload">Recargar</button>
    </div>
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Teléfono</th>
          <th>Dirección</th>
          <th class="muted">ID</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tbody"></tbody>
    </table>
    <div class="muted" id="empty" style="display:none;margin-top:8px">No hay solicitantes</div>
  </div>

  <script>
    const API_BASE = `${location.origin}/api/solicitantes`;
    const $ = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);

    const msgOk = $('#msg-ok');
    const msgError = $('#msg-error');

    function showOk(t){ msgOk.textContent=t; msgOk.style.display='block'; setTimeout(()=>msgOk.style.display='none', 3000); }
    function showErr(t){ msgError.textContent=t; msgError.style.display='block'; setTimeout(()=>msgError.style.display='none', 5000); }

    async function loadSolicitantes(){
      try{
        const r = await fetch(API_BASE, { headers: { 'Accept': 'application/json' }});
        if(!r.ok) throw new Error('Error al cargar');
        const data = await r.json();
        const items = Array.isArray(data) ? data : data.data || [];
        const tbody = $('#tbody');
        tbody.innerHTML = '';
        if(items.length === 0){ $('#empty').style.display='block'; return; }
        $('#empty').style.display='none';

        for(const s of items){
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td><strong>${escapeHtml(s.nombre ?? '')}</strong></td>
            <td>${escapeHtml(s.telefono ?? 'N/A')}</td>
            <td>${escapeHtml(s.direccion ?? 'N/A')}</td>
            <td class="muted" title="${s.id}">${escapeHtml(s.id ?? '')}</td>
            <td class="actions">
              <button class="btn" data-edit="${s.id}">Editar</button>
              <button class="btn danger" data-del="${s.id}">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        }
      }catch(e){ showErr(e.message); }
    }

    function escapeHtml(s){
      return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    async function createSolicitante(payload){
      const r = await fetch(API_BASE, {
        method: 'POST',
        headers: {'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify(payload)
      });
      if(r.status===422){
        const j = await r.json();
        throw new Error(Object.values(j.errors||{}).flat().join(' | ') || 'Validación fallida');
      }
      if(!r.ok) throw new Error('Error al crear');
      return r.json();
    }

    async function updateSolicitante(id, payload){
      const r = await fetch(`${API_BASE}/${id}`, {
        method: 'PUT',
        headers: {'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify(payload)
      });
      if(r.status===422){
        const j = await r.json();
        throw new Error(Object.values(j.errors||{}).flat().join(' | ') || 'Validación fallida');
      }
      if(!r.ok) throw new Error('Error al actualizar');
      return r.json();
    }

    async function deleteSolicitante(id){
      const r = await fetch(`${API_BASE}/${id}`, { method:'DELETE', headers:{'Accept':'application/json'} });
      if(!r.ok && r.status!==204) throw new Error('Error al eliminar');
    }

    function resetForm(){
      $('#id').value='';
      $('#nombre').value='';
      $('#telefono').value='';
      $('#direccion').value='';
      $('#form-title').textContent='Crear solicitante';
    }

    function fillForm(s){
      $('#id').value = s.id || '';
      $('#nombre').value = s.nombre || '';
      $('#telefono').value = s.telefono || '';
      $('#direccion').value = s.direccion || '';
      $('#form-title').textContent='Editar solicitante';
      window.scrollTo({top:0, behavior:'smooth'});
    }

    // Handle create / update submit
    $('#solicitante-form').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const id = $('#id').value.trim();
      const payload = {
        nombre: $('#nombre').value.trim(),
        telefono: $('#telefono').value.trim() || null,
        direccion: $('#direccion').value.trim() || null,
      };

      try{
        if(id){
          await updateSolicitante(id, payload);
          showOk('Solicitante actualizado');
        }else{
          await createSolicitante(payload);
          showOk('Solicitante creado');
        }
        resetForm();
        loadSolicitantes();
      }catch(err){ showErr(err.message); }
    });

    $('#btn-reset').addEventListener('click', resetForm);
    $('#btn-reload').addEventListener('click', loadSolicitantes);

    // Delegate edit/delete buttons
    document.addEventListener('click', async (e)=>{
      const editId = e.target?.dataset?.edit;
      const delId  = e.target?.dataset?.del;

      if(editId){
        try{
          const r = await fetch(`${API_BASE}/${editId}`, { headers:{'Accept':'application/json'}});
          if(!r.ok) throw new Error('No se pudo cargar el solicitante');
          const s = await r.json();
          fillForm(s);
        }catch(err){ showErr(err.message); }
      }

      if(delId){
        if(!confirm('¿Eliminar este solicitante?')) return;
        try{
          await deleteSolicitante(delId);
          showOk('Solicitante eliminado');
          loadSolicitantes();
        }catch(err){ showErr(err.message); }
      }
    });

    // Init
    loadSolicitantes();
  </script>
</body>
</html>
