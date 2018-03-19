CREATE TABLE `jimmy`.`activity_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `parent_id` INT NULL,
  `message` VARCHAR(100) NULL,
  `related_link` VARCHAR(45) NULL,
  `created` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `jimmy`.`activity_log` 
ADD COLUMN `related_item` VARCHAR(100) NULL AFTER `message`;

