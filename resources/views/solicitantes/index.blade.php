@extends('layouts.admin')

@section('title', 'Solicitantes')

@section('page-title', 'Gestión de Solicitantes')

@section('breadcrumb')
<li class="breadcrumb-item active">Solicitantes</li>
@endsection

@section('content')
<div class="row">
  <!-- Formulario -->
  <div class="col-md-12">
    <div class="card card-success">
      <div class="card-header">
        <h3 class="card-title" id="form-title">
          <i class="fas fa-user-plus"></i> Crear Solicitante
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <form id="solicitante-form">
          <input type="hidden" id="id">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="nombre">
                  <i class="fas fa-user"></i> Nombre <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="nombre" placeholder="Nombre completo" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="telefono">
                  <i class="fas fa-phone"></i> Teléfono (opcional)
                </label>
                <input type="tel" class="form-control" id="telefono" placeholder="Número de teléfono">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="direccion">
                  <i class="fas fa-map-marker-alt"></i> Dirección (opcional)
                </label>
                <textarea class="form-control" id="direccion" placeholder="Dirección completa" rows="3"></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-success" id="btn-save">
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
          <i class="fas fa-list"></i> Listado de Solicitantes
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" id="btn-reload" title="Recargar">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <table id="solicitantes-table" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Teléfono</th>
              <th>Dirección</th>
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
  .card-success {
    border-top: 3px solid #28a745;
  }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
  const API_BASE = `${location.origin}/api/solicitantes`;
  
  // DataTable
  const table = $('#solicitantes-table').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: API_BASE,
      type: 'GET',
      dataSrc: function(json) {
        // Manejar tanto arrays directos como respuestas paginadas
        if (Array.isArray(json)) {
          return json;
        } else if (json.data && Array.isArray(json.data)) {
          return json.data;
        } else {
          return [];
        }
      },
      error: function(xhr, error, thrown) {
        console.error('Error cargando solicitantes:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar los solicitantes'
        });
      }
    },
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    },
    columns: [
      { 
        data: 'nombre', 
        name: 'nombre',
        render: function(data, type, row) {
          return `<strong class="text-success">${data || ''}</strong>`;
        }
      },
      { 
        data: 'telefono', 
        name: 'telefono',
        render: function(data, type, row) {
          return data || '<span class="text-muted">N/A</span>';
        }
      },
      { 
        data: 'direccion', 
        name: 'direccion',
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
  function loadSolicitantes() {
    table.ajax.reload();
  }

  // Create solicitante
  async function createSolicitante(payload) {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: JSON.stringify(payload)
    });
    
    if (response.status === 422) {
      const errors = await response.json();
      throw new Error(Object.values(errors.errors || {}).flat().join(' | ') || 'Validación fallida');
    }
    
    if (!response.ok) throw new Error('Error al crear solicitante');
    return response.json();
  }

  // Update solicitante
  async function updateSolicitante(id, payload) {
    const response = await fetch(`${API_BASE}/${id}`, {
      method: 'PUT',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: JSON.stringify(payload)
    });
    
    if (response.status === 422) {
      const errors = await response.json();
      throw new Error(Object.values(errors.errors || {}).flat().join(' | ') || 'Validación fallida');
    }
    
    if (!response.ok) throw new Error('Error al actualizar solicitante');
    return response.json();
  }

  // Delete solicitante
  async function deleteSolicitante(id) {
    const response = await fetch(`${API_BASE}/${id}`, { 
      method: 'DELETE', 
      headers: {'Accept': 'application/json'} 
    });
    
    if (!response.ok && response.status !== 204) throw new Error('Error al eliminar solicitante');
  }

  // Reset form
  function resetForm() {
    $('#id').val('');
    $('#nombre').val('');
    $('#telefono').val('');
    $('#direccion').val('');
    $('#form-title').html('<i class="fas fa-user-plus"></i> Crear Solicitante');
    $('#solicitante-form')[0].reset();
  }

  // Fill form for editing
  function fillForm(solicitante) {
    $('#id').val(solicitante.id || '');
    $('#nombre').val(solicitante.nombre || '');
    $('#telefono').val(solicitante.telefono || '');
    $('#direccion').val(solicitante.direccion || '');
    $('#form-title').html('<i class="fas fa-user-edit"></i> Editar Solicitante');
    
    // Scroll to top
    $('html, body').animate({scrollTop: 0}, 'slow');
  }

  // Form submit
  $('#solicitante-form').submit(async function(e) {
    e.preventDefault();
    
    const id = $('#id').val().trim();
    const payload = {
      nombre: $('#nombre').val().trim(),
      telefono: $('#telefono').val().trim() || null,
      direccion: $('#direccion').val().trim() || null,
    };

    try {
      if (id) {
        await updateSolicitante(id, payload);
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'Solicitante actualizado correctamente',
          timer: 2000,
          showConfirmButton: false
        });
      } else {
        await createSolicitante(payload);
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'Solicitante creado correctamente',
          timer: 2000,
          showConfirmButton: false
        });
      }
      
      resetForm();
      loadSolicitantes();
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
  $('#btn-reload').click(loadSolicitantes);

  // Edit button
  $(document).on('click', '.btn-edit', async function() {
    const id = $(this).data('id');
    
    try {
      const response = await fetch(`${API_BASE}/${id}`, { 
        headers: {'Accept': 'application/json'} 
      });
      
      if (!response.ok) throw new Error('No se pudo cargar el solicitante');
      
      const solicitante = await response.json();
      fillForm(solicitante);
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
          await deleteSolicitante(id);
          
          Swal.fire({
            icon: 'success',
            title: '¡Eliminado!',
            text: 'Solicitante eliminado correctamente',
            timer: 2000,
            showConfirmButton: false
          });
          
          loadSolicitantes();
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
});
</script>
@endpush
