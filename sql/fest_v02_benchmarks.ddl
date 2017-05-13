CREATE TABLE `fest_v02_benchmarks` (
  `id`             BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `instance_code`  CHAR(22)            NOT NULL DEFAULT '',
  `hostname`       VARCHAR(255)        NOT NULL DEFAULT '',
  `instance_begin` TIMESTAMP           NOT NULL DEFAULT '0000-00-00 00:00:00',
  `event_time`     TIMESTAMP           NOT NULL DEFAULT '0000-00-00 00:00:00',
  `event_class`    VARCHAR(255)        NOT NULL DEFAULT '',
  `event_function` VARCHAR(255)        NOT NULL DEFAULT '',
  `event`          VARCHAR(255)        NOT NULL DEFAULT '',
  `detail`         VARCHAR(255)        NOT NULL DEFAULT '',
  `created`        TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 102
  DEFAULT CHARSET = utf8;