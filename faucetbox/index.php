<?php

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'your_dbname');
define('DB_USER', 'db_username');
define('DB_PASS', 'your_db_password');

	try{
	$conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch(PDOException $e){
	echo $e->getMessage();
	die();
	}
	session_start();
	
	//custom parameters
	$api_key = "1234XYB"; //faucetbox API KEY
	$startBal = 300; //starting balance for users
	$reefAmount = 60; //referral amount
	$timeBetweenClaims = 1800; //wait time between claims in seconds
	require_once("ayah.php"); //Are You A Human Bot Protection insert your keys in the ayah_config file
	                          //Sign up for a free account at areyouahuman.com to get your keys
	require_once("faucetbox.php");
	
	$ayah = new AYAH();
	$reefer = $_GET['ref'];
	
	if (!$ipp = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
    {
      die('Invalid IP address.');
    }
	$timeRaw = time();
	$timeNewUser = time();
	$zero = 0;
	
	$claimMsg = "Welcome to Faucet+Dice";

		$currency = "BTC";
		$faucetbox = new Faucetbox($api_key, $currency);	
		$faucetBal = $faucetbox->getBalance();
		$getfaucetbal2 = $faucetBal["balance_bitcoin"];

	if(array_key_exists('start', $_POST))
{
		$time = time();
		$score = $ayah->scoreResult();
		if ($score)  
	{
		$userAddy = $_POST['bitad'];
		if($reefer == $userAddy){
		die("You Cant Refer Yourself");
		}
				
		//check if already in DB
		  $checkAddy = $conn->prepare("SELECT * FROM faucetbox WHERE addy = ?");
		  $checkAddy->execute(array($userAddy));
		  $numrowAddy = count($checkAddy->fetchAll());
		  if($numrowAddy > 0){
		  //returning visitor forward to game area
			$returnVisit = $conn->prepare("SELECT * FROM faucetbox WHERE addy = ?");
		    $returnVisit->execute(array($userAddy));
			$timeResult = $returnVisit->fetch(PDO::FETCH_OBJ);
			$timeDif = $time - $timeResult->time;
			$reeferPay = $timeResult->reefer;
			if($timeDif < $timeBetweenClaims){
			$message = "You must wait 30 minutes between plays";
			} else {
		    $setNewGame = $conn->prepare("UPDATE faucetbox SET bbb = ?, time = ? WHERE addy = ?");
			$setNewGame->execute(array($startBal, $time, $userAddy));
			//check if ref and send ref payment
			
		    $currency = "BTC";
		    $faucetbox = new Faucetbox($api_key, $currency);
			$countSt = strlen($reeferPay);
				if($countSt > 5){
				//this is a referral send ref payment
				$reefResult = $faucetbox->sendReferralEarnings($reeferPay, $reefAmount);
				}	
		  session_start();
		  $_SESSION['cow']=$userAddy;
		  header('Location: ../faucetboxgame');
		   }
		  
		  } else if($numrowAddy == 0){
		  //check for unique IP
		   $checkIpp = $conn->prepare("SELECT * FROM faucetbox WHERE ipp = ?");
		  $checkIpp->execute(array($ipp));
		  $numrowIpp = count($checkIpp->fetchAll());
		     if($numrowIpp > 0){
		     die("Duplicate IP Detected");
		     } else if($numrowIpp == 0){
		  $startNew = $conn->prepare("INSERT INTO faucetbox (addy, time, bbb, ipp, reefer) VALUES (?, ?, ?, ?, ?)"); 
		  $startNew->execute(array('$userAddy', '$timeNewUser', '$startBal', '$ipp', '$reefer'));
		  //new user registered forward to game area
		  //payout to refeer
		  
		  $currency = "BTC";
		  $faucetbox = new Faucetbox($api_key, $currency);
		  $reefResult2 = $faucetbox->sendReferralEarnings($reefer, $reefAmount);
		  session_start();
		  $_SESSION['cow']=$userAddy;
		  header('Location: ../faucetboxgame');
		     }
		  }
		
	} //end captcha
	else
	{
	$message = "Your Captcha Was Invalid";
	}
} //end if post start button

       
?>
<!DOCTYPE html>
<html>
<head>
<title>Faucet+Dice</title>
<meta name="description" content="Faucet+Dice is a free game you can play to earn real bitcoins!">

<meta name="keywords" content="Bitcoin, Game, Free, Technology, Finance, Computers, Education, Universities, Lifestyle, Downloads, Games">

<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Droid+Sans">
<style>
html { 
  background-color: #ffffff;
  margin: 0;
  padding: 0;
  color: #121212;
  font-family: "Droid Sans";
}

a {
	text-decoration: none;
	color: #5C8AE6;
}
#footer{        
    font-size: 10px; 
	color: #666666;
}
#hr{
	width: 100%;
	background-color: #5C8AE6;
	height: 2px;
}

#submit{
	float: right;
	height: 32px;
	border-radius: 3px;
	color: #ffffff;
	background-color: #5C8AE6;
}

#msg {
	display: block;
	font-weight: bold;
	color: #0033CC;
}

#register {
	margin-right: auto;
	margin-left: auto;
}

#addy {
	height: 28px;
	width: 200px;
	border: 1px solid #333333;
	border-radius: 5px;
}

#usrLgOut {
	height: 30px;
	width: 100px;
	border: 1px solid #333333;
	background-color: #5C8AE6;
	border-radius: 5px;
	font-weight: bold;
	color: #ffffff;
}
#usrDice {
	height: 30px;
	width: 100px;
	border: 1px solid #333333;
	background-color: #FF8533;
	border-radius: 5px;
	font-weight: bold;
	color: #ffffff;
}

