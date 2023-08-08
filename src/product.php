<?php declare(strict_types=1);
include 'Route.php';
include 'index.php';
include 'connection.php';
include 'otpMail.php';


$route = new Router();
$payload = array();

try {
    $method = $route->getRequestMethod();
    $endPoint = $route->getEndPoint();

    switch ($endPoint) {
        case "step-one":
            $route->route(array("stepOne"), "POST", $payload, $route);
            break;
        case "step-two":
            $route->route(array("stepTwo"), "POST", $payload, $route);
            break;
        case "get-uploaded-images":
            $route->route(array("getUploadedImages"), "POST", $payload, $route);
            break;
        case "get-all-products":
            $route->route(array("getAllProducts"), "GET", $payload, $route);
            break;
        case "get-single-products":
            $route->route(array("getSingleProducts"), "GET", $payload, $route);
            break;
        case "get-chat-count-of-a-product":
            $route->route(array("getChatCountOfAProduct"), "GET", $payload, $route);
            break;
        case "get-single-products-cart-count":
            $route->route(array("getInsightsOfAProduct"), "GET", $payload, $route);
            break;
        case "add-to-cart":
            $route->route(array("addToCart"), "POST", $payload, $route);
            break;
        case "get-cart-details":
            $route->route(array("getCartDetails"), "GET", $payload, $route);
            break;
        case "remove-from-cart":
            $route->route(array("removeFromCart"), "DELETE", $payload, $route);
            break;
        case "is-product-is-in-cart":
            $route->route(array("isProductIsInCart"), "GET", $payload, $route);
            break;
        case "get-my-products":
            $route->route(array("getMyProducts"), "GET", $payload, $route);
            break;
        case "search":
            $route->route(array("search"), "GET", $payload, $route);
            break;
        case "search-trending":
            $route->route(array("searchTrending"), "GET", $payload, $route);
            break;
        case "increment-product-view-count":
            $route->route(array("incrementProductViewCount"), "PUT", $payload, $route);
            break;
        case "add-to-search-history":
            $route->route(array("addToSearchHistory"), "POST", $payload, $route);
            break;
        case "remove-from-search-history":
            $route->route(array("removeFromSearchHistory"), "DELETE", $payload, $route);
            break;
        case "change-product-status":
            $route->route(array("changeProductStatus"), "POST", $payload, $route);
            break;
        case "get-unique-category-list":
            $route->route(array("getUniqueCategoryList"), "GET", $payload, $route);
            break;
        case "validator":
            $route->route(array("validator"), "GET", $payload, $route);
            break;
        case "initiate-chat":
            $route->route(array("initiateChat"), "POST", $payload, $route);
            break;

        default:
            $route->NotFound404Error();
    }

} catch (Exception $e) {
    echo $e->getMessage();
}

function stepOne($payload, &$route)
{
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


    $model = $data["Model"];
    $productName = $data["title"];
    $yearOfPurchase = $data["purchasedYear"];
    $description = $data["description"];
    $noOfOwner = $data["noOfOwner"];
    $location = $data["location"];
    $price = $data["price"];
    $category = $data["category"];

    $sql = "INSERT  INTO product (`model`,`productName`,`yearOfPurchase`,`description`,`noOfOwner` ,`category`,`userId`,`location`,`price`) values ('$model' , '$productName','$yearOfPurchase','$description','$noOfOwner','$category','$id','$location','$price');";
    $result = $conn->mysqli->execute_query($sql);

    if ($result != 1) {
        $route->InternalServerError();
    }

    $sql = "SELECT `id` FROM PRODUCT WHERE userId='$id' and productName = '$productName' and model = '$model'";
    $result = $conn->mysqli->execute_query($sql);
    $productDetails = json_encode(mysqli_fetch_assoc($result));
    $productDetails = json_decode($productDetails, true);

    $route->setResponse(array("data" => $productDetails), "step one completed", "sucess");
}

