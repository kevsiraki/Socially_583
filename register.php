<?php

   if(!isset($_SERVER['HTTP_REFERER'])){
    // redirect them to your desired location
    header('location: login.php');
    exit;
}

// Include config file
require_once "config.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Define variables and initialize with empty values
$username = $password = $confirm_password = $email = $firstname = $lastname = $dob = $ans = "";
$username_err = $password_err = $confirm_password_err = $captchaerror = $email_err = $fname_err = $lname_err =$captcha_err = $ans_err = $ques_err =  "";
$rows = 0;

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    //get/validate basic info:
    if (empty(trim($_POST["firstname"])))
    {
        $fname_err = "Please enter a first name.";
    }
	else if (strlen(trim($_POST["firstname"])) < 3 || strlen(trim($_POST["firstname"])) > 25)
    {
        $fname_err = "First name must have atleast 2 characters and not exceed 25.";
    }
    elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', trim($_POST["firstname"])))
    {
        $fname_err = "First name can only contain letters, numbers, and underscores/dashes.";
    }
    else
    {
        $firstname = trim($_POST["firstname"]);
    }

    if (empty(trim($_POST["lastname"])))
    {
        $lname_err = "Please enter a last name.";
    }
	else if (strlen(trim($_POST["lastname"])) < 3 || strlen(trim($_POST["lastname"])) > 25)
    {
        $lname_err = "Last name must have atleast 2 characters and not exceed 25.";
    }
    else if (!preg_match('/^[a-zA-Z0-9_-]+$/', trim($_POST["lastname"])))
    {
        $lname_err = "Last name can only contain letters, numbers, and underscores/dashes.";
    }
    else
    {
        $lastname = trim($_POST["lastname"]);
    }

    $dob = trim($_POST["dob"]);

    // Validate username
    if (empty(trim($_POST["username"])))
    {
        $username_err = "Please enter a username.";
    }
    else if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"])))
    {
        $username_err = "Username can only contain letters, numbers, and underscores.";
    }
	else if(count(array_count_values(str_split(trim($_POST["username"])))) == 1) {
		$username_err= "Username cannot contain all the same character.";
	}
	else if (strlen(trim($_POST["username"])) < 8 || strlen(trim($_POST["username"])) > 25)
    {
        $username_err= "Username must have atleast 8 characters and not exceed 25.";
    }
    else
    {
        // Prepare a select statement
        $sql = "SELECT email FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = trim($_POST["username"]);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt))
            {
                /* store result */
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1)
                {
                    $username_err = "This username is already taken.";
                }
                else
                {
                    $username = trim($_POST["username"]);
                }
            }
            else
            {
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    if (empty(trim($_POST["email"])))
    {
        $email_err = "Please enter an email.";
    }
    else
    {
        //validate email
        $sql2 = "SELECT username FROM users WHERE email = ?";

        if ($stmt = mysqli_prepare($link, $sql2))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            // Set parameters
            $param_email = trim($_POST["email"]);
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt))
            {
                /* store result */
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1)
                {
                    $email_err =  "This email is already taken.";	
                }
                else
                {
                    $email = trim($_POST["email"]);
                    $token = md5($_POST["email"]) . rand(10, 9999);
                }
            }
            else
            {
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
		else  if ($stmt = mysqli_prepare($link, $sql2))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $email);
            // Set parameters
            $param_email = trim($_POST["email"]);
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt))
            {
                /* store result */
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1)
                {
                    $email_err = "This email is already taken.";
                }
                else {}
            }
            else
            {
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
	
    // Validate password
    if (empty(trim($_POST["password"])))
    {
        $password_err = "Please enter a password.";
    }
	else if (!(
		preg_match('/[A-Za-z]/', trim($_POST["password"])) 
		&& preg_match('/[0-9]/', trim($_POST["password"])) 
		&& preg_match('/[A-Z]/', trim($_POST["password"]))
		&& preg_match('/[a-z]/', trim($_POST["password"]))
		))
	{
		$password_err = 'Password must contain a lowercase letter, uppercase letter, and a number.';
	}
    else if (strlen(trim($_POST["password"])) < 8 || strlen(trim($_POST["password"])) > 25)
    {
        $password_err = "Password must have atleast 8 characters and not exceed 25.";
    }
    else
    {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"])))
    {
        $confirm_password_err = "Please confirm password.";
    }
    else
    {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && $password != $confirm_password)
        {
            $confirm_password_err = "Password did not match.";
        }
    }

    //validate question
    $aQuestions = $_POST['questions'];

    if (empty(trim($_POST["answer"])) || !isset($aQuestions))
    {
        $ans_err = "Please enter question and answer.";
    }
    else if (strlen(trim($_POST["answer"])) < 8 || strlen(trim($_POST["answer"])) > 25)
    {
        $ans_err= "Answer must have atleast 8 characters and not exceed 25.";
    }
    else
    {
        $ans = trim($_POST["answer"]);
    }
	$var = NULL; 
	if(isset($_POST['formHobbies'])) {
			$aHobbies = $_POST['formHobbies'];
			$nHobbies = count($aHobbies);
		}
		else if(!isset($aHobbies)) {
			$confirm_password_err = "Please select at least one hobby.";
		}

    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) 
		&& empty($email_err) && empty($fname_err) && empty($lname_err) && empty($ans_err))
    {
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password, first_name, last_name, email, email_verification_link, email_verified_at, 
			 dob, ans, ques) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssssssss", $param_username, $param_password, $firstname, $lastname,
			$param_email, $token, $var , $dob, password_hash(strtolower($ans) , PASSWORD_DEFAULT) , $aQuestions[0]);
            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
				for($i=0; $i < $nHobbies; $i++)
			{
      mysqli_query($link, "INSERT INTO hobbies (username, hobby) VALUES ('".$username."', '".$aHobbies[$i]."')");
    }
			}
            else
            {
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
		
        $result = mysqli_query($link, "SELECT * FROM users WHERE email= '" . $_POST["email"] . "'");
        $row = mysqli_num_rows($result);
        $rows = $row;
        if ($row == 1)
        {
            //sends email
            $link2 = "<a href='localhost/Socially/verify-email.php?key=" . $_POST["email"] . "&token=" . $token . "'>Verify</a>";
            require "phpmail/src/Exception.php";
            require "phpmail/src/PHPMailer.php";
            require "phpmail/src/SMTP.php";
            $mail = new PHPMailer(true);
            try
            {
                $mail->CharSet = "utf-8";
                $mail->IsSMTP();
                // enable SMTP authentication
                $mail->SMTPAuth = true;
                // GMAIL username
                $mail->Username = "compsciemail123@gmail.com";
                // GMAIL password
                $mail->Password = "comp424smtp123*";
                $mail->SMTPSecure = "ssl";
                // sets GMAIL as the SMTP server
                $mail->Host = "smtp.gmail.com";
                // set the SMTP port for the GMAIL server
                $mail->Port = "465";
                $mail->From = "compsciemail123@gmail.com";
                $mail->FromName = "WebMaster";
                //$mail->AddAddress('reciever_email_id', 'reciever_name');
                //var_dump($email);
                $mail->addAddress($email, "kevin");
                $mail->Subject = "Verify your E-mail";
                $mail->IsHTML(true);
                date_default_timezone_set("America/Los_Angeles");
                $date = date("Y-m-d H:i:s");
                $greeting = "";
                if (date('H') < 12)
                {
                    $greeting = "Good morning";
                }
                else if (date('H') >= 12 && date('H') < 18)
                {
                    $greeting = "Good afternoon";
                }
                else if (date('H') >= 18)
                {
                    $greeting = "Good evening";
                }
                $mail->Body = " " . $greeting . " " . $firstname . " (Username: " . $username . "). Click On This Link to Verify Email: " . $link2 . ". (Note: If this isn't you, ignore this email).";
            }
            catch(phpmailerException $e)
            {
                echo $e->errorMessage();
            }
            catch(Exception $e)
            {
                mysqli_query($link, "DELETE FROM users WHERE username = '$username';");
                header("location: register.php"); //Boring error messages from anything else!
                
            }
            if ($mail->Send())
            {
				
                header("location: login.php");
            }
            else
            {
                echo "Mail Error - >" . $mail->ErrorInfo;
            }
        }
        else
        {
            mysqli_query($link, "DELETE FROM users WHERE username = '$username';");
            header("location: register.php"); //Boring error messages from anything else!
            
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
  <head> 
    <meta charset="UTF-8">
	  <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.2.0/zxcvbn.js"></script>
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; background-color: #e8f3fd;}
        			.wrapper
			{ 
				width: 360px; 
				padding: 20px; 
				margin-left: auto;
				margin-right: auto; 
				margin-top:1%; 
			}
			@font-face {
  font-family: myFirstFont;
  src: url(sansation_light.woff);
}
h2 {
  font-family: myFirstFont;
}
		

#blueDIV {
  position:absolute;
  width:200px;
  padding:10px;
  border-radius:20px;
  background-color:white;
  right: 3%;
}
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
      <h2>Socially</h2>
      <form action="
						<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
          <label for="exampleInputEmail1">Email Address</label>
          <input type="email" name="email"  id="email" required="" aria-describedby="emailHelp" class="form-control
		  <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
          <span class="invalid-feedback"> <?php echo $email_err; ?> </span> 
		  <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
        </div>
		<div class="form-group">
          <label>First Name</label>
          <input type="firstname" name="firstname" id="firstname"  required=""  class="form-control
		  <?php echo (!empty($fname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $firstname; ?>">
          <span class="invalid-feedback"> <?php echo $fname_err; ?> </span> 
        </div>
		<div class="form-group">
          <label>Last Name</label>
          <input type="lastname" name="lastname" id="lastname"  required="" class="form-control
	<?php echo (!empty($lname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $lastname; ?>">
		  
          <span class="invalid-feedback"> <?php echo $lname_err; ?> </span> 
        </div>
		<div class="form-group">
          <label>Date of Birth</label>
		<input type="date" id="dob" name="dob" required="" value="<?php echo $dob; ?>">
		</div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" class="form-control 
			<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
          <span class="invalid-feedback"> <?php echo $username_err; ?> </span>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" id="password" class="form-control 
			<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
          <span class="invalid-feedback"> <?php echo $password_err; ?> </span>
		  <br>
		  <input type="checkbox"  onclick="showF()">Show Password </input>
		  <script>
function showF() {
  var x = document.getElementById("password");
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
            var password = document.getElementById('password');
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
          <input type="password" name="confirm_password" class="form-control 
		<?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
          <span class="invalid-feedback"> <?php echo $confirm_password_err; ?> </span>
        </div>
		<div class="form-group">
			<label for='formHobbies[]'>Select Your Hobbies:</label><br>
			<select multiple="multiple" name="formHobbies[]" size= 7>
				<option value="hiking">Hiking</option>
				<option value="swimming">Swimming</option>
				<option value="calligraphy">Calligraphy</option>
				<option value="bowling">Bowling</option>
				<option value="movie">Movie</option>
				<option value="cooking">Cooking</option>
				<option value="dancing">Dancing</option>
			</select>
			</div>
<div class="form-group">
	<label for='questions[]'>Select a Recovery Question:</label><br>
<select  name="questions[]" class="form-control" size = "4">
    <option value="1" <?php if (isset($aQuestions[0]) && $aQuestions[0]=="1") echo "selected";?>>What is your mother's maiden name?</option>
    <option value="2"<?php if (isset($aQuestions[0]) && $aQuestions[0]=="2") echo "selected";?>>What is your favorite pet's name?</option>
    <option value="3"<?php if (isset($aQuestions[0]) && $aQuestions[0]=="3") echo "selected";?>>What city was your first job in?</option>
    <option value="4"<?php if (isset($aQuestions[0]) && $aQuestions[0]=="4") echo "selected";?>>Where did you go to for 6th grade?</option>
    <option value="5"<?php if (isset($aQuestions[0]) && $aQuestions[0]=="5") echo "selected";?>>Who was your 3rd grade teacher?</option>
    <option value="6"<?php if (isset($aQuestions[0]) && $aQuestions[0]=="6") echo "selected";?>>What was your childhood nickname?</option>
</select>  
 </div>
	<div class="form-group">
          <label>Answer</label>
		  <input type="answer" name="answer" class="form-control <?php echo (!empty($ans_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $ans; ?>">
          <span class="invalid-feedback"><?php echo $ans_err; ?></span>
        </div>
        <div class="form-group">
          <input name="Submit" type="submit" value="Submit" class="btn btn-primary">
          <input type="reset" class="btn btn-secondary ml-2" value="Reset">
		  <br><br>
          <p>Already have an account? <a href="login.php">Login here</a>. </p> 
		  <?php if($rows>1) : ?> 
		  
		  <p>You already have an account. Verify your email and <a href="login.php">Login here</a>. </p> 
		  <?php endif; ?>
        </div>
      </form>
    </div>
  </body>
</html>