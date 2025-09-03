@extends('layouts.admin')

@section('title', 'Donaciones')

@section('page-title', 'Gestión de Donaciones')

@section('breadcrumb')
<li class="breadcrumb-item active">Donaciones</li>
@endsection

@section('content')
<div class="row">
  <!-- Formulario -->
  <div class="col-md-12">
    <div class="card card-warning">
      <div class="card-header">
        <h3 class="card-title" id="form-title">
          <i class="fas fa-hand-holding-heart"></i> Crear Donación
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <form id="donacion-form">
          <input type="hidden" id="id">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="solicitud_id">
                  <i class="fas fa-clipboard-list"></i> Solicitud <span class="text-danger">*</span>
                </label>
                <select class="form-control select2" id="solicitud_id" required>
                  <option value="">Seleccione una solicitud</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="usuario_id">
                  <i class="fas fa-user"></i> Usuario (donante opcional)
                </label>
                <select class="form-control select2" id="usuario_id">
                  <option value="">Sin usuario específico</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="titulo">
                  <i class="fas fa-tag"></i> Título <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="titulo" placeholder="Título de la donación" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="cantidad">
                  <i class="fas fa-hashtag"></i> Cantidad (opcional)
                </label>
                <input type="number" class="form-control" id="cantidad" min="0" placeholder="0">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="estado">
                  <i class="fas fa-info-circle"></i> Estado <span class="text-danger">*</span>
                </label>
                <select class="form-control" id="estado" required>
                  <option value="ofrecida">Ofrecida</option>
                  <option value="confirmada">Confirmada</option>
                  <option value="entregada">Entregada</option>
                  <option value="cancelada">Cancelada</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="notas">
                  <i class="fas fa-sticky-note"></i> Notas (opcional)
                </label>
                <textarea class="form-control" id="notas" placeholder="Notas adicionales" rows="3"></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-warning" id="btn-save">
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
          <i class="fas fa-list"></i> Listado de Donaciones
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" id="btn-reload" title="Recargar">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <table id="donaciones-table" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Título</th>
              <th>Solicitud</th>
              <th>Usuario</th>
              <th>Cantidad</th>
              <th>Estado</th>
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
  .card-warning {
    border-top: 3px solid #ffc107;
  }
  .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
  }
  .status-ofrecida { background-color: #fff3cd; color: #856404; }
  .status-confirmada { background-color: #d1ecf1; color: #0c5460; }
  .status-entregada { background-color: #d4edda; color: #155724; }
  .status-cancelada { background-color: #f8d7da; color: #721c24; }
  .tipo-badge {
    background-color: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
  }
  .cantidad-badge {
    background-color: #f8f9fa;
    color: #6c757d;
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
  const API_BASE = `${location.origin}/api/donaciones`;
  const SOLICITUDES_API = `${location.origin}/api/solicitudes`;
  const USUARIOS_API = `${location.origin}/api/usuarios`;
  
  // Inicializar Select2
  $('.select2').select2({
    theme: 'bootstrap-5',
    width: '100%'
  });
  
  // DataTable
  const table = $('#donaciones-table').DataTable({
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
        console.error('Error cargando donaciones:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar las donaciones'
        });
      }
    },
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    },
    columns: [
      { 
        data: 'titulo', 
        name: 'titulo',
        render: function(data, type, row) {
          return `<strong class="text-warning">${data || ''}</strong>`;
        }
      },
      { 
        data: 'solicitud.tipo', 
        name: 'solicitud',
        render: function(data, type, row) {
          return data ? `<span class="tipo-badge">${data}</span>` : '<span class="text-muted">N/A</span>';
        }
      },
      { 
        data: 'usuario.nombre', 
        name: 'usuario',
        render: function(data, type, row) {
          return data || '<span class="text-muted">N/A</span>';
        }
      },
      { 
        data: 'cantidad', 
        name: 'cantidad',
        render: function(data, type, row) {
          if (data !== null && data !== undefined) {
            return `<span class="cantidad-badge">${data}</span>`;
          }
          return '<span class="text-muted">N/A</span>';
        }
      },
      { 
        data: 'estado', 
        name: 'estado',
        render: function(data, type, row) {
          const statusClass = `status-${data || 'ofrecida'}`;
          return `<span class="status-badge ${statusClass}">${data || ''}</span>`;
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

  // Load solicitudes
  async function loadSolicitudes() {
    try {
      const response = await fetch(SOLICITUDES_API, { 
        headers: { 'Accept': 'application/json' } 
      });
      
      if (!response.ok) throw new Error('Error al cargar solicitudes');
      
      const data = await response.json();
      const items = Array.isArray(data) ? data : data.data || [];
      
      const select = $('#solicitud_id');
      select.empty().append('<option value="">Seleccione una solicitud</option>');
      
      items.forEach(item => {
        const optionText = `${item.tipo} - ${item.estado} (${item.solicitante?.nombre || 'N/A'})`;
        select.append(new Option(optionText, item.id));
      });
      
      select.trigger('change');
    } catch (error) {
      console.error('Error cargando solicitudes:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudieron cargar las solicitudes'
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
      
      const select = $('#usuario_id');
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

  // Load donaciones
  function loadDonaciones() {
    table.ajax.reload();
  }

  // Create donacion
  async function createDonacion(payload) {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: JSON.stringify(payload)
    });
    
    if (response.status === 422) {
      const errors = await response.json();
      throw new Error(Object.values(errors.errors || {}).flat().join(' | ') || 'Validación fallida');
    }
    
    if (!response.ok) throw new Error('Error al crear donación');
    return response.json();
  }

  // Update donacion
  async function updateDonacion(id, payload) {
    const response = await fetch(`${API_BASE}/${id}`, {
      method: 'PUT',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: JSON.stringify(payload)
    });
    
    if (response.status === 422) {
      const errors = await response.json();
      throw new Error(Object.values(errors.errors || {}).flat().join(' | ') || 'Validación fallida');
    }
    
    if (!response.ok) throw new Error('Error al actualizar donación');
    return response.json();
  }

  // Delete donacion
  async function deleteDonacion(id) {
    const response = await fetch(`${API_BASE}/${id}`, { 
      method: 'DELETE', 
      headers: {'Accept': 'application/json'} 
    });
    
    if (!response.ok && response.status !== 204) throw new Error('Error al eliminar donación');
  }

  // Reset form
  function resetForm() {
    $('#id').val('');
    $('#solicitud_id').val('').trigger('change');
    $('#usuario_id').val('').trigger('change');
    $('#titulo').val('');
    $('#cantidad').val('');
    $('#estado').val('ofrecida');
    $('#notas').val('');
    $('#form-title').html('<i class="fas fa-hand-holding-heart"></i> Crear Donación');
    $('#donacion-form')[0].reset();
  }

  // Fill form for editing
  function fillForm(donacion) {
    $('#id').val(donacion.id || '');
    $('#solicitud_id').val(donacion.solicitud_id || '').trigger('change');
    $('#usuario_id').val(donacion.usuario_id || '').trigger('change');
    $('#titulo').val(donacion.titulo || '');
    $('#cantidad').val(donacion.cantidad || '');
    $('#estado').val(donacion.estado || 'ofrecida');
    $('#notas').val(donacion.notas || '');
    $('#form-title').html('<i class="fas fa-hand-holding-heart"></i> Editar Donación');
    
    // Scroll to top
    $('html, body').animate({scrollTop: 0}, 'slow');
  }

  // Form submit
  $('#donacion-form').submit(async function(e) {
    e.preventDefault();
    
    const id = $('#id').val().trim();
    const payload = {
      solicitud_id: $('#solicitud_id').val().trim(),
      usuario_id: $('#usuario_id').val().trim() || null,
      titulo: $('#titulo').val().trim(),
      cantidad: $('#cantidad').val().trim() ? parseInt($('#cantidad').val()) : null,
      estado: $('#estado').val(),
      notas: $('#notas').val().trim() || null,
    };

    try {
      if (id) {
        await updateDonacion(id, payload);
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'Donación actualizada correctamente',
          timer: 2000,
          showConfirmButton: false
        });
      } else {
        await createDonacion(payload);
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'Donación creada correctamente',
          timer: 2000,
          showConfirmButton: false
        });
      }
      
      resetForm();
      loadDonaciones();
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
  $('#btn-reload').click(loadDonaciones);

  // Edit button
  $(document).on('click', '.btn-edit', async function() {
    const id = $(this).data('id');
    
    try {
      const response = await fetch(`${API_BASE}/${id}`, { 
        headers: {'Accept': 'application/json'} 
      });
      
      if (!response.ok) throw new Error('No se pudo cargar la donación');
      
      const donacion = await response.json();
      fillForm(donacion);
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
          await deleteDonacion(id);
          
          Swal.fire({
            icon: 'success',
            title: '¡Eliminado!',
            text: 'Donación eliminada correctamente',
            timer: 2000,
            showConfirmButton: false
          });
          
          loadDonaciones();
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
  loadSolicitudes();
  loadUsuarios();
});
</script>
@endpush
