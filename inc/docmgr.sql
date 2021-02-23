-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 04, 2010 at 07:02 PM
-- Server version: 5.1.37
-- PHP Version: 5.3.2-0.dotdeb.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `docmgr`
--

-- --------------------------------------------------------

--
-- Table structure for table `dokument`
--

CREATE TABLE IF NOT EXISTS `dokument` (
  `dokid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dokname` varchar(60) NOT NULL DEFAULT '',
  `bemerkung` varchar(255) DEFAULT NULL,
  `pfad` varchar(75) NOT NULL DEFAULT '',
  `version` varchar(30) DEFAULT NULL,
  `ist_neu` int(1) NOT NULL DEFAULT '0',
  `erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dokid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `dokument`
--


-- --------------------------------------------------------

--
-- Table structure for table `download`
--

CREATE TABLE IF NOT EXISTS `download` (
  `dlid` int(11) NOT NULL AUTO_INCREMENT,
  `dokid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `zeit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dlid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `download`
--


-- --------------------------------------------------------

--
-- Table structure for table `gruppe`
--

CREATE TABLE IF NOT EXISTS `gruppe` (
  `gid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bezeichnung` varchar(15) DEFAULT NULL,
  `bemerkung` varchar(255) DEFAULT NULL,
  `erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `gruppe`
--

INSERT INTO `gruppe` (`gid`, `bezeichnung`, `bemerkung`, `erstellt`) VALUES
(1, 'admin', NULL, '2002-05-02 09:21:31');

-- --------------------------------------------------------

--
-- Table structure for table `gruppe_dok_rel`
--

CREATE TABLE IF NOT EXISTS `gruppe_dok_rel` (
  `dokid` int(10) unsigned NOT NULL DEFAULT '0',
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dokid`,`gid`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gruppe_dok_rel`
--


-- --------------------------------------------------------

--
-- Table structure for table `kunde`
--

CREATE TABLE IF NOT EXISTS `kunde` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL,
  `passwort` varchar(14) NOT NULL,
  `Nachname` varchar(30) NOT NULL DEFAULT '',
  `Vorname` varchar(30) DEFAULT NULL,
  `anrede` enum('Herr','Frau') NOT NULL DEFAULT 'Herr',
  `email` varchar(60) DEFAULT NULL,
  `bemerkung` varchar(255) DEFAULT NULL,
  `erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `kunde`
--

INSERT INTO `kunde` (`uid`, `username`, `passwort`, `Nachname`, `Vorname`, `anrede`, `email`, `bemerkung`, `erstellt`) VALUES
(1, 'admin', 'adpexzg3FUZAk', 'Administrator', NULL, 'Herr', 'webmaster@domain.tld', NULL, '2010-04-04 18:32:18'),
(2, 'test', 'teH0wLIpW0gyQ', 'Tester', 'Harald', 'Herr', '', NULL, '2010-04-04 18:32:18');

-- --------------------------------------------------------

--
-- Table structure for table `kunde_bak`
--

CREATE TABLE IF NOT EXISTS `kunde_bak` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(12) NOT NULL DEFAULT '',
  `passwort` varchar(12) NOT NULL DEFAULT '',
  `Nachname` varchar(30) NOT NULL DEFAULT '',
  `Vorname` varchar(30) DEFAULT NULL,
  `anrede` enum('Herr','Frau') NOT NULL DEFAULT 'Herr',
  `email` varchar(60) DEFAULT NULL,
  `bemerkung` varchar(255) DEFAULT NULL,
  `erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `kunde_bak`
--

INSERT INTO `kunde_bak` (`uid`, `username`, `passwort`, `Nachname`, `Vorname`, `anrede`, `email`, `bemerkung`, `erstellt`) VALUES
(1, 'admin', 'admin', 'Administrator', NULL, 'Herr', 'webmaster@domain.tld', NULL, '2002-05-02 09:22:48'),
(2, 'test', 'test', 'Tester', 'Harald', 'Herr', '', NULL, '2002-04-25 15:34:19');

-- --------------------------------------------------------

--
-- Table structure for table `kunde_dok_rel`
--

CREATE TABLE IF NOT EXISTS `kunde_dok_rel` (
  `dokid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dokid`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `kunde_dok_rel`
--


-- --------------------------------------------------------

--
-- Table structure for table `kunde_gruppe_rel`
--

CREATE TABLE IF NOT EXISTS `kunde_gruppe_rel` (
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`gid`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `kunde_gruppe_rel`
--

INSERT INTO `kunde_gruppe_rel` (`gid`, `uid`, `erstellt`) VALUES
(1, 1, '2002-05-03 23:29:20');

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

CREATE TABLE IF NOT EXISTS `statistics` (
  `idstatistics` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `dokname` varchar(60) NOT NULL DEFAULT '',
  `version` varchar(30) DEFAULT NULL,
  `dokzeit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bemerkung` varchar(255) DEFAULT NULL,
  `host` varchar(255) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `zeit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idstatistics`),
  KEY `idx_uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `statistics`
--

