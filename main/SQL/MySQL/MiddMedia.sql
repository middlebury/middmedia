
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

CREATE TABLE IF NOT EXISTS `middmedia_queue` (
  `directory` varchar(50) collate utf8_bin NOT NULL,
  `file` varchar(75) collate utf8_bin NOT NULL,
  `upload_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `processing` tinyint(1) NOT NULL default '0',
  `processing_start` timestamp NULL default NULL,
  `quality` varchar(20) collate utf8_bin default NULL,
  PRIMARY KEY  (`directory`,`file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='A queue for file uploads that need conversion.';
