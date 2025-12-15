
import Calendario from '@/views/Calendario.vue'
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth.js'
import AppLayout from '@/layouts/AppLayout.vue'
import Directorio from '@/views/Directorio.vue'
import Comunicados from '@/views/Comunicados.vue'
import Login from '@/views/Login.vue'
import Dashboard from '@/views/Dashboard.vue'
import Documentos from '@/views/Documentos.vue'
import MisSolicitudes from '@/views/solicitudes/MisSolicitudes.vue'
import NuevaSolicitud from '@/views/solicitudes/NuevaSolicitud.vue'
import Perfil from '@/views/Perfil.vue'

import LicenciasMedicas from '@/views/licencias/LicenciasMedicas.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { esPublica: true }
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiereAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard'
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: Dashboard,
      },
      {
        path: 'documentos',
        name: 'Documentos',
        component: Documentos,
      },
      {
        path: 'solicitudes',
        name: 'MisSolicitudes',
        component: MisSolicitudes,
      },
      {
        path: 'solicitudes/nueva',
        name: 'NuevaSolicitud',
        component: NuevaSolicitud,
      },
      {
        path: 'comunicados',
        name: 'Comunicados',
        component: Comunicados,
      },
      {
        path: 'perfil',
        name: 'Perfil',
        component: Perfil,
      },
      {
        path: 'directorio',
        name: 'Directorio',
        component: Directorio,
      },
      {
        path: 'calendario',
        name: 'Calendario',
        component: Calendario,
      },

      {
        path: 'licencias',
        name: 'LicenciasMedicas',
        component: LicenciasMedicas,
      },
      { path: 'ajustes', redirect: '/dashboard' },
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()


  if (!authStore.usuario && authStore.token) {
    await authStore.validarTokenAlCargar()
  }

  const estaAutenticado = authStore.estaAutenticado

  if (to.meta.requiereAuth && !estaAutenticado) {
    next({ name: 'Login' })
  } else if (to.meta.esPublica && estaAutenticado) {
    next({ name: 'Dashboard' })
  } else {
    next()
  }
})

export default router