function stepTwo($payload, &$route)
{
    try {

        $requestBody = file_get_contents('php://input');
        $data = json_decode($requestBody, true);

        $resultFromToken = $route->verifyToken($_GET["token"]);

        if (!$resultFromToken) {
            $route->UnAuthenticationError();
        }
        $userId = $resultFromToken["id"];
        if ($userId <= 0) {
            $route->UnAuthenticationError();
        }

        if (isset($_FILES['images'])) {
            $images = $_FILES['images'];


            $uploadDir = "uploads/";

            $image = $_FILES["images"];
            $imageName = $image["name"];
            $imageTmpName = $image["tmp_name"];
            $imageType = $image["type"];
            $imageSize = $image["size"];
            $imageError = $image["error"];
            if ($imageError === UPLOAD_ERR_OK) {
                $destination = $uploadDir . $imageName;
                if (move_uploaded_file($imageTmpName, $destination)) {
                    $imagePath = "https://mini-project-resellify.000webhostapp.com/api/phpBackEnd/src/uploads/$imageName";
                    $productId = $_GET["id"];
                    $sql = "INSERT  INTO images (`userId`,`productId`,`url`) values ($userId,'$productId','$imagePath');";
                    $conn = new Connection();
                    $result = $conn->mysqli->execute_query($sql);

                    if ($result != 1) {
                        $route->InternalServerError();
                    }
                    $sql = "UPDATE product set status = 'COMPLETED' where id = $productId and status='INCOMPLETE'";
                    $result = $conn->mysqli->execute_query($sql);
                    if ($result != 1) {
                        $route->InternalServerError();
                    }
                    $route->setResponse(
                        array(
                            "data" => array(
                                "userId" => $userId,
                                "productId" => $productId
                            )
                        ),
                        "Image uploaded",
                        "sucess"
                    );
                } else {
                    echo "Error moving the uploaded image.";
                }
            } else {
                echo "Error during image upload. Error code: " . $imageError;
            }

            return;
        }
    } catch (e) {
        $route->InternalServerError();
    }
}

function getUploadedImages($payload, &$route)
{
    try {
        $conn = new Connection();
        $requestBody = file_get_contents('php://input');
        $data = json_decode($requestBody, true);


        $resultFromToken = $route->verifyToken($_GET["token"]);

        if (!$resultFromToken) {
            $route->UnAuthenticationError();
        }
        $userId = $resultFromToken["id"];
        $productId = $_GET["id"];
        if ($userId <= 0) {
            $route->UnAuthenticationError();
        }
        $sql1 = "SELECT * from images where productId = $productId";
        $iamgesArray = [];
        $images = $conn->mysqli->execute_query($sql1);
        while ($row = mysqli_fetch_assoc($images)) {
            array_push($iamgesArray, $row);
        }

        $route->setResponse($iamgesArray, "sucess", "sucess");
    } catch (err) {
        $route->InternalServerError();
    }
}

function getAllProducts($payload, &$route)
{
    try {
        $conn = new Connection();
        $sql2 = "SELECT p.*, chatCount,
       GROUP_CONCAT(i.url) as images,
       COALESCE(c.cart_count, 0) as cart_count
FROM product as p
JOIN images as i ON p.id = i.productId
LEFT JOIN (
    SELECT productId, COUNT(*) as cart_count
    FROM cart
    GROUP BY productId
) as c ON p.id = c.productId
LEFT JOIN (
    SELECT productid, COUNT(*) as chatCount
    FROM chats
    GROUP BY productid
) as ch ON p.id = ch.productid
WHERE p.id = i.productId
  AND p.status NOT IN ('INCOMPLETE', 'SOLD','CANCEL','COMPLETE')
GROUP BY i.productId, p.createdAt
ORDER BY p.createdAt DESC;;
";
        $products = $conn->mysqli->execute_query($sql2);
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }

        $route->setResponse($productsArray, "sucess", "sucess");
    } catch (e) {
        print_r(e);
    }
}

function getMyProducts($payload, &$route)
{
    try {
        $userId = $_GET["userId"];
        $conn = new Connection();
        $sql2 = "SELECT p.*, 
       GROUP_CONCAT(i.url) as images,
       COALESCE(c.cart_count, 0) as cart_count,chatCount
FROM product as p
JOIN images as i ON p.id = i.productId
LEFT JOIN (
    SELECT productId, COUNT(*) as cart_count
    FROM cart
    GROUP BY productId
) as c ON p.id = c.productId
LEFT JOIN (
    SELECT productid, COUNT(*) as chatCount
    FROM chats
    GROUP BY productid
) as ch ON p.id = ch.productid
WHERE p.id = i.productId
  AND p.userId =$userId
GROUP BY i.productId, p.createdAt
ORDER BY p.createdAt DESC;";
        $products = $conn->mysqli->execute_query($sql2);
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }

        $route->setResponse($productsArray, "sucess", "sucess");
    } catch (e) {
        print_r(e);
    }
}

