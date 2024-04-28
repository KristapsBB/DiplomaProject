CREATE DATABASE IF NOT EXISTS `diploma_project_db`;
CREATE USER IF NOT EXISTS 'diploma_project_admin'@'localhost' IDENTIFIED BY '123';
SET PASSWORD FOR 'diploma_project_admin'@'localhost' = PASSWORD('123');
GRANT ALL PRIVILEGES ON diploma_project_db.* TO 'diploma_project_admin'@'localhost';
FLUSH PRIVILEGES;
