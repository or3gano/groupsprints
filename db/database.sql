SET NAMES utf8;

DROP TABLE IF EXISTS `sprint_group`;
CREATE TABLE `sprint_group` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `sprint_id` int(10) unsigned NOT NULL,
    `group_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sprint_id` (`sprint_id`),
    KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
