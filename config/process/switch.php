<?php
    require '../inc/core.php';

    if (isset($_GET['tri']))
    {
        $trigger = $_GET['tri'];

        if ($trigger === 'logout')
        {
            logout();
        }
        else
        {
            $_SESSION['view'] = $trigger;
            if($trigger === 'bill')
            {
                $_SESSION['main'] = 'bill';
            }
        }

        gb();

    }
    elseif (isset($_GET['main']))
    {
        $main = $_GET['main'];
        $_SESSION['main'] = $main;
        gb();
    }

