# Guía de instalación — ChemIllusion Study Cards (`local_chemillusion`)

Esta guía explica cómo instalar y configurar el complemento en un sitio Moodle.
No se requiere Composer, acceso por consola, Conda, Python ni paquetes nativos.

## Requisitos

- Moodle compatible (consulta `version.php` para conocer la versión mínima admitida).
- Permisos de administrador del sitio para instalar complementos locales.
- Acceso saliente a PubChem **solo** si habilitas la búsqueda en PubChem (opcional).

## Instalar desde un ZIP

1. Descarga el ZIP de la versión: `dist/local_chemillusion-<versión>-moodleXX.zip`.
2. Inicia sesión como administrador y ve a **Administración del sitio → Complementos → Instalar complementos**.
3. Arrastra el ZIP a la zona de carga (o selecciónalo) y elige **Instalar complemento desde el archivo ZIP**.
4. Revisa la pantalla de validación y continúa con **Actualizar la base de datos de Moodle ahora**.

> El complemento se instala como cualquier complemento local estándar. No hay
> pasos adicionales específicos del español: las cadenas en español se cargan
> automáticamente cuando el idioma del sitio o del usuario es español.

## Configuración

Ve a **Administración del sitio → Complementos → Complementos locales → Tarjetas de estudio de ChemIllusion**.

### Modo de funcionamiento

- **Local solamente:** todas las herramientas de estudio funcionan dentro de Moodle, sin cuenta.
- **Local + vinculación de cuenta:** estudiantes y docentes pueden vincular una cuenta de ChemIllusion.
- **Local + herramientas SaaS:** habilita las funciones avanzadas de ChemIllusion.

### Servicios externos

- **Habilitar búsqueda en PubChem:** permite resolver moléculas mediante la API pública de PubChem (los resultados se guardan en caché).
- **Desactivar todas las llamadas externas:** interruptor maestro; cuando está activo, el complemento no realiza ninguna llamada de red externa.

### Conexión con ChemIllusion

- **URL base de ChemIllusion:** sitio usado para la vinculación de cuentas y los enlaces.
- **Secreto de firma de lanzamiento:** secreto compartido para firmar el estado de vinculación. Manténlo privado; nunca se expone en la página ni en el navegador.

## Cambiar el idioma del sitio a español

1. Ve a **Administración del sitio → Idioma → Paquetes de idioma** e instala "Español (es)" o "Español - Internacional".
2. Establece el idioma predeterminado del sitio en español, o permite que cada usuario elija su idioma.
3. Abre las páginas del complemento y verifica que la interfaz aparezca en español.

## Verificación posterior a la instalación

- La interfaz del complemento aparece en español cuando el idioma de Moodle es español.
- La búsqueda de moléculas funciona (si PubChem está habilitado) o muestra un mensaje claro si está desactivada.
- El resumen de privacidad (`privacy.php`) se muestra en español.

## Desinstalación

Ve a **Administración del sitio → Complementos → Visión general de complementos**, localiza
**local_chemillusion** y selecciona **Desinstalar**. Sigue las indicaciones para
eliminar los datos del complemento.

Para detalles de privacidad y flujo de datos, consulta `docs/PRIVACY.es.md`.
