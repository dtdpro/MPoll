ALTER TABLE `#__mpoll_results` ADD `res_ans_other_alt` text NOT NULL AFTER `res_ans_other`;
ALTER TABLE `#__mpoll_polls` ADD `poll_results_searchable` tinyint(1) NOT NULL DEFAULT '0' AFTER `poll_showresults`;
ALTER TABLE `#__questions` ADD `q_filter` tinyint(1) NOT NULL DEFAULT '0' AFTER `q_match`;
ALTER TABLE `#__questions` ADD `q_filter_name` VARCHAR(255) NULL AFTER `q_filter`, ADD `q_filter_width` VARCHAR(4) NOT NULL DEFAULT '1-1' AFTER `q_filter_name`;
ALTER TABLE `#__mpoll_polls` ADD `poll_results_showall` BOOLEAN NOT NULL DEFAULT FALSE AFTER `poll_results_searchable`;
ALTER TABLE `#__mpoll_polls` ADD `poll_results_msg_noresults` TEXT NOT NULL AFTER `poll_results_showall`, ADD `poll_results_msg_filterfirst` TEXT NOT NULL AFTER `poll_results_msg_noresults`;
ALTER TABLE `#__mpoll_questions` ADD `q_hidden` BOOLEAN NOT NULL DEFAULT FALSE AFTER `q_req`;
ALTER TABLE `#__mpoll_polls` ADD `poll_results_sortby` INT NOT NULL DEFAULT '0' AFTER `poll_results_showall`, ADD `poll_results_sortdirr` VARCHAR(5) NOT NULL DEFAULT 'ASC' AFTER `poll_results_sortby`;
ALTER TABLE `#__mpoll_polls` ADD `poll_results_sortby2` INT NOT NULL DEFAULT '0' AFTER `poll_results_sortdirr`, ADD `poll_results_sortdirr2` VARCHAR(5) NOT NULL DEFAULT 'ASC' AFTER `poll_results_sortby2`;
ALTER TABLE `#__mpoll_completed` ADD `featured` tinyint NOT NULL DEFAULT '0' AFTER `published`;
ALTER TABLE `#__mpoll_polls` ADD `poll_results_showfeat` BOOLEAN NOT NULL DEFAULT FALSE AFTER `poll_results_showall`;
ALTER TABLE `#__mpoll_polls` ADD `poll_payment_trigger` INT NOT NULL DEFAULT '0' AFTER `poll_payment_enabled`;