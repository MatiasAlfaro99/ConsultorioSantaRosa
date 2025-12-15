import { defineStore } from 'pinia'
import apiClient from '@/api/axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    // Intentamos recuperar el usuario y token del almacenamiento local al iniciar
    usuario: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('authToken') || null,
  }),

  getters: {
    estaAutenticado: (state) => !!state.token,

    // Roles ajustados a tu Backend
    esAdmin: (state) => state.usuario?.role === 'admin',
    esDirector: (state) => ['director', 'direccion'].includes(state.usuario?.role),
    esSubdireccion: (state) => state.usuario?.role === 'subdireccion',
    esJefatura: (state) => state.usuario?.role === 'jefatura',

    // Permisos compuestos existentes
    puedeAdministrar: (state) => ['admin', 'director', 'direccion'].includes(state.usuario?.role),

    // --- AGREGA ESTA LÍNEA (Es la que busca tu Directorio.vue) ---
    puedeAdministrarDirectorio: (state) => {
      // Definimos quiénes pueden ver los botones de Agregar/Editar/Eliminar
      const rolesPermitidos = ['admin', 'director', 'subdireccion', 'jefatura'];
      return state.usuario && rolesPermitidos.includes(state.usuario.role);
    }
  },
  actions: {
    async iniciarSesion(credenciales) {
      try {
        // 1. Petición de Login al Backend
        const respuesta = await apiClient.post('/login', credenciales)

        // 2. Extraer datos (Asegúrate de que tu backend devuelve 'token' y 'user')
        const { token, user } = respuesta.data

        // 3. Actualizar Estado de Pinia
        this.token = token
        this.usuario = user

        // 4. Persistir en LocalStorage
        localStorage.setItem('authToken', token)
        localStorage.setItem('user', JSON.stringify(user))

        // 5. CRÍTICO: Configurar Axios inmediatamente con el nuevo token
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`

        // 6. Redirigir al Dashboard
        router.push('/dashboard')

      } catch (error) {
        console.error('Error en el login:', error)
        throw error // Lanza el error para que la Vista lo muestre
      }
    },

    async validarTokenAlCargar() {
      // 1. Recuperar token del almacenamiento
      const tokenGuardado = localStorage.getItem('authToken')

      if (!tokenGuardado) {
        this.limpiarSesion()
        return false
      }

      // 2. CRÍTICO: Configurar Axios ANTES de hacer la petición
      // Si no hacemos esto, la petición irá sin token y dará 401
      this.token = tokenGuardado
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${tokenGuardado}`

      try {
        // 3. Verificar si el token sigue siendo válido preguntando al backend
        const respuesta = await apiClient.get('/user')

        // 4. Actualizar usuario con datos frescos
        this.usuario = respuesta.data
        localStorage.setItem('user', JSON.stringify(this.usuario))

        return true
      } catch (error) {
        // Si el token expiró o es inválido (Error 401), limpiamos todo
        console.error("Sesión expirada o inválida")
        this.limpiarSesion()
        return false
      }
    },

    limpiarSesion() {
      this.usuario = null
      this.token = null
      localStorage.removeItem('authToken')
      localStorage.removeItem('user')

      // Limpiar cabecera de axios para evitar enviar tokens viejos
      delete apiClient.defaults.headers.common['Authorization']
    },

    async cerrarSesion() {
      try {
        // Intentamos avisar al backend para invalidar el token
        await apiClient.post('/logout')
      } catch (e) {
        // Si falla (ej. token ya expirado), cerramos localmente igual
        console.log('Cierre de sesión local (Backend no respondió o token expirado)')
      } finally {
        this.limpiarSesion()
        router.push('/login')
      }
    },
  },
})