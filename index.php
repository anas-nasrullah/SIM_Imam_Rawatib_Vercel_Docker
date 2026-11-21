<?php
require __DIR__.'/config.php';
if(!db()->query('SELECT COUNT(*) FROM users')->fetchColumn()) go('setup.php');
if(!user())go('login.php');
go(login_destination());
