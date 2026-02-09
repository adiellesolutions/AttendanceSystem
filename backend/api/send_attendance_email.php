<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../libs/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/../libs/PHPMailer/SMTP.php";
require_once __DIR__ . "/../libs/PHPMailer/Exception.php";

function sendAttendanceEmail($name, $type, $time, $studentEmail, $guardianEmails) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ichellemc17@gmail.com'; // ✅ YOUR GMAIL
        $mail->Password   = 'laap xrpe lwli lowu';        // ✅ APP PASSWORD
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(
            'ichellemc17@gmail.com',
            'RFID Attendance System'
        );

        if ($studentEmail) {
            $mail->addAddress($studentEmail);
        }

        foreach ($guardianEmails as $email) {
            if ($email) $mail->addAddress($email);
        }

        $mail->isHTML(true);
        $mail->Subject = "RFID Attendance Notification";

        $mail->Body = "
            <h3>Attendance Update</h3>
            <p><strong>Student:</strong> {$name}</p>
            <p><strong>Action:</strong> {$type}</p>
            <p><strong>Time:</strong> {$time}</p>
        ";

        $mail->send();

    } catch (Exception $e) {
        // silently fail so RFID never breaks
    }
}
