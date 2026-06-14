<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Spanish (international / Latin American neutral) language strings for local_chemillusion.
 *
 * Mirrors every key in lang/en/local_chemillusion.php. Terminology follows
 * docs/i18n/spanish-chemistry-glossary.md. Technical tokens (SMILES, InChI,
 * InChIKey, PubChem, RDKit, WASM, CID) are intentionally left untranslated.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Tarjetas de estudio de ChemIllusion';
$string['plugindescription'] = 'Herramientas de química para estudiar en Moodle, respetuosas con la privacidad: búsqueda de moléculas, representación de estructuras con RDKit.js, resaltado de grupos funcionales, tarjetas de estudio y vinculación opcional con una cuenta de ChemIllusion.';

// Navigation / pages.
$string['nav_studytools'] = 'Herramientas de estudio de ChemIllusion';
$string['dashboard_heading'] = 'Tarjetas de estudio de ChemIllusion';
$string['dashboard_intro'] = 'Herramientas gratuitas de química para estudiar dentro de Moodle. Busca moléculas, arma mazos de estudio y aprende grupos funcionales y reactivos.';
$string['tools_heading'] = 'Búsqueda de moléculas y herramientas de estudio';
$string['cards_heading'] = 'Mazos de estudio';

// Settings: mode.
$string['settings_mode_heading'] = 'Modo de funcionamiento';
$string['settings_mode_desc'] = 'Elige cuánto de ChemIllusion se habilita. El modo local solamente mantiene todas las herramientas de estudio dentro de Moodle, sin necesidad de cuenta.';
$string['settings_mode'] = 'Modo de ChemIllusion';
$string['settings_mode_help'] = 'Local solamente: solo herramientas de estudio. Local + vinculación: estudiantes y docentes pueden vincular una cuenta de ChemIllusion. Local + SaaS: habilita las funciones avanzadas de ChemIllusion.';
$string['mode_local_only'] = 'Local solamente';
$string['mode_local_link'] = 'Local + vinculación de cuenta de ChemIllusion';
$string['mode_local_saas'] = 'Local + herramientas SaaS de ChemIllusion';

// Settings: external services.
$string['settings_external_heading'] = 'Servicios externos';
$string['settings_external_desc'] = 'Controla qué llamadas externas puede hacer el complemento. Los administradores pueden desactivar todas las llamadas externas.';
$string['settings_enable_pubchem'] = 'Habilitar búsqueda en PubChem';
$string['settings_enable_pubchem_help'] = 'Permite resolver moléculas en el servidor mediante la API REST pública de PubChem. Los resultados se almacenan en caché localmente.';
$string['settings_enable_account_linking'] = 'Habilitar vinculación de cuenta de ChemIllusion';
$string['settings_enable_account_linking_help'] = 'Muestra botones que permiten a las personas usuarias vincular o crear una cuenta de ChemIllusion. Nunca se envían calificaciones ni listas de clase.';
$string['settings_enable_visual_preview'] = 'Habilitar ejemplos de vista previa visual de ChemIllusion';
$string['settings_enable_visual_preview_help'] = 'Muestra imágenes de ejemplo incluidas con el complemento, del estilo de las tarjetas visuales de ChemIllusion. No se genera ninguna imagen anónima dentro de Moodle.';
$string['settings_enable_conversion_metadata'] = 'Habilitar metadatos externos de analítica/conversión';
$string['settings_enable_conversion_metadata_help'] = 'Con consentimiento, envía a ChemIllusion metadatos mínimos de origen (sin datos personales) cuando una persona usuaria pulsa un botón del embudo.';
$string['settings_enable_rdkit'] = 'Habilitar el modo de química local con RDKit WASM (Fase 1B)';
$string['settings_enable_rdkit_help'] = 'Carga de forma diferida el paquete RDKit.js/WASM incluido en las páginas de herramientas de estudio, para validar y representar SMILES y detectar grupos funcionales en el navegador.';

// Settings: connection.
$string['settings_conn_heading'] = 'Conexión con ChemIllusion';
$string['settings_conn_desc'] = 'Dónde se aloja ChemIllusion y cómo se firman los tokens de lanzamiento. El secreto de firma nunca llega al navegador.';
$string['settings_base_url'] = 'URL base de ChemIllusion';
$string['settings_base_url_help'] = 'URL base del sitio de ChemIllusion que se usa para la vinculación de cuentas y los enlaces de los botones.';
$string['settings_signing_secret'] = 'Secreto de firma de lanzamiento';
$string['settings_signing_secret_help'] = 'Secreto compartido que se usa para firmar el estado de vinculación/lanzamiento de cuenta. Manténlo privado; nunca se expone en la página ni en el JavaScript.';

