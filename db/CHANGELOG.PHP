<?
die('NO ACCESS');
?>


ALTER TABLE  `Feedback` CHANGE  `objectType`  `objectType` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;

UPDATE `Feedback` SET `objectType` = "Stack" WHERE `objectType` = 1;
UPDATE `Feedback` SET `objectType` = "Photo" WHERE `objectType` = 2;
UPDATE `Feedback` SET `objectType` = "Song" WHERE `objectType` = 3;
UPDATE `Feedback` SET `objectType` = "Article" WHERE `objectType` = 4;
UPDATE `Feedback` SET `objectType` = "User" WHERE `objectType` = 5;

ALTER TABLE `Feedback` CHANGE `objectType` `objectType` ENUM ('Article', 'Contact', 'Feedback', 'File', 'Follower', 'Message', 'Order', 'Photo', 'Product', 'Question', 'Sale', 'Site', 'Song', 'Stack', 'Store', 'Theme', 'Transaction','User' ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;





ALTER TABLE  `Feedback` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;

UPDATE `Feedback` SET `type` = "comment" WHERE `type` = 1;
UPDATE `Feedback` SET `type` = "heart" WHERE `type` = 2;
UPDATE `Feedback` SET `type` = "rating" WHERE `type` = 3;
UPDATE `Feedback` SET `type` = "note" WHERE `type` = 4;
UPDATE `Feedback` SET `type` = "promote" WHERE `type` = 5;
UPDATE `Feedback` SET `type` = "share" WHERE `type` = 6;
UPDATE `Feedback` SET `type` = "validate" WHERE `type` = 7;


ALTER TABLE `Feedback` CHANGE `type` `type` ENUM('comment','heart','rating','note','promote','share','validate') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;



ALTER TABLE  `User` DROP  `invid` ,
DROP  `type` ,
DROP  `access` ,
DROP  `apiKey` ,
DROP  `interests` ,
DROP  `stats`;

ALTER TABLE  `User` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `User` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;

ALTER TABLE  `Photo` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Photo` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;

ALTER TABLE  `Song` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Song` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Article` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Article` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Stack` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Stack` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Message` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Message` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Order` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Order` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Feedback` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Feedback` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Contact` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Contact` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Question` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Question` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Product` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Product` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Sale` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Sale` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Subscriber` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Subscriber` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Follower` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Follower` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Site` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Site` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;
ALTER TABLE  `Transaction` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;

ALTER TABLE  `Transaction` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;

ALTER TABLE  `File` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;
ALTER TABLE  `File` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;

ALTER TABLE  `Promotion` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;
ALTER TABLE  `Promotion` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;

ALTER TABLE  `Languages` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;
ALTER TABLE  `Languages` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;

DROP TABLE  `ShopitemPriceGenre`;
DROP TABLE  `ShopitemFormat`;
DROP TABLE  `Shopitem`;
DROP TABLE  `Invite`;
DROP TABLE  `Project`;
DROP TABLE  `Vars`;
DROP TABLE  `Url`;


ALTER TABLE  `shorturl` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL;
RENAME TABLE  `shorturl` TO  `Shortlink` ;
ALTER TABLE  `Shortlink` ADD  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE  `Shortlink` ADD  `modified` INT(11) NOT NULL;
ALTER TABLE  `Shortlink` ADD  `deleted` INT(11) NOT NULL;


RENAME TABLE  `Languages` TO  `Translation` ;



CREATE TABLE IF NOT EXISTS `Tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `objectType` enum('Article','Contact','Feedback','File','Follower','Message','Order','Photo','Product','Question','Sale','Site','Song','Stack','Store','Theme','Transaction','User') NOT NULL,
  `objectId` int(11) NOT NULL,
  `tag` varchar(255) NOT NULL,
  `action` enum('created','liked','commented','followed') NOT NULL,
  `created` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `deleted` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `Feed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `objectType` enum('Article','Contact','Feedback','File','Follower','Message','Order','Photo','Product','Question','Sale','Site','Song','Stack','Store','Theme','Transaction','User') NOT NULL,
  `objectId` int(11) NOT NULL,
  `seen` int(2) NOT NULL,
  `clicked` int(2) NOT NULL,
  `publish` date NOT NULL,
  `importance` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `deleted` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `Credit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `objectType` enum('Article','Contact','Feedback','File','Follower','Message','Order','Photo','Product','Question','Sale','Site','Song','Stack','Store','Theme','Transaction','User') NOT NULL,
  `objectId` int(11) NOT NULL,
  `who` int(11) NOT NULL,
  `name` VARCHAR( 255 ) NOT NULL,
  `role` enum('photographer','model','make-up artist','stylist','set designer','retoucher','publisher','clothe designer','creative director','agent','gallery','other') NOT NULL,
  `approved` int(1) NOT NULL,
  `created` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `deleted` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE  `Site` DROP  `represented`;

ALTER TABLE  `Site` ADD  `client` VARCHAR(255) NULL DEFAULT NULL AFTER `adminNIC`;



ALTER TABLE  `Transaction` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;
ALTER TABLE  `Transaction` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;


ALTER TABLE  `File` CHANGE  `creationDate`  `created` INT( 11 ) NOT NULL ,
CHANGE  `modifyDate`  `modified` INT( 11 ) NOT NULL;
ALTER TABLE  `File` ADD  `deleted` INT NOT NULL DEFAULT  '0' AFTER  `modified`;


ALTER TABLE  `Site` ADD  `published` BOOLEAN NULL DEFAULT FALSE AFTER  `channel` ;


ALTER TABLE  `User` CHANGE  `gender`  `gender` VARCHAR( 1 ) NOT NULL ;





-- 2014-01-20: Add 2 deleted fields for both members of the conversation


ALTER TABLE  `Message` ADD  `archived_sender` BOOLEAN NOT NULL DEFAULT FALSE AFTER  `openDate`, ADD  `archived_receiver` BOOLEAN NOT NULL DEFAULT FALSE AFTER  `archived_sender`;



-- 2014-06-13: Added filename and filepath to Photo


ALTER TABLE  `Photo` ADD  `filename` VARCHAR (255) NOT NULL AFTER  `dateShot`,  ADD  `filepath` VARCHAR (255) NOT NULL AFTER  `filename`;
