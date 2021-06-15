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



            $facility = htmlentities($_POST['facility']);
            $duration = dateDifference($checkin_date, $checkout_date);
            $quantity = htmlentities($_POST['qty']);

            //get facility details
            $fd = get_row("facilities", "`id` = $facility",$route);
            $fac_name = $fd['name'];
            $fac_price = $fd['cost'];
            $tax_grp = $fd['tax_group'];

            //taxable amount
            $taxable_amount = price_value($duration * $quantity * $fac_price);
            br("Facility : $fac_name");
            br("Quantity : $quantity");
            br("Duration : $duration");
            br("Price : $fac_price");
            br("Cost : $taxable_amount");

            $amount_to_pay = $duration * $quantity * $fac_price;

            //get tax
            $tax_details = get_row("tax_master","`id` = $tax_grp", $route);
            $tax_desc = $tax_details['description'];
            $tax_rate = $tax_details['rate'];
            $tax_value = price_value($tax_rate / 100 * $amount_to_pay);
            $total_amount = price_value($tax_value + $amount_to_pay);

            br("Tax Description : $tax_desc");
            br("Tax Rate : $tax_rate%");
            br("Total Amount : $total_amount");

            $sql = "INSERT INTO `current_bill` 
            (`bill_number` , `facility` , `qty` , `duration` , `price` , `tax_desc`,`tax_rate`,`taxable_amount`,`total_amount`) VALUES
            ('$current_bill' , '$fac_name' , $quantity , $duration, '$fac_price' , '$tax_desc' , $tax_rate , '$taxable_amount','$total_amount')";

            if($l_route->exec($sql))
            {
                //check if vat exist
                $sql = "SELECT * FROM `current_tax` WHERE `t_desc` = '$tax_desc'";
                $stmt = $l_route->prepare($sql);
                $stmt->execute();

                if ($data = $stmt->fetch()) {
                    $update = "update current_tax set amount_value = amount_value + $tax_value where t_desc = 'VAT'";
                    $l_route->exec($update);
                    br("Updated");
                } else {
                    $insert = "INSERT INTO `current_tax` (`bill`,`t_desc`,`rate`,`amount_value`) VALUES ('$current_bill' , '$tax_desc' , '$tax_rate' , '$tax_value')";
                    $l_route->exec($insert);
                    br($tax_desc." Added");
                }
            }

            gb();

            die();

            $sql = "INSERT INTO `current_bill` (`bill_number`,`bill_item`,`item_quantity`,`duration`) VALUES ('$current_bill','$facility','$quantity','$duration')";
            $stmt = $l_route->prepare($sql);
            $stmt->execute();



            $bill = array($facility,"$duration:$quantity");
            array_push($_SESSION['bill_item'],$bill);






            gb();


        }

        //book
        if(isset($_POST['book']))
        {
//            "Cash Payment"=>1,
//            "Momo Payment"=>2,
//            "Card Payment"=>3

            $payment_method = $_SESSION['p_method'];
            $bill_total = htmlentities($_POST['bill_total']);
            $customer = htmlentities($_POST['customer_name']);

//            $_SESSION['tax'] = array(
//
//            );

            if(!isset($_SESSION['tax']))
            {
                $_SESSION['tax'] = [
//                    array(
//                        'tax_description' => 'TAX DESCRIPTTION',
//                        'tax_rate' => 'TAX RATE',
//                        'tax_value' => 'VALUE'
//                    )
                ];
            }

            $bill_item = $_SESSION['bill_item']; //bill items
            $bill_items_count = 0;

            //loop through each bill item with a while loop
            while($bill_items_count < count($_SESSION['bill_item']))
            {
                ##current bill item
                $current_item = $_SESSION['bill_item'][$bill_items_count];

                ## each bill as an array format array($facility,"$duration:$quantity")
                $facility_id = $current_item[0]; //facility id
                $duration_quantity_explode = explode(':',$current_item[1]);
                $duration = $duration_quantity_explode[0];
                $quantity = $duration_quantity_explode[1];

                //get facility row
                $facility_details = get_row('facilities', "`id` = $facility_id", $route);

                //facility details
                $facility_name = $facility_details['name'];
                $facility_cost = $facility_details['cost'];
                $facility_cost_actual_cost = $duration * $quantity * $facility_cost;
                $facility_tax_grp = $facility_details['tax_group'];

                //echo $facility_name.'<br>'.$facility_cost.'<br>';

                //get tax details
                $tax_details = get_row("tax_master", "`id` = $facility_tax_grp", $route);
                $tax_description = $tax_details['description'];
                $tax_rate = $tax_details['rate'];
                $tax_value = $tax_rate / 100 * $facility_cost_actual_cost;

                //echo 'Tax Group : '.$tax_description.'<br> Tax Rate : '.$tax_rate.'<br> Tax Value : '. $tax_value . '<br>';

                //check tax
                $tax_count = 0;

//               array(
//                   'tax_description' => 'TAX DESCRIPTTION',
//                   'tax_rate' => 'TAX RATE',
//                   'tax_value' => 'VALUE'
//               )

                if(count($_SESSION['tax']) < 1)
                {
                    //add tax
                    $tax_to_push = array(
                        'tax_description' => $tax_description,
                        'tax_rate' => $tax_rate.'%',
                        'tax_value' => $tax_value
                    );

                    array_push($_SESSION['tax'], $tax_to_push);
                    echo "first tax pushed <br>";
                }
                else
                {
                    echo '<br>there is tax <br>';
                    while($tax_count < count($_SESSION['tax']))
                    {
//                    array(
//                        'tax_description' => 'TAX DESCRIPTTION',
//                        'tax_rate' => 'TAX RATE',
//                        'tax_value' => 'VALUE'
//                    )

                        $current_tax = $_SESSION['tax'][$tax_count];

                        //get current tax session detail
                        $tax_session_description = $current_tax['tax_description']; //description

                        $item_tax = $tax_description;
                        //check if description
                        $tax_exist = 0;
                        if($tax_session_description === $item_tax)
                        {
                            $tax_exist = 1;
                            $tax_session_value = $current_tax['tax_value'];
                            $new_value = $tax_session_value + $tax_value;

                            $tax_session_value = $new_value;

                            echo "Old Value : " . $tax_session_value . '<br>';
                            echo 'New Value : '. $new_value . '<br>';
                        }
                        else
                        {
                            $tax_exist = 0;
                        }

//                        var_dump($_SESSION['tax']);



                        $tax_count ++;
                    }
                }



                $bill_items_count ++;
            }


        }
    }

    ## if getting request
    if($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        ### CANCEL BILL
        if (isset($_GET['cancel_bill']))
        {
            //delete all locally stored bills
            $delete = "DELETE FROM `current_bill` WHERE `status` = 0";
            $l_route->exec($delete);
            //delete from tax
            $delete_tax = "DELETE FROM `current_tax` WHERE `status` = 0";
            $l_route->exec($delete_tax);

            gb();
        }
        ##SELECT PAYMENT
        elseif (isset($_GET['sel_pmt']))
        {

            $option = $_GET['sel_pmt'];
            $_SESSION['p_method'] = $option;
            if($option === '2')
            {
                $_SESSION['c_stg'] = 'type';
            }
            elseif($option === '3')
            {

                $_SESSION['c_stg'] = 'network';
            }
            gb();
        }
        ## setting card payment type
        elseif(isset($_GET['c_type']))
        {
            $_SESSION['c_type'] = $_GET['c_type'];
            $_SESSION['c_stg'] = 'number';
            gb();
        }

        ## momo carrier
        elseif (isset($_GET['momo_carrier']))
        {
            $_SESSION['momo_carrier'] = $_GET['momo_carrier'];
            $_SESSION['c_stg'] = 'payment_details';
            gb();
        }

        ##delete bill item
        elseif(isset($_GET['del_bill_item']))
        {
            $bill_item = $_GET['item'];
            //get bill item details
            $bo_d = get_row("current_bill","`id` = $bill_item", $l_route);
            $bill_tax_description = $bo_d['tax_desc'];
            $bill_tax_value = price_value($bo_d['total_amount'] - $bo_d['taxable_amount']);

            //get tax details
            $curr_tax_d = get_row("current_tax", "`t_desc` = '$bill_tax_description'", $l_route);
            $t_desc = $curr_tax_d['t_desc'];
            $tax_value = price_value($curr_tax_d['amount_value']);

            //compare bill tax value and curr vat value
            if($bill_tax_value === $tax_value)
            {
                //delete vat
                delete("current_tax","`t_desc` = '$t_desc'",$l_route);
            }
            else
            {
                //sub from vat value
                $tv = $tax_value - $bill_tax_value;
                update("current_tax","`t_desc` = '$t_desc'","`amount_value` = $tv",$l_route);
            }

            //delete bill
            delete("current_bill","`id` = $bill_item",$l_route);
            gb();
        }


    }
