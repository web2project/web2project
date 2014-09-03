
-- Adding more email templates to the system

INSERT INTO `email_templates` (`email_template_identifier`, `email_template_name`, `email_template_language`, `email_template_subject`, `email_template_body`) VALUES
('password-reset', 'Password Reset', 'en_US', 'New password for - {{username}}', 'The user account {{username}} has this email associated with it.\nA web user from {{baseurl}} has just requested that a new password be sent.\n\nYour New Password is: {{newpass}} If you didn''t ask for this, don''t worry. You are seeing this message, not them. If this was an error just login with your new password and then change your password to what you would like it to be.');
