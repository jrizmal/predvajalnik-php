<?php
function createLoginButton()
{
  $basedir = BASE_DIR;
  $baseurl = BASE_URL;
  return "
  <a class='nav-link p-0' href='{$baseurl}prijava/'><button class='btn btn-outline-success my-2 my-sm-0'>
  Prijava
</button><span class='sr-only'>(current)</span></a>";
}
function createDropdown($user = null)
{
  $basedir = BASE_DIR;
  $baseurl = BASE_URL;
  return "<li class='nav-item dropdown'>
  <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
      {$user["name"]}
  </a>
  <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
      <a class='dropdown-item' href='{$baseurl}priljubljeni/'>Moji priljubljeni</a>
      <a class='dropdown-item' href='{$baseurl}moji/'>Moji seznami</a>
      <div class='dropdown-divider'></div>
      <a class='dropdown-item' href='{$baseurl}profil/'>Moj profil</a>
      <a class='dropdown-item' href='{$baseurl}odjava/'>Odjava</a>
  </div>
</li>";
}
function createNav()
{
  $status = session_status();
  if ($status == PHP_SESSION_NONE) {
    //There is no active session
    session_start();
  }
  $basedir = BASE_DIR;
  $baseurl = BASE_URL;
  $loggedIn = isset($_SESSION['loggedIn']) && !empty($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;
  if ($loggedIn) {
    $user = $_SESSION['user'];
    // Return navbar with user
    return "<nav class='navbar navbar-expand-lg navbar-light bg-light'>

    <a class='navbar-brand' href='{$baseurl}'>
        <img src='{$basedir}static/icon/favicon.ico' alt='Logo image' class='mr-2 mb-1'>Predvajalnik</a>
    <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarSupportedContent' aria-controls='navbarSupportedContent' aria-expanded='false' aria-label='Toggle navigation'>
        <span class='navbar-toggler-icon'></span>
    </button>

    <div class='collapse navbar-collapse' id='navbarSupportedContent'>
        <ul class='navbar-nav mr-auto'>
            <li class='nav-item active'>
                <a class='nav-link' href='{$baseurl}vsi/'>Vsi seznami predvajanja<span class='sr-only'>(current)</span></a>
            </li>
            <li class='nav-item'>
                <a class='nav-link' href='{$baseurl}lestvica/'>Lestvica najboljših</a>
            </li>" . createDropdown($user) . "
        </ul>
    </div>
</nav>";
  } else {
    // Return generic navbar

    return "<nav class='navbar navbar-expand-lg navbar-light bg-light'>

    <a class='navbar-brand' href='{$baseurl}'>
        <img src='{$basedir}static/icon/favicon.ico' alt='Logo image' class='mr-2 mb-1'>Predvajalnik</a>
    <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarSupportedContent' aria-controls='navbarSupportedContent' aria-expanded='false' aria-label='Toggle navigation'>
        <span class='navbar-toggler-icon'></span>
    </button>

    <div class='collapse navbar-collapse' id='navbarSupportedContent'>
        <ul class='navbar-nav mr-auto'>
            <li class='nav-item active'>
                <a class='nav-link' href='#'>Vsi seznami predvajanja<span class='sr-only'>(current)</span></a>
            </li>
            <li class='nav-item'>
                <a class='nav-link' href='lestvica/'>Lestvica najboljših</a>
            </li>
            
        </ul>
        " . createLoginButton() . "
    </div>
</nav>";
  }
}
