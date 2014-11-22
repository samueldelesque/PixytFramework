<?
die('NO ACCESS');
?>

RENAME TABLE  `stats`.`_tmp` TO  `stats`.`Stat` ;
ALTER TABLE `Stat` ADD `uid` INT NOT NULL AFTER `id`;

ALTER TABLE  `Stat` ADD  `created` INT NOT NULL ,
ADD  `modified` INT NOT NULL ,
ADD  `deleted` INT NOT NULL;

ALTER TABLE  `Stat` ADD  `device` VARCHAR( 255 ) NOT NULL AFTER  `language` ;

ALTER TABLE  `Stat` CHANGE  `time`  `hour` INT( 2 ) NOT NULL;
ALTER TABLE  `Stat` ADD  `day` DATE NOT NULL;