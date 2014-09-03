
-- Adding the first email templates to the system

INSERT INTO `email_templates` (`email_template_identifier`, `email_template_name`, `email_template_language`, `email_template_subject`, `email_template_body`) VALUES
('new-account-created', 'New Account Created', 'en_US', 'New Account Created', 'Dear {{contact_name}},\n\n\nCongratulations! Your account has been activated by the administrator.\n\nPlease use the login information provided earlier.\nYou may login at the following URL: {{base_url}}\n\n\nIf you have any difficulties or questions, please ask the administrator for help.\n\nOur Warmest Regards,\nThe Support Staff.\n\n****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****'),
('new-account-requested', 'HR Email', 'en_US', 'New Account Review', 'A new user has signed up on {{company_name}}. Please go through the user details below:\r\n\r\nName: {{contact_name}}\r\nUsername: {{user_name}}\r\nEmail: {{email_address}}\r\n\r\nYou may check this account at the following URL: {{base_url}}/index.php?m=users&a=view&user_id={{user_id}}\r\n\r\nThank you very much.\r\n\r\nThe {{company_name}} Taskforce\r\n\r\n****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****');
