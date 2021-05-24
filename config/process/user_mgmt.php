<?php
    require '../inc/core.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        ##login
        if (isset($_POST['login']))
        {
            $clerk_code = htmlspecialchars($_POST['clerk_code']);
            $clerk_key = htmlspecialchars($_POST['clerk_key']);





            //check if clerk exist
            if (row_count("clerk" , "`clerk_code` = '$clerk_code'", database_connect($db_host, $db_user, $db_password, "SMHOS")) < 1)
            {
                error("clerk does not exist");
            }
            else
            {
                echo "clerk exist";
                $clerk_details = get_row("clerk" , "`clerk_code` = '$clerk_code'", database_connect($db_host, $db_user, $db_password, "SMHOS"));

                $clerk_db_key = $clerk_details['clerk_key'];
                //compare keys
                if (compare_two_strings(md5($clerk_key) , $clerk_db_key))
                {

                    //start sessions
                    $_SESSION['cli_login'] = true;
                    $_SESSION['clerk_id'] = $clerk_details['id'];
                    $_SESSION['view'] = 'welcome';

                }

                else
                {
                    set_session('clerk_code', $clerk_code);
                    error("Wrong key combination");
                }
            }
            gb();
        }
    }
