<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/*
 * Implements the view for the login form.
 */

?>
<html lang="en">

<head>
    <title>ComicLib</title>
    <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/PWAHead.html"
    ?>
</head>

<header>
    <?php
    /*
     * Include the headers for Bootstrap and FontAwesome.
     */
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapHeader.html";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/FontAwesomeHeader.html";
    ?>

    <link href="/resources/css/SignIn.css" rel="stylesheet">

</header>
<body>

<!-- Full-screen background.-->
<div style='background: url("/resources/images/Background.jpg") no-repeat center center fixed; background-size:
cover; position: fixed; height: 100%; width: 100%;'>

    <div class="text-center text-light" style="min-height: 100%; display: flex; align-items: center">
        <div class="container">
            <h1 class="display-3">
                <img src="/resources/images/ComicLib_darker.png" style="height: 1.2em; width: auto" alt="">
                <b>ComicLib</b></h1><br>
            <form class="form-signin" method="post">
                <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
                <label for="inputUserName" class="sr-only">User Name</label>
                <input type="text" id="inputUserName" name="username" class="form-control" placeholder="User Name"
                       required=""
                       autofocus="">
                <label for="inputPassword" class="sr-only">Password</label>
                <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password"
                       required="">
                <!-- Dynamically hide the error message if it has no content. -->
                <div class="alert alert-danger <?php if (empty($this->loginStatus)) print 'd-none'; ?>">
                    <?php
                    print $this->loginStatus;
                    ?>
                </div>
                <button class="btn btn-lg btn-primary btn-block" type="submit" value="Submit">Sign in <i
                            class="fas fa-sign-in-alt fa-sm"></i></button>
            </form>
        </div>
    </div>
</div>


<?php
/*
 * Include the JavaScript libraries for Bootstrap.
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapBody.html";
?>
</body>
</html>