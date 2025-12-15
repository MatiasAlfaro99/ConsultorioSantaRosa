<template>
  <div class="pagina-comunicados">
    <div class="encabezado-seccion">
      <div>
        <h1 class="titulo-pagina">Comunicados Oficiales</h1>
        <p class="subtitulo-pagina">Noticias y anuncios importantes del CESFAM.</p>
      </div>
      
      <button v-if="esAdmin" class="boton-primario" @click="abrirModalCrear">
        <PlusIcon class="icono-btn" />
        Nuevo Comunicado
      </button>
    </div>

    <div v-if="cargando" class="estado-carga">
      <div class="spinner"></div>
      <p>Cargando noticias...</p>
    </div>

    <div v-else class="contenedor-comunicados">
      <div v-if="comunicados.length === 0" class="estado-vacio">
        <MegaphoneIcon class="icono-vacio" />
        <p>No hay comunicados publicados aún.</p>
      </div>

      <div v-else class="grid-noticias">
        <div v-for="item in comunicados" :key="item.id" class="tarjeta-comunicado">
          
          <div class="card-header">
            <div class="header-left">
                <div class="icono-wrapper">
                  <MegaphoneIcon class="icono-card" />
                </div>
                <div class="meta-info">
                  <span class="autor">{{ item.user_name || 'Dirección CESFAM' }}</span>
                  <span class="fecha">{{ formatearFecha(item.created_at) }}</span>
                  <span v-if="item.evento" class="badge-evento">
                    📅 Agendado: {{ formatearFechaEvento(item.evento.fecha_inicio) }}
                  </span>
                </div>
            </div>
            
            <div v-if="esAdmin" class="acciones-card">
                <button class="btn-icon editar" @click="abrirModalEditar(item)" title="Editar">
                    <PencilIcon class="icono-sm" />
                </button>
                <button class="btn-icon eliminar" @click="confirmarEliminar(item.id)" title="Eliminar">
                    <TrashIcon class="icono-sm" />
                </button>
            </div>
          </div>
          
          <div class="card-body">
            <h3 class="titulo-comunicado">{{ item.titulo }}</h3>
            <p class="texto-comunicado">{{ item.contenido }}</p>
          </div>

        </div>
      </div>
    </div>

    <div v-if="mostrarModal" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>{{ modoEdicion ? 'Editar Comunicado' : 'Nuevo Comunicado' }}</h2>
                <button class="btn-cerrar" @click="cerrarModal">
                    <XMarkIcon class="icono-sm" />
                </button>
            </div>
            
            <form @submit.prevent="guardarComunicado" class="formulario-modal">
                <div class="campo">
                    <label>Título</label>
                    <input 
                        v-model="formulario.titulo" 
                        type="text" 
                        placeholder="Ej: Campaña de Vacunación..." 
                        required 
                        class="input-form"
                    >
                </div>
                
                <div class="campo">
                    <label>Contenido</label>
                    <textarea 
                        v-model="formulario.contenido" 
                        rows="6" 
                        placeholder="Escribe el detalle del comunicado..." 
                        required
                        class="input-form"
                    ></textarea>
                </div>

                <div v-if="!modoEdicion" class="integracion-calendario">
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="formulario.agendar_evento">
                        <span>📅 Publicar también en el Calendario</span>
                    </label>

                    <div v-if="formulario.agendar_evento" class="fila-fechas animated-fade">
                        <div class="campo">
                            <label>Fecha</label>
                            <input type="date" v-model="formulario.fecha_evento" required class="input-form">
                        </div>
                        <div class="campo">
                            <label>Hora</label>
                            <input type="time" v-model="formulario.hora_evento" required class="input-form">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancelar" @click="cerrarModal">Cancelar</button>
                    <button type="submit" class="boton-primario" :disabled="guardando">
                        {{ guardando ? 'Guardando...' : (modoEdicion ? 'Actualizar' : 'Publicar') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import { PlusIcon, MegaphoneIcon, PencilIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import apiClient from '@/api/axios.js'
import { useAuthStore } from '@/store/auth.js'

const authStore = useAuthStore()
const comunicados = ref([])
const cargando = ref(true)
const guardando = ref(false)

// Estado del Modal
const mostrarModal = ref(false)
const modoEdicion = ref(false)
const idEdicion = ref(null)

// Formulario reactivo
const formulario = reactive({
    titulo: '',
    contenido: '',
    // Campos para la integración con calendario
    agendar_evento: false,
    fecha_evento: '',
    hora_evento: '09:00'
})

// Permisos
const esAdmin = computed(() => {
  const rol = authStore.usuario?.role
  return ['direccion', 'jefatura', 'admin'].includes(rol)
})

// --- Cargar Datos ---
const cargarComunicados = async () => {
  // Ponemos cargando en true para dar feedback visual de refresco
  if (comunicados.value.length === 0) cargando.value = true
  
  try {
    const respuesta = await apiClient.get('/comunicados')
    // Ajuste seguro: Laravel a veces envuelve en 'data' y otras no
    comunicados.value = Array.isArray(respuesta.data) ? respuesta.data : (respuesta.data.data || [])
  } catch (error) {
    console.error("Error cargando comunicados:", error)
  } finally {
    cargando.value = false
  }
}

// --- Lógica del Modal ---
const abrirModalCrear = () => {
    modoEdicion.value = false
    
    // Resetear formulario completo
    formulario.titulo = ''
    formulario.contenido = ''
    
    // Valores por defecto para calendario
    formulario.agendar_evento = false
    const hoy = new Date()
    formulario.fecha_evento = hoy.toISOString().split('T')[0] // YYYY-MM-DD
    formulario.hora_evento = hoy.toTimeString().slice(0, 5) // HH:MM
    
    idEdicion.value = null
    mostrarModal.value = true
}

const abrirModalEditar = (item) => {
    modoEdicion.value = true
    formulario.titulo = item.titulo
    formulario.contenido = item.contenido
    // En edición no mostramos calendario para no complicar la lógica
    idEdicion.value = item.id
    mostrarModal.value = true
}

const cerrarModal = () => {
    mostrarModal.value = false
}

// --- CRUD ---
const guardarComunicado = async () => {
    guardando.value = true
    try {
        if (modoEdicion.value) {
            // Editar (PUT)
            await apiClient.put(`/comunicados/${idEdicion.value}`, {
                titulo: formulario.titulo,
                contenido: formulario.contenido
            })
        } else {
            // Crear (POST) - Enviamos todo el objeto formulario
            // El backend decidirá si crea el evento basándose en 'agendar_evento'
            await apiClient.post('/comunicados', formulario)
        }
        
        cerrarModal()
        await cargarComunicados() // Recargar lista para ver el nuevo ítem
        alert("Publicado correctamente")

    } catch (error) {
        console.error("Error al guardar:", error)
        alert("Hubo un error al guardar el comunicado. Revisa que los campos sean válidos.")
    } finally {
        guardando.value = false
    }
}

const confirmarEliminar = async (id) => {
    if(!confirm("¿Estás seguro de eliminar este comunicado?")) return;

    try {
        await apiClient.delete(`/comunicados/${id}`)
        // Eliminar localmente para feedback instantáneo
        comunicados.value = comunicados.value.filter(c => c.id !== id)
    } catch (error) {
        console.error("Error al eliminar:", error)
        alert("No se pudo eliminar el comunicado.")
    }
}

// --- Utilidades ---
const formatearFecha = (fechaISO) => {
  if (!fechaISO) return ''
  const fecha = new Date(fechaISO)
  return fecha.toLocaleDateString('es-CL', { 
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
  })
}

const formatearFechaEvento = (fechaISO) => {
  if (!fechaISO) return ''
  const fecha = new Date(fechaISO)
  const dia = fecha.getDate()
  const mes = fecha.toLocaleDateString('es-CL', { month: 'short' })
  const hora = fecha.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })
  return `${dia} ${mes} - ${hora}`
}

onMounted(cargarComunicados)
</script>

<style scoped>
.pagina-comunicados {
  max-width: 1000px;
  margin: 0 auto;
  padding-bottom: 3rem;
}

/* Encabezado */
.encabezado-seccion {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.titulo-pagina { font-size: 1.8rem; font-weight: 700; color: #1F2937; margin-bottom: 0.25rem; }
.subtitulo-pagina { color: #6B7280; font-size: 1rem; }

.boton-primario {
  background-color: var(--color-azul-institucional, #0B74DE);
  color: white;
  border: none;
  padding: 0.7rem 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: background 0.2s;
}
.boton-primario:hover { background-color: #1E40AF; }
.boton-primario:disabled { background-color: #93C5FD; cursor: not-allowed; }
.icono-btn { width: 20px; height: 20px; }

/* Grid */
.grid-noticias { display: flex; flex-direction: column; gap: 1.5rem; }

.tarjeta-comunicado {
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
  transition: transform 0.2s;
}
.tarjeta-comunicado:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.header-left { display: flex; gap: 1rem; align-items: center; }

.icono-wrapper { background-color: #EFF6FF; padding: 10px; border-radius: 50%; }
.icono-card { width: 24px; height: 24px; color: #0B74DE; }

.meta-info { display: flex; flex-direction: column; }
.autor { font-weight: 700; color: #1F2937; font-size: 0.95rem; }
.fecha { font-size: 0.85rem; color: #6B7280; text-transform: capitalize; }
.badge-evento { 
  background: #EEF2FF; 
  color: #4F46E5; 
  font-size: 0.75rem; 
  font-weight: 600; 
  padding: 3px 8px; 
  border-radius: 6px; 
  margin-top: 0.25rem;
  width: fit-content;
}

.titulo-comunicado { font-size: 1.2rem; font-weight: 700; color: #1F2937; margin-bottom: 0.75rem; }
.texto-comunicado { color: #4B5563; line-height: 1.6; white-space: pre-line; }

/* Acciones en Card */
.acciones-card { display: flex; gap: 0.5rem; }
.btn-icon {
    background: none; border: none; cursor: pointer; padding: 5px; border-radius: 4px; color: #6B7280;
    transition: all 0.2s;
}
.btn-icon:hover { background-color: #F3F4F6; }
.btn-icon.editar:hover { color: #0B74DE; }
.btn-icon.eliminar:hover { color: #DC2626; }
.icono-sm { width: 20px; height: 20px; }

/* MODAL */
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex; justify-content: center; align-items: center;
    z-index: 1000;
}
.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.modal-header h2 { font-size: 1.5rem; color: #1F2937; margin: 0; }
.btn-cerrar { background: none; border: none; cursor: pointer; color: #6B7280; }

.formulario-modal { display: flex; flex-direction: column; gap: 1.2rem; }
.campo { display: flex; flex-direction: column; gap: 0.5rem; }
.campo label { font-weight: 600; font-size: 0.9rem; color: #374151; }
.input-form {
    padding: 0.75rem; border: 1px solid #D1D5DB; border-radius: 6px; font-family: inherit; font-size: 0.95rem;
}
.input-form:focus { outline: none; border-color: #0B74DE; box-shadow: 0 0 0 3px rgba(11, 116, 222, 0.1); }

/* Estilos Integración Calendario */
.integracion-calendario {
    background: #F9FAFB; padding: 1rem; border-radius: 8px; border: 1px solid #E5E7EB;
}
.checkbox-label {
    display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #374151; cursor: pointer; user-select: none;
}
.fila-fechas { display: flex; gap: 1rem; margin-top: 1rem; }
.fila-fechas .campo { flex: 1; }
.animated-fade { animation: fadeIn 0.3s ease; }

.modal-footer { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem; }
.btn-cancelar {
    background: white; border: 1px solid #D1D5DB; padding: 0.7rem 1.2rem; border-radius: 8px; cursor: pointer; font-weight: 500;
}
.btn-cancelar:hover { background: #F9FAFB; }

/* Estados Generales */
.estado-carga, .estado-vacio { text-align: center; padding: 4rem 0; color: #6B7280; }
.icono-vacio { width: 48px; height: 48px; opacity: 0.5; margin: 0 auto 1rem auto; display: block; }
.spinner {
  border: 3px solid #f3f3f3; border-top: 3px solid #0B74DE;
  border-radius: 50%; width: 30px; height: 30px;
  animation: spin 1s linear infinite; margin: 0 auto 1rem auto;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>