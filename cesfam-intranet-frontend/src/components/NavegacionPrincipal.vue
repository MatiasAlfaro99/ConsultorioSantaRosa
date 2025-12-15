<template>
  <nav class="contenedor-navegacion">
    <div class="logo-desktop">
      <img :src="logoCesfam" alt="Logo CESFAM" />
    </div>

    <ul class="lista-menu">
      <router-link to="/dashboard" class="item-menu-link">
        <li class="item-menu">
          <HomeIcon class="icono-menu" />
          <span class="texto-menu">Inicio</span>
        </li>
      </router-link>

      <router-link to="/solicitudes" class="item-menu-link">
        <li class="item-menu">
          <PencilSquareIcon class="icono-menu" />
          <span class="texto-menu">Solicitudes</span>
        </li>
      </router-link>

      <router-link to="/documentos" class="item-menu-link">
        <li class="item-menu">
          <DocumentTextIcon class="icono-menu" />
          <span class="texto-menu">Documentos</span>
        </li>
      </router-link>

      <router-link to="/directorio" class="item-menu-link">
        <li class="item-menu">
          <UserGroupIcon class="icono-menu" />
          <span class="texto-menu">Directorio</span>
        </li>
      </router-link>

      <router-link to="/comunicados" class="item-menu-link">
        <li class="item-menu">
          <MegaphoneIcon class="icono-menu" />
          <span class="texto-menu">Comunicados</span>
        </li>
      </router-link>
      
      <router-link to="/calendario" class="item-menu-link">
        <li class="item-menu">
          <CalendarIcon class="icono-menu" />
          <span class="texto-menu">Calendario</span>
        </li>
      </router-link>



      <router-link to="/licencias" class="item-menu-link">
        <li class="item-menu">
          <ClipboardDocumentCheckIcon class="icono-menu" />
          <span class="texto-menu">Licencias</span>
        </li>
      </router-link>
    </ul>
  </nav>
</template>

<script setup>
import { 
  HomeIcon, 
  DocumentTextIcon, 
  MegaphoneIcon, 
  CalendarIcon,
  UserGroupIcon,
  PencilSquareIcon,

  ClipboardDocumentCheckIcon
} from '@heroicons/vue/24/outline'
</script>

<style>
.contenedor-navegacion { height: 100%; }

.logo-desktop { display: none; }
.logo-desktop img { width: 100%; max-width: 150px; margin-bottom: 2rem; }

.lista-menu { list-style: none; display: flex; justify-content: space-around; }

.item-menu-link { text-decoration: none; color: var(--color-texto-secundario); }

.item-menu {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 8px;
  font-size: 0.75rem;
}

.item-menu-link:hover .item-menu { color: var(--color-azul-institucional); }

.item-menu-link.router-link-active .item-menu {
  color: var(--color-azul-institucional);
  font-weight: 500;
}

.icono-menu { width: 28px; height: 28px; }
.texto-menu { display: block; }

@media (min-width: 768px) {
  .logo-desktop { display: block; }

  .lista-menu {
    flex-direction: column;
    justify-content: flex-start;
    gap: 0.5rem;
  }
  
  .item-menu-link { border-radius: 8px; }
  
  .item-menu {
    flex-direction: row;
    font-size: 1rem;
    padding: 12px;
    gap: 1rem;
  }
  
  .item-menu-link.router-link-active { background-color: var(--color-fondo); }
  .icono-menu { width: 24px; height: 24px; }
}
</style>