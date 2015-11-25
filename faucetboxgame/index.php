<?php
	$conn = mysqli_connect("localhost", "db_username", "db_password", "database_name");
	if (mysqli_connect_errno()){
	echo "Connection to DB failed" . mysqli_connect_error();
	}
    session_start();
	
	//custom parameters
	$api_key = "1234XYB"; //faucetbox API KEY
	
	$userAddy = $_SESSION['cow'];
	require_once("faucetbox.php");
	$selfNav = mysqli_query($conn, "SELECT * FROM faucetbox WHERE addy = '$userAddy'");
	$rowNav = mysqli_num_rows($selfNav);
	$rowAssoc = mysqli_fetch_assoc($selfNav);
	$balance = $rowAssoc['bbb'];
	$reefer = $rowAssoc['reefer'];
	
	//auto cashout if bal over 9999
	if($balance > 9999){
	   	$currency = "BTC";
		$faucetbox = new Faucetbox($api_key, $currency);
		$result = $faucetbox->send($userAddy, $amount);
		  if($result["success"] === true){
		  $_SESSION['cashout'] = $result["html"];
		  //reset balance to zero
		  mysqli_query($conn, "UPDATE faucetbox SET bbb = 0 WHERE addy = '$userAddy'");
		  		header('Location: ../faucetbox');
	}
	}
					
	//redirect if not logged in 
	if($rowNav < 1){
	header('Location: ../faucetbox');
	} else {
	
	$diceMsg = "Welcome to Faucet+Dice";
	$calcBal = $balance / 100000000;
	
	if(isset($_POST['rollHi'])){
	$betAmt = $_POST['bet'];
	$probability = $_POST['multiplier'];
	if(!is_numeric($betAmt) || !is_numeric($probability)){
	$message = "Invalid Input";
	} else {
	//filter var
	$betAmt = filter_var($betAmt, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$probability = filter_var($probability, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$multi = 100 / $probability;
	$multi2 = $multi - 0.02;  //house edge
	$grossProfit = $betAmt * $multi2;
	$netProfit = $grossProfit - $betAmt;
	$dbPrize = $netProfit;
	$dbBet = $betAmt;
	$target = 100 / $multi;
		if($dbBet > $balance){
		$message = "Insufficient Funds<br>";
		} else if ($target > 97 || $target < 2){
		$message = "Win Chance must be between 2 - 97<br>";
		} else if ($dbBet < 1 || $dbBet > 5000){
		$message = "Bets must be between 1 - 5,000 Satoshi<br>";
		} else {
		$latestGame = 	 "SELECT * FROM faucetboxgames 
					 WHERE addy = '$userAddy' AND open = 0 
					 ORDER BY count DESC LIMIT 1";
			$latestQ = mysqli_query($conn, $latestGame);
			$latestR = mysqli_fetch_assoc($latestQ);
			$luckyNum = $latestR['roll'];
			$gmID = $latestR['gid'];
			$luckySecret = $latestR['salt'];
			$target2 = 100 / $multi;
		    $calcHiRoll = 100 - $target2;
			mysqli_query($conn, "UPDATE faucetboxgames SET ltgt = 2, bet = $dbBet, uuu = $calcHiRoll, open = 1 WHERE gid = '$gmID'");
			
			if($luckyNum > $calcHiRoll && $dbBet <= $balance){
			//user wins
			$diceMsg = "You Won +".sprintf('%.0F',$netProfit)." Satoshis!";
			//verify game was legit
			$vgQuery = mysqli_query($conn, "SELECT * FROM faucetboxgames WHERE gid = '$gmID'");
			$vgResult = mysqli_fetch_assoc($vgQuery);
			$vgBet = $vgResult['bet'];
			$vgBatb = $vgResult['batb'];
				if($vgBet > $vgBatb || $vgBet != $dbBet || $dbBet < 1){
				    
					die("A fatal error has occurred");
				} else {
				mysqli_query($conn, "UPDATE faucetboxgames SET profit = '$dbPrize' WHERE gid = '$gmID'");
				mysqli_query($conn, "UPDATE faucetbox SET bbb = bbb + '$dbPrize' WHERE addy = '$userAddy'");
			
				//display updated balance
				$balQuery = mysqli_query($conn, "SELECT bbb FROM faucetbox WHERE addy = '$userAddy'");
				$rowAssoc = mysqli_fetch_assoc($balQuery);
				$balance = $rowAssoc['bbb'];
	            $calcBal = $balance / 100000000;
				}
			} else if($luckyNum < $calcHiRoll && $dbBet <= $balance){
			//user loses
			$lossBet = $dbBet * -1;
			$diceMsg = "You Lost -".sprintf('%.0F',$betAmt)." Satoshis";
			//verify game was legit
			$vgQuery = mysqli_query($conn, "SELECT * FROM faucetboxgames WHERE gid = '$gmID'");
			$vgResult = mysqli_fetch_assoc($vgQuery);
			$vgBet = $vgResult['bet'];
			$vgBatb = $vgResult['batb'];
				if($vgBet > $vgBatb || $vgBet != $dbBet || $dbBet < 1){
				    die("A fatal error has occurred");
				} else {
			mysqli_query($conn, "UPDATE faucetbox SET bbb = bbb - '$dbBet' WHERE addy = '$userAddy'");
			mysqli_query($conn, "UPDATE faucetboxgames SET profit = '$lossBet' WHERE gid = '$gmID'");
				//display updated balance
				$balQuery = mysqli_query($conn, "SELECT bbb FROM faucetbox WHERE addy = '$userAddy'");
				$rowAssoc = mysqli_fetch_assoc($balQuery);
				$balance = $rowAssoc['bbb'];
	            $calcBal = $balance / 100000000;
				}
			} // ends if lost else
			else {
			$diceMsg = "An error occurred";
			}
		} // ends bet validate else
	} //ends is numeric else
} //ends post
	
	if(isset($_POST['rollLo'])){
	$betAmt = $_POST['bet'];
	$probability = $_POST['multiplier'];
	if(!is_numeric($betAmt) || !is_numeric($probability)){
	$message = "Invalid Input";
	} else {
	//filter var
	$betAmt = filter_var($betAmt, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$probability = filter_var($probability, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$multi = 100 / $probability;
	$multi2 = $multi - 0.02;
	$grossProfit = $betAmt * $multi2;
	$netProfit = $grossProfit - $betAmt;
	$dbPrize = $netProfit;
	$dbBet = $betAmt;
	$target = 100 / $multi;
	
	if($dbBet > $balance){
		$message = "Insufficient Funds<br>";
		} else if ($target > 97 || $target < 2){
		$message = "Win Chance must be between 2 - 97<br>";
		} else if ($dbBet < 1 || $dbBet > 5000){
		$message = "Bets must be between 1 - 5,000 Satoshi<br>";
		} else {
		$latestGame = 	 "SELECT * FROM faucetboxgames 
					 WHERE addy = '$userAddy' AND open = 0 
					 ORDER BY count DESC LIMIT 1";
			$latestQ = mysqli_query($conn, $latestGame);
			$latestR = mysqli_fetch_assoc($latestQ);
			$luckyNum = $latestR['roll'];
			$gmID = $latestR['gid'];
			$luckySecret = $latestR['salt'];
			$target2 =  100 / $multi;
			$userTarget =  100 / $multi;
			mysqli_query($conn, "UPDATE faucetboxgames SET ltgt = 1, bet = $dbBet, uuu = $userTarget, open = 1 WHERE gid = '$gmID'");
			
			if($luckyNum < $target2 && $dbBet <= $balance){
			//user wins
			$diceMsg = "You Won +".sprintf('%.0F',$netProfit)." Satoshis!";
			
			
			//verify game was legit
			$vgQuery = mysqli_query($conn, "SELECT * FROM faucetboxgames WHERE gid = '$gmID'");
			$vgResult = mysqli_fetch_assoc($vgQuery);
			$vgBet = $vgResult['bet'];
			$vgBatb = $vgResult['batb'];
				if($vgBet > $vgBatb || $vgBet != $dbBet || $dbBet < 1){
				    die("A fatal error has occurred");
				} else {
				mysqli_query($conn, "UPDATE faucetboxgames SET profit = '$dbPrize' WHERE gid = '$gmID'");
				mysqli_query($conn, "UPDATE faucetbox SET bbb = bbb + '$dbPrize' WHERE addy = '$userAddy'");
				
				//display updated balance
				$balQuery = mysqli_query($conn, "SELECT bbb FROM faucetbox WHERE addy = '$userAddy'");
				$rowAssoc = mysqli_fetch_assoc($balQuery);
				$balance = $rowAssoc['bbb'];
	            $calcBal = $balance / 100000000;
				}
			} else  if($luckyNum > $target2 && $dbBet <= $balance){
			//user loses
			$lossBet = $dbBet * -1;
			$diceMsg = "You Lost -".sprintf('%.0F',$betAmt)." Satoshis";
			$userTarget =  100 / $multi;
			
			//verify game was legit
			$vgQuery = mysqli_query($conn, "SELECT * FROM faucetboxgames WHERE gid = '$gmID'");
			$vgResult = mysqli_fetch_assoc($vgQuery);
			$vgBet = $vgResult['bet'];
			$vgBatb = $vgResult['batb'];
				if($vgBet > $vgBatb || $vgBet != $dbBet || $dbBet < 1){
				    die("A fatal error has occurred");
				} else {
				mysqli_query($conn, "UPDATE faucetbox SET bbb = bbb - '$dbBet' WHERE addy = '$userAddy'");
				mysqli_query($conn, "UPDATE faucetboxgames SET profit = $lossBet WHERE gid = '$gmID'");
				//display updated balance
				$balQuery = mysqli_query($conn, "SELECT bbb FROM faucetbox WHERE addy = '$userAddy'");
				$rowAssoc = mysqli_fetch_assoc($balQuery);
				$balance = $rowAssoc['bbb'];
	            $calcBal = $balance / 100000000;
				}
			} // ends if lost else
			else {
			$diceMsg = "An error occurred";
			}
		} // ends bet validate else
	} //ends is numeric else
} //ends post

  //generate roll id
    $gameid = uniqid();
  //generate salt
  $genSalt = time();
  $genSalt2 = mt_rand(1111111, 3333333);
  $genSalt3 = $genSalt2 / 1000;
  $genSalt4 = $genSalt3 * $genSalt;
  $salt = sha1($genSalt4);
  $spacer = "+";
   //generate roll 
	$pick = mt_rand(0, 10000);
	
	$pick2 = $pick / 100;
	$proof = sha1($salt.$spacer.$pick2);
  //check balance
  $verifyQuery = mysqli_query($conn, "SELECT * FROM faucetbox WHERE addy = '$userAddy'");
  $verifyResult = mysqli_fetch_assoc($verifyQuery);
  $verifyBalance = $verifyResult['bbb'];
	mysqli_query($conn, "INSERT INTO faucetboxgames (gid, addy, salt, roll, batb) VALUES ('$gameid', '$userAddy', '$salt', '$pick2', '$verifyBalance')");
		
	
	
	if(isset($_POST['cashout'])){
	    $cashQuery = mysqli_query($conn, "SELECT * FROM faucetbox WHERE addy = '$userAddy'") or die("fatal error.");
        $cashResult = mysqli_fetch_assoc($cashQuery);
        $amount = $cashResult['bbb'];
		if($amount > 29000){
		die("Stop hacking you hacker");
		} else if ($amount < 1){
		$diceMsg = "You need at least 1 satoshi to cashout";
		} else {
		$currency = "BTC";
		$faucetbox = new Faucetbox($api_key, $currency);
		$result = $faucetbox->send($userAddy, $amount);
		  if($result["success"] === true){
		  $_SESSION['cashout'] = $result["html"];
		  //reset balance to zero
		  mysqli_query($conn, "UPDATE faucetbox SET bbb = 0 WHERE addy = '$userAddy'");
		  		header('Location: ../faucetbox');
		  } else{
		  
		  $diceMsg = "Error".$result["html"];
		  
		  }
		
		}
	}
	
} //end redirect else	
	
?>
<!DOCTYPE html>
<html>
<head>
<title>Faucet+Dice</title>
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
    width: 90%;
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
    width: 90%;
}
#bprint{
    font-size: 24px;
    color: #40729a;
}
#claimCont{
    position: relative;
    margin-left: auto;
    margin-right: auto;
    display: block;
    margin-top: 12px;
    padding: 12px;
    background-color: #f2f2f2;
    color: #929292;
    text-align: center;
    width: 90%;
}
#cashout{
    width: 468px;
    border: 1px solid #94aed5;
    border-radius: 3px;
    background-color: #f8fafd;
    color: #94aed5;
    font-weight: bold;
    padding: 12px;
}
#multiplier{
    width: 50px;
    display: block-inline;
    border: 1px solid #94aed5;
    background-color: #f8fafd;
    color: #94aed5;
    margin-top: 5px;
    font-weight: bold;
    padding: 12px;
    border-top-left-radius: 3px;
    border-bottom-left-radius: 3px;
}
#profit{
    position: relative;
    left: -1px;
    width: 98px;
    display: block-inline;
    border: 1px solid #94aed5;
    background-color: #f8fafd;
    color: #94aed5;
    margin-top: 5px;
    font-weight: bold;
    padding: 12px;
    border-top-right-radius: 3px;
    border-bottom-right-radius: 3px;
}
#rollLo{
    width: 100px;
    border: 1px solid #94aed5;
    background-color: #f8fafd;
    color: #94aed5;
    margin-top: 15px;
    font-weight: bold;
    padding: 12px;
    border-top-left-radius: 3px;
    border-bottom-left-radius: 3px;
}
#rollHi{
    position: relative;
    left: -1px;
    width: 100px;
    border: 1px solid #94aed5;
    background-color: #f8fafd;
    color: #94aed5;
    margin-top: 15px;
    font-weight: bold;
    padding: 12px;
    border-top-right-radius: 3px;
    border-bottom-right-radius: 3px;
}
#bet{
    position: relative;
    margin-left: auto;
    margin-right: auto;
    width: 88px;
    margin-top: 5px;
    border: 1px solid #94aed5;
    background-color: #f8fafd;
    border-radius: 3px;
    color: #94aed5;
    font-weight: bold;
    padding: 12px;
    display: inline-block;
}
#doubleBtn{
    width: 20px;
	display: inline-block;
    border: 1px solid #94aed5;
    background-color: #f8fafd;
    color: #94aed5;
	font-size: 11px;
    padding: 10px;
	margin: 2px;
    border-top-left-radius: 3px;
    border-bottom-left-radius: 3px;
}
#halfBtn{
    width: 20px;
	display: inline-block;
    border: 1px solid #94aed5;
    background-color: #f8fafd;
    color: #94aed5;
	font-size: 11px;
    padding: 10px;
	margin: 2px;
	margin-left: -2px;
    border-top-right-radius: 3px;
    border-bottom-right-radius: 3px;
}
#tpLbl{
    position: relative;
    left: -30px;
    top: 20px;
	font-size: 11px;
}
#mdLbl{
    position: relative;
    left: -25px;
    top: 20px;
	font-size: 11px;
}
#btmLblL{
    position: relative;
	display: inline-block;
    left: -25px;
    top: 15px;
	font-size: 11px;
}
#btmLblR{
    position: relative;
	display: inline-block;
    top: 15px;
	font-size: 11px;
}
#gmLoss{
   color: #E60000;
}
#gmWin{
   color: #00B800;
}
#claimCont td{
    padding: 7px;
}
#provText{
   font-size: 12px;
}
.rollWin{
  color: #00B800;
}
.rollLose{
  color: #E60000;
}
#nextRoll{
   border: 2px solid #b881c8;
   border-radius: 4px;
   padding: 6px;
}
#provFair{
    position: relative;
    margin-left: auto;
    margin-right: auto;
    display: block;
    margin-top: 12px;
    padding: 12px;
    background-color: #f3e8f6;
    color: #393939;
    text-align: center;
    width: 90%;
}

