<?php
session_start();
require 'db.php';
$notifications = $pdo->query("SELECT message, created_at FROM notifications ORDER BY created_at DESC LIMIT 5")->fetchAll();
$unreadCount = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function generateRandomPassword($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    return substr(str_shuffle(str_repeat($characters, $length)), 0, $length);
}


if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';

    $query = "SELECT id, name, email, contact, gender, age, meter_no, is_verified, is_archived FROM residents WHERE 1";

    $params = [];

    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
if ($status === 'Paid') {
    $query .= " AND is_verified = 1 AND is_archived = 0";
} elseif ($status === 'Unpaid') {
    $query .= " AND is_verified = 0 AND is_archived = 0";
} elseif ($status === 'Archived') {
    $query .= " AND is_archived = 1";
} else {
    $query .= " AND is_archived = 0"; 
}

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($residents);
    exit();
}

if  (isset($_POST['action']) && $_POST['action'] === 'add_household') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $age = $_POST['age'] ?? '';
    $meter_no = $_POST['meter_no'] ?? '';
    $is_verified = 0;

    $randomPassword = generateRandomPassword();
    $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);

    $check = $pdo->prepare("SELECT COUNT(*) FROM residents WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists!']);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO residents (name, email, contact, gender, age, meter_no, is_verified, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $success = $stmt->execute([$name, $email, $contact, $gender, $age, $meter_no, $is_verified, $hashedPassword]);

    if ($success) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'aquabillmagahis@gmail.com';
            $mail->Password = 'tmsx uskx ckzo sgsv';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('aquabillmagahis@gmail.com', 'Aqua-Bill Admin');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Aqua-Bill';
            $mail->Body = "<h3>Welcome, $name!</h3>
                           <p>You have been registered in Aqua-Bill.</p>
                           <p>Your temporary password is: <strong>$randomPassword</strong></p>
                           <p>Please change your password after logging in.</p>";

            $mail->send();

            echo json_encode(['status' => 'success', 'message' => 'Household added successfully and email sent.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'warning', 'message' => 'Household added but email failed: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add household']);
    }
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'edit_household') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $age = $_POST['age'] ?? '';
    $meter_no = $_POST['meter_no'] ?? '';

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid resident ID']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE residents SET name = ?, email = ?, contact = ?, gender = ?, age = ?, meter_no = ? WHERE id = ?");
    $success = $stmt->execute([$name, $email, $contact, $gender, $age, $meter_no, $id]);

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Household Representative updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update resident']);
    }
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'archive_resident' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE residents SET is_archived = 1 WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success', 'message' => 'Household Representative archived successfully']);
    exit();
}


if (isset($_GET['action']) && $_GET['action'] === 'unarchive_resident' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE residents SET is_archived = 0 WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success', 'message' => 'Household Representative restored successfully']);
    exit();
}


if (isset($_GET['action']) && $_GET['action'] === 'get_resident' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT id, name, email, contact, gender, age, meter_no FROM residents WHERE id = ?");
    $stmt->execute([$id]);
    $resident = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resident) {
        echo json_encode(['status' => 'success', 'data' => $resident]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Household Representative not found']);
    }
    exit();
}


