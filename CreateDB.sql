CREATE DATABASE `phpSecrets` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */;
USE `phpSecrets`;

CREATE TABLE `tblSecrets` (
  `vcGUID` varchar(99) NOT NULL,
  `vcSecret` varchar(9999) NOT NULL,
  `dtExpiration` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

GRANT
    USAGE ON `phpSecrets`.* TO `phpsecret` @`localhost` IDENTIFIED BY PASSWORD 'SetStrongPassword2!!!!';
GRANT
  SELECT,
  INSERT,
  UPDATE,
  DELETE,
  REFERENCES,
  LOCK TABLES,
  EXECUTE,
  SHOW
  VIEW,
  TRIGGER
  ON `phpSecrets`.* TO `phpsecret` @`localhost`;

CREATE USER
    'expirescript' @'localhost' IDENTIFIED BY PASSWORD 'AnotherStrongPassword!!';

GRANT DELETE ON phpSecrets.tblSecrets TO 'expirescript'@'localhost';