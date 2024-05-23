#
# Creating table `tenders`
#

CREATE TABLE IF NOT EXISTS `tenders` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `publication_number` VARCHAR(16) NOT NULL ,
    `publication_date` DATE NOT NULL ,
    `notice_title` TEXT NOT NULL ,
    `buyer_name` TEXT NOT NULL ,
    `country` TEXT NOT NULL ,
    `contract_nature` VARCHAR(20) ,
    `deadline` DATE ,
    `link` VARCHAR(60) NOT NULL ,
    PRIMARY KEY (`id`),
    UNIQUE (`publication_number`)
) ENGINE = InnoDB;
