<template>
  <div class="pagina-calendario">
    <div class="encabezado-cal">
      <h1 class="titulo-pagina">Calendario Institucional</h1>
      <button v-if="puedeCrear" class="btn-nuevo" @click="abrirModalManual">
        + Nuevo Evento
      </button>
    </div>

    <div class="calendario-contenedor">
      <FullCalendar ref="fullCalendarRef" :options="calendarOptions" />
    </div>

    <ModalEvento 
      v-if="mostrarModal"
      :mostrar="mostrarModal" 
      :preseleccionFecha="fechaSeleccionada"
      :evento-editar="eventoSeleccionado"
      @cerrar="cerrarModal"
      @guardado="recargarEventos"
      @eliminar="eliminarDesdeModal"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import esLocale from '@fullcalendar/core/locales/es'
import apiClient from '@/api/axios.js'
import { useAuthStore } from '@/store/auth.js'
import ModalEvento from '@/components/calendario/ModalEvento.vue'

const authStore = useAuthStore()
const fullCalendarRef = ref(null) 
const mostrarModal = ref(false)
const fechaSeleccionada = ref('')
const eventoSeleccionado = ref(null) 
const eventos = ref([])

// --- LÓGICA DE PERMISOS ACTUALIZADA ---
const puedeCrear = computed(() => {
  // Ahora usamos el getter robusto del store que incluye 'direccion', 'admin', 'subdireccion'.
  // Agregamos 'esJefatura' explícitamente por si el getter total no lo incluía.
  return authStore.tienePermisoTotal || authStore.esJefatura
})

// --- MÉTODOS DE APERTURA ---

// Clic en una celda vacía (Crear nuevo)
function handleDateClick(arg) {
  if (!puedeCrear.value) return
  
  eventoSeleccionado.value = null // Limpiamos para indicar que es CREACIÓN
  fechaSeleccionada.value = arg.dateStr
  mostrarModal.value = true
}

// Clic en botón "+ Nuevo Evento"
function abrirModalManual() {
  eventoSeleccionado.value = null // Limpiamos para indicar que es CREACIÓN
  fechaSeleccionada.value = new Date().toISOString().split('T')[0]
  mostrarModal.value = true
}

// Clic en un evento existente (Editar)
function handleEventClick(info) {
  if (!puedeCrear.value) {
    // Si no tiene permisos, solo mostramos info básica (solo lectura)
    // Podrías abrir un modal de "Ver Detalle" aquí si quisieras en el futuro
    alert(`Evento: ${info.event.title}\n${info.event.extendedProps.descripcion || ''}`)
    return
  }

  // Recuperamos los datos del objeto de FullCalendar para pasarlos al Modal
  eventoSeleccionado.value = {
    id: info.event.id,
    titulo: info.event.title,
    fecha_inicio: info.event.startStr, 
    fecha_fin: info.event.endStr,
    // Recuperamos los datos extra que guardamos en extendedProps
    descripcion: info.event.extendedProps.descripcion,
    lugar: info.event.extendedProps.lugar,
    categoria: info.event.extendedProps.categoria
  }

  // Abrimos el modal en modo edición
  mostrarModal.value = true
}

function cerrarModal() {
  mostrarModal.value = false
  eventoSeleccionado.value = null 
}

// --- LOGICA API ---

const recargarEventos = () => {
  mostrarModal.value = false
  cargarEventosDesdeAPI()
}

const eliminarDesdeModal = async (idEvento) => {
    if(!confirm("¿Estás seguro de eliminar este evento permanentemente?")) return;

    try {
        await apiClient.delete(`/eventos/${idEvento}`)
        mostrarModal.value = false
        cargarEventosDesdeAPI() // Recargar calendario
        alert("Evento eliminado correctamente")
    } catch (error) {
        console.error("Error eliminando:", error)
        alert("Hubo un error al eliminar el evento.")
    }
}

const cargarEventosDesdeAPI = async () => {
  try {
    const respuesta = await apiClient.get('/eventos')
    // Ajuste por si el backend devuelve { data: [...] } o directo [...]
    const datosRaw = respuesta.data.data || respuesta.data || []

    const eventosFormateados = datosRaw.map(ev => {
        if (!ev.fecha_inicio) return null;

        return {
            id: ev.id,
            title: ev.titulo, 
            start: ev.fecha_inicio, 
            end: ev.fecha_fin, 
            color: obtenerColor(ev.categoria), 
            extendedProps: {
                descripcion: ev.descripcion,
                lugar: ev.lugar,
                categoria: ev.categoria 
            }
        }
    }).filter(e => e !== null)
    
    eventos.value = eventosFormateados
    // Actualizamos las opciones del calendario reactivamente
    calendarOptions.events = eventosFormateados

  } catch (error) {
    console.error("❌ Error cargando calendario:", error)
  }
}

const obtenerColor = (categoria) => {
  const colores = {
    reunion: '#3B82F6', capacitacion: '#8B5CF6', 
    operativo: '#10B981', efemeride: '#F59E0B', urgente: '#EF4444'
  }
  return colores[categoria] || '#6B7280'
}

// Configuración del objeto FullCalendar
const calendarOptions = reactive({
  plugins: [ dayGridPlugin, interactionPlugin ],
  initialView: 'dayGridMonth',
  locale: esLocale,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth'
  },
  // Asignamos la referencia reactiva de eventos
  events: eventos, 
  dateClick: handleDateClick,
  eventClick: handleEventClick, 
  editable: false, // Arrastrar desactivado para evitar ediciones accidentales sin modal
  height: 'auto',
  selectable: true
})

onMounted(cargarEventosDesdeAPI)
</script>

<style scoped>
.pagina-calendario { max-width: 1200px; margin: 0 auto; padding-bottom: 3rem; }
.encabezado-cal { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.titulo-pagina { font-size: 1.8rem; font-weight: 700; color: #1F2937; }
.btn-nuevo { background-color: #0B74DE; color: white; border: none; padding: 0.7rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.btn-nuevo:hover { background-color: #095cb3; }
.calendario-contenedor { background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
:deep(.fc-toolbar-title) { font-size: 1.5rem; text-transform: capitalize; }
:deep(.fc-button-primary) { background-color: #0B74DE; border-color: #0B74DE; }
:deep(.fc-event) { cursor: pointer; }
</style>