<template>
  <div class="acciones-rapidas-container">
    
    <router-link to="/solicitudes/nueva" class="accion-card primaria">
      <div class="icono-box">
        <PlusIcon class="icono" />
      </div>
      <div class="texto-box">
        <span class="titulo">Nueva Solicitud</span>
        <span class="subtitulo">Vacaciones o Permisos</span>
      </div>
    </router-link>

    <router-link to="/calendario" class="accion-card secundaria">
      <div class="icono-box-secundario">
        <CalendarIcon class="icono-secundario" />
      </div>
      <span class="titulo-secundario">Ver Calendario</span>
    </router-link>

    <button v-if="esAdmin" @click="$emit('crear-comunicado')" class="accion-card terciaria">
      <div class="icono-box-terciario">
        <MegaphoneIcon class="icono-terciario" />
      </div>
      <div class="texto-box">
        <span class="titulo-terciario">Nuevo Comunicado</span>
        <span class="subtitulo">Publicar anuncio</span>
      </div>
    </button>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { PlusIcon, CalendarIcon, MegaphoneIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/store/auth.js'

const authStore = useAuthStore()

// Reutilizamos la lógica de rol
const esAdmin = computed(() => {
  const rol = authStore.usuario?.role
  return rol === 'direccion' || rol === 'jefatura' || rol === 'admin'
})
</script>

<style scoped>
.acciones-rapidas-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.accion-card {
  display: flex; 
  align-items: center; 
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-radius: 14px;
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer; 
  border: none; 
  text-align: left;
}

.accion-card:hover { 
  transform: translateY(-2px); 
  box-shadow: 0 8px 20px rgba(0,0,0,0.08); 
}

/* Estilo Primaria (Azul Fuerte - Solicitud) */
.primaria { 
  background: linear-gradient(135deg, #0B74DE 0%, #0A66C2 100%); 
  color: white;
  box-shadow: 0 4px 12px rgba(11, 116, 222, 0.25);
}
.primaria:hover { box-shadow: 0 8px 20px rgba(11, 116, 222, 0.35); }
.icono-box { 
  background: rgba(255,255,255,0.2); 
  padding: 10px; 
  border-radius: 10px; 
  display: flex; 
}
.icono { width: 22px; height: 22px; color: white; }
.texto-box { display: flex; flex-direction: column; }
.titulo { font-weight: 600; font-size: 0.95rem; }
.subtitulo { font-size: 0.75rem; opacity: 0.85; font-weight: 400; }

/* Estilo Secundaria (Blanco - Calendario) */
.secundaria { 
  background: white; 
  border: 1px solid #E2E8F0; 
  color: #374151; 
}
.secundaria:hover { border-color: #CBD5E1; }
.icono-box-secundario { color: #0B74DE; }
.icono-secundario { width: 22px; height: 22px; }
.titulo-secundario { font-weight: 600; font-size: 0.95rem; }

/* Estilo Terciaria (Azul claro - Comunicado) */
.terciaria { 
  background: #EFF6FF; 
  border: 1px solid #DBEAFE; 
  color: #1E40AF; 
}
.terciaria:hover { border-color: #BFDBFE; background: #DBEAFE; }
.icono-box-terciario { 
  background: white; 
  padding: 10px; 
  border-radius: 10px; 
  display: flex;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.icono-terciario { width: 22px; height: 22px; color: #2563EB; }
.titulo-terciario { font-weight: 600; font-size: 0.95rem; color: #1E40AF; }
.terciaria .subtitulo { color: #3B82F6; }
</style>