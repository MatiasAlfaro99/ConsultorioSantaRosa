<template>
  <div class="seccion-modulos">
    <h3 class="titulo-seccion">Accesos Directos</h3>
    
    <div class="grid-modulos">
      
      <router-link to="/documentos" class="tarjeta-modulo">
        <div class="icono-bg azul">
          <DocumentTextIcon class="icono" />
        </div>
        <span class="texto">Gestor Documental</span>
      </router-link>
      
      <router-link to="/comunicados" class="tarjeta-modulo">
        <div class="icono-bg verde">
          <MegaphoneIcon class="icono" />
        </div>
        <span class="texto">Comunicados</span>
      </router-link>
      
      <router-link to="/calendario" class="tarjeta-modulo">
        <div class="icono-bg morado">
          <CalendarIcon class="icono" />
        </div>
        <span class="texto">Calendario</span>
      </router-link>
      
      <router-link to="/solicitudes" class="tarjeta-modulo">
        <div class="icono-bg naranja">
          <PencilSquareIcon class="icono" />
        </div>
        <span class="texto">Mis Solicitudes</span>
      </router-link>
      
      <router-link to="/directorio" class="tarjeta-modulo">
        <div class="icono-bg gris">
          <UserGroupIcon class="icono" />
        </div>
        <span class="texto">Directorio</span>
      </router-link>



      <router-link to="/licencias" class="tarjeta-modulo">
        <div class="icono-bg rojo">
          <ClipboardDocumentCheckIcon class="icono" />
        </div>
        <span class="texto">Licencias Médicas</span>
      </router-link>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { 
  DocumentTextIcon, MegaphoneIcon, CalendarIcon, 
  PencilSquareIcon, UserGroupIcon, ClipboardDocumentCheckIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/store/auth.js'

const authStore = useAuthStore()
const puedeVerLicencias = computed(() => authStore.usuario && authStore.usuario.role === 'subdireccion')
</script>

<style scoped>
.seccion-modulos { margin-top: 0.5rem; }

.titulo-seccion {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 1.25rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #F1F5F9;
}

.grid-modulos {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1.25rem;
}

.tarjeta-modulo {
  background-color: white;
  border-radius: 14px;
  padding: 1.75rem 1.25rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.875rem;
  text-decoration: none;
  color: #1E293B;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.02);
  border: 1px solid #F1F5F9;
  transition: all 0.2s ease;
}

.tarjeta-modulo:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.06);
  border-color: #E2E8F0;
}

.icono-bg {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icono { width: 28px; height: 28px; }
.texto { font-weight: 600; font-size: 0.9rem; text-align: center; }

/* Paleta de Colores para Iconos */
.azul { background-color: #EFF6FF; color: #0B74DE; }
.verde { background-color: #ECFDF5; color: #10B981; }
.morado { background-color: #F3E8FF; color: #9333EA; }
.naranja { background-color: #FFF7ED; color: #F97316; }
.gris { background-color: #F8FAFC; color: #64748B; }
.amarillo { background-color: #FEFCE8; color: #CA8A04; }
.rojo { background-color: #FEF2F2; color: #EF4444; }
</style>
