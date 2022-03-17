<?php

// Initialize the session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";
// Define variables and initialize with empty values
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
$sql3 = "SELECT * FROM users WHERE username = '" . trim($_SESSION["username"]) . "' ";
$result3 = mysqli_query($link, $sql3);
$basics = mysqli_fetch_assoc($result3);
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST")
{

   // Validate new password
    if (empty(trim($_POST["new_password"])))
    {
        $new_password_err = "Please enter the new password.";
    }
	else if ( password_verify(trim($_POST["new_password"]) ,  trim($basics["password"])) )
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

    // Check input errors before updating the database
    if (empty($new_password_err) && empty($confirm_password_err))
    {
        // Prepare an update statement
        $sql = "UPDATE users SET password = ? WHERE id = ?";

        if ($stmt = mysqli_prepare($link, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);

            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt))
            {
                // Password updated successfully. Destroy the session, and redirect to login page
                session_destroy();
                header("location: login.php");
                exit();
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
    <div class="wrapper">
        <h2>Reset Password</h2>
        <p>Please fill out this form to reset your password.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
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
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <a class="btn btn-link ml-2" href="welcome.php">Cancel</a>
            </div>
        </form>
    </div>    
</body>
</html>