#error {
	color: red;
}
#user{
    position: relative;
	left: 12px;
    display: inline-block;
    font-size: 24px;
}
#buttons{
    position: relative;
    float: right;
    right: 12px;
    display: inline-block;
}
#active{
    position: relative;
    margin-left: auto;
    margin-right: auto;
    display: block;
    padding: 12px;
    background-color: #e9f1e1;
    color: #559922;
    text-align: center;
    font-weight: bold;
    width: 75%;
}
#balance{
    position: relative;
    margin-left: auto;
    margin-right: auto;
    display: block;
    margin-top: 12px;
    padding: 12px;
    background-color: #f4f6f9;
    color: #a7bdd1;
    text-align: center;
    width: 75%;
}
#bprint{
    font-size: 24px;
    color: #40729a;
}
#claimCont, #reefCont{
    position: relative;
    margin-left: auto;
    margin-right: auto;
    display: block;
    margin-top: 12px;
    padding: 12px;
    background-color: #f2f2f2;
    color: #929292;
    text-align: center;
    width: 75%;
}
#balance2{
    position: relative;
    margin-left: auto;
    margin-right: auto;
    display: block;
    margin-top: 12px;
    padding: 12px;
    background-color: #f4f6f9;
    color: #a7bdd1;
    text-align: center;
    width: 260px;
}
#cashCont{
    position: relative;
    margin-left: auto;
    margin-right: auto;
    display: block;
    margin-top: 12px;
    padding: 12px;
    background-color: #f3e8f6;
    color: #b881c8;
    text-align: center;
    width: 75%;
}
#faqCont{
    position: relative;
    margin-left: auto;
    margin-right: auto;
    display: block;
    margin-top: 12px;
    padding: 12px;
    background-color: #f3e8f6;
    color: #b881c8;
    width: 75%;
}
#claimBtn, #cashBtn{
    position: relative;
	margin-left: auto;
	margin-right: auto;
    width: 450px;
    border: 1px solid #94aed5;
    border-radius: 3px;
    background-color: #f8fafd;
    color: #94aed5;
    font-weight: bold;
    padding: 12px;
}
#math{
    border: 1px solid #94aed5;
    border-radius: 3px;
    background-color: #f8fafd;
    color: #94aed5;
    font-weight: bold;
    padding: 3px;
	margin: 4px;
}
#rewardsCont{
   font-family: sans-serif; 
    font-size: 11px;
    font-weight: bold;
    text-align: center;
}

#bitad{
    position: relative;
    margin-left: auto;
    margin-right: auto;
    width: 425px;
    margin-top: 5px;
    border: 1px solid #94aed5;
    background-color: #f8fafd;
    border-radius: 3px;
    color: #94aed5;
    font-weight: bold;
    padding: 12px;
    display: block;
}
#prevComms{
  position: relative;
  margin-left: auto;
  margin-right: auto;
  font-size: 14px;
}
#funcaptcha{
  position: relative;
  margin-left: auto;
  margin-right: auto;
}
.green{
   color: #00B800;
}

</style>
</head>
<body>
<br><div id="user"><img src="logo.png"></div>
<div id="buttons">
<div id="balance2">FAUCET BALANCE<br><span id="bprint"><?php echo $getfaucetbal2; ?> BTC</span></div>
</div>
<br><br><center>--AD SPOT--</center><br>
    <div id="active"><?php if(isset($_SESSION['cashout'])){echo $_SESSION['cashout'];} else {echo $claimMsg;} ?></div>
	
    <div id="balance">BALANCE<br><span id="bprint">0.00000000 BTC</span></div>
        <div id="claimCont"><h3>Faucet+Dice is a free game where you can earn real bitcoins!</h3> 
		Just enter your bitcoin address below, fill out the captcha, and press Play. You can start a new game every 30 minutes. 
		You can withdrawal immediately to FaucetBox. 
		  </div> <br>
		<center><?php echo "<span id='error'>".$message."</span>"; echo "<span id='msg'>".$prizeMsg."</span>"; ?></center><br>
 <form method="post"><center>
    <?php  echo $ayah->getPublisherHTML(); ?>
	--AD SPOT--
<input type="text" name="bitad" id="bitad" size="65" placeholder="Your BTC Address"><br>
 <input type="submit" name="start" id="claimBtn" value="PLAY"><br><br></form>
 <table><tr><td>--AD SPOT--</td><td>

</td></tr></table>
 </center>
 <div id="cashCont">
 <h2>GETTING STARTED WITH BITCOIN</h2>
		Are you new to bitcoin? Not to worry, it's easy to get started these days. Bitcoin is quickly becoming the fastest, easiest and 
		cheapest way to send and receive money online. 
		<a href="http://coinbase.com">Open your own bitcoin wallet today, it's free!</a>
 </div>
 
 <div id="reefCont"><h3>Referral Program</h3>
 Refer another user and receive 100% referral commission on all their faucet claims!<br><br>
 Your referral URL:  <input type="text" value="http://yourwebsite.com/faucetbox/?ref=YOUR_BITCOIN_ADDRESS" size="52" onClick="this.select();" readonly>
  </div>
  <br>
   <br>
  <div id="faqCont">
  <center><h3>Rules & FAQs</h3></center>
  - More excellent information can go here<br>
  </div>
  
<br><br>
</body>
</html>