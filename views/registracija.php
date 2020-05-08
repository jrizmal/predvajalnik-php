<!DOCTYPE html>
<html lang="en">

<head>
    <?php

    include "./components/header.component.php";
    echo createHeaders();

    ?>
</head>

<body>
    <?php
    include "./components/navbar.component.php";
    echo createNav();
    ?>
    <div class="container">
        <div class="row my-3">
            <div class="col-md-4">
                <h3>Registracija</h3>
                <form method="post" action="<?= BASE_URL . "registracija/" ?>">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Vaše ime</label>
                        <input type="text" class="form-control" id="ime" aria-describedby="nameHelp" name="ime">
                        <!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Email naslov</label>
                        <input type="email" class="form-control" id="email" aria-describedby="emailHelp" name="email">
                        <!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
                    </div>
                    <div class="form-group">
                        <label for="password1">Geslo</label>
                        <input type="password" class="form-control" aria-describedby="passHelp" id="password1" name="geslo1">
                        <small id="passHelp" class="form-text text-muted">Geslo naj bo dolgo vsaj 9 znakov.</small>
                    </div>
                    <div class="form-group">
                        <label for="password2">Ponovite geslo</label>
                        <input type="password" class="form-control" id="password2" name="geslo2">
                    </div>
                    <!-- <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="exampleCheck1">
                        <label class="form-check-label" for="exampleCheck1">Check me out</label>
                    </div> -->
                    <div class="form-group">
                        <span style="color:red;"><?= $errorMessage ?></span>
                    </div>
                    <button type="submit" class="btn btn-success">Registracija</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>