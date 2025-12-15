<template>
  <div class="card-dashboard">
    <!-- SECCIÓN COMUNICADOS -->
    <div class="card-header">
      <div class="titulo-wrapper">
        <div class="icono-titulo comunicados">
            <MegaphoneIcon class="icono-sm" />
        </div>
        <h3>Comunicados Oficiales</h3>
      </div>
      <router-link to="/comunicados" class="ver-todo">Ver todos</router-link>
    </div>

    <div v-if="cargando" class="loading">
      <div class="spinner-sm"></div>
    </div>

    <div v-else-if="comunicados.length === 0" class="empty-mini">
      <p>No hay avisos recientes.</p>
    </div>

    <ul v-else class="lista-comunicados">
      <li v-for="item in comunicados" :key="item.id" class="item-comunicado">
        <div class="info-top">
          <span v-if="esReciente(item.created_at)" class="badge-nuevo">¡NUEVO!</span>
          <span class="fecha">{{ formatearFecha(item.created_at) }}</span>
          <span v-if="item.evento" class="badge-evento">
            📅 {{ formatearFechaEvento(item.evento.fecha_inicio) }}
          </span>
        </div>
        <h4 class="titulo">{{ item.titulo }}</h4>
        <p class="resumen">{{ cortarTexto(item.contenido) }}</p>
      </li>
    </ul>

    <!-- SECCIÓN PRÓXIMOS EVENTOS -->
    <div class="card-header eventos-header">
      <div class="titulo-wrapper">
        <div class="icono-titulo eventos">
            <CalendarIcon class="icono-sm" />
        </div>
        <h3>Próximos Eventos</h3>
      </div>
      <router-link to="/calendario" class="ver-todo">Ver calendario</router-link>
    </div>

    <div v-if="cargandoEventos" class="loading">
      <div class="spinner-sm"></div>
    </div>

    <div v-else-if="eventos.length === 0" class="empty-mini">
      <p>No hay eventos próximos.</p>
    </div>

    <ul v-else class="lista-eventos">
      <li v-for="ev in eventos" :key="ev.id" class="item-evento">
        <div class="fecha-box" :class="ev.categoria || 'default'">
          <span class="dia">{{ obtenerDia(ev.fecha_inicio) }}</span>
          <span class="mes">{{ obtenerMes(ev.fecha_inicio) }}</span>
        </div>
        <div class="info-evento">
          <h4 class="titulo-evento">{{ ev.titulo }}</h4>
          <span class="hora-evento">{{ obtenerHora(ev.fecha_inicio) }} hrs</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { MegaphoneIcon, CalendarIcon } from '@heroicons/vue/24/outline'
import apiClient from '@/api/axios.js'

const comunicados = ref([])
const eventos = ref([])
const cargando = ref(true)
const cargandoEventos = ref(true)

const cargarDatos = async () => {
  try {
    const res = await apiClient.get('/comunicados')
    const datos = Array.isArray(res.data) ? res.data : (res.data.data || [])
    comunicados.value = datos.slice(0, 3) 
  } catch (error) {
    console.error("Error widget comunicados:", error)
  } finally {
    cargando.value = false
  }
}

const cargarEventos = async () => {
  try {
    const res = await apiClient.get('/eventos')
    const datos = Array.isArray(res.data) ? res.data : (res.data.data || [])
    eventos.value = datos.slice(0, 3) 
  } catch (error) {
    console.error("Error widget eventos:", error)
  } finally {
    cargandoEventos.value = false
  }
}

// Utilidades Comunicados
const formatearFecha = (f) => new Date(f).toLocaleDateString('es-CL', { day: 'numeric', month: 'long' })
const formatearFechaEvento = (f) => {
  const fecha = new Date(f)
  const dia = fecha.getDate()
  const mes = fecha.toLocaleDateString('es-CL', { month: 'short' })
  const hora = fecha.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })
  return `${dia} ${mes} - ${hora}`
}
const cortarTexto = (t) => t && t.length > 50 ? t.substring(0, 50) + '...' : t
const esReciente = (f) => {
  const d = new Date(f); const hoy = new Date();
  hoy.setDate(hoy.getDate() - 3); return d > hoy;
}

// Utilidades Eventos
const obtenerDia = (f) => new Date(f).getDate()
const obtenerMes = (f) => new Date(f).toLocaleDateString('es-CL', { month: 'short' }).toUpperCase().replace('.', '')
const obtenerHora = (f) => new Date(f).toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })

onMounted(() => {
  cargarDatos()
  cargarEventos()
})
</script>

<style scoped>
.card-dashboard {
  background: white; border-radius: 16px; padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); height: 100%;
}
.card-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f3f4f6;
}
.eventos-header {
  margin-top: 1.5rem;
}
.titulo-wrapper { display: flex; align-items: center; gap: 0.5rem; }
.icono-titulo { padding: 6px; border-radius: 8px; }
.icono-titulo.comunicados { background: #ECFDF5; color: #059669; }
.icono-titulo.eventos { background: #EEF2FF; color: #4F46E5; }
.icono-sm { width: 20px; height: 20px; }
.card-header h3 { font-size: 1rem; font-weight: 700; color: #1F2937; margin: 0; }
.ver-todo { font-size: 0.8rem; color: var(--color-azul-institucional); font-weight: 600; text-decoration: none; }

/* Comunicados */
.lista-comunicados { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.8rem; }
.item-comunicado { padding-bottom: 0.8rem; border-bottom: 1px solid #f9fafb; }
.item-comunicado:last-child { border-bottom: none; padding-bottom: 0; }
.info-top { display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.25rem; }
.fecha { font-size: 0.7rem; color: #9CA3AF; text-transform: capitalize; }
.badge-nuevo { background: #DCFCE7; color: #166534; font-size: 0.6rem; font-weight: 800; padding: 2px 5px; border-radius: 4px; }
.badge-evento { background: #EEF2FF; color: #4F46E5; font-size: 0.6rem; font-weight: 600; padding: 2px 6px; border-radius: 4px; }
.titulo { font-size: 0.9rem; font-weight: 600; color: #374151; margin: 0 0 0.15rem 0; }
.resumen { font-size: 0.8rem; color: #6B7280; margin: 0; line-height: 1.3; }

/* Eventos */
.lista-eventos { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem; }
.item-evento { display: flex; gap: 0.75rem; align-items: center; }
.fecha-box { 
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  width: 44px; height: 44px; background: #F3F4F6; border-radius: 8px; color: #4B5563; flex-shrink: 0;
}
.fecha-box.reunion { background: #DBEAFE; color: #1E40AF; }
.fecha-box.capacitacion { background: #F3E8FF; color: #7C3AED; }
.fecha-box.operativo { background: #D1FAE5; color: #065F46; }
.fecha-box.urgente { background: #FEE2E2; color: #991B1B; }
.fecha-box.importante { background: #FEF3C7; color: #92400E; }
.dia { font-size: 1rem; font-weight: 800; line-height: 1; }
.mes { font-size: 0.6rem; font-weight: 600; margin-top: 1px; }
.info-evento { display: flex; flex-direction: column; }
.titulo-evento { font-size: 0.85rem; font-weight: 600; margin: 0; color: #374151; }
.hora-evento { font-size: 0.75rem; color: #9CA3AF; }

.loading, .empty-mini { text-align: center; color: #9CA3AF; padding: 1rem 0; font-size: 0.85rem; }
.spinner-sm { width: 18px; height: 18px; border: 2px solid #eee; border-top-color: var(--color-azul-institucional); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>