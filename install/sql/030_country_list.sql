
-- Implemented a simple way to specify preferred countries for the dropdown box
--   as per http://bugs.web2project.net/view.php?id=630
--   I (caseydk) set the default using countries of core web2project team
--   members and major contributors.

INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`)
	VALUES (1, 'GlobalCountriesPreferred', 'Australia', 'AU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`)
	VALUES (1, 'GlobalCountriesPreferred', 'Canada', 'CA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`)
	VALUES (1, 'GlobalCountriesPreferred', 'Germany', 'DE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`)
	VALUES (1, 'GlobalCountriesPreferred', 'France', 'FR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`)
	VALUES (1, 'GlobalCountriesPreferred', 'Portugal', 'PT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`)
	VALUES (1, 'GlobalCountriesPreferred', 'United Kingdom', 'GB');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`)
	VALUES (1, 'GlobalCountriesPreferred', 'United States', 'US');

-- Updating the country list as per http://bugs.web2project.net/view.php?id=624 and used
--   http://www.iso.org/iso/english_country_names_and_code_elements for clarifications.

INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Andorra', 'AD') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Andorra', `sysval_value_id` = 'AD';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Aland Islands', 'AX') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Aland Islands', `sysval_value_id` = 'AX';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Saint Barthélemy', 'BL') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Saint Barthélemy', `sysval_value_id` = 'BL';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Western Sahara', 'EH') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Western Sahara', `sysval_value_id` = 'EH';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Falkland Islands (Malvinas)', 'FK') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Falkland Islands (Malvinas)', `sysval_value_id` = 'FK';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Micronesia, Federated States Of', 'FM') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Micronesia, Federated States Of', `sysval_value_id` = 'FM';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'United Kingdom', 'GB') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'United Kingdom', `sysval_value_id` = 'GB';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Guernsey', 'GG') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Guernsey', `sysval_value_id` = 'GG';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Isle Of Man', 'IM') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Isle Of Man', `sysval_value_id` = 'IM';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Jersey', 'JE') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Jersey', `sysval_value_id` = 'JE';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', "Korea, Democratic People's Republic Of", 'KP') ON DUPLICATE KEY UPDATE
	`sysval_value` = "Korea, Democratic People's Republic Of", `sysval_value_id` = 'KP';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Korea, Republic Of', 'KR') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Korea, Republic Of', `sysval_value_id` = 'KR';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', "Lao People's Democratic Republic", 'LA') ON DUPLICATE KEY UPDATE
	`sysval_value` = "Lao People's Democratic Republic", `sysval_value_id` = 'LA';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Libyan Arab Jamahiriya', 'LY') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Libyan Arab Jamahiriya', `sysval_value_id` = 'LY';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Moldova, Republic Of', 'MD') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Moldova, Republic Of', `sysval_value_id` = 'MD';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Montenegro', 'ME') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Montenegro', `sysval_value_id` = 'ME';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Saint Martin (French part)', 'MF') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Saint Martin (French part)', `sysval_value_id` = 'MF';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Macedonia, The Former Yugoslav Republic Of', 'MK') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Macedonia, The Former Yugoslav Republic Of', `sysval_value_id` = 'MK';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Macao', 'MO') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Macao', `sysval_value_id` = 'MO';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'French Polynesia', 'PF') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'French Polynesia', `sysval_value_id` = 'PF';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Pitcairn', 'PN') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Pitcairn', `sysval_value_id` = 'PN';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Palestinian Territory, Occupied', 'PS') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Palestinian Territory, Occupied', `sysval_value_id` = 'PS';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Reunion', 'RE') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Reunion', `sysval_value_id` = 'RE';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Serbia', 'RS') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Serbia', `sysval_value_id` = 'RS';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Svalbard And Jan Mayen', 'SJ') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Svalbard And Jan Mayen', `sysval_value_id` = 'SJ';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Slovakia', 'SK') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Slovakia', `sysval_value_id` = 'SK';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Sao Tome And Principe', 'ST') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Sao Tome And Principe', `sysval_value_id` = 'ST';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Syrian Arab Republic', 'SY') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Syrian Arab Republic', `sysval_value_id` = 'SY';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Tajikistan', 'TJ') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Tajikistan', `sysval_value_id` = 'TJ';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Timor-Leste', 'TL') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Timor-Leste', `sysval_value_id` = 'TL';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Taiwan, Province Of China', 'TW') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Taiwan, Province Of China', `sysval_value_id` = 'TW';
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'GlobalCountries', 'Tanzania, United Republic Of', 'TZ') ON DUPLICATE KEY UPDATE
	`sysval_value` = 'Tanzania, United Republic Of', `sysval_value_id` = 'TZ';