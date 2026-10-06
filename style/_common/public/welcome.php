<?php /** @var w2p_Core_CAppUI $AppUI */ ?>
<table cellspacing="2" cellpadding="4" border="0" class="tbl info" width="80%" style="border-radius: 10px;">
    <tr><th><span><?php echo sprintf($AppUI->_('Welcome to %s'), 'web2Project'); ?></span></th></tr>
    <tr>
        <td>
            <p><?php echo sprintf($AppUI->_('%s is an Open Source business oriented Project Management System (PMS) built for flexibility and reuse.'), '<a href="http://www.web2Project.net/" target="_blank"><b>web2Project</b></a>'); ?>
                <?php echo sprintf($AppUI->_('Some of the key benefits of %s are'), '<b>web2Project</b>'); ?>:</p>
            <ul>
                <li><?php echo $AppUI->_('A centralized platform for project communication to avoid confusion and risks'); ?></li>
                <li><?php echo $AppUI->_('Gantt charts to describe both the task and overall project progress'); ?></li>
                <li><?php echo $AppUI->_('A secure web-based infrastructure capable of managing any amount of projects, companies, departments and users'); ?></li>
                <li><?php echo $AppUI->_('A modular infrastructure that allows adding and removing modules customize the system, activites, and reporting to your business needs'); ?></li>
                <li><?php echo $AppUI->_('A role-based and relationship-based access control system (ReBAC and RBAC) to give fine-grained control over sensitive data'); ?></li>
                <li><?php echo $AppUI->_('A shared calendar capturing tasks and events across projects that respects permissions'); ?></li>
            </ul>
        </td>
    </tr>
    <tr><th><span><?php echo $AppUI->_('web2Project Online Assistance'); ?></span></th></tr>
    <tr>
        <td>
            <p><?php echo $AppUI->_('We have 3 main places for your assistance, and 1 link for custom development help. Please remember that all of these places are our community. You can help others as well by contributing your thoughts and problems you\'re having, as well as the solutions you develop. This is "open source software" at it\'s best; we\'re all in this together.'); ?></p>
            <p><?php echo $AppUI->_('Here are several places where you can find help online:'); ?></p>

            <h1>web2Project Docs</h1>
            <ul>
                <li>The <a href="https://docs.web2project.net/">web2project Wiki</a> (e.g. explanation of modules, features, the Hook system, etc). The wiki is also a community project and you are invited to contribute to it. Learn more about <a href="https://docs.web2project.net/docs/join-the-team.html">becoming a member of the team, just click here</a>.</li>
                <li>Find some <a href="https://github.com/web2project">additional community created User Guides (pictures and everything!) right here</a>. Download them and open the files! They're a few years old at this point but you may still find the answer you're looking for in there.</li>
            </ul>
            <h1>web2Project Forums</h1>
            <ul>
                <li>Find the <a href="http://support.web2project.net/">current support forum here</a>.</li>
                <li>Our <a href="http://forums.web2project.net/">previous support forum</a> has a lot of good information in it, and can still be accessed. This one is not officially supported anymore so don't drop anything new in it and expect an answer!</li>
            </ul>
            <h1>web2Project Issue Tracker</h1>
            <ul>
                <li>For those of you who are a bit more advanced in your open source software involvement, information about known problems and suggested enhancements can be read and submitted at <a href="http://bugs.web2project.net/my_view_page.php">our Mantis site account</a>.</li>
            </ul>
            <p>Thanks for using web2Project!</p>
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