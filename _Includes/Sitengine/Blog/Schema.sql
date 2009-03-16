-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 25, 2008 at 11:19 AM
-- Server version: 5.0.51
-- PHP Version: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `pantichrist2008`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog_blogs`
--

CREATE TABLE IF NOT EXISTS `blog_blogs` (
  `id` varchar(14) collate latin1_bin NOT NULL default '',
  `mdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `cdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `oid` varchar(14) collate latin1_bin NOT NULL default '',
  `uid` varchar(14) collate latin1_bin NOT NULL default '',
  `gid` varchar(14) collate latin1_bin NOT NULL default '',
  `rag` int(1) NOT NULL default '0',
  `raw` int(1) NOT NULL default '0',
  `uag` int(1) NOT NULL default '0',
  `uaw` int(1) NOT NULL default '0',
  `dag` int(1) NOT NULL default '0',
  `daw` int(1) NOT NULL default '0',
  `publish` int(1) NOT NULL,
  `titleLang0` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `titleLang1` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `markupLang0` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `markupLang1` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `slug` varchar(255) collate latin1_bin NOT NULL,
  `sorting` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `keyword` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

-- --------------------------------------------------------

--
-- Table structure for table `blog_comments`
--

CREATE TABLE IF NOT EXISTS `blog_comments` (
  `id` varchar(14) collate latin1_bin NOT NULL default '',
  `parentId` varchar(14) collate latin1_bin NOT NULL,
  `mdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `cdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `oid` varchar(14) collate latin1_bin NOT NULL default '',
  `uid` varchar(14) collate latin1_bin NOT NULL default '',
  `gid` varchar(14) collate latin1_bin NOT NULL default '',
  `rag` int(1) NOT NULL default '0',
  `raw` int(1) NOT NULL default '0',
  `uag` int(1) NOT NULL default '0',
  `uaw` int(1) NOT NULL default '0',
  `dag` int(1) NOT NULL default '0',
  `daw` int(1) NOT NULL default '0',
  `approve` int(1) NOT NULL,
  `comment` text character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `shouldyId` (`parentId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

-- --------------------------------------------------------

--
-- Table structure for table `blog_files`
--

CREATE TABLE IF NOT EXISTS `blog_files` (
  `id` varchar(14) collate latin1_bin NOT NULL default '',
  `parentId` varchar(14) collate latin1_bin NOT NULL,
  `mdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `cdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `oid` varchar(14) collate latin1_bin NOT NULL default '',
  `uid` varchar(14) collate latin1_bin NOT NULL default '',
  `gid` varchar(14) collate latin1_bin NOT NULL default '',
  `rag` int(1) NOT NULL default '0',
  `raw` int(1) NOT NULL default '0',
  `uag` int(1) NOT NULL default '0',
  `uaw` int(1) NOT NULL default '0',
  `dag` int(1) NOT NULL default '0',
  `daw` int(1) NOT NULL default '0',
  `file1OriginalSource` varchar(255) collate latin1_bin NOT NULL default '',
  `file1OriginalName` varchar(255) collate latin1_bin NOT NULL default '',
  `file1OriginalSize` varchar(14) collate latin1_bin NOT NULL default '',
  `file1OriginalMime` varchar(255) collate latin1_bin NOT NULL default '',
  `file1OriginalWidth` varchar(5) collate latin1_bin NOT NULL default '',
  `file1OriginalHeight` varchar(5) collate latin1_bin NOT NULL default '',
  `file1ThumbnailSource` varchar(255) collate latin1_bin NOT NULL default '',
  `file1ThumbnailName` varchar(255) collate latin1_bin NOT NULL default '',
  `file1ThumbnailSize` varchar(14) collate latin1_bin NOT NULL default '',
  `file1ThumbnailMime` varchar(255) collate latin1_bin NOT NULL default '',
  `file1ThumbnailWidth` varchar(5) collate latin1_bin NOT NULL default '',
  `file1ThumbnailHeight` varchar(5) collate latin1_bin NOT NULL default '',
  `publish` int(1) NOT NULL,
  `titleLang0` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `titleLang1` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `markupLang0` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `markupLang1` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `sorting` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `shouldyId` (`parentId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` varchar(14) collate latin1_bin NOT NULL default '',
  `blogId` varchar(14) collate latin1_bin NOT NULL,
  `mdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `cdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `oid` varchar(14) collate latin1_bin NOT NULL default '',
  `uid` varchar(14) collate latin1_bin NOT NULL default '',
  `gid` varchar(14) collate latin1_bin NOT NULL default '',
  `rag` int(1) NOT NULL default '0',
  `raw` int(1) NOT NULL default '0',
  `uag` int(1) NOT NULL default '0',
  `uaw` int(1) NOT NULL default '0',
  `dag` int(1) NOT NULL default '0',
  `daw` int(1) NOT NULL default '0',
  `file1OriginalSource` varchar(255) collate latin1_bin NOT NULL default '',
  `file1OriginalName` varchar(255) collate latin1_bin NOT NULL default '',
  `file1OriginalSize` varchar(14) collate latin1_bin NOT NULL default '',
  `file1OriginalMime` varchar(255) collate latin1_bin NOT NULL default '',
  `file1OriginalWidth` varchar(5) collate latin1_bin NOT NULL default '',
  `file1OriginalHeight` varchar(5) collate latin1_bin NOT NULL default '',
  `publish` int(1) NOT NULL,
  `type` varchar(255) collate latin1_bin NOT NULL default '',
  `titleLang0` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `titleLang1` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `markupLang0` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `markupLang1` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `teaserLang0` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `teaserLang1` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `url` varchar(500) collate latin1_bin NOT NULL,
  `embedTag` text collate latin1_bin NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `goodyId` (`blogId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
