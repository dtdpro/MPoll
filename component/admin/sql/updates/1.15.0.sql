ALTER TABLE  `#__mpoll_polls` ADD  `poll_confemail` BOOLEAN NOT NULL DEFAULT  '0' AFTER `poll_emailsubject` ,
ADD  `poll_confmsg` TEXT NOT NULL AFTER  `poll_confemail` ,
ADD `poll_confsubject` VARCHAR( 255 ) NOT NULL AFTER `poll_confmsg`,
ADD `poll_conffromemail` VARCHAR( 255 ) NOT NULL AFTER `poll_confsubject`,
ADD `poll_conffromname` VARCHAR( 255 ) NOT NULL AFTER `poll_conffromemail`;