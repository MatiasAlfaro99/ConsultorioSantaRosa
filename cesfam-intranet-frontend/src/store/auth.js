import { defineStore } from 'pinia'
import apiClient from '@/api/axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    usuario: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('authToken') || null,
  }),

  getters: {
    estaAutenticado: (state) => !!state.token,

    // --- ROLES ESPECÍFICOS (Normalizando nombres) ---
    esAdmin: (state) => state.usuario?.role === 'admin',
    
    // Aquí cubrimos ambas posibilidades por si en la BD se guardó distinto
    esDirector: (state) => ['director', 'direccion'].includes(state.usuario?.role),
    
    esSubdireccion: (state) => state.usuario?.role === 'subdireccion',
    esJefatura: (state) => state.usuario?.role === 'jefatura',

    // --- PERMISOS DE ALTO NIVEL (CRUD GLOBAL) ---
    // Usaremos esto para habilitar botones de Crear/Editar/Borrar en Calendario, Documentos y Comunicados
    tienePermisoTotal: (state) => {
        const rolesSuperiores = ['admin', 'director', 'direccion', 'subdireccion'];
        return rolesSuperiores.includes(state.usuario?.role);
    },

    // Permisos para gestión de solicitudes (ya lo tenías)
    puedeAdministrarDirectorio: (state) => {
      const rolesPermitidos = ['admin', 'director', 'direccion', 'subdireccion', 'jefatura'];
      return state.usuario && rolesPermitidos.includes(state.usuario.role);
    }
  },

  actions: {
    async iniciarSesion(credenciales) {
      try {
        const respuesta = await apiClient.post('/login', credenciales)
        const { token, user } = respuesta.data

        this.token = token
        this.usuario = user

        localStorage.setItem('authToken', token)
        localStorage.setItem('user', JSON.stringify(user))

        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`
        router.push('/dashboard')

      } catch (error) {
        console.error('Error en el login:', error)
        throw error
      }
    },

    async validarTokenAlCargar() {
      const tokenGuardado = localStorage.getItem('authToken')
      if (!tokenGuardado) {
        this.limpiarSesion()
        return false
      }
      this.token = tokenGuardado
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${tokenGuardado}`

      try {
        const respuesta = await apiClient.get('/user')
        this.usuario = respuesta.data
        localStorage.setItem('user', JSON.stringify(this.usuario))
        return true
      } catch (error) {
        this.limpiarSesion()
        return false
      }
    },

    limpiarSesion() {
      this.usuario = null
      this.token = null
      localStorage.removeItem('authToken')
      localStorage.removeItem('user')
      delete apiClient.defaults.headers.common['Authorization']
    },

    async cerrarSesion() {
      try {
        await apiClient.post('/logout')
      } catch (e) {
        console.log('Logout local')
      } finally {
        this.limpiarSesion()
        router.push('/login')
      }
    },
  },
})
