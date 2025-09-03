@extends('layouts.admin')

@section('title', 'Usuarios')

@section('page-title', 'Gestión de Usuarios')

@section('breadcrumb')
<li class="breadcrumb-item active">Usuarios</li>
@endsection

@section('content')
<div class="row">
  <!-- Formulario -->
  <div class="col-md-12">
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title" id="form-title">
          <i class="fas fa-user-plus"></i> Crear Usuario
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <form id="usuario-form">
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
                <label for="email">
                  <i class="fas fa-envelope"></i> Email <span class="text-danger">*</span>
                </label>
                <input type="email" class="form-control" id="email" placeholder="correo@dominio.com" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="ci">
                  <i class="fas fa-id-card"></i> CI (opcional)
                </label>
                <input type="text" class="form-control" id="ci" placeholder="Documento / CI">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="password">
                  <i class="fas fa-lock"></i> Contraseña
                  <small class="text-muted" id="password-hint">(min 6 — deje vacío para no cambiar)</small>
                </label>
                <div class="input-group">
                  <input type="password" class="form-control" id="password" minlength="6" placeholder="******">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" id="toggle-password">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>
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
          <i class="fas fa-list"></i> Listado de Usuarios
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" id="btn-reload" title="Recargar">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <table id="usuarios-table" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              <th>CI</th>
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
  .card-info {
    border-top: 3px solid #17a2b8;
  }
  .nombre-text {
    color: #17a2b8;
    font-weight: 600;
  }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
  const API_BASE = `${location.origin}/api/usuarios`;
  
  // DataTable
  const table = $('#usuarios-table').DataTable({
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
        console.error('Error cargando usuarios:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar los usuarios'
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
        render: function(data) {
          return `<span class="nombre-text">${data || ''}</span>`;
        }
      },
      { data: 'email', name: 'email' },
      { data: 'ci', name: 'ci' },
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

  // Toggle password visibility
  $('#toggle-password').click(function() {
    const passwordField = $('#password');
    const icon = $(this).find('i');
    
    if (passwordField.attr('type') === 'password') {
      passwordField.attr('type', 'text');
      icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      passwordField.attr('type', 'password');
      icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });

  // Load usuarios
  function loadUsuarios() {
    table.ajax.reload();
  }

  // Create usuario
  async function createUsuario(payload) {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: JSON.stringify(payload)
    });
    
    if (response.status === 422) {
      const errors = await response.json();
      throw new Error(Object.values(errors.errors || {}).flat().join(' | ') || 'Validación fallida');
    }
    
    if (!response.ok) throw new Error('Error al crear usuario');
    return response.json();
  }

  // Update usuario
  async function updateUsuario(id, payload) {
    const response = await fetch(`${API_BASE}/${id}`, {
      method: 'PUT',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: JSON.stringify(payload)
    });
    
    if (response.status === 422) {
      const errors = await response.json();
      throw new Error(Object.values(errors.errors || {}).flat().join(' | ') || 'Validación fallida');
    }
    
    if (!response.ok) throw new Error('Error al actualizar usuario');
    return response.json();
  }

  // Delete usuario
  async function deleteUsuario(id) {
    const response = await fetch(`${API_BASE}/${id}`, { 
      method: 'DELETE', 
      headers: {'Accept': 'application/json'} 
    });
    
    if (!response.ok && response.status !== 204) throw new Error('Error al eliminar usuario');
  }

  // Reset form
  function resetForm() {
    $('#id').val('');
    $('#nombre').val('');
    $('#email').val('');
    $('#ci').val('');
    $('#password').val('');
    $('#form-title').html('<i class="fas fa-user-plus"></i> Crear Usuario');
    $('#password-hint').text('(min 6 — deje vacío para no cambiar)');
    $('#usuario-form')[0].reset();
  }

  // Fill form for editing
  function fillForm(usuario) {
    $('#id').val(usuario.id || '');
    $('#nombre').val(usuario.nombre || '');
    $('#email').val(usuario.email || '');
    $('#ci').val(usuario.ci || '');
    $('#password').val('');
    $('#form-title').html('<i class="fas fa-user-edit"></i> Editar Usuario');
    $('#password-hint').text('(deje vacío para no cambiar)');
    
    // Scroll to top
    $('html, body').animate({scrollTop: 0}, 'slow');
  }

  // Form submit
  $('#usuario-form').submit(async function(e) {
    e.preventDefault();
    
    const id = $('#id').val().trim();
    const payload = {
      nombre: $('#nombre').val().trim(),
      email: $('#email').val().trim(),
      ci: $('#ci').val().trim() || null,
    };
    
    const password = $('#password').val();
    if (password) payload.password = password;

    try {
      if (id) {
        await updateUsuario(id, payload);
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'Usuario actualizado correctamente',
          timer: 2000,
          showConfirmButton: false
        });
      } else {
        await createUsuario(payload);
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'Usuario creado correctamente',
          timer: 2000,
          showConfirmButton: false
        });
      }
      
      resetForm();
      loadUsuarios();
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
  $('#btn-reload').click(loadUsuarios);

  // Edit button
  $(document).on('click', '.btn-edit', async function() {
    const id = $(this).data('id');
    
    try {
      const response = await fetch(`${API_BASE}/${id}`, { 
        headers: {'Accept': 'application/json'} 
      });
      
      if (!response.ok) throw new Error('No se pudo cargar el usuario');
      
      const usuario = await response.json();
      fillForm(usuario);
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
          await deleteUsuario(id);
          
          Swal.fire({
            icon: 'success',
            title: '¡Eliminado!',
            text: 'Usuario eliminado correctamente',
            timer: 2000,
            showConfirmButton: false
          });
          
          loadUsuarios();
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
