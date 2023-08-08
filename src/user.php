<?php declare(strict_types=1);
include 'Route.php';
include 'index.php';
include 'connection.php';
include 'otpMail.php';


$route = new Router();
// $requestBody = file_get_contents('php://input');
$payload = array();

try {


    $method = $route->getRequestMethod();
    // echo $method;

    // $nameQuery = $route->getQuery("name", true);
    // echo $nameQuery;

    $endPoint = $route->getEndPoint();
    // echo $endPoint;

    switch ($endPoint) {
        // case "add-user":
        //     $payload["2"] = "22";
        //     $route->route(array("checkUser", "checkUsertwo"), "GET", $payload, $route);
        //     echo ("sdsd===============================>");
        //     print_r($payload);
        //     break;
        case "auth-me":
            $route->route(array("authMe"), "POST", $payload, $route);
            break;

        case "add-user":
            $route->route(array("createUser"), "POST", $payload, $route);
            break;

        case "verify-otp":
            $route->route(array("verifyOtp"), "POST", $payload, $route);
            break;

        case "save-user-data":
            $route->route(array("saveUserData"), "POST", $payload, $route);
            break;
        case "get-user-data":
            $route->route(array("getUserData"), "GET", $payload, $route);
            break;

        default:
            $route->NotFound404Error();

    }

} catch (Exception $e) {
    echo $e->getMessage();
}

function authMe($payload, &$route)
{
    try {
        $requestBody = file_get_contents('php://input');
        $data = json_decode($requestBody, true);


        $resultFromToken = $route->verifyToken($data["token"]);

        if (!$resultFromToken) {
            $route->UnAuthenticationError();
        }
        $id = $resultFromToken["id"];
        if ($id <= 0) {
            $route->UnAuthenticationError();
        }

        $conn = new Connection();

        $sql = "select `id`,`name`,`email`,`phoneNumber`,`profileImage`,`address` , `userType` from user where id = '$id'";

        $result = $conn->mysqli->execute_query($sql);
        $userData = json_encode(mysqli_fetch_assoc($result));
        $userData = json_decode($userData, true);

        if ($userData == null) {
            $route->UnAuthenticationError();
        }

        $route->setResponse(array("data" => $userData), "user exist", "sucess");
    } catch (e) {
        $route->InternalServerError();
    }
}

function getUserData($payload, &$route)
{
    try {
        $uid = $route->getQuery("userId", true);
        $sql1 = "select name,email from user where id = '$uid'";
        $conn = new Connection();
        $result = $conn->mysqli->execute_query($sql1);
        $userData = [];
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($userData, $row);
        }
        $route->setResponse($userData, "user data", "sucess");
    } catch (e) {
        $route->InternalServerError();
    }
}

function createUser($payload, &$route)
{
    try {

        $requestBody = file_get_contents('php://input');
        $data = json_decode($requestBody, true);

        $email = $data['email'];

        if (isset($email) && $email != "") {
            // $encpass = password_hash($data['password'], PASSWORD_BCRYPT);
            $conn = new Connection();

            $sql = "select * from user where email = '$email'";

            $result = $conn->mysqli->execute_query($sql);
            $isUserExist = json_encode(mysqli_fetch_assoc($result));
            $isUserExist = json_decode($isUserExist, true);

            //echo $isUserExist["isMailVerified"];

            // if ($isUserExist !== null && $isUserExist["isMailVerified"] == 1) {
            //     $route->setResponse(array("data" => "User with this account is already exist"), "User with this account is already exist", "failure");
            // }

            $otp = rand(100000, 999999);

            $content = "This mail is to verify your mail, and for signUp";
            $html = "<div>
                        <h1>This is to the response of otp request<br/>Here is your otp <strong>$otp </strong><br/></h1>
                        <p>Note: This OTP will be valid for next five minutes only</p>
                        <p>If this request is no done by u,then someone might enter mail id by mistake</p>
                </div>
            ";

            // $isMialSend = sendMail($email, "OTP for signup to resellify", $html, $content, "New User");
            $isMialSend = 1;
            if ($isMialSend != 1) {
                $route->setResponse(array("data" => "OTP not send to $email"), "Failed to send otp $email", "failure");
            }

            //user first time signUp
            //echo "isUserExist==>";
            //print_r($isUserExist);
            if ($isUserExist != null) {
                $sql = "UPDATE `user` SET lastOtp = $otp , updatedAt = current_timestamp where email='$email';";
                //echo $sql;
                $result = $conn->mysqli->execute_query($sql);
                if ($result == 1) {
                    $route->setResponse(array("data" => "OTP send to $email"), "OTP send to $email", "sucess");
                }
            }
            //if user , previously otp not verified
            else {
                $sql = "INSERT INTO `user` (`email`,`lastotp`,`updatedAt`) VALUES ('$email','$otp',current_timestamp());";
                $result = $conn->mysqli->execute_query($sql);
                if ($result == 1) {
                    $route->setResponse(array("data" => "OTP send to $email"), "OTP send to $email", "sucess");
                }
            }
        } else {
            $route->RequirementsNotMatchedError();
        }
    } catch (e) {
        $route->InternalServerError();
    }

    // $sql = "INSERT INTO `user` (`id`, `name`, `email`, `phoneNumber`, `profileImage`, `address`, `createdAt`, `updatedAt`, `password`, `lastOtp`) VALUES (NULL, 'varma', 'varma@gmail.com', '7136482845', '', 'mysore', current_timestamp(), current_timestamp(), '1234', NULL);";
    // $result = $conn->mysqli->execute_query($sql);
    // $resultArray = [];
    // while ($row = mysqli_fetch_assoc($result)) {
    //     array_push($resultArray, $row);
    // }


    // if ($result == 1) {
    //     $route->setResponse(array("data" => "User added"), "User added sucessfully", "sucess");
    // }

    // $conn = new Connection();
    // $data = $conn->mysqli->query("SELECT * FROM `user` WHERE 1");
    // print_r($data);
    // $data = executeQuery("SELECT * FROM `user`");
}