#prevGames{
  position: relative;
  margin-left: auto;
  margin-right: auto;
  font-size: 14px;
}
#exAd{
 vertical-align: top;
}

</style>
</head>
<body>
<script>
function btcConvert(input){
 if (isNaN(input.value)){
 input.value = 0;
 }
 var multi = document.getElementById('multiplier').value;
 var multi2 = 100 / multi;
 var multi3 = multi2 - 0.02; 
 var betAmount = document.getElementById('bet').value;
 var grossProfit = multi3 * betAmount;
 var profit2 = grossProfit - betAmount;
 document.getElementById('profit').value = profit2.toFixed(0);
 var multi = document.getElementById('multiplier').value;
 document.getElementById('btmLblL').innerHTML = "Roll under " + multi;
 var rollHiC = 100 - multi;
 document.getElementById('btmLblR').innerHTML = "Roll over " + rollHiC;
}
window.onload = function() {
  var multiIn = document.getElementById('multiplier');
  btcConvert(multiIn);
};

function double(){
 var oba = document.getElementById('bet').value;
 var dbl = 2 * oba;
 document.getElementById('bet').value = dbl.toFixed(0);
 var multi = document.getElementById('multiplier').value;
 var multi2 = 100 / multi;
 var multi3 = multi2 - 0.02; 
 var betAmount = document.getElementById('bet').value;
 var grossProfit = multi3 * betAmount;
 var profit2 = grossProfit - betAmount;
 document.getElementById('profit').value = profit2.toFixed(0);
}

