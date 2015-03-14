<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/25/14
 * Time: 11:35 AM
 */

namespace Velacolib\Utility;

use Zend\Mail;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\Mvc\Controller\AbstractActionController;
use Velacolib\Utility\Utility;
use Zend\Validator\File\MimeType;

class MailUtility extends AbstractActionController {

    public static $option;
    public static $servicelocator;

    public static function getSM()
    {
        return self::$servicelocator;
    }

    public static function setSM($val)
    {
        self::$servicelocator = $val;
    }

    public static function sendEmail($templateName, $data, $subject, $receiveEmail, $smtp = true)
    {
        // setup SMTP options
        $config = Utility::getConfig();
        $options = new Mail\Transport\SmtpOptions(array(
            'name' => 'localhost',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'connection_class' => 'login',
            'connection_config' => array(
                'username' => $config['emailId'],
                'password' => $config['emailPassword'],
                'ssl' => 'tls',
            ),
        ));

        if ($smtp) {
            $senderEmail = $config['emailId'];
            $senderName = $config['emailId'];
        } else {
            $senderEmail = $config['emailId'];
            $senderName = 'Kaffa - Coffee & more';
        }

        $render = self::$servicelocator->get('ViewRenderer');
        $content = $render->render('email/' . $templateName, array('data' => $data));

// make a header as html
        $html = new MimePart($content);
        $html->type = "text/html";
        $body = new MimeMessage();
        $body->setParts(array($html,));

// instance mail
        $mail = new Mail\Message();
        $mail->setBody($body); // will generate our code html from template.phtml
        $mail->setFrom($senderEmail, $senderName);
        $mail->setTo($receiveEmail);
        $mail->setSubject($subject);

        if ($smtp) $transport = new Mail\Transport\Smtp($options);
        else {
            $transport = new Mail\Transport\Sendmail();
        }
        $status = $transport->send($mail);
        return $status;

//        $transport = new Mail\Transport\Smtp($options);
//        $transport->send($mail);
    }

    public static function sendMailAttachment($data,$fileName,$subject, $receiveEmail, $smtp = true){


        // setup SMTP options
        $config = Utility::getConfig();
        $options = new Mail\Transport\SmtpOptions(array(
            'name' => 'localhost',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'connection_class' => 'login',
            'connection_config' => array(
                'username' => $config['emailId'],
                'password' => $config['emailPassword'],
                'ssl' => 'tls',
            ),
        ));

        if ($smtp) {
            $senderEmail = $config['emailId'];
            $senderName = $config['emailId'];
        } else {
            $senderEmail = $config['emailId'];
            $senderName = 'Kaffa - Coffee & more';
        }


//        $render = self::$servicelocator->get('ViewRenderer');
        //$content = $render->render('email/' . $templateName, array('data' => $data));
        $content = fopen($data,'r');

// make a header as html
        $html = new MimePart($content);
        $html->type = \Zend\Mime\Mime::TYPE_OCTETSTREAM;
        $html->filename = $fileName;
        $html->disposition = \Zend\Mime\Mime::DISPOSITION_INLINE;
        $body = new MimeMessage();
        $body->setParts(array($html,));

// instance mail
        $mail = new Mail\Message();
        $mail->setBody($body); // will generate our code html from template.phtml
        $mail->setFrom($senderEmail, $senderName);
        $mail->setTo($receiveEmail);
        $mail->setSubject($subject);

        if ($smtp) $transport = new Mail\Transport\Smtp($options);
        else {
            $transport = new Mail\Transport\Sendmail();
        }
      $transport->send($mail);


    }
} 