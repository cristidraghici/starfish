<?php
if (!class_exists('starfish')) { die(); }

class contact
{
        function send()
        {
                $name = post('name');
                $email_address = post('email');
                $phone = post('phone');
                $message = post('message');

                if( empty($name) || empty($email_address) || empty($phone) || empty($message) || !filter_var($email_address,FILTER_VALIDATE_EMAIL) )
                {
                        echo "No arguments Provided!";
                        return false;
                }

                // Create the email and send the message
                $to = 'contact@starfish.ml'; // Add your email address inbetween the '' replacing yourname@yourdomain.com - This is where the form will send a message to.
                $email_subject = "Website Contact Form:  $name";
                $email_body = "You have received a new message from your website contact form.\n\n"."Here are the details:\n\nName: $name\n\nEmail: $email_address\n\nPhone: $phone\n\nMessage:\n$message";
                $headers = "From: noreply@starfish.ml\n"; // This is the email address the generated message will be from. We recommend using something like noreply@yourdomain.com.
                $headers .= "Reply-To: $email_address";	

                @mail($to,$email_subject,$email_body,$headers);
                return true;
        }
}

?>