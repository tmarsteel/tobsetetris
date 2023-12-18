CREATE DATABASE tetris;
CREATE USER IF NOT EXISTS tetris IDENTIFIED BY 'tetris';

CREATE TABLE `tetris`.`highscore` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(12) NOT NULL,
  `score` int(10) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bricksequence` int(8) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `score` (`score`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

GRANT SELECT,INSERT ON `tetris`.`highscore` TO tetris;
