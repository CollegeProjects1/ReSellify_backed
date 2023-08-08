<?php
class Connection
{

    private $dbHost = "localhost";
    private $dbUser = "root";
    private $dbPassword = "root";
    private $dbName = "resellify";

    private $conn = false;
    public $mysqli = "";

    private $result = [];

    // for data base connection
    public function __construct()
    {
        if (!$this->conn) {
            $this->mysqli = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName, 3307);

            $this->conn = true;

            if ($this->mysqli->connect_error) {
                array_push($this->result, $this->mysqli->connect_error);
                return false;
            } else {
                return true;
            }
        }
    }

    public function isTableExists($table)
    {
        $table_Query = "SHOW TABLES";

        $table_list = $this->mysqli->query($table_Query);

        print_r($table_list);

        if ($table_list->num_rows == 1) {
            return true;
        } else {
            array_push($this->result, $table . " table does not exist");
            return false;
        }
    }

    // public function insert($tableName , $param = array()){
    //     echo $tableName;
    //         if(!$this->isTableExists($tableName)){
    //             array_push();
    //             return false;
    //         }

    //         // implode is similar to join by ","
    //         // joining every elements separated by ","
    //         $columns = implode(',',array_keys($param));

    //         // now simailary for values
    //         $values = implode("','",array_values($param));


    //         $query = "INSERT INTO $table ($columns) VALUES(`$values`)";
    //         print_r($query);

    //         if($this->mysqli->query($query)){
    //             array_push($this->result,$this->mysqli->insert_id);
    //             print_r($this->result);
    //             // retun true;
    //         }else{
    //             array_push($this->result,$this->mysqli->error);
    //             print_r($this->result);
    //             // retun false;
    //         }

    // }

    public function update()
    {
    }

    public function delete()
    {
    }

    public function getResult()
    {
        $val = $this->result;
        $this->result = array();
        return $val;
    }

    // here we are closing a connection upon completing all work
    public function __destruct()
    {
        if ($this->conn) {
            if ($this->mysqli->close()) {
                $this->conn = false;
                return true;
            }
            return false;
        } else {
            return false;
        }
    }
}
?>