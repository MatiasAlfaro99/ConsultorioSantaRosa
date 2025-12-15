<template>
  <div class="dashboard-contenedor">
    <header class="dashboard-header">
      <div class="header-content">
        <h1 class="saludo">Bienvenido, {{ nombreUsuario }}</h1>
        <p class="subtitulo">Intranet del CESFAM Santa Rosa</p>
      </div>
      <div class="header-meta">
        <span class="fecha-actual">{{ fechaHoy }}</span>
      </div>
    </header>

    <div class="dashboard-grid">
      <main class="columna-principal">
        <!-- Pasamos el usuario completo (que ya trae los Appends) como prop 'saldo' -->
        <InformacionPersonal :saldo="authStore.usuario" />

        <section class="seccion-dashboard">
          <h2 class="titulo-seccion">Acciones Rápidas</h2>
          <AccesosRapidos @crear-comunicado="abrirModal" />
        </section>

        <ModulosPrincipales />
      </main>

      <aside class="columna-lateral">
        <ComunicadosRecientes ref="widgetComunicados" />
      </aside>
    </div>

    <!-- Modal Nuevo Comunicado -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="mostrarModal" class="modal-overlay" @click.self="cerrarModal">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal-title-block">
                <div class="modal-icon">
                  <MegaphoneIcon class="icon-modal" />
                </div>
                <h2>Nuevo Comunicado</h2>
              </div>
              <button class="btn-cerrar" @click="cerrarModal" aria-label="Cerrar">
                <XMarkIcon class="icon-close" />
              </button>
            </div>
            
            <form @submit.prevent="guardarComunicado" class="formulario-modal">
              <div class="campo">
                <label for="titulo-comunicado">Título</label>
                <input 
                  id="titulo-comunicado"
                  v-model="formulario.titulo" 
                  type="text" 
                  placeholder="Ej: Suspensión de servicios..." 
                  required 
                  class="input-form"
                >
              </div>
              <div class="campo">
                <label for="contenido-comunicado">Contenido</label>
                <textarea 
                  id="contenido-comunicado"
                  v-model="formulario.contenido" 
                  rows="5" 
                  placeholder="Detalle del anuncio..." 
                  required 
                  class="input-form"
                ></textarea>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn-cancelar" @click="cerrarModal">
                  Cancelar
                </button>
                <button type="submit" class="btn-guardar" :disabled="guardando">
                  <span v-if="guardando" class="btn-loading">
                    <span class="spinner-btn"></span>
                    Publicando...
                  </span>
                  <span v-else>Publicar Comunicado</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, ref, reactive } from 'vue'
import { useAuthStore } from '@/store/auth.js'
import apiClient from '@/api/axios.js'
import { MegaphoneIcon, XMarkIcon } from '@heroicons/vue/24/outline'

// Componentes
import InformacionPersonal from '@/components/dashboard/InformacionPersonal.vue'
import AccesosRapidos from '@/components/dashboard/AccesosRapidos.vue'
import ModulosPrincipales from '@/components/dashboard/ModulosPrincipales.vue'
import ComunicadosRecientes from '@/components/dashboard/ComunicadosRecientes.vue'

const authStore = useAuthStore()
const nombreUsuario = computed(() => authStore.usuario?.name || 'Colega')
const fechaHoy = computed(() => new Date().toLocaleDateString('es-CL', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))

// --- Lógica del Modal ---
const mostrarModal = ref(false)
const guardando = ref(false)
const widgetComunicados = ref(null)
const formulario = reactive({ titulo: '', contenido: '' })

const abrirModal = () => {
  formulario.titulo = ''
  formulario.contenido = ''
  mostrarModal.value = true
}

const cerrarModal = () => {
  mostrarModal.value = false
}

const guardarComunicado = async () => {
  guardando.value = true
  try {
    await apiClient.post('/comunicados', formulario)
    alert("Comunicado publicado en el Muro Principal.")
    cerrarModal()
    
    if(widgetComunicados.value && widgetComunicados.value.cargarDatos) {
      widgetComunicados.value.cargarDatos()
    } else {
      window.location.reload()
    }

  } catch (error) {
    console.error(error)
    alert("Error al publicar")
  } finally {
    guardando.value = false
  }
}
</script>

<style scoped>
/* ===== LAYOUT PRINCIPAL ===== */
.dashboard-contenedor { 
  max-width: 1280px; 
  margin: 0 auto; 
  padding: 2rem; 
}

/* ===== HEADER ===== */
.dashboard-header { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  margin-bottom: 2.5rem; 
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #FAFBFC 0%, #F1F5F9 100%);
  border-radius: 16px;
  border: 1px solid #E2E8F0;
}

.header-content { display: flex; flex-direction: column; gap: 0.25rem; }

.saludo { 
  font-size: 1.75rem; 
  font-weight: 700; 
  color: #1E293B; 
  margin: 0; 
  letter-spacing: -0.02em;
}

