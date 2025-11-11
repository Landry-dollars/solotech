<?php
session_start();

include 'db.php';


$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if (empty($errors)) {
        // Try to select from 'users' table (common) and fall back to 'user' if needed.
        $queryCandidates = [
            "SELECT id, name, email, phone, password, IFNULL(role, '') AS role, IFNULL(is_admin, 0) AS is_admin FROM user WHERE email = ? LIMIT 1",
            "SELECT id, name, email, phone, password, '' AS role, 0 AS is_admin FROM user WHERE email = ? LIMIT 1"
        ];

        $found = false;
        foreach ($queryCandidates as $q) {
            $stmt = $conn->prepare($q);
            if ($stmt === false) continue;
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) { $stmt->close(); continue; }
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                $found = true;
                $id = $row['id'];
                $db_name = $row['name'];
                $db_email = $row['email'];
                $phone = $row['phone'];
                $db_password = $row['password'];
                $role = $row['role'] ?? '';
                $is_admin = !empty($row['is_admin']);
                $stmt->close();
                break;
            }
            $stmt->close();
        }

        if (!$found) {
            $errors[] = "Aucun compte trouvé avec cet email.";
        } else {
            if (strcasecmp($name, $db_name) !== 0) {
                $errors[] = "Nom ne correspond pas à cet email.";
            } else {
                // Verify password using password_verify against stored hash
                if (password_verify($password, $db_password)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['user_name'] = $db_name;
                    $_SESSION['user_email'] = $db_email;
                    $_SESSION['user_phone'] = $phone;
                    $_SESSION['logged_in'] = true;

                    // If user has an admin flag or role, set admin session and redirect to dashboard
                    if ($is_admin || strtolower($role) === 'admin') {
                        $_SESSION['admin_logged_in'] = true;
                        $conn->close();
                        header("Location: dashboard.php");
                        exit;
                    }

                    $conn->close();
                    header("Location: home.php");
                    exit;
                } else {
                    $errors[] = "Mot de passe incorrect.";
                }
            }
        }
    }

    if (!empty($errors)) {
        $msg = rawurlencode(implode(" - ", $errors));
        $conn->close();
        header("Location: Login.php?error=" . $msg);
        exit;
    }
} else {
    header("Location: Login.php");
    exit;
}
?>
