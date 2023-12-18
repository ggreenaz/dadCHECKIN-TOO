<?php
// Error reporting and PHP settings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database configuration file
require_once '../config.php';

// Function to execute a query and return results in an associative array
function executeQuery($pdo, $query, $parameters = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error in query: " . $e->getMessage();
        return [];
    }
}

// Fetch reason for visit stats
$reasonStatsQuery = "SELECT visit_reasons.reason_description, COUNT(*) as count 
                     FROM checkin_checkout 
                     JOIN visit_reasons ON checkin_checkout.visit_reason_id = visit_reasons.reason_id 
                     GROUP BY visit_reason_id";
$reasonStats = executeQuery($pdo, $reasonStatsQuery);

// Fetch data for 'Person Visited' chart
$personVisitedStatsQuery = "SELECT visiting_persons.person_name, COUNT(checkin_checkout.user_id) as visit_count 
                            FROM checkin_checkout 
                            JOIN visiting_persons ON checkin_checkout.visiting_person_id = visiting_persons.person_id 
                            GROUP BY checkin_checkout.visiting_person_id";
$personVisitedStats = executeQuery($pdo, $personVisitedStatsQuery);

// Fetch data for 'Time Spent Per Visit Reason' chart
$timeSpentStatsQuery = "SELECT visit_reasons.reason_description, AVG(TIMESTAMPDIFF(MINUTE, checkin_checkout.checkin_time, checkin_checkout.checkout_time)) as avg_minutes_spent 
                        FROM checkin_checkout 
                        JOIN visit_reasons ON checkin_checkout.visit_reason_id = visit_reasons.reason_id 
                        GROUP BY checkin_checkout.visit_reason_id";
$timeSpentStats = executeQuery($pdo, $timeSpentStatsQuery);

// Rest of your code for HTML and JavaScript...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Visitor History Data</title>
    <link rel="stylesheet" type="text/css" href="./theme.php">
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <img src="../img/dnd-project-sm-logo.png">
    <h1>Visitor History Data</h1>
   <h2><a href="./index.php" class="button">Return to Admin Dashboard</a></h2>
    <!-- Chart Selector Dropdown -->
    <select id="chartSelector">
        <option value="all">All Charts</option>
        <option value="reason-for-visit-container">Reason for Visit</option>
        <option value="person-visited-container">Person Visited</option>
        <option value="time-spent-container">Time Spent Per Visit Reason</option>
    </select>

    <div id="reason-for-visit-container" class="chart-container">
        <h2>Reason for Visit</h2>
        <canvas id="reasonForVisitChart"></canvas>
    </div>

    <div id="person-visited-container" class="chart-container">
        <h2>Person Visited</h2>
        <canvas id="personVisitedChart"></canvas>
    </div>

    <div id="time-spent-container" class="chart-container">
        <h2>Time Spent Per Visit Reason</h2>
        <canvas id="timeSpentChart"></canvas>
    </div>

<script>
document.getElementById('chartSelector').addEventListener('change', function() {
    var selectedChart = this.value;
    var chartContainers = document.querySelectorAll('.chart-container');

    chartContainers.forEach(function(container) {
        container.style.display = (selectedChart === 'all' || container.id === selectedChart) ? 'block' : 'none';
    });
});

document.addEventListener('DOMContentLoaded', function() {
    var chartContainers = document.querySelectorAll('.chart-container');
    chartContainers.forEach(function(container) {
        container.style.display = 'none'; // Hide all containers initially
    });
    document.getElementById('reason-for-visit-container').style.display = 'block'; // Show the first chart

    // Reason for Visit Chart
    var ctxReasonForVisit = document.getElementById('reasonForVisitChart').getContext('2d');
    new Chart(ctxReasonForVisit, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($reasonStats, 'reason_description')); ?>,
            datasets: [{
                label: 'Reason for Visit',
                data: <?php echo json_encode(array_column($reasonStats, 'count')); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    // ... more colors ...
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    // ... more border colors ...
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 30,
                    bottom: 50
                }
            }
            // ... other options ...
        }
    });

    // Person Visited Chart
    var ctxPersonVisited = document.getElementById('personVisitedChart').getContext('2d');
    new Chart(ctxPersonVisited, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($personVisitedStats, 'person_name')); ?>,
            datasets: [{
                label: 'Number of Visits',
                data: <?php echo json_encode(array_column($personVisitedStats, 'visit_count')); ?>,
                backgroundColor: [
                    'rgba(22, 160, 133, 0.7)',
                    'rgba(41, 128, 185, 0.7)',
                    'rgba(192, 57, 43, 0.7)',
                    // ... more colors ...
                ]
            }]
        },
        options: {
            maintainAspectRatio: true,
            layout: {
                padding: {
                    bottom: 30
                }
            },
            scales: {
                xAxes: [{
                    ticks: {
                        autoSkip: true,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }]
            }
        }
    });

    // Time Spent Per Visit Reason Chart
    var ctxTimeSpent = document.getElementById('timeSpentChart').getContext('2d');
    new Chart(ctxTimeSpent, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($timeSpentStats, 'reason_description')); ?>,
            datasets: [{
                label: 'Average Minutes Spent',
                data: <?php echo json_encode(array_column($timeSpentStats, 'avg_minutes_spent')); ?>,
                // ... other dataset properties ...
            }]
        },
        options: {
            maintainAspectRatio: true,
            layout: {
                padding: {
                    bottom: 30
                }
            },
            scales: {
                xAxes: [{
                    ticks: {
                        autoSkip: true,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }]
            }
        }
    });
});
</script>
</body>
</html>
