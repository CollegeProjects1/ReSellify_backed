<?php
$conn = mysqli_connect("localhost", "root", "root", "resellify", 3307);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Get the message content and sender from the POST data
    $message = $_POST['message'];
    $sender = $_POST['sender'];
    $chatid = $_POST['cid'];


    // Insert the new message into the database
    $sql = "INSERT INTO chatroom1 (sender, message,chatid) VALUES ('$sender', '$message','$chatid')";
    if ($conn->query($sql) === TRUE) {
        echo "Message inserted successfully";
    } else {
        echo "Error inserting message: " . $conn->error;
    }
} else {
    echo "Invalid request method";
}
?>