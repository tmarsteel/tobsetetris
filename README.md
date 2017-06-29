# TTetris
An HTML5 Tetris game with online scoreboard and cheating protection.

## Server requirements

* MySQL Server 5.6
* PHP 5.6
  * Session support
  * mysql extension
* Apache Webserver
  * `AllowOverride All`
  * You can use any other but you will have to port the `.htaccess` files.


## How to set up

1. Checkout this repo into a directory (say `/var/www/tetris` for the purpose of this
readme) and point an Apache VHost either to `/var/www/tetetris` or `/var/www/ttetris/public`.
2. Setup a database user for the app
3. Log into the MySQL server, create a schema and grant SELECT, INSERT, UPDATE, DELETE to the
   user you just created
4. Create the highscore table with this statement:

       CREATE TABLE `highscore` (
         `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
         `name` varchar(12) NOT NULL,
         `score` int(10) NOT NULL,
         `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
         `bricksequence` int(8) NOT NULL,
         PRIMARY KEY (`id`),
         KEY `score` (`score`)
       ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;
       
3. Put the database credentials and schema name into `src/config.inc.php`