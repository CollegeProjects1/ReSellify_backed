<?php
# ------------------
# Create a campaign
# ------------------
# Include the Brevo library
// require_once(__DIR__ . "/APIv3-php-library/autoload.php");
require_once __DIR__ . '/vendor/autoload.php';
echo __DIR__;

function sendOtp($name, $subject, $fromName, $email, $htmlContent)
{
    require_once __DIR__ . '/vendor/autoload.php';

    // require_once(__DIR__ . "/APIv3-php-library/autoload.php");
    # Instantiate the client
    Sendinblue\Client\Configuration::getDefaultConfiguration()->setApiKey("api-key", "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");
    $api_instance = new Sendinblue\Client\Api\EmailCampaignsApi();
    $emailCampaigns = new \Sendinblue\Client\Model\CreateEmailCampaign();
    # Define the campaign settings
    $email_campaigns['name'] = $name;
    $email_campaigns['subject'] = $subject;
    $email_campaigns['sender'] = array("name" => $fromName, "email" => $email);
    $email_campaigns['type'] = "classic";
    # Content that will be sent
    $email_campaigns["htmlContent"] = $htmlContent;
    # Select the recipients
    $email_campaigns["recipients"] = array("listIds" => [2, 7]);
    # Schedule the sending in one hour
    $email_campaigns["scheduledAt"] = "2018-01-01 00:00:01";
    // );
# Make the call to the client
    try {
        $result = $api_instance->createEmailCampaign($emailCampaigns);
        print_r($result);
    } catch (Exception $e) {
        echo 'Exception when calling EmailCampaignsApi->createEmailCampaign: ', $e->getMessage(), PHP_EOL;
    }
}
sendOtp("OTP for signUp", "This is otp mail", "resellify", "19.shashank.p@gmail.com", "101010");
?>