CREATE TABLE `braintree_payment` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT NULL COMMENT '',
  `customer_id` VARCHAR(45) NULL COMMENT '',
  `subscription_id` VARCHAR(45) NULL COMMENT '',
  `status` VARCHAR(45) NULL COMMENT '',
  `created` TIMESTAMP NOT NULL COMMENT '',
  PRIMARY KEY (`id`)  COMMENT '');

ALTER TABLE `jimmy`.`braintree_payment` 
ADD UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC)  COMMENT '';