function getSingleProducts($payload, &$route)
{
    try {
        $conn = new Connection();
        $id = $route->getQuery("productId");
        $sql2 = "SELECT p.* , GROUP_CONCAT(url) as images FROM product as p,images as i where p.id = i.productId and p.id = $id and p.status not in ('INCOMPLETE','SOLD')  GROUP by i.productId;";
        $products = $conn->mysqli->execute_query($sql2);
        // print_r("imagesResult");
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }

        $route->setResponse($productsArray, "sucess", "sucess");
    } catch (e) {
        print_r(e);
    }
}

function getChatCountOfAProduct($payload, &$route)
{
    try {
        $conn = new Connection();
        $id = $route->getQuery("productId");
        $sql2 = "Select count(productid) as count from chats where productid =$id;";
        $products = $conn->mysqli->execute_query($sql2);
        // print_r("imagesResult");
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }

        $route->setResponse($productsArray, "sucess", "sucess");
    } catch (err) {
        print(err);
    }
}
function getInsightsOfAProduct($payload, &$route)
{
    try {
        $conn = new Connection();
        $id = $route->getQuery("productId");
        $sql2 = "SELECT COUNT(c.productId) as cartCount FROM cart as c , product as p WHERE p.id = c.productId and p.id =$id;";
        $products = $conn->mysqli->execute_query($sql2);
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }
        $route->setResponse($productsArray[0], "sucess", "sucess");
    } catch (e) {
        print_r(e);
    }
}

function isProductIsInCart($payload, &$route)
{
    try {
        $conn = new Connection();
        $pid = $route->getQuery("productId");
        $uid = $route->getQuery("userId");
        $sql2 = "SELECT * FROM cart as c WHERE c.productId =$pid and c.userId=$uid;";
        $products = $conn->mysqli->execute_query($sql2);
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }
        $isPresent = false;
        if (sizeof($productsArray) > 0) {
            $isPresent = true;
        }
        $route->setResponse(array("isPresent" => $isPresent), "sucess", "sucess");
    } catch (e) {
        print_r(e);
    }
}

function addToCart($payload, &$route)
{
    try {
        $conn = new Connection();
        $pid = $route->getQuery("productId");
        $uid = $route->getQuery("userId");
        $sql1 = "SELECT * FROM cart as c WHERE c.productId = $pid and c.userId =$uid;";
        $sql2 = "SELECT * FROM product as p WHERE p.id= $pid and p.userId =$uid;";
        $products = $conn->mysqli->execute_query($sql1);
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }

        if (sizeof($productsArray) >= 1) {
            $route->setResponse(array("data" => "This product is already exist in cart"), "This product is already exist in cart", "failure");
        }
        $products = $conn->mysqli->execute_query($sql2);
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }

        if (sizeof($productsArray) >= 1) {
            $route->setResponse(array("data" => "You cont add your product in cart"), "This product is already exist in cart", "failure");
        }
        $sql2 = "INSERT INTO `cart` (`id`, `userId`, `productId`, `createdAt`, `updatedAt`) VALUES (NULL, '$uid', '$pid', current_timestamp(), current_timestamp());";
        $products = $conn->mysqli->execute_query($sql2);
        if ($products == 1) {
            $route->setResponse(array("data" => "product added to cart"), "product added to cart", "sucess");
        } else {
            $route->InternalServerError();
        }
    } catch (e) {
        print_r(e);
    }
}

function removeFromCart($payload, &$route)
{
    try {
        $conn = new Connection();
        $pid = $route->getQuery("productId");
        $uid = $route->getQuery("userId");
        $sql1 = "SELECT * FROM cart as c WHERE c.productId = $pid and c.userId =$uid;";
        $products = $conn->mysqli->execute_query($sql1);
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }

        if (sizeof($productsArray) == 0) {
            $route->setResponse(array("data" => "This product not exist in cart"), "This product not exist in cart", "failure");
        }
        $sql1 = "DELETE FROM cart WHERE `userId` = '$uid' and `productId` = '$pid';";
        $products = $conn->mysqli->execute_query($sql1);
        if ($products == 1) {
            $route->setResponse(array("data" => "product removed from cart"), "Product removed from cart", "sucess");
        } else {
            $route->InternalServerError();
        }
    } catch (e) {
        print_r(e);
    }
}
function getCartDetails($payload, &$route)
{
    try {
        $conn = new Connection();
        $uid = $route->getQuery("userId");
        $sql1 = "SELECT p.* , GROUP_CONCAT(i.url) as images FROM cart as c , product as p , images as i WHERE c.productId = p.id and i.productId = p.id and c.userId =$uid GROUP BY p.id order by p.createdAt DESC;";
        $products = $conn->mysqli->execute_query($sql1);
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }

        $route->setResponse(array("data", $productsArray), "Here is a cart details", "sucess");
    } catch (e) {
        print_r(e);
    }
}

