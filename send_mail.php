    <?php
    /**
     * ARR Hospital – Contact Form Mail Handler
     * -----------------------------------------
     * Receives POST data from the contact form, validates it,
     * and sends an email to the hospital's inbox using PHP mail().
     * Also sends a confirmation email back to the sender.
     *
     * NOTE: This uses PHP's built-in mail() function.
     * Your hosting server must have mail() enabled (most shared
     * hosting providers like Hostinger, GoDaddy, etc. support this).
     */

    header('Content-Type: application/json');

    // ── Configuration ──────────────────────────────────────
    $receiver_email = "support@shentechnology.com"; 
    $from_email     = "no-reply@arrhospital.in";  // Use your domain email
    $hospital_name  = "ARR Hospital";

    // ── Only accept POST requests ──────────────────────────
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode([
            "status"  => "error",
            "message" => "Invalid request method."
        ]);
        exit;
    }

    // ── Collect & Sanitize Input ───────────────────────────
    $name    = isset($_POST['name'])    ? trim(strip_tags($_POST['name']))    : '';
    $email   = isset($_POST['email'])   ? trim(strip_tags($_POST['email']))   : '';
    $phone   = isset($_POST['phone'])   ? trim(strip_tags($_POST['phone']))   : '';
    $subject = isset($_POST['subject']) ? trim(strip_tags($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

    // ── Validation ─────────────────────────────────────────
    if (empty($name) || empty($email) || empty($phone) || empty($subject) || empty($message)) {
        echo json_encode([
            "status"  => "error",
            "message" => "Please fill in all the required fields."
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "status"  => "error",
            "message" => "Please enter a valid email address."
        ]);
        exit;
    }

    // ══════════════════════════════════════════════════════════
    // STEP 1: Send Enquiry Email to Hospital
    // ══════════════════════════════════════════════════════════
    $full_subject = "ARR Hospital – New Contact Enquiry: $subject";

    $headers  = "From: $hospital_name Website <$from_email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    $safe_message = nl2br(htmlspecialchars($message));

    $emailBody = "
    <html>
    <body style='font-family: Arial, sans-serif; margin: 0; padding: 0;'>
        <div style='max-width: 600px; margin: 0 auto;'>
            <div style='background: #015fc9; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                <h2 style='color: #ffffff; margin: 0;'>New Contact Enquiry</h2>
                <p style='color: rgba(255,255,255,0.85); margin: 5px 0 0;'>ARR Hospital Website</p>
            </div>
            <div style='background: #f8f9fa; padding: 25px; border: 1px solid #e9ecef;'>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 10px 8px; font-weight: bold; color: #333; width: 100px; vertical-align: top;'>Name:</td>
                        <td style='padding: 10px 8px; color: #555;'>$name</td>
                    </tr>
                    <tr style='background: #fff;'>
                        <td style='padding: 10px 8px; font-weight: bold; color: #333; vertical-align: top;'>Email:</td>
                        <td style='padding: 10px 8px; color: #555;'><a href='mailto:$email'>$email</a></td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 8px; font-weight: bold; color: #333; vertical-align: top;'>Phone:</td>
                        <td style='padding: 10px 8px; color: #555;'><a href='tel:$phone'>$phone</a></td>
                    </tr>
                    <tr style='background: #fff;'>
                        <td style='padding: 10px 8px; font-weight: bold; color: #333; vertical-align: top;'>Subject:</td>
                        <td style='padding: 10px 8px; color: #555;'>$subject</td>
                    </tr>
                </table>
                <div style='margin-top: 15px; padding: 15px; background: #fff; border-radius: 6px; border: 1px solid #e0e0e0;'>
                    <strong style='color: #333;'>Message:</strong>
                    <p style='color: #555; line-height: 1.6; margin: 8px 0 0;'>$safe_message</p>
                </div>
            </div>
            <div style='background: #343a40; padding: 15px; text-align: center; border-radius: 0 0 8px 8px;'>
                <p style='color: rgba(255,255,255,0.6); margin: 0; font-size: 12px;'>This email was sent from the ARR Hospital website contact form.</p>
            </div>
        </div>
    </body>
    </html>";

    $mail_sent = @mail($receiver_email, $full_subject, $emailBody, $headers);

    if ($mail_sent) {

        // ══════════════════════════════════════════════════════════
        // STEP 2: Send Confirmation Email to the Sender
        // ══════════════════════════════════════════════════════════
        $confirm_subject = "Thank you for contacting ARR Hospital";

        $confirm_headers  = "From: $hospital_name <$from_email>\r\n";
        $confirm_headers .= "Reply-To: $from_email\r\n";
        $confirm_headers .= "MIME-Version: 1.0\r\n";
        $confirm_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $confirm_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $short_message = htmlspecialchars(substr($message, 0, 150)) . (strlen($message) > 150 ? '...' : '');

        $confirmBody = "
    <html>
    <body style='font-family: Arial, sans-serif; margin: 0; padding: 0;'>
        <div style='max-width: 600px; margin: 0 auto;'>
            <div style='background: #015fc9; padding: 30px; text-align: center; border-radius: 8px 8px 0 0;'>
                <h2 style='color: #ffffff; margin: 0;'>Thank You for Contacting Us!</h2>
            </div>
            <div style='background: #ffffff; padding: 30px; border: 1px solid #e9ecef;'>
                <p style='color: #333; font-size: 16px; line-height: 1.6;'>Dear <strong>$name</strong>,</p>
                <p style='color: #555; font-size: 15px; line-height: 1.6;'>
                    We have received your message and our team will review it shortly.
                    We typically respond within <strong>24 hours</strong> during business days.
                </p>
                <div style='background: #f8f9fa; padding: 15px 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #015fc9;'>
                    <p style='margin: 0 0 5px; color: #333; font-weight: bold;'>Your enquiry summary:</p>
                    <p style='margin: 0; color: #555;'><strong>Subject:</strong> $subject</p>
                    <p style='margin: 5px 0 0; color: #555;'><strong>Message:</strong> $short_message</p>
                </div>
                <p style='color: #555; font-size: 15px; line-height: 1.6;'>
                    If your matter is urgent, please call us directly at
                    <a href='tel:+918300646102' style='color: #015fc9; font-weight: bold;'>+91 83006 46102</a>.
                </p>
                <p style='color: #555; font-size: 15px; margin-top: 25px;'>
                    Warm regards,<br>
                    <strong style='color: #333;'>ARR Hospital Team</strong><br>
                    <span style='color: #888; font-size: 13px;'>Gynaecology &amp; Pediatric Care, Tuticorin</span>
                </p>
            </div>
            <div style='background: #343a40; padding: 15px; text-align: center; border-radius: 0 0 8px 8px;'>
                <p style='color: rgba(255,255,255,0.6); margin: 0; font-size: 12px;'>ARR Hospital | 4M, 183/4, Muthammal Colony, 3rd Street Extension, Tuticorin – 628002</p>
            </div>
        </div>
    </body>
    </html>";

        // Try to send confirmation (don't let it fail the whole response)
        @mail($email, $confirm_subject, $confirmBody, $confirm_headers);

        echo json_encode([
            "status"  => "success",
            "message" => "Thank you! Your message has been sent successfully. A confirmation email has been sent to your inbox. We will get back to you shortly."
        ]);

    } else {
        echo json_encode([
            "status"  => "error",
            "message" => "Sorry, there was a problem sending your message. Please try again or contact us directly at +91 83006 46102."
        ]);
    }
