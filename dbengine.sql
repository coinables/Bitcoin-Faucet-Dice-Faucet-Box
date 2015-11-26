-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 25, 2015 at 07:30 PM
-- Server version: 5.5.45-cll-lve
-- PHP Version: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dbengine`
--

-- --------------------------------------------------------

--
-- Table structure for table `faucetbox`
--

CREATE TABLE IF NOT EXISTS `faucetbox` (
  `count` int(60) NOT NULL AUTO_INCREMENT,
  `addy` varchar(100) NOT NULL,
  `time` int(50) NOT NULL,
  `bbb` int(12) NOT NULL,
  `ipp` varchar(100) NOT NULL,
  `reefer` varchar(100) NOT NULL,
  PRIMARY KEY (`count`),
  UNIQUE KEY `addy` (`addy`,`ipp`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1587 ;

-- --------------------------------------------------------

--
-- Table structure for table `faucetboxgames`
--

CREATE TABLE IF NOT EXISTS `faucetboxgames` (
  `gid` varchar(60) NOT NULL,
  `addy` varchar(100) NOT NULL,
  `count` int(30) NOT NULL AUTO_INCREMENT,
  `salt` varchar(100) NOT NULL,
  `roll` decimal(5,2) NOT NULL,
  `bet` int(4) NOT NULL,
  `ltgt` int(1) NOT NULL,
  `uuu` decimal(5,2) NOT NULL,
  `profit` int(6) NOT NULL,
  `open` int(1) NOT NULL,
  `batb` int(20) NOT NULL,
  PRIMARY KEY (`count`),
  UNIQUE KEY `gid` (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=771705 ;

-- --------------------------------------------------------

--
-- Table structure for table `fbbal`
--

CREATE TABLE IF NOT EXISTS `fbbal` (
  `balance` decimal(10,8) NOT NULL,
  `count` int(1) NOT NULL,
  PRIMARY KEY (`balance`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