function verifyOtp($payload, &$route)
{
    try {
        $requestBody = file_get_contents('php://input');
        // print_r($requestBody);
        $data = json_decode($requestBody, true);

        $otp = $data['otp'];
        $email = $data['email'];
        // $sql = "SELECT lastOtp,updatedAt FROM user WHERE email - $email and lastOtp = $otp;";
        $sql = "SELECT `updatedAt`,`id`,`name`, TIMESTAMPDIFF(MINUTE, `updatedAt`, CURRENT_TIMESTAMP) as `timeDiff` from user where email = '$email'  and lastOtp = '$otp';";
        $conn = new Connection();
        $result = $conn->mysqli->execute_query($sql);

        $data = json_encode(mysqli_fetch_assoc($result));
        $data = json_decode($data, true);

        if ($data == null || $data == "") {
            $route->setResponse(array("data" => "invalid creadentials"), "invalid creadentials", "failure");
        }

        if ($data["timeDiff"] >= 5) {
            $route->setResponse(array("data" => "invalid OTP"), "invalid OTP", "failure");
        }

        $userName = $data["name"];

        $sql = "UPDATE `user` SET `isMailVerified` = 1 , lastOtp = Null where email='$email';";
        $result2 = $conn->mysqli->execute_query($sql);

        if ($result2 != 1) {
            $route->setResponse(array("data" => "signUp failed"), "signUp failed", "failure");
        }

        $token = $route->generateToken(
            array(
                "id" => $data["id"],
                "email" => $email
            ),
        );

        $route->setResponse(
            array(
                "data" => array(
                    "id" => $data["id"],
                    "email" => $email,
                    "name" => $userName,
                    "token" => "$token"
                )
            ),
            "signUp sucessfull",
            "sucess"
        );


    } catch (err) {
        $route->InternalServerError();
    }
}

function saveUserData($payload, &$route)
{
    try {
        $requestBody = file_get_contents('php://input');
        // print_r($requestBody);
        $data = json_decode($requestBody, true);


        if (!(array_key_exists("id", $data) && array_key_exists("token", $data) && array_key_exists("name", $data) && array_key_exists("address", $data))) {
            $route->RequirementsNotMatchedError();
        }

        $resultFromToken = $route->verifyToken($data["token"]);


        if (!$resultFromToken) {
            $route->UnAuthenticationError();
        }
        $id = $resultFromToken["id"];
        if ($id <= 0) {
            $route->UnAuthenticationError();
        }

        $conn = new Connection();

        $name = $data['name'];
        $email = $data['email'];
        $address = $data['address'];

        $sql = "UPDATE `user` SET `name` = '$name', `address` = '$address' where id = '$id' and email='$email'";

        $result = $conn->mysqli->execute_query($sql);

        if ($result != 1) {
            $route->setResponse(array("data" => "data not saved"), "Data not saved", "failure");
        }
        $route->setResponse(array("data", "data saved"), "Data saved", "sucess");
    } catch (err) {
        $route->InternalServerError();
    }
}

function checkUser($payload, &$route)
{
    // print_r($payload);
    // echo "1st method checking user ===>";
    $payload["dun1"] = "sdsdsd";
    $payload["name1"] = "shashank";
    $payload["name2"] = "shashi";
    print_r($payload);
    $route->setResponse($payload, "checked invalid user", "failure");
    return $payload;
}


function checkUsertwo($payload, &$route)
{
    // echo "====>fun2 calll ====>";
    // print_r($payload);
    unset($payload["0"]);
    // print_r($payload);
    // echo "2nd method checking user ===>";
    $route->setResponse($payload, "checked user", "sucess");
}
?>