-- Add a new field to the forum_watch table

ALTER TABLE `forum_watch` ADD (`notify_by_email` BOOLEAN DEFAULT false);
