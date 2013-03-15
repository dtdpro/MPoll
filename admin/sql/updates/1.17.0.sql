ALTER TABLE  `#__mpoll_questions` ADD  `q_min` INT NOT NULL AFTER  `q_req` ,
ADD  `q_max` INT NOT NULL AFTER  `q_min` ,
ADD  `q_match` INT NOT NULL AFTER  `q_max`;

ALTER TABLE  `#__mpoll_questions_opts` ADD  `opt_disabled` BOOLEAN NOT NULL DEFAULT FALSE AFTER  `opt_correct`;

ALTER TABLE  `#__mpoll_questions` CHANGE  `q_type`  `q_type` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE  `#__mpoll_questions` ADD  `q_pretext` TEXT NOT NULL AFTER  `q_text`;

ALTER TABLE  `#__mpoll_polls` ADD  `poll_pagetype` VARCHAR( 50 ) NOT NULL DEFAULT 'poll' AFTER  `poll_end`;

ALTER TABLE  `#__mpoll_polls` ADD  `poll_resultsemail` BOOLEAN NOT NULL DEFAULT FALSE AFTER  `poll_cat`