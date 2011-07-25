# phpMyAdmin SQL Dump
# version 2.5.6
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Sep 30, 2005 at 01:46 AM
# Server version: 3.23.58
# PHP Version: 4.3.8
# 
# Database : `saratogahigh_com_-_main`
# 

# --------------------------------------------------------

#
# Table structure for table `ADMINCAT_LIST`
#

DROP TABLE IF EXISTS `ADMINCAT_LIST`;
CREATE TABLE `ADMINCAT_LIST` (
  `ADMINCAT_ID` int(10) unsigned NOT NULL auto_increment,
  `ADMINCAT_NAME` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`ADMINCAT_ID`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

#
# Dumping data for table `ADMINCAT_LIST`
#

INSERT DELAYED INTO `ADMINCAT_LIST` (`ADMINCAT_ID`, `ADMINCAT_NAME`) VALUES (1, 'Project Development'),
(2, 'Database Maintenance'),
(3, 'Registration'),
(4, 'Troubleshooting'),
(5, 'Map'),
(6, 'Statistics');

# --------------------------------------------------------

#
# Table structure for table `ADMINLINK_LIST`
#

DROP TABLE IF EXISTS `ADMINLINK_LIST`;
CREATE TABLE `ADMINLINK_LIST` (
  `ADMINLINK_ID` int(10) unsigned NOT NULL auto_increment,
  `ADMINLINK_PAGE` int(10) unsigned NOT NULL default '0',
  `ADMINLINK_QUERY` varchar(30) NOT NULL default '',
  `ADMINLINK_CAT` int(10) unsigned NOT NULL default '0',
  `ADMINLINK_NAME` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`ADMINLINK_ID`)
) TYPE=MyISAM AUTO_INCREMENT=27 ;

#
# Dumping data for table `ADMINLINK_LIST`
#

INSERT DELAYED INTO `ADMINLINK_LIST` (`ADMINLINK_ID`, `ADMINLINK_PAGE`, `ADMINLINK_QUERY`, `ADMINLINK_CAT`, `ADMINLINK_NAME`) VALUES (2, 2, '', 2, 'Teacher List'),
(3, 3, '', 2, 'Class List'),
(21, 28, '', 1, 'Aimbot Latest Source'),
(4, 19, '', 2, 'Teacher-Class List'),
(5, 16, '', 2, 'Teacher-Room List'),
(20, 27, '', 1, 'Task List'),
(19, 25, '', 3, 'Donations'),
(10, 15, '', 3, 'Create Account'),
(11, 7, '', 4, 'Duplicate Names'),
(12, 8, '', 5, 'Edit Map'),
(13, 5, '', 1, 'User Comments'),
(14, 11, '', 6, 'Login History Graph'),
(15, 21, '', 2, 'SHdb Control Panel'),
(16, 22, '', 2, 'Calendar Categories'),
(17, 23, '', 6, 'AIM Bot Logs'),
(18, 24, '', 2, 'External Class Links'),
(22, 26, '', 6, 'IP / User Logs'),
(23, 29, '', 4, 'PHP Syntax Check'),
(24, 30, '', 2, 'Merge Parents'),
(25, 32, '', 3, 'Account Comments FAQ'),
(26, 31, 'next=/shcp/', 3, 'Webmail');

# --------------------------------------------------------

#
# Table structure for table `ADMINPAGE_LIST`
#

DROP TABLE IF EXISTS `ADMINPAGE_LIST`;
CREATE TABLE `ADMINPAGE_LIST` (
  `ADMINPAGE_ID` int(10) unsigned NOT NULL auto_increment,
  `ADMINPAGE_PATH` varchar(40) NOT NULL default '',
  `ADMINPAGE_PERMISSION` tinyint(3) unsigned NOT NULL default '0',
  `ADMINPAGE_ACTIONNAME` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`ADMINPAGE_ID`)
) TYPE=MyISAM AUTO_INCREMENT=33 ;

#
# Dumping data for table `ADMINPAGE_LIST`
#

INSERT DELAYED INTO `ADMINPAGE_LIST` (`ADMINPAGE_ID`, `ADMINPAGE_PATH`, `ADMINPAGE_PERMISSION`, `ADMINPAGE_ACTIONNAME`) VALUES (28, 'source.php', 3, ''),
(2, 'teachers.php', 2, ''),
(3, 'classes.php', 2, ''),
(27, 'tasklist.php', 1, ''),
(5, 'comments.php', 1, ''),
(6, 'deltry.php', 2, 'Delete User'),
(7, 'dupnames.php', 2, ''),
(8, 'editmap.php', 2, ''),
(9, 'edittask.php', 1, 'Modify Task'),
(10, 'electionentry.php', 2, 'Modify Election Entry'),
(11, 'graph.php', 1, ''),
(12, 'loginas.php', 2, 'Login As...'),
(13, 'logs.php', 1, 'Login History by Day'),
(14, 'newtask.php', 1, 'Create Task'),
(15, 'newuser.php', 2, ''),
(16, 'teacherroom.php', 2, ''),
(25, 'donations.php', 3, ''),
(18, 'valform.php', 1, 'Duplicate Validation Form'),
(19, 'validclass.php', 1, ''),
(20, 'resetpw.php', 2, 'Reset Password'),
(21, 'sqlcp.php', 3, ''),
(22, 'calcats.php', 1, 'Calendar Categories'),
(23, 'botlog.php', 3, ''),
(24, 'classextern.php', 2, ''),
(26, 'userlog.php', 2, 'Login History by User'),
(29, 'highlight.php', 3, 'PHP Syntax Check'),
(30, 'mergeparents.php', 2, 'Merge Parents'),
(31, 'email.php', 2, 'Webmail'),
(32, 'commentsfaq.php', 1, 'Account Comments FAQ');

# --------------------------------------------------------

#
# Table structure for table `ASBXTRACK_LIST`
#

DROP TABLE IF EXISTS `ASBXTRACK_LIST`;
CREATE TABLE `ASBXTRACK_LIST` (
  `ASBXTRACK_ID` int(10) unsigned NOT NULL auto_increment,
  `ASBXTRACK_NAME` varchar(50) NOT NULL default '',
  `ASBXTRACK_SHORT` varchar(20) NOT NULL default '',
  `ASBXTRACK_GR` smallint(5) unsigned default NULL,
  `ASBXTRACK_DISPLAY` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ASBXTRACK_ID`)
) TYPE=MyISAM AUTO_INCREMENT=15 ;

#
# Dumping data for table `ASBXTRACK_LIST`
#

INSERT DELAYED INTO `ASBXTRACK_LIST` (`ASBXTRACK_ID`, `ASBXTRACK_NAME`, `ASBXTRACK_SHORT`, `ASBXTRACK_GR`, `ASBXTRACK_DISPLAY`) VALUES (1, 'Associated Student Body', 'ASB', NULL, 1),
(2, 'Class of 2004', 'Class of 2004', 2004, 1),
(3, 'Class of 2005', 'Class of 2005', 2005, 1),
(4, 'Senior Class', 'Senior Class', 2006, 1),
(5, 'Junior Class', 'Junior Class', 2007, 1),
(6, 'SaratogaHigh.com Updates', 'SaratogaHigh.com', NULL, 1),
(7, 'Student Bulletins', 'Students', NULL, 0),
(8, 'Alumni Bulletins', 'Alumni', NULL, 0),
(9, 'Teacher Bulletins', 'Teachers', NULL, 0),
(10, 'Parent Bulletins', 'Parents', NULL, 0),
(11, 'General Bulletins', 'General', NULL, 0),
(12, 'PTSA Communications', 'PTSA', 1, 1),
(13, 'Sophomore Class', 'Sophomore Class', 2008, 1),
(14, 'Freshman Class', 'Freshman Class', 2009, 1);

# --------------------------------------------------------

#
# Table structure for table `ASBXUSER_LIST`
#

DROP TABLE IF EXISTS `ASBXUSER_LIST`;
CREATE TABLE `ASBXUSER_LIST` (
  `ASBXUSER_ID` int(11) NOT NULL auto_increment,
  `ASBXUSER_USER` int(10) unsigned NOT NULL default '0',
  `ASBXUSER_TITLE` varchar(25) NOT NULL default '',
  `ASBXUSER_TRACK` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ASBXUSER_ID`),
  UNIQUE KEY `Usertrack` (`ASBXUSER_USER`,`ASBXUSER_TRACK`)
) TYPE=MyISAM AUTO_INCREMENT=58 ;

