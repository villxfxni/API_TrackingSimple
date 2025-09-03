<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Solicitudes — CRUD simple</title>
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
    .status-abierta{color:#856404;background:#fff3cd;padding:2px 8px;border-radius:4px;font-size:.8rem}
    .status-en-proceso{color:#0c5460;background:#d1ecf1;padding:2px 8px;border-radius:4px;font-size:.8rem}
    .status-completada{color:#155724;background:#d4edda;padding:2px 8px;border-radius:4px;font-size:.8rem}
    .status-cancelada{color:#721c24;background:#f8d7da;padding:2px 8px;border-radius:4px;font-size:.8rem}
    .status-cerrada{color:#6c757d;background:#f8f9fa;padding:2px 8px;border-radius:4px;font-size:.8rem}
    .tipo-badge{background:#e3f2fd;color:#1976d2;padding:4px 8px;border-radius:4px;font-size:.8rem;display:inline-block}
    .json-preview{background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;padding:8px;font-family:monospace;font-size:.8rem;max-height:100px;overflow-y:auto}
  </style>
</head>
<body>
  <h1>Solicitudes — CRUD</h1>

  <div id="msg-ok" class="ok"></div>
  <div id="msg-error" class="error"></div>

  <div class="card">
    <h3 id="form-title">Crear solicitud</h3>
    <form id="solicitud-form">
      <input type="hidden" id="id">
      <div class="row">
        <div>
          <label>Solicitante <span style="color:red">*</span></label>
          <select id="solicitante_id" required>
            <option value="">Seleccione un solicitante</option>
          </select>
        </div>
        <div>
          <label>Creado por (opcional)</label>
          <select id="creado_por_usuario_id">
            <option value="">Sin usuario específico</option>
          </select>
        </div>
      </div>
      <div class="row">
        <div>
          <label>Tipo <span style="color:red">*</span></label>
          <input id="tipo" placeholder="Tipo de solicitud" required>
        </div>
        <div>
          <label>Estado <span style="color:red">*</span></label>
          <select id="estado" required>
            <option value="abierta">Abierta</option>
            <option value="en-proceso">En Proceso</option>
            <option value="completada">Completada</option>
            <option value="cancelada">Cancelada</option>
            <option value="cerrada">Cerrada</option>
          </select>
        </div>
      </div>
      <div class="row">
        <div>
          <label>Descripción (opcional)</label>
          <textarea id="descripcion" placeholder="Descripción de la solicitud" rows="3"></textarea>
        </div>
        <div>
          <label>Detalle JSON (opcional)</label>
          <textarea id="detalle" placeholder='{"campo": "valor"}' rows="3"></textarea>
          <small class="muted">Formato JSON válido</small>
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
          <th>Tipo</th>
          <th>Solicitante</th>
          <th>Estado</th>
          <th>Creado por</th>
          <th>Descripción</th>
          <th class="muted">ID</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tbody"></tbody>
    </table>
    <div class="muted" id="empty" style="display:none;margin-top:8px">No hay solicitudes</div>
  </div>

  <script>
    const API_BASE = `${location.origin}/api/solicitudes`;
    const SOLICITANTES_API = `${location.origin}/api/solicitantes`;
    const USUARIOS_API = `${location.origin}/api/usuarios`;
    const $ = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);

    const msgOk = $('#msg-ok');
    const msgError = $('#msg-error');

    function showOk(t){ msgOk.textContent=t; msgOk.style.display='block'; setTimeout(()=>msgOk.style.display='none', 3000); }
    function showErr(t){ msgError.textContent=t; msgError.style.display='block'; setTimeout(()=>msgError.style.display='none', 5000); }

    function getStatusClass(estado) {
      const statusMap = {
        'abierta': 'status-abierta',
        'en-proceso': 'status-en-proceso',
        'completada': 'status-completada',
        'cancelada': 'status-cancelada',
        'cerrada': 'status-cerrada'
      };
      return statusMap[estado] || 'status-abierta';
    }

    function formatJsonPreview(detalle) {
      if (!detalle) return 'N/A';
      try {
        if (typeof detalle === 'string') {
          detalle = JSON.parse(detalle);
        }
        return JSON.stringify(detalle, null, 2);
      } catch (e) {
        return detalle;
      }
    }

    async function loadSolicitantes() {
      try {
        const r = await fetch(SOLICITANTES_API, { headers: { 'Accept': 'application/json' }});
        if(!r.ok) throw new Error('Error al cargar solicitantes');
        const data = await r.json();
        const items = Array.isArray(data) ? data : data.data || [];
        const select = $('#solicitante_id');
        select.innerHTML = '<option value="">Seleccione un solicitante</option>';
        
        for(const s of items) {
          const option = document.createElement('option');
          option.value = s.id;
          option.textContent = s.nombre;
          select.appendChild(option);
        }
      } catch(e) { showErr('Error al cargar solicitantes: ' + e.message); }
    }

    async function loadUsuarios() {
      try {
        const r = await fetch(USUARIOS_API, { headers: { 'Accept': 'application/json' }});
        if(!r.ok) throw new Error('Error al cargar usuarios');
        const data = await r.json();
        const items = Array.isArray(data) ? data : data.data || [];
        const select = $('#creado_por_usuario_id');
        select.innerHTML = '<option value="">Sin usuario específico</option>';
        
        for(const u of items) {
          const option = document.createElement('option');
          option.value = u.id;
          option.textContent = u.nombre;
          select.appendChild(option);
        }
      } catch(e) { showErr('Error al cargar usuarios: ' + e.message); }
    }

    async function loadSolicitudes(){
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
            <td><span class="tipo-badge">${escapeHtml(s.tipo ?? '')}</span></td>
            <td><strong>${escapeHtml(s.solicitante?.nombre ?? 'N/A')}</strong></td>
            <td><span class="${getStatusClass(s.estado)}">${escapeHtml(s.estado ?? '')}</span></td>
            <td>${escapeHtml(s.creador?.nombre ?? 'N/A')}</td>
            <td>${escapeHtml(s.descripcion ?? 'N/A')}</td>
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

    async function createSolicitud(payload){
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

    async function updateSolicitud(id, payload){
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

    async function deleteSolicitud(id){
      const r = await fetch(`${API_BASE}/${id}`, { method:'DELETE', headers:{'Accept':'application/json'} });
      if(!r.ok && r.status!==204) throw new Error('Error al eliminar');
    }

    function resetForm(){
      $('#id').value='';
      $('#solicitante_id').value='';
      $('#creado_por_usuario_id').value='';
      $('#tipo').value='';
      $('#estado').value='abierta';
      $('#descripcion').value='';
      $('#detalle').value='';
      $('#form-title').textContent='Crear solicitud';
    }

    function fillForm(s){
      $('#id').value = s.id || '';
      $('#solicitante_id').value = s.solicitante_id || '';
      $('#creado_por_usuario_id').value = s.creado_por_usuario_id || '';
      $('#tipo').value = s.tipo || '';
      $('#estado').value = s.estado || 'abierta';
      $('#descripcion').value = s.descripcion || '';
      $('#detalle').value = s.detalle ? JSON.stringify(s.detalle, null, 2) : '';
      $('#form-title').textContent='Editar solicitud';
      window.scrollTo({top:0, behavior:'smooth'});
    }

    // Handle create / update submit
    $('#solicitud-form').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const id = $('#id').value.trim();
      
      let detalle = null;
      const detalleText = $('#detalle').value.trim();
      if (detalleText) {
        try {
          detalle = JSON.parse(detalleText);
        } catch (e) {
          showErr('El campo detalle debe ser un JSON válido');
          return;
        }
      }

      const payload = {
        solicitante_id: $('#solicitante_id').value.trim(),
        creado_por_usuario_id: $('#creado_por_usuario_id').value.trim() || null,
        tipo: $('#tipo').value.trim(),
        estado: $('#estado').value,
        descripcion: $('#descripcion').value.trim() || null,
        detalle: detalle,
      };

      try{
        if(id){
          await updateSolicitud(id, payload);
          showOk('Solicitud actualizada');
        }else{
          await createSolicitud(payload);
          showOk('Solicitud creada');
        }
        resetForm();
        loadSolicitudes();
      }catch(err){ showErr(err.message); }
    });

    $('#btn-reset').addEventListener('click', resetForm);
    $('#btn-reload').addEventListener('click', loadSolicitudes);

    // Delegate edit/delete buttons
    document.addEventListener('click', async (e)=>{
      const editId = e.target?.dataset?.edit;
      const delId  = e.target?.dataset?.del;

      if(editId){
        try{
          const r = await fetch(`${API_BASE}/${editId}`, { headers:{'Accept':'application/json'}});
          if(!r.ok) throw new Error('No se pudo cargar la solicitud');
          const s = await r.json();
          fillForm(s);
        }catch(err){ showErr(err.message); }
      }

      if(delId){
        if(!confirm('¿Eliminar esta solicitud?')) return;
        try{
          await deleteSolicitud(delId);
          showOk('Solicitud eliminada');
          loadSolicitudes();
        }catch(err){ showErr(err.message); }
      }
    });

    // Init
    loadSolicitantes();
    loadUsuarios();
    loadSolicitudes();
  </script>
</body>
</html>
