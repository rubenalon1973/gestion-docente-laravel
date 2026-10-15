# Diccionario de datos

Última revisión: 2026-09-29

## Criterios de estado

El estado funcional y el estado técnico son independientes:

- **Confirmado**: requisito validado para el alcance del proyecto.
- **Provisional**: decisión útil, pero todavía modificable.
- **Pendiente**: requiere una respuesta o decisión.
- **Implementado**: existe actualmente en una migración aplicada.
- **Planificado**: todavía no existe en MariaDB.

## Tabla `teachers`

Estado técnico: **implementada**.

| Campo | Tipo MariaDB | Nulo | Estado funcional | Descripción |
|---|---|---:|---|---|
| `id` | `BIGINT UNSIGNED` | No | Técnico | Identificador interno autoincremental |
| `first_name` | `VARCHAR(100)` | No | Confirmado | Nombre del profesor |
| `last_names` | `VARCHAR(150)` | No | Confirmado | Apellidos conservados en un único campo |
| `identity_document` | `VARCHAR(20)` | Sí | Confirmado | DNI/NIE normalizado sin espacios y en mayúsculas; único cuando tiene valor |
| `email` | `VARCHAR(255)` | Sí | Confirmado | Correo electrónico; su unicidad está pendiente |
| `phone` | `VARCHAR(30)` | Sí | Confirmado | Teléfono con posibilidad de prefijo y símbolos |
| `birth_date` | `DATE` | Sí | Confirmado | Fecha de nacimiento |
| `birth_place` | `VARCHAR(150)` | Sí | Confirmado | Lugar de nacimiento; añadido mediante migración reversible aplicada |
| `locality` | `VARCHAR(150)` | Sí | Confirmado | Localidad principal |
| `postal_code` | `VARCHAR(32)` | Sí | Confirmado | Código postal como texto para admitir formatos internacionales |
| `province` | `VARCHAR(150)` | Sí | Confirmado | Provincia o división territorial equivalente |
| `country` | `VARCHAR(150)` | Sí | Confirmado | País como texto libre |
| `address` | `TEXT` | Sí | Confirmado | Dirección postal |
| `years_of_experience` | `SMALLINT UNSIGNED` | Sí | Confirmado | Años totales de experiencia declarados |
| `creates_content` | `TINYINT(1)` | Sí | Confirmado | Indicador independiente: crea contenido; `NULL` significa no informado |
| `updates_content` | `TINYINT(1)` | Sí | Confirmado | Indicador independiente: actualiza contenido; `NULL` significa no informado |
| `status` | `VARCHAR(20)` | No | Confirmado | `active`, `inactive` o `archived`; valor inicial `active` e índice de búsqueda |
| `internal_notes` | `TEXT` | Sí | Confirmado | Observaciones internas; máximo funcional de 5.000 caracteres |
| `created_at` | `TIMESTAMP` | Sí | Técnico | Fecha de creación del registro |
| `updated_at` | `TIMESTAMP` | Sí | Técnico | Fecha de última modificación |

### Reglas actuales de `teachers`

- Solo `first_name` y `last_names` son obligatorios.
- El correo todavía no tiene restricción `UNIQUE`.
- `identity_document` es opcional, pero no puede repetirse cuando tiene valor.
- La fecha de nacimiento no puede ser futura.
- Los años de experiencia deben estar entre 0 y 100.
- Código postal, provincia y país son opcionales y admiten formatos internacionales.
- No se almacenan materias, titulaciones ni documentos directamente en esta tabla.
- Durante desarrollo y pruebas solo se utilizan datos ficticios.
- Los programas y herramientas de IA se registran mediante filas independientes en `teacher_tools`, con categoría, nombre y nivel.

## Tabla `users`

Estado técnico: **implementada por Laravel**.

Representa al personal autorizado que accederá a la aplicación. No representa automáticamente a los profesores.

Campos principales:

| Campo | Finalidad |
|---|---|
| `id` | Identificador interno |
| `name` | Nombre visible del usuario |
| `email` | Identificador de acceso único |
| `email_verified_at` | Fecha de verificación del correo |
| `password` | Contraseña almacenada mediante hash |
| `remember_token` | Token para mantener la sesión |
| `created_at`, `updated_at` | Auditoría temporal básica |

La posible relación futura entre `users` y `teachers` está pendiente.

## Materias y relación con profesores

## Tabla `subjects`

Estado técnico: **implementada**.
Estado funcional: **vocabulario abierto y reutilizable**.

