#
# Creating table `users` and adding admin
#

CREATE TABLE `diploma_project_db`.`users` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `login` VARCHAR(32) NOT NULL ,
    `password` VARCHAR(64) NOT NULL ,
    `token` VARCHAR(80) NULL ,
    `status` INT NOT NULL DEFAULT '0' ,
    PRIMARY KEY (`id`), UNIQUE (`login`)
) ENGINE = InnoDB;

# password: hash('sha256', 'lsduDfR5gviY4ad27u6sfh' . 321)
INSERT INTO `diploma_project_db`.`users`(`login`, `password`, `token`, `status`)
VALUES ('admin','46053221b09c8471eec148aac752291974fee138fe5bd192c083dbae4f0619c7','',5);
