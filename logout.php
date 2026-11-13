<?php require_once __DIR__.'/includes/bootstrap.php';check_post();$_SESSION=[];session_regenerate_id(true);flash('success','You have signed out.');redirect('index.php');
