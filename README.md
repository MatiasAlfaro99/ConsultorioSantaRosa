Intranet Administrativa CESFAM Santa Rosa

Proyecto Integrado

Este proyecto es una aplicación web destinada a centralizar y automatizar los procesos administrativos internos del Centro de Salud Familiar (CESFAM) Santa Rosa, enfocándose en la gestión documental y el flujo de solicitudes de permisos y vacaciones.
Caracteristicas Principales

    Gestion de Saldos de Recursos (RF1): Cálculo preciso de días administrativos y feriado legal, aplicando la regla de conteo solo de días hábiles (lunes a viernes).

    Flujo de Aprobación Jerárquica (RF4): Flujo de trabajo de tres niveles para solicitudes de permiso: Funcionario -> Jefatura/Subdirección -> Dirección.

    Repositorio Documental Seguro (RF2): Almacenamiento privado de circulares y protocolos, con acceso controlado por rol.

    Comunicacion Interna: Publicación de comunicados oficiales (RF3) y directorio de funcionarios (RF6).

Arquitectura y Stack Tecnológico

La solución se implementó bajo una arquitectura API RESTful desacoplada para garantizar la escalabilidad y la mantenibilidad.

    Backend (API): Laravel (PHP 12 / 8.5) para lógica de negocio, ORM (Eloquent), Autenticación JWT y API REST.

    Frontend (SPA): Vue.js (3.x) para interfaz de usuario dinámica y experiencia de Single Page Application.

    Base de Datos: PostgreSQL (15.x) para almacenamiento relacional robusto para datos críticos.

    Contenerización: Docker / Docker Compose para un entorno de desarrollo consistente y despliegue rápido.

Seguridad y Buenas Prácticas

El proyecto adhiere a prácticas modernas de desarrollo:

    Service Layer Pattern: La lógica de negocio compleja (ej. cálculo de días [RF1]) está separada de los Controladores.

    RBAC (Control de Acceso Basado en Roles): Distinción estricta de permisos entre Funcionario, Jefatura y Dirección.

    Autenticacion JWT: Se utiliza un sistema de tokens para proteger todas las rutas de la API.

    Seguridad Documental: Los archivos se almacenan en rutas de storage privado y el acceso se verifica mediante roles.

Guia de Instalacion y Despliegue (Docker)

Esta guía asume que tienes Docker y Docker Compose instalados en tu sistema.
1. Clonar el Repositorio

git clone https://github.com/MatiasAlfaro99/ConsultorioSantaRosa cd intranet-cesfam
2. Configuracion de Variables de Entorno

Copia el archivo de configuración de ejemplo y ajústalo (.env):

cp .env.example .env

    Asegurate de:

        Definir una clave unica en APP_KEY.

        Configurar la base de datos DB_HOST, DB_DATABASE, etc., según lo definido en el docker-compose.yml.

3. Construir y Levantar los Contenedores

Este comando construirá las imágenes necesarias y levantará el Backend, el Frontend y la Base de Datos PostgreSQL:

docker-compose up --build -d
4. Ejecutar Migraciones y Seeds (Inicializar DB)

Una vez que los contenedores estén activos, accede al contenedor del Backend para inicializar la base de datos.
Entrar al contenedor del Backend

docker exec -it consultorio-v2 bash
Ejecutar migraciones y seeds

php artisan migrate --seed
Salir del contenedor

exit

    Nota: El seed de la base de datos creará usuarios iniciales con los roles Funcionario, Jefatura y Direccion para pruebas.

5. Acceso a la Aplicacion

La aplicación estará disponible en:

    Frontend (Intranet): http://localhost:[PUERTO_FRONTEND] (Revisa el docker-compose.yml para el puerto exacto, comunmente 80 o 8080).

    Backend (API): http://localhost:[PUERTO_BACKEND]

Contribuyentes

Matías Alfaro Donoso - Desarrollado para la asignatura Proyecto Integrado