if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Households | Aqua-Bill</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  

  <style>
    
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f4f7fa;
      display: flex;
      min-height: 100vh;
    }
    .sidebar {
      width: 240px;
      background: #0077B6;
      color: white;
      display: flex;
      flex-direction: column;
      padding-top: 1rem;
      transition: transform 0.3s ease-in-out;
    }
    .sidebar.hide {
      transform: translateX(-100%);
      position: absolute;
      z-index: 1000;
    }
    .logo-container {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding-bottom: 1rem;
      border-bottom: 1px solid #ffffff44;
      margin-bottom: 1rem;
    }
    .sidebar-logo {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 50%;
      border: 2px solid white;
      margin-bottom: 0.5rem;
    }
    .logo-text {
      color: white;
      font-size: 1.2rem;
      font-weight: bold;
      letter-spacing: 1px;
    }
    .sidebar a {
      color: white;
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      text-decoration: none;
      font-weight: 500;
    }
    .sidebar a i {
      margin-right: 1rem;
      width: 20px;
    }
    .sidebar a:hover,
    .sidebar a.active {
      background: #90e0ef;
      color: black;
    }

    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .topbar {
      background: #0077B6;
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .topbar-left {
      display: flex;
      align-items: center;
    }
    
    .burger {
      font-size: 1.5rem;
      cursor: pointer;
      margin-right: 1rem;
      display: block;
    }
    .user-dropdown {
      position: relative;
    }
    .user-logo {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      cursor: pointer;
      object-fit: cover;
      border: 2px solid white;
    }
    .dropdown-menu {
      position: absolute;
      right: 0;
      top: 45px;
      background: white;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      display: none;
      flex-direction: column;
      min-width: 150px;
      z-index: 10;
    }
    .dropdown-menu a {
      padding: 0.75rem 1rem;
      display: block;
      text-decoration: none;
      color: #333;
      font-size: 0.95rem;
      border-bottom: 1px solid #eee;
    }
    .dropdown-menu a:hover {
      background-color: #90e0ef;
    }
    .container {
      padding: 2rem;
    }
    .btn {
      background: #0077B6;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .status-paid { color: green; font-weight: bold; }
    .status-unpaid { color: red; font-weight: bold; }

    .filters {
      margin: 1rem 0 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 0.8rem;
      background-color: white;
      padding: 1rem 1.5rem;
      border-radius: 12px 12px 0 0;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .filter-controls {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.8rem;
    }
    .search-input, .status-select {
      padding: 0.5rem 0.8rem;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 1rem;
      width: auto;
    }
    .search-btn {
      background: #0077B6;
      border: none;
      padding: 0.5rem 0.9rem;
      border-radius: 8px;
      color: white;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .search-btn i {
      margin-left: 0;
    }

    
    .section-title {
      font-weight: 700;
      font-size: 1.5rem;
      margin-bottom: 0.8rem;
      color: #222;
    }

    table {
      border-collapse: separate;
      border-spacing: 0 6px;
      width: 100%;
      background: transparent;
    }
    table thead {
      background-color: #0077B6;
      border-radius: 12px 12px 0 0;
      display: table-header-group;
    }
    table thead tr th {
      color: white;
      padding: 1rem 1.3rem;
      font-weight: bold;
      font-size: 1rem;
      border-left: 1px solid #005f8a;
      border-right: 1px solid #005f8a;
      border-top: 1px solid #005f8a;
      border-bottom: 1px solid #005f8a;
      position: relative;
      background-color: #0077B6;
      box-sizing: border-box;
    }
    table thead tr th:first-child {
      border-top-left-radius: 12px;
      border-left: none;
    }
    table thead tr th:last-child {
      border-top-right-radius: 12px;
      border-right: none;
    }
    table tbody tr {
      background: white;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
      margin-bottom: 6px;
      display: table-row;
    }
    table tbody tr td {
      padding: 0.8rem 1rem;
      border-left: 1px solid #eee;
      border-right: 1px solid #eee;
      border-bottom: 1px solid #eee;
      vertical-align: middle;
    }
    table tbody tr td:first-child {
      border-left: none;
    }
    table tbody tr td:last-child {
      border-right: none;
    }
  </style>
</head>
<body>


<div class="sidebar" id="sidebar">
  <div class="logo-container">
    <img src="logo.png" alt="Aqua-Bill Logo" class="sidebar-logo" />
    <span class="logo-text">AQUA-BILL</span>
  </div>
  <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
  <a href="households.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'households.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Households</a>
  <a href="collectors.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'collectors.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Collectors</a>
  <a href="announcements.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> Announcements</a>
  <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Reports</a>
  <a href="notifications.php" class="<?= basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : '' ?>">
  <i class="fas fa-bell"></i> Notifications
</a>
  <a href="payment.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payment.php' ? 'active' : ''; ?>">
    <i class="fas fa-upload"></i> Payment
  </a>
</div>

<div class="main-content">
  <div class="topbar">
    <div class="topbar-left">
      <i class="fas fa-bars burger" onclick="toggleSidebar()"></i>
      <span>Households Representative</span>
    </div>
    <div class="topbar-right">

      <div class="user-dropdown">
        <img src="profile.jpg" alt="Admin" class="user-logo" onclick="toggleDropdown()" />
        <div class="dropdown-menu" id="dropdownMenu">
          <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
          <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    </div>
  </div>

  <div class="container">

    <div class="filters">
      <div class="filter-controls">
        <input type="text" id="search" class="search-input" placeholder="Search name or email" />
        <button onclick="fetchResidents()" class="search-btn"><i class="fas fa-search"></i></button>
        <span class="filter-label" style="font-weight:bold; margin-left:0.5rem;">Filter Status:</span>
        <select id="status" class="status-select">
          <option value="">All</option>
          <option value="Paid">Paid</option>
          <option value="Unpaid">Unpaid</option>
          <option value="Archived">Archived</option>
        </select>
      </div>
      <button class="btn" title="Add Household" onclick="showAddModal()"><i class="fas fa-plus"></i></button>
    </div>

    <table id="residentsTable">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Contact</th>
          <th>Gender</th>
          <th>Age</th>
          <th>Meter No.</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody><!-- Filled by JS --></tbody>
    </table>
  </div>
</div>


<div class="modal fade" id="addHouseholdModal" tabindex="-1" aria-labelledby="addHouseholdModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addHouseholdForm">
        <div class="modal-header">
          <h5 class="modal-title" id="addHouseholdModalLabel">Add New Household</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for="nameInput" class="form-label">Full Name:</label>
          <input type="text" class="form-control" id="nameInput" name="name" required />
          
          <label for="emailInput" class="form-label mt-3">Email Address:</label>
          <input type="email" class="form-control" id="emailInput" name="email" required />
          
          <label for="contactInput" class="form-label mt-3">Contact Number:</label>
          <input type="text" class="form-control" id="contactInput" name="contact" required />
          
          <label for="genderSelect" class="form-label mt-3">Gender:</label>
          <select class="form-select" id="genderSelect" name="gender" required>
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
          
          <label for="ageInput" class="form-label mt-3">Age:</label>
          <input type="number" class="form-control" id="ageInput" name="age" min="1" required />
          
          <label for="meterNoInput" class="form-label mt-3">Meter No.:</label>
          <input type="text" class="form-control" id="meterNoInput" name="meter_no" required />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Add Household</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="modal fade" id="editHouseholdModal" tabindex="-1" aria-labelledby="editHouseholdModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editHouseholdForm">
        <div class="modal-header">
          <h5 class="modal-title" id="editHouseholdModalLabel">Edit Household</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="editId" name="id" />
          <label for="editNameInput" class="form-label">Full Name:</label>
          <input type="text" class="form-control" id="editNameInput" name="name" required />
          
          <label for="editEmailInput" class="form-label mt-3">Email Address:</label>
          <input type="email" class="form-control" id="editEmailInput" name="email" required />
          
          <label for="editContactInput" class="form-label mt-3">Contact Number:</label>
          <input type="text" class="form-control" id="editContactInput" name="contact" required />
          
          <label for="editGenderSelect" class="form-label mt-3">Gender:</label>
          <select class="form-select" id="editGenderSelect" name="gender" required>
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
          
          <label for="editAgeInput" class="form-label mt-3">Age:</label>
          <input type="number" class="form-control" id="editAgeInput" name="age" min="1" required />
          
          <label for="editMeterNoInput" class="form-label mt-3">Meter No.:</label>
          <input type="text" class="form-control" id="editMeterNoInput" name="meter_no" required />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
    
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const addModal = new bootstrap.Modal(document.getElementById('addHouseholdModal'));
const editModal = new bootstrap.Modal(document.getElementById('editHouseholdModal'));

function showAddModal() {
  addModal.show();
}
function hideAddModal() {
  addModal.hide();
}
function showEditModal() {
  editModal.show();
}
function hideEditModal() {
  editModal.hide();
}
function archiveResident(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: "This will archive the Household Representative.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, archive it!'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(`households.php?action=archive_resident&id=${id}`)
        .then(res => res.json())
        .then(data => {
          Swal.fire('Archived!', data.message, 'success');
          fetchResidents(); 
        });
    }
  });
}
function unarchiveResident(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: "This will restore the Household Representative.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, restore it!'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(`households.php?action=unarchive_resident&id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Restored!', data.message, 'success');
            fetchResidents();
          } else {
            Swal.fire('Failed!', 'Failed to restore.', 'error');
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error', 'An error occurred.', 'error');
        });
    }
  });
}

