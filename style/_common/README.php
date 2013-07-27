<?php
/**
 * This is the beginning of the templating layer for web2project. Any HTML used
 *   within the core system will be contained within this directory. Each of
 *   these templates will be included within their modules similar to any other
 *   templating layer with a snippet like this:
 *
 *      include $AppUI->getTheme()->resolveTemplate('companies/list');
 *
 * which resolves to this file by default:
 *
 *      ./styles/_common/companies/list.php
 *
 * If you wish to customize this for your needs, create a file such as this:
 *
 *      ./styles/(your theme)/companies/list.php
 *
 * Once you select your preferred theme (via the user preferences), your custom
 *   file will override the default _common file automatically with no
 *   additional configuration. This same principle can be applied to all
 *   addedit, view, list, and subview templates.
 *
 * In some cases, the module-specific template may be overridden by a generic
 *   system-wide template such as:
 *
 *      ./styles/_common/list.php
 *
 * This generic template can be overriden in the same manner.
 */
