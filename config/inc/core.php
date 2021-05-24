<?php

    session_start();
    //requirements
    require 'functions.php';
    //core values
    $db_host = 'localhost';
    $db_user = 'root';
    $db_password = "Sunderland@411";
    $db = 'SMHOS';
    $route = database_connect($db_host, $db_user, $db_password, $db);



    //check if login
    if(is_logged_in('cli_login'))
    {
        //get user details
        $clerk_id = $_SESSION['clerk_id'];
        $clerk = get_row("clerk" , "`id` = $clerk_id", database_connect($db_host, $db_user, $db_password, $db));

        $my_code = $clerk['clerk_code'];

        //configuration
        if(!isset($_SESSION['view']))
        {
            $_SESSION['view'] = 'welcome';
        }
        $view = $_SESSION['view'];

        if(!isset($_SESSION['main']))
        {
            $_SESSION['main'] = 'none';
        }
        $main = $_SESSION['main'];

        //configure mains
        if($main === 'bill')
        {
            if (!isset($_SESSION['bill_item']))
            {
                $_SESSION['bill_item'] = [];
            }

            //active category
            if (!isset($_SESSION['act_cat']))
            {
                //get category
                $category = query("SELECT `id` FROM `facCat` LIMIT 1", database_connect($db_host, $db_user, $db_password, "SMHOS"));
                $_SESSION['act_cat'] = $category['id'];
            }

            $act_cat = $_SESSION['act_cat'];

            //get categories
            $cat_sql = "SELECT * FROM `facCat` ORDER BY `name`";
            $cat_stmt = $route->prepare($cat_sql);
            $cat_stmt->execute();

            //get facilities
            $facilities_count = row_count("facilities" , "`facCat` = $act_cat", database_connect($db_host, $db_user, $db_password, $db));

            $facilities_sql = "SELECT * FROM `facilities` WHERE `facCat` = $act_cat ORDER BY `name`";
            $facilities_stmt = $route->prepare($facilities_sql);
            $facilities_stmt->execute();



            $bill = $_SESSION['bill_item'];

            //array_push($_SESSION['bill_item'], array(18,"1:1"));


//            $bill_count = 0;
//            while ($bill_count < count($_SESSION['bill_item']))
//            {
//                $d = $_SESSION['bill_item'][$bill_count];
//                $facility = $d[0];
//                $quantity = $d[1];
//
//                echo "Facility : ".$facility . '<br>';
//                echo "Quantity ".$quantity . '<br>';
//                $bill_count ++;
//            }
//
//            die();

        }

    }

