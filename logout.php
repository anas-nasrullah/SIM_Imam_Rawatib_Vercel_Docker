<?php
require __DIR__.'/config.php';verify_post();$_SESSION=[];session_regenerate_id(true);go('login.php');
