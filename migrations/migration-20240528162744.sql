ALTER TABLE `users`
ADD `number_of_failed_login` INT NOT NULL DEFAULT '0' AFTER `status`,
ADD `datetime_of_unblocking` DATETIME NULL DEFAULT NULL AFTER `number_of_failed_login`;
