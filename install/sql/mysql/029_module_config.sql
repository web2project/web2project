
CREATE TABLE IF NOT EXISTS `module_config` (
    `module_config_id` int(10) NOT NULL AUTO_INCREMENT,
    `module_name` varchar(50) NOT NULL,
    `module_config_name` varchar(50) NOT NULL,
    `module_config_value` varchar(50) NOT NULL,
    `module_config_text` varchar(50) NOT NULL,
    `module_config_order` int(10) NOT NULL,
    PRIMARY KEY (`module_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;