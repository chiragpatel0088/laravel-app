<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists('/vendor/autoload.php'))
    require '/vendor/autoload.php';
else if (file_exists('../../../vendor/autoload.php'))
    require '../../../vendor/autoload.php';


class Mailer
{

    /* Checks if the domain actually exists that the email is being sent to */
    /* SOURCE 9/06/2020: http://www.joemarini.com/tutorials/tutorialpages/emaildomainexists.php#:~:text=Using%20PHP%2C%20you%20can%20check,type%20and%20a%20given%20host. */
    function CheckMX($email)
    {
        if (!is_null($email) && strlen($email) > 0) {
            $mailDomain = explode('@', $email)[1];
            if (checkdnsrr($mailDomain, "MX")) {
                // this is a valid email domain!
                return true;
            }
        }
        // this email domain doesn't exist! bad dog! no biscuit!
        return false;
    }

    function sendQuote($recipient, $quote_id)
    {
        global $database;
        $config = $database->getConfigs();

        $quote_details = $database->getQuoteDetails($quote_id)[0];
        $unified_id = $quote_details['unified_id'];

        $mail = new PHPMailer(true);

        try {

            // Check mail address has a valid host and is actually a email
            if (!$this->checkMX($recipient)) throw new Exception("Email does not exist.");
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) throw new Exception("Email not valid.");

            //Server settings
            $mail->SMTPDebug = 0;                                       // 2 for Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = EMAIL_HOSTS;                            // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = EMAIL_USERNAME;                         // SMTP username
            $mail->Password   = EMAIL_PASSWORD;                         // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('jobs@jaxxonconcretepumps.co.nz', 'Quotes Jaxxon Concrete Pumps');        // Must be same as Username above!
            //$mail->addAddress($detailer_email, $detailer_name);            // Add a recipient
            $mail->addAddress($recipient, "");     // Add a recipient
            $mail->addReplyTo('jobs@jaxxonconcretepumps.co.nz', 'NO REPLY');

            // Quote id padded
            $quote_number = 'JX-' . str_pad($unified_id, 6, "0", STR_PAD_LEFT);

            // Obsufucated id
            $obs_quote_id = $database->translateString($quote_id, "e");

            // Quote address
            $quote_address = $quote_details['job_addr_1'] . " " . $quote_details['job_addr_2'];
            $quote_address_2 = $quote_details['job_suburb'];
            $quote_address_3 = $quote_details['job_city'] . " " . $quote_details['job_post_code'];

            // Job type name
            $job_type_name = $database->getNameForJobTypeID($quote_details['job_type'])['type_name'];

            // Customer person name and company name
            $customer_names = $database->getCustomerNameFieldsByID($quote_details['customer_id']);

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = '[Jaxxon Concrete Pumps Notification] Quote - ' . $quote_number;
            $mail->Body    = '
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=us-ascii">
                <title>Spindle System Notification - Quote has been made for you</title>
                <style type="text/css">
                    .style3, table
                    {
                        font-size: 12px;
                        color: #333;
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-weight: normal;
                        line-height: 18px;
                    }
                    table th
                    {
                        width: 100px;	
                    }
                    .style9
                    {
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-size: 10px;
                        color: #333;
                    }
                    a:link,
                    a:visited,
                    a:hover
                    {
                        color: #0000FF ;
                    }
                </style>
            </head>
            <body>
                <table width="580" cellspacing="0" cellpadding="0">
                
                    <tr>
                        <td height="1" align="left" valign="top" bgcolor="#FFFFFF">
                            Hello ' . $customer_names['first_name'] . ',<br><br>
                            <img src="cid:580pixel_spacerGray.jpg" width="580" height="2">
                        </td>
                    </tr>
            
                    <tr>     
                        <td align="left" valign="top">
                            <br>
                            ' . 'Jaxxon Concrete Pumps has sent you a quote, click the job number link to view:&nbsp;<br><br>

                            <table border="0" cellpadding="2" cellspacing="2">
                                <tr valign="top"><th align="left">Job No.:&nbsp;&nbsp;</th><td><a href="' . $config['WEB_ROOT'] . 'customer_quote?id=' . $obs_quote_id . '">' . $quote_number . '</a></td></tr>                    
                                <tr valign="top"><th align="left">Date:&nbsp;&nbsp;</th><td>' . date('d/m/Y', strtotime($quote_details['job_date'])) . '</td></tr>                    
                                <tr valign="top"><th align="left">Address:&nbsp;&nbsp;</th><td>' . $quote_address . '</td></tr>
                                <tr valign="top"><th align="left">&nbsp;&nbsp;</th><td>' . $quote_address_2 . '</td></tr>
                                <tr valign="top"><th align="left">&nbsp;&nbsp;</th><td>' . $quote_address_3 . '</td></tr>                                
                            </table>   

                            <br>&nbsp;
                            <br>
                            Please <strong>ACCEPT</strong> or <strong>DECLINE</strong> using the buttons on the quote page.  
                        </td>
                    </tr>
                    
                    <tr>
                        <td height="1" align="left" valign="top" bgcolor="#FFFFFF">
                            <img src="cid:580pixel_spacerGray.jpg" width="580" height="2">
                        </td>
                    </tr>
                    <tr>
                        <td height="35" align="left" valign="top" bgcolor="#FFFFFF">
                            <p class="style9">
                                This is an automated notification from Spindle, please do not reply to this email.
                            </p>
                            <p class="style9" align="center">
                        </td>
                    </tr>
                </table>
            </body>';
            $mail->AltBody = 'You have a quote sent to you from Jaxxon Concrete Pumps. Click the link below to view the quote.'; // Backup message for non html email viewing
            $mail->send();
        } catch (Exception $e) {
            echo json_encode(array("status" => "error", "message" => "Error, please check the email is correct: <strong>" . $recipient . "</strong>"));
            return false;
        }

