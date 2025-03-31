ALTER TABLE `#__mpoll_polls` CHANGE `poll_payment_trigger` `poll_payment_trigger` VARCHAR(255) NOT NULL DEFAULT '0', CHANGE `poll_payment_subplan_trigger` `poll_payment_subplan_trigger` VARCHAR(255) NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `#__mpoll_email_templates` (
  `tmpl_id` int NOT NULL,
  `tmpl_poll` int NOT NULL,
  `tmpl_name` varchar(255) NOT NULL,
    `tmpl_email_to` INT NOT NULL,
    `tmpl_from_name` varchar(150) DEFAULT NULL,
    `tmpl_from_email` varchar(150) DEFAULT NULL,
    `tmpl_subject` varchar(255) DEFAULT NULL,
    `tmpl_content` longtext,
    `published` int NOT NULL DEFAULT '1',
    `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` int NOT NULL,
    `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified_by` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `#__mpoll_email_templates` ADD PRIMARY KEY (`tmpl_id`);
ALTER TABLE `#__mpoll_email_templates` MODIFY `tmpl_id` int NOT NULL AUTO_INCREMENT;