#
# Dumping data for table `ASBXUSER_LIST`
#

INSERT DELAYED INTO `ASBXUSER_LIST` (`ASBXUSER_ID`, `ASBXUSER_USER`, `ASBXUSER_TITLE`, `ASBXUSER_TRACK`) VALUES (2, 1894, 'ASB President', 1),
(3, 98, 'ASB Vice President', 1),
(4, 1651, 'ASB Secretary', 1),
(5, 2150, 'ASB Treasurer', 1),
(6, 2834, 'Head Commissioner', 1),
(7, 1540, 'Head Commissioner', 1),
(8, 2048, 'Assistant Principal', 1),
(9, 1349, 'Senior Class President', 2),
(10, 2867, 'Senior Class VP', 2),
(11, 2643, 'Senior Class Secretary', 2),
(12, 1534, 'Senior Class Treasurer', 2),
(13, 2012, 'Senior Class President', 3),
(14, 1347, 'Senior Class VP', 3),
(15, 1511, 'Senior Class Secretary', 3),
(16, 4447, 'Senior Class Treasurer', 3),
(17, 2079, 'Senior Class President', 4),
(18, 1653, 'Senior Class VP', 4),
(19, 2499, 'Senior Class Secretary', 4),
(20, 2396, 'Senior Class Treasurer', 4),
(21, 29, 'SaratogaHigh.com Staff', 6),
(22, 11, 'SaratogaHigh.com Staff', 6),
(23, 142, 'SaratogaHigh.com Staff', 6),
(24, 2990, 'Junior Class President', 5),
(25, 1401, 'ASB Board Rep', 1),
(26, 3219, 'Junior Class VP', 5),
(27, 3192, 'Junior Class Secretary', 5),
(52, 4773, 'Sophomore Class President', 13),
(29, 11, 'SaratogaHigh.com Staff', 7),
(30, 29, 'SaratogaHigh.com Staff', 7),
(31, 29, 'SaratogaHigh.com Staff', 8),
(32, 29, 'SaratogaHigh.com Staff', 9),
(33, 29, 'SaratogaHigh.com Staff', 10),
(34, 11, 'SaratogaHigh.com Staff', 8),
(35, 11, 'SaratogaHigh.com Staff', 9),
(36, 11, 'SaratogaHigh.com Staff', 10),
(37, 1, 'SaratogaHigh.com Staff', 7),
(38, 1, 'SaratogaHigh.com Staff', 8),
(39, 1, 'SaratogaHigh.com Staff', 9),
(40, 1, 'SaratogaHigh.com Staff', 10),
(41, 1, 'SaratogaHigh.com Staff', 11),
(42, 11, 'SaratogaHigh.com Staff', 11),
(43, 29, 'SaratogaHigh.com Staff', 11),
(44, 4244, 'PTSA President', 12),
(45, 101, 'SaratogaHigh.com Staff', 7),
(46, 101, 'SaratogaHigh.com Staff', 8),
(47, 101, 'SaratogaHigh.com Staff', 9),
(48, 101, 'SaratogaHigh.com Staff', 10),
(49, 101, 'SaratogaHigh.com Staff', 11),
(50, 101, 'SaratogaHigh.com Staff', 6),
(51, 3201, 'Junior Class Treasurer', 5),
(53, 4209, 'PTSA Vice President', 12),
(54, 4787, 'Sophomore Class VP', 13),
(55, 4539, 'Sophomore Class Secretary', 13),
(56, 4616, 'Sophomore Class Treasurer', 13),
(57, 1566, 'ASB Newsletter', 1);

# --------------------------------------------------------

#
# Table structure for table `ASBX_LIST`
#

