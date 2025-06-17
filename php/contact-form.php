<?php
namespace PortoContactForm;

session_cache_limiter('nocache');
header('Expires: ' . gmdate('r', 0));
header('Content-type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'php-mailer/src/PHPMailer.php';
require 'php-mailer/src/SMTP.php';
require 'php-mailer/src/Exception.php';

$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'het@saphalyacorp.com';
    $mail->Password   = 'Het@2210';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Form data
    $form_name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $contact_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $contact_message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    // Validate inputs
    if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    if (empty($form_name) || empty($contact_message)) {
        throw new Exception('Name and message are required');
    }

    // Anti-spam checks
    if (preg_match('/http|www|\.com|\.org|\.net/i', $contact_message) || // Block URLs
        strtoupper($contact_message) === $contact_message || // Block all-caps
        strlen($contact_message) > 500) { // Limit message length
        throw new Exception('Invalid message content');
    }

    // reCAPTCHA validation
    $recaptcha_secret = '6Lc_TwcrAAAAABv8wEjOqNUSUzNW3NVup9zPMHyK'; // Your secret key
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
    $recaptcha = json_decode($verify);
    if (!$recaptcha->success) {
        throw new Exception('CAPTCHA verification failed');
    }

    // Recipients
    $mail->setFrom('het@saphalyacorp.com', 'Saphalya Corporation');
    $mail->addAddress($contact_email, $form_name);
    $mail->addReplyTo('het@saphalyacorp.com', 'Saphalya Corporation');
    $mail->addAddress('het@saphalyacorp.com');
$servername = "localhost"; // Change if needed
$username = "root"; // Change if needed
$password = ""; // Change if needed
$database = "kostbar"; // Change to your database name
    // Database
    // $servername = "127.0.0.1:3306";
    // $username = "u768511311_saphalya_corp";
    // $password = "Saphlya@2210";
    // $database = "u768511311_saphalya";

    $conn = mysqli_connect($servername, $username, $password, $database);
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    $sql = "INSERT INTO `contact` (`name`, `email`, `message`) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $form_name, $contact_email, $contact_message);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Thank You for Contacting Saphalya Corporation';
    $mail_body = '<html>
    <body style="font-family: Open Sans, Helvetica, Arial, sans-serif; background-color: #F2F2F2;">
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; background: #FFFFFF; border-radius: 10px;">
            <img src="https://saphalyacorp.com/img/demos/industry-factory/logo-light.png" alt="Saphalya Corporation" style="max-width: 100%;">
            <h2 style="color: #4F4F4F;">Thank You for Reaching Out!</h2>
            <p style="color: #4F4F4F;">Dear ' . htmlspecialchars($form_name) . ',</p>
            <p style="color: #4F4F4F;">We’ve received your message and will get back to you soon.</p>
            <ul style="color: #4F4F4F;">
                <li><strong>Name:</strong> ' . htmlspecialchars($form_name) . '</li>
                <li><strong>Email:</strong> ' . htmlspecialchars($contact_email) . '</li>
                <li><strong>Message:</strong> ' . htmlspecialchars($contact_message) . '</li>
            </ul>
            <p style="color: #4F4F4F;">Best regards,<br>Saphalya Corporation Team</p>
        </div>
        <div style="text-align: center; font-size: 12px; color: #4F4F4F; padding: 10px;">
            © 2024 Saphalya Corporation. All rights reserved.
        </div>
    </body>
    </html>';
    $mail->Body = $mail_body;

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>