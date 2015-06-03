DROP TABLE IF EXISTS `cleantalk_server`;
CREATE TABLE `cleantalk_server` (
  `server_id` int(11) NOT NULL default 1,
  `work_url` varchar(255),
  `server_url` varchar(255),
  `server_ttl` int(11),
  `server_changed` int(11),
  PRIMARY KEY (`server_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cleantalk_timelabels`;
CREATE TABLE `cleantalk_timelabels` (
  `ct_key` varchar(255) NOT NULL default 'mail_error',
  `ct_value` int(11),
  PRIMARY KEY (`ct_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
