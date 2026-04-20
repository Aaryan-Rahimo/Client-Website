-- Author: Angad
-- Date Created: 2026-04-19
-- Description: Users Table for the clinic database.

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') DEFAULT 'client',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES (1,'Dr. Ruby Suresh','ruby@clinic.com','905-000-0000','$2y$10$AUOwVPPFtSdMlfOVpWdezeNxmQNg0XrOYmA2utqryNKTQm6HKR0Z2','admin','2026-04-06 08:44:55');
UNLOCK TABLES;

