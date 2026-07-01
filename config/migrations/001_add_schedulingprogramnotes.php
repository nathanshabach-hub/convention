<?php
/**
 * Migration: Add schedulingprogramnotes table
 * This script adds the missing schedulingprogramnotes table to the database
 * for storing Small Program notes and customizations.
 */

// Database connection details - adjust as needed
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'convention_acp_demo_test';

// Create connection
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table SQL
$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `schedulingprogramnotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conventionseasons_id` int(11) DEFAULT NULL,
  `notes_json` longtext COLLATE utf8mb4_unicode_ci,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_convention_season` (`conventionseasons_id`),
  KEY `conventionseasons_id` (`conventionseasons_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'schedulingprogramnotes' created successfully or already exists.\n";
} else {
    echo "✗ Error creating table: " . $conn->error . "\n";
    exit(1);
}

$conn->close();
echo "Migration completed successfully!\n";
?>
