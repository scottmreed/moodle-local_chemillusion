# Tarjetas de estudio de ChemIllusion para Moodle (`local_chemillusion`)

Complemento de química **para estudiar** en Moodle, de código abierto y
respetuoso con la privacidad: búsqueda de moléculas, representación de
estructuras en el navegador con RDKit.js, resaltado de grupos funcionales,
tarjetas de estudio para estudiantes, resúmenes accesibles y vinculación
opcional con una cuenta de ChemIllusion para herramientas avanzadas de IA,
imágenes, video y flujos de trabajo para docentes.

> Este complemento **no** se promociona como la primera herramienta de dibujo
> químico para Moodle. Moodle ya tiene editores de química. Nuestra propuesta
> es el estudio con RDKit, la accesibilidad, la vinculación de cuentas y la
> escalada visual/IA con ChemIllusion.

- **Componente:** `local_chemillusion`
- **Licencia:** GPL-3.0-or-later (consulta `LICENSE`)
- **Estado:** Alfa (0.1.0) — Fase 1A + andamiaje de la Fase 1B
- **Química incluida:** RDKit.js / RDKit WASM (BSD-3-Clause), de carga diferida; consulta `THIRD_PARTY.md`

## Fases

| Fase | Qué agrega |
|------|------------|
| **1A — Base apta para el directorio** | Complemento en PHP: ajustes de administración, búsqueda en PubChem con caché en el servidor, mazos y tarjetas de estudio, diccionarios de reactivos y grupos funcionales, embudo de vinculación de cuenta y proveedor de privacidad. No requiere RDKit. |
| **1B — Modo local con RDKit WASM** | Incluye RDKit.js/WASM en el ZIP del complemento, de carga diferida solo en las páginas de herramientas: validación de SMILES, representación SVG, coincidencia de grupos funcionales por SMARTS, resaltado estático de átomos/enlaces y tarjetas más ricas. |

## Qué permanece privado

El overlay completo de Ketcher de ChemIllusion, el arnés del agente, el
inventario privado MCP, los detalles internos de generación de imágenes/video,
la facturación y el ChemTutor avanzado permanecen en el repositorio privado
`chem-art-generator`. Este complemento solo consume contratos y enlaces
**públicos y acotados** de ChemIllusion.

## Instalación (administradores)

1. Descarga un ZIP de versión (`dist/local_chemillusion-<versión>-moodleXX.zip`).
2. En Moodle: **Administración del sitio → Complementos → Instalar complementos** y sube el ZIP.
3. Completa la actualización y luego visita **Administración del sitio → Complementos → Complementos locales → Tarjetas de estudio de ChemIllusion** para configurar el modo, los servicios externos y la privacidad.

No se requiere Composer, acceso por consola, Conda, Python ni paquetes nativos.

Consulta `docs/INSTALL.es.md` para la guía de instalación detallada en español.

## Privacidad de un vistazo

- La instalación predeterminada es **local solamente**, con búsqueda opcional en PubChem y **sin** vinculación de cuentas hasta que un administrador la habilite.
- No se envían calificaciones, listas de clase, enunciados sin procesar ni respuestas de estudiantes a ChemIllusion.
- Los administradores pueden desactivar **todas** las llamadas externas con un solo interruptor.
- Consulta `docs/PRIVACY.es.md` y el resumen en producto de `privacy.php`.

## Documentación en español

- `docs/INSTALL.es.md` — guía de instalación.
- `docs/PRIVACY.es.md` — modos de privacidad y flujo de datos.
- `docs/TEACHER_QUICKSTART.es.md` — inicio rápido para docentes.
- `docs/i18n/spanish-chemistry-glossary.md` — glosario de química y términos del producto.

## Desarrollo

Consulta `docs/local-dev-testing.md`. El entorno local de Moodle vive en el
repositorio aparte `scottmreed/Moodle-plugin-dev`, que monta este repositorio en
`/var/www/html/local/chemillusion`.

---

Mantenido por MolLogic / Scott Reed.
