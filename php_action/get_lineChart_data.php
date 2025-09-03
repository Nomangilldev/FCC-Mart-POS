<?php
include_once 'db_connect.php';

// Set filter and date range
$filter = $_GET['filter'] ?? 'today';
$today = date('Y-m-d');
$agg = 'daily'; // default aggregation

switch ($filter) {
    case 'today':
        $start = $end = $today;
        $agg = 'hourly';
        break;

    case 'yesterday':
        $start = $end = date('Y-m-d', strtotime('-1 day'));
        $agg = 'hourly';
        break;

    case 'weekly': // last 7 days
        $start = date('Y-m-d', strtotime('-6 days'));
        $end = $today;
        break;

    case 'this_week': // Full current week (Mon–Sun)
        $start = date('Y-m-d', strtotime('monday this week'));
        $end = date('Y-m-d', strtotime('sunday this week'));
        break;

    case 'last_week': // Full last week
        $start = date('Y-m-d', strtotime('monday last week'));
        $end = date('Y-m-d', strtotime('sunday last week'));
        break;

    case 'this_month': // Full current month
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        $agg = 'monthly';
        break;

    case 'last_month': // Full last month
        $start = date('Y-m-01', strtotime('first day of last month'));
        $end = date('Y-m-t', strtotime('last month'));
        $agg = 'monthly';
        break;

    case 'year':
        $start = date('Y-01-01');
        $end = $today;
        $agg = 'monthly';
        break;

    case 'last_year':
        $start = date('Y-01-01', strtotime('last year'));
        $end = date('Y-12-31', strtotime('last year'));
        $agg = 'monthly';
        break;

    default:
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Invalid filter']));
}

// Sanitize dates
$start = mysqli_real_escape_string($dbc, $start);
$end = mysqli_real_escape_string($dbc, $end);

// Fetch sales and purchases
$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id']; // Logged-in user's ID

$where_clause = ($user_role === 'admin') ? '' : " AND user_id = '$user_id'";

