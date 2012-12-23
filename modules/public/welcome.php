<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Welcome', 'public.png', $m, $m . '.' . $a);
$titleBlock->show();
?>

<table cellspacing="2" cellpadding="4" border="0" class="tbl info" width="100%">
    <tr><th><span><?php echo sprintf($AppUI->_('Welcome to %s'), 'web2Project'); ?></span></th></tr>
    <tr>
        <td>
            <p><?php echo sprintf($AppUI->_('%s is an Open Source business oriented Project Management System (PMS) built for the future.'), '<a href="http://www.web2Project.net/" target="_blank"><b>web2Project</b></a>'); ?>
            <?php echo sprintf($AppUI->_('Some of the key benefits of %s are'), '<b>web2Project</b>'); ?>:</p>
            <ul>
                <li><?php echo $AppUI->_('A centralized platform for project communication to avoid confusion and risks'); ?></li>
                <li><?php echo $AppUI->_('Gantt charts to describe both the task and overall project level'); ?></li>
                <li><?php echo $AppUI->_('A secure web-based infrastructure capable of managing any amount of projects, companies, departments and users'); ?></li>
                <li><?php echo $AppUI->_('A modular infrastructure that allows adding and removing core and custom modules to aid the Project Manager in his job from the cleanest to the meanest business environments'); ?></li>
                <li><?php echo $AppUI->_('A role-based permission system to make the user management flexible to give the Project Manager fine-grained control over sensitive data'); ?></li>
                <li><?php echo $AppUI->_('A shared calendar capturing tasks and events across projects but still limited by roles and permissions'); ?></li>
            </ul>
        </td>
    </tr>
    <tr><th><span><?php echo $AppUI->_('web2Project Online Assistance'); ?></span></th></tr>
    <tr>
        <td>
            <p><?php echo $AppUI->_('We have 3 main places for your assistance. Please remember that all of these places are not one-way: You can help others as well by contributing.'); ?></p>
            <ul>
                <li><?php echo sprintf($AppUI->_('The %1$s%3s Online Documentation%2$s is intended to provide you with informations about how to use %3$s (e.g. explanation of modules, features, training ...).'), '<a href="http://wiki.web2Project.net/" target="_blank">', '</a>', '<b>web2Project</b>'); ?></li>
                <li><?php echo sprintf($AppUI->_('Further Assistance can be found in the %1$s%2$s Support Forums%3$s.'), '<a href="http://forums.web2Project.net/" target="_blank">', '<b>web2Project</b>', '</a>'); ?></li>
                <li><?php echo sprintf($AppUI->_('Information about known problems and suggested enhancements can be read and submitted at the %1$s%2$s Bugs & Suggestions%3$s site.'), '<a href="http://bugs.web2Project.net/" target="_blank">', '<b>web2Project</b>', '</a>'); ?></li>
            </ul>
            <p><?php echo sprintf($AppUI->_('If you\'re looking for Professional Support concerning %s consider getting in touch with the developers directly on the forums.'), '<a href="http://www.web2Project.net/" target="_blank"><b>web2Project</b></a>'); ?></p>
        </td>
    </tr>
    <tr><th><span><?php echo $AppUI->_('Terms of Use'); ?></span></th></tr>
    <tr>
        <td>
            <p><?php echo sprintf($AppUI->_('Each organization may have different Terms of Use for the %s software, depending on applicable law and regulations on each Country and/or State, and inner organization usage and workflow rules.'), '<b>web2Project</b>'); ?>
            <?php echo $AppUI->_('Please contact your System Administrator to get a copy of the Terms of Use applicable to you.'); ?></p>
            <p><b><?php echo $AppUI->_('All uses of this product against the Law are forbidden.'); ?></b></p>
        </td>
    </tr>
    <tr><th><span><?php echo $AppUI->_('License and Disclaimer or Warranty'); ?></span></th></tr>
    <tr>
        <td>
            <p><?php echo sprintf($AppUI->_('%s is free software. You can redistribute it and/or modify it under the terms of the Clear BSD License, a copy of which is available within the installation package.'), '<b>web2Project</b>'); ?></p>
            <p><?php echo $AppUI->_('It is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.'); ?></p>
            <p><?php echo $AppUI->_('See the Clear BSD Public License for more details.'); ?>
            <?php echo $AppUI->_('You should have received a copy along with web2Project. If not, you can download the'); ?> <a href="http://labs.metacarta.com/license-explanation.html">Clear BSD License from MetaCarta</a></p>
            <p><?php echo $AppUI->_('The web2Project Team'); ?></p>
        </td>
    </tr>
    <tr><th><span><?php echo $AppUI->_('Third Party Credits and Licenses'); ?></span></th></tr>
    <tr>
        <td width="100%" valign="top">
            <p><?php echo sprintf($AppUI->_('The following Third Party libraries/scripts were used in making %1$s, but %2$swe are not endorsed by any of them%3$s and only show here for proper Credit and as a token of gratitude for the amazing effort provided by their developers and communities in building such quality libraries.'), '<b>web2Project</b>', '<b>', '</b>'); ?></p>

            <p><?php echo $AppUI->_('We wish to thank all the following Third Party projects in alphabetical order'); ?>:</p>
            <ul>
                <li><a href="http://adodb.sourceforge.net/" target="_blank">ADOdb</a> <?php echo $AppUI->_('is dual licensed using BSD and LGPL'); ?>.</li>
                <li><a href="http://gifs.hu" target="_blank">Captcha</a> <?php echo $AppUI->_('by László Zsidi, no license information'); ?>.</li>
                <li><a href="http://www.dotproject.net" target="_blank">dotProject</a> <?php echo $AppUI->_('originally licensed BSD, then GPL 2'); ?>.</li>
                <li><a href="http://www.ros.co.nz/pdf/" target="_blank">(ezpdf) PHP Pdf creation</a> <?php echo $AppUI->_('licensed Public Domain'); ?>.</li>
                <li><a href="http://www.urwpp.de" target="_blank">Free UCS scalable fonts project</a> <?php echo $AppUI->_('licensed GPL'); ?>.</li>
                <li><a href="http://www.aditus.nu/jpgraph/" target="_blank">JPGraph</a> <?php echo $AppUI->_('licensed Q PUBLIC LICENSE version 1.0'); ?>.</li>
                <li><a href="http://dynarch.com/mishoo/calendar.epl" target="_blank">JSCalendar</a> <?php echo $AppUI->_('licensed GNU Lesser General Public License'); ?>.</li>
                <li><a href="http://www.php.net" target="_blank">Pear</a> <?php echo $AppUI->_('licensed PHP license 2'); ?>.</li>
                <li><a href="http://phpgacl.sourceforge.net/" target="_blank">phpGACL</a> <?php echo $AppUI->_('licensed GNU Lesser General Public License'); ?>.</li>
                <li><a href="http://phpmailer.codeworxtech.com/" target="_blank">PHPMailer</a> <?php echo $AppUI->_('licensed GNU Library or Lesser General Public License (LGPL)'); ?>.</li>
                <li><a href="http://www.xajaxproject.org/" target="_blank">xAjax</a> <?php echo $AppUI->_('licensed BSD.'); ?></li>
            </ul>
        </td>
    </tr>
</table>
