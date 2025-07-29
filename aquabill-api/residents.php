<?php
header('Content-Type: application/json');

$conn = new mysqli('localhost', 'root', '', 'aquabill');
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit();
}

// ─── POST: mark resident as paid / undo ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing resident ID.']);
        exit();
    }
    $residentId = intval($data['id']);

    // Undo payment
    if (!empty($data['undo'])) {
        $stmt = $conn->prepare("
            UPDATE residents
            SET is_verified  = 0,
                payment_mode = NULL
            WHERE id = ?
        ");
        $stmt->bind_param("i", $residentId);
        $ok = $stmt->execute();
        $stmt->close();

        echo json_encode([
            'status'  => $ok ? 'success' : 'error',
            'message' => $ok ? 'Payment status undone.' : 'Failed to undo payment.'
        ]);
        $conn->close();
        exit();
    }

    // Normal payment flow
    if (empty($data['payment_mode'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing payment_mode.']);
        exit();
    }
    $mode = $conn->real_escape_string($data['payment_mode']);
    $now  = date('Y-m-d H:i:s');

    // 1) Mark resident as verified
    $u = $conn->prepare("
        UPDATE residents
        SET is_verified  = 1,
            payment_mode = ?
        WHERE id = ?
    ");
    $u->bind_param("si", $mode, $residentId);
    if (! $u->execute()) {
        echo json_encode(['status'=>'error','message'=>'Failed to update resident.']);
        exit();
    }
    $u->close();

    // 2) Find that resident’s latest bill
    $b = $conn->prepare("
        SELECT id, total, due_date
        FROM bills
        WHERE resident_id = ?
        ORDER BY due_date DESC
        LIMIT 1
    ");
    $b->bind_param("i", $residentId);
    $b->execute();
    $b->bind_result($billId, $amount, $dueDate);
    if (! $b->fetch()) {
        // no bill found
        $b->close();
        echo json_encode(['status'=>'error','message'=>'Resident paid but no bill found.']);
        exit();
    }
    $b->close();

    // 3) Log into payment_history
    $h = $conn->prepare("
        INSERT INTO payment_history
            (resident_id, bill_id, amount, payment_mode, paid_at)
        VALUES
            (?, ?, ?, ?, ?)
    ");
    $h->bind_param("iidss", $residentId, $billId, $amount, $mode, $now);
    $h->execute();
    $h->close();

    // 4) Create notification
    $msgResident = "Your payment of ₱" . number_format($amount,2) . " on {$dueDate} has been received.";
    $n = $conn->prepare("
        INSERT INTO notifications
            (sender_type, sender_id, receiver_type, receiver_id, message)
        VALUES
            ('system', NULL, 'resident', ?, ?)
    ");
    $n->bind_param("is", $residentId, $msgResident);
    $n->execute();
    $n->close();

    echo json_encode([
        'status'  => 'success',
        'message' => 'Resident marked as paid and history logged.'
    ]);
    $conn->close();
    exit();
}

// ─── GET single resident ───────────────────────────────────────────────────────
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("
        SELECT id, name, meter_no, is_verified, payment_mode,
               age, gender, email, contact, profile_pic
        FROM residents WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resident = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($resident) {
        echo json_encode([
            'status' => 'success',
            'data'   => [
                'id'           => $resident['id'],
                'name'         => $resident['name'],
                'meter_no'     => $resident['meter_no'],
                'status'       => $resident['is_verified'] == 1 ? 'paid' : 'unpaid',
                'payment_mode' => $resident['payment_mode'],
                'age'          => $resident['age'],
                'gender'       => $resident['gender'],
                'email'        => $resident['email'],
                'contact'      => $resident['contact'],
                'profile_pic'  => $resident['profile_pic'],
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Resident not found']);
    }
    exit();
}

// ─── GET all residents ─────────────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT id, name, is_verified, payment_mode, profile_pic
    FROM residents
");
$stmt->execute();

$residents = [];
foreach ($stmt->get_result() as $row) {
    $residents[] = [
        'id'           => $row['id'],
        'name'         => $row['name'],
        'status'       => $row['is_verified'] == 1 ? 'paid' : 'unpaid',
        'payment_mode' => $row['payment_mode'],
        'profile_pic'  => $row['profile_pic'],
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'data' => $residents]);
