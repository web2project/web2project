<?php /* $Id$ $URL$ */

/**
 * 	@package web2project
 * 	@subpackage core
 * 	@version $Revision$
 */

/**
 * 	w2p_Core_HookHandler Class
 *
 * This class processes all of the hooks in a consistent and error-resistant
 *   manner.
 * 
 * The original model for this functionality was based on Drupal's methods for
 *   laying out and interacting with hooks.  It should not be considered
 *   complete at this time.
 *
 * 	@author Keith Casey <caseydk@sourceforge.net>
 *
 */
class w2p_Core_HookHandler
{

    protected $AppUI = null;

    public function __construct(w2p_Core_CAppUI $AppUI)
    {
        $this->AppUI = $AppUI;
    }

    /*
     * This is the generic hook handler that counts on no result, feedback, etc.
     *   In general, this may be enough, but the additional methods may be
     *   useful too.
     */

    public function process($name)
    {
        $hookname = 'hook_' . $name;
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

    public function calendar()
    {
        $hookname = 'hook_calendar';
        $moduleList = $this->AppUI->getLoadableModuleList();

        foreach ($moduleList as $module) {
            if (class_exists($module['mod_main_class'])) {
                $object = new $module['mod_main_class']();
                if (is_callable(array($object, $hookname))) {
                    $itemList = $object->{$hookname}($this->AppUI->user_id);
                    foreach ($itemList as $calendarItem) {
                        $buffer .= w2p_API_iCalendar::formatCalendarItem($calendarItem, $module['mod_directory']);
                    }
                }
            }
        }

        return $buffer;
    }

    public function calendar_links()
    {
        $hookname = 'hook_calendar_links';
        $moduleList = $this->AppUI->getLoadableModuleList();

        foreach ($moduleList as $module) {
            if (class_exists($module['mod_main_class'])) {
                $object = new $module['mod_main_class']();
                if (is_callable(array($object, $hookname)) && is_callable(array($object, 'getCalendarLink'))) {
                    $itemList = $object->{$hookname}($this->AppUI->user_id);
                    foreach ($itemList as $item) {
                        $dateIndex = str_replace('/', '', $item['startDate']);
                        $this->links[$dateIndex][] = $object->getCalendarLink($AppUI, $item);
                    }
                }
            }
        }

        return $this->links;
    }

}