-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Server: localhost
-- Dump Time: 18 Νοε 2012 στις 13:41:31
-- Server Version: 5.5.16
-- Version PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `yii_test`
--

-- --------------------------------------------------------

--
-- table `user` schema
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
    `status` smallint(6) NOT NULL DEFAULT '0',
  `password` varchar(255) DEFAULT NULL,
  `password_strategy` varchar(50) DEFAULT NULL,
   `salt` varchar(255) DEFAULT NULL,
  `requires_new_password` tinyint(1) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT NULL,
  `login_time` int(11) DEFAULT NULL,
  `login_ip` varchar(32) DEFAULT NULL,
  `activation_key` varchar(128) DEFAULT NULL,
  `validation_key` varchar(255) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

--
--  admin user record  Password is :1.Login either with username (admin) or email:(admin@admin.com)
--

INSERT INTO `user` (`id`, `username`, `email`, `status`,`password`,  `password_strategy`,`salt`, `requires_new_password`, `reset_token`, `login_attempts`, `login_time`, `login_ip`, `activation_key`,`validation_key`,  `create_time`, `update_time`) VALUES
(1, 'admin',  'admin@admin.com',1,'$2a$14$JYivYkjkbINiDPHUcJFqpONQmeKO3Asllp9rFOdqqwIKjy8oZAnr6',  'bcrypt','$2a$14$JYivYkjkbINiDPHUcJFqpQ',  NULL, NULL, NULL, NULL, '127.0.0.1',  NULL,'d5c769295189118526dd2e87911f3994','2012-11-18 14:26:59', '2012-11-18 14:26:59');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
