ALTER TABLE  `#__mpoll_questions` ADD  `q_default` VARCHAR( 255 ) NOT NULL AFTER `q_hint`;
ALTER TABLE  `#__mpoll_questions` CHANGE  `q_type`  `q_type` ENUM(  'textar',  'textbox',  'multi', 'cbox',  'mcbox',  'email',  'attach', 'message' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;