// Settings: privacy.
$string['settings_privacy_heading'] = 'Privacidad';
$string['settings_privacy_desc'] = 'Los valores predeterminados favorecen los datos mínimos. Se muestra un resumen del flujo de datos antes de habilitar las funciones SaaS de ChemIllusion.';
$string['settings_minimal_mode'] = 'Modo mínimo (predeterminado)';
$string['settings_minimal_mode_help'] = 'Almacena solo los datos locales mínimos necesarios para las herramientas de estudio y la asignación de cuentas.';
$string['settings_disable_external'] = 'Desactivar todas las llamadas externas';
$string['settings_disable_external_help'] = 'Interruptor maestro: cuando se activa, el complemento no realiza ninguna llamada de red externa.';
$string['settings_cache_ttl'] = 'Duración de la caché de búsqueda (segundos)';
$string['settings_cache_ttl_help'] = 'Cuánto tiempo se almacenan en caché en el servidor los resultados de PubChem. El valor predeterminado es una semana (604800 segundos).';

// Capabilities.
$string['chemillusion:view'] = 'Usar las herramientas de estudio de ChemIllusion';
$string['chemillusion:managedecks'] = 'Crear y gestionar mazos de estudio de ChemIllusion';
$string['chemillusion:viewdashboard'] = 'Ver el panel de conversión de ChemIllusion';
$string['chemillusion:link'] = 'Vincular una cuenta de ChemIllusion';

// Lookup UI.
$string['lookup_label'] = 'Nombre de molécula, SMILES, InChI o InChIKey';
$string['lookup_placeholder'] = 'p. ej. aspirina, CC(=O)Oc1ccccc1C(=O)O';
$string['lookup_button'] = 'Buscar';
$string['lookup_inputtype'] = 'Tipo de entrada detectado: {$a}';
$string['result_name'] = 'Nombre preferido';
$string['result_cid'] = 'CID de PubChem';
$string['result_formula'] = 'Fórmula molecular';
$string['result_mw'] = 'Peso molecular';
$string['result_canonical_smiles'] = 'SMILES canónico';
$string['result_isomeric_smiles'] = 'SMILES isomérico';
$string['result_inchikey'] = 'InChIKey';
$string['result_pubchem_link'] = 'Ver en PubChem';
$string['result_open_chemillusion'] = 'Abrir en ChemIllusion';
$string['result_textonly'] = 'Mostrar versión solo de texto';

// Decks / cards.
$string['deck_create'] = 'Crear mazo';
$string['deck_name'] = 'Nombre del mazo';
$string['deck_addcard'] = 'Agregar al mazo';
$string['deck_empty'] = 'Aún no hay mazos. Crea uno para empezar a estudiar.';
$string['deck_type_molecule'] = 'Identidad de la molécula';
$string['deck_type_functional_group'] = 'Reconocimiento de grupos funcionales';
$string['deck_type_reagent'] = 'Acrónimo de reactivo';
$string['deck_type_name_smiles'] = 'De nombre a SMILES';
$string['deck_type_formula_mw'] = 'Fórmula / peso molecular';
$string['flashcard_show_answer'] = 'Mostrar respuesta';
$string['flashcard_next'] = 'Siguiente tarjeta';
$string['flashcard_prev'] = 'Tarjeta anterior';
$string['flashcard_progress'] = 'Tarjeta {$a->index} de {$a->total}';
$string['flashcard_textonly'] = 'Versión solo de texto';

// Functional groups / reagents.
$string['functional_groups_heading'] = 'Grupos funcionales detectados';
$string['functional_group_highlight'] = 'Resaltar';
$string['functional_group_makecard'] = 'Crear tarjeta de estudio';
$string['common_mistake'] = 'Error frecuente';
$string['reagent_role'] = 'Función';
$string['reagent_use'] = 'Uso común';

// CTAs / funnel.
$string['cta_continue_chemillusion'] = 'Continuar en ChemIllusion';
$string['cta_save_deck'] = 'Guardar este mazo en ChemIllusion';
$string['cta_visual_card'] = 'Generar una tarjeta de estudio visual en ChemIllusion';
$string['cta_guided_workspace'] = 'Probar el espacio de trabajo guiado de moléculas';
$string['cta_visual_card_blurb'] = 'Hazlo memorable en ChemIllusion: genera una tarjeta de estudio visual con tu molécula y el grupo funcional resaltado.';
$string['cta_teacher_account'] = 'Crear una cuenta gratuita de profesor en ChemIllusion';
$string['cta_teacher_demo'] = 'Agendar una demo de ChemIllusion';
$string['cta_teacher_convert'] = 'Convertir este mazo en una actividad guiada de ChemIllusion';
$string['cta_teacher_visualset'] = 'Generar tarjetas de estudio visuales para tu clase';
$string['cta_accessible_readout'] = 'Probar la lectura accesible de moléculas';
$string['teacher_dashboard_blurb'] = 'Tus estudiantes pueden usar gratis las tarjetas de estudio locales de Moodle. Vincula una cuenta de profesor de ChemIllusion para crear mazos visuales más completos, tutoriales guiados, lecturas accesibles de moléculas y actividades listas para el curso.';

