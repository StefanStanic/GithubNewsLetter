<?php

include_once('includes/simple_html_dom.php');
require 'phpmailer/PHPMailerAutoload.php';
require_once 'includes/creds.php';

//init curl
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,'https://github.com/trending');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

$response = curl_exec($ch);
curl_close($ch);

$html = new simple_html_dom();
$html->load($response);

$githubRepos = array();

$subscriptionArray = ['vts.stefan.stanic@gmail.com'];

foreach ($html->find('.repo-list > li') as $repo) {
    $item['title'] =  trim($repo->find('div[class=d-inline-block] > h3 > a')[0]->plaintext);
    $item['link'] =  $repo->find('div[class=d-inline-block] > h3 > a')[0]->attr['href'];
    $item['description'] =  trim($repo->find('div[class=py-1] > p')[0]->plaintext);

    $githubRepos[] = $item;
}


$mailContent.= file_get_contents("mailtemplate/header.html");
foreach($githubRepos as $array => $repoItem)
{
    $mailContent.=
        'Repository name: <strong><a href="https://github.com'.$repoItem['link'].'" target="_blank">'.$repoItem['title'].'</a></strong><br>
         Description: '.$repoItem['description'].'<br><hr>';

}
$mailContent.= file_get_contents("mailtemplate/footer.html");

foreach ($subscriptionArray as $email){
    $mail = new PHPMailer;

    $mail->isSMTP();                                        // Set mailer to use SMTP
//    $mail->SMTPDebug = 4;                                   // Enable verbose debug output
    $mail->Host = 'smtp.gmail.com';                         // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                                 // Enable SMTP authentication
    $mail->Username = $username;                            // SMTP username
    $mail->Password = $password;                            // SMTP password
    $mail->SMTPSecure = 'tls';                              // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                      // TCP port to connect to

    $mail->setFrom('newsletter@stefke.info', 'stefke.info');
    $mail->addAddress($email);
    $mail->addReplyTo('noreply@stefke.info');

    $mail->isHTML(true);                             // Set email format to HTML

    $mail->Subject = 'GitHub Trending Repositories Daily';
    $mail->Body = $mailContent;
    if($mail->Send()){
        echo "Newsletter sent";
    }
    else{
        echo "Failed to send newsletter";
    }
}

?>