.subtitulo { 
  color: #64748B; 
  margin: 0; 
  font-size: 0.95rem;
  font-weight: 500;
}

.header-meta { text-align: right; }

.fecha-actual { 
  font-weight: 500; 
  color: #64748B; 
  text-transform: capitalize; 
  font-size: 0.9rem;
}

/* ===== GRID PRINCIPAL ===== */
.dashboard-grid { 
  display: grid; 
  grid-template-columns: 1fr; 
  gap: 2.5rem; 
}

.columna-principal { 
  display: flex; 
  flex-direction: column; 
  gap: 2.5rem; 
}

.columna-lateral { 
  display: flex; 
  flex-direction: column; 
  gap: 1.5rem; 
}

/* ===== SECTION TITLES ===== */
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

/* ===== MODAL ===== */
.modal-overlay { 
  position: fixed; 
  inset: 0; 
  background: rgba(15, 23, 42, 0.6); 
  backdrop-filter: blur(4px);
  display: flex; 
  justify-content: center; 
  align-items: center; 
  z-index: 1000;
  padding: 1rem;
}

.modal-content { 
  background: white; 
  padding: 0;
  border-radius: 16px; 
  width: 100%;
  max-width: 520px; 
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.2);
  overflow: hidden;
}

.modal-header { 
  display: flex; 
  justify-content: space-between; 
  align-items: center;
  padding: 1.5rem 2rem;
  background: #F8FAFC;
  border-bottom: 1px solid #E2E8F0;
}

.modal-title-block {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.modal-icon {
  width: 40px;
  height: 40px;
  background: var(--color-azul-institucional, #0B74DE);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-modal { width: 22px; height: 22px; color: white; }

.modal-header h2 { 
  font-size: 1.25rem; 
  font-weight: 700; 
  margin: 0; 
  color: #1E293B;
}

.btn-cerrar { 
  background: none; 
  border: none; 
  cursor: pointer; 
  padding: 8px;
  border-radius: 8px;
  color: #64748B;
  transition: all 0.2s;
}

.btn-cerrar:hover { 
  background: #E2E8F0; 
  color: #1E293B;
}

.icon-close { width: 20px; height: 20px; }

.formulario-modal { 
  display: flex; 
  flex-direction: column; 
  gap: 1.25rem;
  padding: 2rem;
}

.campo { 
  display: flex; 
  flex-direction: column; 
  gap: 0.5rem; 
}

.campo label { 
  font-weight: 600; 
  font-size: 0.875rem; 
  color: #374151; 
}

.input-form { 
  padding: 0.875rem 1rem; 
  border: 1px solid #E2E8F0; 
  border-radius: 10px;
  font-size: 0.95rem;
  font-family: inherit;
  transition: all 0.2s;
  background: #FAFBFC;
}

.input-form:focus { 
  outline: none; 
  border-color: var(--color-azul-institucional, #0B74DE);
  background: white;
  box-shadow: 0 0 0 3px rgba(11, 116, 222, 0.1);
}

.input-form::placeholder { color: #94A3B8; }

.modal-footer { 
  display: flex; 
  justify-content: flex-end; 
  gap: 0.75rem; 
  margin-top: 0.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #F1F5F9;
}

.btn-cancelar { 
  background: white; 
  border: 1px solid #E2E8F0; 
  padding: 0.75rem 1.25rem; 
  border-radius: 10px; 
  cursor: pointer;
  font-weight: 500;
  font-size: 0.9rem;
  color: #64748B;
  transition: all 0.2s;
}

.btn-cancelar:hover { 
  background: #F8FAFC; 
  border-color: #CBD5E1;
  color: #1E293B;
}

.btn-guardar { 
  background: var(--color-azul-institucional, #0B74DE); 
  color: white; 
  border: none; 
  padding: 0.75rem 1.5rem; 
  border-radius: 10px; 
  cursor: pointer; 
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(11, 116, 222, 0.2);
}

.btn-guardar:hover:not(:disabled) { 
  background: #0A66C2;
  box-shadow: 0 4px 8px rgba(11, 116, 222, 0.3);
}

.btn-guardar:disabled { 
  opacity: 0.7; 
  cursor: not-allowed; 
}

.btn-loading {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.spinner-btn {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

/* ===== ANIMACIONES ===== */
@keyframes spin { to { transform: rotate(360deg); } }

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-active .modal-content,
.modal-fade-leave-active .modal-content {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .modal-content,
.modal-fade-leave-to .modal-content {
  transform: scale(0.95) translateY(-10px);
  opacity: 0;
}

/* ===== RESPONSIVE ===== */
@media (min-width: 1024px) {
  .dashboard-grid { grid-template-columns: 2fr 1fr; }
}

@media (max-width: 640px) {
  .dashboard-contenedor { padding: 1rem; }
  .dashboard-header { 
    flex-direction: column; 
    align-items: flex-start; 
    gap: 1rem;
    padding: 1.25rem;
  }
  .header-meta { text-align: left; }
  .saludo { font-size: 1.5rem; }
}
</style>