CREATE TABLE `log_active_record` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`model_class` VARCHAR(255) NOT NULL,
	`model_id` VARCHAR(50) NOT NULL,
	`log` TEXT NOT NULL,
	`created_by` VARCHAR(100) NOT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `model_class_model_id` (`model_class`, `model_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
