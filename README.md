# Gestión Docente

Proyecto personal de aprendizaje para centralizar la información profesional del profesorado, su disponibilidad, documentación y tarifas.

La aplicación reproduce un caso de gestión interna utilizando exclusivamente datos ficticios. El código y la base de datos emplean nombres en inglés, mientras que la interfaz se presenta en español.

## Funcionalidades

- Acceso autenticado sin registro público.
- Alta, consulta, edición, búsqueda, filtrado y cambio de estado de profesores.
- Gestión de datos personales, contacto, experiencia y observaciones.
- Materias, disponibilidad, formación y conocimientos de programas y herramientas.
- Documentos privados con validación por categoría y límite de 10 MB.
- Catálogo administrable de servicios.
- Tarifas generales y específicas con vigencia e histórico.
- Exportación de información en PDF y CSV.
- Interfaz responsive con temas claro y oscuro.

## Tecnologías

- PHP 8.3 y Laravel 13.
- SQLite para una puesta en marcha local sencilla y MariaDB 10.6 o posterior para despliegues persistentes.
- Blade, Tailwind CSS 4 y JavaScript.
- Vite y npm para los recursos frontend.
- Composer para las dependencias PHP.
- PHPUnit para las pruebas automatizadas.

## Preparación local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

La configuración de ejemplo utiliza SQLite para facilitar la demostración local. Para un despliegue persistente se recomienda configurar MariaDB en `.env`. Nunca se deben versionar contraseñas, claves ni archivos `.env` reales.

Después:

```bash
php artisan migrate --seed
npm run build
```

El seeder incorpora cinco perfiles de profesores completamente ficticios, junto con materias, disponibilidad, formación, herramientas y tarifas de demostración. No crea usuarios ni contraseñas y tampoco añade documentos físicos.

Crear el primer usuario de forma interactiva:

```bash
php artisan user:create
```

Para restablecer administrativamente una contraseña:

```bash
php artisan user:reset-password
```

## Desarrollo

```bash
composer run dev
```

La aplicación quedará disponible normalmente en `http://localhost:8000`.

## Comprobaciones

```bash
php artisan test --compact
vendor/bin/pint --format agent
npm run build
git diff --check
```

## Datos y documentos

- Los datos de demostración son completamente ficticios.
- Los documentos se guardan en almacenamiento privado, fuera de `public/`.
- El tamaño máximo admitido es de 10 MB por archivo.
- PHP y el servidor web deben configurarse con límites superiores a 10 MB.
- La base de datos y `storage/app/private` deben incluirse en la política de copias de seguridad de cualquier instalación real.

## Despliegue

Antes de desplegar se debe preparar un `.env` específico del entorno y verificar:

- `APP_ENV=production` y `APP_DEBUG=false`;
- HTTPS y raíz web apuntando a `public/`;
- credenciales de base de datos con privilegios limitados;
- permisos de escritura en `storage/` y `bootstrap/cache/`;
- límites de subida de PHP;
- migraciones, datos iniciales y primer usuario;
- copias de seguridad y procedimiento de restauración;
- capacidad disponible para documentos y registros.

Las credenciales y los datos reales pertenecen siempre al entorno de ejecución y nunca al repositorio.

## Documentación técnica

- [Requisitos funcionales](docs/requisitos.md)
- [Diagrama entidad-relación](docs/database/erd.md)
- [Diccionario de datos](docs/database/data-dictionary.md)

## Autor

Proyecto personal de Rubén Alonso, desarrollado como ejercicio práctico de análisis, diseño, implementación, pruebas y despliegue de una aplicación web completa.
