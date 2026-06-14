# Privacidad y flujo de datos — ChemIllusion Study Cards

Este complemento está diseñado para ser **local primero** y respetuoso con la
privacidad. Esta página resume qué datos permanecen en Moodle y qué datos (si
los hay) salen de tu sitio.

## Principios

- El **modo local** puede usarse sin conectar una cuenta de ChemIllusion.
- Las herramientas conectadas requieren autorización o configuración.
- El administrador puede **desactivar todas las llamadas externas** con un solo interruptor.
- La fase inicial **no** envía calificaciones a ChemIllusion.
- La vinculación de cuenta es **opcional**.
- No se almacenan trazas detalladas de IA en ChemIllusion salvo que la persona usuaria o la institución lo habilite.
- Los datos enviados se limitan según el modo de privacidad configurado.

## Modos

### Modo local

En el modo local, el complemento funciona dentro de Moodle y no envía
solicitudes a ChemIllusion. Algunas funciones pueden consultar servicios
públicos como **PubChem** si el administrador las habilita. Las herramientas
avanzadas de ChemIllusion requieren una cuenta conectada o configuración
institucional.

### Modo conectado

Cuando un administrador habilita la vinculación de cuentas o las herramientas
SaaS, y una persona usuaria opta explícitamente por conectarse, el complemento
puede enviar datos mínimos a ChemIllusion para habilitar esas funciones.

## Qué datos se envían (solo si se habilita)

Cuando una persona usuaria vincula explícitamente una cuenta o pulsa un botón
del embudo, y solo si la función está habilitada, se puede enviar:

- una etiqueta estática de origen (por ejemplo, "moodle");
- el rol general (estudiante, profesor o administrador);
- qué herramienta de estudio originó la acción.

## Qué datos NO se envían

- Calificaciones.
- Listas de clase ni inscripciones.
- Enunciados sin procesar ni respuestas de estudiantes.
- Información personal identificable más allá de lo que la persona usuaria
  proporcione explícitamente al vincular una cuenta.

## Datos almacenados localmente en Moodle

- Una asignación mínima entre la persona usuaria de Moodle y una cuenta vinculada
  de ChemIllusion (incluido un hash unidireccional del correo, si se proporciona).
- Mazos y tarjetas de estudio creados por la persona usuaria.
- Resultados de búsqueda de PubChem en caché (durante el tiempo de vida configurado).

Consulta el proveedor de privacidad del complemento (`classes/privacy/provider.php`)
y la página de resumen en producto (`privacy.php`) para conocer los detalles
declarados a la API de privacidad de Moodle.

## Controles del administrador

- **Desactivar todas las llamadas externas:** interruptor maestro.
- **Habilitar/deshabilitar PubChem.**
- **Habilitar/deshabilitar la vinculación de cuentas.**
- **Modo mínimo (predeterminado):** almacena solo los datos locales necesarios.

## Para instituciones

Las instituciones pueden ejecutar el complemento totalmente en modo local, o
habilitar la vinculación de cuentas y las herramientas conectadas según sus
políticas. Antes de habilitar las funciones SaaS de ChemIllusion se muestra un
resumen del flujo de datos.
