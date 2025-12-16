<template>
  <div class="pagina-solicitudes">
    <div class="encabezado-seccion">
      <div>
        <h1 class="titulo-pagina">Gestión de Solicitudes</h1>
        <p class="subtitulo-pagina">Revisa tus solicitudes y las de tu equipo.</p>
      </div>
      <router-link to="/solicitudes/nueva" class="boton-primario">
        <PlusIcon class="icono-btn" />
        Nueva Solicitud
      </router-link>
    </div>

    <div class="tabs-container">
      <button 
        class="tab-btn" 
        :class="{ activo: tabActiva === 'mis_solicitudes' }"
        @click="tabActiva = 'mis_solicitudes'"
      >
        Mis Solicitudes
      </button>
      <button 
        v-if="puedeGestionar"
        class="tab-btn" 
        :class="{ 'activo': tabActiva === 'por_aprobar' }" 
        @click="tabActiva = 'por_aprobar'"
      >
        Gestión de Equipo
        <span v-if="conteoPendientes > 0" class="badge-conteo">{{ conteoPendientes }}</span>
      </button>
    </div>

    <div v-if="cargando" class="estado-carga">
      Cargando historial...
    </div>

    <div v-else class="contenedor-datos">
      
      <div v-if="solicitudesFiltradas.length === 0" class="estado-vacio">
        <InboxIcon class="icono-vacio" />
        <p>No hay solicitudes en esta sección.</p>
      </div>

      <div v-else>
        <table class="tabla-solicitudes desktop-only">
          <thead>
            <tr>
              <th>Funcionario</th>
              <th>Tipo</th>
              <th>Fechas / Horas</th>
              <th>Estado Actual</th>
              <th v-if="tabActiva === 'por_aprobar'">Seguimiento / Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="solicitud in solicitudesFiltradas" :key="solicitud.id">
              <td>
                <div class="info-funcionario">
                  <span class="nombre-func">
                    {{ esMia(solicitud) ? 'Yo' : solicitud.solicitante?.name || 'Funcionario' }}
                  </span>
                  <span class="fecha-solicitud">
                    El {{ formatearFecha(solicitud.created_at) }}
                  </span>
                </div>
              </td>
              <td class="capitalize">
                {{ formatearTipo(solicitud.tipo) }}
                <span v-if="solicitud.es_por_horas" class="badge-mini">Horas</span>
              </td>
              <td>
                <div v-if="solicitud.es_por_horas">
                  {{ formatearFecha(solicitud.fecha_inicio) }} <br>
                  <span class="texto-gris">{{ solicitud.hora_inicio }} - {{ solicitud.hora_fin }}</span>
                </div>
                <div v-else>
                  {{ formatearFecha(solicitud.fecha_inicio) }} <br> 
                  <span class="texto-gris">hasta</span> {{ formatearFecha(solicitud.fecha_fin) }}
                </div>
              </td>
              <td>
                <span :class="['badge', obtenerClaseEstado(solicitud.estado)]">
                  {{ formatearEstado(solicitud.estado) }}
                </span>
              </td>
              
              <td v-if="tabActiva === 'por_aprobar'">
                
                <div v-if="puedeAprobar(solicitud)" class="acciones-jefe">
                  <button @click="aprobar(solicitud.id, solicitud.estado)" class="btn-icon btn-aprobar" title="Aprobar">
                    <CheckCircleIcon />
                  </button>
                  <button @click="rechazar(solicitud.id)" class="btn-icon btn-rechazar" title="Rechazar">
                    <XCircleIcon />
                  </button>
                </div>

                <span v-else-if="estaEnDireccion(solicitud)" class="badge-espera">
                   <ClockIcon class="icono-mini-espera" /> En Dirección
                </span>

                <span v-else-if="solicitud.estado === 'aprobado'" class="badge-info-green">
                   <CheckCircleIcon class="icono-mini-espera" /> Finalizado
                </span>

                 <span v-else-if="solicitud.estado === 'rechazado'" class="badge-info-red">
                   Rechazado
                </span>

                <span v-else class="texto-gris">-</span>
              </td>

              <td v-else>
                 <button 
                    v-if="solicitud.estado === 'aprobado'"
                    @click="descargarPDF(solicitud.id)" 
                    class="btn-icon btn-pdf" 
                    title="Descargar Comprobante"
                  >
                    <DocumentArrowDownIcon />
                  </button>
                  <span v-else class="texto-gris">-</span>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="lista-movil mobile-only">
          <div v-for="solicitud in solicitudesFiltradas" :key="solicitud.id" class="tarjeta-solicitud">
            <div class="tarjeta-header">
              <span class="nombre-func">
                {{ esMia(solicitud) ? 'Yo' : solicitud.solicitante?.name }}
              </span>
              <span :class="['badge', obtenerClaseEstado(solicitud.estado)]">
                {{ formatearEstado(solicitud.estado) }}
              </span>
            </div>
            <div class="tarjeta-body">
              <div class="dato-fila">
                <span class="label">Tipo:</span> 
                <span class="capitalize">{{ formatearTipo(solicitud.tipo) }}</span>
                <span v-if="solicitud.es_por_horas" class="badge-mini">Horas</span>
              </div>
              <div class="dato-fila">
                <CalendarIcon class="icono-mini" />
                <span v-if="solicitud.es_por_horas">
                  {{ formatearFecha(solicitud.fecha_inicio) }} ({{ solicitud.hora_inicio }} - {{ solicitud.hora_fin }})
                </span>
                <span v-else>
                  {{ formatearFecha(solicitud.fecha_inicio) }} - {{ formatearFecha(solicitud.fecha_fin) }}
                </span>
              </div>
              
              <div v-if="tabActiva === 'por_aprobar'" class="contenedor-acciones-movil">
                 <div v-if="puedeAprobar(solicitud)" class="acciones-movil">
                    <button @click="aprobar(solicitud.id, solicitud.estado)" class="btn-movil aprobar">Aprobar</button>
                    <button @click="rechazar(solicitud.id)" class="btn-movil rechazar">Rechazar</button>
                 </div>
                 <div v-else-if="estaEnDireccion(solicitud)">
                    <span class="badge-espera movil-espera">
                       <ClockIcon class="icono-mini-espera" /> Pendiente Dirección
                    </span>
                 </div>
                 <div v-else-if="solicitud.estado === 'aprobado'">
                    <span class="badge-info-green movil-espera">Finalizado</span>
                 </div>
              </div>

              <div v-if="solicitud.estado === 'aprobado'" class="acciones-movil-pdf">
                 <button @click="descargarPDF(solicitud.id)" class="btn-movil pdf-btn">
                    <DocumentArrowDownIcon class="icono-btn-movil"/> Descargar Comprobante
                 </button>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { 
  PlusIcon, 
  InboxIcon, 
  CalendarIcon, 
  CheckCircleIcon, 
  XCircleIcon, 
  DocumentArrowDownIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'
import apiClient from '@/api/axios.js'
import { useAuthStore } from '@/store/auth.js'

const authStore = useAuthStore()
const solicitudes = ref([])
const cargando = ref(true)
const tabActiva = ref('mis_solicitudes') 

const cargarSolicitudes = async () => {
  cargando.value = true
  try {
    const respuesta = await apiClient.get('/solicitudes')
    solicitudes.value = respuesta.data
  } catch (error) {
    console.error("Error:", error)
  } finally {
    cargando.value = false
  }
}

// --- COMPUTED ---
const puedeGestionar = computed(() => {
  return authStore.esJefatura || authStore.esSubdireccion || authStore.esDirector
})

const solicitudesFiltradas = computed(() => {
  if (tabActiva.value === 'mis_solicitudes') {
    return solicitudes.value.filter(s => esMia(s))
  } else {
    // Aquí mostramos TODAS las del equipo, para que no desaparezcan al cambiar de estado
    return solicitudes.value.filter(s => !esMia(s))
  }
})

const conteoPendientes = computed(() => {
  return solicitudes.value.filter(s => !esMia(s) && puedeAprobar(s)).length
})

// --- LÓGICA DE PERMISOS ---
const esMia = (solicitud) => {
  return solicitud.user_id === authStore.usuario.id
}

const puedeAprobar = (solicitud) => {
  const estado = solicitud.estado
  // Definimos cuándo es MI turno
  if (authStore.esJefatura && estado === 'pendiente_jefatura') return true
  if (authStore.esDirector && estado === 'pendiente_direccion') return true
  return false
}

// Lógica para mostrar "Esperando Dirección"
const estaEnDireccion = (solicitud) => {
  // Si soy Jefe y el estado es pendiente_direccion, significa que ya pasó por mí
  if (authStore.esJefatura && solicitud.estado === 'pendiente_direccion') return true
  return false
}

// --- ACCIONES ---
const descargarPDF = async (id) => {
  try {
    const response = await apiClient.get(`/solicitudes/${id}/descargar`, {
      responseType: 'blob' 
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `comprobante_solicitud_${id}.pdf`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (error) {
    console.error(error)
    alert('Error al descargar el comprobante.')
  }
}

const aprobar = async (id, estadoActual) => {
  if (!confirm('¿Aprobar esta solicitud?')) return
  
  try {
    let endpoint = ''
    if (estadoActual === 'pendiente_jefatura') {
      endpoint = `/solicitudes/${id}/aprobar-jefe`
    } else if (estadoActual === 'pendiente_direccion') {
      endpoint = `/solicitudes/${id}/aprobar-director`
    }

    if (endpoint) {
      await apiClient.post(endpoint)
      alert('Solicitud Aprobada Exitosamente')
      cargarSolicitudes()
    }
  } catch (error) {
    console.error(error)
    alert('Error al aprobar la solicitud')
  }
}

const rechazar = async (id) => {
  const razon = prompt('Ingrese la razón del rechazo:')
  if (!razon) return

  try {
    await apiClient.post(`/solicitudes/${id}/rechazar`, { razon_rechazo: razon })
    alert('Solicitud Rechazada')
    cargarSolicitudes()
  } catch (error) {
    alert('Error al rechazar')
  }
}

// --- FORMATO ---
const formatearFecha = (fechaRaw) => {
  if (!fechaRaw) return '-'
  const date = new Date(fechaRaw)
  return date.toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const formatearTipo = (tipo) => {
  const map = {
    'vacaciones': 'Feriado Legal',
    'administrativo': 'Día Administrativo',
    'sin_goce': 'Sin Goce de Sueldo',
    'devolucion': 'Devolución de Tiempo',
    'otros': 'Otros'
  }
  return map[tipo] || tipo
}

const formatearEstado = (estado) => {
  const map = {
    'pendiente_jefatura': 'Pendiente Jefe',
    'pendiente_subdireccion': 'Pendiente Subdirección',
    'pendiente_direccion': 'Pendiente Dirección',
    'aprobado': 'Aprobado',
    'rechazado': 'Rechazado'
  }
  return map[estado] || estado
}

const obtenerClaseEstado = (estado) => {
  if (estado && estado.includes('pendiente')) return 'badge-warning'
  if (estado === 'aprobado') return 'badge-success'
  if (estado === 'rechazado') return 'badge-danger'
  return 'badge-gray'
}

onMounted(cargarSolicitudes)
</script>

<style scoped>
.pagina-solicitudes { padding-bottom: 2rem; }
.encabezado-seccion { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.titulo-pagina { font-size: 1.5rem; font-weight: 700; color: var(--color-texto-principal); }
.subtitulo-pagina { color: var(--color-texto-secundario); font-size: 0.9rem; }

.boton-primario {
  background-color: var(--color-azul-institucional);
  color: white;
  padding: 0.75rem 1.25rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.icono-btn { width: 20px; height: 20px; }

/* TABS */
.tabs-container { display: flex; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-borde); }
.tab-btn {
  background: none; border: none; padding: 0.75rem 1rem; font-size: 0.95rem; font-weight: 500;
  color: var(--color-texto-secundario); cursor: pointer; border-bottom: 2px solid transparent; display: flex; align-items: center; gap: 0.5rem;
}
.tab-btn:hover { color: var(--color-azul-institucional); }
.tab-btn.activo { color: var(--color-azul-institucional); border-bottom-color: var(--color-azul-institucional); font-weight: 600; }
.badge-conteo { background-color: #EF4444; color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 10px; }

/* TABLA */
.tabla-solicitudes { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; border: 1px solid var(--color-borde); }
.tabla-solicitudes th { background-color: #F9FAFB; padding: 1rem; font-size: 0.85rem; text-align: left; color: var(--color-texto-secundario); }
.tabla-solicitudes td { padding: 1rem; border-bottom: 1px solid var(--color-borde); color: var(--color-texto-principal); }

/* ESTADO VACIO */
.estado-vacio { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; background-color: white; border-radius: 12px; border: 1px dashed var(--color-borde); color: var(--color-texto-secundario); gap: 1rem; }
.icono-vacio { width: 64px; height: 64px; stroke: #D1D5DB; }

.info-funcionario { display: flex; flex-direction: column; }
.nombre-func { font-weight: 600; }
.fecha-solicitud { font-size: 0.75rem; color: var(--color-texto-secundario); }
.texto-gris { color: var(--color-texto-secundario); font-size: 0.85rem; }
.badge-mini { background-color: #E0E7FF; color: #4338CA; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; margin-left: 0.5rem; }

.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
.badge-warning { background-color: #FFF7ED; color: #C2410C; border: 1px solid #FED7AA; }
.badge-success { background-color: #ECFDF5; color: #15803D; border: 1px solid #86EFAC; }
.badge-danger { background-color: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }
.badge-gray { background-color: #F3F4F6; color: #4B5563; }

/* Botones y Etiquetas de Estado */
.acciones-jefe { display: flex; gap: 0.5rem; }
.btn-icon { background: none; border: none; cursor: pointer; padding: 4px; transition: transform 0.1s; }
.btn-icon:hover { transform: scale(1.1); }
.btn-icon svg { width: 24px; height: 24px; }
.btn-aprobar { color: #16A34A; }
.btn-rechazar { color: #DC2626; }
.btn-pdf { color: #2563EB; }

/* Nuevos Badges */
.badge-espera { display: flex; align-items: center; gap: 4px; color: #D97706; font-size: 0.75rem; font-weight: 600; background: #FEF3C7; padding: 4px 8px; border-radius: 12px; }
.badge-info-green { display: flex; align-items: center; gap: 4px; color: #15803D; font-size: 0.75rem; font-weight: 600; background: #DCFCE7; padding: 4px 8px; border-radius: 12px; }
.badge-info-red { display: flex; align-items: center; gap: 4px; color: #B91C1C; font-size: 0.75rem; font-weight: 600; background: #FEE2E2; padding: 4px 8px; border-radius: 12px; }
.icono-mini-espera { width: 14px; height: 14px; }

/* Mobile */
.mobile-only { display: none; }
@media (max-width: 768px) {
  .desktop-only { display: none; }
  .mobile-only { display: block; }
  .tarjeta-solicitud { background: white; border: 1px solid var(--color-borde); border-radius: 12px; padding: 1rem; margin-bottom: 1rem; }
  .acciones-movil { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }
  .acciones-movil-pdf { margin-top: 1rem; }
  .btn-movil { padding: 0.5rem; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 0.5rem; width: 100%;}
  .aprobar { background-color: #DCFCE7; color: #16A34A; }
  .rechazar { background-color: #FEE2E2; color: #DC2626; }
  .pdf-btn { background-color: #DBEAFE; color: #2563EB; }
  .movil-espera { justify-content: center; width: 100%; margin-top: 0.5rem; }
  .icono-btn-movil { width: 20px; height: 20px; }
}
</style>