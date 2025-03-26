-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 27, 2025 at 01:13 AM
SET
    SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

SET
    time_zone = "+00:00";

--
-- Database: `task_manager`
--
-- --------------------------------------------------------
--
-- Table structure for table `tasks`
--
CREATE TABLE `tasks` (
    `id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `description` text,
    `due_date` date DEFAULT NULL,
    `status` tinyint(1) DEFAULT '0'
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------
--
-- Table structure for table `users`
--
CREATE TABLE `users` (
    `id` int(11) NOT NULL,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

--
-- Dumping data for table `users`
--
INSERT INTO
    `users` (`id`, `username`, `password`)
VALUES
    (
        1,
        'admin',
        '$2y$10$KObJLiNK3HQmHYqN4.b8I.wEKaP130ELLBs5LRXF2tTscZhLglsFO'
    );

--
-- Indexes for dumped tables
--
--
-- Indexes for table `tasks`
--
ALTER TABLE
    `tasks`
ADD
    PRIMARY KEY (`id`),
ADD
    KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE
    `users`
ADD
    PRIMARY KEY (`id`),
ADD
    UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--
--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE
    `tasks`
MODIFY
    `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE
    `users`
MODIFY
    `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 2;

--
-- Constraints for dumped tables
--
--
-- Constraints for table `tasks`
--
ALTER TABLE
    `tasks`
ADD
    CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;