<template>
  <div class="pagina-documentos">
    <div class="encabezado-pagina">
      <h1 class="titulo-pagina">Gestor Documental</h1>
      
      <button 
        v-if="puedeSubirDocumentos"
        class="boton-primario"
        @click="abrirModalCrear"
      >
        <ArrowUpOnSquareIcon class="icono-btn" />
        Subir Documento
      </button>
    </div>

    <div class="barra-acciones">
      <div class="campo-busqueda">
        <MagnifyingGlassIcon class="icono-busqueda" />
        <input 
          type="text" 
          v-model="terminoBusqueda"
          placeholder="Buscar por nombre o categoría..."
          class="input-busqueda"
        />
      </div>
    </div>

    <div class="contenedor-lista">
      <div v-if="cargando" class="estado-carga">
        <div class="spinner"></div>
        <p>Cargando documentos...</p>
      </div>
      
      <div v-else-if="documentosFiltrados.length > 0">
        <DocumentoItem 
          v-for="documento in documentosFiltrados"
          :key="documento.id"
          :documento="documento"
          @eliminar="eliminarDeLista"
          @editar="abrirModalEditar"
        />
      </div>

      <div v-else class="estado-vacio">
        No se encontraron documentos.
      </div>
    </div>
    
    <ModalSubirDocumento 
      :mostrar="mostrarModal"
      :documentoEditar="documentoSeleccionado"
      @cerrar="cerrarModal"
      @subidaExitosa="refrescarDocumentos"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ArrowUpOnSquareIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/store/auth.js'
import apiClient from '@/api/axios.js'
import DocumentoItem from '@/components/documentos/DocumentoItem.vue'
import ModalSubirDocumento from '@/components/documentos/ModalSubirDocumento.vue'

const authStore = useAuthStore()
const todosLosDocumentos = ref([])
const terminoBusqueda = ref('')
const cargando = ref(true)

// Estados del Modal
const mostrarModal = ref(false)
const documentoSeleccionado = ref(null)

const rolesPermitidos = ['admin','direccion', 'subdireccion', 'jefatura']
const puedeSubirDocumentos = computed(() => authStore.usuario && rolesPermitidos.includes(authStore.usuario.role))

const documentosFiltrados = computed(() => {
  if (!terminoBusqueda.value) return todosLosDocumentos.value
  const busqueda = terminoBusqueda.value.toLowerCase()
  return todosLosDocumentos.value.filter(doc => 
    doc.title.toLowerCase().includes(busqueda) || 
    doc.type.toLowerCase().includes(busqueda)
  )
})

const cargarDocumentos = async () => {
  cargando.value = true
  try {
    const respuesta = await apiClient.get('/documents')
    // Ajuste por si Laravel devuelve paginación
    const datosCrudos = respuesta.data.data || respuesta.data
    
    // DEFINIMOS LA BASE URL (Ajusta si tu puerto no es 80)
    // Como tus logs decían "http://localhost/api/v1", tu storage está en localhost
    const baseUrl = 'http://localhost' 

    todosLosDocumentos.value = datosCrudos.map(doc => {
      // LOG TEMPORAL: Para que veas en consola si llega 'ruta_archivo'
      console.log("Procesando doc:", doc.nombre, "Ruta:", doc.ruta_archivo)

      return {
        id: doc.id,
        title: doc.nombre,
        type: doc.categoria || 'Sin Categoría',
        
        // --- CORRECCIÓN CRÍTICA ---
        // 1. Usamos 'ruta_archivo' (nombre real de tu BD)
        // 2. Construimos la URL completa: Servidor + /storage/ + Ruta BD
        file: doc.ruta_archivo ? `${baseUrl}/storage/${doc.ruta_archivo}` : null,
        
        created_at: doc.created_at,
        user: doc.user
      }
    })

  } catch (error) {
    console.error("Error cargando documentos:", error)
  } finally {
    cargando.value = false
  }
}
// --- ACCIONES CRUD ---

const abrirModalCrear = () => {
  documentoSeleccionado.value = null // Null = Crear
  mostrarModal.value = true
}

const abrirModalEditar = (documento) => {
  documentoSeleccionado.value = documento // Objeto = Editar
  mostrarModal.value = true
}

const cerrarModal = () => {
  mostrarModal.value = false
  documentoSeleccionado.value = null
}

const refrescarDocumentos = () => {
  cargarDocumentos() // Recargar la lista completa
}

const eliminarDeLista = (id) => {
  todosLosDocumentos.value = todosLosDocumentos.value.filter(d => d.id !== id)
}

onMounted(cargarDocumentos)
</script>

<style scoped>
/* Estilos limpios y profesionales */
.pagina-documentos { max-width: 1000px; margin: 0 auto; padding-bottom: 3rem; }

.encabezado-pagina { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.titulo-pagina { font-size: 1.8rem; font-weight: 700; color: #1F2937; }

.boton-primario {
  background-color: #0B74DE; color: white; border: none; padding: 0.7rem 1.2rem;
  border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;
  cursor: pointer; transition: background 0.2s;
}
.boton-primario:hover { background-color: #1E40AF; }
.icono-btn { width: 20px; height: 20px; }

.barra-acciones { margin-bottom: 1.5rem; }
.campo-busqueda { position: relative; max-width: 400px; }
.icono-busqueda { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 20px; color: #9CA3AF; }
.input-busqueda {
  width: 100%; padding: 0.8rem 1rem 0.8rem 2.5rem; border: 1px solid #D1D5DB;
  border-radius: 8px; font-size: 0.95rem; outline: none;
}
.input-busqueda:focus { border-color: #0B74DE; box-shadow: 0 0 0 2px rgba(11, 116, 222, 0.1); }

.contenedor-lista { background: white; border: 1px solid #E5E7EB; border-radius: 12px; padding: 1.5rem; min-height: 200px; }
.estado-carga, .estado-vacio { text-align: center; padding: 3rem; color: #6B7280; }
.spinner {
  border: 3px solid #f3f3f3; border-top: 3px solid #0B74DE; border-radius: 50%;
  width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 0 auto 1rem auto;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
