<?php
require_once '../config/connection.php';

$respondents = $pdo->query("SELECT id FROM respondents")->fetchAll();
$num_respondents = count($respondents);

if ($num_respondents == 0) {
    header("Location: respondents.php?error=no_respondents");
    exit;
}

$criteria = $pdo->query("SELECT id FROM criteria")->fetchAll();

// Clear current comparisons
$pdo->query("DELETE FROM comparisons");

foreach ($criteria as $c1) {
    foreach ($criteria as $c2) {
        if ($c1['id'] == $c2['id']) {
            $pdo->prepare("INSERT INTO comparisons (criteria_1, criteria_2, value) VALUES (?, ?, ?)")
                ->execute([$c1['id'], $c2['id'], 1]);
        } else {
            // Get all values from respondents for this pair
            $stmt = $pdo->prepare("SELECT value FROM respondent_comparisons WHERE criteria_1 = ? AND criteria_2 = ?");
            $stmt->execute([$c1['id'], $c2['id']]);
            $values = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($values) > 0) {
                // Calculate Geometric Mean
                $product = array_product($values);
                $geo_mean = pow($product, 1 / count($values));
                
                $pdo->prepare("INSERT INTO comparisons (criteria_1, criteria_2, value) VALUES (?, ?, ?)")
                    ->execute([$c1['id'], $c2['id'], $geo_mean]);
            }
        }
    }
}

header("Location: comparisons.php?aggregated=1");
exit;
?>
