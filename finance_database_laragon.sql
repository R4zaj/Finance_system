-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------
-- Laragon-compatible export
-- Tables reordered to satisfy foreign key dependencies.
-- users table: added AUTO_INCREMENT and PRIMARY KEY clause (was missing).
-- All other tables, columns, constraints, and data are unchanged.
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for finance_system
CREATE DATABASE IF NOT EXISTS `finance_system` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `finance_system`;

-- --------------------------------------------------------
-- Independent tables (no foreign key dependencies)
-- --------------------------------------------------------

-- Dumping structure for table finance_system.accounts
CREATE TABLE IF NOT EXISTS `accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('Asset','Liability','Revenue','Expense') NOT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.accounts: ~9 rows (approximately)
INSERT INTO `accounts` (`account_id`, `name`, `type`) VALUES
	(1, 'Cash', 'Asset'),
	(2, 'Accounts Receivable', 'Asset'),
	(3, 'Equipment', 'Asset'),
	(4, 'Accounts Payable', 'Liability'),
	(5, 'Student Fees Revenue', 'Revenue'),
	(6, 'Tuition Revenue', 'Revenue'),
	(7, 'Salary Expense', 'Expense'),
	(8, 'Utilities Expense', 'Expense'),
	(9, 'Supplies Expense', 'Expense');

-- Dumping structure for table finance_system.departments
CREATE TABLE IF NOT EXISTS `departments` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.departments: ~5 rows (approximately)
INSERT INTO `departments` (`department_id`, `name`) VALUES
	(1, 'Computer Science'),
	(2, 'Mathematics'),
	(3, 'Business Administration'),
	(4, 'Engineering'),
	(5, 'Human Resources');

-- Dumping structure for table finance_system.positions
CREATE TABLE IF NOT EXISTS `positions` (
  `position_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`position_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.positions: ~5 rows (approximately)
INSERT INTO `positions` (`position_id`, `title`, `description`) VALUES
	(1, 'Professor', 'Senior academic staff'),
	(2, 'Associate Professor', 'Mid-level academic staff'),
	(3, 'Assistant Professor', 'Junior academic staff'),
	(4, 'Administrative Staff', 'Office administration'),
	(5, 'Department Head', 'Department leadership');

-- Dumping structure for table finance_system.courses
CREATE TABLE IF NOT EXISTS `courses` (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `credits` int(11) DEFAULT NULL,
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `course_code` (`course_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.courses: ~5 rows (approximately)
INSERT INTO `courses` (`course_id`, `course_code`, `name`, `description`, `credits`) VALUES
	(1, 'CS101', 'Introduction to Programming', 'Basic programming concepts', 3),
	(2, 'MATH201', 'Calculus II', 'Advanced calculus topics', 4),
	(3, 'BUS301', 'Financial Accounting', 'Principles of accounting', 3),
	(4, 'ENG101', 'Engineering Fundamentals', 'Introduction to engineering', 4),
	(5, 'HR401', 'Organizational Behavior', 'Study of workplace behavior', 3);

-- Dumping structure for table finance_system.suppliers
CREATE TABLE IF NOT EXISTS `suppliers` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.suppliers: ~5 rows (approximately)
INSERT INTO `suppliers` (`supplier_id`, `name`, `contact_person`, `phone`, `email`, `address`) VALUES
	(1, 'Office Supplies Co.', 'Jane Doe', '555-0101', 'jane@officesupplies.com', '123 Main St, City'),
	(2, 'Tech Equipment Ltd.', 'Tom Harris', '555-0102', 'tom@techequip.com', '456 Oak Ave, City'),
	(3, 'Book Distributors Inc.', 'Mary Lee', '555-0103', 'mary@bookdist.com', '789 Pine Rd, City'),
	(4, 'Lab Supplies Corp.', 'Bill Gates', '555-0104', 'bill@labsupplies.com', '321 Elm St, City'),
	(5, 'Furniture Mart', 'Susan Clark', '555-0105', 'susan@furnituremart.com', '654 Maple Dr, City');

