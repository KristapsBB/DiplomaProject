ALTER TABLE `users`
CHANGE `status` `access_level`
INT(11) NOT NULL DEFAULT '0';