function half(){
 var oba = document.getElementById('bet').value;
 var dbl = oba / 2;
 document.getElementById('bet').value = dbl.toFixed(0);
 var multi = document.getElementById('multiplier').value;
 var multi2 = 100 / multi;
 var multi3 = multi2 - 0.02; 
 var betAmount = document.getElementById('bet').value;
 var grossProfit = multi3 * betAmount;
 var profit2 = grossProfit - betAmount;
 document.getElementById('profit').value = profit2.toFixed(0);
}

</script>
<script>
function noteLimit(element, stopAt)
{
    var max_chars = stopAt;

    if(element.value.length > max_chars) {
        element.value = element.value.substr(0, max_chars);
    }
}
</script>

<br><div id="user"><a href="../faucetbox"><img src="logo.png"></a></div><br><table width="95%"><tr><td width="165" id="exAd">

</td><td>
    <div id="active"><?php echo $diceMsg; ?></div><center><span id="error">Balances are not saved! Be sure to click cashout when you are done playing!</span></center>
    <div id="balance">BALANCE<br><span id="bprint"><?php echo sprintf('%.8F',$calcBal); ?> BTC</span><br>
	<form method="post"><input type="submit" name="cashout" id="cashout" value="Cashout To FaucetBox"></form>
	</div>
        <div id="claimCont"><?php echo $message; ?>
				
            <form method="post"><div id="tpLbl">Win % Chance | Profit on Win</div><br>
			<input type="text" id="multiplier" name="multiplier" value="<?php if(!isset($_POST['multiplier'])){ echo "49.5"; } else { echo $_POST['multiplier']; } ?>" placeholder="49.5" onchange="btcConvert(this); noteLimit(this, 4)" onkeyup="btcConvert(this); noteLimit(this, 4)" onkeydown="noteLimit(this, 4);"/>
			<input type="text" id="profit" readonly><br><div id="mdLbl">Bet Amount (1 - 5,000 Satoshi)</div><br>
			<input type="text" name ="bet" id="bet" value="<?php if(!isset($_POST['bet'])){ echo "200"; } else { echo $_POST['bet']; } ?>" onchange="btcConvert(this); noteLimit(this, 4)" onkeyup="btcConvert(this); noteLimit(this, 4)" onkeydown="noteLimit(this, 4);"/><span id="doubleBtn" onClick="double();" width="70">2x</span><span id="halfBtn" onClick="half();" width="70">/2</span>
			<br><div id="btmLblL"></div><div id="btmLblR"></div><br>
			<input type="submit" name="rollLo" id="rollLo" value="Roll Lo">
			<input type="submit" name="rollHi" id="rollHi" value="Roll Hi"></form></div>
