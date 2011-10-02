<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 */

/**
 *	w2p_Core_HookHandler Class
 *
 * This class processes all of the hooks in a consistent and error-resistant
 *   manner.
 * 
 * The original model for this functionality was based on Drupal's methods for
 *   laying out and interacting with hooks.  It should not be considered
 *   complete at this time.
 *
 *	@author Keith Casey <caseydk@sourceforge.net>
 *
 */
class w2p_Core_HookHandler
{
    protected $AppUI = null;

    public function __construct(w2p_Core_CAppUI $AppUI)
    {
        $this->AppUI = $AppUI;
    }

    public function process($name)
    {
        $hookname = 'hook_'.$name;
        $moduleList = $this->AppUI->getLoadableModuleList();

        foreach ($moduleList as $module) {
            if (class_exists($module['mod_main_class'])) {
                $object = new $module['mod_main_class']();
                if (is_callable(array($object, $hookname))) {
                    $object->{$hookname}();
                }
            }
        }
    }
}