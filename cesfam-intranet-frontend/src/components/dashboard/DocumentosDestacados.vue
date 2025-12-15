<template>
  <div class="tarjeta-lista">
    <div class="tarjeta-header">
      <h3 class="tarjeta-titulo">Documentos Destacados</h3>
      <router-link to="/documentos" class="tarjeta-ver-todos">
        Ver todos
      </router-link>
    </div>

    <ul v-if="documentos.length > 0" class="lista-items">
      <li 
        v-for="documento in documentos" 
        :key="documento.id" 
        class="item"
      >
        <div class="item-icono icono-documento">
          <DocumentTextIcon />
        </div>
        <div class="item-contenido">
          <span class="item-titulo">{{ documento.title }}</span>
          <span class="item-subtitulo">
            Categoría: {{ documento.type }} | {{ formatearFecha(documento.updated_at) }}
          </span>
        </div>
      </li>
    </ul>
    
    <div v-else class="lista-vacia">
      No hay documentos destacados.
    </div>
  </div>
</template>

<script setup>
import { DocumentTextIcon } from '@heroicons/vue/24/outline'

defineProps({
  documentos: Array
})

const formatearFecha = (fechaISO) => {
  if (!fechaISO) return 'Fecha no disponible'
  const fecha = new Date(fechaISO)
  return fecha.toLocaleDateString('es-CL', { 
    year: 'numeric', 
    month: '2-digit', 
    day: '2-digit' 
  })
}
</script>

<style>
.tarjeta-lista {
  background-color: var(--color-tarjeta);
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid var(--color-borde);
  height: 100%; /* Para igualar altura */
}
.tarjeta-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}
.tarjeta-titulo {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0; /* Reset */
}
.tarjeta-ver-todos {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-azul-institucional);
  text-decoration: none;
  cursor: pointer;
}
.tarjeta-ver-todos:hover {
  text-decoration: underline;
}
.lista-items {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
.item {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.item-icono {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.item-icono.icono-documento {
  background-color: #E0F2F2; 
  color: var(--color-verde-salud);
}
.item-icono svg {
  width: 24px;
  height: 24px;
}
.item-contenido {
  display: flex;
  flex-direction: column;
}
.item-titulo {
  font-weight: 500;
  color: var(--color-texto-principal);
}
.item-subtitulo {
  font-size: 0.875rem;
  color: var(--color-texto-secundario);
}
.lista-vacia {
  font-size: 0.875rem;
  color: var(--color-texto-secundario);
  padding: 1rem 0;
}
</style>