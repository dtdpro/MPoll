ALTER TABLE  `#__mpoll_polls` CHANGE  `poll_charttype`  `poll_emailto` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE  `#__mpoll_polls` ADD  `poll_reqreq` BOOLEAN NOT NULL DEFAULT  '0' AFTER `poll_only` ,
ADD  `poll_regreqmsg` TEXT NOT NULL AFTER  `poll_reqreq`;

ALTER TABLE  `#__mpoll_polls` ADD  `poll_emailsubject` VARCHAR( 255 ) NOT NULL AFTER `poll_emailto`;

ALTER TABLE  `#__mpoll_questions` CHANGE  `q_type`  `q_type` ENUM(  'textar',  'textbox',  'multi', 'cbox',  'mcbox',  'email',  'attach' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;