SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

CREATE TABLE `server` (
    ip varchar(255) NOT NULL PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `nurse` (
    id int UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    tel_nr varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `room` (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `toilet` (
    id varchar(255) NOT NULL PRIMARY KEY,
    room_id INT UNSIGNED NOT NULL,
    username varchar(255) NOT NULL,
    password varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `bed` (
    id varchar(255) NOT NULL PRIMARY KEY,
    room_id INT UNSIGNED NOT NULL,
    username varchar(255) NOT NULL,
    password varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `log` (
    id int UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    category_log_id INT UNSIGNED NOT NULL,
    `value` text,
    `timestamp` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `category_log` (
    id int UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `history` (
   id int UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
   `bed_id` varchar(255) NOT NULL,
   `category_history_id int NOT NULL,
   `duration` varchar(255),
   `timestamp` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `category_history` (
   id int UNSIGNED NOT NULL PRIMARY KEY,
   name varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;  


INSERT INTO `server` VALUES ('192.168.0.1');

INSERT INTO `category_log` VALUES 
(1, 'EMERGENCY'),
(2, 'INFUS'),
(3, 'CODE BLUE');

INSERT INTO `category_history` VALUES 
(1, 'PANGGILAN MASUK'),
(2, 'PANGGILAN TIDAK TERJAWAB');


INSERT INTO room VALUES 
(1, 'ROOM 1');

INSERT INTO bed VALUES
('010101', 1, 'bed11', '1234');

INSERT INTO toilet VALUES
('020101', 1, 'toilet11', '1234');

COMMIT;
