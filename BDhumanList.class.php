<?php
if (file_exists('BDwork.class.php')){
    include 'BDwork.class.php';
    if (class_exists('DatabaseHuman')){
    }
    else die ('cannot include class.');
}
else die ('no file with class finded.');
/**
 * Class ListOfPeople
 *
 * generates lists of DatabaseHuman objects
 * returns it for further processing
 */
class ListOfPeople{

    public $userIds=array();

    private $sqlSettings=array(
        'host'=>'localhost',
        'user'=>'root',
        'password'=>'root',
        'database'=>'test',
    );

    private $mysqli;

    public function __construct($field, $operator, $value){

        $this->mysqli = new mysqli($this->sqlSettings['host'],
            $this->sqlSettings['user'],
            $this->sqlSettings['password'],
            $this->sqlSettings['database'],
        );

        if ($this->mysqli->connect_error) {
            die('Unable to connect to database');
        }

        $query="SELECT * FROM users WHERE $field$operator$value";
        $request=$this->mysqli->prepare($query);
        $request->execute();
        $result=$request->get_result();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                array_push($this->userIds, $row['id']);
            }
    }

    public function GetUsersArray(){
        $userArray=array();
        foreach ($this->userIds as $userId){
            $user=new DatabaseHuman($userId);
            if (isset($user))array_push($userArray, $user);
        }
    }

    public function DeleteUsersArray(){
        foreach ($this->userIds as $userId){
            $userToDelete=new DatabaseHuman($userId);
            $userToDelete->Delete();
        }
    }

}