<?php

session_start();
$error = "";

if (array_key_exists("logout", $_GET)) {
    unset($_SESSION);
    setcookie("id", "", time() - 60 * 60);
    $_COOKIE["id"] = "";
    session_destroy();
} else if ((array_key_exists("id", $_SESSION) and ($_SESSION["id"])) or (array_key_exists("id", $_COOKIE) and ($_COOKIE["id"]))) {
    header("Location: loggedInPage.php");
}

if (array_key_exists("submit", $_POST)) {

    include("connection.php");

    if (!$_POST["email"]) {
        $error .= "An email address is required<br>";
    }
    if (!$_POST["password"]) {
        $error .= "A password is required<br>";
    }
    if ($error != "") {
        $error = "<p>There were error(s) in your form:</p>" . $error;
    } else {
        if ($_POST["signUp"] == "1") {

            $query = "SELECT id FROM users WHERE email='" . mysqli_real_escape_string($link, $_POST["email"]) . "' LIMIT 1";
            $result = mysqli_query($link, $query);

            if (mysqli_num_rows($result) > 0) {
                $error = "<p>The email you entered has already been taken</p>";
            } else {
                $query = "INSERT INTO users (email, password) VALUES('" . mysqli_real_escape_string($link, $_POST["email"]) . "','" . mysqli_real_escape_string($link, $_POST["password"]) . "')";

                if (!mysqli_query($link, $query)) {
                    $error = "<p>Sign up failed. Please try again later</p>";
                } else {
                    $query = "UPDATE users SET password='" . md5(md5(mysqli_insert_id($link)) . $_POST["password"]) . "' WHERE id=" . mysqli_insert_id($link) . " LIMIT 1";
                    mysqli_query($link, $query);

                    $_SESSION["id"] = mysqli_insert_id($link);
                    if ($_POST["stayLoggedIn"] == "1") {
                        setcookie("id", mysqli_insert_id($link), time() + 60 * 60 * 24 * 365);
                    }
                    header("Location: loggedInPage.php");
                }
            }
        } else {
            $query = "SELECT * FROM users where email='" . mysqli_real_escape_string($link, $_POST["email"]) . "' LIMIT 1";
            $result = mysqli_query($link, $query);
            $row = mysqli_fetch_array($result);

            if (isset($row)) {
                $hashedPassword = md5(md5($row["id"]) . $_POST["password"]);
                if ($hashedPassword == $row["password"]) {
                    $_SESSION["id"] = $row["id"];
                    if ($_POST["stayLoggedIn"] == "1") {
                        setcookie("id", $row["id"], time() + 60 * 60 * 24 * 365);
                    }
                    header("Location: loggedInPage.php");
                } else {
                    $error = "<p>The email/password you entered is wrong</p>";
                }
            } else {
                $error = "<p>The email/password you entered is wrong</p>";
            }
        }
    }
}
?>

<?php include("header.php"); ?>

<div class="container rounded-pill">
    <h1>Secret Diary</h1>
    <p class="lead"><strong>Store your secrets securely!</strong></p>
    <div id="error"><?php if($error != "") {
        echo('<div class="alert alert-danger" role="alert">'.$error.'</div>');
    } ?></div>

    <form method="POST" id="signUpForm">

        <p class="lead"><strong>Sign up now!</strong></p>

        <div class="form-group">
            <input type="email" class="form-control" name="email" placeholder="Your Email">
        </div>

        <div class="form-group">
            <input type="password" class="form-control" name="password" placeholder="password">
        </div>

        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="checkbox1" name="stayLoggedIn" value=1>
            <label class="form-check-label" for="checkbox1">Stay logged in</label>
        </div>

        <div class="form-group">
            <input type="hidden" name="signUp" value="1">
            <input type="submit" class="btn btn-success" name="submit" value="Sign Up!">
        </div>

        <p><a href="#" class="toggleForm">Log In</a></p>
    </form>

    <form method="POST" id="logInForm">

        <p class="lead"><strong>Log in to your account</strong></p>

        <div class="form-group">
            <input type="email" class="form-control" name="email" placeholder="Your Email">
        </div>

        <div class="form-group">
            <input type="password" class="form-control" name="password" placeholder="password">
        </div>

        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="checkbox2" name="stayLoggedIn" value=1>
            <label class="form-check-label" for="checkbox2">Stay logged in</label>
        </div>

        <div class="form-group">
            <input type="hidden" name="signUp" value="0">
            <input type="submit" class="btn btn-success" name="submit" value="Log In!">
        </div>
        
        <p><a href="#" class="toggleForm">Sign Up</a></p>
    </form>
</div>

<?php include("footer.php"); ?>