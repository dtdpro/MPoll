CREATE TABLE IF NOT EXISTS `#__mpoll_completed` (
  `cm_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cm_user` int(11) NOT NULL,
  `cm_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cm_poll` int(11) NOT NULL,
  `cm_useragent` text NOT NULL,
  `cm_ipaddr` VARCHAR( 50 ) NOT NULL,
  PRIMARY KEY (`cm_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__mpoll_polls` (
  `poll_id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_name` varchar(255) NOT NULL,
  `poll_alias` varchar(255) NOT NULL,
  `poll_desc` text NOT NULL,
  `poll_start` datetime NOT NULL,
  `poll_end` datetime NOT NULL,
  `poll_pagetype` varchar(50) NOT NULL DEFAULT 'poll',
  `published` tinyint(1) NOT NULL,
  `poll_only` tinyint(4) NOT NULL DEFAULT '1',
  `poll_regreq` tinyint(4) NOT NULL DEFAULT '0',
  `poll_regreqmsg` text NOT NULL,
  `poll_results_msg_before` text NOT NULL,
  `poll_results_msg_after` text NOT NULL,
  `poll_results_msg_mod` text NOT NULL,
  `poll_showresults` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(11) NOT NULL DEFAULT '1',
  `poll_cat` int(11) NOT NULL,
  `poll_resultsemail` BOOLEAN NOT NULL DEFAULT FALSE,
  `poll_emailto` varchar(255) NOT NULL,
  `poll_emailsubject` varchar(255) NOT NULL,
  `poll_confemail` tinyint(1) NOT NULL DEFAULT '0',
  `poll_confmsg` text NOT NULL,
  `poll_confsubject` varchar(255) NOT NULL,
  `poll_conffromemail` varchar(255) NOT NULL,
  `poll_conffromname` varchar(255) NOT NULL,
  `poll_created` datetime NOT NULL,
  `poll_created_by` int(11) NOT NULL,
  `poll_modified` datetime NOT NULL,
  `poll_modified_by` int(11) NOT NULL,
  PRIMARY KEY (`poll_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__mpoll_questions` (
  `q_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `q_poll` int(11) NOT NULL,
  `ordering` smallint(6) NOT NULL,
  `q_text` text NOT NULL,
  `q_pretext` TEXT NOT NULL,
  `q_hint` text NOT NULL,
  `q_default` varchar(255) NOT NULL,
  `q_type` varchar(20) NOT NULL,
  `q_req` tinyint(1) NOT NULL DEFAULT '1',
  `q_min` INT NOT NULL,
  `q_max` INT NOT NULL,
  `q_match` INT NOT NULL,
  `published` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`q_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__mpoll_questions_opts` (
  `opt_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `opt_qid` bigint(20) NOT NULL,
  `opt_txt` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,
  `opt_other` tinyint(1) NOT NULL DEFAULT '0',
  `opt_correct` tinyint(1) NOT NULL DEFAULT '0',
  `opt_selectable` tinyint(1) NOT NULL DEFAULT '1',
  `opt_disabled` tinyint(1) NOT NULL DEFAULT '0',
  `opt_color` varchar(10) NOT NULL DEFAULT '#000000',
  `published` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`opt_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__mpoll_results` (
  `res_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `res_user` int(11) NOT NULL,
  `res_poll` int(11) NOT NULL,
  `res_qid` bigint(20) NOT NULL,
  `res_ans` text NOT NULL,
  `res_cm` int(11) NOT NULL,
  `res_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `res_ans_other` text NOT NULL,
  PRIMARY KEY (`res_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
