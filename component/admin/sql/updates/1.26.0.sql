CREATE TABLE IF NOT EXISTS `#__mpoll_payment` (
  `pay_id` int(11) NOT NULL,
  `pay_cm` int(11) NOT NULL,
  `pay_poll` int(11) NOT NULL,
  `pay_type` varchar(255) DEFAULT NULL,
  `pay_status` varchar(255) DEFAULT NULL,
  `pay_amount` double DEFAULT NULL,
  `pay_payid` varchar(255) DEFAULT NULL,
  `pay_trans` varchar(255) DEFAULT NULL,
  `pay_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pay_updated` datetime NOT NULL,
  `pay_invoice` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__mpoll_payment_log` (
  `log_id` int(11) NOT NULL,
  `log_payment` int(11) NOT NULL,
  `log_headers` longtext,
  `log_data` longtext,
  `log_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_verified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__mpoll_payment` ADD PRIMARY KEY (`pay_id`);
ALTER TABLE `#__mpoll_payment_log` ADD PRIMARY KEY (`log_id`);
ALTER TABLE `#__mpoll_payment` MODIFY `pay_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__mpoll_payment_log` MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__mpoll_polls` ADD `poll_payment_enabled` BOOLEAN NOT NULL DEFAULT FALSE AFTER `poll_conffromname`;
ALTER TABLE `#__mpoll_polls` ADD `poll_payment_amount` DOUBLE NOT NULL AFTER `poll_payment_enabled`;
ALTER TABLE `#__mpoll_polls` ADD `poll_payment_fromname` VARCHAR(255) NOT NULL AFTER `poll_payment_amount`;
ALTER TABLE `#__mpoll_polls` ADD `poll_payment_fromemail` VARCHAR(255) NOT NULL AFTER `poll_payment_fromname`;
ALTER TABLE `#__mpoll_polls` ADD `poll_payment_subject` VARCHAR(255) NOT NULL AFTER `poll_payment_fromemail`;
ALTER TABLE `#__mpoll_polls` ADD `poll_payment_body` TEXT NOT NULL AFTER `poll_payment_subject`;
ALTER TABLE `#__mpoll_polls` ADD `poll_results_emails` TEXT NOT NULL AFTER `poll_resultsemail`;

ALTER TABLE `#__mpoll_polls` ADD `poll_payment_title` VARCHAR(255) NOT NULL AFTER `poll_payment_amount`;
ALTER TABLE `#__mpoll_polls` ADD `poll_payment_instructions` TEXT NOT NULL AFTER `poll_payment_title`;

ALTER TABLE `#__mpoll_completed` ADD `cm_pubid` VARCHAR(64) NOT NULL AFTER `cm_id`;
ALTER TABLE `#__mpoll_completed` ADD `cm_status` VARCHAR(64) NOT NULL DEFAULT 'complete' AFTER `cm_ipaddr`;

DELETE FROM `#__mpoll_questions` WHERE `q_type` = "captcha";

ALTER TABLE `#__mpoll_polls` ADD `poll_payment_to` int(11) NOT NULL AFTER  `poll_payment_amount`;

ALTER TABLE `#__mpoll_polls` ADD `poll_confemail_to` int(11) NOT NULL AFTER  `poll_confemail`;

ALTER TABLE `#__mpoll_polls` ADD `poll_payment_adminemail` VARCHAR(255) NOT NULL AFTER `poll_payment_body`;
ALTER TABLE `#__mpoll_polls` ADD `poll_payment_adminsubject` VARCHAR(255) NOT NULL AFTER `poll_payment_adminemail`;