function getUniqueCategoryList($payload, &$route)
{
    try {
        $conn = new Connection();
        $sql1 = "SELECT DISTINCT category FROM product;";
        $category = $conn->mysqli->execute_query($sql1);
        $categoryArray = [];
        while ($row = mysqli_fetch_assoc($category)) {
            array_push($categoryArray, $row);
        }

        $route->setResponse($categoryArray, "unique category", "sucess");
    } catch (e) {
        print_r(e);
    }
}

function search($payload, &$route)
{
    try {
        $conn = new Connection();
        $search = $route->getQuery("search");
        $sql = "SELECT p.*, 
       GROUP_CONCAT(i.url) as images,
       COALESCE(c.cart_count, 0) as cart_count,
       COALESCE(ch.chatCount, 0) as chat_count
FROM product as p
JOIN images as i ON p.id = i.productId
LEFT JOIN (
    SELECT productId, COUNT(*) as cart_count
    FROM cart
    GROUP BY productId
) as c ON p.id = c.productId
LEFT JOIN (
    SELECT productid, COUNT(*) as chatCount
    FROM chats
    GROUP BY productid
) as ch ON p.id = ch.productid
WHERE (p.productName LIKE '%$search%'
   OR p.model LIKE '%$search%'
   OR p.description LIKE '%$search%'
   OR p.yearOfPurchase LIKE '%$search%'
   OR p.price LIKE '%$search%'
   OR p.noOfOwner LIKE '%$search%'
   OR p.location LIKE '%$search%'
   OR p.category LIKE '%$search%')
   AND p.status NOT IN ('INCOMPLETE', 'SOLD','CANCEL')
GROUP BY p.id
LIMIT 5;
";

        $searchResult = $conn->mysqli->execute_query($sql);
        $searchResultArray = [];
        while ($row = mysqli_fetch_assoc($searchResult)) {
            array_push($searchResultArray, $row);
        }
        $route->setResponse($searchResultArray, 'search result', 'sucess');
    } catch (e) {
        print_r(e);

    }
}

function searchTrending($payload, &$route)
{
    try {
        $conn = new Connection();
        $userId = $route->getQuery('userId');
        $sql = "SELECT p.*, COUNT(c.id) as noOfCart FROM product as p
JOIN cart as c ON p.id = c.productId
GROUP BY p.id
ORDER BY p.views DESC, noOfCart DESC LIMIT 5;";
        $trendingResult = $conn->mysqli->execute_query($sql);
        $searchResultArray = [];
        while ($row = mysqli_fetch_assoc($trendingResult)) {
            array_push($searchResultArray, $row);
        }

        $sql2 = "SELECT * FROM `recentlysearchhistory` as r , `product` as p WHERE r.userId =$userId and p.id = r.productId  ORDER BY r.createdAt DESC limit 5";
        $recentSearchResult = $conn->mysqli->execute_query($sql2);
        $recentSearchResultArray = [];

        while ($row = mysqli_fetch_assoc($recentSearchResult)) {
            array_push($recentSearchResultArray, $row);
        }
        $route->setResponse(array("trendingResult" => $searchResultArray, "recentSearch" => $recentSearchResultArray, ), "search result", "sucess");
    } catch (e) {
        $route->InternalServerError();
    }
}

function incrementProductViewCount($payload, &$route)
{
    try {
        $conn = new Connection();
        $productId = $route->getQuery("productId");
        $sql = "update product set views =views+1 where id = $productId;";
        $result = $conn->mysqli->execute_query($sql);
        if ($result) {
            $route->setResponse(array("data" => "sucessfull incremented product view"), "sucessfull incremented product view", "sucess");
        }
    } catch (e) {
        $route->InternalServerError();
    }
}

