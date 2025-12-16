<template>
  <div class="modal-overlay" @click.self="cerrar">
    <div class="modal-content">
      <div class="modal-header">
        <h2>{{ form.id ? 'Editar Evento' : 'Nuevo Evento' }}</h2>
        <button type="button" class="btn-cerrar" @click="cerrar">×</button>
      </div>

      <form @submit.prevent="guardar" class="formulario">
        
        <div v-if="errores.length" class="alerta-error">
            <ul><li v-for="(err, index) in errores" :key="index">{{ err }}</li></ul>
        </div>

        <div class="campo">
          <label>Título del Evento</label>
          <input v-model="form.titulo" type="text" required placeholder="Ej: Reunión Clínica" class="input-form">
        </div>

        <div class="campo">
          <label>Categoría</label>
          <select v-model="form.categoria" required class="input-form">
            <option value="reunion">Reunión</option>
            <option value="capacitacion">Capacitación</option>
            <option value="operativo">Operativo / Terreno</option>
            <option value="efemeride">Efeméride / Feriado</option>
            <option value="urgente">Urgente</option>
          </select>
        </div>

        <div class="fila-doble">
          <div class="campo">
            <label>Fecha</label>
            <input v-model="form.fecha_inicio" type="date" required class="input-form">
          </div>
          <div class="campo">
            <label>Hora Inicio</label>
            <input v-model="form.hora_inicio" type="time" required class="input-form">
          </div>
        </div>

        <div class="campo">
          <label>Descripción</label>
          <textarea v-model="form.descripcion" rows="3" placeholder="Detalles adicionales..." class="input-form"></textarea>
        </div>

        <div class="modal-footer">
          <button 
            v-if="form.id" 
            type="button" 
            class="btn-eliminar" 
            @click="emitirEliminar"
          >
            Eliminar
          </button>

          <div class="acciones-derecha">
            <button type="button" class="btn-cancelar" @click="cerrar">Cancelar</button>
            <button type="submit" class="boton-guardar" :disabled="guardando">
              {{ guardando ? 'Guardando...' : (form.id ? 'Guardar Cambios' : 'Agendar Evento') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, watch } from 'vue'
import apiClient from '@/api/axios.js'

const props = defineProps(['mostrar', 'preseleccionFecha', 'eventoEditar'])
const emit = defineEmits(['cerrar', 'guardado', 'eliminar'])

const guardando = ref(false)
const errores = ref([])

const form = reactive({
  id: null,
  titulo: '',
  categoria: 'reunion',
  fecha_inicio: '',
  hora_inicio: '09:00',
  descripcion: ''
})

onMounted(() => {
    resetForm()
})

watch(() => props.mostrar, (val) => {
    if (val) resetForm()
})

const resetForm = () => {
    errores.value = []
    
    if (props.eventoEditar) {
        const ev = props.eventoEditar
        
        form.id = ev.id
        form.titulo = ev.titulo
        form.descripcion = ev.descripcion || ''
        form.categoria = ev.categoria || 'reunion'

        if (ev.fecha_inicio) {
            const fechaObj = new Date(ev.fecha_inicio)
            // Ajustamos para obtener la fecha local correctamente
            const year = fechaObj.getFullYear()
            const month = String(fechaObj.getMonth() + 1).padStart(2, '0')
            const day = String(fechaObj.getDate()).padStart(2, '0')
            form.fecha_inicio = `${year}-${month}-${day}`
            
            // Hora formato HH:mm local
            const hours = String(fechaObj.getHours()).padStart(2, '0')
            const minutes = String(fechaObj.getMinutes()).padStart(2, '0')
            form.hora_inicio = `${hours}:${minutes}`
        }
    
    } else {
        form.id = null
        if (props.preseleccionFecha) {
            form.fecha_inicio = props.preseleccionFecha
        } else {
            // Fecha local actual YYYY-MM-DD
            const hoy = new Date()
            const year = hoy.getFullYear()
            const month = String(hoy.getMonth() + 1).padStart(2, '0')
            const day = String(hoy.getDate()).padStart(2, '0')
            form.fecha_inicio = `${year}-${month}-${day}`
        }
        form.titulo = ''
        form.descripcion = ''
        form.hora_inicio = '09:00'
        form.categoria = 'reunion'
    }
}

const cerrar = () => {
    emit('cerrar')
}

const emitirEliminar = () => {
    emit('eliminar', form.id)
}

// ESTA ES LA PARTE QUE CAMBIÉ PARA ARREGLAR LA HORA
const guardar = async () => {
  guardando.value = true
  errores.value = []

  try {
    // Construimos la fecha combinada manualmente para no perder la zona horaria
    const fechaCombinadaStr = `${form.fecha_inicio} ${form.hora_inicio}:00`
    // Creamos objeto Date solo para calcular la hora de fin
    const fechaStartObj = new Date(form.fecha_inicio + 'T' + form.hora_inicio + ':00')
    const fechaEndObj = new Date(fechaStartObj)
    fechaEndObj.setHours(fechaEndObj.getHours() + 1) // +1 hora

    // Función auxiliar para formatear a YYYY-MM-DD HH:mm:ss LOCAL
    const formatLocal = (date) => {
        const pad = (n) => n < 10 ? '0' + n : n;
        return date.getFullYear() + '-' +
            pad(date.getMonth() + 1) + '-' +
            pad(date.getDate()) + ' ' +
            pad(date.getHours()) + ':' +
            pad(date.getMinutes()) + ':' +
            pad(date.getSeconds());
    }

    const payload = {
      titulo: form.titulo,
      categoria: form.categoria,
      descripcion: form.descripcion,
      // Enviamos el string directo tal cual lo escribió el usuario
      fecha_inicio: fechaCombinadaStr, 
      // Calculamos el fin manteniendo el formato local
      fecha_fin: formatLocal(fechaEndObj), 
      lugar: 'CESFAM' 
    }
    
    if (form.id) {
        await apiClient.put(`/eventos/${form.id}`, payload)
        alert('Evento actualizado correctamente')
    } else {
        await apiClient.post('/eventos', payload)
        alert('Evento creado exitosamente')
    }
    
    emit('guardado')
    cerrar()

  } catch (error) {
    console.error("Error API:", error)
    if (error.response && error.response.status === 422) {
        const dataErrors = error.response.data.errors || {}
        errores.value = Object.values(dataErrors).flat()
    } else {
        errores.value = ["Ocurrió un error al guardar el evento."]
    }
  } finally {
    guardando.value = false
  }
}
</script>

<style scoped>
/* Tus estilos se mantienen igual */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-content { background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 450px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
.modal-header { display: flex; justify-content: space-between; margin-bottom: 1.5rem; align-items: center; }
.modal-header h2 { font-size: 1.25rem; font-weight: 700; margin: 0; color: #1F2937; }
.btn-cerrar { background: none; border: none; cursor: pointer; font-size: 1.5rem; color: #9CA3AF; line-height: 1; }
.formulario { display: flex; flex-direction: column; gap: 1rem; }
.campo { display: flex; flex-direction: column; gap: 0.4rem; }
.fila-doble { display: flex; gap: 1rem; }
.fila-doble .campo { flex: 1; }
.campo label { font-weight: 600; font-size: 0.85rem; color: #374151; }
.input-form { padding: 0.6rem; border: 1px solid #D1D5DB; border-radius: 6px; font-family: inherit; font-size: 0.95rem; }
.modal-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; }
.acciones-derecha { display: flex; gap: 1rem; margin-left: auto; }
.btn-cancelar { background: white; border: 1px solid #D1D5DB; padding: 0.6rem 1rem; border-radius: 6px; cursor: pointer; }
.boton-guardar { background: #0B74DE; color: white; border: none; padding: 0.6rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-eliminar { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; padding: 0.6rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-eliminar:hover { background: #fecaca; }
.alerta-error { background-color: #FEE2E2; color: #B91C1C; padding: 0.75rem; border-radius: 6px; font-size: 0.85rem; }
.alerta-error ul { margin: 0; padding-left: 1.2rem; }
</style>