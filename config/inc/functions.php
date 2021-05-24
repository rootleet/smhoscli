<?php

    ##check if user logged in
    function is_logged_in($session_variable)
    {
        if(isset($_SESSION[$session_variable]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    ##prepare_sql
    function prepare_query($query, $connection)
    {
        return $connection->prepare($query);
    }

    ## execute stmt
    function execute_query($stmt)
    {
        return $stmt->execute();
    }

    ##connect to database
    function database_connect($host , $user, $password, $db)
    {
        //set DSN
        $dns = 'mysql:host='.$host.';dbname='.$db;

        //create pdo instanse
        $pdo = new PDO($dns,$user,$password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    }

    ##count database rows
    function row_count($table , $condition, $database_connection)
    {
        if ($condition === 'none')
        {
            $sql = "SELECT * FROM `$table`";
        }
        else
        {
            $sql = "SELECT * FROM `$table` WHERE $condition";
        }

        $stmt = prepare_query($sql, $database_connection);
        $stmt->execute();
        return $stmt->rowCount();


    }

    ##fech row
    function get_row($table, $condition, $connection)
    {
        if ($condition === 'none')
        {
            $sql = "SELECT * FROM `$table`";
        }
        else
        {
            $sql = "SELECT * FROM `$table` WHERE $condition";
        }

        $stmt = prepare_query($sql, $connection);
        execute_query($stmt);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    ## row with limit
    function get_row_w_limit($table, $condition, $limit, $connection)
    {
        if ($condition === 'none')
        {
            $sql = "SELECT * FROM `$table`";
        }
        else
        {
            $sql = "SELECT * FROM `$table` WHERE $condition";
        }

        $stmt = prepare_query($sql, $connection);
        execute_query($stmt);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    ##execute query
    function query($sql, $connection)
    {
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    ##compare md5 keys
    function compare_two_strings($key1,$key2)
    {
        if($key1 === $key2)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    ##go back to previous page
    function gb()
    {
        header("Location:".$_SERVER['HTTP_REFERER']);
    }

    ##set error
    function error($message)
    {
        $_SESSION['error'] = $message;
    }

    ##show error
    function show_error()
    {
        if (isset($_SESSION['error']))
        {
            echo $_SESSION['error'];
            unset($_SESSION['error']);
        }
    }

    ##logout function
    function logout()
    {
        // Unset all of the session variables
        $_SESSION = array();

        // Destroy the session.
        session_destroy();
    }

    ##set session
    function set_session($variable, $value)
    {
        $_SESSION[$variable] = $value;
    }

    ##get image data
    function image_src($image)
    {
    // Read image path, convert to base64 encoding
        $imageData = base64_encode(file_get_contents($image));

    // Format the image SRC:  data:{mime};base64,{data};
        return 'data: '.mime_content_type($image).';base64,'.$imageData;
    }

    ##date difference
    function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
    {

        //////////////////////////////////////////////////////////////////////
        //PARA: Date Should In YYYY-MM-DD Format
        //RESULT FORMAT:
        // '%y Year %m Month %d Day %h Hours %i Minute %s Seconds'        =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
        // '%y Year %m Month %d Day'                                    =>  1 Year 3 Month 14 Days
        // '%m Month %d Day'                                            =>  3 Month 14 Day
        // '%d Day %h Hours'                                            =>  14 Day 11 Hours
        // '%d Day'                                                        =>  14 Days
        // '%h Hours %i Minute %s Seconds'                                =>  11 Hours 49 Minute 36 Seconds
        // '%i Minute %s Seconds'                                        =>  49 Minute 36 Seconds
        // '%h Hours                                                    =>  11 Hours
        // '%a Days                                                        =>  468 Days
        //////////////////////////////////////////////////////////////////////

        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);

        $interval = date_diff($datetime1, $datetime2);

        return $interval->format($differenceFormat);

    }



