
UPDATE modules SET mod_main_class = 'CEvent' WHERE mod_main_class = 'CCalendar';

UPDATE modules SET mod_version = '3.0.0' WHERE mod_directory in ('companies',
        'projects', 'tasks', 'calendar', 'files', 'contacts', 'forums', 'admin',
        'system', 'departments', 'help', 'public', 'smartsearch',
        'projectdesigner', 'reports', 'links', 'resources', 'history');