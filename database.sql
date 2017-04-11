-- Generation Time: Apr 11, 2017 at 02:30 PM
-- Server version: 5.6.34-log
-- PHP Version: 7.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fbimport`
--

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `name` varchar(256) NOT NULL,
  `fbname` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `fbname` varchar(256) CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `postid` varchar(256) NOT NULL,
  `type` varchar(256) NOT NULL,
  `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `length` int(11) NOT NULL,
  `picture` mediumtext CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `link` mediumtext CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `message` mediumtext CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `pagepost` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `poststats`
--

CREATE TABLE `poststats` (
  `postid` varchar(256) NOT NULL,
  `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shares` int(11) NOT NULL,
  `comments` int(11) NOT NULL,
  `likes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD UNIQUE KEY `fbname_2` (`fbname`),

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD UNIQUE KEY `postid` (`postid`),
  ADD KEY `fbname` (`fbname`(255)),
  ADD KEY `fbname_2` (`fbname`(255)),
  ADD KEY `type` (`type`),
  ADD KEY `pagepost` (`pagepost`),
  ADD KEY `length` (`length`,`pagepost`),
  ADD KEY `time` (`time`);

--
-- Indexes for table `poststats`
--
ALTER TABLE `poststats`
  ADD KEY `postid` (`postid`),
  ADD KEY `time` (`time`,`shares`,`comments`,`likes`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
