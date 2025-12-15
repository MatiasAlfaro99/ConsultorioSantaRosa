<template>
  <div class="contenedor-encabezado">
    <div class="logo-movil">
      <img :src="logoCesfam" alt="Logo CESFAM" />
    </div>
    
    <div class="espaciador"></div>

    <div class="grupo-usuario" v-if="authStore.usuario">
      
      <div class="info-perfil" @click="irAPerfil" title="Ir a mi Perfil">
        <img 
          :src="authStore.usuario.profile_photo_url" 
          class="avatar-header" 
          alt="Avatar"
        />
        
        <div class="textos-perfil desktop-only">
          <span class="nombre-corto">{{ authStore.usuario.name }} - </span>
          <span class="rol-usuario">{{ authStore.usuario.role }}</span>
        </div>
      </div>

      <div class="separador-vertical"></div>

      <button class="boton-salir" @click="manejarCerrarSesion" title="Cerrar Sesión">
        <ArrowRightOnRectangleIcon class="icono-salir" />
      </button>

    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
import logoCesfam from '@/assets/logo-cesfam.png'
import { useAuthStore } from '@/store/auth.js'

const authStore = useAuthStore()
const router = useRouter()

const irAPerfil = () => {
  router.push('/perfil')
}

const manejarCerrarSesion = () => {
  if(confirm("¿Desea cerrar sesión?")) {
    authStore.cerrarSesion()
  }
}
</script>

<style scoped>
.contenedor-encabezado {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  height: 100%;
}

.logo-movil { display: block; }
.logo-movil img { height: 32px; }

.espaciador { flex-grow: 1; }

.grupo-usuario {
  display: flex;
  align-items: center;
  gap: 1rem;
  background-color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 50px; /* Diseño tipo píldora */
  border: 1px solid transparent;
  transition: all 0.2s;
}

.grupo-usuario:hover {
  border-color: #E5E7EB;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* ZONA PERFIL */
.info-perfil {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.avatar-header {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #EFF6FF;
}

.textos-perfil {
  display: flex;
  flex-direction: column;
  text-align: right;
}

.nombre-corto { font-size: 0.9rem; font-weight: 600; color: var(--color-texto-principal); line-height: 1.2; }
.rol-usuario { font-size: 0.7rem; color: var(--color-texto-secundario); text-transform: uppercase; letter-spacing: 0.05em; }

.separador-vertical { width: 1px; height: 24px; background-color: #E5E7EB; }

/* BOTÓN SALIR */
.boton-salir {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 6px;
  border-radius: 50%;
  color: var(--color-texto-secundario);
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.boton-salir:hover {
  background-color: #FEF2F2;
  color: #EF4444; /* Rojo al pasar el mouse */
}

.icono-salir { width: 22px; height: 22px; }

/* RESPONSIVE */
.desktop-only { display: none; }

@media (min-width: 768px) {
  .logo-movil { display: none; }
  .desktop-only { display: block; }
}
</style>