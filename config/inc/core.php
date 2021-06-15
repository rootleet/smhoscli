<?php

    session_start();
    //requirements
    $srv_root = $_SERVER['DOCUMENT_ROOT'];

    require 'functions.php';
    //core values
    $db_host = 'localhost';
    $db_user = 'root';
    $db_password = "Sunderland@411";
    $db = 'SMHOS';
    $route = database_connect($db_host, $db_user, $db_password, $db);
    $local_sqlite = $srv_root.'config/database/phpsqlite.db';
    $l_route = new PDO("sqlite:$local_sqlite");




    $today = date('M/d/Y');
    $time = date("H:m:i");

    $payment_options = array(
        "Cash Payment"=>1,
        "Momo Payment"=>2,
        "Card Payment"=>3
    );



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

            if(!isset($_SESSION['bill_total']))
            {
                $_SESSION['bill_total'] = 0;
            }

            if(!isset($_SESSION['p_method']))
            {
                $_SESSION['p_method'] = 0;
            }
            $p_method = $_SESSION['p_method'];

            

            //current_bill
            $current_bill = get_row("bookings", "`date_booked` = '$today'", $route) + 1;

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

            $facilities_sql = "SELECT * FROM `facilities` WHERE `facCat` = $act_cat ORDER BY `name` ASC";
            $facilities_stmt = $route->prepare($facilities_sql);
            $facilities_stmt->execute();

            //get first facility
            if(!isset($_SESSION['act_fac']))
            {
                $first_fac = query("SELECT * FROM `facilities` WHERE `facCat` = $act_cat ORDER BY `name` ASC LIMIT 1",$route);
                $_SESSION['act_fac'] = $first_fac['id'];
            }
            $act_fac = $_SESSION['act_fac'];




            $bill = $_SESSION['bill_item'];

            $bil_total= price_value($_SESSION['bill_total']);

            //get total bill
            if(count($bill) > 0)
            {

            }

            //check if current bill has some stuffs
            $sql = "SELECT * FROM `current_bill` ORDER BY `id` DESC";
            $stmt = $l_route->prepare($sql);
            $stmt->execute();

            if ($data = $stmt->fetch()) {
                $bill_exist = 'yes';
                $bill_total = getSumOfColumn("current_bill",'total_amount',"`status` = 0",$l_route);
            } else {
                $bill_exist = 'no';
            }

            //get bill from local storage
            $bill_sql = "SELECT * FROM `current_bill` ORDER BY `id` DESC";
            $bill_stmt = $l_route->prepare($bill_sql);
            $bill_stmt->execute();

            $bill_count = $bill_stmt->rowCount();

            //get tax
            $tax_sql = "SELECT * FROM `current_tax` ORDER BY `id` DESC";
            $tax_stmt = $l_route->prepare($tax_sql);
            $tax_stmt->execute();
            if($tax_e = $tax_stmt->fetch())
            {
                $tax_exist = 'yes';
            } else {
                $tax_exist = 'no';
            }

            $tax_sql_x = "SELECT * FROM `current_tax` ORDER BY `id` DESC";
            $tax_stmt_x = $l_route->prepare($tax_sql_x);
            $tax_stmt_x->execute();




        }

    }

