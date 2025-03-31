ALTER TABLE `#__mpoll_polls` ADD `poll_redirect` tinyint(1) NOT NULL DEFAULT '0' AFTER `poll_end_msg`;
ALTER TABLE `#__mpoll_polls` ADD `poll_redirect_url`varchar(255) NOT NULL AFTER `poll_redirect`;