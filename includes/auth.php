<?php

require_once __DIR__ . '/functions.php';

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('login.php');
    }
}
