<template>
  <div class="dashboard-container">
    <header class="dashboard-header">
      <div class="header-content">
        <h1 class="saludo-usuario" v-if="authStore.usuario">
          Hola, <span class="nombre-destacado">{{ authStore.usuario.name }}</span>
        </h1>
        <p class="fecha-actual">{{ fechaActual }}</p>
      </div>
    </header>
    
    <div v-if="cargando" class="contenedor-carga">
      <div class="spinner"></div>
      <p>Cargando...</p>
    </div>

    <div v-else class="dashboard-content">
      
      <section class="section-actions">
        <AccesosRapidos @abrirModalSubida="abrirModal" />
      </section>

      <section class="section-status">
        <InformacionPersonal 
          :saldo="miSaldo" 
          :conteo-solicitudes="conteoSolicitudes"
        />
      </section>

      <section class="section-modules">
        <ModulosPrincipales />
      </section>

      <section class="section-lists">
        <div class="list-wrapper">
          <ComunicadosRecientes :comunicados="comunicadosRecientes" />
        </div>
        <div class="list-wrapper">
          <DocumentosDestacados :documentos="documentosDestacados" />
        </div>
      </section>

    </div>

    <ModalSubirDocumento 
      :mostrar="mostrarModal"
      @cerrar="cerrarModal"
      @subidaExitosa="refrescarDatos"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/store/auth.js'
import apiClient from '@/api/axios.js'
import InformacionPersonal from '@/components/dashboard/InformacionPersonal.vue'
import ModulosPrincipales from '@/components/dashboard/ModulosPrincipales.vue'
import AccesosRapidos from '@/components/dashboard/AccesosRapidos.vue'
import ComunicadosRecientes from '@/components/dashboard/ComunicadosRecientes.vue'
import DocumentosDestacados from '@/components/dashboard/DocumentosDestacados.vue'
import ModalSubirDocumento from '@/components/documentos/ModalSubirDocumento.vue'

const authStore = useAuthStore()
const miSaldo = ref(null)
const comunicadosRecientes = ref([])
const documentosDestacados = ref([])
const conteoSolicitudes = ref(0)
const cargando = ref(true)
const mostrarModal = ref(false)

const fechaActual = computed(() => {
  const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  const fecha = new Date().toLocaleDateString('es-CL', opciones)
  return fecha.charAt(0).toUpperCase() + fecha.slice(1)
})

const cargarDatosDashboard = async () => {
  try {
    const [saldoRes, solicitudesRes, comunicadosRes, documentosRes] = await Promise.all([
      apiClient.get('/mi-saldo'),
      apiClient.get('/solicitudes-permiso'),
      apiClient.get('/comunicados'),
      apiClient.get('/documents')
    ])

    miSaldo.value = saldoRes.data
    comunicadosRecientes.value = comunicadosRes.data.slice(0, 3)
    documentosDestacados.value = documentosRes.data.slice(0, 3)
    
    const pendientes = solicitudesRes.data.filter(s => 
      s.estado === 'pendiente_jefatura' || s.estado === 'pendiente_direccion'
    )
    conteoSolicitudes.value = pendientes.length

  } catch (error) {
    console.error("Error cargando dashboard:", error)
  } finally {
    cargando.value = false
  }
}

const abrirModal = () => { mostrarModal.value = true }
const cerrarModal = () => { mostrarModal.value = false }
const refrescarDatos = () => { cargarDatosDashboard() }

onMounted(cargarDatosDashboard)
</script>

<style>
:root {
  --gap-layout: 2rem;
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding-bottom: 4rem;
}

/* HEADER */
.dashboard-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--color-borde);
}

.saludo-usuario {
  font-size: 1.5rem;
  font-weight: 400;
  color: var(--color-texto-principal);
  margin-bottom: 0.25rem;
}

.nombre-destacado { font-weight: 700; color: var(--color-azul-institucional); }
.fecha-actual { color: var(--color-texto-secundario); font-size: 0.95rem; }

/* CONTENIDO VERTICAL */
.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: var(--gap-layout);
}

/* LISTAS EN GRID (Desktop) */
.section-lists {
  display: grid;
  gap: var(--gap-layout);
}

@media (min-width: 1024px) {
  .section-lists {
    grid-template-columns: 1fr 1fr;
    align-items: start;
  }
}
</style>
