ALTER TABLE `users`
CHANGE `token` `auth_token`
VARCHAR(80) DEFAULT NULL;

ALTER TABLE `users`
ADD `email` VARCHAR(256) AFTER `login`,
ADD `rescue_token` VARCHAR(80) AFTER `access_level`,
ADD `status` INT NOT NULL DEFAULT '0' AFTER `rescue_token`,
ADD UNIQUE (`email`),
ADD UNIQUE (`rescue_token`);

UPDATE `users`
SET `status`= 8,
`email` = 'admin@diploma-project.localhost'
WHERE `id`=1;

ALTER TABLE `users`
CHANGE `email` `email`
VARCHAR(256) NOT NULL;

ALTER TABLE `users`
ADD UNIQUE (`auth_token`);
