<?php
    require '../inc/core.php';

    ## if we are posting a form
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        //add to bill
        if(isset($_POST['add_to_bill']))
        {
            //get from to
            $checkin_date = htmlspecialchars($_POST['checkin_date']);
            $checkout_date = htmlspecialchars($_POST['checkout_date']);

            //get days different
            $days = dateDifference($checkin_date, $checkout_date);
            echo $days;
        }
    }