| Campo | Tipo MariaDB | Nulo | Descripción |
|---|---|---:|---|
| `id` | `BIGINT UNSIGNED` | No | Identificador autoincremental |
| `name` | `VARCHAR(150)` | No | Nombre común de la materia |
| `description` | `TEXT` | Sí | Explicación general de la materia |
| `created_at` | `TIMESTAMP` | Sí | Fecha de creación |
| `updated_at` | `TIMESTAMP` | Sí | Fecha de modificación |

La entrada de materias es de texto libre dentro de un vocabulario abierto y reutilizable, no mediante un catálogo cerrado.

Reglas actuales:

- `name` es obligatorio y admite entradas libres.
- `description` es opcional.
- La tabla tiene `created_at` y `updated_at`.

## Tabla `subject_teacher`

Estado técnico: **implementada**.
Estado funcional: **implementada**.

| Campo | Tipo MariaDB | Nulo | Clave | Descripción |
|---|---|---:|---|---|
| `subject_id` | `BIGINT UNSIGNED` | No | PK, FK | Referencia a `subjects.id` |
| `teacher_id` | `BIGINT UNSIGNED` | No | PK, FK | Referencia a `teachers.id` |
| `details` | `TEXT` | Sí | — | Detalle específico de la materia que puede impartir ese profesor |
| `created_at` | `TIMESTAMP` | Sí | — | Fecha de creación de la relación |
| `updated_at` | `TIMESTAMP` | Sí | — | Fecha de modificación |

Reglas actuales:

- La pareja `subject_id`–`teacher_id` no puede repetirse.
- Ambas columnas forman la clave primaria compuesta.
- Ambas claves foráneas son `BIGINT UNSIGNED` y usan `ON DELETE CASCADE`.
- `Teacher::subjects()` devuelve una relación `belongsToMany(Subject::class)` con `withPivot('details')` y `withTimestamps()`.
- `Subject::teachers()` devuelve una relación `belongsToMany(Teacher::class)` con `withPivot('details')` y `withTimestamps()`.

## Tabla `availabilities`

Estado técnico: **implementada**.  
Estado funcional: **diseño provisional**.

| Campo | Tipo MariaDB | Nulo | Clave | Descripción |
|---|---|---:|---|---|
| `id` | `BIGINT UNSIGNED` | No | PK | Identificador autoincremental |
| `teacher_id` | `BIGINT UNSIGNED` | No | FK | Referencia a `teachers.id` con `ON DELETE CASCADE` |
| `day_of_week` | `TINYINT UNSIGNED` | No | — | Día de la semana; convención provisional ISO de `1` a `7` |
| `start_time` | `TIME` | No | — | Inicio de la franja |
| `end_time` | `TIME` | No | — | Fin de la franja |
| `modality` | `VARCHAR(20)` | No | — | Única modalidad representada por la fila |
| `created_at` | `TIMESTAMP` | Sí | — | Fecha de creación |
| `updated_at` | `TIMESTAMP` | Sí | — | Fecha de modificación |

Reglas actuales:

- La convención provisional para `day_of_week` es ISO: `1 = lunes` y `7 = domingo`.
- Cada fila representa una sola modalidad.
- El borrado de un profesor elimina en cascada sus disponibilidades.
- `Teacher::availabilities()` devuelve una relación `HasMany`.
- `Availability::teacher()` devuelve una relación `BelongsTo`.
- No existe todavía una restricción para impedir solapamientos.
- No existe todavía un catálogo cerrado de modalidades.

## Tabla `qualifications`

Estado técnico: **implementada**. Estado funcional: **estructura inicial ampliable**.

| Campo | Tipo MariaDB | Nulo | Clave | Descripción |
|---|---|---:|---|---|
| `id` | `BIGINT UNSIGNED` | No | PK | Identificador autoincremental |
| `teacher_id` | `BIGINT UNSIGNED` | No | FK | Referencia a `teachers.id` con `ON DELETE CASCADE` |
| `title` | `VARCHAR(200)` | No | — | Nombre de la titulación o estudio |
| `institution` | `VARCHAR(200)` | Sí | — | Institución que imparte o expide la formación |
| `completion_year` | `SMALLINT UNSIGNED` | Sí | — | Año de finalización |
| `notes` | `TEXT` | Sí | — | Información complementaria |
| `created_at` | `TIMESTAMP` | Sí | — | Fecha de creación |
| `updated_at` | `TIMESTAMP` | Sí | — | Fecha de modificación |

`Teacher::qualifications()` devuelve una relación `HasMany` y `Qualification::teacher()` una relación `BelongsTo`.

