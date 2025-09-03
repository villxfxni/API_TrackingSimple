@extends('layouts.admin')

@section('title', 'Solicitudes')

@section('page-title', 'Gestión de Solicitudes')

@section('breadcrumb')
<li class="breadcrumb-item active">Solicitudes</li>
@endsection

@section('content')
<div class="row">
  <!-- Formulario -->
  <div class="col-md-12">
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title" id="form-title">
          <i class="fas fa-clipboard-plus"></i> Crear Solicitud
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <form id="solicitud-form">
          <input type="hidden" id="id">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="solicitante_id">
                  <i class="fas fa-user-tie"></i> Solicitante <span class="text-danger">*</span>
                </label>
                <select class="form-control select2" id="solicitante_id" required>
                  <option value="">Seleccione un solicitante</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="creado_por_usuario_id">
                  <i class="fas fa-user"></i> Creado por (opcional)
                </label>
                <select class="form-control select2" id="creado_por_usuario_id">
                  <option value="">Sin usuario específico</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tipo">
                  <i class="fas fa-tag"></i> Tipo <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="tipo" placeholder="Tipo de solicitud" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="estado">
                  <i class="fas fa-info-circle"></i> Estado <span class="text-danger">*</span>
                </label>
                <select class="form-control" id="estado" required>
                  <option value="abierta">Abierta</option>
                  <option value="en-proceso">En Proceso</option>
                  <option value="completada">Completada</option>
                  <option value="cancelada">Cancelada</option>
                  <option value="cerrada">Cerrada</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="descripcion">
                  <i class="fas fa-align-left"></i> Descripción (opcional)
                </label>
                <textarea class="form-control" id="descripcion" placeholder="Descripción de la solicitud" rows="3"></textarea>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="detalle">
                  <i class="fas fa-code"></i> Detalle JSON (opcional)
                </label>
                <textarea class="form-control" id="detalle" placeholder='{"campo": "valor"}' rows="3"></textarea>
                <small class="text-muted">Formato JSON válido</small>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-info" id="btn-save">
                <i class="fas fa-save"></i> Guardar
              </button>
              <button type="button" class="btn btn-secondary" id="btn-reset">
                <i class="fas fa-undo"></i> Cancelar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-list"></i> Listado de Solicitudes
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" id="btn-reload" title="Recargar">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <table id="solicitudes-table" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Solicitante</th>
              <th>Estado</th>
              <th>Creado por</th>
              <th>Descripción</th>
              <th>ID</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
  .table th {
    background-color: #f4f6f9;
    border-color: #dee2e6;
  }
  .btn-group-sm > .btn, .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.2rem;
  }
  .card-info {
    border-top: 3px solid #17a2b8;
  }
  .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
  }
  .status-abierta { background-color: #fff3cd; color: #856404; }
  .status-en-proceso { background-color: #d1ecf1; color: #0c5460; }
  .status-completada { background-color: #d4edda; color: #155724; }
  .status-cancelada { background-color: #f8d7da; color: #721c24; }
  .status-cerrada { background-color: #f8f9fa; color: #6c757d; }
  .tipo-badge {
    background-color: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
  const API_BASE = `${location.origin}/api/solicitudes`;
  const SOLICITANTES_API = `${location.origin}/api/solicitantes`;
  const USUARIOS_API = `${location.origin}/api/usuarios`;
  
  // Inicializar Select2
  $('.select2').select2({
    theme: 'bootstrap-5',
    width: '100%'
  });
  
  // DataTable
  const table = $('#solicitudes-table').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: API_BASE,
      type: 'GET',
      dataSrc: function(json) {
        if (Array.isArray(json)) {
          return json;
        } else if (json.data && Array.isArray(json.data)) {
          return json.data;
        } else {
          return [];
        }
      },
      error: function(xhr, error, thrown) {
        console.error('Error cargando solicitudes:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar las solicitudes'
        });
      }
    },
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    },
    columns: [
      { 
        data: 'tipo', 
        name: 'tipo',
        render: function(data, type, row) {
          return `<span class="tipo-badge">${data || ''}</span>`;
        }
      },
      { 
        data: 'solicitante.nombre', 
        name: 'solicitante',
        render: function(data, type, row) {
          return data ? `<strong class="text-info">${data}</strong>` : '<span class="text-muted">N/A</span>';
        }
      },
      { 
        data: 'estado', 
        name: 'estado',
        render: function(data, type, row) {
          const statusClass = `status-${data || 'abierta'}`;
          return `<span class="status-badge ${statusClass}">${data || ''}</span>`;
        }
      },
      { 
        data: 'creador.nombre', 
        name: 'creador',
        render: function(data, type, row) {
          return data || '<span class="text-muted">N/A</span>';
        }
      },
      { 
        data: 'descripcion', 
        name: 'descripcion',
        render: function(data, type, row) {
          if (data) {
            return data.length > 50 ? data.substring(0, 50) + '...' : data;
          }
          return '<span class="text-muted">N/A</span>';
        }
      },
      { data: 'id', name: 'id', visible: false },
      { 
        data: null, 
        orderable: false, 
        searchable: false,
        render: function(data, type, row) {
          return `
            <div class="btn-group btn-group-sm">
              <button class="btn btn-info btn-edit" data-id="${row.id}" title="Editar">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-danger btn-delete" data-id="${row.id}" title="Eliminar">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          `;
        }
      }
    ],
    order: [[0, 'asc']],
    pageLength: 10,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
  });

  // Load solicitantes
  async function loadSolicitantes() {
    try {
      const response = await fetch(SOLICITANTES_API, { 
        headers: { 'Accept': 'application/json' } 
      });
      
      if (!response.ok) throw new Error('Error al cargar solicitantes');
      
      const data = await response.json();
      const items = Array.isArray(data) ? data : data.data || [];
      
      const select = $('#solicitante_id');
      select.empty().append('<option value="">Seleccione un solicitante</option>');
      
      items.forEach(item => {
        select.append(new Option(item.nombre, item.id));
      });
      
      select.trigger('change');
    } catch (error) {
      console.error('Error cargando solicitantes:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudieron cargar los solicitantes'
      });
    }
  }

  // Load usuarios
  async function loadUsuarios() {
    try {
      const response = await fetch(USUARIOS_API, { 
        headers: { 'Accept': 'application/json' } 
      });
      
      if (!response.ok) throw new Error('Error al cargar usuarios');
      
      const data = await response.json();
      const items = Array.isArray(data) ? data : data.data || [];
      
      const select = $('#creado_por_usuario_id');
      select.empty().append('<option value="">Sin usuario específico</option>');
      
      items.forEach(item => {
        select.append(new Option(item.nombre, item.id));
      });
      
      select.trigger('change');
    } catch (error) {
      console.error('Error cargando usuarios:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudieron cargar los usuarios'
      });
    }
  }

  // Load solicitudes
  function loadSolicitudes() {
    table.ajax.reload();
  }

  // Create solicitud
  async function createSolicitud(payload) {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: JSON.stringify(payload)
    });
    
    if (response.status === 422) {
      const errors = await response.json();
      throw new Error(Object.values(errors.errors || {}).flat().join(' | ') || 'Validación fallida');
    }
    
    if (!response.ok) throw new Error('Error al crear solicitud');
    return response.json();
  }

  // Update solicitud
  async function updateSolicitud(id, payload) {
    const response = await fetch(`${API_BASE}/${id}`, {
      method: 'PUT',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: JSON.stringify(payload)
    });
    
    if (response.status === 422) {
      const errors = await response.json();
      throw new Error(Object.values(errors.errors || {}).flat().join(' | ') || 'Validación fallida');
    }
    
    if (!response.ok) throw new Error('Error al actualizar solicitud');
    return response.json();
  }

  // Delete solicitud
  async function deleteSolicitud(id) {
    const response = await fetch(`${API_BASE}/${id}`, { 
      method: 'DELETE', 
      headers: {'Accept': 'application/json'} 
    });
    
    if (!response.ok && response.status !== 204) throw new Error('Error al eliminar solicitud');
  }

  // Reset form
  function resetForm() {
    $('#id').val('');
    $('#solicitante_id').val('').trigger('change');
    $('#creado_por_usuario_id').val('').trigger('change');
    $('#tipo').val('');
    $('#estado').val('abierta');
    $('#descripcion').val('');
    $('#detalle').val('');
    $('#form-title').html('<i class="fas fa-clipboard-plus"></i> Crear Solicitud');
    $('#solicitud-form')[0].reset();
  }

  // Fill form for editing
  function fillForm(solicitud) {
    $('#id').val(solicitud.id || '');
    $('#solicitante_id').val(solicitud.solicitante_id || '').trigger('change');
    $('#creado_por_usuario_id').val(solicitud.creado_por_usuario_id || '').trigger('change');
    $('#tipo').val(solicitud.tipo || '');
    $('#estado').val(solicitud.estado || 'abierta');
    $('#descripcion').val(solicitud.descripcion || '');
    $('#detalle').val(solicitud.detalle ? JSON.stringify(solicitud.detalle, null, 2) : '');
    $('#form-title').html('<i class="fas fa-clipboard-edit"></i> Editar Solicitud');
    
    // Scroll to top
    $('html, body').animate({scrollTop: 0}, 'slow');
  }

  // Form submit
  $('#solicitud-form').submit(async function(e) {
    e.preventDefault();
    
    const id = $('#id').val().trim();
    
    // Validar JSON del campo detalle
    let detalle = null;
    const detalleText = $('#detalle').val().trim();
    if (detalleText) {
      try {
        detalle = JSON.parse(detalleText);
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error de formato',
          text: 'El campo detalle debe ser un JSON válido'
        });
        return;
      }
    }

    const payload = {
      solicitante_id: $('#solicitante_id').val().trim(),
      creado_por_usuario_id: $('#creado_por_usuario_id').val().trim() || null,
      tipo: $('#tipo').val().trim(),
      estado: $('#estado').val(),
      descripcion: $('#descripcion').val().trim() || null,
      detalle: detalle,
    };

    try {
      if (id) {
        await updateSolicitud(id, payload);
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'Solicitud actualizada correctamente',
          timer: 2000,
          showConfirmButton: false
        });
      } else {
        await createSolicitud(payload);
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'Solicitud creada correctamente',
          timer: 2000,
          showConfirmButton: false
        });
      }
      
      resetForm();
      loadSolicitudes();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.message
      });
    }
  });

  // Reset button
  $('#btn-reset').click(resetForm);

  // Reload button
  $('#btn-reload').click(loadSolicitudes);

  // Edit button
  $(document).on('click', '.btn-edit', async function() {
    const id = $(this).data('id');
    
    try {
      const response = await fetch(`${API_BASE}/${id}`, { 
        headers: {'Accept': 'application/json'} 
      });
      
      if (!response.ok) throw new Error('No se pudo cargar la solicitud');
      
      const solicitud = await response.json();
      fillForm(solicitud);
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.message
      });
    }
  });

  // Delete button
  $(document).on('click', '.btn-delete', function() {
    const id = $(this).data('id');
    
    Swal.fire({
      title: '¿Estás seguro?',
      text: "Esta acción no se puede deshacer",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(async (result) => {
      if (result.isConfirmed) {
        try {
          await deleteSolicitud(id);
          
          Swal.fire({
            icon: 'success',
            title: '¡Eliminado!',
            text: 'Solicitud eliminada correctamente',
            timer: 2000,
            showConfirmButton: false
          });
          
          loadSolicitudes();
        } catch (error) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
          });
        }
      }
    });
  });

  // Initial load
  loadSolicitantes();
  loadUsuarios();
});
</script>
@endpush
