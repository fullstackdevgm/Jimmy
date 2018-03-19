ALTER TABLE `jimmy`.`user_cancel_log` CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT ;

ALTER TABLE `jimmy`.`reports` ADD COLUMN `parent` INT NULL COMMENT '' AFTER `user_id`;

UPDATE reports AS r JOIN client AS c ON c.client_id = r.user_id SET r.parent = c.parent;