// Account linking.
$string['link_heading'] = 'Vincula tu cuenta de ChemIllusion';
$string['link_start'] = 'Conectar ChemIllusion';
$string['link_status_pending'] = 'Vinculación pendiente';
$string['link_status_linked'] = 'Cuenta vinculada';
$string['link_success'] = 'Tu cuenta de ChemIllusion ya está vinculada.';
$string['link_disabled'] = 'La vinculación de cuentas no está habilitada en este sitio.';

// RDKit status.
$string['rdkit_loading'] = 'Cargando el motor de química local…';
$string['rdkit_ready'] = 'Motor de química local listo';
$string['rdkit_failed'] = 'El motor de química local no está disponible; se muestran los datos de texto y de PubChem en su lugar.';
$string['rdkit_disabled'] = 'El modo local de RDKit está desactivado por el administrador.';

// Dashboard metrics.
$string['metric_lookups'] = 'Búsquedas de moléculas';
$string['metric_decks'] = 'Mazos creados';
$string['metric_sessions'] = 'Sesiones de estudio';
$string['metric_link_clicks'] = 'Clics para vincular cuenta';
$string['metric_demo_clicks'] = 'Clics de demo para profesores';

// Privacy summary page.
$string['privacy_page_heading'] = 'Resumen del flujo de datos de ChemIllusion';
$string['privacy_page_intro'] = 'Esta página explica exactamente qué datos almacena el complemento y qué datos (si los hay) salen de tu sitio Moodle.';

// Errors.
$string['error_nomatch'] = 'Ninguna molécula coincidió con tu búsqueda. Revisa la ortografía o prueba con una cadena SMILES.';
$string['error_ratelimited'] = 'El servicio de búsqueda está ocupado. Inténtalo de nuevo en un momento.';
$string['error_network'] = 'No se pudo conectar con el servicio de búsqueda. Inténtalo de nuevo más tarde.';
$string['error_external_disabled'] = 'Las búsquedas externas están desactivadas por el administrador.';
$string['error_invalidinput'] = 'Ingresa un nombre de molécula, SMILES, InChI o InChIKey.';
$string['error_invalidsesskey'] = 'Tu sesión expiró. Vuelve a cargar la página e inténtalo de nuevo.';

// Tasks.
$string['purgeexpiredcachetask'] = 'Purgar la caché de búsqueda de ChemIllusion vencida';

// Privacy API metadata.
$string['privacy:metadata:local_chemillusion_links'] = 'Asignación mínima entre una persona usuaria de Moodle y una cuenta vinculada de ChemIllusion.';
$string['privacy:metadata:local_chemillusion_links:userid'] = 'La persona usuaria de Moodle que vinculó una cuenta.';
$string['privacy:metadata:local_chemillusion_links:chemillusion_user_id'] = 'El identificador opaco de la cuenta de ChemIllusion.';
$string['privacy:metadata:local_chemillusion_links:chemillusion_email_hash'] = 'Un hash unidireccional del correo usado para vincular, si se proporcionó.';
$string['privacy:metadata:local_chemillusion_links:linked_at'] = 'Cuándo se vinculó la cuenta.';
$string['privacy:metadata:local_chemillusion_links:last_launch_at'] = 'Cuándo lanzó ChemIllusion por última vez la persona usuaria.';
$string['privacy:metadata:local_chemillusion_decks'] = 'Mazos de estudio creados por la persona usuaria.';
$string['privacy:metadata:local_chemillusion_decks:userid'] = 'La persona usuaria que creó el mazo.';
$string['privacy:metadata:local_chemillusion_decks:name'] = 'El nombre del mazo.';
$string['privacy:metadata:local_chemillusion_decks:created_at'] = 'Cuándo se creó el mazo.';
$string['privacy:metadata:local_chemillusion_cards'] = 'Tarjetas dentro de un mazo de estudio creado por la persona usuaria.';
$string['privacy:metadata:local_chemillusion_cards:prompt'] = 'El texto del enunciado de la tarjeta.';
$string['privacy:metadata:local_chemillusion_cards:answer'] = 'El texto de la respuesta de la tarjeta.';
$string['privacy:metadata:chemillusion_saas'] = 'Información enviada a ChemIllusion cuando una persona usuaria vincula explícitamente una cuenta o pulsa un botón del embudo (solo si está habilitado).';
$string['privacy:metadata:chemillusion_saas:source'] = 'Una etiqueta estática de origen (por ejemplo, "moodle").';
$string['privacy:metadata:chemillusion_saas:role'] = 'El rol general (estudiante, profesor o administrador).';
$string['privacy:metadata:chemillusion_saas:surface'] = 'Qué herramienta de estudio originó la acción.';
