CREATE TABLE `webIM`.`friend`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `friend_uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '好友id',
  `remark` varchar(255)  NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加时间',
  `gf_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'friend_group_id',
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除:0->否，1->是',
  `resource` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1->查找添加',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '联系人（好友）';

CREATE TABLE `webIM`.`friend_group`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(255)  NOT NULL DEFAULT '' COMMENT '分组名称',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除:0->否，1->是',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '联系人分组';

CREATE TABLE `webIM`.`group`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255)  NOT NULL DEFAULT '' COMMENT '分组名称',
  `master_uid` int(11) UNSIGNED NOT NULL DEFAULT 0 comment '创建人（群主）',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `member_sum` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '成员总数',
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除:0->否，1->是',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '聊天群';

CREATE TABLE `webIM`.`group_member`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` int(11) UNSIGNED NOT NULL DEFAULT 0 comment '群id',
  `uid` int(11) UNSIGNED NOT NULL DEFAULT 0 comment '成员id',
  `remark` varchar(255)  NOT NULL DEFAULT '' COMMENT '成员备注',
  `is_master` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否为管理员，0->不是，1->是',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '加群时间',
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除:0->否，1->是',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '群成员';

CREATE TABLE `webIM`.`private_message`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_uid` int(11) UNSIGNED NOT NULL DEFAULT 0 comment '发信息的人',
  `to_uid` int(11) UNSIGNED NOT NULL DEFAULT 0 comment '接受消息的人',
  `content` varchar(255)  NOT NULL DEFAULT '' COMMENT '消息内容',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '发送时间',
  `is_readed` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否读取:0->否，1->是',
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除:0->否，1->是',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '私人消息';

CREATE TABLE `webIM`.`group_message`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_uid` int(11) UNSIGNED NOT NULL DEFAULT 0 comment '发信息的人',
  `group_id` int(11) UNSIGNED NOT NULL DEFAULT 0 comment '群id',
  `content` varchar(255)  NOT NULL DEFAULT '' COMMENT '消息内容',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '发送时间',
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除:0->否，1->是',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '群消息';