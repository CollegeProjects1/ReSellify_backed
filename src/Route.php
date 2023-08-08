<?php
class Router
{
    private $allowedRequestMethod = array("GET", "POST", "PUT", "DELETE");
    private $secreteKey = "resellifyProjectDoneAT6yhSem";

    public function getRequestMethod()
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];

            if (in_array($method, $this->allowedRequestMethod) == 1) {
                return $method;
            } else {
                die($this->setResponse(array(), "This method is not allowed", "failure"));

            }
        } catch (Exception $e) {
            die($this->setResponse(array(), $e->getMessage(), "failure"));
        }
    }

    public function getEndPoint()
    {
        try {

            if (isset($_GET["route"]) != 1) {
                die($this->setResponse(array("data" => "invalid"), "End point required", "failure"));
            }
            return $_GET["route"];
        } catch (Exception $e) {
            die($this->setResponse(array(), $e->getMessage(), "failure"));
        }
    }

    public function getQuery(string $key = "", bool $isRequired = false)
    {
        try {
            if (!$key || empty($key)) {
                die($this->setResponse(array(), "key is required", "failure"));
            }
            if (isset($_GET[$key]) != 1 && $isRequired) {
                die($this->setResponse(array(), $key . " is required", "failure"));
            }

            return $_GET[$key];
        } catch (Exception $e) {
            die($this->setResponse(array(), $e->getMessage(), "failure"));
        }
    }

    public function setResponse(array $data, string $message, string $status = "sucess")
    {
        try {
            header('Content-Type: application/json');
            $response = array(
                'status' => $status,
                'message' => $message,
                'data' => $data
            );
            die(json_encode($response));

        } catch (Exception $e) {
            $response = array(
                'status' => "failure",
                'message' => $e->getMessage(),
                'data' => array("data" => "invalid")
            );
            die(json_encode($response));
        }
    }

    public function route(array $funArray, string $method = "GET", array &$payload = array(), &$route)
    {

        if ($this->getRequestMethod() != $method) {
            $this->NotFound404Error();
        }

        foreach ($funArray as $value) {
            if (function_exists($value) == 1) {
                $payload = $value($payload, $route);
            } else {
                $this->InternalServerError();
            }
        }
    }

    public function NotFound404Error()
    {
        die(
            $this->setResponse(array("data" => "404 , route not found"), "404 , route not found", "failure")
        );
    }

    public function InternalServerError()
    {
        die(
            $this->setResponse(array("data" => "InternalServerError"), "InternalServerError", "failure")
        );
    }
    public function RequirementsNotMatchedError()
    {
        die(
            $this->setResponse(array("data" => "RequirementsNotMatchedError"), "Please fill all required fields with valid data!!!", "failure")
        );
    }

    public function UnAuthenticationError()
    {
        die(
            $this->setResponse(array("data" => "unAuthenticated"), "Please  login!!!", "failure")
        );
    }

    function generateToken($payload)
    {
        // here we are defining the algo which we are using
        $header = ["algo" => "HS256", "type" => "HWT"];

        // if ($expire != null) {
        $header['expire'] = time() + 86400;
        // }
        // encoding header content
        $header_encoded = base64_encode(json_encode($header));

        // encoding payload (data) content
        $payload_encoded = base64_encode(json_encode($payload));

        //now create signature hash
        $signature = hash_hmac("SHA256", $header_encoded . $payload_encoded, $this->secreteKey);
        $signature_encoded = base64_encode($signature);
        return $header_encoded . "." . $payload_encoded . "." . $signature_encoded;
    }

    public function verifyToken($token)
    {
        try {
            $token_parts = explode('.', $token);
            $signature = base64_encode(hash_hmac("SHA256", $token_parts[0] . $token_parts[1], $this->secreteKey));

            if ($signature != $token_parts[2]) {
                return false;
            }

            $header = base64_decode($token_parts[0]);
            $data = json_decode($header);
            if ($data->expire < time()) {
                return false;
            }

            $payload = json_decode(base64_decode($token_parts[1]), true);
            return $payload;
        } catch (e) {
            $this->InternalServerError();
        }
    }

}
?>