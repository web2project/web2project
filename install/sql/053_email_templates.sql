
-- Adding support for storing email templates

CREATE TABLE `email_templates` (
  `email_template_id` int(10) NOT NULL AUTO_INCREMENT,
  `email_template_identifier` varchar(50) NOT NULL,
  `email_template_name` varchar(255) NOT NULL,
  `email_template_language` varchar(5) NOT NULL,
  `email_template_subject` varchar(255) NOT NULL,
  `email_template_body` text NOT NULL,
  PRIMARY KEY (`email_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;