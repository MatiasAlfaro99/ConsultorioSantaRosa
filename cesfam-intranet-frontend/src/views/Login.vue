<template>
  <div class="contenedor-login">
    <div class="formulario-caja">
      <img :src="logoCesfam" alt="Logo CESFAM" class="logo-login" />
      <h1 class="titulo-login">Intranet Administrativa</h1>

      <form @submit.prevent="manejarLogin" class="formulario-login">
        <div class="campo-form">
          <label for="email">Email</label>
          <input type="email" id="email" v-model="email" required />
        </div>
        <div class="campo-form">
          <label for="password">Contraseña</label>
          <input type="password" id="password" v-model="password" required />
        </div>

        <div v-if="errorLogin" class="error-login">
          {{ errorLogin }}
        </div>

        <button type="submit" class="boton-login" :disabled="cargando">
          {{ cargando ? 'Ingresando...' : 'Ingresar' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/store/auth'
import logoCesfam from '@/assets/logo-cesfam.png'

const email = ref('')
const password = ref('')
const errorLogin = ref(null)
const cargando = ref(false) // Feedback visual para el usuario

const authStore = useAuthStore()

const manejarLogin = async () => {
  cargando.value = true
  errorLogin.value = null
  
  try {
    // Llamamos a la acción del Store
    await authStore.iniciarSesion({ 
      email: email.value, 
      password: password.value 
    })
    // Si pasa, el router.push ocurre dentro del store
  } catch (error) {
    // MEJORA: Priorizamos el mensaje que envía Laravel
    if (error.response && error.response.data && error.response.data.message) {
        errorLogin.value = error.response.data.message; 
    } else if (error.message) {
        errorLogin.value = error.message; // Fallback al error técnico
    } else {
        errorLogin.value = "Ocurrió un error inesperado. Intente nuevamente.";
    }
  } finally {
    cargando.value = false
  }
}
</script>

<style>
.contenedor-login {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: var(--color-fondo);
}
.formulario-caja {
  background-color: var(--color-tarjeta);
  padding: 2.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  width: 100%;
  max-width: 400px;
  text-align: center;
}
.logo-login {
  width: 150px;
  margin-bottom: 1.5rem;
}
.titulo-login {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 2rem;
  color: var(--color-texto-principal);
}
.formulario-login {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
.campo-form {
  text-align: left;
}
.campo-form label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}
.campo-form input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--color-borde);
  border-radius: 8px;
  font-size: 1rem;
}
.boton-login {
  background-color: var(--color-azul-institucional);
  color: white;
  padding: 0.75rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
}
.boton-login:disabled {
  background-color: var(--color-texto-secundario);
}
.error-login {
  color: red;
  font-size: 0.875rem;
}
</style>
