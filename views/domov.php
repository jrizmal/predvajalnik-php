<!DOCTYPE html>
<html lang="en">

<head>
<?php

include "./components/header.component.php";
echo createHeaders("Domov");

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
                <h3>Dobrodošli!</h3>
            </div>
        </div>
    </div>
</body>

</html>