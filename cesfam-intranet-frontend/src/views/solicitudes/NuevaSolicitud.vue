<template>
  <div class="contenedor-formulario">
    <div class="encabezado-form">
      <h1 class="titulo-pagina">Nueva Solicitud</h1>
      <p class="subtitulo">Complete los datos para solicitar su permiso.</p>
    </div>

    <div class="tarjeta-form">
      <form @submit.prevent="enviarSolicitud" class="formulario">
        
        <!-- TIPO DE PERMISO -->
        <div class="grupo-input">
          <label class="etiqueta">Tipo de Permiso</label>
          <div class="opciones-tipo">
            <label class="radio-card" :class="{ activo: form.tipo === 'vacaciones' }">
              <input type="radio" value="vacaciones" v-model="form.tipo" />
              <span class="icono">🏖️</span>
              <span class="texto">Feriado Legal</span>
            </label>
            
            <label class="radio-card" :class="{ activo: form.tipo === 'administrativo' }">
              <input type="radio" value="administrativo" v-model="form.tipo" />
              <span class="icono">📅</span>
              <span class="texto">Día Administrativo</span>
            </label>

            <label class="radio-card" :class="{ activo: form.tipo === 'sin_goce' }">
              <input type="radio" value="sin_goce" v-model="form.tipo" />
              <span class="icono">💸</span>
              <span class="texto">Sin Goce</span>
            </label>

            <label class="radio-card" :class="{ activo: form.tipo === 'devolucion' }">
              <input type="radio" value="devolucion" v-model="form.tipo" />
              <span class="icono">⏱️</span>
              <span class="texto">Devolución</span>
            </label>

            <label class="radio-card" :class="{ activo: form.tipo === 'otros' }">
              <input type="radio" value="otros" v-model="form.tipo" />
              <span class="icono">📝</span>
              <span class="texto">Otros</span>
            </label>
          </div>
        </div>

        <!-- TIPO DE DURACIÓN (Días vs Horas) -->
        <div class="grupo-input">
          <label class="etiqueta">Duración</label>
          <div class="toggle-duracion">
            <button 
              type="button" 
              :class="['btn-toggle', { activo: form.duracion_tipo === 'dias' }]"
              @click="form.duracion_tipo = 'dias'"
            >
              Por Días
            </button>
            <button 
              type="button" 
              :class="['btn-toggle', { activo: form.duracion_tipo === 'horas' }]"
              @click="form.duracion_tipo = 'horas'"
            >
              Por Horas
            </button>
          </div>
        </div>

        <!-- FECHAS (Modo Días) -->
        <div v-if="form.duracion_tipo === 'dias'" class="fila-fechas">
          <div class="grupo-input">
            <label for="inicio" class="etiqueta">Desde</label>
            <input 
              type="date" 
              id="inicio" 
              v-model="form.fecha_inicio" 
              class="input-fecha"
              required
              :min="fechaMinima"
            />
          </div>

          <div class="grupo-input">
            <label for="fin" class="etiqueta">Hasta</label>
            <input 
              type="date" 
              id="fin" 
              v-model="form.fecha_fin" 
              class="input-fecha"
              required
              :min="form.fecha_inicio || fechaMinima"
            />
          </div>
        </div>

        <!-- FECHAS Y HORAS (Modo Horas) -->
        <div v-else class="fila-fechas">
          <div class="grupo-input">
            <label for="fecha_dia" class="etiqueta">Día</label>
            <input 
              type="date" 
              id="fecha_dia" 
              v-model="form.fecha_inicio" 
              class="input-fecha"
              required
              :min="fechaMinima"
              @change="form.fecha_fin = form.fecha_inicio" 
            />
          </div>

          <div class="grupo-input">
            <label for="hora_inicio" class="etiqueta">Hora Inicio</label>
            <input 
              type="time" 
              id="hora_inicio" 
              v-model="form.hora_inicio" 
              class="input-fecha"
              required
            />
          </div>

          <div class="grupo-input">
            <label for="hora_fin" class="etiqueta">Hora Fin</label>
            <input 
              type="time" 
              id="hora_fin" 
              v-model="form.hora_fin" 
              class="input-fecha"
              required
            />
          </div>
        </div>

        <!-- RESUMEN -->
        <div class="resumen-dias">
          <span class="texto-resumen">Resumen:</span>
          <span class="numero-dias">{{ resumenCalculado }}</span>
        </div>

        <div v-if="error" class="mensaje-error">
          {{ error }}
        </div>

        <div class="acciones-form">
          <button type="button" @click="$router.back()" class="btn-cancelar">
            Cancelar
          </button>
          <button type="submit" class="btn-enviar" :disabled="cargando || !esValido">
            {{ cargando ? 'Enviando...' : 'Confirmar Solicitud' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/axios.js'

const router = useRouter()
const cargando = ref(false)
const error = ref(null)

const form = ref({
  tipo: 'vacaciones',
  duracion_tipo: 'dias', // 'dias' | 'horas'
  fecha_inicio: '',
  fecha_fin: '',
  hora_inicio: '',
  hora_fin: '',
  motivo: '',
})

// Fecha mínima es "hoy"
const fechaMinima = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Validación básica
const esValido = computed(() => {
  if (form.value.duracion_tipo === 'dias') {
    return form.value.fecha_inicio && form.value.fecha_fin
  } else {
    return form.value.fecha_inicio && form.value.hora_inicio && form.value.hora_fin
  }
})

// Cálculo de Resumen
const resumenCalculado = computed(() => {
  if (form.value.duracion_tipo === 'dias') {
    if (!form.value.fecha_inicio || !form.value.fecha_fin) return '-'
    
    const inicio = new Date(form.value.fecha_inicio)
    const fin = new Date(form.value.fecha_fin)
    
    if (fin < inicio) return 'Fecha fin inválida'
    
    const diferenciaTiempo = Math.abs(fin - inicio)
    const dias = Math.ceil(diferenciaTiempo / (1000 * 60 * 60 * 24)) + 1
    return `${dias} día(s)`
  } else {
    if (!form.value.hora_inicio || !form.value.hora_fin) return '-'
    return `${form.value.hora_inicio} a ${form.value.hora_fin} hrs`
  }
})

const enviarSolicitud = async () => {
  cargando.value = true
  error.value = null

  try {
    // 1. Cálculo de Días Solicitados (Necesario para el campo 'dias_solicitados' en la DB)
    let diasSolicitados = null;
    let motivo = form.value.motivo || 'Permiso solicitado sin motivo detallado.';

    if (form.value.duracion_tipo === 'dias') {
        const inicio = new Date(form.value.fecha_inicio);
        const fin = new Date(form.value.fecha_fin);
        
        // Aseguramos que la fecha fin no sea anterior a inicio y que sean válidas.
        if (fin >= inicio) {
            const diferenciaTiempo = Math.abs(fin - inicio);
            // Días incluyendo el día de inicio (+1)
            diasSolicitados = Math.ceil(diferenciaTiempo / (1000 * 60 * 60 * 24)) + 1;
        }
    }
    
    // 2. Preparar el Payload (Alineado con el DTO y Validaciones de Laravel)
    const payload = {
      tipo: form.value.tipo,
      fecha_inicio: form.value.fecha_inicio,
      
      // Si es por horas, fecha_fin debe ser igual a fecha_inicio. Si es por días, usa la fecha_fin del form.
      fecha_fin: form.value.duracion_tipo === 'dias' ? form.value.fecha_fin : form.value.fecha_inicio, 
      
      es_por_horas: form.value.duracion_tipo === 'horas', // Boolean
      
      // Backend requiere formato HH:mm:ss (por eso añadimos ':00')
      hora_inicio: form.value.duracion_tipo === 'horas' ? `${form.value.hora_inicio}:00` : null, 
      hora_fin: form.value.duracion_tipo === 'horas' ? `${form.value.hora_fin}:00` : null,
      
      dias_solicitados: diasSolicitados, // Nuevo campo requerido por la DB
      motivo: motivo, // Campo que puedes añadir al formulario o dejar con este valor por defecto
    }

    // 3. Ejecutar la Petición (Endpoint corregido a /solicitudes)
    await apiClient.post('/solicitudes', payload) 

    alert('¡Solicitud enviada correctamente!')
    router.push('/solicitudes') // Redirigir al historial

  } catch (err) {
    console.error("Error detallado:", err)
    if (err.response && err.response.data) {
      const mensajeBackend = err.response.data.message || ''
      const erroresValidacion = err.response.data.errors 
        ? Object.values(err.response.data.errors).flat().join(', ') 
        : ''
      
      // Mostrar errores de validación del backend si existen
      error.value = `No se pudo enviar: ${mensajeBackend} ${erroresValidacion}`.trim()
    } else {
      error.value = "Error de conexión. Intente nuevamente más tarde."
    }
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
.contenedor-formulario {
  max-width: 600px;
  margin: 0 auto;
  padding-bottom: 2rem;
}

.encabezado-form {
  text-align: center;
  margin-bottom: 2rem;
}
.titulo-pagina { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; }
.subtitulo { color: var(--color-texto-secundario); }

.tarjeta-form {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  border: 1px solid var(--color-borde);
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}

.formulario { display: flex; flex-direction: column; gap: 1.5rem; }

.grupo-input { display: flex; flex-direction: column; gap: 0.5rem; }
.etiqueta { font-weight: 600; font-size: 0.9rem; color: var(--color-texto-principal); }

/* Selector de Tipo (Radio Cards) */
.opciones-tipo { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 0.5rem; }

.radio-card {
  border: 1px solid var(--color-borde);
  border-radius: 8px;
  padding: 0.75rem;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  transition: all 0.2s;
  text-align: center;
}
.radio-card input { display: none; } 

.radio-card:hover { background-color: #F9FAFB; }
.radio-card.activo {
  border-color: var(--color-azul-institucional);
  background-color: #EFF6FF;
  color: var(--color-azul-institucional);
}
.radio-card .icono { font-size: 1.25rem; }
.radio-card .texto { font-weight: 500; font-size: 0.8rem; }

/* Toggle Duración */
.toggle-duracion {
  display: flex;
  background-color: #F3F4F6;
  padding: 4px;
  border-radius: 8px;
}
.btn-toggle {
  flex: 1;
  border: none;
  background: transparent;
  padding: 0.5rem;
  border-radius: 6px;
  font-weight: 500;
  color: var(--color-texto-secundario);
  cursor: pointer;
  transition: all 0.2s;
}
.btn-toggle.activo {
  background-color: white;
  color: var(--color-azul-institucional);
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* Fechas */
.fila-fechas { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
/* Si hay 3 inputs (fecha, hora inicio, hora fin) */
.fila-fechas:has(> :nth-child(3)) { grid-template-columns: 1fr 1fr 1fr; }

.input-fecha {
  padding: 0.75rem;
  border: 1px solid var(--color-borde);
  border-radius: 8px;
  font-size: 1rem;
  width: 100%;
}
.input-fecha:focus {
  outline: 2px solid var(--color-azul-institucional);
  border-color: var(--color-azul-institucional);
}

/* Resumen */
.resumen-dias {
  background-color: #F3F4F6;
  padding: 1rem;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 500;
}
.numero-dias { color: var(--color-azul-institucional); font-weight: 700; }

/* Botones */
.acciones-form { display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; margin-top: 1rem; }

.btn-cancelar {
  padding: 0.8rem;
  border: 1px solid var(--color-borde);
  background: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
}

.btn-enviar {
  background-color: var(--color-azul-institucional);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-enviar:disabled { background-color: #9CA3AF; cursor: not-allowed; }
.btn-enviar:hover:not(:disabled) { background-color: #1E40AF; }

.mensaje-error { color: #DC2626; background-color: #FEF2F2; padding: 0.75rem; border-radius: 6px; font-size: 0.9rem; }

@media (max-width: 640px) {
  .fila-fechas { grid-template-columns: 1fr !important; }
}
</style>
