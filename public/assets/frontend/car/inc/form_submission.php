<?php
function send_response($status, $message, $http_code = 200)
{
  http_response_code($http_code);
  echo json_encode(['status' => $status, 'message' => $message]);
  exit;
}

// Validate request method
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // configs
  $action = $_POST['action'] ?? '';
  $recipient = "recipient@example.com";

  // Sanitize input
  $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
  $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
  $subject = htmlspecialchars(trim($_POST['subject'] ?? 'Contact Request'));
  $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
  $message = htmlspecialchars(trim($_POST['message'] ?? ''));

  // Validate input
  if (empty($name)) {
    $errors[] = 'Name is required.';
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
  }
  if (empty($phone)) {
    $errors[] = 'Phone is required.';
  }
  if (empty($message)) {
    $errors[] = 'Message cannot be empty.';
  }

  if (!empty($errors)) {
    send_response('error', $errors, 400);
  }

  // Build email
  if ($action === 'subscribe') {
    $email = $email;
    $subject = "Subject Subscribe Email";
    $to = $recipient ?? "recipient@example.com";
    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message = "Subscribe Email " . $email;
    $messageBody = "Email: $email<br>Message: $message";

    // Send email
    echo mail($to, $subject, $messageBody, $headers) ? 'success' : 'error';
  } else {
    $name = $name ?? '';
    $email = $email;
    $phone = $phone ?? '';
    $message = $message ?? '';
    $subject = "Subject Email";
    $to = $recipient ?? "recipient@example.com";
    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $messageBody = "Name: $name<br>Email: $email<br>Phone: $phone<br>Message: $message";

    // Send email
    echo mail($to, $subject, $messageBody, $headers) ? 'success' : 'error';
  }
} else if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  send_response('error', 'Invalid request method.', 405);
}
