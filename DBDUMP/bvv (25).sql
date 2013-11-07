-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 31, 2013 at 04:32 PM
-- Server version: 5.5.32
-- PHP Version: 5.3.10-1ubuntu3.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bvv`
--

-- --------------------------------------------------------

--
-- Table structure for table `attribute`
--

CREATE TABLE IF NOT EXISTS `attribute` (
  `id_attribute` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_attribute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `attribute_lang`
--

CREATE TABLE IF NOT EXISTS `attribute_lang` (
  `id_attribute` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  KEY `id_attribute` (`id_attribute`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `attribute_value`
--

CREATE TABLE IF NOT EXISTS `attribute_value` (
  `id_attribute_value` int(11) NOT NULL AUTO_INCREMENT,
  `id_attribute` int(11) NOT NULL,
  PRIMARY KEY (`id_attribute_value`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=113 ;

-- --------------------------------------------------------

--
-- Table structure for table `attribute_value_lang`
--

CREATE TABLE IF NOT EXISTS `attribute_value_lang` (
  `id_attribute_value` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  KEY `id_attribute` (`id_lang`),
  KEY `id_attribute_value` (`id_attribute_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `value` text CHARACTER SET latin1 NOT NULL,
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `config_lang`
--

CREATE TABLE IF NOT EXISTS `config_lang` (
  `name` varchar(255) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  KEY `name` (`name`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE IF NOT EXISTS `contact` (
  `id_contact` int(11) NOT NULL AUTO_INCREMENT,
  `contact_to` varchar(255) NOT NULL,
  `contact_from` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `has_been_read` tinyint(4) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_contact`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `entity`
--

CREATE TABLE IF NOT EXISTS `entity` (
  `id_entity` int(11) NOT NULL AUTO_INCREMENT,
  `id_entity_model` int(11) NOT NULL,
  `state` enum('published','draft') NOT NULL,
  `templates` varchar(255) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_entity`),
  KEY `id_modele` (`id_entity_model`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50 ;

-- --------------------------------------------------------

--
-- Table structure for table `entity_field`
--

CREATE TABLE IF NOT EXISTS `entity_field` (
  `id_entity_field` int(11) NOT NULL AUTO_INCREMENT,
  `id_entity` int(11) NOT NULL,
  `id_field_model` int(11) NOT NULL,
  `raw_value` text NOT NULL,
  PRIMARY KEY (`id_entity_field`),
  KEY `id_entity` (`id_entity`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=674 ;

-- --------------------------------------------------------

--
-- Table structure for table `entity_field_lang`
--

CREATE TABLE IF NOT EXISTS `entity_field_lang` (
  `id_entity_field` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `value` text NOT NULL,
  KEY `id_fields` (`id_entity_field`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `entity_lang`
--

CREATE TABLE IF NOT EXISTS `entity_lang` (
  `id_entity` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `link_rewrite` varchar(255) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `meta_description` text NOT NULL,
  KEY `id_entity` (`id_entity`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `entity_level`
--

CREATE TABLE IF NOT EXISTS `entity_level` (
  `id_parent` int(11) NOT NULL,
  `id_entity` int(11) NOT NULL,
  `isdefault` tinyint(1) NOT NULL DEFAULT '0',
  `position` tinyint(4) NOT NULL,
  PRIMARY KEY (`id_parent`,`id_entity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `hook`
--

CREATE TABLE IF NOT EXISTS `hook` (
  `id_hook` int(11) NOT NULL AUTO_INCREMENT,
  `smarty_name` varchar(255) NOT NULL,
  `method_name` varchar(255) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_hook`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `inbound`
--

CREATE TABLE IF NOT EXISTS `inbound` (
  `id_inbound` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_inbound`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `lang`
--

CREATE TABLE IF NOT EXISTS `lang` (
  `id_lang` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` varchar(4) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `defaultlang` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_lang`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE IF NOT EXISTS `media` (
  `id_media` int(11) NOT NULL AUTO_INCREMENT,
  `id_directory` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `mimetype` varchar(50) NOT NULL,
  `size` int(11) NOT NULL COMMENT '// in Kb',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_media`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=646 ;

-- --------------------------------------------------------

--
-- Table structure for table `media_directory`
--

CREATE TABLE IF NOT EXISTS `media_directory` (
  `id_directory` int(11) NOT NULL AUTO_INCREMENT,
  `id_parent` int(11) NOT NULL,
  `dirname` varchar(255) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_directory`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=96 ;

-- --------------------------------------------------------

--
-- Table structure for table `media_lang`
--

CREATE TABLE IF NOT EXISTS `media_lang` (
  `id_media` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alt` varchar(255) NOT NULL,
  KEY `id_media` (`id_media`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `model_entity`
--

CREATE TABLE IF NOT EXISTS `model_entity` (
  `id_entity_model` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `hierarchic` tinyint(4) NOT NULL,
  `id_parent` int(11) NOT NULL,
  `templates` varchar(255) NOT NULL,
  `entities_templates` varchar(255) NOT NULL,
  `deleted` tinyint(4) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_entity_model`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `model_entity_field`
--

CREATE TABLE IF NOT EXISTS `model_entity_field` (
  `id_field_model` int(11) NOT NULL AUTO_INCREMENT,
  `id_entity_model` int(11) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `params` text NOT NULL,
  `position` int(11) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_field_model`),
  KEY `id_model_entity` (`id_entity_model`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

-- --------------------------------------------------------

--
-- Table structure for table `model_entity_field_lang`
--

CREATE TABLE IF NOT EXISTS `model_entity_field_lang` (
  `id_field_model` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  KEY `id_fields` (`id_field_model`,`id_lang`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `model_entity_lang`
--

CREATE TABLE IF NOT EXISTS `model_entity_lang` (
  `id_entity_model` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `link_rewrite` varchar(255) NOT NULL,
  KEY `id_lang` (`id_lang`),
  KEY `id_entity_model` (`id_entity_model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

CREATE TABLE IF NOT EXISTS `module` (
  `id_module` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `module_name` varchar(255) NOT NULL,
  `module_description` text NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_module`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `module_hook`
--

CREATE TABLE IF NOT EXISTS `module_hook` (
  `id_module` int(11) NOT NULL,
  `id_hook` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  KEY `id_module` (`id_module`,`id_hook`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `richtext`
--

CREATE TABLE IF NOT EXISTS `richtext` (
  `id_fulltext` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_permission`
--

CREATE TABLE IF NOT EXISTS `user_permission` (
  `id_user` int(11) NOT NULL,
  `id_entity_model` int(11) NOT NULL,
  `id_entity` int(11) NOT NULL,
  KEY `id_user` (`id_user`,`id_entity_model`,`id_entity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
