<?php
	$conn = mysqli_connect("localhost", "db_username", "db_password", "database_name");
	if (mysqli_connect_errno()){
	echo "Connection to DB failed" . mysqli_connect_error();
	}
	if (session_status() == PHP_SESSION_NONE) {
    session_start();
	}
	
	//custom parameters
	$api_key = "1234XYB"; //faucetbox API KEY
	$startBal = 300; //starting balance for users
	$reefAmount = 300; //referral amount
	$timeBetweenClaims = 1800; //wait time between claims in seconds
	$recaptchaKey = "6Lsitekey"; //recaptcha site key
	$recaptchaSecret = "6Lsecretkey"; //recaptcha secret key
	
	require_once("faucetbox.php");
	mysqli_set_charset($conn,"utf8");
	if(isset($_GET['ref'])){
	$reefer = $_GET['ref'];
	$reefer = mysqli_real_escape_string($conn, $reefer);
	}
	$ipp = $_SERVER['REMOTE_ADDR'];
	$timeNewUser = time();
	$zero = 0;
	
	$claimMsg = "Welcome to Faucet+Dice";

		$currency = "BTC";
		$faucetbox = new Faucetbox($api_key, $currency);	
		$faucetBal = $faucetbox->getBalance();
		$getfaucetbal2 = $faucetBal["balance_bitcoin"];

	if(isset($_POST['g-recaptcha-response']))
{
		$capResponse = $_POST['g-recaptcha-response'];
		$pingCaptcha = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$capResponse&remoteip=$ipp");
		$jsonCaptcha = json_decode($pingCaptcha, TRUE);
		$captchaSuccess = $jsonCaptcha["success"];
		$time = time();
		if ($captchaSuccess == "true")
	{
		$userAddy = $_POST['bitad'];
		mysqli_set_charset($conn,"utf8");
		$userAddy = mysqli_real_escape_string($conn, $userAddy);
		if(isset($reefer)){
		  if($reefer == $userAddy){
		die("You Cant Refer Yourself");
		  }
		}
				
		//check if already in DB
		  $checkAddy = mysqli_query($conn, "SELECT * FROM faucetbox WHERE addy = '$userAddy'");
		  $numrowAddy = mysqli_num_rows($checkAddy);
		  if($numrowAddy > 0){
		  //returning visitor forward to game area
			$timeQuery = mysqli_query($conn, "SELECT * FROM faucetbox WHERE addy = '$userAddy'");
			$timeResult = mysqli_fetch_assoc($timeQuery);
			$timeDif = $time - $timeResult['time'];
			$reeferPay = $timeResult['reefer'];
			  
			  //check last time IP claimed
			  $timeQuery2 = mysqli_query($conn, "SELECT * FROM faucetbox WHERE ipp = '$ipp'");
			  $timeResult2 = mysqli_fetch_assoc($timeQuery2);
			  $timeDif2 = $time - $timeResult2['time'];
			  
			if($timeDif < $timeBetweenClaims || $timeDif2 < $timeBetweenClaims){
			$message = "You must wait 30 minutes between plays";
			} else {
		  mysqli_query($conn, "UPDATE faucetbox SET bbb = '$startBal', time = '$time' WHERE addy = '$userAddy'");
			//check if ref and send ref payment
			
		    $currency = "BTC";
		    $faucetbox = new Faucetbox($api_key, $currency);
			$countSt = strlen($reeferPay);
				if($countSt > 5){
				//this is a referral send ref payment
				$reefResult = $faucetbox->sendReferralEarnings($reeferPay, $reefAmount);
				}	
		  $_SESSION['cow']=$userAddy;
		  header('Location: ../faucetboxgame');
		  mysqli_close($conn);
		  }
		  
		  } else if($numrowAddy == 0){
		  //check for unique IP
		   $checkIpp = mysqli_query($conn, "SELECT * FROM faucetbox WHERE ipp = '$ipp'");
		   $numrowIpp = mysqli_num_rows($checkIpp);
		     if($numrowIpp > 0){
		     die("Duplicate IP Detected");
		     } else if($numrowIpp == 0){
		  mysqli_query($conn, "INSERT INTO faucetbox (addy, time, bbb, ipp, reefer) VALUES ('$userAddy', '$timeNewUser', '$startBal', '$ipp', '$reefer')");
		  //new user registered forward to game area
		  //payout to refeer
		  if(isset($_GET['ref'])){
		  $currency = "BTC";
		  $faucetbox = new Faucetbox($api_key, $currency);
		  $reefAmount = 60;
		  $reefResult2 = $faucetbox->sendReferralEarnings($reefer, $reefAmount);
		  }
		  $_SESSION['cow']=$userAddy;
		  header('Location: ../faucetboxgame');
		  mysqli_close($conn);
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
<script src='https://www.google.com/recaptcha/api.js'></script>
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
		<center><?php if(isset($message)){ echo "<span id='error'>".$message."</span>"; } else if(isset($prizeMsg)){ echo "<span id='msg'>".$prizeMsg."</span>"; } ?></center><br>
  <form method="post"><center>
    <div class="g-recaptcha" data-sitekey="<?php echo $recaptchaKey; ?>"></div>
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
