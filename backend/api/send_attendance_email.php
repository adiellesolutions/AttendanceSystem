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

                // Convert backend type to formal attendance label
        if ($type == "entry") {
            $attendanceLabel = "Time In";
            $actionMessage = "Entry into the premises has been successfully recorded.";
        } elseif ($type == "exit") {
            $attendanceLabel = "Time Out";
            $actionMessage = "Exit from the premises has been successfully recorded.";
        } else {
            $attendanceLabel = ucfirst($type);
            $actionMessage = "Attendance activity has been recorded.";
        }



        $mail->Body = "
    <p>Good day,</p>

    <p>This is to formally notify that {$actionMessage}</p>

    <h3>Attendance Details</h3>
    <p><strong>Student Name:</strong> {$name}</p>
    <p><strong>Attendance Type:</strong> {$attendanceLabel}</p>
    <p><strong>Date & Time:</strong> {$time}</p>

    <p>Please keep this record for reference. In case of any discrepancy, kindly contact the school administration.</p>

    <br>
    <p><em>This is an automated notification from the Attendance Monitoring System.</em></p>
";


        $mail->send();

    } catch (Exception $e) {
        // silently fail so RFID never breaks
    }
}
