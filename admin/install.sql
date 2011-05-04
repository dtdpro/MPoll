CREATE TABLE IF NOT EXISTS `#__mpoll_completed` (
  `cm_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cm_user` int(11) NOT NULL,
  `cm_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cm_poll` int(11) NOT NULL,
  PRIMARY KEY (`cm_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__mpoll_polls` (
  `poll_id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_name` varchar(255) NOT NULL,
  `poll_desc` text NOT NULL,
  `poll_start` date NOT NULL,
  `poll_end` date NOT NULL,
  `published` tinyint(1) NOT NULL,
  `poll_regonly` tinyint(1) NOT NULL DEFAULT '1',
  `poll_only` tinyint(4) NOT NULL DEFAULT '1',
  `poll_rmsg` text NOT NULL,
  `poll_showresults` tinyint(1) NOT NULL DEFAULT '1',
  `poll_charttype` enum('bar','pieg') NOT NULL DEFAULT 'bar',
  PRIMARY KEY (`poll_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__mpoll_questions` (
  `q_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `q_poll` int(11) NOT NULL,
  `ordering` smallint(6) NOT NULL,
  `q_text` text NOT NULL,
  `q_type` enum('textar','textbox','multi','cbox','mcbox') NOT NULL,
  `q_req` tinyint(1) NOT NULL DEFAULT '1',
  `q_charttype` enum('bar','barg','pieg') NOT NULL,
  PRIMARY KEY (`q_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__mpoll_questions_opts` (
  `opt_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `opt_qid` bigint(20) NOT NULL,
  `opt_txt` varchar(255) NOT NULL,
  `ordering` tinyint(4) NOT NULL,
  `opt_other` tinyint(1) NOT NULL DEFAULT '0',
  `opt_correct` tinyint(1) NOT NULL DEFAULT '0',
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
