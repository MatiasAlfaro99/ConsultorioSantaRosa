<template>
  <div class="kpi-grid">
    
    <div class="kpi-card" :class="{ 'cargando': cargando }">
      <div class="card-header">
        <span class="card-title">Feriado Legal</span>
        <div class="icon-circle bg-blue-light">
          <SunIcon class="icon text-blue" />
        </div>
      </div>

      <div class="card-body">
        <div class="number-row">
          <span class="big-number text-blue">
            {{ cargando ? '-' : (datos.saldos?.vacaciones?.disponibles ?? 0) }}
          </span>
          <span class="unit">días disp.</span>
        </div>

        <div class="progress-container">
          <div 
            class="progress-bar bg-blue" 
            :style="{ width: calcularPorcentaje(datos.saldos?.vacaciones) + '%' }"
          ></div>
        </div>
        
        <p class="subtext">
          de {{ datos.saldos?.vacaciones?.total || 15 }} días totales
        </p>
      </div>
    </div>

    <div class="kpi-card" :class="{ 'cargando': cargando }">
      <div class="card-header">
        <span class="card-title">Días Administrativos</span>
        <div class="icon-circle bg-green-light">
          <BuildingOfficeIcon class="icon text-green" />
        </div>
      </div>

      <div class="card-body">
        <div class="number-row">
          <span class="big-number text-green">
            {{ cargando ? '-' : (datos.saldos?.administrativos?.disponibles ?? 0) }}
          </span>
          <span class="unit">días disp.</span>
        </div>

        <div class="progress-container">
          <div 
            class="progress-bar bg-green" 
            :style="{ width: calcularPorcentaje(datos.saldos?.administrativos) + '%' }"
          ></div>
        </div>

        <p class="subtext">
          de {{ datos.saldos?.administrativos?.total || 6 }} días totales
        </p>
      </div>
    </div>

    <router-link to="/solicitudes" class="kpi-card kpi-link" :class="estadoClase">
      <div class="card-header">
        <span class="card-title">Solicitudes en Curso</span>
        <div class="icon-circle" :class="iconoFondo">
          <ClockIcon class="icon" :class="iconoTexto" />
        </div>
      </div>

      <div class="card-body centered-body">
        <span class="big-number" :class="conteoSolicitudes > 0 ? 'text-orange' : 'text-gray'">
          {{ cargando ? '-' : conteoSolicitudes }}
        </span>
        <span class="status-badge" :class="conteoSolicitudes > 0 ? 'badge-orange' : 'badge-gray'">
          {{ conteoSolicitudes === 1 ? 'Pendiente' : 'Pendientes' }}
        </span>
      </div>
    </router-link>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { SunIcon, BuildingOfficeIcon, ClockIcon } from '@heroicons/vue/24/outline'
import apiClient from '@/api/axios'

// --- ESTADO ---
const cargando = ref(true)
const datos = ref({
  saldos: {
    vacaciones: { disponibles: 0, total: 15 },
    administrativos: { disponibles: 0, total: 6 }
  },
  conteo_pendientes: 0
})

const conteoSolicitudes = computed(() => datos.value.conteo_pendientes)

// --- ESTILOS COMPUTADOS ---
const estadoClase = computed(() => conteoSolicitudes.value > 0 ? 'border-orange-left' : 'border-gray-left')
const iconoFondo = computed(() => conteoSolicitudes.value > 0 ? 'bg-orange-light' : 'bg-gray-light')
const iconoTexto = computed(() => conteoSolicitudes.value > 0 ? 'text-orange' : 'text-gray')

// --- LÓGICA ---
const cargarResumen = async () => {
  try {
    const response = await apiClient.get('/dashboard/resumen')
    datos.value = response.data
  } catch (error) {
    console.error('Error cargando KPIs:', error)
  } finally {
    cargando.value = false
  }
}

const calcularPorcentaje = (rubro) => {
  if (!rubro || !rubro.total || rubro.total === 0) return 0
  // Calculamos porcentaje de días DISPONIBLES
  const porcentaje = (rubro.disponibles / rubro.total) * 100
  return Math.max(0, Math.min(100, porcentaje)) // Limitar entre 0 y 100
}

onMounted(cargarResumen)
</script>

<style scoped>
/* GRID PRINCIPAL */
.kpi-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .kpi-grid { grid-template-columns: repeat(3, 1fr); }
}

/* TARJETA BASE */
.kpi-card {
  background-color: white;
  border-radius: 16px; /* Bordes más redondeados */
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.03);
  border: 1px solid #F1F5F9;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
  transition: transform 0.2s, box-shadow 0.2s;
}

.kpi-card.cargando { opacity: 0.6; pointer-events: none; }

/* HEADER DE TARJETA (Icono + Titulo) */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #64748B;
}

.icon-circle {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.icon { width: 22px; height: 22px; }

/* CUERPO DE TARJETA */
.card-body {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.number-row {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
}

.big-number {
  font-size: 2.5rem; /* Número grande y claro */
  font-weight: 800;
  line-height: 1;
}

.unit {
  font-size: 0.9rem;
  font-weight: 500;
  color: #94A3B8;
}

/* BARRA DE PROGRESO */
.progress-container {
  width: 100%;
  height: 8px;
  background-color: #F1F5F9;
  border-radius: 99px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  border-radius: 99px;
  transition: width 1s ease-in-out;
}

.subtext {
  font-size: 0.8rem;
  color: #94A3B8;
  margin: 0;
}

/* ESTILOS ESPECIFICOS DE COLORES */
.bg-blue-light { background-color: #EFF6FF; }
.text-blue { color: #2563EB; }
.bg-blue { background-color: #2563EB; }

.bg-green-light { background-color: #ECFDF5; }
.text-green { color: #10B981; }
.bg-green { background-color: #10B981; }

.bg-orange-light { background-color: #FFF7ED; }
.text-orange { color: #F97316; }

.bg-gray-light { background-color: #F8FAFC; }
.text-gray { color: #94A3B8; }

/* ESTILOS ESPECIFICOS PENDIENTES */
.kpi-link { text-decoration: none; border-left: 4px solid transparent; }
.kpi-link:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

.border-orange-left { border-left-color: #F97316; }
.border-gray-left { border-left-color: #CBD5E1; }

.centered-body {
  align-items: flex-start; /* Alineado a la izquierda para consistencia */
}

.status-badge {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-orange { background-color: #FFF7ED; color: #F97316; }
.badge-gray { background-color: #F1F5F9; color: #94A3B8; }

</style>