## Tabla `teacher_tools`

Estado técnico: **implementada y probada**.
Estado funcional: **estructura inicial confirmada**.

| Campo | Tipo MariaDB | Nulo | Clave | Descripción |
|---|---|---:|---|---|
| `id` | `BIGINT UNSIGNED` | No | PK | Identificador autoincremental |
| `teacher_id` | `BIGINT UNSIGNED` | No | FK | Referencia a `teachers.id` con `ON DELETE CASCADE` |
| `category` | `VARCHAR(20)` | No | — | Categoría inicial: `software` o `ai` |
| `tool_name` | `VARCHAR(150)` | No | — | Nombre del programa o herramienta |
| `level` | `VARCHAR(50)` | No | — | Nivel declarado |
| `created_at` | `TIMESTAMP` | Sí | — | Fecha de creación |
| `updated_at` | `TIMESTAMP` | Sí | — | Fecha de modificación |

`Teacher::teacherTools()` devuelve una relación `HasMany` y `TeacherTool::teacher()` una relación `BelongsTo`. Las categorías admitidas son `software` y `ai`; los niveles son básico, intermedio y avanzado.

## Tabla `documents`

Estado técnico: **implementada**.
Estado funcional: **implementada con validación por categoría**.

| Campo | Tipo MariaDB | Nulo | Clave | Descripción |
|---|---|---:|---|---|
| `id` | `BIGINT UNSIGNED` | No | PK | Identificador autoincremental |
| `teacher_id` | `BIGINT UNSIGNED` | No | FK | Referencia a `teachers.id` con `ON DELETE CASCADE` |
| `category` | `VARCHAR(30)` | No | — | Categoría funcional del documento |
| `original_name` | `VARCHAR(255)` | No | — | Nombre original conservado como metadato |
| `disk` | `VARCHAR(50)` | No | — | Disco privado; valor predeterminado `local` |
| `storage_path` | `VARCHAR(500)` | No | — | Ruta interna del archivo fuera de MariaDB |
| `mime_type` | `VARCHAR(100)` | Sí | — | Tipo MIME detectado |
| `size_bytes` | `BIGINT UNSIGNED` | Sí | — | Tamaño en bytes |
| `sha256` | `CHAR(64)` | Sí | — | Huella SHA-256 opcional |
| `created_at` | `TIMESTAMP` | Sí | — | Fecha de creación |
| `updated_at` | `TIMESTAMP` | Sí | — | Fecha de modificación |

Reglas confirmadas:

- Los archivos son privados y se almacenan fuera de MariaDB; la tabla conserva sus metadatos.
- Currículum: PDF o DOCX.
- Fotografía: JPG, JPEG, PNG o WEBP.
- Titulación: PDF, JPG, JPEG o PNG.
- Otros: PDF, DOCX, JPG, JPEG o PNG.
- Cada envío contendrá un solo archivo, con un máximo de 10 MB.
- El nombre físico será opaco y el archivo permanecerá en almacenamiento privado fuera de `public/`.
- La extensión, el MIME real y la categoría deben ser compatibles.
- La descarga siempre pasa por la aplicación autenticada y la ruta comprueba que el documento pertenece al profesor indicado.
- `Teacher::documents()` devuelve una relación `HasMany` y `Document::teacher()` una relación `BelongsTo`.

Antes del despliegue, PHP y el servidor web deben configurarse con límites superiores a 10 MB.

## Trabajo futuro no implementado

| Tabla | Estado funcional | Finalidad |
|---|---|---|
| `audit_logs` | Opcional | Registro de operaciones relevantes si se aprueba el requisito de auditoría |

## Requisitos funcionales confirmados

| Requisito | Estado técnico | Criterio confirmado |
|---|---|---|
| Lugar de nacimiento | Implementado | `teachers.birth_place` existe como `VARCHAR(150) NULL` |
| PVP | Implementado | Se expresa por servicio y puede variar por profesor, materia, modalidad, unidad y vigencia |
| Indicadores de contenido | Implementados | `creates_content` y `updates_content` son independientes |
| Materias | Implementado | Se introducen como texto libre dentro de un vocabulario abierto y reutilizable; no forman un catálogo cerrado |
| Lugares de trabajo | No estructurados | Permanecen en el currículum; no se exige una colección de empleos |
| Programas o software | Implementado | Se registran como filas independientes con nombre y nivel en `teacher_tools` |
| Manejo de IA | Implementado | La categoría `ai` distingue las herramientas de IA dentro de `teacher_tools` |

