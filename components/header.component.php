<?php

function createHeaders($naslovStrani="")
{
    $basedir = BASE_DIR;
    if(empty($naslovStrani)){
        return "<meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Predvajalnik</title>
        <link rel='shortcut icon' href='{$basedir}static/icon/favicon.ico' type='image/x-icon'>
        <link rel='stylesheet' href='{$basedir}static/css/bootstrap.min.css'>
        <script src='{$basedir}static/js/jquery-3.5.1.min.js'></script>
        <script src='{$basedir}static/js/bootstrap.bundle.min.js'></script>";
    }else{
        return "<meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Predvajalnik - {$naslovStrani}</title>
    <link rel='shortcut icon' href='{$basedir}static/icon/favicon.ico' type='image/x-icon'>
    <link rel='stylesheet' href='{$basedir}static/css/bootstrap.min.css'>
    <script src='{$basedir}static/js/jquery-3.5.1.min.js'></script>
    <script src='{$basedir}static/js/bootstrap.bundle.min.js'></script>";
    }
    
}
