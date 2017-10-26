CREATE TABLE `user` (
  `id` bigint(21) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(256) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='用户表';


CREATE TABLE `role` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
	`name` varchar(32) NOT NULL DEFAULT '' COMMENT '角色名称',
	`uid` bigint(21) unsigned NOT NULL DEFAULT 0 COMMENT '用户id',
	`ctime` int(11) unsigned NOT NULL  DEFAULT 0 COMMENT '创建时间', 
	`utime` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
	PRIMARY KEY (`id`),
	KEY `uid_ctime` (`uid`, `ctime`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色表';

