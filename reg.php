<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Login.php');
    exit;
}

include 'db.php';

$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

// validation of data 

if ($name === '' || $email === '' || $phone === '' || $password === '') {
    $errors['global'] = "Veuillez remplir tout les champs";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Email invalide.";
}
if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{6,20}$/', $phone)) {
    $errors['phone'] = "Téléphone invalide.";
}
if (strlen($password) < 4) {
    $errors['password'] = "Mot de passe trop court.";
} else {
    $hashed_password = password_hash($password,PASSWORD_DEFAULT);
}

if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['old'] = ['name'=>$name,'email'=>$email,'phone'=>$phone];
    $conn->close();
    header('Location: index.php');
    exit;
}

// uniqueness checks
$stmt = $conn->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $_SESSION['register_errors'] = ['email' => 'Cet email est déjà utilisé.'];
    $_SESSION['old'] = ['name'=>$name,'email'=>$email,'phone'=>$phone];
    $conn->close();
    header('Location: Login.php');
    exit;
}
$stmt->close();

// INSERT hashed  password (secure)
$sql = "INSERT INTO user (name, email, phone, password) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['register_errors'] = ['global' => 'Erreur prepare.'];
    $conn->close();
    header('Location: Login.php');
    exit;
}
$stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: Login.php?success=' . rawurlencode('Inscription réussie.'));
    exit;
} else {
    $stmt->close();
    $_SESSION['register_errors'] = ['global' => 'Impossible de créer le compte.'];
    $_SESSION['old'] = ['name'=>$name,'email'=>$email,'phone'=>$phone];
    $conn->close();
    header('Location: index.php');
    exit;
}
?>