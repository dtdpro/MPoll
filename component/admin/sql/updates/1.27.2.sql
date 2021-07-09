ALTER TABLE  `#__mpoll_polls` ADD  `poll_shownotstarted` BOOLEAN NOT NULL DEFAULT FALSE AFTER  `poll_start`;
ALTER TABLE `#__mpoll_polls` ADD `poll_notstart_msg` VARCHAR(255) NOT NULL AFTER `poll_shownotstarted`;
