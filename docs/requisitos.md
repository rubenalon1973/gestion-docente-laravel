# Requisitos funcionales

Este documento reúne los requisitos acordados para la aplicación Gestión de Profesores. Los nombres del código y de la base de datos se escriben en inglés; la interfaz y los textos visibles para las personas usuarias estarán en español.

## Acceso y seguridad

- La aplicación será de uso interno y requerirá autenticación.
- No existirá registro público de usuarios.
- Todas las funciones de profesores, documentos, servicios y tarifas estarán protegidas.
- Los usuarios se crearán y sus contraseñas se restablecerán mediante comandos administrativos interactivos.
- La recuperación automática por correo y los roles diferenciados quedan fuera del MVP inicial.

## Profesores

Cada profesor podrá registrar:

- Nombre y apellidos.
- DNI/NIE opcional, único cuando tenga valor y normalizado sin espacios y en mayúsculas. No se validará todavía el algoritmo de la letra.
- Fecha y lugar de nacimiento.
- Teléfono, correo electrónico, dirección, localidad, código postal, provincia y país. Los datos de ubicación serán texto opcional para admitir formatos internacionales.
- Fotografía.
- Estado: activo, inactivo o archivado.
- Observaciones internas opcionales, con un máximo de 5.000 caracteres.

El listado permitirá búsqueda textual, filtros combinables, orden estable y paginación. El DNI/NIE y las observaciones internas no se mostrarán en el listado general.

Los profesores se mantienen separados de los usuarios de acceso. Esto permite añadir en el futuro un registro o área privada para profesores sin mezclar ambos conceptos.

## Formación y experiencia

- Titulaciones o estudios.
- Certificados y diplomas.
- Años de experiencia.
- El detalle completo de empresas o centros de trabajo permanecerá en el currículum.
- Sigue pendiente decidir si se añadirá un resumen estructurado de experiencia para facilitar búsquedas.

## Materias y servicios

- Las materias serán texto libre y reutilizable, no un catálogo cerrado.
- Un profesor podrá impartir una o varias materias.
- La modalidad podrá ser presencial, en línea o ambas.
- Se registrará por separado si el profesor crea contenido y si actualiza contenido.

## Disponibilidad

La disponibilidad se indicará por día, franja horaria y modalidad. Al crearla se podrán seleccionar varios días para generar la misma franja en cada uno; posteriormente cada franja se editará o eliminará de forma independiente.

## Programas e inteligencia artificial

Cada profesor podrá registrar uno o varios conocimientos mediante entradas independientes con:

- Categoría: programa o inteligencia artificial.
- Nombre de la herramienta.
- Nivel.

Las categorías iniciales serán programa e inteligencia artificial. Los niveles disponibles serán básico, intermedio y avanzado.

## Documentos

- Los archivos serán privados y se almacenarán fuera del directorio público.
- La base de datos guardará únicamente sus metadatos y una referencia al archivo físico.
- El nombre físico será opaco para evitar exponer datos personales.
- Los formatos admitidos dependerán de la categoría: currículum (`pdf`, `docx`), fotografía (`jpg`, `jpeg`, `png`, `webp`), titulación o certificado (`pdf`, `jpg`, `jpeg`, `png`) y otros (`pdf`, `docx`, `jpg`, `jpeg`, `png`).
- La extensión, el tipo MIME real y la categoría deberán ser compatibles entre sí. No se admitirán otros formatos.
- Cada subida corresponderá a un archivo.
- El tamaño máximo será de 10 MB.
- Incluir el nombre del profesor en el documento es recomendable, pero no será un bloqueo automático.

HEIC, TXT y cualquier extensión no incluida expresamente quedan excluidos.

## Tarifas

- Las tarifas se expresarán en euros.
- El importe comunicado será sin IVA y los impuestos se tratarán por separado.
- El precio podrá variar por profesor, servicio, materia y modalidad.
- El importe usará dos decimales y deberá ser igual o superior a cero.
- La unidad será ampliable; inicialmente se incluyen hora, curso y jornada.
- Los servicios se gestionarán mediante un catálogo administrable, con activación y desactivación sin borrado físico.
- Se conservará el histórico mediante fechas inclusivas de inicio y fin de vigencia.
- Una tarifa podrá ser general o quedar limitada por materia, modalidad o ambas.
- No se permitirán vigencias solapadas dentro del mismo profesor, servicio, materia o ámbito general, modalidad y unidad.
- Cuando coincidan tarifas generales y específicas se priorizará la coincidencia más concreta.

## Privacidad y datos

- En desarrollo se usarán exclusivamente datos ficticios.
- No se introducirán datos personales reales hasta disponer de las medidas de seguridad, permisos y entorno adecuados.
- Los despliegues de demostración utilizarán exclusivamente datos ficticios.
- Antes de utilizar información real deberán estar definidos accesos, backups, restauración, retención y capacidad para documentos privados.

## Funcionalidad aplazada

No bloquean el MVP inicial:

- Resumen estructurado y buscable de experiencia además del currículum.
- Recuperación automática de contraseña por correo.
- Roles diferentes y acceso propio para profesores.
- Cálculo o presentación de impuestos.
- Índices adicionales de búsqueda mientras no exista evidencia de problemas de rendimiento.
