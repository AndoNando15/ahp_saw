<?php
require_once 'config/connection.php';

$pdo->query("CREATE TABLE IF NOT EXISTS respondents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
)");

$pdo->query("CREATE TABLE IF NOT EXISTS respondent_comparisons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    respondent_id INT NOT NULL,
    criteria_1 INT NOT NULL,
    criteria_2 INT NOT NULL,
    value DOUBLE NOT NULL,
    FOREIGN KEY (respondent_id) REFERENCES respondents(id) ON DELETE CASCADE
)");

echo "Aggregation tables created!";
?>
