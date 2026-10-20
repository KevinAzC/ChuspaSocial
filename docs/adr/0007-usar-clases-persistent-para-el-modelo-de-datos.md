# ADR-007: Usar clases persistent para el modelo de datos

## Estado

Aceptado.

## Contexto

ChuspaSocial necesita representar entidades de su modelo de datos, como publicaciones, comentarios, reacciones y anuncios, manteniendo reglas consistentes al leer y modificar información.

Moodle permite acceder directamente a la base de datos mediante `$DB`, pero también ofrece `\core\persistent` para representar registros mediante clases que centralizan la definición de propiedades, la validación y las operaciones habituales de persistencia.

## Decisión

Usar clases basadas en `\core\persistent` para representar las entidades principales del modelo de datos de ChuspaSocial.

Cada persistent definirá la tabla asociada y las propiedades de la entidad, de modo que la creación, lectura, actualización y eliminación de registros se realicen mediante una interfaz consistente. Las llamadas directas a `$DB` se reservarán para consultas específicas que no encajen adecuadamente en las operaciones de una persistent.

## Alternativas descartadas

- Usar llamadas directas a `$DB` para todas las operaciones del modelo. Se descarta porque repartiría la validación y las reglas de cada entidad entre distintos servicios, aumentando la duplicación y el acoplamiento con la estructura de las tablas.
- Crear una capa de acceso a datos propia sin utilizar `\core\persistent`. Se descarta porque duplicaría mecanismos que Moodle ya proporciona para definir propiedades, validar datos y manejar el ciclo de vida de los registros.

## Consecuencias

Las entidades principales tendrán una representación orientada a objetos y sus reglas básicas podrán mantenerse cerca de la definición de cada persistent. Esto facilita reutilizar validaciones, mantener un comportamiento consistente y probar el modelo de datos de forma aislada.

Como costo, será necesario crear y mantener una clase persistent por cada entidad que lo requiera y respetar las convenciones de `\core\persistent`. Las consultas complejas que involucren agregaciones o varias tablas podrán seguir utilizando `$DB` cuando resulte más apropiado.
