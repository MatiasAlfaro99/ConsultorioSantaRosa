<template>
  <div class="item-documento">
    <div class="item-icono" :class="claseIcono">
      <DocumentTextIcon v-if="esTipo('protocolo')" />
      <MegaphoneIcon v-else-if="esTipo('circular')" />
      <ClipboardDocumentListIcon v-else-if="esTipo('formulario')" />
      <DocumentIcon v-else />
    </div>
    
    <div class="item-contenido-principal">
      <h4 class="item-titulo">{{ documento.title }}</h4>
      
      <div class="item-meta">
        <span class="meta-tag">{{ documento.type }}</span>
        <span class="meta-separador">•</span>
        <span class="meta-fecha">{{ formatearFecha(documento.created_at) }}</span>
      </div>
    </div>

    <div class="item-acciones">
      <a 
        :href="urlDescarga" 
        target="_blank" 
        download
        class="btn-accion btn-descargar" 
        title="Descargar"
      >
        <ArrowDownTrayIcon class="icono-accion" />
      </a>

      <button 
        v-if="esAdmin" 
        class="btn-accion btn-editar" 
        title="Editar"
        @click="$emit('editar', documento)"
      >
        <PencilSquareIcon class="icono-accion" />
      </button>

      <button 
        v-if="esAdmin" 
        class="btn-accion btn-eliminar" 
        title="Eliminar"
        @click="confirmarEliminacion"
      >
        <TrashIcon class="icono-accion" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { 
  DocumentTextIcon, ClipboardDocumentListIcon, MegaphoneIcon, DocumentIcon,
  ArrowDownTrayIcon, PencilSquareIcon, TrashIcon 
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/store/auth.js'
import apiClient from '@/api/axios.js'

const props = defineProps({
  documento: { type: Object, required: true }
})

const emit = defineEmits(['eliminar', 'editar'])
const authStore = useAuthStore()

const urlDescarga = computed(() => {

  return props.documento.file || '#'
})

const esAdmin = computed(() => {
  const rol = authStore.usuario?.role
  return ['director', 'direccion', 'subdireccion', 'jefatura', 'admin'].includes(rol)
})

// --- UTILIDADES ---
const esTipo = (t) => (props.documento.type || '').toLowerCase() === t

const formatearFecha = (f) => {
  if(!f) return ''
  return new Date(f).toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric'})
}

const confirmarEliminacion = async () => {
  if(confirm('¿Estás seguro de eliminar este documento?')) {
    try {
      await apiClient.delete(`/documents/${props.documento.id}`)
      

      emit('eliminar', props.documento.id) 
      
      alert("Documento eliminado correctamente") // Feedback visual
    } catch (error) {
      alert('Error al eliminar. Verifica permisos.')
      console.error(error)
    }
  }
}
const claseIcono = computed(() => {
  const tipo = (props.documento.type || '').toLowerCase()
  switch (tipo) {
    case 'protocolo': return 'bg-blue-100 text-blue-600'
    case 'circular': return 'bg-yellow-100 text-yellow-600'
    case 'formulario': return 'bg-purple-100 text-purple-600'
    default: return 'bg-gray-100 text-gray-600'
  }
})

</script>

<style scoped>
.item-documento {
  display: flex; align-items: center; gap: 1rem; padding: 1rem;
  background: white; border: 1px solid #E5E7EB; border-radius: 12px;
  margin-bottom: 0.75rem; transition: all 0.2s;
}
.item-documento:hover { border-color: #0B74DE; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

.item-icono {
  width: 40px; height: 40px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.item-icono svg { width: 20px; height: 20px; }

/* Colores por tipo */
.bg-blue-100 { background-color: #EFF6FF; color: #2563EB; } /* Default */

.item-contenido-principal { flex: 1; }
.item-titulo { font-size: 0.95rem; font-weight: 600; color: #1F2937; margin: 0; }
.item-meta { font-size: 0.8rem; color: #6B7280; margin-top: 2px; }
.meta-tag { text-transform: capitalize; background: #F3F4F6; padding: 1px 6px; border-radius: 4px; }

.item-acciones { display: flex; gap: 0.5rem; }

.btn-accion {
  width: 32px; height: 32px; border-radius: 6px; border: 1px solid transparent;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; background: transparent; color: #6B7280;
  transition: all 0.2s;
}
.btn-accion:hover { background-color: #F3F4F6; color: #0B74DE; }

.btn-descargar:hover { color: #0B74DE; background-color: #EFF6FF; }
.btn-editar:hover { color: #F59E0B; background-color: #FFFBEB; }
.btn-eliminar:hover { color: #EF4444; background-color: #FEF2F2; }

.icono-accion { width: 18px; height: 18px; }
</style>
