<?php
	
  if(isset($_POST['verify']))
  {  
  $salt = $_POST['secret'];  
  $roll = $_POST['roll'];
  $spacer = "+";
  $proof = "SHA1 Hash: ".sha1($salt.$spacer.$roll);
  }
	
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
#verifyB{
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



</style>
</head>
<body>
<table>
  <tr>
  <td width="165">
  --AD SPOT--
  </td><td>
    <div id="active">Verify Dice Rolls</div>
	<br><center>-AD SPOT--</center><br>
        <div id="claimCont">		
            <form method="post">
			Secret: <input type="text" name="secret" size="45" placeholder="FD5sa1f2..." value="<?php echo $_POST['secret']; ?>"><br>
			Roll: <input type="text" name="roll" size="20" placeholder="23.74" value="<?php echo $_POST['roll']; ?>"><br>
			<input type="submit" id="verifyB" name="verify" value="Verify">
			</form>
			  <br>
			<?php echo $proof; ?>  
		</div>
    <div id="provFair">
	This tool will help you easily verify a roll. Simply paste in the secret and the roll and press verify. This will output the same 
	SHA1 hash that was displayed before you pressed the Roll Lo or Roll Hi button. <br><br>
	
	The outcome of the next roll is displayed as a hash before you place your wager. This means the server decides it will roll a 20 
	before it knows how much you are betting or what your target is. You can verify a roll wasn't changed by copying the "Hash of 
	next roll", then after playing that roll combine the "secret" and plus sign "+" and the roll "20" and perform a SHA1 hash. The 
	resulting hash will be the same as the "Hash of next roll" that was displayed before you played that game.<br><br>
	[secret]+[roll]=HASH<br>
Example: 9d45f162f6e735a1ee946ac1c4460526e3e7f2c2+43.47=61529ce3ee447392520fb6e4c59ba3ba3b4cb122
	</div></td>
	<td width="165">
	--AD SPOT--
</td></tr></table>

</body>
</html>