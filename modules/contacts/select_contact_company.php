<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

$table_name = w2PgetParam($_GET, 'table_name', 'companies');
$company_id = (int) w2PgetParam($_GET, 'company_id', 0);
$dept_id = (int) w2PgetParam($_GET, 'dept_id', 0);
$select_list = array();

switch ($table_name) {
	case 'companies':
//TODO: deprecated
		$id_field = 'company_id';
		$name_field = 'company_name';
		$selection_string = 'Company';
		$dataId = $company_id;

		$company = new CCompany;
		$companyList = $company->getCompanyList();

		foreach($companyList as $comp) {
			$select_list[$comp['company_id']] = $comp['company_name'];
		}
		break;
	case 'departments':
		$id_field = 'dept_id';
		$name_field = 'dept_name';
		$selection_string = 'Department';
		$dataId = $dept_id;

		$deptList = CDepartment::getDepartmentList(null, $company_id, null);
		foreach($deptList as $dept) {
			$select_list[$dept['dept_id']] = $dept['dept_name'];
		}
		break;
}
$select_list = array('0' => '') + $select_list;

$myId = (int) w2PgetParam($_POST, $id_field, 0);

if ($myId) {
	$q = new w2p_Database_Query;
	$q->addTable($table_name);
	$q->addQuery('*');
	$q->addWhere($id_field . '=' . $myId);
	$r_data = $q->loadHash();
	$q->clear();
	$data_update_script = '';
	$update_address = (isset($_POST['overwrite_address'])) ? true : false;

	if ($table_name == 'companies') {
		$update_fields = array();
		if ($update_address) {
			$update_fields = array('company_address1' => 'contact_address1', 'company_address2' => 'contact_address2', 'company_city' => 'contact_city', 'company_state' => 'contact_state', 'company_zip' => 'contact_zip', 'company_phone1' => 'contact_phone', 'company_phone2' => 'contact_phone2', 'company_fax' => 'contact_fax');
		}
        if ($myId > 0) {
            $data_update_script = "opener.setCompany($myId , '" . $AppUI->__($r_data[$name_field], UI_OUTPUT_JS) ."');";
        } else {
            $data_update_script = "opener.setCompany($myId, '');";
        }
	} else {
		if ($table_name == 'departments') {
			$data_update_script = "opener.setDepartment($myId);";
		}
	}
	?>
		<script language="javascript" type="text/javascript">
			<?php echo $data_update_script; ?>
			self.close();
		</script>
	<?php
} else {
	?>
        <style>
            div {
                display: none;
            }
        </style>
		<form name="frmSelector" action="./index.php?m=contacts&a=select_contact_company&dialog=1&table_name=<?php echo $table_name . '&' . $additional_get_information; ?>" method="post" accept-charset="utf-8">
			<?php
            echo $AppUI->getTheme()->styleRenderBoxTop();
			?>
			<table class="std">
			<tr>
				<td colspan="2">
					<?php
						echo $AppUI->_('Select') . ' ' . $AppUI->_($selection_string) . ': ';
						echo arraySelect($select_list, $id_field, 'class="text"', $dataId);
					?>
				</td>
			</tr>
			<tr>
				<td>
					<input type="button" class="button" value="<?php echo $AppUI->_('cancel'); ?>" onclick="window.close()" />
				</td>
				<td align="right">
					<input type="submit" class="button" value="<?php echo $AppUI->_('Select', UI_CASE_LOWER); ?>" />
				</td>
			</tr>
			</table>
		</form>
	<?php
}