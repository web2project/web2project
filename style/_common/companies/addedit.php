<form name="changeclient" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="form-horizontal addeidt companies">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />

    <div class="std addedit companies">
        <div class="column left">
            <p>
                <label><?php echo $AppUI->_('Company Name'); ?>:</label>
                <input type="text" class="text" name="company_name" value="<?php echo w2PformSafe($company->company_name); ?>" size="50" maxlength="255" /> (<?php echo $AppUI->_('required'); ?>)
            </p>
            <p>
                <label><?php echo $AppUI->_('Email'); ?>:</label>
                <input type="text" class="text" name="company_email" value="<?php echo w2PformSafe($company->company_email); ?>" size="30" maxlength="255" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Phone'); ?>:</label>
                <input type="text" class="text" name="company_phone1" value="<?php echo w2PformSafe($company->company_phone1); ?>" maxlength="30" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Phone'); ?>2:</label>
                <input type="text" class="text" name="company_phone2" value="<?php echo w2PformSafe($company->company_phone2); ?>" maxlength="50" />
            </p>
            <p>
                <label><?php echo $AppUI->_('URL'); ?>2:</label>
                <input type="text" class="text" value="<?php echo w2PformSafe($company->company_primary_url); ?>" name="company_primary_url" size="50" maxlength="255" />
                <a href="javascript: void(0);" onclick="testURL('CompanyURLOne')">[<?php echo $AppUI->_('test'); ?>]</a>
            </p>
            <p>
                <label><?php echo $AppUI->_('Description'); ?>:</label>
                <textarea name="company_description"><?php echo $company->company_description; ?></textarea>
            </p>
            <p>
                <?php
                $custom_fields = new w2p_Core_CustomFields($m, $a, $company->company_id, "edit");
                $custom_fields->printHTML();
                ?>
            </p>
            <p>
                <input type="button" value="<?php echo $AppUI->_('back'); ?>" class="cancel button btn btn-danger" onclick="javascript:history.back(-1);" />
            </p>
        </div>
        <div class="column right">
            <p>
                <label><?php echo $AppUI->_('Address'); ?>1:</label>
                <input type="text" class="text" name="company_address1" value="<?php echo w2PformSafe($company->company_address1); ?>" size="50" maxlength="255" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Address'); ?>2:</label>
                <input type="text" class="text" name="company_address2" value="<?php echo w2PformSafe($company->company_address2); ?>" size="50" maxlength="255" />
            </p>
            <p>
                <label><?php echo $AppUI->_('City'); ?>:</label>
                <input type="text" class="text" name="company_city" value="<?php echo w2PformSafe($company->company_city); ?>" size="50" maxlength="50" />
            </p>
            <p>
                <label><?php echo $AppUI->_('State'); ?>:</label>
                <input type="text" class="text" name="company_state" value="<?php echo w2PformSafe($company->company_state); ?>" maxlength="50" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Zip'); ?>:</label>
                <input type="text" class="text" name="company_zip" value="<?php echo w2PformSafe($company->company_zip); ?>" maxlength="15" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Country'); ?>:</label>
                <?php
                echo arraySelect($countries, 'company_country', 'size="1" class="text"', $company->company_country ? $company->company_country : 0);
                ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Fax'); ?>:</label>
                <input type="text" class="text" name="company_fax" value="<?php echo w2PformSafe($company->company_fax); ?>" maxlength="30" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Company Owner'); ?>:</label>
                <?php
                $perms = &$AppUI->acl();
                $users = $perms->getPermittedUsers('companies');
                echo arraySelect($users, 'company_owner', 'size="1" class="text"', $company->company_owner ? $company->company_owner : $AppUI->user_id);
                ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Type'); ?>:</label>
                <?php
                echo arraySelect($types, 'company_type', 'size="1" class="text"', $company->company_type, true);
                ?>
            </p>
            <p>
                <input type="button" value="<?php echo $AppUI->_('save'); ?>" class="save button btn btn-primary" onclick="submitIt()" />
            </p>
        </div>
    </div>
</form>