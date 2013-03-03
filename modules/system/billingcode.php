<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$company_id = (int) w2PgetParam($_POST, 'company_id', -1);
$billingcode_id = (int) w2PgetParam($_GET, 'billingcode_id', 0);

if (!canEdit('system')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$bcode = new CSystem_Bcode();
$bcode->load($billingcode_id);
$billingcodes = $bcode->getBillingCodes($company_id, false);

// get a list of permitted companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('None specified')), $companies);
$companies = arrayMerge(array('-1' => $AppUI->_('All Codes')), $companies);
$billingCategory = w2PgetSysVal('BudgetCategory');
$billingCategory = arrayMerge(array('0' => $AppUI->_('None specified')), $billingCategory);

$titleBlock = new w2p_Theme_TitleBlock('Edit Billing Codes', 'myevo-weather.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();
?>
<script language="javascript" type="text/javascript">
<!--
function submitIt(){
	var form = document.frmAddcode;
	form.submit();
}

function changeIt() {
	var f=document.changeMe;
    document.getElementById('company_filter').value = document.getElementById('company_id').value;
	var msg = '';
	f.submit();
}


function delIt2(id) {
	document.frmDel.billingcode_id.value = id;
	document.frmDel.submit();
}
-->
</script>

<form name="frmDel" action="./index.php?m=system" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_billingcode_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
	<input type="hidden" name="billingcode_id" value="" />
</form>
<form name="changeMe" action="./index.php?m=system&amp;a=billingcode" method="post" accept-charset="utf-8">
    <input type="hidden" name="company_id" id="company_filter" value="" />
</form>

<form name="frmAddcode" action="./index.php?m=system" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_billingcode_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="billingcode_status" value="0" />
    <table width="100%" border="0" cellpadding="1" cellspacing="1" class="std list">
        <tr>
            <td>
                <?php echo arraySelect($companies, 'billingcode_company', 'size="1" class="text" onchange="changeIt();"', $company_id, false); ?>
            </td>
        </tr>
        <tr>
            <th width="40">&nbsp;
            </th>
            <th><?php echo $AppUI->_('Company'); ?></th>
            <th><?php echo $AppUI->_('Billing Code'); ?></th>
            <th><?php echo $AppUI->_('Value'); ?></th>
            <th><?php echo $AppUI->_('Description'); ?></th>
            <th><?php echo $AppUI->_('Billing Category'); ?></th>
        </tr>

        <?php
        foreach ($billingcodes as $code) {
            ?><tr>
                <td>
                    <a href="?m=system&a=billingcode&billingcode_id=<?php echo $code['billingcode_id']; ?>" title="<?php echo $AppUI->_('edit'); ?>">
                        <img src="<?php echo w2PfindImage('icons/stock_edit-16.png'); ?>" border="0" alt="<?php echo $AppUI->_('edit'); ?>" />
                    </a>
                    <?php if (!$code['billingcode_status']) { ?>
                        <a href="javascript:delIt2(<?php echo $code['billingcode_id']; ?>);" title="<?php echo $AppUI->_('delete'); ?>">
                            <img src="<?php echo w2PfindImage('icons/stock_delete-16.png'); ?>" border="0" alt="<?php echo $AppUI->_('delete'); ?>" />
                        </a>
                    <?php } ?>
                </td>
                <td align="left">&nbsp;<?php echo (('' != $code['company_name']) ? $code['company_name'] : 'None specified'); ?></td>
                <td align="left">&nbsp;<?php echo $code['billingcode_name'] . ($code['billingcode_status'] == 1 ? ' (deleted)' : ''); ?></td>
                <td nowrap="nowrap" align="center"><?php echo $code['billingcode_value']; ?></td>
                <td nowrap="nowrap"><?php echo $code['billingcode_desc']; ?></td>
                <td nowrap="nowrap"><?php echo $billingCategory[$code['billingcode_category']]; ?></td>
            </tr><?php
        } ?>
        <tr>
            <td>&nbsp;<input type="hidden" name="billingcode_id" value="<?php echo $billingcode_id; ?>" /></td>
            <td>
                <?php
                    unset($companies[-1]);
                    echo arraySelect($companies, 'billingcode_company', 'size="1" class="text"', $bcode->billingcode_company, false);
                ?>
            </td>
            <td><input type="text" class="text" name="billingcode_name" value="<?php echo $bcode->billingcode_name; ?>" /></td>
            <td align="center"><input type="text" class="text" name="billingcode_value" value="<?php echo $bcode->billingcode_value; ?>" size="7" /></td>
            <td><input type="text" class="text" name="billingcode_desc" value="<?php echo $bcode->billingcode_desc; ?>" /></td>
            <td>
                <?php
                    echo arraySelect($billingCategory, 'billingcode_category', 'size="1" class="text"', $bcode->billingcode_category, false);
                ?>
            </td>
        </tr>
        <tr>
            <td align="left">
                <input class="button"  type="button" value="<?php echo $AppUI->_('back'); ?>" onclick="javascript:history.back(-1);" />
            </td>
            <td colspan="3" align="right">
                <input class="button" type="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
            </td>
        </tr>
    </table>
</form>