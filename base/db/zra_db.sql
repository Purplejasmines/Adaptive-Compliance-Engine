-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2025 at 05:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zra_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `allocation`
--

CREATE TABLE `allocation` (
  `AllocationID` int(11) NOT NULL,
  `PaymentID` int(11) NOT NULL,
  `ReturnID` int(11) NOT NULL,
  `AllocatedAmount` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auditcases`
--

CREATE TABLE `auditcases` (
  `AuditID` int(11) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `CaseOfficerID` int(11) NOT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `AuditType` enum('Desk Audit','Field Audit','Investigation') NOT NULL,
  `Status` enum('Open','Closed','Appealed') NOT NULL,
  `FindingsSummary` text DEFAULT NULL,
  `RiskLevel` enum('Low','Medium','High') DEFAULT 'Low',
  `RiskScore` int(11) DEFAULT 0,
  `Province` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `biz_assessments`
--

CREATE TABLE `biz_assessments` (
  `AssessmentID` int(11) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `Amount` decimal(12,2) NOT NULL,
  `DueDate` date NOT NULL,
  `Status` enum('Unpaid','Paid','Overdue') DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `biz_businesses`
--

CREATE TABLE `biz_businesses` (
  `BusinessID` int(11) NOT NULL,
  `BusinessName` varchar(255) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `tpin_hash` varchar(255) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `biz_businesses`
--

INSERT INTO `biz_businesses` (`BusinessID`, `BusinessName`, `TPIN`, `Email`, `tpin_hash`, `CreatedAt`) VALUES
(1, 'Pateh Innovations', '0987654321', 'patnovations@work.net', '$2y$10$k7y80Q0PALkZT2BQFO1rp.oCs6eWDsRJLkvt0HHosMlwyPNT/ItNO', '2025-10-22 15:55:46');

-- --------------------------------------------------------

--
-- Table structure for table `biz_employees`
--

CREATE TABLE `biz_employees` (
  `EmployeeID` int(11) NOT NULL,
  `BusinessTPIN` varchar(20) NOT NULL,
  `FullName` varchar(255) NOT NULL,
  `NRC` varchar(20) DEFAULT NULL,
  `Position` varchar(100) DEFAULT NULL,
  `Salary` decimal(12,2) DEFAULT NULL,
  `HireDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `biz_notices`
--

CREATE TABLE `biz_notices` (
  `NoticeID` int(11) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `NoticeType` varchar(100) DEFAULT NULL,
  `Message` text DEFAULT NULL,
  `Status` enum('Active','Resolved') DEFAULT 'Active',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `biz_payments`
--

CREATE TABLE `biz_payments` (
  `PaymentID` int(11) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `PaymentDate` date NOT NULL,
  `AmountPaid` decimal(12,2) NOT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL,
  `Status` enum('Completed','Overdue') DEFAULT 'Completed',
  `AmountDue` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `biz_taxreturns`
--

CREATE TABLE `biz_taxreturns` (
  `ReturnID` int(11) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `TaxYear` year(4) NOT NULL,
  `DueDate` varchar(50) DEFAULT NULL,
  `TaxType` varchar(50) DEFAULT NULL,
  `PeriodLabel` varchar(50) DEFAULT NULL,
  `FilingDate` date DEFAULT NULL,
  `Status` enum('Pending','Filed','Overdue') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `individuals`
--

CREATE TABLE `individuals` (
  `IndividualID` int(11) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `NRC` varchar(20) DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `tpin_hash` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `individuals`
--

INSERT INTO `individuals` (`IndividualID`, `TPIN`, `FirstName`, `LastName`, `email`, `NRC`, `DateOfBirth`, `tpin_hash`) VALUES
(6, '2134567890', 'Pateh', 'Mike', 'jas@try.com', NULL, NULL, ''),
(8, '3214567890', 'Pateh', 'Mike', 'jasper@try.com', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `interactions`
--

CREATE TABLE `interactions` (
  `InteractionID` int(11) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `InteractionTimestamp` datetime NOT NULL,
  `Channel` enum('Portal Message','Phone Call','Email','Walk-in') NOT NULL,
  `Subject` varchar(100) DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `AgentID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PaymentID` int(11) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `PaymentDate` date DEFAULT NULL,
  `AmountPaid` decimal(15,2) DEFAULT NULL,
  `PaymentMethod` varchar(100) NOT NULL,
  `TransactionReference` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalties`
--

CREATE TABLE `penalties` (
  `PenaltyID` int(11) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `ReturnID` int(11) DEFAULT NULL,
  `AuditID` int(11) DEFAULT NULL,
  `PenaltyType` enum('Late Filing','Late Payment','Under-declaration') NOT NULL,
  `Amount` decimal(15,2) NOT NULL,
  `IssueDate` date DEFAULT NULL,
  `Status` enum('Outstanding','Paid','Waived') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reference_taxrates`
--

CREATE TABLE `reference_taxrates` (
  `RateID` int(11) NOT NULL,
  `TaxTypeID` int(11) NOT NULL,
  `RatePercentage` decimal(5,2) DEFAULT NULL,
  `EffectiveStartDate` date DEFAULT NULL,
  `EffectiveEndDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reference_taxtypes`
--

CREATE TABLE `reference_taxtypes` (
  `TaxTypeID` int(11) NOT NULL,
  `TaxName` enum('Value Added Tax','Pay As You Earn','Turnover Tax') NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxpayers`
--

CREATE TABLE `taxpayers` (
  `TPIN` varchar(20) NOT NULL,
  `TaxpayerType` enum('Individual','Business') NOT NULL,
  `RegistrationDate` date NOT NULL,
  `Status` enum('Active','Inactive','Deregistered') NOT NULL,
  `PrimaryEmail` varchar(100) DEFAULT NULL,
  `PrimaryPhone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taxpayers`
--

INSERT INTO `taxpayers` (`TPIN`, `TaxpayerType`, `RegistrationDate`, `Status`, `PrimaryEmail`, `PrimaryPhone`) VALUES
('2134567890', 'Individual', '2025-10-21', 'Active', 'jas@try.com', NULL),
('3214567890', 'Individual', '2025-10-21', 'Active', 'jasper@try.com', NULL),
('jas@try.com', 'Individual', '2025-10-21', 'Active', 'jas@try.com', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `taxreturns`
--

CREATE TABLE `taxreturns` (
  `ReturnID` int(11) NOT NULL,
  `TPIN` varchar(20) NOT NULL,
  `TaxTypeID` int(11) NOT NULL,
  `TaxPeriod` varchar(100) NOT NULL,
  `FilingDate` date DEFAULT NULL,
  `DueDate` date DEFAULT NULL,
  `DeclaredTurnover` decimal(15,2) DEFAULT NULL,
  `CalculatedTax` decimal(15,2) DEFAULT NULL,
  `Status` enum('Filed','Processing','Assessed','Queried') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tax_admins`
--

CREATE TABLE `tax_admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tax_admins`
--

INSERT INTO `tax_admins` (`id`, `full_name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Pategree Mike', 'pategree@taxadmin.com', '$2y$10$XLmxv9CM2NqoYjZo8YSUOelIwp5JDuy7F9HdXEtwJtmiLddHlEFz6', 'admin', '2025-10-21 11:03:52');

-- --------------------------------------------------------

--
-- Table structure for table `tpin_hashes`
--

CREATE TABLE `tpin_hashes` (
  `HashID` int(11) NOT NULL,
  `IndividualID` int(11) NOT NULL,
  `tpin_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tpin_hashes`
--

INSERT INTO `tpin_hashes` (`HashID`, `IndividualID`, `tpin_hash`, `created_at`, `updated_at`) VALUES
(1, 6, '$2y$10$B2LQMET/i2QkzosvJ6g8DOwTtWAJaBtEpJd2asLC6wEC10Rar3R/G', '2025-10-21 14:13:36', '2025-10-21 14:13:36'),
(2, 8, '$2y$10$6lSdURQvcSLs4BHI8jlf4e4hYU0pzcCibq2oYogZ2IRHjSFs0172G', '2025-10-21 14:24:20', '2025-10-21 14:24:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allocation`
--
ALTER TABLE `allocation`
  ADD PRIMARY KEY (`AllocationID`),
  ADD KEY `PaymentID` (`PaymentID`),
  ADD KEY `ReturnID` (`ReturnID`);

--
-- Indexes for table `auditcases`
--
ALTER TABLE `auditcases`
  ADD PRIMARY KEY (`AuditID`),
  ADD KEY `TPIN` (`TPIN`);

--
-- Indexes for table `biz_assessments`
--
ALTER TABLE `biz_assessments`
  ADD PRIMARY KEY (`AssessmentID`),
  ADD KEY `TPIN` (`TPIN`);

--
-- Indexes for table `biz_businesses`
--
ALTER TABLE `biz_businesses`
  ADD PRIMARY KEY (`BusinessID`),
  ADD UNIQUE KEY `TPIN` (`TPIN`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `biz_employees`
--
ALTER TABLE `biz_employees`
  ADD PRIMARY KEY (`EmployeeID`),
  ADD UNIQUE KEY `NRC` (`NRC`),
  ADD KEY `BusinessTPIN` (`BusinessTPIN`);

--
-- Indexes for table `biz_notices`
--
ALTER TABLE `biz_notices`
  ADD PRIMARY KEY (`NoticeID`),
  ADD KEY `TPIN` (`TPIN`);

--
-- Indexes for table `biz_payments`
--
ALTER TABLE `biz_payments`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `TPIN` (`TPIN`);

--
-- Indexes for table `biz_taxreturns`
--
ALTER TABLE `biz_taxreturns`
  ADD PRIMARY KEY (`ReturnID`),
  ADD KEY `TPIN` (`TPIN`);

--
-- Indexes for table `individuals`
--
ALTER TABLE `individuals`
  ADD PRIMARY KEY (`IndividualID`),
  ADD UNIQUE KEY `TPIN` (`TPIN`),
  ADD UNIQUE KEY `NRC` (`NRC`);

--
-- Indexes for table `interactions`
--
ALTER TABLE `interactions`
  ADD PRIMARY KEY (`InteractionID`),
  ADD KEY `TPIN` (`TPIN`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `TPIN` (`TPIN`);

--
-- Indexes for table `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`PenaltyID`),
  ADD KEY `TPIN` (`TPIN`),
  ADD KEY `ReturnID` (`ReturnID`),
  ADD KEY `AuditID` (`AuditID`);

--
-- Indexes for table `reference_taxrates`
--
ALTER TABLE `reference_taxrates`
  ADD PRIMARY KEY (`RateID`),
  ADD KEY `TaxTypeID` (`TaxTypeID`);

--
-- Indexes for table `reference_taxtypes`
--
ALTER TABLE `reference_taxtypes`
  ADD PRIMARY KEY (`TaxTypeID`);

--
-- Indexes for table `taxpayers`
--
ALTER TABLE `taxpayers`
  ADD PRIMARY KEY (`TPIN`);

--
-- Indexes for table `taxreturns`
--
ALTER TABLE `taxreturns`
  ADD PRIMARY KEY (`ReturnID`),
  ADD KEY `TPIN` (`TPIN`),
  ADD KEY `TaxTypeID` (`TaxTypeID`);

--
-- Indexes for table `tax_admins`
--
ALTER TABLE `tax_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tpin_hashes`
--
ALTER TABLE `tpin_hashes`
  ADD PRIMARY KEY (`HashID`),
  ADD KEY `IndividualID` (`IndividualID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allocation`
--
ALTER TABLE `allocation`
  MODIFY `AllocationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auditcases`
--
ALTER TABLE `auditcases`
  MODIFY `AuditID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `biz_assessments`
--
ALTER TABLE `biz_assessments`
  MODIFY `AssessmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `biz_businesses`
--
ALTER TABLE `biz_businesses`
  MODIFY `BusinessID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `biz_employees`
--
ALTER TABLE `biz_employees`
  MODIFY `EmployeeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `biz_notices`
--
ALTER TABLE `biz_notices`
  MODIFY `NoticeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `biz_payments`
--
ALTER TABLE `biz_payments`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `biz_taxreturns`
--
ALTER TABLE `biz_taxreturns`
  MODIFY `ReturnID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `individuals`
--
ALTER TABLE `individuals`
  MODIFY `IndividualID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `interactions`
--
ALTER TABLE `interactions`
  MODIFY `InteractionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penalties`
--
ALTER TABLE `penalties`
  MODIFY `PenaltyID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reference_taxrates`
--
ALTER TABLE `reference_taxrates`
  MODIFY `RateID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reference_taxtypes`
--
ALTER TABLE `reference_taxtypes`
  MODIFY `TaxTypeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxreturns`
--
ALTER TABLE `taxreturns`
  MODIFY `ReturnID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tax_admins`
--
ALTER TABLE `tax_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tpin_hashes`
--
ALTER TABLE `tpin_hashes`
  MODIFY `HashID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allocation`
--
ALTER TABLE `allocation`
  ADD CONSTRAINT `allocation_ibfk_1` FOREIGN KEY (`PaymentID`) REFERENCES `payments` (`PaymentID`),
  ADD CONSTRAINT `allocation_ibfk_2` FOREIGN KEY (`ReturnID`) REFERENCES `taxreturns` (`ReturnID`);

--
-- Constraints for table `auditcases`
--
ALTER TABLE `auditcases`
  ADD CONSTRAINT `auditcases_ibfk_1` FOREIGN KEY (`TPIN`) REFERENCES `taxpayers` (`TPIN`);

--
-- Constraints for table `biz_assessments`
--
ALTER TABLE `biz_assessments`
  ADD CONSTRAINT `biz_assessments_ibfk_1` FOREIGN KEY (`TPIN`) REFERENCES `biz_businesses` (`TPIN`) ON DELETE CASCADE;

--
-- Constraints for table `biz_employees`
--
ALTER TABLE `biz_employees`
  ADD CONSTRAINT `biz_employees_ibfk_1` FOREIGN KEY (`BusinessTPIN`) REFERENCES `biz_businesses` (`TPIN`) ON DELETE CASCADE;

--
-- Constraints for table `biz_notices`
--
ALTER TABLE `biz_notices`
  ADD CONSTRAINT `biz_notices_ibfk_1` FOREIGN KEY (`TPIN`) REFERENCES `biz_businesses` (`TPIN`) ON DELETE CASCADE;

--
-- Constraints for table `biz_payments`
--
ALTER TABLE `biz_payments`
  ADD CONSTRAINT `biz_payments_ibfk_1` FOREIGN KEY (`TPIN`) REFERENCES `biz_businesses` (`TPIN`) ON DELETE CASCADE;

--
-- Constraints for table `biz_taxreturns`
--
ALTER TABLE `biz_taxreturns`
  ADD CONSTRAINT `biz_taxreturns_ibfk_1` FOREIGN KEY (`TPIN`) REFERENCES `biz_businesses` (`TPIN`) ON DELETE CASCADE;

--
-- Constraints for table `individuals`
--
ALTER TABLE `individuals`
  ADD CONSTRAINT `individuals_ibfk_1` FOREIGN KEY (`TPIN`) REFERENCES `taxpayers` (`TPIN`);

--
-- Constraints for table `interactions`
--
ALTER TABLE `interactions`
  ADD CONSTRAINT `interactions_ibfk_1` FOREIGN KEY (`TPIN`) REFERENCES `taxpayers` (`TPIN`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`TPIN`) REFERENCES `taxpayers` (`TPIN`);

--
-- Constraints for table `penalties`
--
ALTER TABLE `penalties`
  ADD CONSTRAINT `penalties_ibfk_1` FOREIGN KEY (`TPIN`) REFERENCES `taxpayers` (`TPIN`),
  ADD CONSTRAINT `penalties_ibfk_2` FOREIGN KEY (`ReturnID`) REFERENCES `taxreturns` (`ReturnID`),
  ADD CONSTRAINT `penalties_ibfk_3` FOREIGN KEY (`AuditID`) REFERENCES `auditcases` (`AuditID`);

--
-- Constraints for table `reference_taxrates`
--
ALTER TABLE `reference_taxrates`
  ADD CONSTRAINT `reference_taxrates_ibfk_1` FOREIGN KEY (`TaxTypeID`) REFERENCES `reference_taxtypes` (`TaxTypeID`);

--
-- Constraints for table `taxreturns`
--
ALTER TABLE `taxreturns`
  ADD CONSTRAINT `taxreturns_ibfk_1` FOREIGN KEY (`TPIN`) REFERENCES `taxpayers` (`TPIN`),
  ADD CONSTRAINT `taxreturns_ibfk_2` FOREIGN KEY (`TaxTypeID`) REFERENCES `reference_taxtypes` (`TaxTypeID`);

--
-- Constraints for table `tpin_hashes`
--
ALTER TABLE `tpin_hashes`
  ADD CONSTRAINT `tpin_hashes_ibfk_1` FOREIGN KEY (`IndividualID`) REFERENCES `individuals` (`IndividualID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
