<?php


function executeQuery(string $query)
{

    $con = mysqli_connect("localhost", "root", "root", "temp", 3307);
    $response = array();

    if ($con) {

        // echo "connectedd \n";
        // $sql = "select * from user";
        // $result = mysqli_query($con, $query);
        // echo json_encode($result);
        // while ($row = mysqli_fetch_assoc($result)) {
        //     echo "Name: " . $row['name'] . "<br>";
        //     echo "Email: " . $row['email'] . "<br>";
        //     echo "Phone: " . $row['phoneNumber'] . "<br>";
        //     echo "<br>";
        // }

    } else {
        // $route = new Route();
        // $route->setResponse(array("data" => "Not connected"), "Database not connected", "failure");
        die("Not connected");
    }
}

// executeQuery("SELECT * FROM `user` WHERE 1");
executeQuery("SHOW TABLES");
// echo$_SERVER['REQUEST_METHOD'];
// echo json_encode($response,JSON_PRETTY_PRINT);

// echo json_encode($_POST[]);

//         if(isset($_POST["message"])){
//             $name = $_POST["message"];
//         }
//         // else{
//         //     echo "invalid name";
//         // }

//         // header("Access-Control-Allow-Origin: *");
//         header("Access-Control_Allow_Origin: *");
// header("Access-Control-Allow-Credentials: true");
// header("Content-type:application/json;charset=utf-8"); 
// header("Access-Control-Allow-Methods: GET");
// // header("Access-Control-Allow-Headers": "Access-Control-Allow-Origin, Accept");

//         // Create an associative array with your response data
// $responseData = [
//     'status' => 'success',
//     'message' => 'Data retrieved successfully',
//     'data' => [
//         'name' => $name,
//         'age' => 25,
//         'email' => 'johndoe@example.com'
//     ]
// ];

// // Convert the array to a JSON string
// $jsonResponse = json_encode($responseData);

// // Set the Content-Type header to indicate JSON format
// header('Content-Type: application/json');

// // Send the JSON response
// echo $jsonResponse;