<?php

require 'connection/connection.php';
require 'function/function.php';
require 'settings/settings.php';
//require 'requirements/requirements.php';

session_start();  

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/6.9.1/src/Exception.php';
// The main PHPMailer class. 
require 'phpmailer/6.9.1/src/PHPMailer.php';
// SMTP class, needed if you want to use SMTP. */
require 'phpmailer/6.9.1/src/SMTP.php';
// email config with UiTM Mail Server
include("phpmailer/config/email_config.php"); // gmail configuration

require 'google-api/vendor/autoload.php';

if(isset($_POST['search']))
{
   $tagID= $_POST['tagID']; 
}
   
// Creating new google client instance
$client = new Google_Client();

$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/treecamus/app/index.php";
$client->setRedirectUri($base_url);

// Adding those scopes which we want to get (email & profile Information)
$client->addScope("email");
$client->addScope("profile");
   
if(isset($_GET['code']))
{
   $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

   if(!isset($token["error"]))
   {
      $client->setAccessToken($token['access_token']);

      // getting profile information
      $google_oauth = new Google_Service_Oauth2($client);
      $google_account_info = $google_oauth->userinfo->get();

      $id =  $google_account_info->id;
      $full_name =  $google_account_info->name;
      $email =  $google_account_info->email;
      $profile_pic =  $google_account_info->picture;

      $allowed = ['uitm.edu.my'];

      $data = ["id" =>  $id];
      // checking user already exists or not
      $get_user = "SELECT u.name, u.google_id, u.is_allow AS UserAllow , u.userID FROM `users` u WHERE u.`google_id`=:id";
      $stmt = $dbh->prepare($get_user);
      $stmt->execute($data);
      $rowCount = $stmt->rowCount();

      //COMPARE ROW WITH ROW FROM 2 TABLE
      $get_user_useraccess ="SELECT u.name, u.google_id, u.profile_image, u.is_allow AS UserAllow , ua.is_allow AS UserAccessAllow, u.userID, ua.roleID, r.roletitle
      FROM `users` u 
      JOIN useraccess ua ON u.userID = ua.userID  
      JOIN roles r ON r.roleID = ua.roleID
      WHERE u.`google_id`=:id";
      $stmt2 = $dbh->prepare($get_user_useraccess);
      $stmt2->execute($data);
      $rowCount2 = $stmt2->rowCount();

      if($rowCount > 0)
      {
         $user = $stmt2->fetch(PDO::FETCH_ASSOC);
         if($user['UserAllow'] == 1 && $user['UserAccessAllow'] == 1)
         {
            $_SESSION['login_id'] = $id; 
            $_SESSION['user_id'] = $user['userID'];
            $_SESSION['userislogged'] = 1;
            $_SESSION['google_id'] = $user['google_id'];
            $_SESSION['usertype'] = $user['roleID'];
            $_SESSION['userfullname'] = $user['name'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? '';
            header('Location: /treecamus/dashboard');
            exit;
         }
         else
         {
            header("Location: /treecamus/app/index.php?error");
            exit;
         }
      }
      else
      {   
         $usertoken= getToken(32);
         $data = ["id" => $id, "full_name" => $full_name, "email" => $email, "profile_pic" => $profile_pic, "usertoken" => $usertoken];
         $insertUser = "INSERT INTO `users`(`google_id`,`name`,`email`,`profile_image`,`is_allow`,`userstoken`) VALUES(:id,:full_name,:email,:profile_pic, 0, :usertoken)";
         $stmt1 = $dbh->prepare($insertUser);
         $stmt1->execute($data);
         if($insertUser)
         {     
            $data1 = ["id" =>  $id];
            $get_user1 = "SELECT u.name, u.google_id, u.is_allow AS UserAllow , u.userID FROM `users` u WHERE u.`google_id`=:id";
            $stmt1 = $dbh->prepare($get_user1);
            $stmt1->execute($data1);
            $rowCount1 =$stmt1->rowCount();

            if($rowCount1 > 0)
            {
               $user1 =$stmt1->fetch(PDO::FETCH_ASSOC);

               if($user1['UserAllow'] == 1)
               {
                  $_SESSION['login_id'] =$id; 
                  header('Location: /treecamus/dashboard');
                  exit;
               }
               else
               {
                  $notify=$user1['UserAllow'];

                  if((isset($notify) && $notify == 0))
                  {            
                     $mail = new PHPMailer(true);            
                     $mail->isSMTP();$mail->SMTPDebug = SMTP_DEBUG;                                                                              
                    $mail->Host = SMTP_HOST; 
                    $mail->SMTPAutoTLS = SMTP_AUTO_TLS;                                
                    $mail->SMTPAuth = SMTP_AUTH; 
                    $mail->SMTPSecure = SMTP_SECURE;                                
                    $mail->Port = SMTP_PORT;                                   
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->setFrom(
                        $_ENV['MAIL_FROM_ADDRESS'] ?? 'ugcdms@uitm.edu.my', 
                        $_ENV['MAIL_FROM_NAME'] ?? 'ugc'    );
                     $mail->Subject = 'New uitm user requested to access the system. Please give a permission and set the role for them!';
                     $mail->isHTML(true);

                     $sql2 = "SELECT u.name, u.email FROM users u JOIN useraccess ua ON u.userID=ua.userID WHERE ua.roleID=2";
                     $stmt =$dbh->prepare($sql2);$stmt->execute();
                     $rowCount =$stmt->rowCount();
                     if($rowCount > 0)
                     {
                        while($d =$stmt->fetch(PDO::FETCH_ASSOC))
                        {
                           $protocol_https = filter_input(INPUT_SERVER, 'HTTPS', FILTER_SANITIZE_STRING);
                           $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL);$request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
                           if(strlen($request_uri) == 0)
                           {
                              $request_uri = filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_URL);$query_string = filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_URL);
                              if($query_string)
                              {
                                    $request_uri .= '?' .$query_string;
                              }
                           }

                           $full_url = ($protocol_https ? 'https' : 'http') . '://' . $host .$request_uri;

                           $body = "There are new user requested to access the system. The user detail: <br /><br />
                           Username: <b>".$full_name."</b><br />
                           User email: <b>".$email."</b><br /><br />
                           Website Link: <a href=".$full_url."> TreeCaMUs</a> ";
                           
                           $mail->msgHTML($body);$mail->addAddress($d['email'],$d['name']);

                           if($mail->send())$status = "success";
                           else
                                 $status = "failed";
                        }
                     }
                     header("Location: /treecamus/app/index.php?error");   
                  } 
               }
            }
            exit;
         }
         else
         {
            echo "Sign up failed! (Something went wrong).";
         }
      } 
   }
   else
   {
      header('Location: /treecamus/app/index.php');
      exit;
   }
}

?>