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
                <h3>Prijava</h3>
                <form method="post" action="<?= BASE_URL . "prijava" ?>">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Email naslov</label>
                        <input type="email" class="form-control" id="email" aria-describedby="emailHelp" name="email">
                        <!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Geslo</label>
                        <input type="password" class="form-control" id="password" name="geslo">
                    </div>
                    <!-- <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="exampleCheck1">
                        <label class="form-check-label" for="exampleCheck1">Check me out</label>
                    </div> -->
                    <div class="form-group">
                        <span style="color:red;"><?= $errorMessage ?></span>
                    </div>
                    <button type="submit" class="btn btn-success">Prijava</button>
                </form>
            </div>
        </div>
        <div class="row my-3">
            <div class="col py-3">
                <h4>Še nimaš računa?</h4>
                <a href="<?= BASE_URL . "registracija" ?>"><button class="btn btn-primary my-2">
                        Registriraj se
                    </button></a>
            </div>
        </div>
    </div>
</body>

</html>