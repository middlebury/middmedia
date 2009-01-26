
CREATE TABLE IF NOT EXISTS `middmedia_metadata` (
  `directory` varchar(50) character set utf8 collate utf8_bin NOT NULL,
  `file` varchar(75) character set utf8 collate utf8_bin NOT NULL,
  `creator` varchar(50) character set utf8 collate utf8_bin NOT NULL,
  PRIMARY KEY  (`directory`,`file`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `middmedia_quotas` (
  `directory` varchar(50) character set utf8 collate utf8_bin NOT NULL,
  `quota` bigint(16) NOT NULL,
  PRIMARY KEY  (`directory`)
) ENGINE=InnoDB;