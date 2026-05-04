<?php

/**
 * Database.php
 * 
 * The Database class is meant to simplify the task of accessing
 * information from the website's database.
 *
 */
ob_start();
include("constants.php");
include("ChromePhp.php");

class MySQLDB
{
    public $connection; //The MySQL database connection
    public $num_active_users; //Number of active users viewing site
    public $num_active_guests; //Number of active guests viewing site
    public $num_members; //Number of signed-up users
    /* Note: call getNumMembers() to access $num_members! */

    /* Class constructor */
    function __construct()
    {
        /* Make connection to database */
        try {
            # MySQL with PDO_MYSQL
            $this->connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error connecting to database.";
        }

        /**
         * Only query database to find out number of members
         * when getNumMembers() is called for the first time,
         * until then, default value set.
         */
        $this->num_members = -1;
        $config            = $this->getConfigs();
        if ($config['TRACK_VISITORS']) {
            /* Calculate number of users at site */
            $this->calcNumActiveUsers();
            /* Calculate number of guests at site */
            $this->calcNumActiveGuests();
        }

        /* Set session time zone */
        $this->setSessionTimeZone();
    } // MySQLDB function

    /**
     * Gather together the configs from the database configuration table.
     */
    function getConfigs()
    {
        $config = array();
        $query = "SELECT * FROM " . TBL_CONFIGURATION;
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $config[$row['config_name']] = $row['config_value'];
        }
        return $config;
    }

    /**
     * Set the session time zone as the server time zone can differ from the user base.
     */
    function setSessionTimeZone()
    {
        try {
            $query = "SET time_zone = 'Pacific/Auckland'";
            $stmt  = $this->connection->prepare($query);
            $stmt->execute();
        } catch (Exception $e) {
            $query = "SET time_zone = '+12:00'";
            $stmt  = $this->connection->prepare($query);
            $stmt->execute();
        }
    }

    /**
     * Update Configs - updates the configuration table in the database
     * 
     */
    function updateConfigs($value, $configname)
    {
        $query = "UPDATE " . TBL_CONFIGURATION . " SET config_value = :value WHERE config_name = :configname";
        $stmt  = $this->connection->prepare($query);
        return $stmt->execute(array(
            ':value' => $value,
            ':configname' => $configname
        ));
    }

    /**
     * confirmUserPass - Checks whether or not the given username is in the database, 
     * if so it checks if the given password is the same password in the database
     * for that user. If the user doesn't exist or if the passwords don't match up, 
     * it returns an error code (1 or 2). On success it returns 0.
     */
    function confirmUserPass($username, $password)
    {
        /* Verify that user is in database */
        $query = "SELECT user_pass, user_level, user_salt, user_activated FROM " . TBL_USERS . " WHERE user_login = :user_login";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':user_login' => $username
        ));
        $count = $stmt->rowCount();

        if (!$stmt || $count < 1) {
            return 1; //Indicates username failure
        }

        /* Retrieve password and userlevel from result, strip slashes */
        $dbarray = $stmt->fetch();

        $dbarray['user_level'] = stripslashes($dbarray['user_level']);
        $dbarray['user_salt']  = stripslashes($dbarray['user_salt']);
        $password             = stripslashes($password);

        $sqlpass = sha1($dbarray['user_salt'] . $password);

        /* Validate that password matches and check if userlevel is equal to 1 
        if (($dbarray['password'] == $sqlpass) && ($dbarray['userlevel'] == 1)) {
            return 3; //Indicates account has not been activated
        }*/

        /* Validate that password matches and check if userlevel is equal to 2 */
        if (($dbarray['user_pass'] == $sqlpass) && ($dbarray['user_activated'] == 0)) {
            return 4; //Indicates admin has not activated account
        }

        /* Validate that password is correct */
        if ($dbarray['user_pass'] == $sqlpass) {
            return 0; //Success! Username and password confirmed
        } else {
            return 2; //Indicates password failure
        }
    }

    /**
     * confirmUserID - Checks whether or not the given username is in the database, 
     * if so it checks if the given userid is the same userid in the database
     * for that user. If the user doesn't exist or if the userids don't match up, 
     * it returns an error code (1 or 2). On success it returns 0.
     */
    function confirmUserID($username, $userid)
    {
        /* Verify that user is in database */
        $query = "SELECT user_id FROM " . TBL_USERS . " WHERE user_login = :user_login";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':user_login' => $username
        ));
        $count = $stmt->rowCount();

        if (!$stmt || $count < 1) {
            return 1; //Indicates username failure
        }

        $dbarray = $stmt->fetch();

        /* Retrieve userid from result, strip slashes */
        $dbarray['user_id'] = stripslashes($dbarray['user_id']);
        $userid            = stripslashes($userid);

        /* Validate that userid is correct */
        if ($userid == $dbarray['user_id']) {
            return 0; //Success! Username and userid confirmed
        } else {
            return 2; //Indicates userid invalid
        }
    }

    /**
     * usernameTaken - Returns true if the username has been taken by another user, false otherwise.
     */
    function usernameTaken($username)
    {
        $query = "SELECT user_login FROM " . TBL_USERS . " WHERE user_login = :user_login";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':user_login' => $username
        ));
        $count = $stmt->rowCount();
        return ($count > 0);
    }


    function addNewUser($email, $password, $token, $usersalt, $firstname, $lastname, $phone, $level)
    {
        $time   = time();
        $config = $this->getConfigs();

        if ($config['ACCOUNT_ACTIVATION'] == 1) {
            $ulevel = OPERATOR_LEVEL;
        } else if ($config['ACCOUNT_ACTIVATION'] == 2) {
            $ulevel = ACT_EMAIL;
        } else if ($config['ACCOUNT_ACTIVATION'] == 3) {
            $ulevel = ADMIN_ACT;
        }
        $password = sha1($usersalt . $password);
        $userip   = $_SERVER['REMOTE_ADDR'];
        if ($level == 9) {
            $query = "INSERT INTO " . TBL_USERS . " SET user_login = :email, user_pass = :password, user_salt = :usersalt, user_id = 0, user_level = $level, user_email = :email, user_timestamp = $time, user_activation_key = :token, user_activated='0', user_registered = $time, user_firstname = '$firstname', user_lastname = '$lastname', user_phone = '$phone'";
        } else {
            $query = "INSERT INTO " . TBL_USERS . " SET user_login = :email, user_pass = :password, user_salt = :usersalt, user_id = 0, user_level = $level, user_email = :email, user_timestamp = $time, user_activation_key = :token, user_activated='0', user_registered = $time, user_firstname = '$firstname', user_lastname = '$lastname', user_phone = '$phone'";
        }

        $stmt  = $this->connection->prepare($query);
        return $stmt->execute(array(
            ':username' => $email,
            ':password' => $password,
            ':usersalt' => $usersalt,
            ':email' => $email,
            ':token' => $token
        ));
    }

    /**
     * updateUserField - Updates a field, specified by the field
     * parameter, in the user's row of the database.
     */
    function updateUserField($username, $field, $value)
    {
        $query = "UPDATE " . TBL_USERS . " SET " . $field . " = :value WHERE user_login = :username";
        $stmt  = $this->connection->prepare($query);
        return $stmt->execute(array(
            ':username' => $username,
            ':value' => $value
        ));
    }

    /**
     * getUserInfo - Returns the result array from a mysql
     * query asking for all information stored regarding
     * the given username. If query fails, NULL is returned.
     */
    function getUserInfo($username)
    {
        $query = "SELECT * FROM " . TBL_USERS . " WHERE user_login = :username";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':username' => $username
        ));
        $dbarray = $stmt->fetch();
        /* Error occurred, return given name by default */
        $result  = count($dbarray);
        if (!$dbarray || $result < 1) {
            return NULL;
        }
        /* Return result array */
        return $dbarray;
    }

    function displayUsersOptions()
    {

        $query = "SELECT * FROM " . TBL_USERS . " ORDER BY ID ASC";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    /**
     * checkUserEmailMatch - Checks whether username
     * and email match in forget password form.
     */
    function checkUserEmailMatch($username, $email)
    {

        $query = "SELECT user_login FROM " . TBL_USERS . " WHERE user_login = :username AND user_email = :email";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':username' => $username,
            ':email' => $email
        ));
        $number_of_rows = $stmt->rowCount();

        if (!$stmt || $number_of_rows < 1) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * getNumMembers - Returns the number of signed-up users
     * of the website, banned members not included. The first
     * time the function is called on page load, the database
     * is queried, on subsequent calls, the stored result
     * is returned. This is to improve efficiency, effectively
     * not querying the database when no call is made.
     */
    function getNumMembers()
    {
        if ($this->num_members < 0) {
            $result            = $this->connection->query("SELECT user_login FROM " . TBL_USERS);
            $this->num_members = $result->rowCount();
        }
        return $this->num_members;
    }

    /**
     * getLastUserRegistered - Returns the username of the last
     * member to sign up and the date.
     */
    function getLastUserRegisteredName()
    {
        $result             = $this->connection->query("SELECT user_login, user_registered FROM " . TBL_USERS . " ORDER BY user_registered DESC LIMIT 0,1");
        $this->lastuser_reg = $result->fetchColumn();
        return $this->lastuser_reg;
    }

    /**
     * getLastUserRegistered - Returns the username of the last
     * member to sign up and the date.
     */
    function getLastUserRegisteredDate()
    {
        $result             = $this->connection->query("SELECT user_login, user_registered FROM " . TBL_USERS . " ORDER BY user_registered DESC LIMIT 0,1");
        $this->lastuser_reg = $result->fetchColumn(1);
        return $this->lastuser_reg;
    }

    /**
     * calcNumActiveUsers - Finds out how many active users
     * are viewing site and sets class variable accordingly.
     */
    function calcNumActiveUsers()
    {
        /* Calculate number of USERS at site */
        $sql                    = $this->connection->query("SELECT * FROM " . TBL_ACTIVE_USERS);
        $this->num_active_users = $sql->rowCount();
    }

    /**
     * calcNumActiveGuests - Finds out how many active guests
     * are viewing site and sets class variable accordingly.
     */
    function calcNumActiveGuests()
    {
        /* Calculate number of GUESTS at site */
        $sql                     = $this->connection->query("SELECT * FROM " . TBL_ACTIVE_GUESTS);
        $this->num_active_guests = $sql->rowCount();
    }

    /**
     * addActiveUser - Updates username's last active timestamp
     * in the database, and also adds him to the table of
     * active users, or updates timestamp if already there.
     */
    function addActiveUser($username, $time)
    {
        $config = $this->getConfigs();

        // new - this checks how long someone has been inactive and logs them off if neccessary unless
        // they have cookies (remember me) set.

        $query = "SELECT * FROM " . TBL_USERS . " WHERE user_login = :username";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':username' => $username
        ));

        $dbarray      = $stmt->fetch();
        $db_timestamp = $dbarray['user_timestamp'];
        $timeout      = time() - $config['USER_TIMEOUT'] * 60;
        if ($db_timestamp < $timeout && !isset($_COOKIE['ediary_cookname']) && !isset($_COOKIE['ediary_cookid']))
            header("Location:" . $config['WEB_ROOT'] . "process");

        $query = "UPDATE " . TBL_USERS . " SET user_timestamp = :time, user_last_online = NOW() WHERE user_login = :username";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':username' => $username,
            ':time' => $time
        ));

        if (!$config['TRACK_VISITORS'])
            return;
        $query = "REPLACE INTO " . TBL_ACTIVE_USERS . " VALUES (:username, :time)";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':username' => $username,
            ':time' => $time
        ));

        $this->calcNumActiveUsers();
    }

    /* addActiveGuest - Adds guest to active guests table */
    function addActiveGuest($ip, $time)
    {
        $config = $this->getConfigs();
        if (!$config['TRACK_VISITORS'])
            return;
        $sql = $this->connection->prepare("REPLACE INTO " . TBL_ACTIVE_GUESTS . " VALUES ('$ip', '$time')");
        $sql->execute();
        $this->calcNumActiveGuests();
    }

    /* These functions are self explanatory, no need for comments */

    /* removeActiveUser */
    function removeActiveUser($username)
    {
        $config = $this->getConfigs();
        if (!$config['TRACK_VISITORS'])
            return;
        $sql = $this->connection->prepare("DELETE FROM " . TBL_ACTIVE_USERS . " WHERE username = '$username'");
        $sql->execute();
        $this->calcNumActiveUsers();
    }

    /* removeActiveGuest */
    function removeActiveGuest($ip)
    {
        $config = $this->getConfigs();
        if (!$config['TRACK_VISITORS'])
            return;
        $sql = $this->connection->prepare("DELETE FROM " . TBL_ACTIVE_GUESTS . " WHERE ip = '$ip'");
        $sql->execute();
        $this->calcNumActiveGuests();
    }

    /* removeInactiveUsers */
    function removeInactiveUsers()
    {
        $config = $this->getConfigs();
        if (!$config['TRACK_VISITORS'])
            return;
        $timeout = time() - $config['USER_TIMEOUT'] * 60;
        $stmt    = $this->connection->prepare("DELETE FROM " . TBL_ACTIVE_USERS . " WHERE timestamp < $timeout");
        $stmt->execute();
        $this->calcNumActiveUsers();
    }

    /* removeInactiveGuests */
    function removeInactiveGuests()
    {
        $config = $this->getConfigs();
        if (!$config['TRACK_VISITORS'])
            return;
        $timeout = time() - $config['GUEST_TIMEOUT'] * 60;
        $stmt    = $this->connection->prepare("DELETE FROM " . TBL_ACTIVE_GUESTS . " WHERE timestamp < $timeout");
        $stmt->execute();
        $this->calcNumActiveGuests();
    }

    function userIdToName($user_id)
    {
        $query = "SELECT user_firstname, user_lastname FROM " . TBL_USERS . " WHERE ID = $user_id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        foreach ($stmt as $row) {
            return $row['user_firstname'] . " " . $row['user_lastname'];
        }
    }

    function userLevelToName($user_level)
    {
        if (is_null($user_level)) return 'Invalid User Level';
        $query = "SELECT level_name FROM " . TBL_USER_LEVELS . " WHERE id = $user_level";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        foreach ($stmt as $row) {
            return $row['level_name'];
        }
    }

    /**********************************************/
    /***********E-DIARY FUNCTIONS BELOW************/
    /**********************************************/

    /* Obsufucate id value */
    function translateString($string, $action)
    {
        $secret_key = 'my_simple_secret_key';
        $secret_iv = 'my_simple_secret_iv';

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'e') {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        } else if ($action == 'd') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }

    /* Get operator users */
    function getOperators()
    {
        $query = "SELECT id, CONCAT(user_firstname, ' ', user_lastname) as fullname FROM " . TBL_USERS . " WHERE (user_level = 1 OR user_level = 2 OR user_level = 4) AND ID != 1";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Get trucks with their id and registration */
    function getTrucks()
    {
        $query = "SELECT id, number_plate FROM " . TBL_TRUCKS . " order by boom desc";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        error_log($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* User is declining the quote */
    function userDeclineQuote($quote_id)
    {
        $query = "UPDATE " . TBL_QUOTES . " SET quote_accepted = 0, date_quote_response = NOW() WHERE id = '$quote_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    /* Customer is declining the quote */
    function customerDeclineQuote($quote_id, $reason)
    {
        $query = "UPDATE " . TBL_QUOTES . " SET quote_accepted = 0, date_quote_response = NOW(), customer_decline_reason = '$reason' WHERE id = '$quote_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    /* User accepted quote */
    function userAcceptQuote($quote_id)
    {
        $query = "UPDATE " . TBL_QUOTES . " SET quote_accepted = 1, date_quote_response = NOW() WHERE id = '$quote_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    /* User accepted quote */
    function customerAcceptQuote($quote_id)
    {
        $query = "UPDATE " . TBL_QUOTES . " SET quote_accepted = 1, date_quote_response = NOW() WHERE id = '$quote_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    /* Update quote sent date */
    function quoteWasSent($quote_id)
    {
        $query = "UPDATE " . TBL_QUOTES . " SET date_quote_sent = NOW() WHERE id = '$quote_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    /* Update job operator sent date */
    function jobSentToOperator($job_id)
    {
        $query = "UPDATE " . TBL_JOBS . " SET operator_emailed = NOW() WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }
    function jobSentToLinesman($job_id)
    {
        // $query = "UPDATE " . TBL_JOBS . " SET linesman_emailed = NOW(), status=6 WHERE id = '$job_id'";
        $query = "UPDATE " . TBL_JOBS . " SET linesman_emailed = NOW() WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    /* Update job supplier sent date */
    function jobSentToSupplier($job_id)
    {
        $query = "UPDATE " . TBL_JOBS . " SET supplier_emailed = NOW() WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }
    /* Update job customer sent date */
    function jobSentToCustomer($job_id)
    {
        $query = "UPDATE " . TBL_JOBS . " SET customer_emailed = NOW() WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }
    /* Update job layer sent date */
    function jobSentToLayer($job_id)
    {
        $query = "UPDATE " . TBL_JOBS . " SET layer_emailed = NOW() WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    /* Update job foreman sent date */
    function jobSentToForeman($job_id)
    {
        $query = "UPDATE " . TBL_JOBS . " SET foreman_emailed = NOW() WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    /* ID to name for job type */
    function getNameForJobTypeID($id)
    {
        $query = "SELECT type_name FROM " . TBL_JOB_TYPES . " WHERE id = '$id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* Get customer name fields by id */
    function getCustomerNameFieldsByID($id)
    {
        $query = "SELECT name, first_name, last_name FROM " . TBL_CUSTOMERS . " WHERE id = '$id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* Get customer ids */
    function getCustomerIDs()
    {
        $query = "SELECT id, name FROM " . TBL_CUSTOMERS;
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Get count of quotes */
    function getTotalQuoteCount()
    {
        $query = "SELECT * FROM " . TBL_QUOTES;
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of pending quotes */
    function getTotalPendingQuoteCount()
    {
        $query = "SELECT * FROM " . TBL_QUOTES . " WHERE date_quote_sent IS NOT NULL AND quote_accepted IS NULL";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of not sent quotes */
    function getTotalNotSentQuoteCount()
    {
        $query = "SELECT * FROM " . TBL_QUOTES . " WHERE date_quote_sent IS NULL AND quote_accepted IS NULL";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of declined quotes */
    function getTotalDeclinedQuoteCount()
    {
        $query = "SELECT * FROM " . TBL_QUOTES . " WHERE quote_accepted = 0";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of quotes without jobs created */
    function getTotalAcceptedQuotesWithoutJobs()
    {
        $query = "SELECT * FROM " . TBL_QUOTES . " WHERE quote_accepted = 1 AND job_id IS NULL";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of site visits */
    function getTotalSiteVisitCount()
    {
        $query = "SELECT * FROM " . TBL_SITE_VISITS;
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of non-complete site inspections */
    function getPendingSiteInspectionCount()
    {
        $query = "SELECT * FROM " . TBL_SITE_VISITS . " WHERE site_visit_completed IS NULL";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of operator's non-complete site inspections */
    function getOperatorPendingSiteInspectionCount($operator_id)
    {
        $query = "SELECT * FROM " . TBL_SITE_VISITS . " WHERE site_visit_completed IS NULL AND site_visit_assigned_operator = '$operator_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of jobs */
    function getTotalJobCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS;
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of complete jobs */
    function getTotalCompleteJobsCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE status = 8";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of ready for invoicing jobs */
    function getTotalReadyForInvoicingJobsCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE status = 10";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of complete and invoiced latest 3 months jobs*/
    function getCurrentCompleteInvoicedJobsCount()
    {
        // $query = "SELECT * FROM " . TBL_JOBS . " WHERE status = 11 ORDER BY invoiced_date DESC LIMIT 600";
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE status = 11 AND invoiced_date >= DATE_SUB(NOW(),INTERVAL 3 MONTH) ORDER BY id DESC";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of complete and invoiced jobs jobs */
    function getTotalCompleteInvoicedJobsCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE status = 11";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of in progress jobs */
    function getInProgressJobCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE status = 7 OR status = 9 OR status = 1 OR status = 3 OR status = 5 OR status = 6";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of non-complete jobs */
    function getTotalNotCompleteJobsCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE status = 7 OR status = 9 OR status = 1";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of ready jobs */
    function getTotalReadyJobCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE status = 6";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of ready jobs */
    function getTotalJobsNotAssignedCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE (status != 7 AND status != 2 AND status != 8) AND sent_to_operator = 0";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of site inspection pending jobs */
    function getTotalSiteInspectionPendingCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE status = 5";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of cancelled jobs */
    function getTotalCancelledJobCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE status = 2";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of operator's in progress jobs */
    function getOperatorInProgressJobCount($operator_id)
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE (status = 7 OR status = 9 OR status = 1 OR status = 3 OR status = 5 OR status = 6) AND operator_id = '$operator_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* Get count of jobs this week */
    function getTotalJobsThisWeekCount()
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE yearweek(DATE(job_date), 1) = yearweek(curdate(), 1)";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }


    /* Returns all of a customers information by their internal db id value */
    function getCustomerDetails($customer_id)
    {
        $query = "SELECT * FROM " . TBL_CUSTOMERS . " WHERE id = '$customer_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* Returns all of a Foreman information by their internal db id value */
    function getForemanDetails($foreman_id)
    {
        $query = "SELECT * FROM " . TBL_FOREMEN . " WHERE id = '$foreman_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* Returns all truck information by their internal db id value */
    function getTruckDetails($truck_id)
    {
        if (empty($truck_id)) return false;
        $query = "SELECT * FROM " . TBL_TRUCKS . " WHERE id = $truck_id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    /* Add a new quote to the system */
    function addQuote(
        $customer_id,
        $job_date,
        $job_timing,
        $job_address_1,
        $job_address_2,
        $job_suburb,
        $job_city,
        $job_post_code,
        $range,
        $truck_id,
        $job_type_id,
        $cubics,
        $mpa,
        $concrete_type_id,
        $mix_type_id
    ) {
        $truck = $this->getTruckDetails($truck_id); // assigned truck to job/quote, we use the details as of present to ensure records remain historical
        $customer = $this->getCustomerDetails($customer_id);
        $highest_concrete_charge = $this->getHighestCostingConcrete($concrete_type_id, $mix_type_id);

        $query = "INSERT INTO " . TBL_QUOTES . " SET customer_id = '$customer_id', truck_id = $truck_id, number_plate = :number_plate, truck_boom = :truck_boom, truck_capacity = :truck_capacity, truck_rate = :truck_rate, truck_min = :truck_min, 
                  truck_travel_rate_km = :travel_rate, truck_washout = :washout, truck_disposal_fee = :disposal_fee, quoted_range = '$range', establishment_fee = :establishment_fee, cubic_rate = :cubic_rate, travel_fee = :travel_fee, 
                  estimate_pump_time = :est_pump_time, job_date = '$job_date', job_timing = $job_timing, job_addr_1 = '$job_address_1', job_addr_2 = '$job_address_2', job_suburb = '$job_suburb', 
                  job_city = '$job_city', job_post_code = '$job_post_code', job_type = $job_type_id, cubics = $cubics, mpa = $mpa, concrete_type = $concrete_type_id, mix_type = :mix_type,
                  discount = :discount";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':number_plate' => isset($truck['number_plate']) ? $truck['number_plate'] : NULL,
            ':truck_boom' => isset($truck['boom']) ? $truck['boom'] : NULL,
            ':truck_capacity' => isset($truck['capacity']) ? $truck['capacity'] : NULL,
            ':truck_rate' => isset($truck['hourly_rate']) ? $truck['hourly_rate'] : NULL,
            ':truck_min' => isset($truck['min']) ? $truck['min'] : NULL,
            ':travel_rate' => isset($truck['travel_rate_km']) ? $truck['travel_rate_km'] : NULL,
            ':washout' => isset($truck['washout']) ? $truck['washout'] : NULL,
            ':disposal_fee' => isset($truck['disposal_fee']) ? $truck['disposal_fee'] : NULL,
            ':establishment_fee' => isset($truck['est_fee']) ? $truck['est_fee'] : NULL,
            ':cubic_rate' => is_numeric($highest_concrete_charge) ? $highest_concrete_charge * floatval($cubics) : '0',
            ':travel_fee' => floatval($range) < 21 || is_null($truck['travel_rate_km']) ? 0 : (floatval($range - 20)) * $truck['travel_rate_km'],
            ':est_pump_time' => (floatval($cubics) / 30) * 60,
            ':mix_type' => isset($mix_type_id) ? $mix_type_id : NULL,
            ':discount' => $customer['discount'] // Defaults to customer's allocated discount rate
        ));

        return $this->connection->lastInsertId();
    }

    /* Add a new quote to the system */
    function addJob(
        $customer_id,
        $job_date,
        $job_timing,
        $job_address_1,
        $job_address_2,
        $job_suburb,
        $job_city,
        $job_post_code,
        $range,
        $truck_id,
        $job_type_id,
        $cubics,
        $mpa,
        $concrete_type_id,
        $mix_type_id
    ) {
        $truck = $this->getTruckDetails($truck_id); // assigned truck to job/quote, we use the details as of present to ensure records remain historical
        $customer = $this->getCustomerDetails($customer_id);
        $highest_concrete_charge = $this->getHighestCostingConcrete($concrete_type_id, $mix_type_id); // We use the highest costing concrete from mix and concrete selection for initial calculations on creation
        $query = "INSERT INTO " . TBL_JOBS . " SET customer_id = '$customer_id', truck_id = $truck_id, number_plate = :number_plate, truck_rate = :truck_rate, truck_boom = :truck_boom, truck_capacity = :truck_capacity, truck_min = :truck_min, 
                      truck_travel_rate_km = :travel_rate, truck_washout = :washout, truck_disposal_fee = :disposal_fee, job_range = :range, establishment_fee = :establishment_fee, cubic_rate = :cubic_rate, travel_fee = :travel_fee, 
                      estimate_pump_time = :est_pump_time, job_date = '$job_date', job_timing = $job_timing, job_addr_1 = '$job_address_1', job_addr_2 = '$job_address_2', job_suburb = '$job_suburb', 
                      job_city = '$job_city', job_post_code = '$job_post_code', job_type = $job_type_id, cubics = $cubics, mpa = $mpa, concrete_type = $concrete_type_id, mix_type = :mix_type, 
                      discount = :discount";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':number_plate' => isset($truck['number_plate']) ? $truck['number_plate'] : 'NULL',
            ':truck_boom' => isset($truck['boom']) ? $truck['boom'] : NULL,
            ':truck_capacity' => isset($truck['capacity']) ? $truck['capacity'] : NULL,
            ':truck_rate' => isset($truck['hourly_rate']) ? $truck['hourly_rate'] : NULL,
            ':truck_min' => isset($truck['min']) ? $truck['min'] : NULL,
            ':travel_rate' => isset($truck['travel_rate_km']) ? $truck['travel_rate_km'] : NULL,
            ':washout' => isset($truck['washout']) ? $truck['washout'] : NULL,
            ':disposal_fee' => isset($truck['disposal_fee']) ? $truck['disposal_fee'] : NULL,
            ':range' => $range, // approx value from create job/quote form
            ':establishment_fee' => isset($truck['est_fee']) ? $truck['est_fee'] : NULL,
            ':cubic_rate' => is_numeric($highest_concrete_charge) ? floatval($highest_concrete_charge) * floatval($cubics) : '0',
            ':travel_fee' => floatval($range) < 21 || is_null($truck['travel_rate_km']) ? 0 : (floatval($range) - 20) * floatval($truck['travel_rate_km']),
            ':est_pump_time' => (floatval($cubics) / 30) * 60,
            ':mix_type' => isset($mix_type_id) ? $mix_type_id : NULL,
            ':discount' => $customer['discount'] // Defaults to customer's allocated discount rate
        ));

        return $this->connection->lastInsertId();
    }

    function updateJob(
        $job_id,
        $customer_id,
        $layer_id,
        $supplier_id,
        $operator_id,
        $foreman_id,
        $job_date,
        $job_timing,
        $job_address_1,
        $job_address_2,
        $job_suburb,
        $job_city,
        $job_post_code,
        $site_visit_required,
        $truck_id,
        $job_type_id,
        $cubics,
        $mpa,
        $concrete_type_id,
        $mix_type_id,
        $job_instructions,
        $ohs_instructions,
        $cubic_charge,
        $establishment_fee,
        $cubic_rate,
        $travel_fee,
        $discount,
        $estimated_pump_time,
        $invoice_number,
        $job_range,
        $am_check,
        $pm_check,
        $passed_check,
        $linesmenArr
    ) {

        try {
            // 开始事务
            $this->connection->beginTransaction();

            /* Update truck details if it was changed, we use the submitted truck id to check against the current one in the db */
            if ($this->truckChanged(TBL_JOBS, $truck_id, $job_id)) {
                $this->updateJobOrQuoteTruckDetails(TBL_JOBS, $truck_id, $job_id);
            }

            // var_dump($operator_id);

            $query = "UPDATE " . TBL_JOBS . " SET customer_id = '$customer_id', foreman_id = $foreman_id, layer_id = $layer_id, supplier_id = $supplier_id, operator_id = $operator_id, 
                                                  job_date = '$job_date', job_timing = $job_timing, job_addr_1 = '$job_address_1', job_addr_2 = '$job_address_2', 
                                                  job_suburb = '$job_suburb', job_city = '$job_city', job_post_code = '$job_post_code', site_visit_required = $site_visit_required, 
                                                  truck_id = $truck_id, job_type = $job_type_id, cubics = $cubics, mpa = $mpa, concrete_type = $concrete_type_id,
                                                  mix_type = :mix_type, job_instructions = '$job_instructions', ohs_instructions = '$ohs_instructions', cubic_charge = :cubic_charge, establishment_fee = $establishment_fee,
                                                  cubic_rate = '$cubic_rate', travel_fee = '$travel_fee', discount = '$discount', estimate_pump_time = '$estimated_pump_time', invoice_number = '$invoice_number', job_range = '$job_range',
                                                  am_check = '$am_check', pm_check = '$pm_check', passed_check = '$passed_check'";

            if ($operator_id === "NULL") {
                // echo $operator_id . '222';
                $query .= ", isOperatorComplete = 0";
            }
            $query .= " WHERE id = '$job_id'";
            error_log($query);
            $stmt  = $this->connection->prepare($query);
            $stmt->execute(array(
                'mix_type' => isset($mix_type_id) ? $mix_type_id : NULL,
                'cubic_charge' => isset($cubic_charge) ? 1 : 0 /* Checkbox is either not set for not selected, or set for checked */
            ));

            // 更新 linesman_jobs 表
            // 获取当前 job_id 的所有 user_id
            $existingQuery = "SELECT user_id FROM " . TBL_LINESMAN_JOBS . " WHERE job_id = :job_id";
            $existingStmt = $this->connection->prepare($existingQuery);
            $existingStmt->execute([':job_id' => $job_id]);
            $existingUserIds = $existingStmt->fetchAll(PDO::FETCH_COLUMN, 0);

            // 检查 linesmenArr 是否有值
            if (!empty($linesmenArr) && is_array($linesmenArr)) {
                // 找出需要删除的 user_id
                $userIdsToDelete = array_diff($existingUserIds, $linesmenArr);
                if (!empty($userIdsToDelete)) {
                    $deleteQuery = "DELETE FROM " . TBL_LINESMAN_JOBS . " WHERE job_id = :job_id AND user_id = :user_id";
                    $deleteStmt = $this->connection->prepare($deleteQuery);
                    foreach ($userIdsToDelete as $user_id) {
                        $deleteStmt->execute([
                            ':job_id' => $job_id,
                            ':user_id' => $user_id
                        ]);
                    }
                }

                // 找出需要新增的 user_id
                $userIdsToAdd = array_diff($linesmenArr, $existingUserIds);
                if (!empty($userIdsToAdd)) {
                    $insertQuery = "INSERT INTO " . TBL_LINESMAN_JOBS . " (job_id, user_id, created_time) VALUES (:job_id, :user_id, NOW())";
                    $insertStmt = $this->connection->prepare($insertQuery);
                    foreach ($userIdsToAdd as $user_id) {
                        $insertStmt->execute([
                            ':job_id' => $job_id,
                            ':user_id' => $user_id
                        ]);
                    }

                    // 更新 jobs 表中的 isLinesmanJob 字段为 1
                    $updateJobQuery = "UPDATE " . TBL_JOBS . " SET isLinesmanJob = 1 WHERE id = :job_id";
                    $updateJobStmt = $this->connection->prepare($updateJobQuery);
                    $updateJobStmt->execute([':job_id' => $job_id]);
                }
            } else {
                // 当 linesmenArr 为空时，删除所有已存在的 linesman_jobs 数据
                if (!empty($existingUserIds)) {
                    $deleteQuery = "DELETE FROM " . TBL_LINESMAN_JOBS . " WHERE job_id = :job_id";
                    $deleteStmt = $this->connection->prepare($deleteQuery);
                    $deleteStmt->execute([':job_id' => $job_id]);
                }

                // 更新 jobs 表中的 isLinesmanJob 字段
                $updateJobQuery = "UPDATE " . TBL_JOBS . " SET isLinesmanJob = 0 WHERE id = :job_id";
                $updateJobStmt = $this->connection->prepare($updateJobQuery);
                $updateJobStmt->execute([':job_id' => $job_id]);

                // 检查 jobs 表中的 sent_to_operator 字段
                $checkSentToOperatorQuery = "SELECT sent_to_operator FROM " . TBL_JOBS . " WHERE id = :job_id";
                $checkSentToOperatorStmt = $this->connection->prepare($checkSentToOperatorQuery);
                $checkSentToOperatorStmt->execute([':job_id' => $job_id]);
                $sent_to_operator = $checkSentToOperatorStmt->fetchColumn();

                // 如果 sent_to_operator 为 0，则将 status 更新为 1
                if ($sent_to_operator == 0) {
                    $updateStatusQuery = "UPDATE " . TBL_JOBS . " SET status = 1 WHERE id = :job_id";
                    $updateStatusStmt = $this->connection->prepare($updateStatusQuery);
                    $updateStatusStmt->execute([':job_id' => $job_id]);
                }
            }

            // 提交事务
            $this->connection->commit();
        } catch (\PDOException $e) {
            // 回滚事务
            $this->connection->rollBack();
            error_log("An SQL error occurred: " . $e->getMessage());
            throw new \Exception("An SQL error occurred: " . $e->getMessage());
        }
    }

    function updateQuote(
        $quote_id,
        $customer_id,
        $job_date,
        $job_timing,
        $job_range,
        $job_address_1,
        $job_address_2,
        $job_suburb,
        $job_city,
        $job_post_code,
        $truck_id,
        $job_type_id,
        $cubics,
        $mpa,
        $concrete_type_id,
        $mix_type_id,
        $quote_summary_notes,
        $cubic_charge,
        $establishment_fee,
        $cubic_rate,
        $travel_fee,
        $discount,
        $estimated_pump_time
    ) {
        /* Update truck details if it was changed, we use the submitted truck id to check against the current one in the db */
        if ($this->truckChanged(TBL_QUOTES, $truck_id, $quote_id)) {
            $this->updateJobOrQuoteTruckDetails(TBL_QUOTES, $truck_id, $quote_id);
        }
        $query = "UPDATE " . TBL_QUOTES . " SET customer_id = '$customer_id', job_date = '$job_date', job_timing = '$job_timing',  quoted_range = '$job_range',
                                             job_addr_1 = '$job_address_1', job_addr_2 = '$job_address_2', job_suburb = '$job_suburb', job_city = '$job_city', job_post_code = '$job_post_code',  
                                             truck_id = '$truck_id', job_type = '$job_type_id', cubics = '$cubics', mpa = '$mpa', concrete_type = '$concrete_type_id', 
                                             mix_type = :mix_type, quote_summary_notes = '$quote_summary_notes', cubic_charge = :cubic_charge, establishment_fee = '$establishment_fee',
                                             cubic_rate = '$cubic_rate', travel_fee = '$travel_fee', discount = '$discount', estimate_pump_time = '$estimated_pump_time' 
                                             WHERE id = '$quote_id';";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            'mix_type' => isset($mix_type_id) ? $mix_type_id : NULL,
            'cubic_charge' => isset($cubic_charge) ? 1 : 0 /* Checkbox is either not set for not selected, or set for checked */
        ));
    }

    function addJobQuoteLinkTableEntry($table, $entry_id)
    {
        $column = $table == TBL_JOBS ? 'link_job_id' : 'quote_id';
        $query = "INSERT INTO " . TBL_JOB_QUOTE_LINK . " SET $column = $entry_id";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $this->connection->lastInsertId();
    }

    function updateJobQuoteLinkTableEntry($table, $search_entry_id, $entry_id)
    {
        $update_column = $table == TBL_JOBS ? 'link_job_id' : 'quote_id';
        $search_column = $table == TBL_JOBS ? 'quote_id' : 'link_job_id';
        $query = "UPDATE " . TBL_JOB_QUOTE_LINK . " SET $update_column = $entry_id WHERE $search_column = $search_entry_id";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    /* Has truck changed for this job or quote */
    function truckChanged($table, $truck_id, $id)
    {
        $query = "SELECT id FROM " . $table . " WHERE id = '$id' AND truck_id = '$truck_id'";
        $stmt = $this->connection->prepare($query);
        return $stmt->rowCount() == 0; // returns 1 if no change, returns 0 if changed
    }

    /* Update the truck details of a quote/job */
    function updateJobOrQuoteTruckDetails($table, $truck_id, $id)
    {
        $truck = $this->getTruckDetails($truck_id); // Get truck details to update the quote and store on the quote at time of submission for historical reasons
        $query = "UPDATE " . $table . " SET establishment_fee = :establishment_fee, number_plate = :number_plate, truck_boom = :truck_boom, truck_capacity = :truck_capacity, truck_rate = :truck_rate, truck_min = :truck_min, truck_travel_rate_km = :travel_rate, truck_washout = :washout, truck_disposal_fee = :disposal_fee WHERE id = '$id';";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':establishment_fee' => $truck['est_fee'],
            ':number_plate' => $truck['number_plate'],
            ':truck_boom' => $truck['boom'],
            ':truck_capacity' => $truck['capacity'],
            ':truck_rate' => $truck['hourly_rate'],
            ':truck_min' => $truck['min'],
            ':travel_rate' => $truck['travel_rate_km'],
            ':washout' => $truck['washout'],
            ':disposal_fee' => $truck['disposal_fee']
        ));
    }

    /**
     * 2024-08-05
     * Get all linesman jobs by job id
     */
    function getAllLinesmanJobsByJobId($job_id)
    {
        $query = "SELECT a.*, b.user_firstname, b.user_lastname FROM " . TBL_LINESMAN_JOBS . " a 
        LEFT JOIN " . TBL_USERS .
            " b ON a.user_id = b.id WHERE a.job_id = :job_id";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(
            [':job_id' => $job_id]
        );
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     *2024-08-02
     * Get a linesman job details
     */
    function getLinesManJobDetails($linesman_job_id)
    {
        $query = "SELECT * FROM " . TBL_LINESMAN_JOBS . "
         WHERE id = :linesman_job_id";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([':linesman_job_id' => $linesman_job_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /* Get job details by job_id */
    function getJobDetails($job_id)
    {
        $query = "SELECT * FROM " . TBL_JOBS . " 
        LEFT JOIN " . TBL_JOB_QUOTE_LINK . " ON jobs.id = job_quote_link.link_job_id 
        LEFT JOIN " . TBL_JOB_STATUSES . " ON jobs.status = job_statuses.id 
        WHERE jobs.id = :job_id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute([':job_id' => $job_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // check if it is a Linesman job，if yes retrieve the linesman_jobs table
        if ($result && $result['isLinesmanJob'] == 1) {
            $linesmanQuery = "SELECT user_id, sentToLinesman, isLinesmanComplete FROM " . TBL_LINESMAN_JOBS . " WHERE job_id = :job_id";
            $linesmanStmt = $this->connection->prepare($linesmanQuery);
            $linesmanStmt->execute([':job_id' => $job_id]);
            $linesmanResults = $linesmanStmt->fetchAll(PDO::FETCH_ASSOC);

            $linesmenIds = array_column($linesmanResults, 'user_id');
            $sentToLinesman = array_column($linesmanResults, 'sentToLinesman');
            $isLinesmanComplete = array_column($linesmanResults, 'isLinesmanComplete');

            // Combine linesmanResult into result
            $result['linesman_user_ids'] = $linesmenIds;
            $result['sentToLinesman'] = $sentToLinesman;
            $result['isLinesmanComplete'] = $isLinesmanComplete;
        }

        return $result;
    }

    /* Get quote details by job_id */
    function getQuoteDetails($quote_id)
    {
        $query = "SELECT * FROM " . TBL_QUOTES . " LEFT JOIN " . TBL_JOB_QUOTE_LINK . " ON quotes.id = job_quote_link.quote_id " . "WHERE id = $quote_id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /* Get concrete details by id */
    function getConcreteDetails($id)
    {
        if (empty($id)) return false;
        $query = "SELECT * FROM " . TBL_CONCRETE_TYPES . " WHERE id = $id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * 2024-08-07
     * Get all linesmen details
     */
    function getLinesmenDetails($job_id)
    {
        try {
            // Transaction started
            $this->connection->beginTransaction();

            //Get all users'id by job_id 
            $linesmanQuery = "SELECT user_id FROM " . TBL_LINESMAN_JOBS . " WHERE job_id = :job_id";
            $linesmanStmt = $this->connection->prepare($linesmanQuery);
            $linesmanStmt->execute([':job_id' => $job_id]);
            $userIds = $linesmanStmt->fetchAll(PDO::FETCH_COLUMN, 0);

            if (empty($userIds)) {
                // return [] if no any linesman
                return [];
            }

            //get users info by users'id got above in user table
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $userQuery = "SELECT * FROM " . TBL_USERS . " WHERE id IN ($placeholders)";
            $userStmt = $this->connection->prepare($userQuery);
            $userStmt->execute($userIds);
            $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

            //  Transaction submitted
            $this->connection->commit();

            return $users;
        } catch (\PDOException $e) {
            // Roll back transaction 
            $this->connection->rollBack();
            error_log("An SQL error occurred: " . $e->getMessage());
            throw new \Exception("An SQL error occurred: " . $e->getMessage());
        }
    }

    /**
     * 2024-08-07
     * Get a linesman details
     */
    function getLinesmanDetails($linesmanId)
    {
        try {
            // Transaction started
            $this->connection->beginTransaction();

            //Get the user infofrom user table by linesmanId
            $userQuery = "SELECT * FROM " . TBL_USERS . " WHERE id = :linesmanId";
            $userStmt = $this->connection->prepare($userQuery);
            $userStmt->execute([':linesmanId' => $linesmanId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            //  Transaction submitted
            $this->connection->commit();

            //return user info otherwise [] if no result
            return $user ? $user : [];
        } catch (\PDOException $e) {
            // roll back transaction
            $this->connection->rollBack();
            error_log("An SQL error occurred: " . $e->getMessage());
            throw new \Exception("An SQL error occurred: " . $e->getMessage());
        }
    }


    /* Get operator details by internal id value */
    function getOperatorDetails($id)
    {
        if (empty($id)) return false;
        $query = "SELECT ID, user_firstname, user_lastname, user_email, user_phone FROM " . TBL_USERS . " WHERE ID = $id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* Get supplier details by internal id value */
    function getSupplierDetails($id)
    {
        if (empty($id)) return false;
        $query = "SELECT * FROM " . TBL_SUPPLIERS . " WHERE id = $id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* Get layer details by internal id value */
    function getLayerDetails($id)
    {
        if (empty($id)) return false;
        $query = "SELECT * FROM " . TBL_LAYERS . " WHERE id = $id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* Get site inspection details by internal id value */
    function getSiteInspectionDetails($site_inspection_id)
    {
        $query = "SELECT jobs.switchJobColor, site_visits.id, site_visits.site_visit_job_id, site_visits.site_visit_completed, site_visits.site_visit_assigned_operator, site_visits.site_visit_photo,site_visits.site_visit_notes, 
        site_visits.site_visit_completed_by, site_visits.date_updated, site_visits.date_created, job_quote_link.unified_id, job_quote_link.link_job_id, jobs.job_date, jobs.job_timing, jobs.job_addr_1, 
        jobs.job_addr_2, jobs.job_suburb, jobs.job_city, jobs.job_post_code, jobs.job_range, jobs.truck_travel_rate_km, jobs.truck_min, jobs.truck_rate, jobs.job_type, job_types.type_name, jobs.job_instructions, concrete_types.concrete_name, 
        customers.name, customers.first_name, customers.last_name, customers.contact_ph, customers.contact_mob, layers.layer_name, layers.layer_firstname, layers.layer_lastname, layers.contact_ph AS layer_ph, layers.contact_mob AS layer_mob,
        suppliers.supplier_name, suppliers.supplier_firstname, suppliers.supplier_lastname, suppliers.contact_ph AS supplier_ph, suppliers.contact_mob AS supplier_mob,
        jobs.cubics, jobs.mpa, foremen.id AS foremen_id, foremen.company AS foremen_company, foremen.first_name AS foremen_firstname, foremen.last_name AS foremen_lastname, foremen.contact_ph AS foremen_phone, foremen.email AS foremen_email
        FROM " . TBL_SITE_VISITS . "
        LEFT JOIN " . TBL_JOB_QUOTE_LINK . " ON site_visits.site_visit_job_id = job_quote_link.link_job_id 
        LEFT JOIN " . TBL_JOBS . " ON jobs.id = site_visits.site_visit_job_id 
        LEFT JOIN " . TBL_JOB_TYPES . " ON jobs.job_type = job_types.id 
        LEFT JOIN " . TBL_CUSTOMERS . " ON customers.id = jobs.customer_id 
        LEFT JOIN " . TBL_LAYERS . " ON layers.id = jobs.layer_id  
        LEFT JOIN " . TBL_SUPPLIERS . " ON suppliers.id = jobs.supplier_id  
        LEFT JOIN " . TBL_CONCRETE_TYPES . " ON concrete_types.id = jobs.concrete_type   
        LEFT JOIN " . TBL_FOREMEN . " ON foremen.id = jobs.foreman_id
        WHERE site_visits.id = $site_inspection_id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* Return highest costing concrete between 2 */
    function getHighestCostingConcrete($concrete_type_id, $mix_type_id)
    {
        $concrete = $this->getConcreteDetails($concrete_type_id);
        if (!isset($mix_type_id)) return $concrete['concrete_charge'];
        $mix = $this->getConcreteDetails($mix_type_id);

        if ($mix && $concrete)
            return $concrete['concrete_charge'] > $mix['concrete_charge'] ? $concrete['concrete_charge'] : $mix['concrete_charge']; // We use the highest costing concrete from mix and concrete selection for initial calculations on creation
        else return 0;
    }

    /* Update quote's job id */
    function updateQuoteJobId($quote_id, $new_job_id)
    {
        $query = "UPDATE " . TBL_QUOTES . " SET job_id = $new_job_id WHERE id = $quote_id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    /* Create job from quote */
    function createJobFromQuote($quote_id)
    {
        $quote_details = $this->getQuoteDetails($quote_id)[0];
        $query = "INSERT INTO " . TBL_JOBS . " SET quote_id = :quote_id, customer_id = :customer_id, truck_id = :truck_id, number_plate = :number_plate, truck_boom = :truck_boom, truck_capacity = :truck_capacity, truck_rate = :truck_rate,
                                                   truck_min = :truck_min, truck_travel_rate_km = :truck_travel_rate, truck_washout = :truck_washout, establishment_fee = :establishment_fee, cubic_rate = :cubic_rate, travel_fee = :travel_fee,
                                                   discount = :discount, job_date = :job_date, job_timing = :job_timing, job_addr_1 = :job_addr_1, job_addr_2 = :job_addr_2, job_suburb = :job_suburb, job_city = :job_city, job_post_code = :job_post_code,
                                                   job_type = :job_type, cubics = :cubics, mpa = :mpa, concrete_type = :concrete_type, mix_type = :mix_type, estimate_pump_time = :estimate_pump_time, job_range = :job_range";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute(array(
            ':quote_id' => $quote_details['quote_id'],
            ':customer_id' => $quote_details['customer_id'],
            ':truck_id' => $quote_details['truck_id'],
            ':number_plate' => $quote_details['number_plate'],
            ':truck_boom' => $quote_details['truck_boom'],
            ':truck_capacity' => $quote_details['truck_capacity'],
            ':truck_rate' => $quote_details['truck_rate'],
            ':truck_min' => $quote_details['truck_min'],
            ':truck_travel_rate' => $quote_details['truck_travel_rate_km'],
            ':truck_washout' => $quote_details['truck_washout'],
            ':establishment_fee' => $quote_details['establishment_fee'],
            ':cubic_rate' => $quote_details['cubic_rate'],
            ':travel_fee' => $quote_details['travel_fee'],
            ':discount' => $quote_details['discount'],
            ':job_date' => $quote_details['job_date'],
            ':job_timing' => $quote_details['job_timing'],
            ':job_addr_1' => $quote_details['job_addr_1'],
            ':job_addr_2' => $quote_details['job_addr_2'],
            ':job_suburb' => $quote_details['job_suburb'],
            ':job_city' => $quote_details['job_city'],
            ':job_post_code' => $quote_details['job_post_code'],
            ':job_type' => $quote_details['job_type'],
            ':cubics' => $quote_details['cubics'],
            ':mpa' => $quote_details['mpa'],
            ':concrete_type' => $quote_details['concrete_type'],
            ':mix_type' => $quote_details['mix_type'],
            ':estimate_pump_time' => $quote_details['estimate_pump_time'],
            ':job_range' => $quote_details['quoted_range']
        ));

        $new_job_id = $this->connection->lastInsertId(); // Use this new job id to update link table
        $this->updateJobQuoteLinkTableEntry(TBL_JOBS, $quote_id, $new_job_id);
        $this->updateQuoteJobId($quote_id, $new_job_id); // Update the quote's job_id entry

        return $new_job_id;
    }

    /* Notification functions */

    // Insert new notifications in bulk to the users which are due to recieve notifications.
    function insertNewJobNotification($recipient_levels, $message_type, $type_id, $class_modifiers, $message)
    {
        global $session;
        // Sometimes a notification is generated by a customer that doesn't have a session, so sender_id is null for non users.
        if (isset($session->userinfo['ID']))
            $sender_id = $session->userinfo['ID'];
        else $sender_id = null;

        $recipients = $this->getUsersToAssignNotifications($recipient_levels);
        foreach ($recipients as $recipient) {
            $query = "INSERT INTO " . TBL_NOTIFICATIONS .
                " (message_type, type_id, recipient_id, sender_id, class_modifiers, message) VALUES ('$message_type', '$type_id', '$recipient[0]', '$sender_id', '$class_modifiers', '$message')";
            $stmt  = $this->connection->prepare($query);
            $stmt->execute();
        }
    }

    //2024-08-01
    function insertNotificationForLinesmen($linesman_id, $message_type, $type_id, $class_modifiers, $message)
    {
        global $session;

        // Sometimes a notification is generated by a customer that doesn't have a session, so sender_id is null for non users.
        if (isset($session->userinfo['ID']))
            $sender_id = $session->userinfo['ID'];
        else $sender_id = null;

        $query = "INSERT INTO " . TBL_NOTIFICATIONS .
            " (message_type, type_id, recipient_id, sender_id, class_modifiers, message) VALUES ('$message_type', '$type_id', '$linesman_id', '$sender_id', '$class_modifiers', '$message')";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    // Insert new notification for a certain operator
    function insertNewNotificationForOperator($operator_id, $message_type, $type_id, $class_modifiers, $message)
    {
        global $session;

        // Sometimes a notification is generated by a customer that doesn't have a session, so sender_id is null for non users.
        if (isset($session->userinfo['ID']))
            $sender_id = $session->userinfo['ID'];
        else $sender_id = null;

        $query = "INSERT INTO " . TBL_NOTIFICATIONS .
            " (message_type, type_id, recipient_id, sender_id, class_modifiers, message) VALUES ('$message_type', '$type_id', '$operator_id', '$sender_id', '$class_modifiers', '$message')";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    function getUsersToAssignNotifications($recipient_levels)
    {
        $query = "SELECT ID FROM " . TBL_USERS . " WHERE user_level IN (" . implode(",", array_map('intval', $recipient_levels)) . ")";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mark notifications as read
    function markNotificationsAsRead($id)
    {
        $query = "UPDATE " . TBL_NOTIFICATIONS . " SET marked_as_read = 1 WHERE recipient_id = '$id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    //Set linesman job as assigned
    function markLinesmanJobAsAssigned($id)
    {
        try {
            //  Transaction started
            $this->connection->beginTransaction();

            // update TBL_LINESMAN_JOBS table
            $query = "UPDATE " . TBL_LINESMAN_JOBS . " SET sentToLinesman = 1 WHERE job_id = :job_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':job_id' => $id]);

            // updateTBL_JOBS table
            $query = "UPDATE " . TBL_JOBS . " SET status = 6 WHERE id = :job_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':job_id' => $id]);

            // Transaction submitted
            $this->connection->commit();
        } catch (\PDOException $e) {
            // Roll back Transaction 
            $this->connection->rollBack();
            error_log("An SQL error occurred: " . $e->getMessage());
            throw new \Exception("An SQL error occurred: " . $e->getMessage());
        }
    }

    // Set job as assigned to
    function markJobAsAssigned($id)
    {
        $query = "UPDATE " . TBL_JOBS . " SET sent_to_operator = 1, status = 6 WHERE id = '$id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }



    // Set job as unassigned
    function markJobAsNotAssigned($id)
    {
        $query = "UPDATE " . TBL_JOBS . " SET sent_to_operator = 0 WHERE id = '$id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    // Cancel the job with the reason submitted
    function cancelJob($job_id, $reason)
    {
        $query = "UPDATE " . TBL_JOBS . " SET status = 2, cancel_reason = '$reason' WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    // Mark job as invoiced with current date
    function markJobAsInvoiced($job_id)
    {
        $query = "UPDATE " . TBL_JOBS . " SET status = '11', invoiced_date = NOW() WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    // Update job with new invoice number
    function updateJobInvoiceNumber($job_id, $invoice_number)
    {
        $query = "UPDATE " . TBL_JOBS . " SET invoice_number = '$invoice_number' WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    // New site inspection assigned to the operator passed in
    function createSiteInspection($job_id, $operator_id)
    {
        $query = "INSERT INTO " . TBL_SITE_VISITS . " (site_visit_job_id, site_visit_assigned_operator) VALUES ('$job_id', $operator_id)";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $this->connection->lastInsertId();
    }

    // Assign an existing site inspeciton with a new operator
    function assignSiteInspection($job_id, $operator_id)
    {
        $query = "UPDATE " . TBL_SITE_VISITS . " SET site_visit_assigned_operator = '$operator_id' WHERE site_visit_job_id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    // Is there already a site inspection for this job?
    function jobHasSiteInspection($job_id)
    {
        $query = "SELECT * FROM " . TBL_SITE_VISITS . " WHERE site_visit_job_id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Are the site inspections for this job and are they complete?
    function jobHasCompleteSiteInspections($job_id)
    {
        $query = "SELECT * FROM " . TBL_SITE_VISITS . " WHERE site_visit_job_id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (is_null($row['site_visit_completed'])) return false; // Null test is to see if the site inspection has a complete date, if it does not, then it's not complete
            }

            return true; // Will reach this statement if there were site inspections and they were all complete
        }
        return false; // Return false if there isn't any site inspections at all
    }

    // Parse a site inspection id and a operator id, which is the new incoming operator id and see if it's different to the current one.
    // If it's different, return true as it doesn't exist in the table yet
    function isSiteInspectionOperatorDifferent($site_inspection_id, $operator_id)
    {
        $query = "SELECT * FROM " . TBL_SITE_VISITS . " WHERE id = '$site_inspection_id' AND site_visit_assigned_operator = '$operator_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount() <= 0;
    }

    // Set site inspection requirement to 1 for a job
    function setJobSiteInspectionToRequired($job_id)
    {
        $query = "UPDATE " . TBL_JOBS . " SET site_visit_required = 1, status = 5 WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    // Get status of job
    function getJobStatus($job_id)
    {
        $query = "SELECT status FROM " . TBL_JOBS . " WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['status'];
    }

    // Set status of job
    function setJobStatus($job_id, $status)
    {
        $query = "UPDATE " . TBL_JOBS . " SET status = '$status' WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    // Is site visit complete
    function isSiteInspectionComplete($site_inspection_id)
    {
        $query = "SELECT * FROM " . TBL_SITE_VISITS . " WHERE id = '$site_inspection_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['site_visit_completed'] != null;
    }

    // Does job have any outstanding site inspections to complete
    function isJobSiteInspectionsComplete($job_id)
    {
        $query = "SELECT * FROM " . TBL_SITE_VISITS . " WHERE site_visit_job_id = '$job_id' AND site_visit_completed IS NULL";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount() <= 0; // If there's any incomplete site inspections, this will return false
    }

    // Is job assigned to operator
    function isJobAssignedToOperator($job_id)
    {
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['sent_to_operator'] == 1;
    }

    // Update site inspection to complete
    function updateSiteInspectionAsComplete($job_id, $site_inspection_id, $site_inspection_photo_reference, $pump_numbers, $notes, $completing_user_id, $linesmenIds)
    {
        try {
            //Transaction Begin
            $this->connection->beginTransaction();

            //Update site_visits table
            $query = "UPDATE " . TBL_SITE_VISITS . " 
        SET site_visit_completed = NOW(), 
            site_visit_pumps = :pump_numbers, 
            site_visit_notes = :notes, 
            site_visit_completed_by = :completing_user_id, 
            site_visit_photo = :site_inspection_photo_reference 
        WHERE id = :site_inspection_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([
                ':pump_numbers' => $pump_numbers,
                ':notes' => $notes,
                ':completing_user_id' => $completing_user_id,
                ':site_inspection_photo_reference' => $site_inspection_photo_reference,
                ':site_inspection_id' => $site_inspection_id
            ]);

            //Update isLinesman_job field to 1 in jobs table
            $updateJobQuery = "UPDATE jobs SET isLinesmanJob = 1 WHERE id = :job_id";
            $updateJobStmt = $this->connection->prepare($updateJobQuery);
            $updateJobStmt->execute([':job_id' => $job_id]);

            //Update linesman_jobs table
            if (!empty($linesmenIds) && is_array($linesmenIds)) {
                $insertLinesmanJobQuery = "INSERT INTO linesman_jobs (job_id, user_id, created_time) VALUES (:job_id, :user_id, NOW())";
                $insertLinesmanJobStmt = $this->connection->prepare($insertLinesmanJobQuery);

                foreach ($linesmenIds as $user_id) {
                    $insertLinesmanJobStmt->execute([
                        ':job_id' => $job_id,
                        ':user_id' => $user_id
                    ]);
                }
            }

            //Transaction Submit
            $this->connection->commit();
        } catch (\PDOException $e) {
            //Roll back transaction if error
            $this->connection->rollBack();
            error_log("An SQL error occurred: " . $e->getMessage() . " " . $site_inspection_photo_reference);
            throw new \Exception("An SQL error occurred: " . $e->getMessage());
            return false;
        }
    }

    // Update site inspection pump numbers and notes
    function updateSiteInspectionDetails($job_id, $site_inspection_id, $pump_numbers, $notes, $linesmenIds)
    {
        try {
            // Transaction started
            $this->connection->beginTransaction();

            // update site_visits table
            $query = "UPDATE " . TBL_SITE_VISITS . " 
                  SET site_visit_completed = NOW(), 
                      site_visit_pumps = :pump_numbers, 
                      site_visit_notes = :notes  
                  WHERE id = :site_inspection_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([
                ':pump_numbers' => $pump_numbers,
                ':notes' => $notes,
                ':site_inspection_id' => $site_inspection_id
            ]);

            //update linesman jobs table
            // get all user id of by job_id 
            $existingQuery = "SELECT user_id FROM " . TBL_LINESMAN_JOBS . " WHERE job_id = :job_id";
            $existingStmt = $this->connection->prepare($existingQuery);
            $existingStmt->execute([':job_id' => $job_id]);
            $existingUserIds = $existingStmt->fetchAll(PDO::FETCH_COLUMN, 0);

            //Check linesmenIds 
            if (!empty($linesmenIds) && is_array($linesmenIds)) {
                //find out which user_id to delete
                $userIdsToDelete = array_diff($existingUserIds, $linesmenIds);
                if (!empty($userIdsToDelete)) {
                    $deleteQuery = "DELETE FROM " . TBL_LINESMAN_JOBS . " WHERE job_id = :job_id AND user_id = :user_id";
                    $deleteStmt = $this->connection->prepare($deleteQuery);
                    foreach ($userIdsToDelete as $user_id) {
                        $deleteStmt->execute([
                            ':job_id' => $job_id,
                            ':user_id' => $user_id
                        ]);
                    }
                }

                // find out which user_id to add
                $userIdsToAdd = array_diff($linesmenIds, $existingUserIds);
                if (!empty($userIdsToAdd)) {
                    $insertQuery = "INSERT INTO " . TBL_LINESMAN_JOBS . " (job_id, user_id, created_time) VALUES (:job_id, :user_id, NOW())";
                    $insertStmt = $this->connection->prepare($insertQuery);
                    foreach ($userIdsToAdd as $user_id) {
                        $insertStmt->execute([
                            ':job_id' => $job_id,
                            ':user_id' => $user_id
                        ]);
                    }

                    // set isLinesmanJob = 1 in jobs table
                    $updateJobQuery = "UPDATE " . TBL_JOBS . " SET isLinesmanJob = 1 WHERE id = :job_id";
                    $updateJobStmt = $this->connection->prepare($updateJobQuery);
                    $updateJobStmt->execute([':job_id' => $job_id]);
                }
            } else {
                //whhen linesmenIds is null, remove all current linesman_jobs of the job
                //also change isLinesmanJob to 0 in jobs table
                if (!empty($existingUserIds)) {
                    $deleteQuery = "DELETE FROM " . TBL_LINESMAN_JOBS . " WHERE job_id = :job_id";
                    $deleteStmt = $this->connection->prepare($deleteQuery);
                    $deleteStmt->execute([':job_id' => $job_id]);
                }

                // update jobs.isLinesmanJob 
                $updateJobQuery = "UPDATE " . TBL_JOBS . " SET isLinesmanJob = 0 WHERE id = :job_id";
                $updateJobStmt = $this->connection->prepare($updateJobQuery);
                $updateJobStmt->execute([':job_id' => $job_id]);

                // check jobs.sent_to_operator 
                $checkSentToOperatorQuery = "SELECT sent_to_operator FROM " . TBL_JOBS . " WHERE id = :job_id";
                $checkSentToOperatorStmt = $this->connection->prepare($checkSentToOperatorQuery);
                $checkSentToOperatorStmt->execute([':job_id' => $job_id]);
                $sent_to_operator = $checkSentToOperatorStmt->fetchColumn();

                // if sent_to_operator 为 0，set status = 1
                if ($sent_to_operator == 0) {
                    $updateStatusQuery = "UPDATE " . TBL_JOBS . " SET status = 1 WHERE id = :job_id";
                    $updateStatusStmt = $this->connection->prepare($updateStatusQuery);
                    $updateStatusStmt->execute([':job_id' => $job_id]);
                }
            }

            // transaction submitted
            $this->connection->commit();
        } catch (\PDOException $e) {
            // roll back
            $this->connection->rollBack();
            error_log("An SQL error occurred: " . $e->getMessage());
            throw new \Exception("An SQL error occurred: " . $e->getMessage());
        }
    }

    /**
     * 2024-08-05
     * updateLinesmanJobAsComplete
     * 
     *       
     * Only when isOperatorComplete of this job = 1 in jobs table,
     * and all isLinesmanComplete = 1 of this job = 1 in linesman_jobs table,
     * change job status = 8 in jobs table
     */
    function updateLinesmanJobAsComplete($job_id, $linesman_job_id)
    {

        try {
            // Update isLinesmanComplete to 1 for the given linesman_job_id
            $query = "UPDATE linesman_jobs SET isLinesmanComplete = 1 WHERE id = :linesman_job_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':linesman_job_id' => $linesman_job_id]);

            //check if isOperatorComplete = 1 
            $query = "SELECT isOperatorComplete FROM jobs WHERE id = :job_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':job_id' => $job_id]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);

            //if isOperatorComplete = 1
            if ($job && $job['isOperatorComplete'] == 1) {
                //check if all isLiesmanComplete of this job = 1
                $query = "SELECT COUNT(*) as count FROM linesman_jobs WHERE job_id = :job_id AND isLinesmanComplete = 0";
                $stmt = $this->connection->prepare($query);
                $stmt->execute([':job_id' => $job_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                //all isOperatorComplete = 1, change status = 8 in jobs table
                if ($result['count'] == 0) {
                    $query = "UPDATE jobs SET status = 8 WHERE id = :job_id";
                    $stmt = $this->connection->prepare($query);
                    $stmt->execute([':job_id' => $job_id]);
                    echo "Job status updated to 8.";
                }

                // else {
                //     echo "Not all linesman jobs are complete.";
                // }
            }

            // else {
            //     echo "Operator job is not complete.";
            // }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * 
     * 2024-09-12
     * No need to consider if linesman jobs are complete or not for now
     * 
     * 2024-08-05
     * Only when isOperatorComplete of this job = 1 in jobs table,
     * and all isLinesmanComplete = 1 of this job = 1 in linesman_jobs table,
     * change job status = 8 in jobs table
     */
    // Update job to complete and add operator input too
    function updateJobAsComplete($job_id, $actual_cubics, $finish_time, $actual_start_time, $onsite_washout, $onsite_disposal, $operator_notes)
    {
        try {
            //Set isOperatorCOmplete = 1
            $query = "UPDATE " . TBL_JOBS . " SET isOperatorComplete = 1 WHERE id = :job_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':job_id' => $job_id]);

            //Check if isOperatorComplete = 1
            $query = "SELECT isOperatorComplete FROM " . TBL_JOBS . " WHERE id = :job_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':job_id' => $job_id]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);

            // if isOperatorComplete = 1
            if ($job && $job['isOperatorComplete'] == 1) {

                //2024-09-12 No need to check if all isLinesmanComplete = 1 for now
                //2024-08-05: Check if all isLinesmanComplete of this job = 1 in linesman_jobs table
                // $query = "SELECT COUNT(*) as incomplete_count FROM linesman_jobs WHERE job_id = :job_id AND isLinesmanComplete = 0";
                // $stmt = $this->connection->prepare($query);
                // $stmt->execute([':job_id' => $job_id]);
                // $result = $stmt->fetch(PDO::FETCH_ASSOC);

                //2024-09-12 No need to check if all isLinesmanComplete = 1 for now
                //2024-08-05:If all isLinesmanComplete of this job = 1, update staus = 8
                //ps: even if there is no linesman jobs link to this job, $result['incomplete_count'] == 0 will return true
                // if ($result['incomplete_count'] == 0) {
                $query = "UPDATE " . TBL_JOBS . " SET status = 8, actual_cubics = :actual_cubics, 
                          job_time_finished = :finish_time, actual_job_timing = :actual_start_time, 
                          onsite_washout = :onsite_washout, onsite_disposal = :onsite_disposal, 
                          operator_notes = :operator_notes, complete_date = NOW() 
                          WHERE id = :job_id";

                $stmt = $this->connection->prepare($query);
                $stmt->execute([
                    ':actual_cubics' => $actual_cubics,
                    ':finish_time' => $finish_time,
                    ':actual_start_time' => $actual_start_time,
                    ':onsite_washout' => $onsite_washout,
                    ':onsite_disposal' => $onsite_disposal,
                    ':operator_notes' => $operator_notes,
                    ':job_id' => $job_id
                ]);
                // echo "Job status updated to 8.";
                // }

                // else {
                //     echo "Not all linesman jobs are complete.";
                // }
            }

            // else {
            //     echo "Operator job is not complete.";
            // }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    //Back up
    // // Update job to complete and add operator input too
    // function updateJobAsComplete($job_id, $actual_cubics, $finish_time, $actual_start_time, $onsite_washout, $onsite_disposal, $operator_notes)
    // {
    //     $query = "UPDATE " . TBL_JOBS . " SET status = 8, actual_cubics = '$actual_cubics', 
    //                         job_time_finished = '$finish_time', actual_job_timing = '$actual_start_time', 
    //                         onsite_washout = '$onsite_washout', onsite_disposal = '$onsite_disposal', operator_notes = '$operator_notes', complete_date = NOW() 
    //                         WHERE id = '$job_id'";
    //     $stmt  = $this->connection->prepare($query);
    //     $stmt->execute();
    // }

    function updateJobDetail($job_id, $field, $value)
    {
        $query = "UPDATE " . TBL_JOBS . " SET $field = '$value' WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }
    /**
     * 2024-08-02
     * update linesman job detail
     */
    function updateLinemanJobDetail($job_id, $field, $value)
    {
        $query = "UPDATE " . TBL_LINESMAN_JOBS . " SET $field = '$value' WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    function addInvoiceDataForJob($job_id, $rate_type, $establishment_fee, $travel_fee, $actual_cubics, $concrete_rate, $cubic_rate, $actual_job_hours, $truck_rate, $hourly_rate, $washdown_fee, $disposal_fee, $special_rate, $special_1, $special_2, $special_desc_1, $special_desc_2, $discount, $gst_rate)
    {
        $query = "INSERT INTO " . TBL_INVOICE_DATA . " (job_id, rate_type, establishment_fee, travel_fee, actual_cubics, concrete_charge, truck_hourly_rate, cubic_rate, actual_job_hours, hourly_rate, washdown_fee, disposal_fee, special_rate, special_rate_1, special_rate_2, special_cost_description_1, special_cost_description_2, discount, gst_rate) 
                                                VALUES ('$job_id', '$rate_type', '$establishment_fee', '$travel_fee', '$actual_cubics', '$concrete_rate', '$truck_rate', '$cubic_rate', '$actual_job_hours', '$hourly_rate', '$washdown_fee', '$disposal_fee', '$special_rate', '$special_1', '$special_2', '$special_desc_1', '$special_desc_2', '$discount', '$gst_rate')";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    function updateInvoiceDataForJob($job_id, $rate_type, $establishment_fee, $travel_fee, $actual_cubics, $concrete_rate, $cubic_rate, $actual_job_hours, $truck_rate, $hourly_rate, $washdown_fee, $disposal_fee, $special_rate, $special_1, $special_2, $special_desc_1, $special_desc_2, $discount, $gst_rate)
    {
        $query = "UPDATE " . TBL_INVOICE_DATA . " SET rate_type = '$rate_type', establishment_fee = '$establishment_fee', travel_fee = '$travel_fee', actual_cubics = '$actual_cubics', concrete_charge = '$concrete_rate', truck_hourly_rate = '$truck_rate', cubic_rate = '$cubic_rate', actual_job_hours = '$actual_job_hours', 
                                                      hourly_rate = '$hourly_rate', washdown_fee = '$washdown_fee', disposal_fee = '$disposal_fee', special_rate = '$special_rate', special_rate_1 = '$special_1', special_rate_2 = '$special_2', special_cost_description_1 = '$special_desc_1', special_cost_description_2 = '$special_desc_2', discount = '$discount', gst_rate = '$gst_rate' WHERE job_id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    function markJobAsReadyForInvoicing($job_id)
    {
        $query = "UPDATE " . TBL_JOBS . " SET status = '10' WHERE id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
    }

    function getJobInvoiceData($job_id)
    {
        $query = "SELECT * FROM " . TBL_INVOICE_DATA . " WHERE job_id = '$job_id'";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 2024-08-05
     */
    function updateLinesmanJobsDetails($linesmanJobId, $actualJobTiming, $jobFinishTime, $lineSizeSelect, $jobNote)
    {
        // var_dump($linesmanJobId);
        $query = "
            UPDATE " . TBL_LINESMAN_JOBS . "
            SET
                actual_job_timing = :actual_job_timing,
                job_time_finished = :job_time_finished,
                line_size_select = :line_size_select,
                linesman_notes = :linesman_notes
            WHERE id = :linesmanJobId
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([
            ':actual_job_timing' => $actualJobTiming,
            ':job_time_finished' => $jobFinishTime,
            ':line_size_select' => $lineSizeSelect,
            ':linesman_notes' => $jobNote,
            ':linesmanJobId' => $linesmanJobId,
        ]);
    }

    function updateOperatorJobDetails($job_id, $job_timing, $actual_cubics, $first_mixer_time, $finish_time, $washout, $disposal, $operator_notes)
    {
        $query = "UPDATE " . TBL_JOBS . " SET actual_job_timing = '$job_timing', actual_cubics = '$actual_cubics', first_mixer_arrival_time = '$first_mixer_time', 
                                            job_time_finished = '$finish_time', onsite_washout = '$washout', onsite_disposal = '$disposal', operator_notes = '$operator_notes' 
                                            WHERE id = '$job_id'";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    function logJobChange($job_id, $field, $message, $reason, $old_value, $new_value, $status)
    {
        global $session;
        $user_id = $session->userinfo['ID'];
        $query = "INSERT INTO " . TBL_JOB_CHANGE_LOGS . " (field_changed, message, reason_changed, job_id, previous_value, new_value, user_id, status_during_edit) VALUES ('$field', '$message', '$reason', '$job_id', '$old_value', '$new_value', '$user_id', '$status')";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    function appendSiteInspectionNotesToJobInstruction($job_id, $job_instructions, $site_inspection_notes)
    {
        $notes = "Site Inspection Notes:\n" . $site_inspection_notes . "\n\n" . $job_instructions . "\n\n";
        $query = "UPDATE " . TBL_JOBS . " SET job_instructions = :notes WHERE id = '$job_id'";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(':notes' => $notes));
    }

    /* Gets a truck's boom length with a id provided */
    function getTruckBoomLength($truck_id)
    {
        $query = "SELECT boom FROM " . TBL_TRUCKS . " WHERE id = '$truck_id'";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['boom'];
    }

    /* Gets the last completed site inspection for the job, as there can be multiple, an order by operation is done and the first result is fetched to get the latest */
    function getLatestCompleteSiteInspection($job_id)
    {
        $query = "SELECT * FROM " . TBL_SITE_VISITS . " WHERE site_visit_job_id = '$job_id' ORDER BY site_visit_completed DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function getTruckCount()
    {
        $query = "SELECT COUNT(id) FROM " . TBL_TRUCKS;
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['COUNT(id)'];
    }

    function getSuitablePumpsForJob($job_id)
    {
        $has_site_inspection = $this->jobHasCompleteSiteInspections($job_id);
        if ($has_site_inspection) {
            $site_inspection = $this->getLatestCompleteSiteInspection($job_id);
            $pump_array = json_decode($site_inspection['site_visit_pumps'], true);

            // Check if pump array contains all trucks
            if (sizeof($pump_array) == $this->getTruckCount()) {
                return array('All Pumps');
            }

            $boom_array = array();
            foreach ($pump_array as $truck_id) {
                array_push($boom_array, $this->getTruckBoomLength($truck_id));
            }
            return $boom_array;
        } else return null;
    }

    function copyJob($job_id)
    {
        $query = "INSERT INTO " . TBL_JOBS . " (quote_id, sent_to_operator, site_visit_required, customer_id, layer_id, supplier_id, operator_id, foreman_id, truck_id, number_plate, truck_boom, truck_capacity, truck_rate,
                                                truck_min, truck_travel_rate_km, truck_washout, truck_disposal_fee, cubic_charge, establishment_fee, cubic_rate, travel_fee, discount, job_range, job_date, job_timing, 
                                                job_addr_1, job_addr_2, job_suburb, job_city, job_post_code, job_type, cubics, mpa, concrete_type, mix_type, estimate_pump_time, job_instructions, ohs_instructions) " .
            "SELECT quote_id, sent_to_operator, site_visit_required, customer_id, layer_id, supplier_id, operator_id, foreman_id, truck_id, number_plate, truck_boom, truck_capacity, truck_rate,
                 truck_min, truck_travel_rate_km, truck_washout, truck_disposal_fee, cubic_charge, establishment_fee, cubic_rate, travel_fee, discount, job_range, job_date, job_timing, 
                 job_addr_1, job_addr_2, job_suburb, job_city, job_post_code, job_type, cubics, mpa, concrete_type, mix_type, estimate_pump_time, job_instructions, ohs_instructions FROM " . TBL_JOBS .
            " WHERE id = '$job_id'";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $this->connection->lastInsertId();
    }

    /**
     * Updated 2024-08-05
     * Uncomplete all linesman jobs as well
     * set jobs.isOperatorComplete = 0
     * set linesman_jobs.isLinesmanComplete = 0
     * alongside the current field
     */
    function undoCompletedJob($job_id)
    {
        try {
            // 开始事务
            $this->connection->beginTransaction();

            // 更新jobs表，将状态和相关字段重置
            $query = "UPDATE " . TBL_JOBS . " SET status = 6, complete_date = NULL, first_mixer_arrival_time = NULL, 
                      job_time_finished = NULL, onsite_washout = NULL, onsite_disposal = NULL, actual_cubics = NULL, 
                      actual_job_timing = NULL, operator_notes = NULL, isOperatorComplete = 0 WHERE id = :job_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':job_id' => $job_id]);

            // 更新linesman_jobs表，将所有对应数据的isLinesmanComplete设为0
            $query = "UPDATE linesman_jobs SET isLinesmanComplete = 0 WHERE job_id = :job_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':job_id' => $job_id]);

            // 提交事务
            $this->connection->commit();

            // echo "Job undone successfully.";
        } catch (PDOException $e) {
            // 如果发生异常，回滚事务
            $this->connection->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }


    //back up for change on 2024-08-05
    // function undoCompletedJob($job_id)
    // {
    //     $query = "UPDATE " . TBL_JOBS . " SET status = 6, complete_date = NULL, first_mixer_arrival_time = NULL, job_time_finished = NULL, onsite_washout = NULL, onsite_disposal = NULL, actual_cubics = NULL, actual_job_timing = NULL, operator_notes = NULL WHERE id = '$job_id'";
    //     $stmt = $this->connection->prepare($query);
    //     $stmt->execute();
    // }

    /**
     * 2024-08-06
     */
    function reinstateCancelledJob($job_id)
    {
        try {
            // transacion started
            $this->connection->beginTransaction();

            // update jobs table
            $query = "UPDATE " . TBL_JOBS . " SET status = 1, sent_to_operator = 0, cancel_reason = '' WHERE id = :job_id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
            $stmt->execute();

            // update linesman_jobs table
            $query = "UPDATE linesman_jobs SET sentToLinesman = 0 WHERE job_id = :job_id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
            $stmt->execute();

            // transaction submit
            $this->connection->commit();
        } catch (PDOException $e) {
            // roll back
            $this->connection->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }

    // function reinstateCancelledJob($job_id)
    // {
    //     $query = "UPDATE " . TBL_JOBS . " SET status = 1, sent_to_operator = 0, cancel_reason = '' WHERE id = '$job_id'";
    //     $stmt = $this->connection->prepare($query);
    //     $stmt->execute();
    // }

    /* Gets all job's within date condition and sums up all of the invoice/incoming and return it */
    function getJobTurnoverForMonth($date)
    {
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT jobs.invoiced_date, i.rate_type, i.establishment_fee, i.travel_fee, i.actual_cubics, i.concrete_charge, i.truck_hourly_rate, i.cubic_rate, i.hourly_rate, i.washdown_fee, i.disposal_fee, i.special_rate, i.special_rate_1, i.special_rate_2, i.discount FROM " . TBL_JOBS . " LEFT JOIN invoice_data AS i ON i.job_id = jobs.id WHERE jobs.invoiced_date IS NOT NULL AND YEAR(jobs.invoiced_date) = :year AND MONTH(jobs.invoiced_date) = :month";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':year' => $year,
            ':month' => $month
        ));
        $invoice_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_turnover = 0;
        foreach ($invoice_data as $row) {
            $job_turnover = $this->calculateTotalInvoicedForJob($row);
            $total_turnover += $job_turnover;
        }

        return $total_turnover;
    }

    /* Gets turnover for a given month and operator */
    function getJobTurnoverForMonthAndOperator($operator_id, $date)
    {
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT jobs.invoiced_date, i.rate_type, i.establishment_fee, i.travel_fee, i.actual_cubics, i.concrete_charge, i.truck_hourly_rate, i.cubic_rate, i.hourly_rate, i.washdown_fee, i.disposal_fee, i.special_rate, i.special_rate_1, i.special_rate_2, i.discount FROM " . TBL_JOBS . " LEFT JOIN invoice_data AS i ON i.job_id = jobs.id WHERE jobs.operator_id = '$operator_id' AND jobs.invoiced_date IS NOT NULL AND YEAR(jobs.invoiced_date) = :year AND MONTH(jobs.invoiced_date) = :month";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':year' => $year,
            ':month' => $month
        ));
        $invoice_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $operator_turnover = 0;
        foreach ($invoice_data as $row) {
            $job_turnover = $this->calculateTotalInvoicedForJob($row);
            $operator_turnover += $job_turnover;
        }

        return $operator_turnover;
    }

    /* Gets turnover for a given month and truck */
    function getJobTurnoverForMonthAndTruck($truck_id, $date)
    {
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT jobs.invoiced_date, i.rate_type, i.establishment_fee, i.travel_fee, i.actual_cubics, i.concrete_charge, i.truck_hourly_rate, i.cubic_rate, i.hourly_rate, i.washdown_fee, i.disposal_fee, i.special_rate, i.special_rate_1, i.special_rate_2, i.discount 
                FROM " . TBL_JOBS . " LEFT JOIN invoice_data AS i ON i.job_id = jobs.id WHERE jobs.truck_id = '$truck_id' AND jobs.invoiced_date IS NOT NULL AND YEAR(jobs.invoiced_date) = :year AND MONTH(jobs.invoiced_date) = :month";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':year' => $year,
            ':month' => $month
        ));
        $invoice_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $truck_turnover = 0;
        foreach ($invoice_data as $row) {
            $job_turnover = $this->calculateTotalInvoicedForJob($row);
            $truck_turnover += $job_turnover;
        }

        return $truck_turnover;
    }

    /* Calculate total $ for invoice data */
    function calculateTotalInvoicedForJob($invoice_data)
    {
        $job_turnover = 0;

        // Check what rate was used to charge this job
        switch ($invoice_data['rate_type']) {
            case 'cubic':
                $job_turnover += $invoice_data['cubic_rate'];
                $job_turnover += $invoice_data['establishment_fee'] + $invoice_data['travel_fee'] + $invoice_data['washdown_fee'] + $invoice_data['disposal_fee'];
                break;
            case 'hourly':
                $job_turnover += $invoice_data['hourly_rate'];
                $job_turnover += $invoice_data['establishment_fee'] + $invoice_data['travel_fee'] + $invoice_data['washdown_fee'] + $invoice_data['disposal_fee'];
                break;
            case 'special':
                $job_turnover += $invoice_data['special_rate'];
                break;
        }

        // Extra costs
        $job_turnover += $invoice_data['special_rate_1'] + $invoice_data['special_rate_2'];

        // Discount
        $discount = $job_turnover * ($invoice_data['discount'] / 100);
        $job_turnover -= $discount;

        return $job_turnover;
    }

    /* Get financial year's total turnover */
    function getTotalTurnoverForFinancialYear($year)
    {
        $fiscal_year_turnover = 0;
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($year . '-4-1' . "+" . $i . " months"));
            $monthly_turnover = $this->getJobTurnoverForMonth(strtotime($date));
            $fiscal_year_turnover += $monthly_turnover;
        }
        return $fiscal_year_turnover;
    }

    /* Get total jobs for a month - uses complete date for date condition matching */
    function getTotalJobsForMonth($date)
    {
        $date_y_m = date('Y-m', $date);
        $query = "SELECT COUNT(*) AS c FROM " . TBL_JOBS . " WHERE DATE_FORMAT(invoiced_date, '%Y-%m') = :date_y_m";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':date_y_m' => $date_y_m
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get total jobs for a financial year */
    function getTotalJobsForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-t', strtotime(($year + 1) . '-3-1'));
        $query = "SELECT COUNT(*) AS c FROM " . TBL_JOBS . " WHERE jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get total truck cubics for a financial year */
    function getTotalTruckCubicsForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-t', strtotime(($year + 1) . '-3-1'));
        $query = "SELECT COUNT(*) AS c FROM " . TBL_JOBS . " WHERE jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }


    /* Get total truck cubics for a month - uses complete date for date condition matching */
    function getTotalTruckCubicsForEachMonth($year)
    {
        $next_year = $year + 1;
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT trucks.number_plate, 
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date) AS total_for_sorting,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date) AS total_for_year,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-04') AS april_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-05') AS may_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-06') AS june_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-07') AS july_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-08') AS august_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-09') AS september_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-10') AS october_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-11') AS november_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-12') AS december_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-01') AS january_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-02') AS february_count,
                    (SELECT SUM(jobs.actual_cubics) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-03') AS march_count 
                    FROM " . TBL_TRUCKS . " ORDER BY boom DESC, trucks.number_plate ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Get cubics of truck for a given month */
    function getTotalTruckCubicsForMonth($date)
    {
        $date_y_m = date('Y-m', $date);
        $query = "SELECT SUM(actual_cubics) AS month_total FROM " . TBL_JOBS . " WHERE DATE_FORMAT(invoiced_date, '%Y-%m') = :date_y_m";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':date_y_m' => $date_y_m
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['month_total'];
    }

    /* Get total cubics for a financial year */
    function getTotalCubicsPerTruckForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-t', strtotime(($year + 1) . '-3-1'));
        $query = "SELECT SUM(actual_cubics) AS total FROM " . TBL_JOBS . " WHERE jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* Get total hours for a financial year */
    function getTotalHoursForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-t', strtotime(($year + 1) . '-3-1'));
        $query = "SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),'.',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')) + 0.0, 0) AS total_hours FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_hours'];
    }

    /* Get total weekday only jobs for a financial year */
    function getTotalWeekdayJobsForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT COUNT(*) AS c FROM " . TBL_JOBS . " WHERE jobs.complete_date >= :start_date AND jobs.complete_date < :end_date AND WEEKDAY(jobs.complete_date) < 5";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get total weekday only jobs for a month */
    function getTotalWeekdayJobsForMonth($date)
    {
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT COUNT(*) AS c FROM " . TBL_JOBS . " WHERE YEAR(jobs.complete_date) = :year AND MONTH(jobs.complete_date) = :month AND WEEKDAY(jobs.complete_date) < 5";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':year' => $year,
            ':month' => $month
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get total weekday + saturday only jobs for a financial year */
    function getTotalWeekdayIncludingSatJobsForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT COUNT(*) AS c FROM " . TBL_JOBS . " WHERE jobs.complete_date >= :start_date AND jobs.complete_date < :end_date AND WEEKDAY(jobs.complete_date) < 6";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get total weekday + saturday only jobs for a month */
    function getTotalWeekdayIncludingSatJobsForMonth($date)
    {
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT COUNT(*) AS c FROM " . TBL_JOBS . " WHERE YEAR(jobs.complete_date) = :year AND MONTH(jobs.complete_date) = :month AND WEEKDAY(jobs.complete_date) < 6";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':year' => $year,
            ':month' => $month
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get client's job count for month */
    function getTotalJobsOfClientForMonth($client_name, $date)
    {
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT COUNT(*) AS c FROM " . TBL_JOBS . " LEFT JOIN " . TBL_CUSTOMERS . " ON customers.id = jobs.customer_id WHERE customers.name LIKE '%$client_name%' AND YEAR(jobs.invoiced_date) = :year AND MONTH(jobs.invoiced_date) = :month;";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':year' => $year,
            ':month' => $month
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get client's job count for a financial year */
    function getTotalJobsOfClientForFinancialYear($client_name, $year)
    {
        $financial_year_total_client_jobs = 0;
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($year . '-4-1' . "+" . $i . " months"));
            $monthly_jobs = $this->getTotalJobsOfClientForMonth($client_name, strtotime($date));
            $financial_year_total_client_jobs += $monthly_jobs;
        }
        return $financial_year_total_client_jobs;
    }

    /* Get total customers added into system at given date. e.g. get all customers since 2021 May will be less than where get all customers created before or on 2021 January theoretically */
    function getTotalCustomersAddedByDate($date)
    {
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT COUNT(*) AS c FROM " . TBL_CUSTOMERS . " WHERE DATE_FORMAT(date_created, '%Y-%m') <= '$year-$month'";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get total customers added into system at given year. */
    function getTotalCustomersAddedByFinancialYear($year)
    {
        $date_end_financial_year = date('Y-m', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT COUNT(*) AS c FROM " . TBL_CUSTOMERS . " WHERE DATE_FORMAT(date_created, '%Y-%m') < :end_date";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get total unique customers sold to in a financial year */
    function getTotalUniqueCustomersSoldToForYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT COUNT(DISTINCT customer_id) AS c FROM " . TBL_JOBS . " WHERE invoiced_date >= :start_date AND invoiced_date < :end_date";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get total unique customers up to a month, just the month not YTD */
    function getTotalUniqueCustomersSoldToForMonth($date)
    {
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT COUNT(DISTINCT customer_id) AS c FROM " . TBL_JOBS . " WHERE YEAR(invoiced_date) = :year AND MONTH(invoiced_date) = :month";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':year' => $year,
            ':month' => $month
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get total unique customers up to a month YTD */
    function getTotalUniqueCustomersSoldToForMonthYTD($year, $date)
    {
        $date = date('Y-m-t', $date); // YTD date
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $query = "SELECT COUNT(DISTINCT customer_id) AS c FROM " . TBL_JOBS . " WHERE invoiced_date >= :start_date AND invoiced_date <= :end_date";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get new customers for a month */
    function getTotalNewCustomersForMonth($date)
    {
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT COUNT(*) AS c FROM " . TBL_CUSTOMERS . " WHERE YEAR(date_created) = :year AND MONTH(date_created) = :month";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':year' => $year,
            ':month' => $month
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get new customers for a year */
    function getTotalNewCustomersForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT COUNT(*) AS c FROM " . TBL_CUSTOMERS . " WHERE date_created >= :start_date AND date_created < :end_date";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get customers not yet sold to for year */
    function getTotalCustomersWithNoJobsForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT jobs.customer_id, COUNT(*) as count FROM " . TBL_JOBS . " LEFT JOIN " . TBL_CUSTOMERS . " ON customers.id = jobs.customer_id WHERE jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date GROUP BY jobs.customer_id HAVING count = 0";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return count($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* Get customers not yet sold to for a month */
    function getTotalCustomersWithNoJobsForMonth($start_year, $date)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($start_year . '-4-1'));

        // Designated date
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT customers.id FROM " . TBL_CUSTOMERS . " WHERE NOT EXISTS (SELECT 1 FROM " . TBL_JOBS . " WHERE jobs.customer_id = customers.id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') <= '$year-$month' AND jobs.invoiced_date >= '$date_start_financial_year') AND DATE_FORMAT(customers.date_created, '%Y-%m') <= '$year-$month'";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return count($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* Get total customers with 4 or less jobs within a financial year */
    function getTotalCustomersWithLessThanFourJobsForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT jobs.customer_id, COUNT(*) as count FROM " . TBL_JOBS . " WHERE jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date GROUP BY jobs.customer_id HAVING count < 4";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return count($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* Get total customers with 4 or less jobs in a month */
    function getTotalCustomersWithLessThanFourJobsForMonth($date)
    {
        $year = date('Y', $date);
        $month = date('m', $date);
        $query = "SELECT jobs.customer_id, COUNT(*) as count FROM " . TBL_JOBS . " WHERE YEAR(jobs.invoiced_date) = :year AND MONTH(jobs.invoiced_date) = :month GROUP BY jobs.customer_id HAVING count < 4";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':year' => $year,
            ':month' => $month
        ));
        return count($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* Get total customers with more than four jobs within the financial year */
    function getTotalCustomersWithMoreThanFourJobsForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT jobs.customer_id, COUNT(*) as count FROM " . TBL_JOBS . " WHERE jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date GROUP BY jobs.customer_id HAVING count >= 4";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return count($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* Get total customers with more than four jobs in a month */
    function getTotalCustomersWithMoreThanFourJobsForMonth($start_year, $date)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($start_year . '-4-1'));
        $end_date = date('Y-m', $date);
        $query = "SELECT jobs.customer_id, COUNT(*) as count FROM " . TBL_JOBS . " WHERE jobs.invoiced_date >= :start_date AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') <= :end_date  GROUP BY jobs.customer_id HAVING count >= 4";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $end_date,
        ));
        return count($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* Get customers with more than 4 jobs in a given year - Returns a array of customer_id values */
    function getCustomersWithMoreThanFourJobsForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT jobs.customer_id, COUNT(*) as count FROM " . TBL_JOBS . " WHERE jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date GROUP BY jobs.customer_id HAVING count >= 4";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Check customer has jobs for given date period */
    function checkCustomerHasJobsForMonth($customer_id, $date, $start_year)
    {
        $date_limit = date('Y-m-t', $date);
        $date_start_financial_year = date('Y-m-d', strtotime($start_year . '-4-1'));
        $query = "SELECT * FROM " . TBL_JOBS . " WHERE customer_id = :customer_id AND invoiced_date >= :start_date AND invoiced_date <= :date_limit;";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':customer_id' => $customer_id,
            ':date_limit' => $date_limit,
            ':start_date' => $date_start_financial_year
        ));
        return count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }

    /* Check customer has jobs for given financial year period */
    function checkCustomerHasJobsForFinancialYear($customer_id, $year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT COUNT(*) AS c FROM " . TBL_JOBS . " WHERE jobs.customer_id = :customer_id AND jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':customer_id' => $customer_id,
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }

    /* Get customer job counts per month of chosen financial year */
    function getCustomerJobCountsForFinancialYear($year)
    {
        $next_year = $year + 1;
        $query = "SELECT jobs.id, jobs.customer_id AS c_id, customers.name, MONTH(jobs.invoiced_date) as invoice_date, IFNULL(COUNT(*), 0) as `job_count` FROM " . TBL_JOBS . "
                    LEFT JOIN customers ON jobs.customer_id = customers.id 
                    WHERE DATE_FORMAT(jobs.invoiced_date, '%Y-%m') >= '$year-04' AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') < '$next_year-04'
                    GROUP BY MONTH(jobs.invoiced_date), c_id
                    ORDER BY c_id ASC, invoice_date ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $customer_ids = $this->getCustomerIDs();
        $customer_job_counts = array();

        foreach ($customer_ids as $c_id) {

            $april_count = $may_count = $june_count = $july_count = $august_count = $september_count = $october_count = $november_count = $december_count = $january_count = $february_count = $march_count = 0;
            $rows = array_filter($results, function ($arr) use ($c_id) {
                return $arr['c_id'] == $c_id['id'];
            });

            foreach ($rows as $counts) {

                switch ($counts['invoice_date']) {
                    case 4:
                        $april_count += $counts['job_count'];
                        break;
                    case 5:
                        $may_count += $counts['job_count'];
                        break;
                    case 6:
                        $june_count += $counts['job_count'];
                        break;
                    case 7:
                        $july_count += $counts['job_count'];
                        break;
                    case 8:
                        $august_count += $counts['job_count'];
                        break;
                    case 9:
                        $september_count += $counts['job_count'];
                        break;
                    case 10:
                        $october_count += $counts['job_count'];
                        break;
                    case 11:
                        $november_count += $counts['job_count'];
                        break;
                    case 12:
                        $december_count += $counts['job_count'];
                        break;
                    case 1:
                        $january_count += $counts['job_count'];
                        break;
                    case 2:
                        $february_count += $counts['job_count'];
                        break;
                    case 3:
                        $march_count += $counts['job_count'];
                        break;
                }
            }

            $total_for_year = $april_count + $may_count + $june_count + $july_count + $august_count + $september_count + $october_count + $november_count + $december_count + $january_count + $february_count + $march_count;
            array_push($customer_job_counts, array(
                'name' => $c_id['name'],
                'total_for_year' => $total_for_year,
                'april_count' => $april_count,
                'may_count' => $may_count,
                'june_count' => $june_count,
                'july_count' => $july_count,
                'august_count' => $august_count,
                'september_count' => $september_count,
                'october_count' => $october_count,
                'november_count' => $november_count,
                'december_count' => $december_count,
                'january_count' => $january_count,
                'february_count' => $february_count,
                'march_count' => $march_count
            ));
        }

        usort($customer_job_counts, function ($a, $b) {
            return $b['total_for_year'] - $a['total_for_year'];
        });

        return $customer_job_counts;
    }

    /* Get operator job counts per month of chosen financial year */
    function getOperatorJobCountsForFinancialYear($year)
    {
        $next_year = $year + 1;
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT users.user_firstname, users.user_lastname, CONCAT(users.user_firstname, ' ', users.user_lastname) as fullname, 
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID = jobs.operator_id AND jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date) AS total_for_year,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-04') AS april_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-05') AS may_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-06') AS june_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-07') AS july_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-08') AS august_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-09') AS september_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-10') AS october_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-11') AS november_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-12') AS december_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-01') AS january_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-02') AS february_count,
                    (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE users.ID  = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-03') AS march_count 
                    FROM " . TBL_USERS . " WHERE (users.user_level = 1 OR users.user_level = 2 OR users.user_level = 4) AND users.ID != 1 ORDER BY total_for_year DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Get hours per month for truck of chosen financial year */
    function getTruckHoursForFinancialYear($year)
    {
        $next_year = $year + 1;
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT trucks.number_plate, 
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),'.',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')) + 0.0, 0) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date) AS total_for_sorting,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date) AS total_for_year,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-04') AS april_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-05') AS may_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-06') AS june_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-07') AS july_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-08') AS august_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-09') AS september_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-10') AS october_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-11') AS november_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-12') AS december_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-01') AS january_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-02') AS february_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE trucks.id = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-03') AS march_count 
                    FROM " . TBL_TRUCKS . " ORDER BY boom DESC, trucks.number_plate ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Get hours per month for operators of chosen financial year */
    function getOperatorHoursForFinancialYear($year)
    {
        $next_year = $year + 1;
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT users.user_firstname, users.user_lastname, CONCAT(users.user_firstname, ' ', users.user_lastname) as fullname, 
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),'.',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')) + 0.0, 0) FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date) AS total_for_sorting,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date) AS total_for_year,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-04') AS april_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-05') AS may_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-06') AS june_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-07') AS july_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-08') AS august_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-09') AS september_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-10') AS october_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-11') AS november_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-12') AS december_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-01') AS january_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-02') AS february_count,
                    (SELECT IFNULL(concat(floor(SUM( TIME_TO_SEC(actual_job_hours))/3600),':',LPAD(floor(SUM( TIME_TO_SEC(actual_job_hours))/60)%60, 2, '0')), '0:00') FROM " . TBL_JOBS . " LEFT JOIN " . TBL_INVOICE_DATA . " ON invoice_data.job_id = jobs.id WHERE users.ID = jobs.operator_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-03') AS march_count 
                    FROM " . TBL_USERS . " WHERE (users.user_level = 1 OR users.user_level = 2 OR users.user_level = 4) AND users.ID != 1 ORDER BY total_for_sorting DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Get truck job counts per month of chosen financial year */
    function getTruckJobCountsForFinancialYear($year)
    {
        $next_year = $year + 1;
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT trucks.number_plate, trucks.brand, 
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id = jobs.truck_id AND jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date) AS total_for_year,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-04') AS april_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-05') AS may_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-06') AS june_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-07') AS july_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-08') AS august_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-09') AS september_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-10') AS october_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-11') AS november_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$year-12') AS december_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-01') AS january_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-02') AS february_count,
                        (SELECT COUNT(*) FROM " . TBL_JOBS . " WHERE trucks.id  = jobs.truck_id AND DATE_FORMAT(jobs.invoiced_date, '%Y-%m') = '$next_year-03') AS march_count 
                        FROM " . TBL_TRUCKS . " ORDER BY boom DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Get customers sold to for a year and their job count */
    function getCustomerJobCountForFinancialYear($year)
    {
        $date_start_financial_year = date('Y-m-d', strtotime($year . '-4-1'));
        $date_end_financial_year = date('Y-m-d', strtotime(($year + 1) . '-4-1'));
        $query = "SELECT customers.name, jobs.customer_id, COUNT(*) as count FROM " . TBL_JOBS . " LEFT JOIN " . TBL_CUSTOMERS . " ON customers.id = jobs.customer_id WHERE jobs.invoiced_date >= :start_date AND jobs.invoiced_date < :end_date GROUP BY jobs.customer_id ORDER BY count DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array(
            ':start_date' => $date_start_financial_year,
            ':end_date' => $date_end_financial_year
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Get total jobs for a financial year */
    function getTotalInvoicedJobsForAllTime()
    {
        $query = "SELECT COUNT(*) AS c FROM " . TBL_JOBS . " WHERE jobs.invoiced_date IS NOT NULL AND status != '2'";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /* Get customers sold to for all time and their job count */
    function getAllCustomersJobCount()
    {
        $query = "SELECT customers.name, jobs.customer_id, COUNT(*) as count FROM " . TBL_JOBS . " LEFT JOIN " . TBL_CUSTOMERS . " ON customers.id = jobs.customer_id GROUP BY jobs.customer_id ORDER BY count DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Get max row order value of trucks table */
    function getMaxRowOrderValueFromTrucks()
    {
        $query = "SELECT MAX(row_order) AS max_row_order FROM " . TBL_TRUCKS;
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['max_row_order'];
    }

    /* Get pump number selections for site inspection */
    function getPumpNumbersOfSiteInspection($site_visit_id)
    {
        $query = "SELECT site_visit_pumps FROM " . TBL_SITE_VISITS . " WHERE id = '$site_visit_id';";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $truck_id_array = json_decode($stmt->fetch(PDO::FETCH_ASSOC)['site_visit_pumps'], true);

        // Get details of selected pumps
        $query = "SELECT id, boom, number_plate FROM " . TBL_TRUCKS . " WHERE id IN (" . implode(', ', $truck_id_array) . ");";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /*get photo count from DB*/
    function getPhotoCount()
    {
        $query = "SELECT * FROM " . TBL_CONFIGURATION . " WHERE config_name = 'photo_count';";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['config_value'];
    }
    /*get delete flag*/
    function getDeleteFlag()
    {
        $query = "SELECT * FROM " . TBL_CONFIGURATION . " WHERE config_name = 'delete_flag';";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['config_value'];
    }
    function getPhotoUrl()
    {
        $query = "SELECT site.site_visit_photo
                     FROM jobs AS jobs
                     LEFT JOIN site_visits AS site
                     ON jobs.id = site.site_visit_job_id
                     WHERE jobs.status=2 and (site.site_visit_photo!=NULL||site.site_visit_photo!='')";

        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    function getPhotoUrlById($job_id)
    {
        $query = "SELECT site.site_visit_photo
                     FROM jobs AS jobs
                     LEFT JOIN site_visits AS site
                     ON jobs.id = site.site_visit_job_id
                     WHERE jobs.status=2 and site.site_visit_job_id=" . $job_id . " and (site.site_visit_photo!=NULL||site.site_visit_photo!='')";

        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    function deleteCanceledJob($job_id)
    {
        $this->connection->beginTransaction();
        $deleteFlag1 = true;
        $deleteFlag2 = true;
        try {
            $query = "DELETE FROM site_visits WHERE site_visit_job_id=" . $job_id;
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
        } catch (Exception $e) {
            $deleteFlag1 = false;
            Error($e->getMessage());
        }
        try {
            $query = "DELETE FROM jobs WHERE id=" . $job_id;
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
        } catch (Exception $e) {
            $deleteFlag2 = false;
            Error($e->getMessage());
        }

        if ($deleteFlag1 && $deleteFlag2) {
            // 如果所有操作成功，则提交事务
            $this->connection->commit();
        } else {
            // 如果任何操作失败，则回滚事务
            $this->connection->rollback();
        }

        return $deleteFlag1 && $deleteFlag2;
    }

    function switchJobColor($jobID)
    {
        // 从数据库中获取当前任务类型
        $query = "SELECT switchJobColor FROM jobs WHERE id = :jobID";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':jobID', $jobID, PDO::PARAM_INT);
        $stmt->execute();
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        // 设置任务类型的当前状态
        $currentColor = $job['switchJobColor'];

        // 检查是否提交了表单并执行任务类型的切换
        // 切换任务类型：如果当前是 0，则切换为 1；否则切换回 0
        $newColor = ($currentColor == 0) ? 1 : 0;

        // 更新数据库中的任务类型
        $updateQuery = "UPDATE jobs SET switchJobColor = :newColor WHERE id = :jobID";
        $updateStmt = $this->connection->prepare($updateQuery);
        $updateStmt->bindParam(':newColor', $newColor, PDO::PARAM_INT);
        $updateStmt->bindParam(':jobID', $jobID, PDO::PARAM_INT);
        $updateStmt->execute();

        return $newColor;

        // 更新当前任务类型，确保页面刷新后显示正确按钮
    }
}; // end of class

/* Create database connection */
$database = new MySQLDB;
