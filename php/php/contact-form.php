<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'php-mailer/src/PHPMailer.php';
require 'php-mailer/src/SMTP.php';
require 'php-mailer/src/Exception.php';



$mail = new PHPMailer(true);

try {
    // SMTP settings for hetpatel598@gmail.com
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'hetpatel598@gmail.com'; // Gmail used to send email
    $mail->Password = 'edus aqwa smjs otio'; // Replace with your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Get form data
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    if (!$email) {
        throw new Exception("Invalid email address");
    }

    // Send email
    $mail->setFrom('hetpatel598@gmail.com', 'Saphalya Corporation');
    $mail->addAddress($email, $name);
    $mail->addReplyTo('hetpatel598@gmail.com', 'Saphalya Corporation');
    $mail->addAddress('hetpatel598@gmail.com'); // also send to yourself

    $mail->isHTML(true);
    $mail->Subject = 'Thank You for Contacting Saphalya Corporation';

    $mail->Body = "
    <html>
    <body>
        <h2>Thank you, $name!</h2>
        <p>We have received your message and will get back to you soon.</p>
        <hr>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong> $message</p>
        <hr>
        <p>Regards,<br>Saphalya Corporation</p>
    </body>
    </html>";

    $mail->send();
    //database connection
    $conn = new mysqli('localhost','root','','kostbar');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO contact (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    // Execute the statement
    if ($stmt->execute()) {
         //Redirect with success message using JavaScript
        echo "<script>
            window.location.href = '../../index.html';
        </script>";

    } else {
        // Error
        echo "Error: " . $stmt->error;
    }
  
   


} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
}
?>
