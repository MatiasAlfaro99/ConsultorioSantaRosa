<template>
  <div class="contenedor-perfil">
    <h1 class="titulo-pagina">Mi Perfil</h1>

    <div class="grid-perfil">
      
      <div class="tarjeta-identidad">
        <div class="avatar-wrapper">
          <img :src="previewFoto || usuario.profile_photo_url" alt="Foto de perfil" class="avatar-img" />
          
          <label for="input-foto" class="btn-cambiar-foto">
            <CameraIcon class="icono-camara" />
          </label>
          <input type="file" id="input-foto" @change="manejarSeleccionFoto" accept="image/*" hidden />
        </div>

        <h2 class="nombre-usuario">{{ usuario.name }}</h2>
        <p class="cargo-usuario">{{ usuario.cargo || 'Funcionario' }}</p>
        <p class="rol-usuario">{{ usuario.role }}</p>

        <div class="divider"></div>

        <button class="btn-upload" @click="triggerInputFile">
          Cambiar Foto
        </button>
      </div>

      <div class="tarjeta-formulario">
        <div class="encabezado-card">
          <h3>Información Personal</h3>
          <p>Actualiza tus datos de contacto y contraseña.</p>
        </div>

        <form @submit.prevent="actualizarPerfil">
          <div class="form-grid">
            <div class="campo">
              <label>Nombre Completo</label>
              <div class="input-icon-wrapper">
                <UserIcon class="icono-input" />
                <input v-model="form.name" type="text" required />
              </div>
            </div>

            <div class="campo">
              <label>Correo Electrónico</label>
              <div class="input-icon-wrapper">
                <EnvelopeIcon class="icono-input" />
                <input v-model="form.email" type="email" required />
              </div>
            </div>

            <div class="campo">
              <label>Teléfono / Anexo</label>
              <div class="input-icon-wrapper">
                <PhoneIcon class="icono-input" />
                <input v-model="form.telefono" type="text" placeholder="+569..." />
              </div>
            </div>

            <div class="campo">
              <label>Cargo (No editable)</label>
              <div class="input-icon-wrapper disabled">
                <BriefcaseIcon class="icono-input" />
                <input :value="usuario.cargo" type="text" disabled />
              </div>
            </div>
          </div>

          <div class="divider"></div>

          <div class="encabezado-card">
            <h3>Seguridad</h3>
            <p>Deja en blanco si no deseas cambiar tu contraseña.</p>
          </div>

          <div class="form-grid">
            <div class="campo">
              <label>Nueva Contraseña</label>
              <input v-model="form.password" type="password" placeholder="Mínimo 6 caracteres" />
            </div>
            <div class="campo">
              <label>Confirmar Contraseña</label>
              <input v-model="form.password_confirmation" type="password" placeholder="Repite la contraseña" />
            </div>
          </div>

          <div class="acciones-footer">
            <button type="submit" class="btn-guardar" :disabled="cargando">
              {{ cargando ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth.js'
import apiClient from '@/api/axios.js'
import { CameraIcon, UserIcon, EnvelopeIcon, PhoneIcon, BriefcaseIcon } from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const usuario = ref({})
const previewFoto = ref(null)
const archivoFoto = ref(null)
const cargando = ref(false)

const form = ref({
  name: '',
  email: '',
  telefono: '',
  password: '',
  password_confirmation: ''
})

onMounted(async () => {
  // Nos aseguramos de tener los datos más frescos
  await authStore.validarTokenAlCargar()
  usuario.value = authStore.usuario
  
  // Llenar formulario
  form.value.name = usuario.value.name
  form.value.email = usuario.value.email
  form.value.telefono = usuario.value.telefono || ''
})

const triggerInputFile = () => {
  document.getElementById('input-foto').click()
}

const manejarSeleccionFoto = (event) => {
  const file = event.target.files[0]
  if (file) {
    archivoFoto.value = file
    // Crear URL temporal para previsualizar
    previewFoto.value = URL.createObjectURL(file)
  }
}

const actualizarPerfil = async () => {
  cargando.value = true
  
  // Usamos FormData porque vamos a enviar archivos
  const formData = new FormData()
  formData.append('name', form.value.name)
  formData.append('email', form.value.email)
  formData.append('telefono', form.value.telefono)
  
  if (archivoFoto.value) {
    formData.append('foto', archivoFoto.value)
  }
  
  if (form.value.password) {
    if (form.value.password !== form.value.password_confirmation) {
      alert("Las contraseñas no coinciden")
      cargando.value = false
      return
    }
    formData.append('password', form.value.password)
    formData.append('password_confirmation', form.value.password_confirmation)
  }

  try {
    const respuesta = await apiClient.post('/perfil', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    // Actualizar el store con los nuevos datos (y la nueva foto)
    authStore.usuario = respuesta.data.user
    // Actualizar localmente
    usuario.value = respuesta.data.user
    
    alert('Perfil actualizado correctamente')
    // Limpiar password
    form.value.password = ''
    form.value.password_confirmation = ''
    
  } catch (error) {
    console.error(error)
    alert('Error al actualizar el perfil. Verifica los datos.')
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
.contenedor-perfil {
  max-width: 1000px;
  margin: 0 auto;
  padding-bottom: 3rem;
}

.titulo-pagina {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--color-texto-principal);
  margin-bottom: 2rem;
}

.grid-perfil {
  display: grid;
  gap: 2rem;
  grid-template-columns: 1fr;
}

/* COLUMNA IZQUIERDA */
.tarjeta-identidad {
  background: white;
  border-radius: 12px;
  border: 1px solid var(--color-borde);
  padding: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  height: fit-content;
}

.avatar-wrapper {
  position: relative;
  margin-bottom: 1rem;
}

.avatar-img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #EFF6FF;
}

.btn-cambiar-foto {
  position: absolute;
  bottom: 0;
  right: 0;
  background-color: var(--color-azul-institucional);
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: 2px solid white;
  transition: transform 0.2s;
}
.btn-cambiar-foto:hover { transform: scale(1.1); }
.icono-camara { width: 20px; height: 20px; }

.nombre-usuario { font-size: 1.25rem; font-weight: 700; color: var(--color-texto-principal); margin-bottom: 0.25rem; }
.cargo-usuario { color: var(--color-texto-secundario); font-weight: 500; margin-bottom: 0.25rem; }
.rol-usuario { 
  background-color: #F3F4F6; padding: 2px 10px; border-radius: 20px; 
  font-size: 0.75rem; text-transform: uppercase; font-weight: 600; color: #4B5563;
}

.divider { width: 100%; height: 1px; background-color: #E5E7EB; margin: 1.5rem 0; }

.btn-upload {
  background-color: white; border: 1px solid var(--color-borde);
  padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 500; cursor: pointer;
  color: var(--color-texto-principal); transition: background 0.2s;
}
.btn-upload:hover { background-color: #F9FAFB; border-color: #D1D5DB; }

/* COLUMNA DERECHA */
.tarjeta-formulario {
  background: white;
  border-radius: 12px;
  border: 1px solid var(--color-borde);
  padding: 2rem;
}

.encabezado-card h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.25rem; }
.encabezado-card p { font-size: 0.9rem; color: var(--color-texto-secundario); margin-bottom: 1.5rem; }

.form-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: 1fr;
}

.campo label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }

.input-icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}
.input-icon-wrapper input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem; /* Espacio para icono */
  border: 1px solid var(--color-borde);
  border-radius: 8px;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s;
}
.input-icon-wrapper input:focus { border-color: var(--color-azul-institucional); }
.input-icon-wrapper.disabled input { background-color: #F9FAFB; cursor: not-allowed; }

.icono-input {
  position: absolute;
  left: 0.75rem;
  width: 20px;
  height: 20px;
  color: #9CA3AF;
}

.acciones-footer { display: flex; justify-content: flex-end; margin-top: 1rem; }

.btn-guardar {
  background-color: var(--color-azul-institucional);
  color: white;
  border: none;
  padding: 0.8rem 2rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-guardar:hover { background-color: #1E40AF; }
.btn-guardar:disabled { background-color: #93C5FD; cursor: not-allowed; }

/* RESPONSIVE DESKTOP */
@media (min-width: 1024px) {
  .grid-perfil {
    grid-template-columns: 300px 1fr; /* Columna izquierda fija, derecha flexible */
  }
  .form-grid {
    grid-template-columns: 1fr 1fr; /* Campos en 2 columnas */
  }
}
</style>
