CREATE TABLE llx_usermailboxconfig
(
  rowid int(11) NOT NULL AUTO_INCREMENT,
  mailbox_imap_login varchar(255) NOT NULL,
  mailbox_imap_password text NOT NULL,
  mailbox_imap_host varchar(255) NOT NULL,
  mailbox_imap_port varchar(8) NOT NULL,
  fk_user int(11) NOT NULL,
  PRIMARY KEY (`rowid`)
)ENGINE=innodb;