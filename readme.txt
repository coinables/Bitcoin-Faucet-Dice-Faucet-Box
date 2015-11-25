FaucetDice
----------
FaucetDice is a bitcoin faucet script that is also a fully functional dice game. The user can cashout using faucetbox. 
Includes a faucet referral system built-in and funcaptcha for anti-bot. Just need to update the API keys in the 
two index.php files. 

This script uses PHP, faucetbox API, and funcaptcha for anti-bot protection. 

You will need to sign up with FaucetBox and Funcaptcha to obtain API Keys. 

1. Download the files in the repository
2. Set up a databsae on your webserver
3. Open up the index.php file in both directories (facuetbox and faucetboxgame). 
Update your database login name on the 1st line of these two pages.
Also insert your API Keys below under the custom parameters
This is where you can also set claim amounts, referral amounts and time between claims.
4. Import the SQL database file into your databse using PHPmyAdmin or similar database manager.
5. The landing page for visitors should be index.php wihtin the faucetbox directory