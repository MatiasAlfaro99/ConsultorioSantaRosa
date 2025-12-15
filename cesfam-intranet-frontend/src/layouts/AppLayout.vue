<template>
  <div class="aplicacion-contenedor">
    <header class="encabezado-superior">
      <Encabezado />
    </header>

    <aside class="navegacion-principal">
      <NavegacionPrincipal />
    </aside>

    <main class="contenido-principal">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import Encabezado from '@/components/Encabezado.vue'
import NavegacionPrincipal from '@/components/NavegacionPrincipal.vue'
</script>

<style>
.aplicacion-contenedor {
  display: grid;
  grid-template-areas:
    "header"
    "main"
    "nav";
  grid-template-rows: auto 1fr auto;
  min-height: 100vh;
}

.encabezado-superior {
  grid-area: header;
  background-color: var(--color-tarjeta);
  border-bottom: 1px solid var(--color-borde);
  padding: 1rem;
  display: flex;
  align-items: center;
}

.contenido-principal {
  grid-area: main;
  padding: 1.5rem;
}

.navegacion-principal {
  grid-area: nav;
  background-color: var(--color-tarjeta);
  border-top: 1px solid var(--color-borde);
}

@media (min-width: 768px) {
  .aplicacion-contenedor {
    grid-template-areas:
      "nav header"
      "nav main";
    grid-template-columns: 240px 1fr;
    grid-template-rows: auto 1fr;
  }

  .navegacion-principal {
    border-top: none;
    border-right: 1px solid var(--color-borde);
    padding: 1.5rem;
  }

  .contenido-principal {
    padding: 2rem;
  }
}
</style>
