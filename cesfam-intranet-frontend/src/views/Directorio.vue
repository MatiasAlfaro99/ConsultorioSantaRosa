<template>
  <div class="pagina-directorio">
    <div class="encabezado-seccion">
      <div>
        <h1 class="titulo-pagina">Gestión de Usuarios</h1>
        <p class="subtitulo-pagina">Administra los funcionarios y sus roles.</p>
      </div>
      
      <button v-if="authStore.puedeAdministrarDirectorio" class="boton-primario" @click="abrirCrear">
        <PlusIcon class="icono-btn" />
        Agregar Nuevo
      </button>
    </div>

    <div class="barra-herramientas">
      <div class="campo-busqueda">
        <MagnifyingGlassIcon class="icono-lupa" />
        <input 
          type="text" 
          v-model="busqueda"
          placeholder="Buscar por nombre, cargo o RUT..."
          class="input-buscador"
        />
      </div>
      
      <div class="filtros">
        <span class="texto-conteo">{{ funcionariosFiltrados.length }} registros</span>
      </div>
    </div>

    <div class="contenedor-tabla">
      <div v-if="cargando" class="estado-carga">
        <div class="spinner"></div>
        <p>Cargando datos...</p>
      </div>

      <table v-else class="tabla-usuarios">
        <thead>
          <tr>
            <th class="col-usuario">Funcionario</th>
            <th>RUT</th>
            <th>Cargo</th>
            <th>Rol Sistema</th>
            <th>Estado</th>
            <th v-if="authStore.puedeAdministrarDirectorio" class="col-acciones"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="usuario in funcionariosFiltrados" :key="usuario.id">
            
            <td>
              <div class="perfil-celda">
                <img 
                  :src="usuario.profile_photo_url" 
                  :alt="usuario.name"
                  class="avatar-img"
                />
                <div class="info-personal">
                  <span class="nombre-texto">{{ usuario.name }}</span>
                  <span class="email-texto">{{ usuario.email }}</span>
                </div>
              </div>
            </td>

            <td class="texto-secundario">{{ usuario.rut || '-' }}</td>
            
            <td class="texto-principal">{{ usuario.cargo || 'Sin asignar' }}</td>

            <td>
              <span :class="['badge-rol', obtenerClaseRol(usuario.role)]">
                {{ usuario.role ? usuario.role.toUpperCase() : 'SIN ROL' }}
              </span>
            </td>

            <td>
              <button 
                class="btn-estado" 
                :class="usuario.is_active ? 'activo' : 'inactivo'"
                @click="cambiarEstado(usuario)"
                :title="usuario.is_active ? 'Click para desactivar' : 'Click para activar'"
                :disabled="!authStore.puedeAdministrarDirectorio || usuario.id === authStore.usuario.id"
              >
                {{ usuario.is_active ? 'Activo' : 'Inactivo' }}
              </button>
            </td>

            <td v-if="authStore.puedeAdministrarDirectorio">
              <div class="acciones-fila">
                <button class="btn-icon editar" title="Editar" @click="abrirEditar(usuario)">
                  <PencilSquareIcon />
                </button>
                <button class="btn-icon eliminar" title="Eliminar" @click="eliminarUsuario(usuario.id)">
                  <TrashIcon />
                </button>
              </div>
            </td>
          </tr>
          
          <tr v-if="funcionariosFiltrados.length === 0">
            <td colspan="6" class="fila-vacia">
              No se encontraron resultados.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="paginacion-footer">
      <span class="texto-paginacion">Mostrando {{ funcionariosFiltrados.length }} resultados</span>
      <div class="botones-paginacion">
        <button class="btn-pag" disabled>Anterior</button>
        <button class="btn-pag activo">1</button>
        <button class="btn-pag" disabled>Siguiente</button>
      </div>
    </div>

    <ModalUsuario 
      :mostrar="mostrarModal" 
      :usuarioEditar="usuarioSeleccionado"
      @cerrar="mostrarModal = false"
      @guardado="recargarDatos"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { MagnifyingGlassIcon, PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'
import apiClient from '@/api/axios.js'
import { useAuthStore } from '@/store/auth.js'
import ModalUsuario from '@/components/directorio/ModalUsuario.vue'

const authStore = useAuthStore()

const funcionarios = ref([])
const cargando = ref(true)
const busqueda = ref('')

// Variables para el Modal
const mostrarModal = ref(false)
const usuarioSeleccionado = ref(null)

// --- LÓGICA DE DATOS ---

const cargarDirectorio = async () => {
  cargando.value = true
  try {
    // IMPORTANTE: Ruta en inglés '/users'
    const respuesta = await apiClient.get('/users')
    const datos = respuesta.data
    
    if (Array.isArray(datos)) {
        funcionarios.value = datos
    } else if (datos && Array.isArray(datos.data)) {
        funcionarios.value = datos.data
    } else {
        funcionarios.value = []
    }
  } catch (error) {
    console.error("Error cargando usuarios:", error)
    alert("Error al cargar la lista de usuarios.")
  } finally {
    cargando.value = false
  }
}

const recargarDatos = () => {
  cargarDirectorio()
}

// --- LÓGICA DE ACCIONES ---

const abrirCrear = () => {
  usuarioSeleccionado.value = null
  mostrarModal.value = true
}

const abrirEditar = (usuario) => {
  usuarioSeleccionado.value = { ...usuario }
  mostrarModal.value = true
}

const eliminarUsuario = async (id) => {
  if (!confirm('¿Estás seguro de eliminar a este funcionario? Si tiene documentos asociados, no podrás borrarlo.')) return

  try {
    await apiClient.delete(`/users/${id}`)
    funcionarios.value = funcionarios.value.filter(u => u.id !== id)
    alert('Funcionario eliminado correctamente.')
  } catch (error) {
    console.error(error)
    // Mostramos el mensaje específico del backend (ej: "No se puede eliminar porque tiene documentos...")
    const mensaje = error.response?.data?.message || 'Error al eliminar. Verifica tus permisos.'
    alert(mensaje)
  }
}

// NUEVA FUNCIÓN: Cambiar Estado (Activo/Inactivo)
const cambiarEstado = async (usuario) => {
  const nuevoEstado = usuario.is_active ? 'desactivar' : 'activar';
  if (!confirm(`¿Deseas ${nuevoEstado} el acceso de ${usuario.name}?`)) return;

  try {
    const response = await apiClient.patch(`/users/${usuario.id}/toggle-status`);
    
    // Actualizamos localmente para ver el cambio de color inmediato
    const index = funcionarios.value.findIndex(u => u.id === usuario.id);
    if (index !== -1) {
      funcionarios.value[index].is_active = response.data.data.is_active;
    }
  } catch (error) {
    console.error(error);
    alert(error.response?.data?.message || 'Error al cambiar estado.');
  }
}

// --- FILTROS Y UTILIDADES ---

const funcionariosFiltrados = computed(() => {
  if (!busqueda.value) return funcionarios.value
  const termino = busqueda.value.toLowerCase()
  
  return funcionarios.value.filter(user => 
    user.name.toLowerCase().includes(termino) ||
    (user.email && user.email.toLowerCase().includes(termino)) ||
    (user.rut && user.rut.toLowerCase().includes(termino)) ||
    (user.cargo && user.cargo.toLowerCase().includes(termino))
  )
})

const obtenerClaseRol = (rol) => {
  const mapa = {
    'admin': 'rol-admin', // Aseguramos que admin tenga color rojo
    'direccion': 'rol-admin',
    'jefatura': 'rol-jefe',
    'subdireccion': 'rol-sub',
    'funcionario': 'rol-user'
  }
  return mapa[rol] || 'rol-user'
}

// Inicializar
onMounted(cargarDirectorio)
</script>

<style scoped>
.pagina-directorio {
  max-width: 1200px;
  margin: 0 auto;
  padding-bottom: 2rem;
}

/* Encabezado */
.encabezado-seccion {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}
.titulo-pagina { font-size: 1.5rem; font-weight: 700; color: var(--color-texto-principal); }
.subtitulo-pagina { color: var(--color-texto-secundario); font-size: 0.9rem; }

.boton-primario {
  background-color: var(--color-azul-institucional);
  color: white;
  border: none;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: background 0.2s;
}
.boton-primario:hover { background-color: #1E40AF; }
.icono-btn { width: 20px; height: 20px; }

/* Barra Herramientas */
.barra-herramientas {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  background: white;
  padding: 1rem;
  border-radius: 12px;
  border: 1px solid var(--color-borde);
}

.campo-busqueda { position: relative; width: 300px; }
.icono-lupa { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 18px; color: #9CA3AF; }
.input-buscador {
  width: 100%;
  padding: 0.5rem 1rem 0.5rem 2.5rem;
  border: 1px solid var(--color-borde);
  border-radius: 6px;
  font-size: 0.9rem;
}
.input-buscador:focus { outline: 2px solid var(--color-azul-institucional); border-color: transparent; }
.texto-conteo { font-size: 0.85rem; color: var(--color-texto-secundario); }

/* TABLA */
.contenedor-tabla {
  background: white;
  border-radius: 12px;
  border: 1px solid var(--color-borde);
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.tabla-usuarios { width: 100%; border-collapse: collapse; text-align: left; }

.tabla-usuarios th {
  background-color: #F9FAFB;
  padding: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-texto-secundario);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid var(--color-borde);
}

.tabla-usuarios td {
  padding: 1rem;
  border-bottom: 1px solid var(--color-borde);
  vertical-align: middle;
}
.tabla-usuarios tr:last-child td { border-bottom: none; }
.tabla-usuarios tr:hover { background-color: #F9FAFB; }

/* ESTILO AVATAR */
.perfil-celda { display: flex; align-items: center; gap: 0.75rem; }
.avatar-img {
  width: 40px; height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid var(--color-borde);
  background-color: #F3F4F6;
}

.info-personal { display: flex; flex-direction: column; }
.nombre-texto { font-weight: 600; color: var(--color-texto-principal); font-size: 0.9rem; }
.email-texto { font-size: 0.8rem; color: var(--color-texto-secundario); }

.texto-principal { font-size: 0.9rem; color: var(--color-texto-principal); }
.texto-secundario { font-size: 0.85rem; color: var(--color-texto-secundario); font-family: monospace; }

/* BADGES DE ROL */
.badge-rol {
  padding: 2px 8px; border-radius: 12px;
  font-size: 0.75rem; font-weight: 600;
  text-transform: uppercase;
  display: inline-block;
}
.rol-admin { background-color: #FEE2E2; color: #B91C1C; } 
.rol-jefe { background-color: #FEF3C7; color: #B45309; } 
.rol-sub { background-color: #E0E7FF; color: #4338CA; } 
.rol-user { background-color: #F3F4F6; color: #4B5563; } 


/* ESTILOS BOTÓN ESTADO (Activo/Inactivo) */
.btn-estado {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-estado:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-estado.activo { background-color: #ECFDF5; color: #10B981; }
.btn-estado.activo:hover { background-color: #D1FAE5; }

.btn-estado.inactivo { background-color: #FEF2F2; color: #EF4444; }
.btn-estado.inactivo:hover { background-color: #FEE2E2; }


/* Acciones */
.acciones-fila { display: flex; gap: 0.5rem; justify-content: flex-end; }
.btn-icon { background: transparent; border: none; cursor: pointer; padding: 6px; border-radius: 6px; color: var(--color-texto-secundario); transition: all 0.2s; }
.btn-icon:hover { background-color: #F3F4F6; }
.btn-icon svg { width: 18px; height: 18px; }
.editar:hover { color: var(--color-azul-institucional); background-color: #EFF6FF; }
.eliminar:hover { color: #EF4444; background-color: #FEE2E2; }

/* Paginación */
.paginacion-footer {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 0;
}
.texto-paginacion { font-size: 0.85rem; color: var(--color-texto-secundario); }
.botones-paginacion { display: flex; gap: 0.25rem; }
.btn-pag { padding: 0.4rem 0.8rem; border: 1px solid var(--color-borde); background: white; border-radius: 6px; font-size: 0.85rem; cursor: pointer; }
.btn-pag.activo { background-color: var(--color-azul-institucional); color: white; border-color: var(--color-azul-institucional); }
.btn-pag:disabled { opacity: 0.5; cursor: not-allowed; }

.fila-vacia { text-align: center; padding: 3rem; color: var(--color-texto-secundario); }
.estado-carga { text-align: center; padding: 3rem; color: var(--color-texto-secundario); }
.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid var(--color-azul-institucional);
  border-radius: 50%;
  width: 30px;
  height: 30px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem auto;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>