ALTER TABLE `client_accounts` 
ADD COLUMN `user_token_id` INT NULL COMMENT '' ;

ALTER TABLE `user_token` 
ADD COLUMN `name` TEXT NULL COMMENT '' AFTER `parent_id` ;

DELETE FROM `user_token`;

CREATE TABLE `migration` (
  `id` INT NOT NULL COMMENT '',
  `user_id` INT NULL COMMENT '',
  `created` TIMESTAMP NOT NULL COMMENT '',
  PRIMARY KEY (`id`)  COMMENT '');


ALTER TABLE `jimmy`.`client_accounts` 
CHANGE COLUMN `api_auth_info` `api_auth_info` TEXT NULL COMMENT '' ;
