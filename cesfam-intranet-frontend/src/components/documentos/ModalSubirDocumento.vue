<template>
  <div v-if="mostrar" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h3>{{ documentoEditar ? 'Editar Documento' : 'Subir Nuevo Documento' }}</h3>
        <button @click="cerrar" class="btn-close">×</button>
      </div>

      <form @submit.prevent="manejarGuardado" class="modal-formulario">
        <div class="campo-form">
          <label for="titulo">Título del Documento</label>
          <input type="text" id="titulo" v-model="form.title" required placeholder="Ej: Protocolo de Seguridad" />
        </div>

        <div class="campo-form">
          <label for="tipo">Tipo de Documento</label>
          <select id="tipo" v-model="form.type">
            <option value="" disabled>Seleccione una categoría</option>
            <option value="protocolo">Protocolo</option>
            <option value="guia">Guía</option>
            <option value="circular">Circular</option>
            <option value="formulario">Formulario</option>
            <option value="otro">Otro</option>selec
          </select>
        </div>

        <div class="campo-form">
          <label for="archivo">Archivo</label>
          <input type="file" id="archivo" @change="manejarSeleccionArchivo" :required="!documentoEditar" />
          <small v-if="documentoEditar" class="texto-ayuda">
            Deja esto vacío para mantener el archivo actual.
          </small>
        </div>

        <div v-if="errorSubida" class="error-subida">
          {{ errorSubida }}
        </div>
        
        <div class="acciones">
          <button type="button" @click="cerrar" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar" :disabled="cargando">
            {{ cargando ? 'Guardando...' : (documentoEditar ? 'Actualizar' : 'Subir Documento') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import apiClient from '@/api/axios.js'

const props = defineProps({
  mostrar: Boolean,
  documentoEditar: Object 
})
const emit = defineEmits(['cerrar', 'subidaExitosa'])

const form = ref({ title: '', type: '' })
const archivo = ref(null)
const cargando = ref(false)
const errorSubida = ref(null)

// CORRECCIÓN 1: 'immediate' con doble M (Inglés)
watch(() => props.documentoEditar, (nuevoDoc) => {
  if (nuevoDoc) {
    form.value.title = nuevoDoc.title || ''
    form.value.type = nuevoDoc.type || '' 
  } else {
    form.value = { title: '', type: '' }
    archivo.value = null
    const input = document.getElementById('archivo')
    if(input) input.value = ''
  }
}, { immediate: true }) // <--- AHORA SÍ CARGARÁ LOS DATOS AL ABRIR

const cerrar = () => {
  emit('cerrar')
  errorSubida.value = null
}

const manejarSeleccionArchivo = (evento) => {
  archivo.value = evento.target.files[0]
}

const manejarGuardado = async () => {
  cargando.value = true
  errorSubida.value = null

  const formData = new FormData()

  // CORRECCIÓN 2: Agregamos el nombre y LA CATEGORÍA que faltaba
  formData.append('nombre', form.value.title)      
  formData.append('categoria', form.value.type) // <--- ¡ESTA LÍNEA FALTABA!

  if (archivo.value) {
    formData.append('archivo', archivo.value)      
  }

  try {
    if (props.documentoEditar) {
      // EDITAR
      formData.append('_method', 'PUT')
      
      await apiClient.post(`/documents/${props.documentoEditar.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      alert('Documento actualizado correctamente')
    } else {
      // CREAR
      if (!archivo.value) {
        throw new Error("Debes seleccionar un archivo") 
      }
      
      await apiClient.post('/documents', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      alert('Documento subido correctamente')
    }
    
    emit('subidaExitosa')
    cerrar()

  } catch (error) {
    console.error("Error completo:", error)
    errorSubida.value = error.response?.data?.message || "Error al guardar. Revisa la consola."
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.5);
  display: flex; justify-content: center; align-items: center; z-index: 50;
}
.modal-content {
  background-color: white; border-radius: 12px; padding: 2rem;
  width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
.modal-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;
}
.modal-header h3 { font-size: 1.25rem; font-weight: 700; color: #1F2937; }
.btn-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6B7280; }

.modal-formulario { display: flex; flex-direction: column; gap: 1.25rem; }
.campo-form label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; color: #374151; }
.campo-form input[type="text"], .campo-form select {
  width: 100%; padding: 0.75rem; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 0.95rem;
}
.texto-ayuda { font-size: 0.8rem; color: #6B7280; margin-top: 0.25rem; display: block; }
.error-subida { color: #DC2626; font-size: 0.875rem; background-color: #FEF2F2; padding: 0.75rem; border-radius: 6px; }

.acciones { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem; }
.btn-cancelar {
  background: white; border: 1px solid #D1D5DB; padding: 0.6rem 1rem;
  border-radius: 8px; cursor: pointer; font-weight: 500;
}
.btn-guardar {
  background-color: #0B74DE; color: white; border: none; padding: 0.6rem 1rem;
  border-radius: 8px; cursor: pointer; font-weight: 600;
}
.btn-guardar:disabled { background-color: #93C5FD; cursor: not-allowed; }
</style>