if ($agg === 'hourly') {
    $sales_query = mysqli_query($dbc, "
        SELECT DATE_FORMAT(timestamp, '%H:00') AS key_date,
               SUM(grand_total) AS total
        FROM orders
        WHERE DATE(timestamp) = '$start' $where_clause
        GROUP BY key_date
        ORDER BY key_date
    ");
    $purchases_query = mysqli_query($dbc, "
        SELECT DATE_FORMAT(timestamp, '%H:00') AS key_date,
               SUM(grand_total) AS total
        FROM purchase
        WHERE DATE(timestamp) = '$start' $where_clause
        GROUP BY key_date
        ORDER BY key_date
    ");
} elseif ($agg === 'daily') {
    $sales_query = mysqli_query($dbc, "
        SELECT DATE(order_date) AS key_date,
               SUM(grand_total) AS total
        FROM orders
        WHERE DATE(order_date) BETWEEN '$start' AND '$end' $where_clause
        GROUP BY key_date
        ORDER BY key_date
    ");
    $purchases_query = mysqli_query($dbc, "
        SELECT DATE(purchase_date) AS key_date,
               SUM(grand_total) AS total
        FROM purchase
        WHERE DATE(purchase_date) BETWEEN '$start' AND '$end' $where_clause
        GROUP BY key_date
        ORDER BY key_date
    ");
} else { // monthly (could be month or year aggregation)
    $is_yearly = in_array($filter, ['year', 'last_year']);

    if ($is_yearly) {
        // Yearly → group by month
        $sales_query = mysqli_query($dbc, "
            SELECT DATE_FORMAT(order_date, '%Y-%m') AS key_date,
                   SUM(grand_total) AS total
            FROM orders
            WHERE DATE(order_date) BETWEEN '$start' AND '$end' $where_clause
            GROUP BY key_date
            ORDER BY key_date
        ");
        $purchases_query = mysqli_query($dbc, "
            SELECT DATE_FORMAT(purchase_date, '%Y-%m') AS key_date,
                   SUM(grand_total) AS total
            FROM purchase
            WHERE DATE(purchase_date) BETWEEN '$start' AND '$end' $where_clause
            GROUP BY key_date
            ORDER BY key_date
        ");
    } else {
        // Monthly → group by day
        $sales_query = mysqli_query($dbc, "
            SELECT DATE(order_date) AS key_date,
                   SUM(grand_total) AS total
            FROM orders
            WHERE DATE(order_date) BETWEEN '$start' AND '$end' $where_clause
            GROUP BY key_date
            ORDER BY key_date
        ");
        $purchases_query = mysqli_query($dbc, "
            SELECT DATE(purchase_date) AS key_date,
                   SUM(grand_total) AS total
            FROM purchase
            WHERE DATE(purchase_date) BETWEEN '$start' AND '$end' $where_clause
            GROUP BY key_date
            ORDER BY key_date
        ");
    }
}

if (!$sales_query) {
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Sales query failed: ' . mysqli_error($dbc)]));
}
if (!$purchases_query) {
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Purchases query failed: ' . mysqli_error($dbc)]));
}

// Store data in arrays
$sales_data = [];
$purchases_data = [];

while ($row = mysqli_fetch_assoc($sales_query)) {
    $sales_data[$row['key_date']] = (float) $row['total'];
}
while ($row = mysqli_fetch_assoc($purchases_query)) {
    $purchases_data[$row['key_date']] = (float) $row['total'];
}

// Generate full range and fill missing data
$labels = [];
$sales = [];
$purchases = [];

if ($agg === 'hourly') {
    $current = 1; // Start from hour 1
    $end_time = 24; // End at hour 24
    while ($current <= $end_time) {
        $labels[] = $current;
        $sales[] = $sales_data[sprintf('%02d:00', $current - 1)] ?? 0;
        $purchases[] = $purchases_data[sprintf('%02d:00', $current - 1)] ?? 0;
        $current++;
    }
} elseif ($agg === 'daily') {
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $start_date = new DateTime($start);
    $end_date = new DateTime($end);
    $interval = new DateInterval('P1D');
    $date_range = new DatePeriod($start_date, $interval, $end_date->modify('+1 day'));
    foreach ($date_range as $date) {
        $day_index = (int) $date->format('N') - 1; // 1 (Mon) to 7 (Sun) -> 0 to 6
        $date_key = $date->format('Y-m-d');
        $labels[] = $days[$day_index];
        $sales[] = $sales_data[$date_key] ?? 0;
        $purchases[] = $purchases_data[$date_key] ?? 0;
    }
} else { // monthly
    $is_yearly = in_array($filter, ['year', 'last_year']);
    $start_date = new DateTime($start);
    $end_date = new DateTime($end);

    if ($is_yearly) {
        // Yearly → 12 months
        for ($m = 1; $m <= 12; $m++) {
            $month_label = date('M', mktime(0, 0, 0, $m, 1)); // Jan, Feb...
            $date_key = $start_date->format('Y-') . sprintf('%02d', $m); // YYYY-MM
            $labels[] = $month_label;
            $sales[] = $sales_data[$date_key] ?? 0;
            $purchases[] = $purchases_data[$date_key] ?? 0;
        }
    } else {
        // Monthly → days in the month
        $days_in_month = $end_date->format('t');
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date_key = $start_date->format('Y-m-') . sprintf('%02d', $day); // YYYY-MM-DD
            $labels[] = $day; // 1, 2, 3...
            $sales[] = $sales_data[$date_key] ?? 0;
            $purchases[] = $purchases_data[$date_key] ?? 0;
        }
    }
}

// Output JSON
header('Content-Type: application/json');
echo json_encode([
    'labels' => $labels,
    'sales' => $sales,
    'purchases' => $purchases
]);

mysqli_close($dbc);
?>