-- Dumping structure for table finance_system.students
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('M','F','Other') DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.students: ~5 rows (approximately)
INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `dob`, `gender`, `enrollment_date`, `email`) VALUES
	(1, 'Alice', 'Anderson', '2000-05-10', 'F', '2022-09-01', 'alice.anderson@email.com'),
	(2, 'Bob', 'Taylor', '1999-08-15', 'M', '2021-09-01', 'bob.taylor@email.com'),
	(3, 'Carol', 'Thomas', '2001-02-20', 'F', '2023-09-01', 'carol.thomas@email.com'),
	(4, 'David', 'Jackson', '2000-11-30', 'M', '2022-09-01', 'david.jackson@email.com'),
	(5, 'Eva', 'White', '2001-07-25', 'F', '2023-09-01', 'eva.white@email.com');

-- Dumping structure for table finance_system.resources
CREATE TABLE IF NOT EXISTS `resources` (
  `resource_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.resources: ~5 rows (approximately)
INSERT INTO `resources` (`resource_id`, `name`, `type`, `description`) VALUES
	(1, 'Projector', 'Equipment', 'HD multimedia projector'),
	(2, 'Laptop Cart', 'Equipment', 'Mobile laptop charging station'),
	(3, 'Conference Room A', 'Room', 'Meeting room for 10 people'),
	(4, '3D Printer', 'Equipment', 'Industrial 3D printer'),
	(5, 'Video Camera', 'Equipment', '4K video recording device');

-- Dumping structure for table finance_system.rooms
CREATE TABLE IF NOT EXISTS `rooms` (
  `room_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  PRIMARY KEY (`room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.rooms: ~5 rows (approximately)
INSERT INTO `rooms` (`room_id`, `name`, `location`, `capacity`) VALUES
	(1, 'Room 101', 'Building A', 40),
	(2, 'Room 202', 'Building B', 30),
	(3, 'Lab 1', 'Science Building', 25),
	(4, 'Lecture Hall 1', 'Main Building', 100),
	(5, 'Seminar Room', 'Building C', 20);

-- Dumping structure for table finance_system.surveys
CREATE TABLE IF NOT EXISTS `surveys` (
  `survey_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  PRIMARY KEY (`survey_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.surveys: ~5 rows (approximately)
INSERT INTO `surveys` (`survey_id`, `title`, `description`, `created_date`) VALUES
	(1, 'Course Evaluation', 'End of semester course evaluation', '2024-01-10'),
	(2, 'Campus Facilities', 'Assessment of campus facilities', '2024-02-15'),
	(3, 'Student Services', 'Evaluation of student services', '2024-03-20'),
	(4, 'Faculty Performance', 'Annual faculty performance survey', '2024-04-05'),
	(5, 'IT Services', 'IT infrastructure satisfaction', '2024-05-12');

-- Dumping structure for table finance_system.eval_criteria
CREATE TABLE IF NOT EXISTS `eval_criteria` (
  `criterion_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`criterion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.eval_criteria: ~5 rows (approximately)
INSERT INTO `eval_criteria` (`criterion_id`, `description`) VALUES
	(1, 'Teaching Effectiveness'),
	(2, 'Research Productivity'),
	(3, 'Service to Department'),
	(4, 'Student Mentoring'),
	(5, 'Professional Development');

-- Dumping structure for table finance_system.qa_indicators
CREATE TABLE IF NOT EXISTS `qa_indicators` (
  `indicator_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `target_value` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`indicator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.qa_indicators: ~5 rows (approximately)
INSERT INTO `qa_indicators` (`indicator_id`, `name`, `description`, `target_value`) VALUES
	(1, 'Student Satisfaction', 'Overall student satisfaction rate', 85.00),
	(2, 'Graduation Rate', '4-year graduation rate', 75.00),
	(3, 'Employment Rate', 'Graduate employment within 6 months', 90.00),
	(4, 'Faculty Qualification', 'Percentage of faculty with PhD', 80.00),
	(5, 'Research Output', 'Publications per faculty per year', 3.00);

-- --------------------------------------------------------
-- Tables with one level of dependencies
-- --------------------------------------------------------

-- Dumping structure for table finance_system.employees
-- (depends on: departments, positions)
CREATE TABLE IF NOT EXISTS `employees` (
  `employee_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('M','F','Other') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `is_faculty` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`employee_id`),
  KEY `fk_emp_department` (`department_id`),
  KEY `fk_emp_position` (`position_id`),
  CONSTRAINT `fk_emp_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  CONSTRAINT `fk_emp_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.employees: ~5 rows (approximately)
INSERT INTO `employees` (`employee_id`, `department_id`, `position_id`, `first_name`, `last_name`, `dob`, `gender`, `email`, `hire_date`, `salary`, `is_faculty`) VALUES
	(1, 1, 1, 'John', 'Smith', '1975-03-15', 'M', 'john.smith@email.com', '2010-08-15', 85000.00, 1),
	(2, 2, 2, 'Sarah', 'Johnson', '1980-07-22', 'F', 'sarah.johnson@email.com', '2012-01-10', 75000.00, 1),
	(3, 3, 4, 'Michael', 'Brown', '1985-11-30', 'M', 'michael.brown@email.com', '2015-06-20', 45000.00, 0),
	(4, 4, 3, 'Emily', 'Davis', '1988-04-18', 'F', 'emily.davis@email.com', '2014-09-01', 65000.00, 1),
	(5, 5, 5, 'Robert', 'Wilson', '1970-09-05', 'M', 'robert.wilson@email.com', '2008-03-12', 95000.00, 1);

-- Dumping structure for table finance_system.budgets
-- (depends on: departments)
CREATE TABLE IF NOT EXISTS `budgets` (
  `budget_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  `allocated_amount` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`budget_id`),
  KEY `fk_budget_dept` (`department_id`),
  CONSTRAINT `fk_budget_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.budgets: ~5 rows (approximately)
INSERT INTO `budgets` (`budget_id`, `department_id`, `year`, `allocated_amount`) VALUES
	(1, 1, '2024', 100000.00),
	(2, 2, '2024', 80000.00),
	(3, 3, '2024', 120000.00),
	(4, 4, '2024', 150000.00),
	(5, 5, '2024', 90000.00);

-- Dumping structure for table finance_system.transactions
-- (depends on: accounts)
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `trans_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `type` enum('Debit','Credit') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `fk_trans_account` (`account_id`),
  CONSTRAINT `fk_trans_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.transactions: ~16 rows (approximately)
INSERT INTO `transactions` (`transaction_id`, `account_id`, `trans_date`, `amount`, `type`, `description`) VALUES
	(1, 1, '2024-01-15', 50000.00, 'Debit', 'Initial cash deposit'),
	(2, 5, '2024-01-20', 25000.00, 'Credit', 'Student fees collected'),
	(3, 7, '2024-01-31', 15000.00, 'Debit', 'January salaries'),
	(4, 1, '2024-02-01', 30000.00, 'Credit', 'Cash withdrawal'),
	(5, 8, '2024-02-15', 5000.00, 'Debit', 'Utility bill payment'),
	(6, 6, '2024-03-01', 40000.00, 'Credit', 'Tuition collection'),
	(7, 9, '2024-03-15', 3000.00, 'Debit', 'Office supplies purchase'),
	(8, 1, '2024-04-01', 20000.00, 'Debit', 'Cash receipt'),
	(9, 5, '2024-04-15', 15000.00, 'Credit', 'Additional fees'),
	(10, 7, '2024-04-30', 18000.00, 'Debit', 'April salaries'),
	(11, 1, '2024-05-01', 35000.00, 'Credit', 'Cash withdrawal'),
	(12, 8, '2024-05-15', 4500.00, 'Debit', 'May utilities'),
	(13, 6, '2024-06-01', 45000.00, 'Credit', 'Summer tuition'),
	(14, 9, '2024-06-15', 2500.00, 'Debit', 'Supply restock'),
	(15, 7, '2024-06-30', 20000.00, 'Debit', 'June salaries'),
	(16, 1, '2026-05-01', 21000.00, 'Debit', 'Test');

-- Dumping structure for table finance_system.inventory_items
-- (depends on: suppliers)
CREATE TABLE IF NOT EXISTS `inventory_items` (
  `inventory_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `reorder_level` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`inventory_id`),
  KEY `fk_item_supplier` (`supplier_id`),
  CONSTRAINT `fk_item_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.inventory_items: ~5 rows (approximately)
INSERT INTO `inventory_items` (`inventory_id`, `name`, `description`, `quantity`, `unit_price`, `reorder_level`, `supplier_id`) VALUES
	(1, 'Printer Paper', 'A4 size white paper', 500, 5.00, 100, 1),
	(2, 'Laptop', 'Business laptop 15"', 50, 800.00, 10, 2),
	(3, 'Textbook', 'Introduction to Programming', 200, 75.00, 50, 3),
	(4, 'Lab Equipment Set', 'Basic chemistry lab set', 30, 250.00, 10, 4),
	(5, 'Office Chair', 'Ergonomic office chair', 100, 150.00, 20, 5);

-- Dumping structure for table finance_system.purchase_orders
-- (depends on: suppliers)
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `po_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('Draft','Ordered','Received','Cancelled') DEFAULT 'Draft',
  PRIMARY KEY (`po_id`),
  KEY `fk_po_supplier` (`supplier_id`),
  CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.purchase_orders: ~5 rows (approximately)
INSERT INTO `purchase_orders` (`po_id`, `supplier_id`, `order_date`, `total_amount`, `status`) VALUES
	(1, 1, '2024-01-10', 2500.00, 'Received'),
	(2, 2, '2024-02-15', 8000.00, 'Received'),
	(3, 3, '2024-03-20', 3750.00, 'Received'),
	(4, 4, '2024-04-05', 5000.00, 'Ordered'),
	(5, 5, '2024-05-12', 3000.00, 'Cancelled');

-- Dumping structure for table finance_system.student_payments
-- (depends on: students)
CREATE TABLE IF NOT EXISTS `student_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `pay_date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `fk_spay_student` (`student_id`),
  CONSTRAINT `fk_spay_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.student_payments: ~6 rows (approximately)
INSERT INTO `student_payments` (`payment_id`, `student_id`, `amount`, `pay_date`, `description`) VALUES
	(1, 1, 5000.00, '2024-01-15', 'Spring semester tuition'),
	(2, 2, 4500.00, '2024-01-20', 'Spring semester tuition'),
	(3, 3, 5500.00, '2024-02-01', 'Spring semester tuition'),
	(4, 4, 4800.00, '2024-02-15', 'Spring semester tuition'),
	(5, 5, 5200.00, '2024-03-01', 'Spring semester tuition'),
	(6, 1, 20000.00, '2026-05-11', '1st Sem');

-- Dumping structure for table finance_system.survey_responses
-- (depends on: surveys)
CREATE TABLE IF NOT EXISTS `survey_responses` (
  `response_id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_id` int(11) NOT NULL,
  `respondent_role` varchar(50) DEFAULT NULL,
  `question` varchar(255) DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `response_date` date DEFAULT NULL,
  PRIMARY KEY (`response_id`),
  KEY `fk_survey` (`survey_id`),
  CONSTRAINT `fk_survey` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`survey_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.survey_responses: ~5 rows (approximately)
INSERT INTO `survey_responses` (`response_id`, `survey_id`, `respondent_role`, `question`, `answer`, `rating`, `response_date`) VALUES
	(1, 1, 'Student', 'How would you rate the course?', 'Very informative', 4, '2024-01-20'),
	(2, 2, 'Student', 'Are facilities adequate?', 'Yes, well maintained', 5, '2024-02-25'),
	(3, 3, 'Staff', 'Rate student services', 'Good overall', 4, '2024-03-30'),
	(4, 4, 'Student', 'Rate faculty teaching', 'Excellent', 5, '2024-04-15'),
	(5, 5, 'Faculty', 'Rate IT support', 'Needs improvement', 2, '2024-05-20');

-- Dumping structure for table finance_system.qa_records
-- (depends on: qa_indicators)
CREATE TABLE IF NOT EXISTS `qa_records` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `indicator_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  `actual_value` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`record_id`),
  KEY `fk_qa_indicator` (`indicator_id`),
  CONSTRAINT `fk_qa_indicator` FOREIGN KEY (`indicator_id`) REFERENCES `qa_indicators` (`indicator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.qa_records: ~5 rows (approximately)
INSERT INTO `qa_records` (`record_id`, `indicator_id`, `year`, `actual_value`) VALUES
	(1, 1, '2023', 82.50),
	(2, 2, '2023', 73.00),
	(3, 3, '2023', 88.00),
	(4, 4, '2023', 78.00),
	(5, 5, '2023', 2.50);

-- --------------------------------------------------------
-- Tables with two levels of dependencies
-- --------------------------------------------------------

-- Dumping structure for table finance_system.classes
-- (depends on: courses, employees)
CREATE TABLE IF NOT EXISTS `classes` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  `max_enrollment` int(11) DEFAULT NULL,
  PRIMARY KEY (`class_id`),
  KEY `fk_class_course` (`course_id`),
  KEY `fk_class_instructor` (`instructor_id`),
  CONSTRAINT `fk_class_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  CONSTRAINT `fk_class_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.classes: ~5 rows (approximately)
INSERT INTO `classes` (`class_id`, `course_id`, `instructor_id`, `semester`, `year`, `max_enrollment`) VALUES
	(1, 1, 1, 'Fall', '2024', 30),
	(2, 2, 2, 'Fall', '2024', 25),
	(3, 3, 3, 'Spring', '2024', 35),
	(4, 4, 4, 'Fall', '2024', 20),
	(5, 5, 5, 'Spring', '2024', 30);

-- Dumping structure for table finance_system.employee_leave
-- (depends on: employees)
CREATE TABLE IF NOT EXISTS `employee_leave` (
  `leave_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Approved','Denied') DEFAULT 'Pending',
  PRIMARY KEY (`leave_id`),
  KEY `fk_leave_employee` (`employee_id`),
  CONSTRAINT `fk_leave_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.employee_leave: ~5 rows (approximately)
INSERT INTO `employee_leave` (`leave_id`, `employee_id`, `start_date`, `end_date`, `leave_type`, `status`) VALUES
	(1, 1, '2024-01-15', '2024-01-20', 'Vacation', 'Approved'),
	(2, 2, '2024-02-01', '2024-02-05', 'Sick', 'Approved'),
	(3, 3, '2024-03-10', '2024-03-15', 'Personal', 'Pending'),
	(4, 4, '2024-04-05', '2024-04-10', 'Vacation', 'Denied'),
	(5, 5, '2024-05-20', '2024-05-25', 'Conference', 'Approved');

-- Dumping structure for table finance_system.employee_payments
-- (depends on: employees)
CREATE TABLE IF NOT EXISTS `employee_payments` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `pay_date` date NOT NULL,
  `gross_amount` decimal(12,2) DEFAULT NULL,
  `deductions` decimal(12,2) DEFAULT NULL,
  `net_amount` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`pay_id`),
  KEY `fk_pay_employee` (`employee_id`),
  CONSTRAINT `fk_pay_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.employee_payments: ~6 rows (approximately)
INSERT INTO `employee_payments` (`pay_id`, `employee_id`, `pay_date`, `gross_amount`, `deductions`, `net_amount`) VALUES
	(1, 1, '2024-01-31', 7083.33, 1500.00, 5583.33),
	(2, 2, '2024-01-31', 6250.00, 1200.00, 5050.00),
	(3, 3, '2024-01-31', 3750.00, 800.00, 2950.00),
	(4, 4, '2024-01-31', 5416.67, 1100.00, 4316.67),
	(5, 5, '2024-01-31', 7916.67, 1800.00, 6116.67),
	(6, 4, '2026-05-08', 2000.00, 300.00, 1700.00);

-- Dumping structure for table finance_system.faculty_evaluations
-- (depends on: employees)
CREATE TABLE IF NOT EXISTS `faculty_evaluations` (
  `eval_id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_id` int(11) NOT NULL,
  `evaluator_id` int(11) NOT NULL,
  `eval_date` date NOT NULL,
  `overall_score` decimal(5,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  PRIMARY KEY (`eval_id`),
  KEY `fk_eval_faculty` (`faculty_id`),
  KEY `fk_eval_evaluator` (`evaluator_id`),
  CONSTRAINT `fk_eval_evaluator` FOREIGN KEY (`evaluator_id`) REFERENCES `employees` (`employee_id`),
  CONSTRAINT `fk_eval_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.faculty_evaluations: ~5 rows (approximately)
INSERT INTO `faculty_evaluations` (`eval_id`, `faculty_id`, `evaluator_id`, `eval_date`, `overall_score`, `comments`) VALUES
	(1, 1, 5, '2024-03-15', 92.50, 'Excellent performance'),
	(2, 2, 5, '2024-03-20', 88.00, 'Very good work'),
	(3, 4, 5, '2024-04-10', 85.50, 'Good overall'),
	(4, 1, 3, '2024-04-15', 90.00, 'Strong teaching skills'),
	(5, 2, 3, '2024-04-20', 87.50, 'Consistent performer');

-- Dumping structure for table finance_system.purchase_items
-- (depends on: purchase_orders, inventory_items)
CREATE TABLE IF NOT EXISTS `purchase_items` (
  `po_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`po_item_id`),
  KEY `fk_pi_po` (`po_id`),
  KEY `fk_pi_item` (`inventory_id`),
  CONSTRAINT `fk_pi_item` FOREIGN KEY (`inventory_id`) REFERENCES `inventory_items` (`inventory_id`),
  CONSTRAINT `fk_pi_po` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.purchase_items: ~5 rows (approximately)
INSERT INTO `purchase_items` (`po_item_id`, `po_id`, `inventory_id`, `quantity`, `unit_price`) VALUES
	(1, 1, 1, 500, 5.00),
	(2, 2, 2, 10, 800.00),
	(3, 3, 3, 50, 75.00),
	(4, 4, 4, 20, 250.00),
	(5, 5, 5, 20, 150.00);

-- Dumping structure for table finance_system.vendor_payments
-- (depends on: suppliers, purchase_orders)
CREATE TABLE IF NOT EXISTS `vendor_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `pay_date` date NOT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `fk_vpay_supplier` (`supplier_id`),
  KEY `fk_vpay_po` (`po_id`),
  CONSTRAINT `fk_vpay_po` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`),
  CONSTRAINT `fk_vpay_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.vendor_payments: ~7 rows (approximately)
INSERT INTO `vendor_payments` (`payment_id`, `supplier_id`, `po_id`, `pay_date`, `amount`) VALUES
	(1, 1, 1, '2024-02-01', 2500.00),
	(2, 2, 2, '2024-03-01', 8000.00),
	(3, 3, 3, '2024-04-01', 3750.00),
	(4, 4, 4, '2024-05-01', 5000.00),
	(5, 5, NULL, '2024-06-08', 3000.00),
	(8, 3, 3, '2026-04-27', 3750.00),
	(9, 1, NULL, '2026-05-08', 30000.00);

-- Dumping structure for table finance_system.reservations
-- (depends on: resources, employees, students)
CREATE TABLE IF NOT EXISTS `reservations` (
  `reservation_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) NOT NULL,
  `reserved_by_employee` int(11) DEFAULT NULL,
  `reserved_by_student` int(11) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
  PRIMARY KEY (`reservation_id`),
  KEY `fk_res_resource` (`resource_id`),
  KEY `fk_res_emp` (`reserved_by_employee`),
  KEY `fk_res_student` (`reserved_by_student`),
  CONSTRAINT `fk_res_emp` FOREIGN KEY (`reserved_by_employee`) REFERENCES `employees` (`employee_id`),
  CONSTRAINT `fk_res_resource` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`),
  CONSTRAINT `fk_res_student` FOREIGN KEY (`reserved_by_student`) REFERENCES `students` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.reservations: ~5 rows (approximately)
INSERT INTO `reservations` (`reservation_id`, `resource_id`, `reserved_by_employee`, `reserved_by_student`, `start_time`, `end_time`, `purpose`, `status`) VALUES
	(1, 1, 1, NULL, '2024-02-01 09:00:00', '2024-02-01 11:00:00', 'Class presentation', 'Confirmed'),
	(2, 2, NULL, 1, '2024-02-02 14:00:00', '2024-02-02 16:00:00', 'Group project', 'Pending'),
	(3, 3, 2, NULL, '2024-02-03 10:00:00', '2024-02-03 12:00:00', 'Department meeting', 'Confirmed'),
	(4, 4, 4, NULL, '2024-02-04 13:00:00', '2024-02-04 15:00:00', 'Research project', 'Cancelled'),
	(5, 5, NULL, 3, '2024-02-05 09:00:00', '2024-02-05 11:00:00', 'Video recording', 'Confirmed');

-- --------------------------------------------------------
-- Tables with three levels of dependencies
-- --------------------------------------------------------

-- Dumping structure for table finance_system.assignments
-- (depends on: classes)
CREATE TABLE IF NOT EXISTS `assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `fk_assign_class` (`class_id`),
  CONSTRAINT `fk_assign_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.assignments: ~5 rows (approximately)
INSERT INTO `assignments` (`assignment_id`, `class_id`, `title`, `description`, `due_date`) VALUES
	(1, 1, 'Programming Assignment 1', 'Basic calculator program', '2024-02-15'),
	(2, 2, 'Calculus Problem Set', 'Integration problems', '2024-02-20'),
	(3, 3, 'Financial Statement Analysis', 'Analyze company financials', '2024-03-01'),
	(4, 4, 'Engineering Design Project', 'Bridge design challenge', '2024-03-15'),
	(5, 5, 'Case Study Analysis', 'Organizational behavior case', '2024-02-28');

-- Dumping structure for table finance_system.enrollments
-- (depends on: classes, students)
CREATE TABLE IF NOT EXISTS `enrollments` (
  `enrollment_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `enroll_date` date DEFAULT NULL,
  PRIMARY KEY (`enrollment_id`),
  KEY `fk_enroll_class` (`class_id`),
  KEY `fk_enroll_student` (`student_id`),
  CONSTRAINT `fk_enroll_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  CONSTRAINT `fk_enroll_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.enrollments: ~5 rows (approximately)
INSERT INTO `enrollments` (`enrollment_id`, `class_id`, `student_id`, `grade`, `enroll_date`) VALUES
	(1, 1, 1, 'A', '2024-01-15'),
	(2, 2, 2, 'B+', '2024-01-15'),
	(3, 3, 3, 'A-', '2024-01-16'),
	(4, 4, 4, 'B', '2024-01-16'),
	(5, 5, 5, 'A+', '2024-01-17');

-- Dumping structure for table finance_system.schedule
-- (depends on: classes, rooms)
CREATE TABLE IF NOT EXISTS `schedule` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `fk_sched_class` (`class_id`),
  KEY `fk_sched_room` (`room_id`),
  CONSTRAINT `fk_sched_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  CONSTRAINT `fk_sched_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.schedule: ~5 rows (approximately)
INSERT INTO `schedule` (`schedule_id`, `class_id`, `room_id`, `start_time`, `end_time`) VALUES
	(1, 1, 1, '2024-01-15 09:00:00', '2024-01-15 10:30:00'),
	(2, 2, 2, '2024-01-15 11:00:00', '2024-01-15 12:30:00'),
	(3, 3, 3, '2024-01-16 09:00:00', '2024-01-16 10:30:00'),
	(4, 4, 4, '2024-01-16 13:00:00', '2024-01-16 14:30:00'),
	(5, 5, 5, '2024-01-17 10:00:00', '2024-01-17 11:30:00');

-- Dumping structure for table finance_system.evaluation_details
-- (depends on: faculty_evaluations, eval_criteria)
CREATE TABLE IF NOT EXISTS `evaluation_details` (
  `detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `eval_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`detail_id`),
  KEY `fk_detail_eval` (`eval_id`),
  KEY `fk_detail_criterion` (`criterion_id`),
  CONSTRAINT `fk_detail_criterion` FOREIGN KEY (`criterion_id`) REFERENCES `eval_criteria` (`criterion_id`),
  CONSTRAINT `fk_detail_eval` FOREIGN KEY (`eval_id`) REFERENCES `faculty_evaluations` (`eval_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.evaluation_details: ~5 rows (approximately)
INSERT INTO `evaluation_details` (`detail_id`, `eval_id`, `criterion_id`, `score`, `remarks`) VALUES
	(1, 1, 1, 95, 'Outstanding teaching'),
	(2, 1, 2, 90, 'Active researcher'),
	(3, 2, 1, 88, 'Good teaching'),
	(4, 2, 3, 85, 'Active in committees'),
	(5, 3, 1, 85, 'Solid teaching performance');

-- --------------------------------------------------------
-- Tables with four levels of dependencies
-- --------------------------------------------------------

-- Dumping structure for table finance_system.submissions
-- (depends on: assignments, students)
CREATE TABLE IF NOT EXISTS `submissions` (
  `submission_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submit_date` datetime DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  PRIMARY KEY (`submission_id`),
  KEY `fk_sub_assign` (`assignment_id`),
  KEY `fk_sub_student` (`student_id`),
  CONSTRAINT `fk_sub_assign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`assignment_id`),
  CONSTRAINT `fk_sub_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.submissions: ~5 rows (approximately)
INSERT INTO `submissions` (`submission_id`, `assignment_id`, `student_id`, `submit_date`, `grade`, `feedback`) VALUES
	(1, 1, 1, '2024-02-14 10:30:00', '95', 'Excellent work'),
	(2, 2, 2, '2024-02-19 14:20:00', '88', 'Good effort'),
	(3, 3, 3, '2024-02-28 09:15:00', '92', 'Well done'),
	(4, 4, 4, '2024-03-14 16:45:00', '85', 'Satisfactory'),
	(5, 5, 5, '2024-02-27 11:00:00', '97', 'Outstanding');

-- --------------------------------------------------------
-- users table (fixed: added AUTO_INCREMENT and PRIMARY KEY clause)
-- Original had id int(11) NOT NULL with no PRIMARY KEY defined in the
-- CREATE TABLE body, which causes a MariaDB/MySQL error on import.
-- The column type, name, and all other fields are unchanged.
-- --------------------------------------------------------

-- Dumping structure for table finance_system.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table finance_system.users: ~7 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `role`, `employee_id`, `created_at`) VALUES
	(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, '2025-12-31 16:00:00'),
	(2, 'john.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 1, '2025-12-31 16:00:00'),
	(3, 'sarah.johnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 2, '2025-12-31 16:00:00'),
	(4, 'michael.williams', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 3, '2025-12-31 16:00:00'),
	(5, 'emily.brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 4, '2025-12-31 16:00:00'),
	(6, 'hr_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr', 1, '2025-12-31 16:00:00'),
	(7, 'finance_head', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'finance', 3, '2025-12-31 16:00:00');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
