<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../store/auth'
import api from '@/api/axios'
import { 
  ClipboardDocumentCheckIcon, 
  ArrowDownTrayIcon, 
  CloudArrowUpIcon,
  DocumentPlusIcon,
  ListBulletIcon,
  UserIcon,
  CalendarDaysIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()

// State
const licencias = ref([])
const cargando = ref(false)
const error = ref('')
const mensaje = ref('')
const activeTab = ref('mis-licencias')

const form = ref({
  user_id: '',
  fecha_inicio: '',
  fecha_fin: '',
  archivo: null,
  observaciones: ''
})

const funcionarios = ref([])
const esSubdireccion = computed(() => authStore.esSubdireccion)

// Loading Data
const cargarUsuarios = async () => {
  if (esSubdireccion.value) {
    try {
      const response = await api.get('/users') 
      funcionarios.value = response.data.data || response.data
    } catch (e) {
      console.error("Error cargando usuarios", e)
    }
  }
}

const cargarLicencias = async () => {
  cargando.value = true
  try {
    const response = await api.get('/licencias-medicas')
    licencias.value = response.data
  } catch (e) {
    console.error("Error cargando licencias", e)
    error.value = "Error al cargar el historial."
  } finally {
    cargando.value = false
  }
}

const onFileChange = (e) => {
  form.value.archivo = e.target.files[0]
}

const handleUpload = async () => {
  error.value = ''
  mensaje.value = ''
  cargando.value = true
  
  const formData = new FormData()
  formData.append('user_id', form.value.user_id)
  formData.append('fecha_inicio', form.value.fecha_inicio)
  formData.append('fecha_fin', form.value.fecha_fin)
  if (form.value.archivo) formData.append('archivo', form.value.archivo)
  if (form.value.observaciones) formData.append('observaciones', form.value.observaciones)

  try {
    await api.post('/licencias-medicas', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    mensaje.value = 'Licencia registrada exitosamente.'
    form.value = { user_id: '', fecha_inicio: '', fecha_fin: '', archivo: null, observaciones: '' }
    activeTab.value = 'mis-licencias'
    cargarLicencias()
  } catch (e) {
    error.value = e.response?.data?.message || 'Error al subir licencia.'
  } finally {
    cargando.value = false
  }
}

const descargar = async (id, nombreArchivo) => {
  try {
    const response = await api.get(`/licencias-medicas/${id}/download`, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    const extension = nombreArchivo ? nombreArchivo.split('.').pop() : 'pdf';
    link.setAttribute('download', `Licencia_${id}.${extension}`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (e) {
    console.error("Error descargando", e)
    alert("Error al descargar el archivo.")
  }
}

onMounted(() => {
  cargarLicencias()
  cargarUsuarios()
})
</script>

<template>
  <div class="page-container">
    <div class="content-wrapper">
      
      <!-- Header -->
      <header class="page-header">
        <div class="header-content">
          <h1 class="page-title">
            <ClipboardDocumentCheckIcon class="title-icon"/>
            Gestión de Licencias Médicas
          </h1>
          <p class="page-subtitle">Historial y registro de licencias del personal.</p>
        </div>
      </header>

      <!-- Tabs -->
      <div class="tabs-container">
        <button 
          @click="activeTab = 'mis-licencias'"
          :class="['tab-button', activeTab === 'mis-licencias' ? 'active' : '']"
        >
          <ListBulletIcon class="tab-icon"/>
          {{ esSubdireccion ? 'Historial General' : 'Mis Licencias' }}
        </button>
        <button 
          v-if="esSubdireccion"
          @click="activeTab = 'ingreso'"
          :class="['tab-button', activeTab === 'ingreso' ? 'active' : '']"
        >
          <DocumentPlusIcon class="tab-icon"/>
          Ingreso de Licencias
        </button>
      </div>

      <!-- Alerts -->
      <Transition name="fade">
        <div v-if="error" class="alert alert-error">
          <span>{{ error }}</span>
        </div>
      </Transition>
      <Transition name="fade">
        <div v-if="mensaje" class="alert alert-success">
          <span>{{ mensaje }}</span>
        </div>
      </Transition>

      <!-- Listado -->
      <section v-if="activeTab === 'mis-licencias'" class="card-section">
        <div class="card-content no-padding">
          <div v-if="cargando" class="loading-state">
            <div class="spinner"></div>
            <p>Cargando datos...</p>
          </div>
          
          <div v-else class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th v-if="esSubdireccion">Funcionario</th>
                  <th>Período</th>
                  <th v-if="esSubdireccion">Registrado Por</th>
                  <th>Detalle</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="licencia in licencias" :key="licencia.id">
                  <td v-if="esSubdireccion">
                    <div class="user-info">
                      <span class="font-medium text-slate-900">{{ licencia.solicitante?.name }}</span>
                      <span class="text-xs text-slate-500">{{ licencia.solicitante?.rut }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="date-range">
                      <div class="date-item">
                        <CalendarDaysIcon class="w-4 h-4 text-slate-400"/>
                        <span>{{ licencia.fecha_inicio }}</span>
                      </div>
                      <span class="date-arrow">→</span>
                      <div class="date-item">
                        <span>{{ licencia.fecha_fin }}</span>
                      </div>
                    </div>
                  </td>
                  <td v-if="esSubdireccion">
                    <div class="user-badge">
                      <UserIcon class="w-3 h-3"/>
                      <span>{{ licencia.registrador?.name || 'Sistema' }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="observation-text" :title="licencia.observaciones">
                      {{ licencia.observaciones || 'Sin observaciones' }}
                    </div>
                  </td>
                  <td>
                    <button @click="descargar(licencia.id, licencia.nombre_archivo)" class="btn-action">
                      <ArrowDownTrayIcon class="w-4 h-4"/>
                      Descargar
                    </button>
                  </td>
                </tr>
                <tr v-if="!cargando && licencias.length === 0">
                  <td colspan="5" class="empty-state">
                    No hay licencias registradas para mostrar.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Formulario (Subdirección) -->
      <section v-if="activeTab === 'ingreso' && esSubdireccion" class="card-section max-w-2xl mx-auto">
        <div class="card-header">
          <h2>Registro de Nueva Licencia</h2>
          <p>Complete los datos del formulario para registrar una licencia.</p>
        </div>
        <div class="card-content">
          <form @submit.prevent="handleUpload" class="form-layout">
            
            <div class="form-group">
              <label>Funcionario</label>
              <div class="input-wrapper">
                <select v-model="form.user_id" required class="form-select">
                  <option value="" disabled>Seleccione funcionario...</option>
                  <option v-for="user in funcionarios" :key="user.id" :value="user.id">
                    {{ user.name }} ({{ user.rut }})
                  </option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Desde</label>
                <input type="date" v-model="form.fecha_inicio" required class="form-input">
              </div>
              <div class="form-group">
                <label>Hasta</label>
                <input type="date" v-model="form.fecha_fin" required class="form-input">
              </div>
            </div>

            <div class="form-group">
              <label>Archivo de Respaldo</label>
              <div class="file-upload-wrapper">
                <input type="file" @change="onFileChange" required accept=".pdf,image/*" id="file-upload" class="hidden-input">
                <label for="file-upload" class="file-drop-area">
                  <CloudArrowUpIcon class="w-8 h-8 text-blue-500 mb-2"/>
                  <span v-if="form.archivo" class="file-name">{{ form.archivo.name }}</span>
                  <span v-else class="file-prompt">Click para seleccionar archivo (PDF/IMG)</span>
                </label>
              </div>
            </div>

            <div class="form-group">
              <label>Observaciones</label>
              <textarea v-model="form.observaciones" rows="3" class="form-textarea" placeholder="Opcional..."></textarea>
            </div>

            <div class="form-actions">
              <button type="submit" :disabled="cargando" class="btn-primary">
                <span v-if="cargando" class="spinner-small"></span>
                {{ cargando ? 'Registrando...' : 'Registrar Licencia' }}
              </button>
            </div>
          </form>
        </div>
      </section>

    </div>
  </div>
</template>

<style scoped>
/* Layout */
.page-container {
  min-height: 100vh;
  background-color: #F8FAFC;
  padding: 2rem 1rem;
}

.content-wrapper {
  max-width: 1280px;
  margin: 0 auto;
}

/* Header */
.page-header {
  background: white;
  border-radius: 16px;
  padding: 1.5rem 2rem;
  margin-bottom: 2rem;
  border: 1px solid #E2E8F0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.title-icon {
  width: 32px;
  height: 32px;
  color: #0B74DE;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1E293B;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 0;
}

.page-subtitle {
  color: #64748B;
  margin: 0.25rem 0 0 3.25rem;
  font-size: 0.95rem;
}

/* Tabs */
.tabs-container {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  border-bottom: 1px solid #E2E8F0;
  padding-bottom: 1px;
}

.tab-button {
  background: none;
  border: none;
  padding: 0.75rem 1.5rem;
  font-size: 0.95rem;
  font-weight: 500;
  color: #64748B;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
}

.tab-button:hover {
  color: #0B74DE;
}

.tab-button.active {
  color: #0B74DE;
  border-bottom-color: #0B74DE;
}

.tab-icon { width: 20px; height: 20px; }

/* Cards */
.card-section {
  background: white;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.card-header {
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #F1F5F9;
  background-color: #FAFBFC;
}

.card-header h2 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1E293B;
  margin: 0;
}

.card-header p {
  color: #64748B;
  font-size: 0.9rem;
  margin: 0.25rem 0 0 0;
}

.card-content { padding: 2rem; }
.card-content.no-padding { padding: 0; }

/* Table */
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }

.data-table th {
  background: #F8FAFC;
  padding: 1rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #64748B;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #E2E8F0;
}

.data-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #F1F5F9;
  color: #334155;
  font-size: 0.9rem;
}

.data-table tr:hover { background-color: #FAFBFC; }

.user-info { display: flex; flex-direction: column; }
.date-range { display: flex; align-items: center; gap: 0.5rem; color: #475569; }
.date-item { display: flex; align-items: center; gap: 0.25rem; }
.date-arrow { color: #94A3B8; }
.user-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  background: #F1F5F9;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 500;
  color: #475569;
}

.btn-action {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: white;
  border: 1px solid #E2E8F0;
  padding: 0.4rem 0.8rem;
  border-radius: 8px;
  color: #0B74DE;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:hover {
  background: #EFF6FF;
  border-color: #BFDBFE;
}

/* Form */
.form-layout { display: flex; flex-direction: column; gap: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group label {
  display: block;
  font-size: 0.9rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-input, .form-select, .form-textarea {
  width: 100%;
  padding: 0.6rem 1rem;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.2s;
  background-color: #F8FAFC;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: #0B74DE;
  background: white;
  box-shadow: 0 0 0 3px rgba(11, 116, 222, 0.1);
}

.file-drop-area {
  border: 2px dashed #CBD5E1;
  border-radius: 12px;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  background: #F8FAFC;
}

.file-drop-area:hover {
  border-color: #0B74DE;
  background: #EFF6FF;
}

.hidden-input { display: none; }
.file-name { font-weight: 600; color: #0B74DE; }
.file-prompt { color: #64748B; font-size: 0.9rem; }

.btn-primary {
  width: 100%;
  background: #0B74DE;
  color: white;
  border: none;
  padding: 0.8rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-primary:hover:not(:disabled) { background: #0A66C2; box-shadow: 0 4px 6px -1px rgba(11, 116, 222, 0.3); }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }

/* Alerts */
.alert { padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.95rem; }
.alert-error { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
.alert-success { background: #F0FDF4; color: #166534; border: 1px solid #DCFCE7; }

/* Utility */
.spinner {
  border: 3px solid #E2E8F0;
  border-top: 3px solid #0B74DE;
  border-radius: 50%;
  width: 40px; height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

.spinner-small {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

.loading-state, .empty-state { padding: 4rem; text-align: center; color: #64748B; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
