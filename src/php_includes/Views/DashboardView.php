<?php
/**
 * Created by ahahn94
 * on 21.07.19
 */

/*
 * Implements the view for the management dashboard.
 */

?>
<html lang="en">
<header>
    <?php
    /*
     * Include the headers for Bootstrap and FontAwesome.
     */
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapHeader.html";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/FontAwesomeHeader.html";
    ?>
</header>
<body>
<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/Menu.html";
?>

<!-- Full-screen background. -->
<div style='background: url("/resources/images/Background.jpg") no-repeat center center fixed; background-size:
cover; position: fixed; height: 100%; width: 100%; overflow: auto'>

    <div class="container bg-light h-100 pb-4" style="overflow: auto">
        <!-- Dynamically hide the error message if it has no content. -->
        <div class="alert alert-danger text-center <?php if (empty($this->errorMessage)) print 'd-none'; ?>">
            <?php print($this->errorMessage); ?>
        </div>
        <br>
        <h1 class="text-center"><i class="fas fa-tachometer-alt"> Management Dashboard</i></h1>
        <div class="mb-3">

            <div class="text-right">
                <!-- Hide from normal users. -->
                <?php if ($this->user["UserGroupID"] == UserManager::getGroupIDs()["admin"]) {
                    ?>
                    <a class="btn btn-primary mb-4" href="/update"><i class="fas fa-sync-alt"> Update Database</i></a>
                    <?php
                } else {
                    ?>
                    <a class="btn btn-primary mb-4" href="/updates"><i class="fas fa-sync-alt"> Updates Status</i></a>
                    <?php
                }
                ?>
            </div>
            <h2>My Password</h2>
            <div class="d-flex form-inline">
                <form method="post">
                    <input hidden name="Action" value="ChangePassword">
                    <input type="password" class="form-control form-control-sm my-1" type="text"
                           placeholder="Current Password" name="CurrentPassword">
                    <input type="password" class="form-control form-control-sm my-1" type="text"
                           placeholder="New Password" name="NewPassword">
                    <input type="password" class="form-control form-control-sm my-1" type="text"
                           placeholder="Repeat New Password" name="NewPasswordRepeat">
                    <button type="submit" class="btn btn-primary btn"><i class="fas fa-key"> Update Password</i>
                    </button>
                </form>
            </div>

            <h2>My API Key</h2>
            <div class="d-flex form-inline">
                <form method="post">
                    <input hidden name="Action" value="RegenerateAPIKey">
                    <input type="password" class="form-control form-control-sm my-1" type="text"
                           placeholder="Password" name="Password">
                    <button type="submit" class="btn btn-primary btn"><i class="fas fa-key"> Regenerate API Key</i>
                    </button>
                </form>
            </div>

            <!-- Hide from normal users. -->
            <?php if ($this->user["UserGroupID"] == UserManager::getGroupIDs()["admin"]) {
                ?>
                <h2>Add New User</h2>
                <div class="d-flex form-inline">
                    <form method="post">
                        <input hidden name="Action" value="AddUser">
                        <input class="form-control form-control-sm my-1" type="text" placeholder="Name" name="Name">
                        <input class="form-control form-control-sm my-1" type="password" placeholder="Password"
                               name="Password">
                        <input class="form-control form-control-sm my-1" type="password" placeholder="Repeat Password"
                               name="PasswordRepeat">
                        <select class="form-control-sm" name="UserGroupName">
                            <?php
                            foreach ($this->groups as $group) {
                                if ($group == "Administrators") {
                                    if ($this->user["UserID"] == UserManager::getDefaultUserID()) {
                                        // Only default user can add admins.
                                        print("<option>$group</option>");
                                    }
                                } else {
                                    if ($group == "Users") print("<option selected>$group</option>");
                                    else print("<option>$group</option>");
                                }
                            }
                            ?>
                        </select>
                        <button class="btn btn-primary btn"><i class="fas fa-user-plus"> Add User</i></button>
                    </form>
                </div>

                <h2>Users</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                        <tr>
                            <th>
                                UserID
                            </th>
                            <th>
                                Name
                            </th>
                            <th>
                                Group
                            </th>
                            <th>
                                Options
                            </th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php
                        foreach ($this->users as $user) {
                            ?>
                            <tr>
                                <td>
                                    <div class="my-1">
                                        <?php
                                        print($user["UserID"]);
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="my-1">
                                        <?php
                                        print($user["Name"]);
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    if ($this->user["UserID"] == UserManager::getDefaultUserID()) {
                                        // The default user can change users groups.
                                        if ($user["UserID"] == UserManager::getDefaultUserID()) {
                                            // The group of the default user can not be changed.
                                            print("<div class=\"my-1\">{$user["GroupName"]}</div>");
                                        } else {
                                            ?>
                                            <form method="post" class="my-auto">
                                                <input hidden name="Action" value="ChangeUserGroup">
                                                <input hidden name="UserID" value="<?php print($user["UserID"]); ?>">
                                                <select class="form-control-sm" name="UserGroupName"
                                                        onchange="this.form.submit()">
                                                    <?php
                                                    foreach ($this->groups as $group) {
                                                        if (strcmp($user["GroupName"], $group) == 0) {
                                                            print("<option selected>$group</option>");
                                                        } else {
                                                            print("<option>$group</option>");
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </form>
                                            <?php
                                        }
                                    } else {
                                        print($user["GroupName"]);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form method="post" class="my-1">
                                        <input hidden name="Action" value="DeleteUser">
                                        <input hidden name="UserID" value="<?php print($user["UserID"]); ?>">
                                        <button type="submit" class="btn btn-sm <?php
                                        if ($user["UserGroupID"] == 1) {

                                            if ($this->user["UserID"] == UserManager::getDefaultUserID()) {
                                                if ($user["UserID"] == UserManager::getDefaultUserID()) {
                                                    // Default user can not be deleted.
                                                    print("btn-secondary disabled\" disabled");
                                                } else {
                                                    // Default user can delete admins.
                                                    print("btn-primary\"");
                                                }
                                            } else {
                                                // User is not the default user. Can not delete admins.
                                                print("btn-secondary disabled\" disabled");
                                            }
                                        } else {
                                            print("btn-primary\"");
                                        }
                                        ?>>
                                        <i class=" fas fa-trash
                                        "> Delete</i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            ?>
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