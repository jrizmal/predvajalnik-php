<!DOCTYPE html>
<html lang="en">

<head>
    <?php

    include "./components/header.component.php";
    echo createHeaders("Moj profil");

    ?>
</head>

<body>
    <?php
    include "./components/navbar.component.php";
    echo createNav();
    ?>
    <div class="container">
        <div class="row my-3">
            <div class="col">
                <h3>Moj profil</h3>
                <ul>
                    <li><?= $user["name"] ?></li>
                    <li><?= $user["email"] ?></li>
                    <li><?= $user["password"] ?></li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>