        $database->quoteWasSent($quote_id);
        echo json_encode(array("status" => "success", "message" => "Quote was sent to <strong>" . $recipient . "</strong>"));
        return true;
    }

    function sendOperatorJob($recipient, $job_id, $name, $title)
    {
        global $session;
        global $database;
        $config = $database->getConfigs();

        $job_details = $database->getJobDetails($job_id);
        // var_dump($job_id, "mailer.php job_id");
        $unified_id = $job_details['unified_id'];

        $mail = new PHPMailer(true);

        try {

            // Check mail address has a valid host and is actually a email
            if (!$this->checkMX($recipient)) throw new Exception("Email does not exist.");
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) throw new Exception("Email not valid.");

            //Server settings
            $mail->SMTPDebug = 0;                                       // 2 for Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = EMAIL_HOSTS;                            // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = EMAIL_USERNAME;                         // SMTP username
            $mail->Password   = EMAIL_PASSWORD;                         // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('jobs@jaxxonconcretepumps.co.nz', 'Jaxxon Concrete Pumps');        // Must be same as Username above!
            //$mail->addAddress($detailer_email, $detailer_name);            // Add a recipient
            $mail->addAddress($recipient, "");     // Add a recipient
            $mail->addReplyTo('jobs@jaxxonconcretepumps.co.nz', 'NO REPLY');

            // Job id padded
            $job_number = 'JX-' . str_pad($unified_id, 6, "0", STR_PAD_LEFT);

            // Obsufucated id
            $obs_job_id = $database->translateString($job_id, "e");

            // Job address
            $job_address = $job_details['job_addr_1'] . " " . $job_details['job_addr_2'];
            $job_address_2 = $job_details['job_suburb'];
            $job_address_3 = $job_details['job_city'] . " " . $job_details['job_post_code'];

            // Job type name
            $job_type_name = $database->getNameForJobTypeID($job_details['job_type'])['type_name'];

            // Customer person name and company name
            $customer_names = $database->getCustomerNameFieldsByID($job_details['customer_id']);

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = '[' . $title . ' Notification] Job Assigned - ' . $job_number;

            if ($title == "Linesman") {
                $mail->Body    = '
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=us-ascii">
                <title>Spindle System Notification - Job Assigned</title>
                <style type="text/css">
                    .style3, table
                    {
                        font-size: 12px;
                        color: #333;
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-weight: normal;
                        line-height: 18px;
                    }
                    table th
                    {
                        width: 100px;	
                    }
                    .style9
                    {
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-size: 10px;
                        color: #333;
                    }
                    a:link,
                    a:visited,
                    a:hover
                    {
                        color: #0000FF ;
                    }
                </style>
            </head>
            <body>
                <table width="580" cellspacing="0" cellpadding="0">
                
                    <tr>
                        <td height="1" align="left" valign="top" bgcolor="#FFFFFF">
                            Hello ' . $name . ',<br><br>
                            <img src="cid:580pixel_spacerGray.jpg" width="580" height="2">
                        </td>
                    </tr>
                    <tr>     
                        <td align="left" valign="top">
                            <br>
                            ' . 'You have been assigned a job, please click the link to go to the app and view the job details:&nbsp;<br><br>

                            <table border="0" cellpadding="2" cellspacing="2">
                                <tr valign="top"><th align="left">Job No.:&nbsp;&nbsp;</th><td><a href="' . $config['WEB_ROOT'] . 'operator_job?enc_id=' . $obs_job_id . '">' . $job_number . '</a></td></tr>                   
                                <tr valign="top"><th align="left">Date:&nbsp;&nbsp;</th><td>' . date('d/m/Y', strtotime($job_details['job_date'])) . '</td></tr>                    
                                <tr valign="top"><th align="left">Address:&nbsp;&nbsp;</th><td>' . $job_address . '</td></tr>
                                <tr valign="top"><th align="left">&nbsp;&nbsp;</th><td>' . $job_address_2 . '</td></tr>
                                <tr valign="top"><th align="left">&nbsp;&nbsp;</th><td>' . $job_address_3 . '</td></tr>                                
                            </table>   

                            <br>&nbsp;
                            <br>
                            
                        </td>
                    </tr>
                    
                    <tr>
                        <td height="1" align="left" valign="top" bgcolor="#FFFFFF">
                            <img src="cid:580pixel_spacerGray.jpg" width="580" height="2">
                        </td>
                    </tr>
                    <tr>
                        <td height="35" align="left" valign="top" bgcolor="#FFFFFF">
                            <p class="style9">
                                This is an automated notification from Spindle, please do not reply to this email.
                            </p>
                            <p class="style9" align="center">
                        </td>
                    </tr>
                </table>
            </body>';
            } else {
                $mail->Body    = '
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=us-ascii">
                <title>Spindle System Notification - Job Assigned</title>
                <style type="text/css">
                    .style3, table
                    {
                        font-size: 12px;
                        color: #333;
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-weight: normal;
                        line-height: 18px;
                    }
                    table th
                    {
                        width: 100px;	
                    }
                    .style9
                    {
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-size: 10px;
                        color: #333;
                    }
                    a:link,
                    a:visited,
                    a:hover
                    {
                        color: #0000FF ;
                    }
                </style>
            </head>
            <body>
                <table width="580" cellspacing="0" cellpadding="0">
                
                    <tr>
                        <td height="1" align="left" valign="top" bgcolor="#FFFFFF">
                            Hello ' . $name . ',<br><br>
                            <img src="cid:580pixel_spacerGray.jpg" width="580" height="2">
                        </td>
                    </tr>
                    <tr>     
                        <td align="left" valign="top">
                            <br>
                            ' . 'You have been assigned a job, please click the link to go to the app and view the job details:&nbsp;<br><br>

                            <table border="0" cellpadding="2" cellspacing="2">
                                <tr valign="top"><th align="left">Job No.:&nbsp;&nbsp;</th><td><a href="' . $config['WEB_ROOT'] . 'operator_job?enc_id=' . $obs_job_id . '">' . $job_number . '</a></td></tr>                    
                                <tr valign="top"><th align="left">Date:&nbsp;&nbsp;</th><td>' . date('d/m/Y', strtotime($job_details['job_date'])) . '</td></tr>                    
                                <tr valign="top"><th align="left">Address:&nbsp;&nbsp;</th><td>' . $job_address . '</td></tr>
                                <tr valign="top"><th align="left">&nbsp;&nbsp;</th><td>' . $job_address_2 . '</td></tr>
                                <tr valign="top"><th align="left">&nbsp;&nbsp;</th><td>' . $job_address_3 . '</td></tr>                                
                            </table>   

                            <br>&nbsp;
                            <br>
                            
                        </td>
                    </tr>
                    
                    <tr>
                        <td height="1" align="left" valign="top" bgcolor="#FFFFFF">
                            <img src="cid:580pixel_spacerGray.jpg" width="580" height="2">
                        </td>
                    </tr>
                    <tr>
                        <td height="35" align="left" valign="top" bgcolor="#FFFFFF">
                            <p class="style9">
                                This is an automated notification from Spindle, please do not reply to this email.
                            </p>
                            <p class="style9" align="center">
                        </td>
                    </tr>
                </table>
            </body>';
            }
            $mail->AltBody = 'You have a job sent to you from Jaxxon Concrete Pumps. Click the link below to view the job.'; // Backup message for non html email viewing

            $mail->send();
        } catch (Exception $e) {
            echo json_encode(array("status" => "error", "message" => "Error, please check the email is correct: <strong>" . $recipient . "</strong>"));
            return false;
        }

        echo json_encode(array("status" => "success", "message" => "Job was sent to <strong>" . $recipient . "</strong>"));
        return true;
    }

    function sendJob($recipient, $job_id, $name, $title)
    {
        global $session;
        global $database;
        $config = $database->getConfigs();

        $job_details = $database->getJobDetails($job_id);
        $unified_id = $job_details['unified_id'];

        $mail = new PHPMailer(true);

        $recipient_emails = explode(';', ltrim(rtrim(trim($recipient), ';'), ';'));

        // If no emails
        if (sizeof($recipient_emails) <= 0 || empty($recipient)) {
            echo json_encode(array("status" => "error", "message" => "Error: No email addresses to send to."));
            return false;
        }

        $emails_not_sent_to = array();
        $emails_sent_to = array();
        foreach ($recipient_emails as $email_destination) {
            // Check mail address has a valid host and is actually a email
            if (!$this->checkMX($email_destination)) {
                array_push($emails_not_sent_to, $email_destination);
                continue;
            } else {
                array_push($emails_sent_to, $email_destination);
                $mail->addAddress($email_destination, "");     // Add a recipient
            }
        }

        //Server settings
        $mail->SMTPDebug = 0;                                       // 2 for Enable verbose debug output
        $mail->isSMTP();                                            // Set mailer to use SMTP
        $mail->Host       = EMAIL_HOSTS;                            // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = EMAIL_USERNAME;                         // SMTP username
        $mail->Password   = EMAIL_PASSWORD;                         // SMTP password
        $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('jobs@jaxxonconcretepumps.co.nz', 'Jaxxon Concrete Pumps');        // Must be same as Username above!
        //$mail->addAddress($detailer_email, $detailer_name);            // Add a recipient
        $mail->addReplyTo('jobs@jaxxonconcretepumps.co.nz', 'NO REPLY');

        // Job id padded
        $job_number = 'JX-' . str_pad($unified_id, 6, "0", STR_PAD_LEFT);

        // Obsufucated id
        $obs_job_id = $database->translateString($job_id, "e");

        // Job address
        $job_address = $job_details['job_addr_1'] . " " . $job_details['job_addr_2'];
        $job_address_2 = $job_details['job_suburb'];
        $job_address_3 = $job_details['job_city'] . " " . $job_details['job_post_code'];

        // Job type name
        $job_type_name = $database->getNameForJobTypeID($job_details['job_type'])['type_name'];

        // Customer person name and company name
        $customer_names = $database->getCustomerNameFieldsByID($job_details['customer_id']);

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = '[Jaxxon ' . $title . ' Notification] Job Confirmed - ' . $job_number;
        $mail->Body    = '
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=us-ascii">
                <title>Spindle System Notification - Job Confirmed</title>
                <style type="text/css">
                    .style3, table
                    {
                        font-size: 12px;
                        color: #333;
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-weight: normal;
                        line-height: 18px;
                    }
                    table th
                    {
                        width: 100px;	
                    }
                    .style9
                    {
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-size: 10px;
                        color: #333;
                    }
                    a:link,
                    a:visited,
                    a:hover
                    {
                        color: #0000FF ;
                    }
                </style>
            </head>
            <body>
                <table width="580" cellspacing="0" cellpadding="0">
                
                    <tr>
                        <td height="1" align="left" valign="top" bgcolor="#FFFFFF">
                            Hello ' . $name . ',<br><br>
                            <img src="cid:580pixel_spacerGray.jpg" width="580" height="2">
                        </td>
                    </tr>
                    <tr>     
                        <td align="left" valign="top">
                            <br>
                            ' . 'Jaxxon Concrete Pumps has sent you a job, click the job number link to view:&nbsp;<br><br>

                            <table border="0" cellpadding="2" cellspacing="2">
                                <tr valign="top"><th align="left">Job No.:&nbsp;&nbsp;</th><td><a href="' . $config['WEB_ROOT'] . 'job_sheet?id=' . $obs_job_id . '">' . $job_number . '</a></td></tr>                    
                                <tr valign="top"><th align="left">Date:&nbsp;&nbsp;</th><td>' . date('d/m/Y', strtotime($job_details['job_date'])) . '</td></tr>                    
                                <tr valign="top"><th align="left">Address:&nbsp;&nbsp;</th><td>' . $job_address . '</td></tr>
                                <tr valign="top"><th align="left">&nbsp;&nbsp;</th><td>' . $job_address_2 . '</td></tr>
                                <tr valign="top"><th align="left">&nbsp;&nbsp;</th><td>' . $job_address_3 . '</td></tr>                                
                            </table>   

                            <br>&nbsp;
                            <br>
                            Please carefully check all the job details and immediately advise any variations to Jaxxon&#39;s at: <a href="mailto:sales@jaxxonconcretepumps.co.nz">sales@jaxxonconcretepumps.co.nz</a>
                        </td>
                    </tr>
                    
                    <tr>
                        <td height="1" align="left" valign="top" bgcolor="#FFFFFF">
                            <img src="cid:580pixel_spacerGray.jpg" width="580" height="2">
                        </td>
                    </tr>
                    <tr>
                        <td height="35" align="left" valign="top" bgcolor="#FFFFFF">
                            <p class="style9">
                                This is an automated notification from Spindle, please do not reply to this email.
                            </p>
                            <p class="style9" align="center">
                        </td>
                    </tr>
                </table>
            </body>';
        $mail->AltBody = 'You have a job sent to you from Jaxxon Concrete Pumps. Click the link below to view the job.'; // Backup message for non html email viewing
        $mail->send();

        if (sizeof($emails_not_sent_to) > 0) {
            echo json_encode(array("status" => "success", "message" => "Job was sent to <strong>" . implode("; ", $emails_sent_to) . "</strong>", "not_sent_to" => implode("; ", $emails_not_sent_to)));
        } else echo json_encode(array("status" => "success", "message" => "Job was sent to <strong>" . implode("; ", $emails_sent_to) . "</strong>"));
        return true;
    }


    function sendSiteInspection($job_id, $site_inspection_id, $assigned_operator)
    {
        global $session;
        global $database;
        $config = $database->getConfigs();


        $job_details = $database->getJobDetails($job_id);
        $operator_details = $database->getOperatorDetails($assigned_operator);
        $recipient = $operator_details['user_email'];
        $unified_id = $job_details['unified_id'];

        // Do not send site inspections to sales@jaxxonconcrete..
        if (strtolower($recipient) === 'sales@jaxxonconcretepumps.co.nz') return true;

        $mail = new PHPMailer(true);

        try {

            // Check mail address has a valid host and is actually a email
            if (!$this->checkMX($recipient)) throw new Exception("MX Record does not exist.");
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) throw new Exception("Email not valid.");

            //Server settings
            $mail->SMTPDebug = 0;                                       // 2 for Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = EMAIL_HOSTS;                            // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = EMAIL_USERNAME;                         // SMTP username
            $mail->Password   = EMAIL_PASSWORD;                         // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('jobs@jaxxonconcretepumps.co.nz', 'Jaxxon Concrete Pumps');        // Must be same as Username above!
            $mail->addAddress($recipient, "");     // Add a recipient
            $mail->addReplyTo('jobs@jaxxonconcretepumps.co.nz', 'NO REPLY');

            // Job id padded
            $job_number = 'JX-' . str_pad($unified_id, 6, "0", STR_PAD_LEFT);

            // Job address
            $job_address = $job_details['job_addr_1'] . " " . $job_details['job_addr_2'];
            $job_address_2 = $job_details['job_suburb'];
            $job_address_3 = $job_details['job_city'] . " " . $job_details['job_post_code'];

            // Job type name
            $job_type_name = $database->getNameForJobTypeID($job_details['job_type'])['type_name'];

            // Customer person name and company name
            $customer_names = $database->getCustomerNameFieldsByID($job_details['customer_id']);

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = '[Jaxxon Notification] Site Inspection Assigned - ' . $job_number;
            $mail->Body    = '
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=us-ascii">
                <title>Spindle System Notification - Job Confirmed</title>
                <style type="text/css">
                    .style3, table
                    {
                        font-size: 12px;
                        color: #333;
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-weight: normal;
                        line-height: 18px;
                    }
                    table th
                    {
                        width: 100px;	
                    }
                    .style9
                    {
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-size: 10px;
                        color: #333;
                    }
                    a:link,
                    a:visited,
                    a:hover
                    {
                        color: #0000FF ;
                    }
                </style>
            </head>
            <body>
                <table width="580" cellspacing="0" cellpadding="0">
                
                    <tr>
                        <td height="1" align="left" valign="top" bgcolor="#FFFFFF">
                            Hello ' . $operator_details['user_firstname'] . ',<br><br>
                            <img src="cid:580pixel_spacerGray.jpg" width="580" height="2">
                        </td>
                    </tr>
                    
                    <tr>     
                        <td align="left" valign="top">
                            <br>
                            ' . $session->userinfo['user_firstname'] . ' has assigned a site inspection to you, click the job number link to view:&nbsp;<br><br>

                            <table border="0" cellpadding="2" cellspacing="2">
                                <tr valign="top"><th align="left">Job No.:&nbsp;&nbsp;</th><td><a href="' . $config['WEB_ROOT'] . 'site_inspection?id=' . $site_inspection_id . '">' . $job_number . '</a></td></tr>                    
                                <tr valign="top"><th align="left">Date:&nbsp;&nbsp;</th><td>' . date('d/m/Y', strtotime($job_details['job_date'])) . '</td></tr>                    
                                <tr valign="top"><th align="left">Address:&nbsp;&nbsp;</th><td>' . $job_address . '</td></tr>
                                <tr valign="top"><th align="left">&nbsp;&nbsp;</th><td>' . $job_address_2 . '</td></tr>
                                <tr valign="top"><th align="left">&nbsp;&nbsp;</th><td>' . $job_address_3 . '</td></tr>                                
                            </table>   

                            <br>&nbsp;
                            <br>
                            Please check the site inspection form for the job address and date
                        </td>
                    </tr>
                    
                    <tr>
                        <td height="1" align="left" valign="top" bgcolor="#FFFFFF">
                            <img src="cid:580pixel_spacerGray.jpg" width="580" height="2">
                        </td>
                    </tr>
                    <tr>
                        <td height="35" align="left" valign="top" bgcolor="#FFFFFF">
                            <p class="style9">
                                This is an automated notification from Spindle, please do not reply to this email.
                            </p>
                            <p class="style9" align="center">
                        </td>
                    </tr>
                </table>
            </body>';
            $mail->AltBody = 'You have been assigned a site inspection by ' . $session->userinfo['user_firstname'] . '. Click the link below to view the site inspection.'; // Backup message for non html email viewing

            $mail->send();
        } catch (Exception $e) {
            /* echo json_encode(array("status" => "error", "message" => "Error, please check the email is correct: <strong>" . $recipient . "</strong>")); */
            return false;
        }

        return true;
        /* echo json_encode(array("status" => "success", "message" => "Site inspection email notification sent to <strong>" . $recipient . "</strong>")); */
    }
};

/* Initialize mailer object */
$mailer = new Mailer;
