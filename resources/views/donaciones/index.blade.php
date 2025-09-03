<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Donaciones — CRUD simple</title>
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
    form input,form select,form textarea{padding:8px;border:1px solid #ccc;border-radius:8px;width:100%}
    form label{font-size:.9rem;margin-top:10px;display:block}
    form .row > div{flex:1}
    .btn{padding:8px 12px;border:1px solid #ccc;border-radius:10px;background:#fafafa;cursor:pointer}
    .btn.primary{background:#0d6efd;color:white;border-color:#0d6efd}
    .btn.danger{background:#dc3545;color:white;border-color:#dc3545}
    .btn.secondary{background:#6c757d;color:white;border-color:#6c757d}
    .status-ofrecida{color:#856404;background:#fff3cd;padding:2px 8px;border-radius:4px;font-size:.8rem}
    .status-confirmada{color:#155724;background:#d4edda;padding:2px 8px;border-radius:4px;font-size:.8rem}
    .status-entregada{color:#0c5460;background:#d1ecf1;padding:2px 8px;border-radius:4px;font-size:.8rem}
    .status-cancelada{color:#721c24;background:#f8d7da;padding:2px 8px;border-radius:4px;font-size:.8rem}
  </style>
</head>
<body>
  <h1>Donaciones — CRUD</h1>

  <div id="msg-ok" class="ok"></div>
  <div id="msg-error" class="error"></div>

  <div class="card">
    <h3 id="form-title">Crear donación</h3>
    <form id="donacion-form">
      <input type="hidden" id="id">
      <div class="row">
        <div>
          <label>Solicitud <span style="color:red">*</span></label>
          <select id="solicitud_id" required>
            <option value="">Seleccione una solicitud</option>
          </select>
        </div>
        <div>
          <label>Usuario (donante opcional)</label>
          <select id="usuario_id">
            <option value="">Sin usuario específico</option>
          </select>
        </div>
      </div>
      <div class="row">
        <div>
          <label>Título <span style="color:red">*</span></label>
          <input id="titulo" placeholder="Título de la donación" required>
        </div>
        <div>
          <label>Cantidad (opcional)</label>
          <input id="cantidad" type="number" min="0" placeholder="0">
        </div>
      </div>
      <div class="row">
        <div>
          <label>Estado <span style="color:red">*</span></label>
          <select id="estado" required>
            <option value="ofrecida">Ofrecida</option>
            <option value="confirmada">Confirmada</option>
            <option value="entregada">Entregada</option>
            <option value="cancelada">Cancelada</option>
          </select>
        </div>
        <div>
          <label>Notas (opcional)</label>
          <textarea id="notas" placeholder="Notas adicionales" rows="3"></textarea>
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
          <th>Título</th>
          <th>Solicitud</th>
          <th>Usuario</th>
          <th>Cantidad</th>
          <th>Estado</th>
          <th class="muted">ID</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tbody"></tbody>
    </table>
    <div class="muted" id="empty" style="display:none;margin-top:8px">No hay donaciones</div>
  </div>

  <script>
    const API_BASE = `${location.origin}/api/donaciones`;
    const SOLICITUDES_API = `${location.origin}/api/solicitudes`;
    const USUARIOS_API = `${location.origin}/api/usuarios`;
    const $ = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);

    const msgOk = $('#msg-ok');
    const msgError = $('#msg-error');

    function showOk(t){ msgOk.textContent=t; msgOk.style.display='block'; setTimeout(()=>msgOk.style.display='none', 3000); }
    function showErr(t){ msgError.textContent=t; msgError.style.display='block'; setTimeout(()=>msgError.style.display='none', 5000); }

    function getStatusClass(estado) {
      const statusMap = {
        'ofrecida': 'status-ofrecida',
        'confirmada': 'status-confirmada',
        'entregada': 'status-entregada',
        'cancelada': 'status-cancelada'
      };
      return statusMap[estado] || 'status-ofrecida';
    }

    async function loadSolicitudes() {
      try {
        const r = await fetch(SOLICITUDES_API, { headers: { 'Accept': 'application/json' }});
        if(!r.ok) throw new Error('Error al cargar solicitudes');
        const data = await r.json();
        const items = Array.isArray(data) ? data : data.data || [];
        const select = $('#solicitud_id');
        select.innerHTML = '<option value="">Seleccione una solicitud</option>';
        
        for(const s of items) {
          const option = document.createElement('option');
          option.value = s.id;
          option.textContent = `${s.tipo} - ${s.estado} (${s.id})`;
          select.appendChild(option);
        }
      } catch(e) { showErr('Error al cargar solicitudes: ' + e.message); }
    }

    async function loadUsuarios() {
      try {
        const r = await fetch(USUARIOS_API, { headers: { 'Accept': 'application/json' }});
        if(!r.ok) throw new Error('Error al cargar usuarios');
        const data = await r.json();
        const items = Array.isArray(data) ? data : data.data || [];
        const select = $('#usuario_id');
        select.innerHTML = '<option value="">Sin usuario específico</option>';
        
        for(const u of items) {
          const option = document.createElement('option');
          option.value = u.id;
          option.textContent = u.nombre;
          select.appendChild(option);
        }
      } catch(e) { showErr('Error al cargar usuarios: ' + e.message); }
    }

    async function loadDonaciones(){
      try{
        const r = await fetch(API_BASE, { headers: { 'Accept': 'application/json' }});
        if(!r.ok) throw new Error('Error al cargar');
        const data = await r.json();
        const items = Array.isArray(data) ? data : data.data || [];
        const tbody = $('#tbody');
        tbody.innerHTML = '';
        if(items.length === 0){ $('#empty').style.display='block'; return; }
        $('#empty').style.display='none';

        for(const d of items){
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${escapeHtml(d.titulo ?? '')}</td>
            <td>${escapeHtml(d.solicitud?.tipo ?? 'N/A')}</td>
            <td>${escapeHtml(d.usuario?.nombre ?? 'N/A')}</td>
            <td>${escapeHtml(d.cantidad ?? 'N/A')}</td>
            <td><span class="${getStatusClass(d.estado)}">${escapeHtml(d.estado ?? '')}</span></td>
            <td class="muted" title="${d.id}">${escapeHtml(d.id ?? '')}</td>
            <td class="actions">
              <button class="btn" data-edit="${d.id}">Editar</button>
              <button class="btn danger" data-del="${d.id}">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        }
      }catch(e){ showErr(e.message); }
    }

    function escapeHtml(s){
      return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    async function createDonacion(payload){
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

    async function updateDonacion(id, payload){
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

    async function deleteDonacion(id){
      const r = await fetch(`${API_BASE}/${id}`, { method:'DELETE', headers:{'Accept':'application/json'} });
      if(!r.ok && r.status!==204) throw new Error('Error al eliminar');
    }

    function resetForm(){
      $('#id').value='';
      $('#solicitud_id').value='';
      $('#usuario_id').value='';
      $('#titulo').value='';
      $('#cantidad').value='';
      $('#estado').value='ofrecida';
      $('#notas').value='';
      $('#form-title').textContent='Crear donación';
    }

    function fillForm(d){
      $('#id').value = d.id || '';
      $('#solicitud_id').value = d.solicitud_id || '';
      $('#usuario_id').value = d.usuario_id || '';
      $('#titulo').value = d.titulo || '';
      $('#cantidad').value = d.cantidad || '';
      $('#estado').value = d.estado || 'ofrecida';
      $('#notas').value = d.notas || '';
      $('#form-title').textContent='Editar donación';
      window.scrollTo({top:0, behavior:'smooth'});
    }

    // Handle create / update submit
    $('#donacion-form').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const id = $('#id').value.trim();
      const payload = {
        solicitud_id: $('#solicitud_id').value.trim(),
        usuario_id: $('#usuario_id').value.trim() || null,
        titulo: $('#titulo').value.trim(),
        cantidad: $('#cantidad').value.trim() ? parseInt($('#cantidad').value) : null,
        estado: $('#estado').value,
        notas: $('#notas').value.trim() || null,
      };

      try{
        if(id){
          await updateDonacion(id, payload);
          showOk('Donación actualizada');
        }else{
          await createDonacion(payload);
          showOk('Donación creada');
        }
        resetForm();
        loadDonaciones();
      }catch(err){ showErr(err.message); }
    });

    $('#btn-reset').addEventListener('click', resetForm);
    $('#btn-reload').addEventListener('click', loadDonaciones);

    // Delegate edit/delete buttons
    document.addEventListener('click', async (e)=>{
      const editId = e.target?.dataset?.edit;
      const delId  = e.target?.dataset?.del;

      if(editId){
        try{
          const r = await fetch(`${API_BASE}/${editId}`, { headers:{'Accept':'application/json'}});
          if(!r.ok) throw new Error('No se pudo cargar la donación');
          const d = await r.json();
          fillForm(d);
        }catch(err){ showErr(err.message); }
      }

      if(delId){
        if(!confirm('¿Eliminar esta donación?')) return;
        try{
          await deleteDonacion(delId);
          showOk('Donación eliminada');
          loadDonaciones();
        }catch(err){ showErr(err.message); }
      }
    });

    // Init
    loadSolicitudes();
    loadUsuarios();
    loadDonaciones();
  </script>
</body>
</html>
