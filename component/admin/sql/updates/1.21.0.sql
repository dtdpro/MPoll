ALTER TABLE `#__mpoll_questions` ADD `q_name` VARCHAR(255) NOT NULL AFTER `ordering`;
UPDATE `#__mpoll_questions` SET `q_name` = `q_text`;