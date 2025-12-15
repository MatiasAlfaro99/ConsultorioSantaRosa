<template>
  <div class="card-dashboard">
    <div class="card-header">
      <div class="titulo-wrapper">
        <div class="icono-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icono-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
        </div>
        <h3>Próximos Eventos</h3>
      </div>
      <router-link to="/calendario" class="ver-todo">Ver mes</router-link>
    </div>

    <div v-if="cargando" class="loading">Cargando...</div>
    
    <div v-else-if="eventos.length === 0" class="empty">
        <p>No hay eventos próximos.</p>
    </div>

    <ul v-else class="lista-eventos">
      <li v-for="ev in eventos" :key="ev.id" class="item-evento">
        <div class="fecha-box" :class="ev.categoria">
            <span class="dia">{{ obtenerDia(ev.fecha_inicio) }}</span>
            <span class="mes">{{ obtenerMes(ev.fecha_inicio) }}</span>
        </div>
        <div class="info">
            <h4 class="titulo">{{ ev.titulo }}</h4>
            <span class="hora">{{ obtenerHora(ev.fecha_inicio) }} hrs</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '@/api/axios.js'

const eventos = ref([])
const cargando = ref(true)

const cargarEventos = async () => {
    try {
        const res = await apiClient.get('/eventos')
        const datos = Array.isArray(res.data) ? res.data : (res.data.data || [])
        // Tomamos los próximos 4
        eventos.value = datos.slice(0, 4)
    } catch (e) {
        console.error(e)
    } finally {
        cargando.value = false
    }
}

// Helpers de fecha
const obtenerDia = (f) => new Date(f).getDate()
const obtenerMes = (f) => new Date(f).toLocaleDateString('es-CL', { month: 'short' }).toUpperCase().replace('.', '')
const obtenerHora = (f) => new Date(f).toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })

onMounted(cargarEventos)
</script>

<style scoped>
.card-dashboard { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; }
.titulo-wrapper { display: flex; align-items: center; gap: 0.5rem; }
.icono-titulo { background: #EEF2FF; padding: 6px; border-radius: 8px; color: #4F46E5; }
.icono-sm { width: 20px; height: 20px; }
.card-header h3 { font-size: 1.1rem; font-weight: 700; margin: 0; color: #1F2937; }
.ver-todo { font-size: 0.85rem; font-weight: 600; color: var(--color-azul-institucional); text-decoration: none; }

.lista-eventos { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem; }
.item-evento { display: flex; gap: 1rem; align-items: center; }

/* Caja de Fecha */
.fecha-box { 
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    width: 50px; height: 50px; background: #F3F4F6; border-radius: 10px; color: #4B5563; flex-shrink: 0;
}
.fecha-box.reunion { background: #DBEAFE; color: #1E40AF; }
.fecha-box.urgente { background: #FEE2E2; color: #991B1B; }
.fecha-box.efemeride { background: #FEF3C7; color: #92400E; }

.dia { font-size: 1.1rem; font-weight: 800; line-height: 1; }
.mes { font-size: 0.65rem; font-weight: 600; margin-top: 2px; }

.info { display: flex; flex-direction: column; }
.titulo { font-size: 0.9rem; font-weight: 600; margin: 0; color: #374151; }
.hora { font-size: 0.8rem; color: #9CA3AF; }
.loading, .empty { text-align: center; color: #9CA3AF; padding: 1rem; font-size: 0.9rem; }
</style>