function fetchResidents() {
  const search = document.getElementById("search").value;
  const status = document.getElementById("status").value;

  fetch(`households.php?ajax=1&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`)
    .then(response => response.json())
    .then(data => {
      const tbody = document.querySelector("#residentsTable tbody");
      tbody.innerHTML = ""; 

      data.forEach(r => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${r.name}</td>
          <td>${r.email}</td>
          <td>${r.contact}</td>
          <td>${r.gender}</td>
          <td>${r.age}</td>
          <td>${r.meter_no}</td>
          <td class="${r.is_verified == 1 ? 'status-paid' : 'status-unpaid'}">
            ${r.is_verified == 1 ? 'Paid' : 'Unpaid'}
          </td>
          <td>
            ${
              status === "Archived"
                ? `<a href="javascript:void(0)" class="btn btn-success btn-sm" onclick="unarchiveResident(${r.id})">
                    <i class="fas fa-undo"></i></a>`
                : `<button class="btn btn-sm btn-primary" onclick="openEditModal(${r.id})"><i class="fas fa-edit"></i></button>
                   <a href="javascript:void(0)" class="btn btn-sm btn-warning" onclick="archiveResident(${r.id})"><i class="fas fa-archive"></i></a>
                   <a href="view_bills.php?id=${r.id}" class="btn btn-sm btn-info"><i class="fas fa-file-invoice"></i></a>`
            }
          </td>
        `;
        tbody.appendChild(tr);
      });
    });
}


function openEditModal(id) {
  fetch(`households.php?action=get_resident&id=${id}`)
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        const r = data.data;
        document.getElementById('editId').value = r.id;
        document.getElementById('editNameInput').value = r.name;
        document.getElementById('editEmailInput').value = r.email;
        document.getElementById('editContactInput').value = r.contact;
        document.getElementById('editGenderSelect').value = r.gender;
        document.getElementById('editAgeInput').value = r.age;
        document.getElementById('editMeterNoInput').value = r.meter_no;
        showEditModal();
      } else {
        Swal.fire('Error', data.message, 'error');
      }
    });
}

document.getElementById('addHouseholdForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  formData.append('action', 'add_household');

  fetch('households.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      Swal.fire('Success!', data.message, 'success');
      hideAddModal();
      form.reset();
      fetchResidents();
    } else {
      Swal.fire('Error', data.message, 'error');
    }
  })
  .catch(() => Swal.fire('Error', 'Failed to add household.', 'error'));
});

document.getElementById('editHouseholdForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  formData.append('action', 'edit_household');

  fetch('households.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      Swal.fire('Updated!', data.message, 'success');
      hideEditModal();
      fetchResidents();
    } else {
      Swal.fire('Error', data.message, 'error');
    }
  })
  .catch(() => Swal.fire('Error', 'Failed to update household.', 'error'));
});

function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('hide');
}

function toggleDropdown() {
  const menu = document.getElementById("dropdownMenu");
  menu.style.display = menu.style.display === "flex" ? "none" : "flex";
}

document.addEventListener("click", function (e) {
  const menu = document.getElementById("dropdownMenu");
  const logo = document.querySelector(".user-logo");
  if (menu && !menu.contains(e.target) && !logo.contains(e.target)) {
    menu.style.display = "none";
  }
});

fetchResidents();
document.getElementById('status').addEventListener('change', fetchResidents);
document.getElementById('search').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') {
    fetchResidents();
  }
});
document.getElementById('search').addEventListener('input', function () {
  fetchResidents();
});
</script>



</script>

</body>
</html>
