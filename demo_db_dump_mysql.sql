--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Tablestructure for Table `test_1`
--

CREATE TABLE IF NOT EXISTS `test_1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foo` varchar(200) COLLATE utf8_bin NOT NULL,
  `bar` int(11) NOT NULL,
  `some_bool` tinyint(1) NOT NULL,
  `dropdown` varchar(80) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;