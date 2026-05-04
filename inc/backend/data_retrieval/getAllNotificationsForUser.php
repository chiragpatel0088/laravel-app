<?php

include("../constants.php");
$notifications_db = new NotificationMySql();

$user_id = $_GET['id'];
$notification_data = $notifications_db->getAllUsersNotifications($user_id);
$unread_notification_count = $notifications_db->getUnreadNotificationCount($user_id);

$notifications = array();
$i = 0;

foreach ($notification_data as $notification) {

    $notifications[$i]['type'] = $notification['message_type'];
    $notifications[$i]['message_type'] = $notification['message_type'];
    $notifications[$i]['type_id'] = $notification['type_id'];
    $notifications[$i]['message'] = $notification['message'];
    $notifications[$i]['unread'] = $notification['marked_as_read'];
    $notifications[$i]['class_modifiers'] = $notification['class_modifiers'];
    $notifications[$i]['date_created'] = date('d/m/Y h:i A', strtotime($notification['date_created'])) . " NZST";
    $i++;
}

$json_notifications['unread_count'] = $unread_notification_count;
$json_notifications['notifications'] = $notifications;

// Send json away :)
echo json_encode($json_notifications);

class NotificationMySql
{
    public $connection; //The MySQL database connection

    function __construct()
    {
        /* Make connection to database */
        try {
            # MySQL with PDO_MYSQL
            $this->connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
           echo 'lolol';
            echo "Error connecting to datfffabase.";
        }
    }

    // Maybe only get last 50, then add a view where users can see all their notifications??
    function getAllUsersNotifications($id)
    {
        $query = "SELECT * FROM " . TBL_NOTIFICATIONS . " WHERE recipient_id = '$id' ORDER BY date_created DESC LIMIT 50";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get unread notifications
    function getUnreadNotificationCount($id)
    {
        $query = "SELECT * FROM " . TBL_NOTIFICATIONS . " WHERE recipient_id = '$id' AND marked_as_read IS NULL";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