DROP TABLE IF EXISTS `ASBX_LIST`;
CREATE TABLE `ASBX_LIST` (
  `ASBX_ID` int(10) unsigned NOT NULL auto_increment,
  `ASBX_SUBJ` varchar(75) NOT NULL default '',
  `ASBX_MSG` text NOT NULL,
  `ASBX_USER` int(10) unsigned NOT NULL default '0',
  `ASBX_TS` datetime NOT NULL default '0000-00-00 00:00:00',
  `ASBX_TRACK` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ASBX_ID`),
  KEY `TrackTS` (`ASBX_TRACK`,`ASBX_TS`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `CLASSCAT_LIST`
#

DROP TABLE IF EXISTS `CLASSCAT_LIST`;
CREATE TABLE `CLASSCAT_LIST` (
  `CLASSCAT_ID` tinyint(3) unsigned NOT NULL auto_increment,
  `CLASSCAT_NAME` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`CLASSCAT_ID`)
) TYPE=MyISAM AUTO_INCREMENT=9 ;

#
# Dumping data for table `CLASSCAT_LIST`
#

INSERT DELAYED INTO `CLASSCAT_LIST` (`CLASSCAT_ID`, `CLASSCAT_NAME`) VALUES (1, 'Applied Arts'),
(2, 'English'),
(3, 'Math'),
(4, 'Science'),
(5, 'Social Studies'),
(6, 'Fine Arts'),
(7, 'World Languages'),
(8, 'General');

# --------------------------------------------------------

#
# Table structure for table `CLASSLINK_LIST`
#

DROP TABLE IF EXISTS `CLASSLINK_LIST`;
CREATE TABLE `CLASSLINK_LIST` (
  `CLASSLINK_ID` int(10) unsigned NOT NULL auto_increment,
  `CLASSLINK_COURSE` int(10) unsigned NOT NULL default '0',
  `CLASSLINK_TEACHER` int(10) unsigned NOT NULL default '0',
  `CLASSLINK_TYPE` enum('Class Website','Online Grades') NOT NULL default 'Class Website',
  `CLASSLINK_URL` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`CLASSLINK_ID`),
  KEY `CLASSLINK_COURSE` (`CLASSLINK_COURSE`),
  KEY `CLASSLINK_TEACHER` (`CLASSLINK_TEACHER`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `CLASS_LIST`
#

DROP TABLE IF EXISTS `CLASS_LIST`;
CREATE TABLE `CLASS_LIST` (
  `CLASS_ID` int(10) unsigned NOT NULL auto_increment,
  `CLASS_NAME` varchar(40) NOT NULL default '',
  `CLASS_SHORTNAME` varchar(40) NOT NULL default '',
  `CLASS_CATEGORY` tinyint(3) unsigned default '0',
  `CLASS_ACTIVE` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`CLASS_ID`),
  KEY `CLASS_CATEGORY` (`CLASS_CATEGORY`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `CMFOLDER_LIST`
#

DROP TABLE IF EXISTS `CMFOLDER_LIST`;
CREATE TABLE `CMFOLDER_LIST` (
  `CMFOLDER_ID` int(10) unsigned NOT NULL auto_increment,
  `CMFOLDER_COURSE` int(10) unsigned default NULL,
  `CMFOLDER_TEACHER` int(10) unsigned default NULL,
  `CMFOLDER_CLASSCAT` tinyint(3) unsigned default NULL,
  `CMFOLDER_TITLE` varchar(255) NOT NULL default '',
  `CMFOLDER_SORT` enum('title','date') NOT NULL default 'date',
  PRIMARY KEY  (`CMFOLDER_ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `CMFRAGMENT_LIST`
#

DROP TABLE IF EXISTS `CMFRAGMENT_LIST`;
CREATE TABLE `CMFRAGMENT_LIST` (
  `CMFRAGMENT_ID` int(10) unsigned NOT NULL auto_increment,
  `CMFRAGMENT_CM` int(10) unsigned NOT NULL default '0',
  `CMFRAGMENT_DATA` text NOT NULL,
  PRIMARY KEY  (`CMFRAGMENT_ID`),
  UNIQUE KEY `CmId` (`CMFRAGMENT_CM`,`CMFRAGMENT_ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `CM_LIST`
#

DROP TABLE IF EXISTS `CM_LIST`;
CREATE TABLE `CM_LIST` (
  `CM_ID` int(10) unsigned NOT NULL auto_increment,
  `CM_FOLDER` int(10) unsigned NOT NULL default '0',
  `CM_TYPE` enum('Link','File','Message') NOT NULL default 'Link',
  `CM_TITLE` varchar(255) NOT NULL default '',
  `CM_AUTHOR` int(10) unsigned NOT NULL default '0',
  `CM_DESC` text NOT NULL,
  `CM_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  `CM_FILENAME` varchar(255) default NULL,
  `CM_FILETYPE` int(10) unsigned default NULL,
  `CM_LENGTH` int(10) unsigned default NULL,
  PRIMARY KEY  (`CM_ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `COLLEGE_LIST`
#

DROP TABLE IF EXISTS `COLLEGE_LIST`;
CREATE TABLE `COLLEGE_LIST` (
  `COLLEGE_ID` int(10) unsigned NOT NULL auto_increment,
  `COLLEGE_NAME` varchar(80) NOT NULL default '',
  `COLLEGE_URL` varchar(80) NOT NULL default '',
  `COLLEGE_CITY` varchar(32) default NULL,
  `COLLEGE_STATE` varchar(32) default NULL,
  PRIMARY KEY  (`COLLEGE_ID`),
  KEY `COLLEGE_STATE` (`COLLEGE_STATE`)
) TYPE=MyISAM AUTO_INCREMENT=95 ;

#
# Dumping data for table `COLLEGE_LIST`
#

INSERT DELAYED INTO `COLLEGE_LIST` (`COLLEGE_ID`, `COLLEGE_NAME`, `COLLEGE_URL`, `COLLEGE_CITY`, `COLLEGE_STATE`) VALUES (1, 'Massachusetts Institute of Technology', 'http://web.mit.edu/', 'Cambridge', 'Massachusetts'),
(2, 'Academy of Art College', 'http://www.academyart.edu/', NULL, NULL),
(3, 'Arizona State University', 'http://www.asu.edu/', NULL, NULL),
(4, 'Boston University', 'http://www.bu.edu/', NULL, NULL),
(5, 'Brigham Young University', 'http://www.byu.edu/index.html', NULL, NULL),
(6, 'Butte College', 'http://www.butte.cc.ca.us/', NULL, NULL),
(7, 'California Culinary Academy', 'http://www.baychef.com/', NULL, NULL),
(8, 'California Institute of Technology', 'http://www.caltech.edu/', NULL, NULL),
(9, 'California Polytechnic State University, San Luis Obispo', 'http://www.calpoly.edu/', NULL, NULL),
(10, 'California State University, Chico', 'http://www.csuchico.edu/', NULL, NULL),
(11, 'California State University, Long Beach', 'http://www.csulb.edu/', NULL, NULL),
(12, 'California State University, Sacramento', 'http://www.csus.edu/', NULL, NULL),
(13, 'Carnegie Mellon University', 'http://www.cmu.edu/', NULL, NULL),
(14, 'College of the Holy Cross', 'http://www.holycross.edu/', NULL, NULL),
(15, 'Cornell University', 'http://www.cornell.edu/', NULL, NULL),
(16, 'Cuesta College', 'http://www.cuesta.edu/index.asp', NULL, NULL),
(17, 'Dartmouth College', 'http://www.dartmouth.edu/', NULL, NULL),
(18, 'De Anza College', 'http://www.deanza.edu/', NULL, NULL),
(19, 'Evergreen Community College', 'http://www.evc.edu/index.htm', NULL, NULL),
(20, 'Foothill Community College', 'http://www.foothill.fhda.edu/', NULL, NULL),
(21, 'George Washington University', 'http://www.gwu.edu/', NULL, NULL),
(22, 'Harvard University', 'http://www.harvard.edu/', NULL, NULL),
(23, 'Johns Hopkins University', 'http://www.jhu.edu/', NULL, NULL),
(24, 'Lane Community College', 'http://www.lanecc.edu/', NULL, NULL),
(25, 'Lewis and Clark College', 'http://www.lclark.edu/', NULL, NULL),
(26, 'Loyola Marymount University', 'http://www.lmu.edu/pages/', NULL, NULL),
(27, 'Mission College', 'http://www.missioncollege.org/', NULL, NULL),
(28, 'New York University', 'http://www.nyu.edu/', NULL, NULL),
(29, 'Northwestern University', 'http://www.northwestern.edu/', NULL, NULL),
(30, 'Occidental College', 'http://www.oxy.edu/', NULL, NULL),
(31, 'Parsons School of Design', 'http://www.parsons.edu/', NULL, NULL),
(32, 'Pomona College', 'http://www.pomona.edu/', NULL, NULL),
(33, 'Princeton University', 'http://www.princeton.edu/', NULL, NULL),
(34, 'Purdue University', 'http://www.purdue.edu/', NULL, NULL),
(35, 'Rhode Island School of Design', 'http://www.risd.edu/', NULL, NULL),
(36, 'San Diego State University', 'http://www.sdsu.edu/', NULL, NULL),
(37, 'San Francisco State University', 'http://www.sfsu.edu/', NULL, NULL),
(38, 'San Jose State University', 'http://www.sjsu.edu/', NULL, NULL),
(39, 'Santa Barbara City College', 'http://www.sbcc.cc.ca.us/', NULL, NULL),
(40, 'Santa Clara University', 'http://www.scu.edu/', NULL, NULL),
(41, 'Santa Monica City College', 'http://www.smc.edu/', NULL, NULL),
(42, 'School of Visual Arts', 'http://www.schoolofvisualarts.edu/', NULL, NULL),
(43, 'Scripps College', 'http://www.scripps.edu/', NULL, NULL),
(44, 'Sierra College', 'http://www.sierra.cc.ca.us/', NULL, NULL),
(45, 'Sonoma State University', 'http://www.sonoma.edu/', NULL, NULL),
(46, 'Southern Methodist University', 'http://www.smu.edu/', NULL, NULL),
(47, 'St. Mary\'s College of California', 'http://www.stmarys-ca.edu/', NULL, NULL),
(48, 'Stanford University', 'http://www.stanford.edu/', NULL, NULL),
(49, 'Tufts University', 'http://www.tufts.edu/', NULL, NULL),
(50, 'University of Arizona', 'http://www.arizona.edu/', NULL, NULL),
(51, 'University of California, Berkeley', 'http://www.berkeley.edu/', NULL, NULL),
(52, 'University of California, Davis', 'http://www.ucdavis.edu/', NULL, NULL),
(53, 'University of California, Irvine', 'http://www.uci.edu/', NULL, NULL),
(54, 'University of California, Los Angeles', 'http://www.ucla.edu/', NULL, NULL),
(55, 'University of California, Riverside', 'http://www.ucr.edu/', NULL, NULL),
(56, 'University of California, San Diego', 'http://www.ucsd.edu/', NULL, NULL),
(57, 'University of California, Santa Barbara', 'http://www.ucsb.edu/', NULL, NULL),
(58, 'University of California, Santa Cruz', 'http://www.ucsc.edu/', NULL, NULL),
(59, 'University of Colorado, Boulder', 'http://www.colorado.edu/', NULL, NULL),
(60, 'University of Illinois, Urbana-Champaign', 'http://www.uiuc.edu/index.html', NULL, NULL),
(61, 'University of Massachusetts, Amherst', 'http://www.umass.edu/', NULL, NULL),
(62, 'University of Michigan, Ann Arbor', 'http://www.umich.edu/', NULL, NULL),
(63, 'University of Oregon', 'http://www.uoregon.edu/', NULL, NULL),
(64, 'University of Pennsylvania', 'http://www.upenn.edu/', NULL, NULL),
(65, 'University of San Diego', 'http://www.sandiego.edu/', NULL, NULL),
(66, 'University of San Francisco', 'http://www.usfca.edu/', NULL, NULL),
(67, 'University of Southern California', 'http://www.usc.edu/', NULL, NULL),
(68, 'University of Tennessee', 'http://www.utk.edu/', NULL, NULL),
(69, 'University of Texas, Austin', 'http://www.utexas.edu/', NULL, NULL),
(70, 'University of the Pacific', 'http://www.uop.edu/', NULL, NULL),
(71, 'University of Utah', 'http://www.utah.edu/', NULL, NULL),
(72, 'University of Washington', 'http://www.washington.edu/', NULL, NULL),
(73, 'University of Wisconsin, Madison', 'http://www.wisc.edu/', NULL, NULL),
(74, 'Washington University at St. Louis', 'http://www.wustl.edu/', NULL, NULL),
(75, 'West Valley College', 'http://www.wvmccd.cc.ca.us/wvc/', NULL, NULL),
(76, 'Whittier College', 'http://www.whittier.edu/', NULL, NULL),
(77, 'Brigham Young University', 'http://home.byu.edu/webapp/home/index.jsp', NULL, NULL),
(78, 'Montana State University, Bozeman', 'http://www.montana.edu/', NULL, NULL),
(79, 'California State University, Monterey Bay', 'http://csumb.edu/', NULL, NULL),
(80, 'Monterey Peninsula College', 'http://www.mpc.edu/', NULL, NULL),
(81, 'Northeastern University', 'http://www.northeastern.edu/', NULL, NULL),
(82, 'Oregon State University', 'http://oregonstate.edu/', NULL, NULL),
(83, 'Pratt Institute', 'http://www.pratt.edu', NULL, NULL),
(84, 'Roger Williams University', 'http://www.rwu.edu/', NULL, NULL),
(85, 'Syracuse University', 'http://www.syr.edu/', NULL, NULL),
(86, 'University of Hartford', 'http://www.hartford.edu/', NULL, NULL),
(87, 'University of Nevada, Las Vegas', 'http://www.unlv.edu/', NULL, NULL),
(88, 'University of Nevada, Reno', 'http://www.unr.edu/content/', NULL, NULL),
(89, 'University of Oxford', 'http://www.ox.ac.uk/', NULL, NULL),
(90, 'University of Wisconsin, Madison', 'http://www.wisc.edu/', NULL, NULL),
(91, 'Yale University', 'http://www.yale.edu/', NULL, NULL),
(92, 'Bucknell University', 'http://www.bucknell.edu/', NULL, NULL),
(93, 'University of Chicago', 'http://www.uchicago.edu/', NULL, NULL),
(94, 'Lafayette College', 'http://www.lafayette.edu/', 'Easton', 'Pennsylvania');

#
# Table structure for table `COMMENTCAT_LIST`
#

DROP TABLE IF EXISTS `COMMENTCAT_LIST`;
CREATE TABLE `COMMENTCAT_LIST` (
  `COMMENTCAT_ID` int(10) unsigned NOT NULL auto_increment,
  `COMMENTCAT_NAME` char(40) NOT NULL default '',
  PRIMARY KEY  (`COMMENTCAT_ID`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

#
# Dumping data for table `COMMENTCAT_LIST`
#

INSERT DELAYED INTO `COMMENTCAT_LIST` (`COMMENTCAT_ID`, `COMMENTCAT_NAME`) VALUES (1, 'General'),
(2, 'Password Requests');

# --------------------------------------------------------

#
# Table structure for table `COMMENTRESPONSE_LIST`
#

DROP TABLE IF EXISTS `COMMENTRESPONSE_LIST`;
CREATE TABLE `COMMENTRESPONSE_LIST` (
  `COMMENTRESPONSE_ID` int(4) unsigned NOT NULL auto_increment,
  `COMMENTRESPONSE_COMMENT` int(4) unsigned NOT NULL default '0',
  `COMMENTRESPONSE_RESPONS` longtext NOT NULL,
  UNIQUE KEY `COMMENTRESPONSE_ID` (`COMMENTRESPONSE_ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `COMMENTRESPONSE_LIST`
#


# --------------------------------------------------------

#
# Table structure for table `COMMENT_LIST`
#

DROP TABLE IF EXISTS `COMMENT_LIST`;
CREATE TABLE `COMMENT_LIST` (
  `COMMENT_ID` int(10) unsigned NOT NULL auto_increment,
  `COMMENT_TS` datetime NOT NULL default '0000-00-00 00:00:00',
  `COMMENT_PAGE` varchar(250) NOT NULL default '',
  `COMMENT_TEXT` text NOT NULL,
  `COMMENT_USER` int(10) unsigned default NULL,
  `COMMENT_ARCHIVED` tinyint(3) unsigned NOT NULL default '0',
  `COMMENT_CAT` int(10) unsigned default '1',
  PRIMARY KEY  (`COMMENT_ID`),
  KEY `COMMENT_CAT` (`COMMENT_CAT`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `CSL_LIST`
#

DROP TABLE IF EXISTS `CSL_LIST`;
CREATE TABLE `CSL_LIST` (
  `CSL_ID` int(10) unsigned NOT NULL auto_increment,
  `CSL_LAST` varchar(40) NOT NULL default '',
  `CSL_FIRST` varchar(40) NOT NULL default '',
  `CSL_MIDDLE` varchar(40) NOT NULL default '',
  `CSL_GR` int(10) unsigned NOT NULL default '0',
  `CSL_SID` smallint(5) unsigned NOT NULL default '0',
  `CSL_TPT` varchar(40) NOT NULL default '',
  `CSL_ADDRESS` varchar(200) NOT NULL default '',
  `CSL_CITY` varchar(40) NOT NULL default '',
  `CSL_STATE` char(2) NOT NULL default '',
  `CSL_ZIP` int(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`CSL_ID`),
  KEY `LAST` (`CSL_LAST`,`CSL_FIRST`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `DONATION_LIST`
#

DROP TABLE IF EXISTS `DONATION_LIST`;
CREATE TABLE `DONATION_LIST` (
  `DONATION_ID` int(10) unsigned NOT NULL auto_increment,
  `DONATION_USER` int(10) unsigned NOT NULL default '0',
  `DONATION_AMT` decimal(7,2) NOT NULL default '0.00',
  `DONATION_TS` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`DONATION_ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `ELECTIONGRADE_LIST`
#

DROP TABLE IF EXISTS `ELECTIONGRADE_LIST`;
CREATE TABLE `ELECTIONGRADE_LIST` (
  `ELECTIONGRADE_ID` int(10) unsigned NOT NULL auto_increment,
  `ELECTIONGRADE_ELECTION` int(10) unsigned NOT NULL default '0',
  `ELECTIONGRADE_GRADE` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ELECTIONGRADE_ID`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

#
# Dumping data for table `ELECTIONGRADE_LIST`
#

INSERT DELAYED INTO `ELECTIONGRADE_LIST` (`ELECTIONGRADE_ID`, `ELECTIONGRADE_ELECTION`, `ELECTIONGRADE_GRADE`) VALUES (1, 2, 11),
(2, 1, 11),
(3, 1, 10),
(4, 1, 9),
(5, 3, 10),
(6, 4, 9);

# --------------------------------------------------------

#
# Table structure for table `ELECTION_LIST`
#

DROP TABLE IF EXISTS `ELECTION_LIST`;
CREATE TABLE `ELECTION_LIST` (
  `ELECTION_ID` int(10) unsigned NOT NULL auto_increment,
  `ELECTION_NAME` varchar(40) NOT NULL default '',
  `ELECTION_DISPLAY` tinyint(3) unsigned NOT NULL default '0',
  `ELECTION_DATE` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ELECTION_ID`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

# --------------------------------------------------------

#
# Table structure for table `EMAIL_LIST`
#

DROP TABLE IF EXISTS `EMAIL_LIST`;
CREATE TABLE `EMAIL_LIST` (
  `EMAIL_ID` int(10) unsigned NOT NULL auto_increment,
  `EMAIL_USER` int(10) unsigned NOT NULL default '0',
  `EMAIL_EMAIL` varchar(60) NOT NULL default '',
  PRIMARY KEY  (`EMAIL_ID`),
  KEY `EMAIL_USER` (`EMAIL_USER`)
) TYPE=MyISAM AUTO_INCREMENT=1066 ;

#
# Table structure for table `EVENTCAT_LIST`
#

DROP TABLE IF EXISTS `EVENTCAT_LIST`;
CREATE TABLE `EVENTCAT_LIST` (
  `EVENTCAT_ID` int(10) unsigned NOT NULL auto_increment,
  `EVENTCAT_NAME` varchar(40) NOT NULL default '',
  `EVENTCAT_ISTEST` tinyint(3) unsigned NOT NULL default '0',
  `EVENTCAT_ORDER` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`EVENTCAT_ID`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

#
# Dumping data for table `EVENTCAT_LIST`
#

INSERT DELAYED INTO `EVENTCAT_LIST` (`EVENTCAT_ID`, `EVENTCAT_NAME`, `EVENTCAT_ISTEST`, `EVENTCAT_ORDER`) VALUES (1, 'Homework', 0, 0),
(2, 'Project', 0, 3),
(3, 'Quiz', 1, 1),
(4, 'Test', 1, 2),
(6, 'Event', 0, 4);

# --------------------------------------------------------

#
# Table structure for table `EVENT_LIST`
#

DROP TABLE IF EXISTS `EVENT_LIST`;
CREATE TABLE `EVENT_LIST` (
  `EVENT_ID` int(10) unsigned NOT NULL auto_increment,
  `EVENT_LAYER` int(10) unsigned NOT NULL default '0',
  `EVENT_LASTAUTHOR` int(10) unsigned NOT NULL default '0',
  `EVENT_DATE` int(10) unsigned NOT NULL default '0',
  `EVENT_TIME` int(11) NOT NULL default '-1',
  `EVENT_DURATION` int(10) unsigned NOT NULL default '0',
  `EVENT_RECUREND` int(10) unsigned NOT NULL default '0',
  `EVENT_RECUR` enum('none','day','week','month','year') NOT NULL default 'none',
  `EVENT_RECURPARAM` int(10) unsigned NOT NULL default '0',
  `EVENT_RECURFREQ` int(10) unsigned NOT NULL default '1',
  `EVENT_TITLE` varchar(120) NOT NULL default '',
  `EVENT_LOCATION` varchar(120) NOT NULL default '',
  `EVENT_CAT` int(10) unsigned NOT NULL default '1',
  `EVENT_DESC` text NOT NULL,
  PRIMARY KEY  (`EVENT_ID`),
  FULLTEXT KEY `EVENT_TITLE` (`EVENT_TITLE`),
  FULLTEXT KEY `EVENT_DESC` (`EVENT_DESC`),
  KEY `LayerRecurDateTime` (`EVENT_LAYER`,`EVENT_RECUR`,`EVENT_DATE`,`EVENT_TIME`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `FILETYPE_LIST`
#

DROP TABLE IF EXISTS `FILETYPE_LIST`;
CREATE TABLE `FILETYPE_LIST` (
  `FILETYPE_ID` int(10) unsigned NOT NULL auto_increment,
  `FILETYPE_EXT` varchar(8) NOT NULL default '',
  `FILETYPE_MIME` varchar(32) NOT NULL default '',
  `FILETYPE_DESC` varchar(128) NOT NULL default '',
  `FILETYPE_ICON` varchar(128) NOT NULL default '',
  `FILETYPE_VIEWABLE` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`FILETYPE_ID`)
) TYPE=MyISAM AUTO_INCREMENT=10 ;

#
# Dumping data for table `FILETYPE_LIST`
#

INSERT DELAYED INTO `FILETYPE_LIST` (`FILETYPE_ID`, `FILETYPE_EXT`, `FILETYPE_MIME`, `FILETYPE_DESC`, `FILETYPE_ICON`, `FILETYPE_VIEWABLE`) VALUES (1, 'txt', 'text/plain', 'Text Document', 'txtdoc.gif', '1'),
(2, 'html', 'text/html', 'HTML Document', 'blankdoc.gif', '1'),
(3, 'htm', 'text/html', 'HTML Document', 'blankdoc.gif', '1'),
(4, 'pdf', 'application/pdf', 'PDF Document', 'pdf.gif', '0'),
(5, 'xls', 'application/vnd.ms-excel', 'Excel Spreadsheet', 'xl.gif', '0'),
(6, 'doc', 'application/msword', 'Word Document', 'doc.gif', '0'),
(7, 'ppt', 'application/vnd.ms-powerpoint', 'PowerPoint Presentation', 'ppt.gif', '0'),
(9, 'exe', 'application/octet-stream', 'Application', 'exe.gif', '0');

# --------------------------------------------------------

#
# Table structure for table `FOREIGNKEY_LIST`
#

DROP TABLE IF EXISTS `FOREIGNKEY_LIST`;
CREATE TABLE `FOREIGNKEY_LIST` (
  `FOREIGNKEY_ID` int(10) unsigned NOT NULL auto_increment,
  `FOREIGNKEY_TABLE` varchar(50) NOT NULL default '',
  `FOREIGNKEY_FIELD` varchar(50) NOT NULL default '',
  `FOREIGNKEY_KEYTABLE` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`FOREIGNKEY_ID`)
) TYPE=MyISAM AUTO_INCREMENT=113 ;

#
# Dumping data for table `FOREIGNKEY_LIST`
#

INSERT DELAYED INTO `FOREIGNKEY_LIST` (`FOREIGNKEY_ID`, `FOREIGNKEY_TABLE`, `FOREIGNKEY_FIELD`, `FOREIGNKEY_KEYTABLE`) VALUES (1, 'ADMINLINK_LIST', 'ADMINLINK_PAGE', 'ADMINPAGE_LIST'),
(2, 'ADMINLINK_LIST', 'ADMINLINK_CAT', 'ADMINCAT_LIST'),
(3, 'CANDIDATE_LIST', 'CANDIDATE_RACE', 'RACE_LIST'),
(4, 'CANDIDATE_LIST', 'CANDIDATE_UID', 'USER_LIST'),
(5, 'CLASSLINK_LIST', 'CLASSLINK_COURSE', 'CLASS_LIST'),
(6, 'CLASSLINK_LIST', 'CLASSLINK_TEACHER', 'TEACHER_LIST'),
(7, 'CLASS_LIST', 'CLASS_CATEGORY', 'CLASSCAT_LIST'),
(8, 'COMMENT_LIST', 'COMMENT_USER', 'USER_LIST'),
(9, 'ELECTIONGRADE_LIST', 'ELECTIONGRADE_ELECTION', 'ELECTION_LIST'),
(10, 'EMAIL_LIST', 'EMAIL_USER', 'USER_LIST'),
(11, 'EVENT_LIST', 'EVENT_LAYER', 'LAYER_LIST'),
(12, 'EVENT_LIST', 'EVENT_LASTAUTHOR', 'USER_LIST'),
(13, 'EVENT_LIST', 'EVENT_CAT', 'EVENTCAT_LIST'),
(14, 'IM_LIST', 'IM_USER', 'USER_LIST'),
(15, 'IM_LIST', 'IM_SERVICE', 'IMSERVICE_LIST'),
(16, 'LAYERCAT_LIST', 'LAYERCAT_LAYER', 'LAYER_LIST'),
(17, 'LAYERCAT_LIST', 'LAYERCAT_CATEGORY', 'CALCAT_LIST'),
(18, 'LAYERUSER_LIST', 'LAYERUSER_LAYER', 'LAYER_LIST'),
(19, 'LAYERUSER_LIST', 'LAYERUSER_USER', 'USER_LIST'),
(20, 'LAYERUSER_LIST', 'LAYERUSER_COLOR', 'CALCOLOR_LIST'),
(21, 'LAYER_LIST', 'LAYER_CLASS', 'CLASS_LIST'),
(22, 'LAYER_LIST', 'LAYER_TEACHER', 'TEACHER_LIST'),
(23, 'LAYER_LIST', 'LAYER_PERSONAL', 'USER_LIST'),
(24, 'MAILFOLDER_LIST', 'MAILFOLDER_OWNER', 'USER_LIST'),
(25, 'MAILREC_LIST', 'MAILREC_MSG', 'MAIL_LIST'),
(26, 'MAILREC_LIST', 'MAILREC_RECIPIENT', 'USER_LIST'),
(27, 'MAILREC_LIST', 'MAILREC_FOLDER', 'MAILFOLDER_LIST'),
(28, 'MAIL_LIST', 'MAIL_SENDER', 'USER_LIST'),
(29, 'MAPNODE_LIST', 'MAPNODE_ROOM', 'ROOM_LIST'),
(30, 'NOTEPAGE_LIST', 'NOTEPAGE_OWNER', 'USER_LIST'),
(31, 'PHONE_LIST', 'PHONE_USER', 'USER_LIST'),
(32, 'RACE_LIST', 'RACE_ELECTION', 'ELECTION_LIST'),
(33, 'SCHEDSPORT_LIST', 'SCHEDSPORT_USER', 'USER_LIST'),
(34, 'SCHEDSPORT_LIST', 'SCHEDSPORT_SPORT', 'SPORT_LIST'),
(35, 'SCHED_LIST', 'SCHED_USER', 'USER_LIST'),
(36, 'SCHED_LIST', 'SCHED_CLASS', 'CLASS_LIST'),
(37, 'SCHED_LIST', 'SCHED_TEACHER', 'TEACHER_LIST'),
(89, 'TASK_LIST', 'TASK_STATUS', 'TASKSTATUS_LIST'),
(39, 'TASK_LIST', 'TASK_PRIORITY', 'TASKPRIORITY_LIST'),
(40, 'TASK_LIST', 'TASK_CAT', 'TASKCAT_LIST'),
(41, 'TASK_LIST', 'TASK_AUTHOR', 'USER_LIST'),
(42, 'TEACHERROOM_LIST', 'TEACHERROOM_TEACHER', 'TEACHER_LIST'),
(43, 'TEACHERROOM_LIST', 'TEACHERROOM_ROOM', 'ROOM_LIST'),
(44, 'USERCOLLEGE_LIST', 'USERCOLLEGE_USER', 'USER_LIST'),
(45, 'USERCOLLEGE_LIST', 'USERCOLLEGE_COLLEGE', 'COLLEGE_LIST'),
(46, 'USER_LIST', 'USER_TEACHERTAG', 'TEACHER_LIST'),
(47, 'VALIDCLASS_LIST', 'VALIDCLASS_COURSE', 'CLASS_LIST'),
(48, 'VALIDCLASS_LIST', 'VALIDCLASS_TEACHER', 'TEACHER_LIST'),
(49, 'SIGNUPUSER_LIST', 'SIGNUPUSER_SIGNUP', 'SIGNUP_LIST'),
(50, 'SIGNUP_LIST', 'SIGNUP_CREATOR', 'USER_LIST'),
(51, 'SIGNUPUSER_LIST', 'SIGNUPUSER_USER', 'USER_LIST'),
(52, 'HEDWIGVOL_LIST', 'HEDWIGVOL_USER', 'USER_LIST'),
(53, 'HEDWIGVOL_LIST', 'HEDWIGVOL_HEDWIG', 'HEDWIG_LIST'),
(54, 'HEDWIGLEG_LIST', 'HEDWIGLEG_HEDWIG', 'HEDWIG_LIST'),
(55, 'HEDWIGRUNNER_LIST', 'HEDWIGRUNNER_LEG', 'HEDWIGLEG_LIST'),
(56, 'HEDWIGRUNNER_LIST', 'HEDWIGRUNNER_USER', 'USER_LIST'),
(57, 'ASBXUSER_LIST', 'ASBXUSER_USER', 'USER_LIST'),
(58, 'ASBXUSER_LIST', 'ASBXUSER_TRACK', 'ASBXTRACK_LIST'),
(59, 'ASBX_LIST', 'ASBX_USER', 'USER_LIST'),
(60, 'ASBX_LIST', 'ASBX_TRACK', 'ASBXTRACK_LIST'),
(61, 'DONATION_LIST', 'DONATION_USER', 'USER_LIST'),
(62, 'HEDWIGCHECKIN_LIST', 'HEDWIGCHECKIN_HEDWIG', 'HEDWIG_LIST'),
(63, 'HEDWIGCHECKIN_LIST', 'HEDWIGCHECKIN_USER', 'USER_LIST'),
(64, 'PARENTSTUDENT_LIST', 'PARENTSTUDENT_PARENT', 'USER_LIST'),
(65, 'PARENTSTUDENT_LIST', 'PARENTSTUDENT_STUDENT', 'USER_LIST'),
(66, 'POSITIONUSER_LIST', 'POSITIONUSER_POSITION', 'POSITION_LIST'),
(67, 'POSITIONUSER_LIST', 'POSITIONUSER_USER', 'USER_LIST'),
(68, 'QAAUTHOR_LIST', 'QAAUTHOR_QAGROUP', 'QAGROUP_LIST'),
(69, 'QAAUTHOR_LIST', 'QAAUTHOR_USER', 'USER_LIST'),
(70, 'QAFILLPAGE_LIST', 'QAFILLPAGE_FILL', 'QAFILL_LIST'),
(71, 'QAFILLPAGE_LIST', 'QAFILLPAGE_PAGE', 'QAPAGE_LIST'),
(72, 'QAFILL_LIST', 'QAFILL_QA', 'QA_LIST'),
(73, 'QAFILL_LIST', 'QAFILL_USER', 'USER_LIST'),
(74, 'QAINVITE_LIST', 'QAINVITE_QA', 'QA_LIST'),
(75, 'QAINVITE_LIST', 'QAINVITE_USER', 'USER_LIST'),
(76, 'QAMC_LIST', 'QAMC_QUESTION', 'QAQUESTION_LIST'),
(77, 'QAPAGE_LIST', 'QAPAGE_QA', 'QA_LIST'),
(78, 'QAQUESTION_LIST', 'QAQUESTION_PAGE', 'QAPAGE_LIST'),
(79, 'QAQUESTION_LIST', 'QAQUESTION_FORMAT', 'QAFORMAT_LIST'),
(80, 'QARESP_LIST', 'QARESP_FILLPAGE', 'QAFILLPAGE_LIST'),
(81, 'QARESP_LIST', 'QARESP_QUESTION', 'QAQUESTION_LIST'),
(82, 'SELLBUY_LIST', 'SELLBUY_ITEM', 'SELL_LIST'),
(83, 'SELLBUY_LIST', 'SELLBUY_BUYER', 'USER_LIST'),
(84, 'SELL_LIST', 'SELL_CAT', 'SELLCAT_LIST'),
(85, 'SIGNUPFIELDS_LIST', 'SIGNUPFIELDS_SIGNUP', 'SIGNUPUSER_LIST'),
(86, 'QA_LIST', 'QA_GROUP', 'QAGROUP_LIST'),
(98, 'CMFOLDER_LIST', 'CMFOLDER_TEACHER', 'TEACHER_LIST'),
(97, 'CMFOLDER_LIST', 'CMFOLDER_COURSE', 'CLASS_LIST'),
(90, 'TASK_LIST', 'TASK_TYPE', 'TASKTYPE_LIST'),
(91, 'SLIDEGROUP_LIST', 'SLIDEGROUP_AUTHOR', 'USER_LIST'),
(92, 'SLIDEGROUP_LIST', 'SLIDEGROUP_CURRENTSLIDE', 'SLIDE_LIST'),
(93, 'SLIDEUSER_LIST', 'SLIDEUSER_SLIDEGROUP', 'SLIDEGROUP_LIST'),
(94, 'SLIDEUSER_LIST', 'SLIDEUSER_USER', 'USER_LIST'),
(95, 'SLIDE_LIST', 'SLIDE_SLIDEGROUP', 'SLIDEGROUP_LIST'),
(96, 'COMMENTRESPONSE_LIST', 'COMMENTRESPONSE_COMMENT', 'COMMENT_LIST'),
(99, 'CMFOLDER_LIST', 'CMFOLDER_CLASSCAT', 'CLASSCAT_LIST'),
(100, 'COMMENT_LIST', 'COMMENT_CAT', 'COMMENTCAT_LIST'),
(101, 'FRONTBOX_LIST', 'FRONTBOX_CAT', 'FRONTCAT_LIST'),
(102, 'FRONTPREF_LIST', 'FRONTPREF_BOX', 'FRONTBOX_LIST'),
(103, 'FRONTPREF_LIST', 'FRONTPREF_USER', 'USER_LIST'),
(104, 'LOG_LIST', 'LOG_USER', 'USER_LIST'),
(105, 'VALIDCLASS_LIST', 'VALIDCLASS_ROOM', 'ROOM_LIST'),
(106, 'CM_LIST', 'CM_FOLDER', 'CMFOLDER_LIST'),
(107, 'CM_LIST', 'CM_AUTHOR', 'USER_LIST'),
(108, 'CM_LIST', 'CM_FILETYPE', 'FILETYPE_LIST'),
(109, 'SUM_LIST', 'SUM_PAYPAL', 'PAYPAL_LIST'),
(110, 'TALKMSG_LIST', 'TALKMSG_GROUP', 'TALKGROUP_LIST'),
(111, 'TALKMSG_LIST', 'TALKMSG_ROOT', 'TALKMSG_LIST'),
(112, 'TALKMSG_LIST', 'TALKMSG_PARENT', 'TALKMSG_LIST');

# --------------------------------------------------------

#
# Table structure for table `FRONTBOX_LIST`
#

DROP TABLE IF EXISTS `FRONTBOX_LIST`;
CREATE TABLE `FRONTBOX_LIST` (
  `FRONTBOX_ID` int(3) NOT NULL auto_increment,
  `FRONTBOX_HTMLID` varchar(30) NOT NULL default '',
  `FRONTBOX_TITLE` varchar(50) NOT NULL default '',
  `FRONTBOX_CAT` int(2) unsigned NOT NULL default '0',
  `FRONTBOX_FUNCTION` varchar(30) NOT NULL default '',
  `FRONTBOX_PHPARGUMENT` longtext NOT NULL,
  UNIQUE KEY `FRONTBOX_ID` (`FRONTBOX_ID`)
) TYPE=MyISAM AUTO_INCREMENT=31 ;

#
# Dumping data for table `FRONTBOX_LIST`
#

INSERT DELAYED INTO `FRONTBOX_LIST` (`FRONTBOX_ID`, `FRONTBOX_HTMLID`, `FRONTBOX_TITLE`, `FRONTBOX_CAT`, `FRONTBOX_FUNCTION`, `FRONTBOX_PHPARGUMENT`) VALUES (1, 'asb_block', 'ASB News', 3, 'news_block', '1'),
(2, 'saratogahigh_block', 'SaratogaHigh.com News', 3, 'news_block', '6'),
(3, 'grade_block', 'My Class News', 3, 'grade_block', ''),
(4, 'calendar_block', 'My Calendar', 3, 'calendar_block', ''),
(5, 'mail_block', 'My Mail', 3, 'mail_block', ''),
(6, 'notes_block', 'My Notes', 3, 'notes_block', ''),
(7, 'weather_block', 'Current Weather', 4, 'weather_block', 'false'),
(8, 'slashdot_block', 'Slashdot', 2, 'rss_block', '"Slashdot","http://www.slashdot.org/index.rss","http://www.slashdot.org/"'),
(9, 'blogdex_block', 'Blogdex', 2, 'rss_block', '"Blogdex","http://www.blogdex.net/xml/index.asp","http://www.blogdex.net/"'),
(10, 'metafilter_block', 'Metafilter', 2, 'rss_block', '"Metafilter","http://xml.metafilter.com/rss.xml","http://www.metafilter.com/",true'),
(11, 'nytimes_block', 'New York Times', 1, 'rss_block', '"New York Times","http://www.nytimes.com/services/xml/rss/nyt/HomePage.xml","http://www.nytimes.com/",true'),
(12, 'reuters_block', 'Reuters', 1, 'rss_block', '"Reuters","http://today.reuters.com/rss/topNews","http://www.reuters.com/"'),
(13, 'fark_block', 'FARK', 2, 'fark_block', ''),
(14, 'google_block', 'Google', 4, 'google_block', ''),
(15, 'xanga_block', 'Xanga', 2, 'xanga_block', ''),
(16, 'livejournal_block', 'Livejournal', 2, 'livejournal_block', ''),
(17, 'wired_block', 'Wired News', 2, 'rss_block', '"Wired News","http://www.wired.com/news/feeds/rss2/0,2610,,00.xml","http://www.wired.com/",true'),
(18, 'washington_block', 'Washington Post', 1, 'rss_block', '"Washington Post","http://www.washingtonpost.com/wp-dyn/rss/linkset/2005/03/24/LI2005032400102.xml","http://www.washingtonpost.com/"'),
(19, 'usatoday_block', 'USA Today', 1, 'rss_block', '"USA Today","http://www.usatoday.com/repurposing/NewslineRss.xml","http://www.usatoday.com/",true'),
(20, 'bbc_block', 'BBC News', 1, 'rss_block', '"BBC News","http://newsrss.bbc.co.uk/rss/newsonline_world_edition/front_page/rss.xml","http://www.bbc.co.uk/",true'),
(21, 'abc_block', 'ABC News', 1, 'rss_block', '"ABC News","http://my.abcnews.go.com/rsspublic/fp_rss20.xml","http://www.abcnews.com/",true'),
(22, 'cnn_block', 'CNN', 1, 'rss_block', '"CNN","http://rss.cnn.com/rss/cnn_topstories.rss","http://www.cnn.com/"'),
(23, 'tech_block', 'Tech Dirt', 2, 'rss_block', '"Tech Dirt","http://www.techdirt.com/techdirt_rss.xml","http://www.techdirt.com/"'),
(24, 'cnet_block', 'CNET.com', 1, 'rss_block', '"CNET.com","http://news.com.com/2547-1_3-0-5.xml","http://news.com.com/",true'),
(25, 'classified_block', 'Classifieds', 3, 'classified_block', ''),
(28, 'task_block', 'Project Tasks', 3, 'task_block', ''),
(29, 'classes_block', 'My Classes', 3, 'classes_block', ''),
(30, 'gnews_block', 'Google News', 1, 'rss_block', '"Google News","http://news.google.com/?ned=us&topic=n&output=rss","http://news.google.com/"');

# --------------------------------------------------------

#
# Table structure for table `FRONTCAT_LIST`
#

DROP TABLE IF EXISTS `FRONTCAT_LIST`;
CREATE TABLE `FRONTCAT_LIST` (
  `FRONTCAT_ID` int(2) unsigned NOT NULL auto_increment,
  `FRONTCAT_TITLE` varchar(40) NOT NULL default '',
  UNIQUE KEY `FRONTCAT_ID` (`FRONTCAT_ID`)
) TYPE=MyISAM AUTO_INCREMENT=5 ;

#
# Dumping data for table `FRONTCAT_LIST`
#

INSERT DELAYED INTO `FRONTCAT_LIST` (`FRONTCAT_ID`, `FRONTCAT_TITLE`) VALUES (1, 'News'),
(2, 'Blogs'),
(3, 'SaratogaHigh.com'),
(4, 'Services');

# --------------------------------------------------------

#
# Table structure for table `FRONTPREF_LIST`
#

DROP TABLE IF EXISTS `FRONTPREF_LIST`;
CREATE TABLE `FRONTPREF_LIST` (
  `FRONTPREF_ID` int(10) unsigned NOT NULL auto_increment,
  `FRONTPREF_USER` int(10) unsigned NOT NULL default '0',
  `FRONTPREF_BOX` varchar(4) NOT NULL default '',
  `FRONTPREF_KEY` longtext NOT NULL,
  `FRONTPREF_VALUE` longtext NOT NULL,
  UNIQUE KEY `FRONTPREF_ID` (`FRONTPREF_ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `HEDWIGCHECKIN_LIST`
#

DROP TABLE IF EXISTS `HEDWIGCHECKIN_LIST`;
CREATE TABLE `HEDWIGCHECKIN_LIST` (
  `HEDWIGCHECKIN_ID` int(10) unsigned NOT NULL auto_increment,
  `HEDWIGCHECKIN_HEDWIG` int(10) unsigned NOT NULL default '0',
  `HEDWIGCHECKIN_USER` int(10) unsigned NOT NULL default '0',
  `HEDWIGCHECKIN_IN` datetime default NULL,
  `HEDWIGCHECKIN_OUT` datetime default NULL,
  PRIMARY KEY  (`HEDWIGCHECKIN_ID`),
  KEY `HEDWIGCHECKIN_USER` (`HEDWIGCHECKIN_USER`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `HEDWIGLEG_LIST`
#

DROP TABLE IF EXISTS `HEDWIGLEG_LIST`;
CREATE TABLE `HEDWIGLEG_LIST` (
  `HEDWIGLEG_ID` int(10) unsigned NOT NULL auto_increment,
  `HEDWIGLEG_HEDWIG` int(10) unsigned NOT NULL default '0',
  `HEDWIGLEG_BEGIN_STOP` int(10) unsigned NOT NULL default '0',
  `HEDWIGLEG_END_STOP` int(10) unsigned NOT NULL default '0',
  `HEDWIGLEG_BEGIN_ADDR` varchar(120) NOT NULL default '',
  `HEDWIGLEG_END_ADDR` varchar(120) NOT NULL default '',
  `HEDWIGLEG_TERRAIN` int(10) unsigned default NULL,
  `HEDWIGLEG_DESC` varchar(60) NOT NULL default '',
  `HEDWIGLEG_MAP` varchar(30) NOT NULL default '',
  `HEDWIGLEG_LEN` decimal(3,1) NOT NULL default '0.0',
  `HEDWIGLEG_STATUS` tinyint(3) unsigned NOT NULL default '0',
  `HEDWIGLEG_DRIVER_OUT` int(10) unsigned default NULL,
  `HEDWIGLEG_DRIVER_IN` int(10) unsigned default NULL,
  PRIMARY KEY  (`HEDWIGLEG_ID`),
  KEY `ORDERS` (`HEDWIGLEG_HEDWIG`,`HEDWIGLEG_BEGIN_STOP`),
  KEY `HEDWIGLEG_LEN` (`HEDWIGLEG_LEN`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `HEDWIGRUNNER_LIST`
#

DROP TABLE IF EXISTS `HEDWIGRUNNER_LIST`;
CREATE TABLE `HEDWIGRUNNER_LIST` (
  `HEDWIGRUNNER_ID` int(10) unsigned NOT NULL auto_increment,
  `HEDWIGRUNNER_LEG` int(10) unsigned NOT NULL default '0',
  `HEDWIGRUNNER_USER` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`HEDWIGRUNNER_ID`),
  KEY `HEDWIGRUNNER_HEDWIG` (`HEDWIGRUNNER_LEG`,`HEDWIGRUNNER_USER`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `HEDWIGVOL_LIST`
#

DROP TABLE IF EXISTS `HEDWIGVOL_LIST`;
CREATE TABLE `HEDWIGVOL_LIST` (
  `HEDWIGVOL_ID` int(10) unsigned NOT NULL auto_increment,
  `HEDWIGVOL_USER` int(10) unsigned NOT NULL default '0',
  `HEDWIGVOL_HEDWIG` int(10) unsigned NOT NULL default '0',
  `HEDWIGVOL_TYPE` enum('Runner','Cyclist','Driver','Dispatcher') NOT NULL default 'Runner',
  PRIMARY KEY  (`HEDWIGVOL_ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `HEDWIG_LIST`
#

DROP TABLE IF EXISTS `HEDWIG_LIST`;
CREATE TABLE `HEDWIG_LIST` (
  `HEDWIG_ID` int(10) unsigned NOT NULL auto_increment,
  `HEDWIG_NAME` varchar(20) NOT NULL default '',
  `HEDWIG_START` datetime NOT NULL default '0000-00-00 00:00:00',
  `HEDWIG_SIGNUP` int(10) unsigned default NULL,
  `HEDWIG_ACTIVE` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`HEDWIG_ID`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

#
# Table structure for table `IMSERVICE_LIST`
#

DROP TABLE IF EXISTS `IMSERVICE_LIST`;
CREATE TABLE `IMSERVICE_LIST` (
  `IMSERVICE_ID` int(10) unsigned NOT NULL auto_increment,
  `IMSERVICE_NAME` varchar(50) NOT NULL default '',
  `IMSERVICE_LINKER` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`IMSERVICE_ID`)
) TYPE=MyISAM AUTO_INCREMENT=5 ;

#
# Dumping data for table `IMSERVICE_LIST`
#

INSERT DELAYED INTO `IMSERVICE_LIST` (`IMSERVICE_ID`, `IMSERVICE_NAME`, `IMSERVICE_LINKER`) VALUES (1, 'MSNM', ''),
(2, 'AIM', 'aim:goim?screenname=%1'),
(3, 'ICQ', 'http://wwp.icq.com/scripts/search.dll?to=%1'),
(4, 'Yahoo! Messenger', 'ymsgr:sendIM?%1');

# --------------------------------------------------------

#
# Table structure for table `IM_LIST`
#

DROP TABLE IF EXISTS `IM_LIST`;
CREATE TABLE `IM_LIST` (
  `IM_ID` int(10) unsigned NOT NULL auto_increment,
  `IM_USER` int(10) unsigned NOT NULL default '0',
  `IM_SERVICE` int(10) unsigned NOT NULL default '0',
  `IM_STRING` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`IM_ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `LAYERCAT_LIST`
#

DROP TABLE IF EXISTS `LAYERCAT_LIST`;
CREATE TABLE `LAYERCAT_LIST` (
  `LAYERCAT_ID` int(10) unsigned NOT NULL auto_increment,
  `LAYERCAT_LAYER` int(10) unsigned NOT NULL default '0',
  `LAYERCAT_CATEGORY` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`LAYERCAT_ID`),
  KEY `LAYERCAT_LAYER` (`LAYERCAT_LAYER`,`LAYERCAT_CATEGORY`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `LAYERUSER_LIST`
#

DROP TABLE IF EXISTS `LAYERUSER_LIST`;
CREATE TABLE `LAYERUSER_LIST` (
  `LAYERUSER_ID` int(10) unsigned NOT NULL auto_increment,
  `LAYERUSER_LAYER` int(10) unsigned NOT NULL default '0',
  `LAYERUSER_USER` int(10) unsigned NOT NULL default '0',
  `LAYERUSER_ACCESS` tinyint(3) unsigned NOT NULL default '0',
  `LAYERUSER_DISPLAY` tinyint(3) unsigned NOT NULL default '1',
  `LAYERUSER_COLOR` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`LAYERUSER_ID`),
  UNIQUE KEY `LAYERUSER_LAYER` (`LAYERUSER_LAYER`,`LAYERUSER_USER`),
  KEY `LAYERUSER_USER` (`LAYERUSER_USER`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `LAYER_LIST`
#

DROP TABLE IF EXISTS `LAYER_LIST`;
CREATE TABLE `LAYER_LIST` (
  `LAYER_ID` int(10) unsigned NOT NULL auto_increment,
  `LAYER_TITLE` varchar(80) NOT NULL default '',
  `LAYER_DESC` varchar(200) NOT NULL default '',
  `LAYER_OPEN` tinyint(3) unsigned NOT NULL default '1',
  `LAYER_SHOWDEFAULT` tinyint(3) unsigned NOT NULL default '0',
  `LAYER_CLASS` int(10) unsigned default NULL,
  `LAYER_TEACHER` int(10) unsigned default NULL,
  `LAYER_PERSONAL` int(10) unsigned default NULL,
  `LAYER_DISPLAY` tinyint(3) unsigned NOT NULL default '1',
  `LAYER_LASTMODIFIED` datetime NOT NULL default '0000-00-00 00:00:00',
  `LAYER_USED` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`LAYER_ID`),
  UNIQUE KEY `LAYER_PERSONAL` (`LAYER_PERSONAL`),
  UNIQUE KEY `LAYER_CLASS` (`LAYER_CLASS`,`LAYER_TEACHER`),
  KEY `LAYER_LASTMODIFIED` (`LAYER_LASTMODIFIED`),
  FULLTEXT KEY `LAYER_TITLE` (`LAYER_TITLE`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Table structure for table `LOG_LIST`
#

DROP TABLE IF EXISTS `LOG_LIST`;
CREATE TABLE `LOG_LIST` (
  `LOG_ID` int(10) unsigned NOT NULL auto_increment,
  `LOG_MO` tinyint(3) unsigned NOT NULL default '0',
  `LOG_YR` smallint(5) unsigned NOT NULL default '0',
  `LOG_USER` int(10) unsigned default NULL,
  `LOG_TS` datetime NOT NULL default '0000-00-00 00:00:00',
  `LOG_PATH` varchar(127) NOT NULL default '',
  `LOG_QUERY` varchar(127) NOT NULL default '',
  `LOG_BROWSER` varchar(255) NOT NULL default '',
  `LOG_IP` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`LOG_ID`),
  KEY `UserTime` (`LOG_USER`,`LOG_TS`),
  KEY `YrMo` (`LOG_YR`,`LOG_MO`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;
