<?php
session_start(); // Ensure session starts at the beginning
include "./components/navbar.html";

// Database connection
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "Nishan_Bakery";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$registerError = $loginError = $successMessage = "";
$redirectToLogin = false;
$successRegistration = false;
$successLogin = false;

// Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $name = trim($_POST["reg_name"]);
    $email = trim($_POST["reg_email"]);
    $password = $_POST["reg_password"];

    if (empty($name) || empty($email) || empty($password)) {
        $registerError = "All fields are required";
    } else {
        $stmt = $conn->prepare("SELECT email FROM user_accounts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $registerError = "Email already exists";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userType = "customer";
            $stmt = $conn->prepare("INSERT INTO user_accounts (name, email, password, user_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $userType);
            
            if ($stmt->execute()) {
                $successMessage = "Registration successful! Please log in.";
                $redirectToLogin = true;
                $successRegistration = true;
            } else {
                $registerError = "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

// Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = trim($_POST["login_email"]);
    $password = $_POST["login_password"];

    if (empty($email) || empty($password)) {
        $loginError = "Email and password are required";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, user_type FROM user_accounts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_type"] = $user["user_type"];
                
                $stmt->close();
                
                $successLogin = true;
                $successMessage = "Login successful!";
                
                // Use JavaScript to redirect after showing success message
              // Use JavaScript to redirect after showing success message
echo "<script>
setTimeout(function() {
    window.location.href = '" . 
    ($user["user_type"] == "customer" ? "./customer/cus-index.php" : 
    ($user["user_type"] == "admin" ? "./admin/admin.php" : "./staff/index.php")) . "';
}, 2000);
</script>";

            } else {
                $loginError = "Invalid email or password";
            }
        } else {
            $loginError = "Invalid email or password";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nishan Bakery - Login/Register</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Import -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Additional style for the success message */
        .success-message {
            position: fixed;
            top: 75px;
            left: 50%;
            transform: translateX(-50%);
           
            color: green;
            font-weight: bolder;
            padding: 12px 25px;
            border-radius: 5px;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: opacity 0.5s ease-in-out;
        }
        
        /* Additional style for the error message */
        .error-message {
            color: red;
            margin: 10px 0;
            font-size: 14px;
            font-weight: bold;
        }

        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 0;
            background: linear-gradient(120deg, #f5f7fa, #eef2f7);
        }

        .login-container * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        .auth-box {
            background-color: #fff;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            width: 850px;
            max-width: 90%;
            min-height: 550px;
            transition: all 0.3s ease;
        }

        .auth-box:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }

        .auth-box h1 {
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
            letter-spacing: -0.5px;
        }

        .auth-box p {
            font-weight: 500;
            font-size: 16px;
            line-height: 1.6;
            letter-spacing: 0.2px;
            margin: 20px 0;
            color: rgba(255, 255, 255, 0.9);
        }

        .auth-box span {
            font-size: 13px;
            color: #777;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .auth-box a {
            color: #ca1212;
            font-size: 13px;
            text-decoration: none;
            margin: 15px 0 10px;
            font-weight: 600;
            transition: color 0.3s;
        }

        .auth-box a:hover {
            color: #e62222;
            text-decoration: underline;
        }

        .auth-box button {
            background-color: #ca1212;
            color: #fff;
            font-size: 13px;
            padding: 12px 45px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-top: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(202, 18, 18, 0.3);
        }

        .auth-box button:hover {
            background-color: #e62222;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(230, 34, 34, 0.4);
        }

        .auth-box button:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px rgba(202, 18, 18, 0.3);
        }

        .auth-box button.hidden-btn {
            background-color: transparent;
            border: 2px solid #fff;
            box-shadow: 0 4px 8px rgba(255, 255, 255, 0.2);
        }

        .auth-box button.hidden-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 6px 12px rgba(255, 255, 255, 0.3);
        }

        .auth-box form {
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
        }

        .auth-box input {
            background-color: #f6f6f6;
            border: none;
            border-bottom: 2px solid #eee;
            margin: 10px 0;
            padding: 14px 15px;
            font-size: 14px;
            border-radius: 8px;
            width: 100%;
            outline: none;
            transition: all 0.3s;
        }

        .auth-box input:focus {
            background-color: #f0f0f0;
            border-bottom-color: #ca1212;
        }

        .auth-box input::placeholder {
            color: #aaa;
            font-weight: 500;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .signin-form {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .auth-box.active .signin-form {
            transform: translateX(100%);
        }

        .signup-form {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .auth-box.active .signup-form {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: moveForm 0.6s;
        }

        @keyframes moveForm {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }
            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .social-icons-auth {
            margin: 25px 0;
            display: flex;
            justify-content: center;
        }
        .social-icons-auth a:nth-child(1) {
            color: #3b5998;
            border-color: #3b5998;
        }
        .social-icons-auth a:nth-child(2) {
            color: #db4437;
            border-color: #db4437;
        }
        .social-icons-auth a:nth-child(3) {
            color:rgb(205, 17, 230);
            border-color:rgb(189, 8, 235);
        }
        .social-icons-auth a:nth-child(4) {
            color: #0077b5;
            border-color: #0077b5;
        }

        .social-icons-auth a {
            border: 1px solid #eee;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 8px;
            width: 55px;
            height: 55px;
            color: #666;
            font-size: 55px;
            transition: all 0.3s;
            background: linear-gradient(145deg, #f0f0f0, #ffffff);
            box-shadow: 3px 3px 6px #e1e1e1, -3px -3px 6px #ffffff;
        }

        .social-icons-auth a:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .social-icons-auth a:nth-child(1):hover {
            color: #3b5998;
            border-color: #3b5998;
        }

        .social-icons-auth a:nth-child(2):hover {
            color: #db4437;
            border-color: #db4437;
        }

        .social-icons-auth a:nth-child(3):hover {
            color:rgb(205, 17, 230);
            border-color:rgb(189, 8, 235);
        }

        .social-icons-auth a:nth-child(4):hover {
            color: #0077b5;
            border-color: #0077b5;
        }

        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: all 0.6s ease-in-out;
            border-radius: 150px 0 0 100px;
            z-index: 1000;
        }

        .auth-box.active .toggle-container {
            transform: translateX(-100%);
            border-radius: 0 150px 100px 0;
        }

        .toggle-auth {
            background: linear-gradient(135deg, #f30d0d, #cf142a);
            height: 100%;
            color: #fff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
            box-shadow: inset 0 0 80px rgba(0, 0, 0, 0.2);
        }

        .auth-box.active .toggle-auth {
            transform: translateX(50%);
        }

        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .toggle-panel h1 {
            font-family: 'Montserrat', sans-serif;
            color: #fff;
            font-size: 38px;
            font-weight: 700;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        .toggle-panel p {
            font-family: 'Montserrat', sans-serif;
            color: #fff;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 15px;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        .toggle-left {
            transform: translateX(-200%);
        }

        .auth-box.active .toggle-left {
            transform: translateX(0);
        }

        .toggle-right {
            right: 0;
            transform: translateX(0);
        }

        .auth-box.active .toggle-right {
            transform: translateX(200%);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="auth-box" id="authBox">
            <!-- Success Message Modal -->
            <?php if (!empty($successMessage)): ?>
                <div id="successModal" class="success-message">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container signup-form">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <h1>Create Account</h1>
                    <div class="social-icons-auth flex justify-center space-x-4 my-6">
                      <a href="#" class="social flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 text-gray-600 transition-all duration-300 hover:border-blue-600 hover:text-blue-600 hover:-translate-y-1 hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z" />
                        </svg>
                      </a>
                      <a href="#" class="social flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 text-gray-600 transition-all duration-300 hover:border-red-600 hover:text-red-600 hover:-translate-y-1 hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M7 11v2.4h3.97c-.16 1.029-1.2 3.02-3.97 3.02-2.39 0-4.34-1.979-4.34-4.42 0-2.44 1.95-4.42 4.34-4.42 1.36 0 2.27.58 2.79 1.08l1.9-1.83c-1.22-1.14-2.8-1.83-4.69-1.83-3.87 0-7 3.13-7 7s3.13 7 7 7c4.04 0 6.721-2.84 6.721-6.84 0-.46-.051-.81-.111-1.16h-6.61z" />
                        </svg>
                      </a>
                      <a href="#" class="social flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 text-gray-600 transition-all duration-300 hover:border-gray-800 hover:text-gray-800 hover:-translate-y-1 hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7.75 2h8.5A5.76 5.76 0 0 1 22 7.75v8.5A5.76 5.76 0 0 1 16.25 22h-8.5A5.76 5.76 0 0 1 2 16.25v-8.5A5.76 5.76 0 0 1 7.75 2Zm0-2A7.75 7.75 0 0 0 0 7.75v8.5A7.75 7.75 0 0 0 7.75 24h8.5A7.75 7.75 0 0 0 24 16.25v-8.5A7.75 7.75 0 0 0 16.25 0Zm8.19 3.5h.06a1.55 1.55 0 0 1 1.54 1.56v.05a1.55 1.55 0 1 1-1.56-1.61Zm-4.19 2.75a5.5 5.5 0 1 1-5.5 5.5 5.5 5.5 0 0 1 5.5-5.5Zm0 2A3.5 3.5 0 1 0 15 12a3.5 3.5 0 0 0-3.5-3.5Z"/>
                        </svg>
                    </a>

                      <a href="#" class="social flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 text-gray-600 transition-all duration-300 hover:border-blue-700 hover:text-blue-700 hover:-translate-y-1 hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z" />
                        </svg>
                      </a>
                    </div>
                    <span>or use your email for registration</span>
                    
                    <!-- Display error message for registration -->
                    <?php if (!empty($registerError)): ?>
                        <div class="error-message"><?php echo $registerError; ?></div>
                    <?php endif; ?>
                    
                    <input type="text" name="reg_name" placeholder="Name" required />
                    <input type="email" name="reg_email" placeholder="Email" required />
                    <input type="password" name="reg_password" placeholder="Password" required />
                    <button type="submit" name="register">Sign Up</button>
                </form>
            </div>
            <div class="form-container signin-form">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <h1>Sign In</h1>
                    <div class="social-icons-auth flex justify-center space-x-4 my-6">
                      <a href="#" class="social flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 text-gray-600 transition-all duration-300 hover:border-blue-600 hover:text-blue-600 hover:-translate-y-1 hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z" />
                        </svg>
                      </a>
                      <a href="#" class="social flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 text-gray-600 transition-all duration-300 hover:border-red-600 hover:text-red-600 hover:-translate-y-1 hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M7 11v2.4h3.97c-.16 1.029-1.2 3.02-3.97 3.02-2.39 0-4.34-1.979-4.34-4.42 0-2.44 1.95-4.42 4.34-4.42 1.36 0 2.27.58 2.79 1.08l1.9-1.83c-1.22-1.14-2.8-1.83-4.69-1.83-3.87 0-7 3.13-7 7s3.13 7 7 7c4.04 0 6.721-2.84 6.721-6.84 0-.46-.051-.81-.111-1.16h-6.61z" />
                        </svg>
                      </a>
                      <a href="#" class="social flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 text-gray-600 transition-all duration-300 hover:border-gray-800 hover:text-gray-800 hover:-translate-y-1 hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7.75 2h8.5A5.76 5.76 0 0 1 22 7.75v8.5A5.76 5.76 0 0 1 16.25 22h-8.5A5.76 5.76 0 0 1 2 16.25v-8.5A5.76 5.76 0 0 1 7.75 2Zm0-2A7.75 7.75 0 0 0 0 7.75v8.5A7.75 7.75 0 0 0 7.75 24h8.5A7.75 7.75 0 0 0 24 16.25v-8.5A7.75 7.75 0 0 0 16.25 0Zm8.19 3.5h.06a1.55 1.55 0 0 1 1.54 1.56v.05a1.55 1.55 0 1 1-1.56-1.61Zm-4.19 2.75a5.5 5.5 0 1 1-5.5 5.5 5.5 5.5 0 0 1 5.5-5.5Zm0 2A3.5 3.5 0 1 0 15 12a3.5 3.5 0 0 0-3.5-3.5Z"/>
                        </svg>
                    </a>

                      <a href="#" class="social flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 text-gray-600 transition-all duration-300 hover:border-blue-700 hover:text-blue-700 hover:-translate-y-1 hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z" />
                        </svg>
                      </a>
                    </div>
                    <span>or use your email password</span>
                    
                    <!-- Display error message for login -->
                    <?php if (!empty($loginError)): ?>
                        <div class="error-message"><?php echo $loginError; ?></div>
                    <?php endif; ?>
                    
                    <input type="email" name="login_email" placeholder="Email" required />
                    <input type="password" name="login_password" placeholder="Password" required />
                    <a href="forgot_password.php">Forgot your password?</a>
                    <button type="submit" name="login">Sign In</button>
                </form>
            </div>
            <div class="toggle-container">
                <div class="toggle-auth">
                    <div class="toggle-panel toggle-left">
                        <h1>Welcome Back!</h1>
                        <p>Enter your personal details to use all of site features</p>
                        <button class="hidden-btn" id="loginBtn" type="button">Sign In</button>
                    </div>
                    <div class="toggle-panel toggle-right">
                        <h1>Hello, Friend!</h1>
                        <p>Register with your personal details to use all of site features</p>
                        <button class="hidden-btn" id="registerBtn" type="button">Sign Up</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const authBox = document.getElementById('authBox');
            const registerBtn = document.getElementById('registerBtn');
            const loginBtn = document.getElementById('loginBtn');

            registerBtn.addEventListener('click', () => {
                authBox.classList.add("active");
            });

            loginBtn.addEventListener('click', () => {
                authBox.classList.remove("active");
            });
            
            // Handle success messages
            const successModal = document.getElementById('successModal');
            if (successModal) {
                // Auto-hide success message after 5 seconds
                setTimeout(function() {
                    successModal.style.opacity = '0';
                    setTimeout(function() {
                        successModal.style.display = 'none';
                    }, 500);
                }, 5000);
            }
            
            <?php if ($redirectToLogin): ?>
            // If registration was successful, switch to login panel
            setTimeout(function() {
                authBox.classList.remove("active");
            }, 1000);
            <?php endif; ?>
        });
    </script>
</body>
</html>