function addToSearchHistory($payload, &$route)
{
    try {
        $productId = $route->getQuery("productId");
        $userId = $route->getQuery("userId");
        $sql1 = "select * from recentlysearchhistory where productId=$productId and $userId = $userId";
        $conn = new Connection();
        $isAlredyExist = $conn->mysqli->execute_query($sql1);
        $count = 0;
        while ($row = mysqli_fetch_assoc($isAlredyExist)) {
            $count += 1;
        }
        // update
        if ($count >= 1) {
            $sql2 = "update recentlysearchhistory set createdAt = current_timestamp() where productId=$productId and $userId = $userId";
            $isInserted = $conn->mysqli->execute_query($sql2);
            if ($isInserted == 1) {
                $route->setResponse(array(["updated sucess"]), "updated sucess", "sucess");
            } else {
                $route->setResponse(array(["not updated"]), "not updated", "failure");
            }
        } else {
            $sql2 = "INSERT INTO `recentlysearchhistory` (`id`, `userId`, `productId`, `createdAt`) VALUES (NULL, $userId, $productId, current_timestamp());";
            $isInserted = $conn->mysqli->execute_query($sql2);
            if ($isInserted == 1) {
                $route->setResponse(array(["Inserted sucess"]), "Inserted sucess", "sucess");
            } else {
                $route->setResponse(array(["not Inserted"]), "not Inserted", "failure");
            }
        }
    } catch (err) {
        $route->InternalServerError();
    }
}

function removeFromSearchHistory($payload, &$route)
{
    try {
        $productId = $route->getQuery("productId");
        $userId = $route->getQuery("userId");
        $sql = "DELETE FROM recentlysearchhistory WHERE userId=$userId and productId=$productId";
        $conn = new Connection();
        $isDeleted = $conn->mysqli->execute_query($sql);
        if ($isDeleted) {
            $route->setResponse(array("data", "Removed sucesfully"), "Removed sucesfully", "sucess");
        } else {
            $route->setResponse(array("data", "Not removed"), "Not removed", "failure");
        }
    } catch (err) {
        $route->InternalServerError();
    }
}

function changeProductStatus($payload, &$route)
{
    try {
        $allowedStatus = ["CANCEL", "SOLD", "VERIFIED"];
        $productId = $route->getQuery("productId");
        $status = $route->getQuery("status");

        if (in_array($status, $allowedStatus)) {
            $sql = "update product set status='$status' where id = $productId";
            $conn = new Connection();
            $isUpdated = $conn->mysqli->execute_query($sql);
            if (!$isUpdated) {
                $route->setResponse(array("data", "Not updated"), "Not updated", "failure");
            }
            $sql2 = "delete from cart where productId = '$productId'";
            $isdeleted = $conn->mysqli->execute_query($sql2);
            $route->setResponse(array("data", "updated sucesfully"), "updated sucesfully", "sucess");
        }
        $route->RequirementsNotMatchedError();
    } catch (e) {
        print(e);
    }
}

function validator($payload, &$route)
{
    try {
        $conn = new Connection();
        $sql2 = "SELECT p.* , GROUP_CONCAT(i.url) as images FROM product as p , images as i WHERE i.productId = p.id and p.status = 'COMPLETED' GROUP BY p.id order by p.createdAt DESC;";

        $products = $conn->mysqli->execute_query($sql2);
        $productsArray = [];
        while ($row = mysqli_fetch_assoc($products)) {
            array_push($productsArray, $row);
        }

        $route->setResponse($productsArray, "sucess", "sucess");
    } catch (err) {

    }
}

function initiateChat($payload, &$route)
{
    try {
        $conn = new Connection();
        $userId = $route->getQuery("userId");
        $productId = $route->getQuery("productId");
        $sql2 = "SELECT name from user  WHERE id = $userId;";
        $user = $conn->mysqli->execute_query($sql2);
        $secondPartyName = "";
        while ($row = mysqli_fetch_assoc($user)) {
            $secondPartyName = $row["name"];
        }

        $sql3 = "SELECT u.name , u.id from user as u , product as p  WHERE p.id = $productId and u.id = p.userId;";
        $first = $conn->mysqli->execute_query($sql3);
        $firstPartyName = "";
        $firstPartyId = "";
        while ($row = mysqli_fetch_assoc($first)) {
            $firstPartyName = $row["name"];
            $firstPartyId = $row["id"];
        }

        $sql4 = "INSERT INTO `chats` (`firstparty`, `secondparty`, `productid`, `sellerid`, `firstpartyname`, `secondpartyname`) VALUES ('$firstPartyId', '$userId', '$productId', '$firstPartyId', '$firstPartyName', '$secondPartyName');";

        $isInserted = $conn->mysqli->execute_query($sql4);

        if ($isInserted == 1) {
            $route->setResponse(array("data" => "chat initiated"), "chat initiated", "sucess");
        }
        $route->RequirementsNotMatchedError();
    } catch (err) {

    }
}
?>