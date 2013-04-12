DROP TABLE IF EXISTS llx_usermailboxconfig;

CREATE TABLE llx_usermailboxconfig
(
  rowid int(11) NOT NULL AUTO_INCREMENT,
  mailbox_imap_login varchar(255) NOT NULL,
  mailbox_imap_password text NOT NULL,
  mailbox_imap_host varchar(255) NOT NULL,
  mailbox_imap_port varchar(8) NOT NULL DEFAULT '993',
  mailbox_imap_ssl boolean NOT NULL DEFAULT 1,
  mailbox_imap_ssl_novalidate_cert boolean NOT NULL DEFAULT 0,
  fk_user int(11) NOT NULL,
  PRIMARY KEY (`rowid`)
)ENGINE=innodb;