## Tabla `services`

Estado técnico: **implementada**.

| Campo | Tipo MariaDB | Nulo | Regla | Descripción |
|---|---|---:|---|---|
| `id` | `BIGINT UNSIGNED` | No | PK | Identificador autoincremental |
| `code` | `VARCHAR(50)` | No | UNIQUE | Código estable del servicio |
| `name` | `VARCHAR(150)` | No | — | Nombre visible |
| `description` | `TEXT` | Sí | — | Descripción opcional |
| `is_active` | `BOOLEAN` | No | `true` | Permite desactivar sin eliminar |
| `created_at` | `TIMESTAMP` | Sí | — | Fecha de creación |
| `updated_at` | `TIMESTAMP` | Sí | — | Fecha de modificación |

## Tabla `pricing_units`

Estado técnico: **implementada**.

| Campo | Tipo MariaDB | Nulo | Regla | Descripción |
|---|---|---:|---|---|
| `id` | `BIGINT UNSIGNED` | No | PK | Identificador autoincremental |
| `code` | `VARCHAR(50)` | No | UNIQUE | Código estable de la unidad |
| `name` | `VARCHAR(100)` | No | — | Nombre visible |
| `is_active` | `BOOLEAN` | No | `true` | Permite desactivar sin eliminar |
| `created_at` | `TIMESTAMP` | Sí | — | Fecha de creación |
| `updated_at` | `TIMESTAMP` | Sí | — | Fecha de modificación |

Las unidades iniciales cargadas por `PricingUnitSeeder` son hora (`hour`), curso (`course`) y jornada (`day`). Pueden añadirse otras sin modificar el esquema.

## Tabla `teacher_service_prices`

Estado técnico: **implementada y probada**.

| Campo | Tipo MariaDB | Nulo | Regla | Descripción |
|---|---|---:|---|---|
| `id` | `BIGINT UNSIGNED` | No | PK | Identificador autoincremental |
| `teacher_id` | `BIGINT UNSIGNED` | No | FK, RESTRICT | Profesor de la tarifa |
| `service_id` | `BIGINT UNSIGNED` | No | FK, RESTRICT | Servicio tarifado |
| `subject_id` | `BIGINT UNSIGNED` | Sí | FK, RESTRICT | Materia concreta; `NULL` indica cualquier materia |
| `pricing_unit_id` | `BIGINT UNSIGNED` | No | FK, RESTRICT | Unidad de cobro |
| `modality` | `VARCHAR(20)` | No | CHECK | `in_person`, `online` o `all` |
| `net_amount` | `DECIMAL(10,2)` | No | CHECK `>= 0` | Importe neto sin IVA |
| `currency` | `CHAR(3)` | No | `EUR`, CHECK | Moneda fija |
| `valid_from` | `DATE` | No | — | Primer día de vigencia, incluido |
| `valid_until` | `DATE` | Sí | CHECK | Último día incluido; `NULL` deja la vigencia abierta |
| `created_at` | `TIMESTAMP` | Sí | — | Fecha de creación |
| `updated_at` | `TIMESTAMP` | Sí | — | Fecha de modificación |

Las vigencias del mismo profesor, servicio, materia o ámbito general, modalidad y unidad no pueden solaparse. `TeacherServicePriceService` lo comprueba dentro de una transacción bloqueando el profesor. Los periodos existentes no se cierran automáticamente.

Las tarifas específicas pueden convivir con tarifas generales. La prioridad es: materia y modalidad exactas; materia exacta y cualquier modalidad; cualquier materia y modalidad exacta; cualquier materia y modalidad.

## Experiencia profesional

- Los lugares concretos donde ha trabajado el profesor permanecen en el currículum.
- No se creará por ahora una colección estructurada de empleos.
- Se mantiene como propuesta técnica mínima y provisional un resumen de experiencia estructurado y buscable, salvo rechazo expreso del cliente.

## Decisiones pendientes

| Asunto | Decisión necesaria |
|---|---|
| Correo del profesor | Si debe ser obligatorio y único |
| Programas o software e IA | Si una futura evolución necesita un catálogo cerrado de herramientas |
| Materias | Si el uso real necesita reglas adicionales de deduplicación sin convertirlas en catálogo cerrado |
| Experiencia | Forma del resumen estructurado y buscable que complementará al CV sin modelar una colección de empleos |
| Presencialidad | Localidades, provincias, distancia y desplazamientos |
| Acceso del profesor | Posible relación futura con `users` |
| Auditoría | Necesidad, alcance y retención de un registro de operaciones |
