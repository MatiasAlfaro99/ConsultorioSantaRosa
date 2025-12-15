<template>
  <div class="kpi-grid">
    
    <div class="kpi-card">
      <div class="kpi-content">
        <span class="kpi-value">{{ saldo?.dias_vacaciones_restantes || 0 }}</span>
        <span class="kpi-label">Días de Vacaciones</span>
      </div>
      <div class="kpi-icon-box bg-blue">
        <SunIcon class="icon text-blue" />
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-content">
        <span class="kpi-value">{{ saldo?.dias_admin_restantes || 0 }}</span>
        <span class="kpi-label">Días Administrativos</span>
      </div>
      <div class="kpi-icon-box bg-green">
        <BuildingOfficeIcon class="icon text-green" />
      </div>
    </div>

    <router-link to="/solicitudes" class="kpi-card kpi-link" :class="estadoClase">
      <div class="kpi-content">
        <span class="kpi-value">{{ conteoSolicitudes }}</span>
        <span class="kpi-label">Solicitudes Pendientes</span>
      </div>
      <div class="kpi-icon-box" :class="iconoFondo">
        <ClockIcon class="icon" :class="iconoTexto" />
      </div>
    </router-link>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { SunIcon, BuildingOfficeIcon, ClockIcon } from '@heroicons/vue/24/outline'

const props = defineProps({ saldo: Object, conteoSolicitudes: Number })

const estadoClase = computed(() => props.conteoSolicitudes > 0 ? 'border-warning' : '')
const iconoFondo = computed(() => props.conteoSolicitudes > 0 ? 'bg-orange' : 'bg-gray')
const iconoTexto = computed(() => props.conteoSolicitudes > 0 ? 'text-orange' : 'text-gray')
</script>

<style scoped>
.kpi-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.25rem;
}

.kpi-card {
  background-color: white;
  border-radius: 14px;
  padding: 1.5rem 1.75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.02);
  border: 1px solid #F1F5F9;
  border-left: 4px solid transparent;
  transition: all 0.2s ease;
}

.kpi-card.border-blue { border-left-color: #0B74DE; }
.kpi-card.border-green { border-left-color: #10B981; }
.kpi-card.border-warning { border-left-color: #F97316; }

.kpi-link { text-decoration: none; cursor: pointer; }
.kpi-link:hover { 
  transform: translateY(-2px); 
  box-shadow: 0 4px 6px rgba(0,0,0,0.04), 0 10px 20px rgba(0,0,0,0.04);
}

.kpi-content { display: flex; flex-direction: column; gap: 0.25rem; }

.kpi-value {
  font-size: 2.25rem;
  font-weight: 800;
  color: #1E293B;
  line-height: 1;
  letter-spacing: -0.02em;
}

.kpi-label {
  font-size: 0.875rem;
  color: #64748B;
  font-weight: 500;
}

.kpi-icon-box {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon { width: 26px; height: 26px; }

/* Colores (Estilo flat suave) */
.bg-blue { background-color: #EFF6FF; }
.text-blue { color: #0B74DE; }

.bg-green { background-color: #ECFDF5; }
.text-green { color: #10B981; }

.bg-orange { background-color: #FFF7ED; }
.text-orange { color: #F97316; }

.bg-gray { background-color: #F8FAFC; }
.text-gray { color: #64748B; }

@media (min-width: 768px) {
  .kpi-grid { grid-template-columns: repeat(3, 1fr); }
}
</style>
