<?php declare(strict_types=1);

require 'vendor/autoload.php'; // If you're using Composer (recommended)
use SendGrid\Mail\From;
use SendGrid\Mail\To;
use SendGrid\Mail\Mail;

function mailer()
{
    $from = new From("dailydash155@gmail.com", "Example User");
    $to = new To(
        "19.shashank.p@gmail.com",
        "Example User",
        [
            'subject' => 'Subject'
        ]
    );
    $email = new Mail($from, $to);

    $email->setTemplateId("REDACTED");
    $sendgrid = new \SendGrid("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");
}

mailer();
?>