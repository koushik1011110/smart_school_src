<?php

header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/
$conn = new mysqli(
    "localhost",
    "root",
    "",
    "smart_school"
);

if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Database Connection Failed'
    ]));
}

/*
|--------------------------------------------------------------------------
| RFID UID
|--------------------------------------------------------------------------
*/
$rfid_uid = strtoupper(trim($_POST['rfid_uid'] ?? ''));

if (empty($rfid_uid)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'RFID UID missing'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Attendance Type IDs
|--------------------------------------------------------------------------
*/
$presentQuery = $conn->query("
    SELECT id
    FROM staff_attendance_type
    WHERE type = 'Present'
    LIMIT 1
");

$lateQuery = $conn->query("
    SELECT id
    FROM staff_attendance_type
    WHERE type = 'Late'
    LIMIT 1
");

if (!$presentQuery || !$lateQuery) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Attendance types not configured'
    ]);
    exit;
}

$presentRow = $presentQuery->fetch_assoc();
$lateRow = $lateQuery->fetch_assoc();

$presentId = (int) $presentRow['id'];
$lateId = (int) $lateRow['id'];

/*
|--------------------------------------------------------------------------
| Find Staff By RFID
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT id,name
    FROM staff
    WHERE rfid_uid = ?
    LIMIT 1
");

$stmt->bind_param("s", $rfid_uid);
$stmt->execute();

$staff = $stmt->get_result()->fetch_assoc();

if (!$staff) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Card not registered'
    ]);
    exit;
}

$staff_id = (int) $staff['id'];

/*
|--------------------------------------------------------------------------
| Today's Attendance Check
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT id,staff_attendance_type_id
    FROM staff_attendance
    WHERE staff_id = ?
    AND date = CURDATE()
    LIMIT 1
");

$stmt->bind_param("i", $staff_id);
$stmt->execute();

$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Attendance already marked',
        'staff_name' => $staff['name']
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Attendance Logic
|--------------------------------------------------------------------------
|
| Before 10:00 AM = Present
| After 10:00 AM  = Late
|
*/

$currentTime = date('H:i:s');

if ($currentTime <= '10:00:00') {

    $attendance_type_id = $presentId;
    $attendance_status = 'Present';

} else {

    $attendance_type_id = $lateId;
    $attendance_status = 'Late';
}

/*
|--------------------------------------------------------------------------
| Insert Attendance
|--------------------------------------------------------------------------
*/
$remark = "RFID Attendance";

$stmt = $conn->prepare("
    INSERT INTO staff_attendance
    (
        date,
        staff_id,
        staff_attendance_type_id,
        remark,
        is_active,
        created_at
    )
    VALUES
    (
        CURDATE(),
        ?,
        ?,
        ?,
        1,
        NOW()
    )
");

$stmt->bind_param(
    "iis",
    $staff_id,
    $attendance_type_id,
    $remark
);

if ($stmt->execute()) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Attendance marked successfully',
        'attendance_status' => $attendance_status,
        'staff_name' => $staff['name'],
        'time' => date('h:i:s A'),
        'date' => date('d-m-Y')
    ]);

} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Attendance insert failed',
        'mysql_error' => $conn->error
    ]);
}