<?php
// Initialize the session
// Include config file
error_reporting(0);
require_once "config.php";
// Define variables and initialize with empty values
$username = $email = $new_password = $confirm_password = $ans = $code ="";
$new_password_err = $confirm_password_err = $email_err = $username_err = $ans_err = "";
$expired = 0;

    //the list of questions
    $array = array(
        "1" => "What is your mother's maiden name?",
        "2" => "What is your favorite pet's name?",
        "3" => "What city was your first job in?",
        "4" => "Where did you go to for 6th grade?",
        "5" => "Who was your 3rd grade teacher?",
        "6" => "What was your childhood nickname?"
    );
	if (isset($_GET["key"]) && isset($_GET["token"])) {
    //get the users selected question:
    $sql4 = "SELECT * FROM users WHERE email = '" . $_GET["key"] . "' ";
    $result4 = mysqli_query($link, $sql4);
    $question = mysqli_fetch_assoc($result4);
    $index = $question['ques'];
	
	
	$email = trim($_GET["key"]);
$key = $_GET["token"];
$curDate = date("Y-m-d H:i:s");

$query = mysqli_query($link,"SELECT * FROM password_reset_temp WHERE keyTo='".$key."' and email='".$email."';"
  );
  $row = mysqli_num_rows($query);
  if ($row==""){
	  $expired = 1;
	//header("location: login.php");
  }
   
  else {
	$row = mysqli_fetch_assoc($query);
	$expDate = $row['expD'];//echo $expDate;
	if ($expDate < $curDate){
		mysqli_query($link,"DELETE FROM password_reset_temp WHERE email='".$email."';");
		$expired = 1;
		
		//header("location: login.php");
	}
  }
	}
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$email = $_POST["email"];
	$sql4 = "SELECT * FROM users WHERE email = '" . $email . "' ";
    $result4 = mysqli_query($link, $sql4);
    $question = mysqli_fetch_assoc($result4);
    $index = $question['ques'];
	$key = $_POST["key"];
	$curDate = date("Y-m-d H:i:s"); //echo $curDate;
	$query = mysqli_query($link,"SELECT * FROM password_reset_temp WHERE keyTo='".$key."' and email='".$email."';"
  );
  $row = mysqli_num_rows($query);
  if ($row==""){
	  $expired = 1;
	//header("location: login.php");
  }
   
  else {
	$row = mysqli_fetch_assoc($query);
	$expDate = $row['expD'];//echo $expDate;
	if ($expDate < $curDate){
		mysqli_query($link,"DELETE FROM password_reset_temp WHERE email='".$email."';");
		$expired = 1;
		//header("location: login.php");
	}
  }
	
    // Check if username is valid
    if (empty(trim($_POST["username"])))
    {
        $username_err = "Please enter username.";
    }
    else
    {
        $username = trim($_POST["username"]);
        $query = mysqli_query($link, "SELECT * FROM users WHERE username='" . $username . "' AND email = '".$email."'");
        if (!$query)
        {
            die('Error: ' . mysqli_error($link));
        }
        if (mysqli_num_rows($query) == 0)
        {
            $username_err = "Username not found or Email is not registered.";
        }
        else{$username = trim($_POST["username"]);}
    }

	//Validate Answer
    if (empty(trim($_POST["answer"])))
    {
        $ans_err = "Please enter an answer.";
    }

    elseif (!(password_verify(strtolower(trim($_POST["answer"])) , $question['ans'])))
    {
        $ans_err = "Incorrect answer.";
    }
    else
    {
        $ans = trim($_POST["answer"]);
    }

    // Validate new password
    if (empty(trim($_POST["new_password"])))
    {
        $new_password_err = "Please enter the new password.";
    }
	else if ( password_verify(trim($_POST["new_password"]) ,  trim($question['password'])) )
	{
		$new_password_err = 'New password cannot be the same as before.';
	}	
    else if (!(
		preg_match('/[A-Za-z]/', trim($_POST["new_password"])) 
		&& preg_match('/[0-9]/', trim($_POST["new_password"])) 
		&& preg_match('/[A-Z]/', trim($_POST["new_password"]))
		&& preg_match('/[a-z]/', trim($_POST["new_password"]))
		))
	{
		$new_password_err = 'New password must contain a lowercase letter, uppercase letter, and a number.';
	}
    else if (strlen(trim($_POST["new_password"])) < 8 || strlen(trim($_POST["new_password"])) > 25)
    {
        $new_password_err = "New password must have atleast 8 characters and not exceed 25.";
    }
    else
    {
        $new_password = trim($_POST["new_password"]);
    }
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"])))
    {
        $confirm_password_err = "Please confirm the password.";
    }
    else
    {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password))
        {
            $confirm_password_err = "Password did not match.";
        }
    }
	$sql3 = "SELECT * FROM users WHERE email = '" . $email . "' ";
    $result3 = mysqli_query($link, $sql3);
    $basics = mysqli_fetch_assoc($result3);
    // Check input errors before updating the database
    if (empty($new_password_err) && empty($confirm_password_err) && empty($email_err) && empty($username_err) && empty($ans_err))
    {
		mysqli_query($link,"UPDATE users SET email = '".$email."' WHERE username = '".$username."'");
		mysqli_query($link,"UPDATE users SET email_verified_at = '".date('Y-m-d H:i:s')."' WHERE username = '".$username."'");
        // Prepare an update statement
        $sql = "UPDATE users SET password = ? WHERE username = ?";
        if ($stmt = mysqli_prepare($link, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_password, $username);
            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt))
            {
             
   // Password updated successfully. Destroy the session, and redirect to login page
				mysqli_query($link,"DELETE FROM password_reset_temp WHERE email='".$email."';");
                header("location: login.php");
            }
            else
            {
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
	
    // Close connection
    mysqli_close($link);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.2.0/zxcvbn.js"></script>
    <style>
        body{ font: 14px sans-serif; background-color: #e8f3fd;}
        .wrapper{ width: 360px; padding: 20px; margin-left: auto;
			margin-right: auto; margin-top:10%; }
			label:hover { transform: scale(1.2); }
a:hover { transform: scale(1.1); }
meter:hover { transform: scale(1.5); }
select:hover { transform: scale(1.1); }
input:hover { transform: scale(1.2); }
textarea:hover { transform: scale(1.2); }
    </style>
</head>
<body>
<?php if($expired == 1): ?>
<div class="wrapper">
<h2>Expired Link.</h2>
<a class=" btn btn-secondary " href="login.php">GO BACK TO LOGIN</a>
   </div>    
<?php endif; ?>
<?php if($expired == 0): ?>
    <div class="wrapper">
	
        <h2>Reset Password</h2>
	
        <p>Please fill out this form to reset your password.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
		<div class="form-group">
          <label>Question: <?php  echo nl2br(htmlspecialchars($array[$index])); ?></label>
          <input type="answer" name="answer" class="form-control <?php echo (!empty($ans_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $ans; ?>">
                <span class="invalid-feedback"><?php echo $ans_err; ?></span>
        </div>
		
		<div class="form-group">
		
          <label>Username</label>
          <input type="text" name="username" class="form-control 
			<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
          <span class="invalid-feedback"> <?php echo $username_err; ?> </span>
        </div>
		 
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" id = "new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
				<input type="checkbox" onclick="showF()">Show Password</input>
		  <script>
function showF() {
  var x = document.getElementById("new_password");
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
}
</script>
				<div class="container">
		  <br>
        <meter max="4" id="password-strength"></meter>
		
        <p id="password-strength-text"></p>
        <script type="text/javascript">
            var strength = {
              0: "Weakest",
              1: "Weak",
              2: "OK",
              3: "Good",
              4: "Strong"
            }
             
            var password = document.getElementById('new_password');
            var meter = document.getElementById('password-strength');
            var text = document.getElementById('password-strength-text');
 
            password.addEventListener('input', function() {
                var val = password.value;
                var result = zxcvbn(val);
 
                // This updates the password strength meter
                meter.value = result.score;
 
                // This updates the password meter text
                if (val !== "") {
                    text.innerHTML = "Password Strength: " + strength[result.score]; 
                } else {
                    text.innerHTML = "";
                }
            });
        </script>
    </div>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
		<input type="hidden" name="email" value="<?php echo $email;?>"/>
		<input type="hidden" name="key" value="<?php echo $key;?>"/>
		<div class="form-group">
                <input type="submit" name="submit" class="btn btn-primary" value="Submit">
                <a class="btn btn-link ml-2" href="login.php">Cancel</a>
            </div>
        </form>
		
    </div>    
	<?php endif; ?>
</body>
</html>
