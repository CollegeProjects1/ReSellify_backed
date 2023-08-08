<?php
$sql = "SELECT productName,description,images FROM product where id=$_GET[pid]";
$conn = mysqli_connect("localhost", "root", "root", "resellify", 3307);

$result = $conn->query($sql);
$res = [];
if (!$result) {
    // Query execution failed, handle the error (e.g., log, display, or exit gracefully).
    die("Error executing query: " . mysqli_error($conn));
}

// Query executed successfully, fetch and process the data.
while ($row = $result->fetch_assoc()) {
    // Process the data as needed.
    $res[] = $row;
    // Alternatively, you can use array_push():
    // array_push($res, $row);
}

// Encode the result as JSON and send the response.
echo json_encode($res);
?>