
-- This is necessary to support MySQL's Strict mode.

ALTER TABLE `budgets_assigned` CHANGE `budget_task` `budget_task` INT(10) NOT NULL DEFAULT '0';