
-- This is just a tweak to get rid of the deprecation notice in the Hooks subststem

UPDATE modules SET mod_main_class = 'CProjectDesigner' WHERE mod_main_class = 'CProjectDesignerOptions';