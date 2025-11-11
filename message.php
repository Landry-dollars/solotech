<?php
session_start();

include 'db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $object = trim($_POST['object'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($email === '' || $object === '' || $message === '') {
        $errors[] = "Fill the form to send message";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Email";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, email, phone, password FROM user WHERE email = ? LIMIT 1");

        if ($stmt === false) {
            $errors[] = "Server Error.";
        } else {
            $stmt->bind_param("s",$email);
            $stmt->execute();
            $stmt->store_result();


            $sql = "INSERT INTO message (email, object, message) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $_SESSION['save_message_error'] = ['global' => 'Prepare Error.'];
                $conn->close();
                header('location: contact.php');
                exit;
            } 

            $stmt->bind_param("sss", $email, $object, $message);
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header('Location: contact.php?success=' . rawurldecode('Message sent successfully'));
                exit;
            } else {
                $stmt->close();
                $_SESSION['register_errors'] = ['global' => 'Impossible d\'envoyer le message.'];
                $conn->close();
                header('Location: contact.php');
                exit;
            }
            
            
        }
        
    }

    if (!empty($errors)) {
        $msg = rawurldecode(implode(" - ", $errors));
        $conn->close();
        header("Location: contact.php?error=" .$msg);
        exit;
    }

} else{
    header("Location: contact.php");
    exit;
}

?>