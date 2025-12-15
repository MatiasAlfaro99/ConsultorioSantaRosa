<template>
  <div class="noticias-wrapper">
    <div class="header-noticias">
      <h3 class="titulo-seccion">Noticias Minsal</h3>
      <a href="https://www.minsal.cl/category/noticias/" target="_blank" class="link-ver-mas">
        Ir al sitio oficial →
      </a>
    </div>

    <div v-if="cargando" class="loading">
      Cargando noticias...
    </div>

    <div v-else class="grid-noticias">
      <a 
        v-for="(noticia, index) in noticias" 
        :key="index" 
        :href="noticia.link" 
        target="_blank"
        class="card-noticia"
      >
        <div class="imagen-container">
          <img 
            :src="noticia.imagen || '/placeholder-news.jpg'" 
            alt="Imagen noticia" 
            class="imagen-noticia" 
            @error="handleImageError"
          />
          <span class="badge-fecha">{{ noticia.fecha }}</span>
        </div>
        <div class="contenido-noticia">
          <h4 class="titulo-noticia">{{ noticia.titulo }}</h4>
          <p class="resumen-noticia">{{ noticia.resumen }}</p>
        </div>
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '@/api/axios.js'

const noticias = ref([])
const cargando = ref(true)

const cargarNoticias = async () => {
  try {
    const response = await apiClient.get('/noticias-minsal')
    noticias.value = response.data
  } catch (error) {
    console.error("Error cargando noticias:", error)
  } finally {
    cargando.value = false
  }
}

const handleImageError = (e) => {
  e.target.src = 'https://via.placeholder.com/300x200?text=Noticia+Minsal' // Imagen por defecto si falla
}

onMounted(cargarNoticias)
</script>

<style scoped>
.noticias-wrapper { margin-top: 2rem; }

.header-noticias {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;
}
.titulo-seccion {
  font-size: 1rem; font-weight: 700; color: var(--color-texto-secundario); text-transform: uppercase; letter-spacing: 0.05em;
}
.link-ver-mas { font-size: 0.85rem; color: var(--color-azul-institucional); text-decoration: none; font-weight: 500; }
.link-ver-mas:hover { text-decoration: underline; }

.grid-noticias {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Responsive auto */
  gap: 1.5rem;
}

.card-noticia {
  background: white; border: 1px solid var(--color-borde); border-radius: 12px; overflow: hidden;
  text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s;
  display: flex; flex-direction: column;
}
.card-noticia:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); }

.imagen-container { position: relative; height: 160px; overflow: hidden; }
.imagen-noticia { width: 100%; height: 100%; object-fit: cover; }

.badge-fecha {
  position: absolute; bottom: 10px; right: 10px;
  background: rgba(0,0,0,0.7); color: white;
  font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; font-weight: 600;
}

.contenido-noticia { padding: 1rem; flex: 1; display: flex; flex-direction: column; }
.titulo-noticia { font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.4; color: var(--color-texto-principal); }
.resumen-noticia { font-size: 0.85rem; color: var(--color-texto-secundario); line-height: 1.5; }

.loading { text-align: center; padding: 2rem; color: #9CA3AF; }
</style>
