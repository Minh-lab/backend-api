<?php
// Direct SQL query using PDO to avoid Laravel bootstrap issues if environment is not 100%
try {
    $db = new PDO('mysql:host=localhost;dbname=ptdapm_n1_team3', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- Milestones (Type: CAPSTONE) ---\n";
    $stmt = $db->query("SELECT milestone_id, phase_name, end_date FROM milestones WHERE type = 'CAPSTONE'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['milestone_id']}, Name: {$row['phase_name']}, End: {$row['end_date']}\n";
    }

    echo "\n--- Pending Topic Requests (Status: PENDING_TEACHER) ---\n";
    $stmt = $db->query("SELECT count(*) as count FROM capstone_requests WHERE type = 'TOPIC_PROP' AND status = 'PENDING_TEACHER'");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Total Pending: $count\n";

    $stmt = $db->query("SELECT lecturer_id, count(*) as count FROM capstone_requests WHERE type = 'TOPIC_PROP' AND status = 'PENDING_TEACHER' GROUP BY lecturer_id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Lecturer ID: {$row['lecturer_id']}, Count: {$row['count']}\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
