<?php
session_start();

$correct_username = "admin";
$correct_password = "pass";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_username = $_POST['username'];
    $entered_password = $_POST['password'];


    if ($entered_username === $correct_username && $entered_password === $correct_password) {

        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Invalid username or password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"> 
    <style>
        #container{
            box-shadow: 0 8px 32px 0 rgba( 0, 0, 0, 0.67 );
        }
    </style>
</head>

<body class="bg-cover bg-center bg-auto bg-gradient-to-br from-gray-100 to-transparent shadow-md min-h-screen flex items-center justify-center bg-no-repeat" style="background-image: url('Images/login_bg2.jpg');">
    <div id="container" class=" bg-opacity-5 shadow-md backdrop-blur backdrop-filter backdrop-shadow-lg border border-opacity-20 rounded-2xl p-6 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-4">Login</h1>
        <?php if (isset($error_message)) : ?>
            <p class="text-red-500 mb-4"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="POST" >
            <div class="mb-4">
                <label for="username" class="block text-white-700">Username:</label>
                <input type="text" name="username" id="username" class="w-full border rounded px-3 py-2 ">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-white-700">Password:</label>
                <input type="password" name="password" id="password" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <button type="submit" class="bg-green-500 text-white rounded px-4 py-2 hover:bg-blue-600">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
