CREATE TABLE `courses` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `course_code` varchar(10) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `lab` tinyint(1) NOT NULL
);

CREATE TABLE `professors` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `name` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT 'placeholder.png'
);

CREATE TABLE `rooms` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `name` varchar(255) NOT NULL
);

CREATE TABLE `schedules` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `professor_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `day` enum('MWF','TTH','Sat') DEFAULT 'MWF',
  `notes` text DEFAULT NULL,
  `professor_status` enum('Present','Absent', 'On Leave') DEFAULT 'Present'
);