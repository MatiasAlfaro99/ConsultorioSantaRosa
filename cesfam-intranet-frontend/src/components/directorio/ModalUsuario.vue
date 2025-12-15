<template>
  <div v-if="mostrar" class="modal-overlay">
    <div class="modal-content">
      <h3>{{ usuarioEditar ? 'Editar Funcionario' : 'Nuevo Funcionario' }}</h3>
      
      <form @submit.prevent="guardar">
        <div class="form-grid">
          <div class="campo">
            <label>Nombre Completo</label>
            <input v-model="form.name" required type="text" placeholder="Ej: Juan Pérez" />
          </div>
          
          <div class="campo">
            <label>Email Institucional</label>
            <input 
              v-model="form.email" 
              required 
              type="email" 
              placeholder="nombre@cesfam.cl"
              title="Ingrese un correo válido"
            />
          </div>

          <div class="campo">
            <label>Contraseña</label>
            <input 
              v-model="form.password" 
              :required="!usuarioEditar" 
              type="password" 
              placeholder="Mínimo 6 caracteres" 
              minlength="6"
            />
          </div>

          <div class="campo">
            <label>RUT</label>
            <input 
              v-model="form.rut" 
              type="text" 
              placeholder="12.345.678-9" 
              maxlength="12"
              @input="aplicarFormatoRut"
            />
            <small class="ayuda">Formato: 12.345.678-9</small>
          </div>

          <div class="campo">
            <label>Cargo</label>
            <input v-model="form.cargo" type="text" placeholder="Ej: Médico, Administrativo" />
          </div>

          <div class="campo">
            <label>Rol en Sistema</label>
            <select v-model="form.role" required>
              <option value="admin">Administrador</option>
              <option value="funcionario">Funcionario</option>
              <option value="jefatura">Jefatura</option>
              <option value="subdireccion">Subdirección</option>
              <option value="direccion">Dirección</option>
            </select>
          </div>

          <div class="campo">
            <label>Estado de la Cuenta</label>
            <label class="switch-container">
              <input type="checkbox" v-model="form.is_active">
              <span class="slider"></span>
              <span class="switch-label">
                {{ form.is_active ? 'Habilitado' : 'Bloqueado' }}
              </span>
            </label>
            <small class="ayuda" v-if="!form.is_active">
              El usuario no podrá iniciar sesión.
            </small>
          </div>

        </div> <div class="acciones">
          <button type="button" @click="$emit('cerrar')" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-guardar">Guardar</button>
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
  usuarioEditar: Object
})
const emit = defineEmits(['cerrar', 'guardado'])

// Inicializamos is_active en true por defecto
const form = ref({ 
  name: '', 
  email: '', 
  password: '', 
  rut: '', 
  cargo: '', 
  role: 'funcionario',
  is_active: true 
})

watch(() => props.usuarioEditar, (newVal) => {
  if (newVal) {
    form.value = { ...newVal, password: '' }
    // Aseguramos conversión a booleano
    form.value.is_active = Boolean(newVal.is_active)
  } else {
    form.value = { 
      name: '', email: '', password: '', rut: '', cargo: '', role: 'funcionario', 
      is_active: true 
    }
  }
})

const aplicarFormatoRut = (event) => {
  let valor = event.target.value.replace(/[^0-9kK]/g, ''); 
  if (valor.length > 1) {
    const dv = valor.slice(-1);
    let cuerpo = valor.slice(0, -1);
    cuerpo = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    valor = `${cuerpo}-${dv}`;
  }
  form.value.rut = valor; 
}

const guardar = async () => {
  try {
    if (props.usuarioEditar) {
      // Ruta corregida en Inglés
      await apiClient.put(`/users/${props.usuarioEditar.id}`, form.value)
    } else {
      // Ruta corregida en Inglés
      await apiClient.post('/users', form.value)
    }
    emit('guardado')
    emit('cerrar')
    alert('Guardado correctamente')
  } catch (error) {
    console.error(error)
    const mensaje = error.response?.data?.message || 'Error al guardar. Revise los datos.'
    alert(mensaje)
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center;
  z-index: 1000;
}
.modal-content {
  background: white; padding: 2rem; border-radius: 12px; width: 600px; /* Un poco más ancho para que quepa bien */
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
h3 { margin-bottom: 1.5rem; font-size: 1.2rem; font-weight: 700; color: #1F2937; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
.campo { display: flex; flex-direction: column; gap: 0.3rem; }

label { font-size: 0.85rem; font-weight: 600; color: #4B5563; }

input, select { 
  padding: 0.6rem; 
  border: 1px solid #E5E7EB; 
  border-radius: 6px; 
  font-size: 0.9rem; 
  outline: none;
  transition: border-color 0.2s;
}
input:focus, select:focus { border-color: #0B74DE; }

.ayuda { font-size: 0.75rem; color: #9CA3AF; }

.acciones { display: flex; justify-content: flex-end; gap: 1rem; }
.btn-cancelar { background: transparent; border: 1px solid #E5E7EB; padding: 0.6rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-cancelar:hover { background-color: #F9FAFB; }

.btn-guardar { background: #0B74DE; color: white; border: none; padding: 0.6rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-guardar:hover { background-color: #1E40AF; }

/* ESTILOS SWITCH TOGGLE */
.switch-container {
  display: flex; align-items: center; gap: 0.8rem; cursor: pointer; margin-top: 0.5rem;
  height: 38px; /* Altura fija para alinear con inputs */
}
.switch-container input { display: none; } 

.slider {
  position: relative; width: 44px; height: 24px;
  background-color: #E5E7EB; border-radius: 34px; transition: .4s;
  flex-shrink: 0;
}
.slider:before {
  position: absolute; content: ""; height: 18px; width: 18px;
  left: 3px; bottom: 3px; background-color: white;
  border-radius: 50%; transition: .4s;
}

input:checked + .slider { background-color: #10B981; } 
input:checked + .slider:before { transform: translateX(20px); }

.switch-label { font-size: 0.9rem; font-weight: 500; color: #374151; }
</style>