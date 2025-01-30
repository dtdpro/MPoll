CREATE TABLE `#__mpoll_completed` (
  `cm_id` bigint(20) NOT NULL,
  `cm_pubid` varchar(64) NOT NULL,
  `cm_user` int(11) NOT NULL,
  `cm_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cm_poll` int(11) NOT NULL,
  `cm_type` VARCHAR(50) NOT NULL DEFAULT 'submission',
  `cm_useragent` text NOT NULL,
  `cm_ipaddr` varchar(50) NOT NULL,
  `cm_status` varchar(64) NOT NULL DEFAULT 'complete',
  `cm_start` DATE NOT NULL DEFAULT "2020-01-01",
  `cm_end` DATE NOT NULL DEFAULT "2090-01-01",
  `published` tinyint NOT NULL DEFAULT '0',
  `featured` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__mpoll_polls` (
  `poll_id` int(11) NOT NULL,
  `poll_name` varchar(255) NOT NULL,
  `poll_alias` varchar(255) NOT NULL,
  `poll_desc` text NOT NULL,
  `poll_start` datetime NOT NULL,
  `poll_shownotstarted` tinyint(1) NOT NULL DEFAULT '0',
  `poll_notstart_msg` varchar(255) NOT NULL,
  `poll_end` datetime NOT NULL,
  `poll_end_msg` varchar(255) NOT NULL,
  `poll_showended` tinyint(1) NOT NULL DEFAULT '0',
  `poll_recaptcha` tinyint(1) NOT NULL DEFAULT '0',
  `poll_printresults` tinyint(1) NOT NULL DEFAULT '0',
  `poll_pagetype` varchar(50) NOT NULL DEFAULT 'poll',
  `published` tinyint(1) NOT NULL,
  `poll_only` tinyint(4) NOT NULL DEFAULT '1',
  `poll_regreq` tinyint(4) NOT NULL DEFAULT '0',
  `poll_regreqmsg` text NOT NULL,
  `poll_accessreqmsg` text NOT NULL,
  `poll_results_msg_before` text NOT NULL,
  `poll_results_msg_after` text NOT NULL,
  `poll_results_msg_mod` text NOT NULL,
  `poll_showresults` tinyint(1) NOT NULL DEFAULT '1',
  `poll_results_searchable` tinyint(1) NOT NULL DEFAULT '0',
  `poll_results_showall` tinyint(1) NOT NULL DEFAULT '0',
  `poll_results_sortby` int(11) NOT NULL DEFAULT '0',
  `poll_results_sortdirr` VARCHAR(5) NOT NULL DEFAULT 'ASC',
  `poll_results_sortby2` int(11) NOT NULL DEFAULT '0',
  `poll_results_sortdirr2` VARCHAR(5) NOT NULL DEFAULT 'ASC',
  `poll_results_msg_noresults` TEXT NOT NULL,
  `poll_results_msg_filterfirst` TEXT NOT NULL,
  `access` int(11) NOT NULL DEFAULT '1',
  `poll_cat` int(11) NOT NULL,
  `poll_resultsemail` tinyint(1) NOT NULL DEFAULT '0',
  `poll_results_emails` text NOT NULL,
  `poll_emailto` varchar(255) NOT NULL,
  `poll_emailsubject` varchar(255) NOT NULL,
  `poll_emailreplyto` int(11) NOT NULL,
  `poll_confemail` tinyint(1) NOT NULL DEFAULT '0',
  `poll_confemail_to` int(11) NOT NULL,
  `poll_confmsg` text NOT NULL,
  `poll_confsubject` varchar(255) NOT NULL,
  `poll_conffromemail` varchar(255) NOT NULL,
  `poll_conffromname` varchar(255) NOT NULL,
  `poll_payment_enabled` int(11) NOT NULL DEFAULT '0',
  `poll_payment_trigger` INT NOT NULL DEFAULT '0',
  `poll_payment_subplan` varchar(255) NOT NULL DEFAULT '0',
  `poll_payment_subplan_trigger` INT NOT NULL DEFAULT '0',
  `poll_payment_amount` double NOT NULL,
  `poll_payment_to` int(11) NOT NULL,
  `poll_payment_title` varchar(255) NOT NULL,
  `poll_payment_instructions` text NOT NULL,
  `poll_payment_fromname` varchar(255) NOT NULL,
  `poll_payment_fromemail` varchar(255) NOT NULL,
  `poll_payment_subject` varchar(255) NOT NULL,
  `poll_payment_body` text NOT NULL,
  `poll_payment_adminemail` varchar(255) NOT NULL,
  `poll_payment_adminsubject` varchar(255) NOT NULL,
  `poll_created` datetime NOT NULL,
  `poll_created_by` int(11) NOT NULL,
  `poll_modified` datetime NOT NULL,
  `poll_modified_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__mpoll_questions` (
  `q_id` bigint(20) NOT NULL,
  `q_poll` int(11) NOT NULL,
  `ordering` smallint(6) NOT NULL,
  `q_name` varchar(255) NOT NULL,
  `q_text` text NOT NULL,
  `q_pretext` text NOT NULL,
  `q_hint` text NOT NULL,
  `q_default` varchar(255) NOT NULL,
  `q_type` varchar(20) NOT NULL,
  `q_req` tinyint(1) NOT NULL DEFAULT '1',
  `q_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `q_min` int(11) NOT NULL,
  `q_max` int(11) NOT NULL,
  `q_match` int(11) NOT NULL,
  `q_filter` tinyint(1) NOT NULL DEFAULT '0',
  `q_filter_name` VARCHAR(255) NULL,
  `q_filter_width` VARCHAR(4) NOT NULL DEFAULT '1-1',
  `params` text NOT NULL,
  `published` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__mpoll_questions_opts` (
  `opt_id` bigint(20) NOT NULL,
  `opt_qid` bigint(20) NOT NULL,
  `opt_txt` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,
  `opt_other` tinyint(1) NOT NULL DEFAULT '0',
  `opt_correct` tinyint(1) NOT NULL DEFAULT '0',
  `opt_selectable` tinyint(1) NOT NULL DEFAULT '1',
  `opt_disabled` tinyint(1) NOT NULL DEFAULT '0',
  `opt_color` varchar(10) NOT NULL DEFAULT '#000000',
  `opt_blank` tinyint(1) NOT NULL DEFAULT '0',
  `published` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__mpoll_results` (
  `res_id` bigint(20) NOT NULL,
  `res_user` int(11) NOT NULL,
  `res_poll` int(11) NOT NULL,
  `res_qid` bigint(20) NOT NULL,
  `res_ans` text NOT NULL,
  `res_cm` int(11) NOT NULL,
  `res_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `res_ans_other` text NOT NULL,
  `res_ans_other_alt` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__mpoll_payment` (
  `pay_id` int(11) NOT NULL,
  `pay_cm` int(11) NOT NULL,
  `pay_poll` int(11) NOT NULL,
  `pay_sale_type` varchar(50) NOT NULL DEFAULT 'order',
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

ALTER TABLE `#__mpoll_completed` ADD PRIMARY KEY (`cm_id`);
ALTER TABLE `#__mpoll_polls` ADD PRIMARY KEY (`poll_id`);
ALTER TABLE `#__mpoll_questions` ADD PRIMARY KEY (`q_id`);
ALTER TABLE `#__mpoll_questions_opts` ADD PRIMARY KEY (`opt_id`);
ALTER TABLE `#__mpoll_results` ADD PRIMARY KEY (`res_id`);
ALTER TABLE `#__mpoll_payment` ADD PRIMARY KEY (`pay_id`);
ALTER TABLE `#__mpoll_payment_log` ADD PRIMARY KEY (`log_id`);

ALTER TABLE `#__mpoll_completed` MODIFY `cm_id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__mpoll_polls` MODIFY `poll_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__mpoll_questions` MODIFY `q_id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__mpoll_questions_opts` MODIFY `opt_id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__mpoll_results`  MODIFY `res_id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__mpoll_payment` MODIFY `pay_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__mpoll_payment_log` MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;
