<?php
require_once 'init.php';

// Get parameters
$flow = isset($_GET['flow']) ? $_GET['flow'] : (isset($_POST['flow']) ? $_POST['flow'] : null);
$volume = isset($_GET['volume']) ? $_GET['volume'] : (isset($_POST['volume']) ? $_POST['volume'] : null);

$response = [
    "success" => true,
    "message" => "No data received"
];

// Update Flow Rate (Real-time Status)
if ($flow !== null) {
    $flow_val = floatval($flow);
    // Determine if we insert or update. Since we want single row ID=1.
    // We already have "INSERT IGNORE ... VALUES (1, 0)" in plan, assuming it's run.
    // We can use INSERT ON DUPLICATE KEY UPDATE for safety.
    $query = "INSERT INTO oximonitor_status (id, flow_rate, updated_at) VALUES (1, $flow_val, NOW()) ON DUPLICATE KEY UPDATE flow_rate = $flow_val, updated_at = NOW()";
    
    if (mysqli_query($conn, $query)) {
         $response["flow_update"] = "Success";
    } else {
         $response["success"] = false;
         $response["flow_error"] = mysqli_error($conn);
    }
}

// Insert Volume Log (Periodic Accumulation)
if ($volume !== null) {
    $volume_val = floatval($volume);
    $query = "INSERT INTO oximonitor_log (volume, created_at) VALUES ($volume_val, NOW())";
    
    if (mysqli_query($conn, $query)) {
         $response["volume_insert"] = "Success";
    } else {
         $response["success"] = false;
         $response["volume_error"] = mysqli_error($conn);
    }
}

echo json_encode($response);
?>
