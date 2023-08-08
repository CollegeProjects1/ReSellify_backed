<?php declare(strict_types=1);
require '../vendor/autoload.php';
require("../sendgrid-php.php");
// If not using Composer, uncomment the above line and
// download sendgrid-php.zip from the latest release here,
// replacing <PATH TO> with the path to the sendgrid-php.php file,
// which is included in the download:
// https://github.com/sendgrid/sendgrid-php/releases

function sendMail($to, $subject, $html, $content, $userName = "New To resellify")
{
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("dailydash155@gmail.com", "Resellify");
    $email->setSubject($subject);
    $email->addTo($to, $userName);
    $email->addContent("text/plain", $content);
    $email->addContent(
        "text/html",
        "<strong>`$html`</strong>"
    );
    $sendgrid = new \SendGrid("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");
    try {
        $response = $sendgrid->send($email);
        // print $response->statusCode() . "\n";
        // print_r($response->headers());
        // print $response->body() . "\n";
        if ($response) {
            return 1;
        } else {
            return 0;
            // echo "mail failed to send";
        }
    } catch (Exception $e) {
        echo 'Caught exception: ' . $e->getMessage() . "\n";
    }
}

?>