<div id="provFair">
<span id="nextRoll">Hash of Next Roll - <?php echo $proof; ?></span> <br>
<h4>Previous Games</h4>
<table id="prevGames" width="85%">
<tr>
<td>Game ID</td>
<td>Secret</td>
<td>Target</td>
<td>Bet</td>
<td>Roll</td>
<td>Profit</td>
</tr>
<?php 
	
$queryHist ="SELECT * FROM faucetboxgames WHERE open = 1 AND addy = '$userAddy' ORDER BY count DESC LIMIT 25";
$resultH=mysqli_query($conn, $queryHist) or die (mysqli_error($conn));
while($outputsH=mysqli_fetch_assoc($resultH)){
    echo "<tr>";
	echo "<td>".$outputsH['count']."</td>";
	echo "<td>".$outputsH['salt']."</td>";
	if($outputsH['ltgt'] == 1){$updwn = "&lt;";} 
	if($outputsH['ltgt'] == 2){$updwn = "&gt;";}
	echo "<td>".$updwn.$outputsH['uuu']."</td>";
	echo "<td>".$outputsH['bet']."</td>";
	echo "<td>".$outputsH['roll']."</td>";
	$btcProfit = $outputsH['profit'] / 100000000;
		if($btcProfit > 0){$proColor="rollWin";} else {$proColor="rollLose";}
	echo '<td><span class="'.$proColor.'">'.sprintf('%.8F',$btcProfit).'</span></td>';
	echo "</tr>";
}	

mysqli_close($conn);

?>
</table><br><h3>Provably Fair</h3>
    The hash is a SHA1 hash of a random secret, and the roll outcome separated by a + symbol.
<br>[secret]+[roll]=HASH <br> Example:  9d45f162f6e735a1ee946ac1c4460526e3e7f2c2+43.47=61529ce3ee447392520fb6e4c59ba3ba3b4cb122 <br>
<a href="../verify" target="_blank">Verification Tool</a>
 </div>

</body>
</html>