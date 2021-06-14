<?php
    session_start();
    require '../inc/functions.php';

    if($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        if(isset($_GET['calculate_cash']))
        {
            $value = $_GET['value'];
            echo price_value($value);
        }
    }