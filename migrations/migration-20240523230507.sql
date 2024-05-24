CREATE TABLE IF NOT EXISTS `tenders_of_users` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `publication_number` VARCHAR(16) NOT NULL ,
    `user_id` INT NOT NULL ,
    PRIMARY KEY (`id`),
    UNIQUE `tender_to_user` (`publication_number`, `user_id`),
    FOREIGN KEY (`publication_number`) REFERENCES `tenders`(`publication_number`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;
