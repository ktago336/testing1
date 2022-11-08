<?php
/*
 *class works with mysql db
 * used to pull DatabaseHuman
 * objects lists for further processing
 *
 *
 *
 */

class DatabaseHuman{
    public $id;

    public $name;

    public $surname;

    /**
     * @var $dateOfBirth
     *
     * format: "xx.xx.xxxx"
     */
    public $dateOfBirth;

    public $sex;

    public $cityOfBirth;

    private $mysqli;

    private $sqlSettings=array(
        'host'=>'localhost',
        'user'=>'root',
        'password'=>'root',
        'database'=>'test',
    );

    /**
     * DatabaseHuman constructor.
     *
     * GIVE id NULL+
     * +other parameters to create new instance of human
     *
     * or
     *
     * GIVE id to pull a human by id from DB
     *
     * @param null $id
     * @param null $name
     * @param null $surname
     * @param null $dateOfBirth
     * @param int $sex              1 for male 0 for female
     * @param null $cityOfBirth
     *
     * creates new instance of human, if all params given
     * inserts a new person to the DB and fill attributes, if only ID given
     * gets a person by id from the DB
     */
    public function __construct($id=null, $name=null, $surname=null, $dateOfBirth=null,
                                $sex=null, $cityOfBirth=null)
    {
        $this->mysqli = new mysqli($this->sqlSettings['host'],
            $this->sqlSettings['user'],
            $this->sqlSettings['password'],
            $this->sqlSettings['database'],
        );

        if ($this->mysqli->connect_error) {
            die('Unable to connect to database');
        }

        if (isset($name, $surname, $dateOfBirth,
                $sex, $cityOfBirth)

            &&gettype($name)=='string'
            &&gettype($surname)=='string'
            &&gettype($dateOfBirth)=='string'
            &&gettype($sex)=='integer'
            &&gettype($cityOfBirth)=='string'
            &&!isset($id)) {

            if ($sex !== 1 && $sex !== 0)
                die('wrong sex input');

            if (preg_match('/\d{2}\.\d{2}\.\d{2,4}/', $dateOfBirth) !== 1)
                die('wrong date of birth format (xx.xx.xxxx)');

            $this->id = $id;
            $this->name = $name;
            $this->surname = $surname;
            $this->dateOfBirth = $dateOfBirth;
            $this->sex = $sex;
            $this->cityOfBirth = $cityOfBirth;
            $this->Insert();
        }

        elseif (isset($id)&&!isset($name, $surname, $dateOfBirth, $sex, $cityOfBirth)){
            if ($id>=0){
                $request=$this->mysqli->prepare("SELECT * FROM users WHERE id=?");
                $request->bind_param('i', $id);
                if($request->execute()) {
                    $request->bind_result($DBid, $DBname, $DBsurname,
                        $DBdateOfBirth, $DBsex, $DBcityOfBirth);
                    $request->fetch();

                    $this->id = $DBid;
                    $this->name = $DBname;
                    $this->surname = $DBsurname;
                    $this->dateOfBirth = $DBdateOfBirth;
                    $this->sex = $DBsex;
                    $this->cityOfBirth = $DBcityOfBirth;
                }
                else echo "wrong user id or database error occured";
            }
        }
        else die('wrong human instance declaration');
    }

    public function Insert(){
        $request=$this->mysqli->prepare("INSERT INTO users(
                  name, surname, dateOfBirth, sex, cityOfBirth)
                  VALUES (?,?,?,?,?)");

        $request->bind_param('sssis', $this->name, $this->surname,
                            $this->dateOfBirth, $this->sex, $this->cityOfBirth);

        if (!$request->execute()){
            die('database insert error');
        };
    }

    public function Delete(){
        $request=$this->mysqli->prepare("DELETE FROM users WHERE id=?");
        $request->bind_param('i', $this->id);
        if (!$request->execute()){
            die('database delete error');
        };
    }

    public static function BirthToAge($human){
        $year=substr($human->dateOfBirth, -4);
        return (int)((int)date('Y')-(int)$year);
    }

    public static function GetSex($human){
        if ($human->sex==1)
            return 'male';
        elseif ($human->sex==0)
            return 'female';
        else die ($human->id . ' user have invalid sex code');
    }

    /**
     * @return stdClass Human with modified field(s) "sex"/"age"/both
     *
     * @param $human
     * @param string $params values: age, sex, agesex
     */
    public function FormatHuman($human, $params=''){

        $ToStd=function ($human, $ModHuman){
            $ModHuman->id = $human->id;
            $ModHuman->name = $human->name;
            $ModHuman->surname = $human->surname;
            $ModHuman->dateOfBirth = $human->dateOfBirth;
            $ModHuman->sex = $human->sex;
            $ModHuman->cityOfBirth = $human->cityOfBirth;
        };

        if ($params=='age'){
            $ModHuman=new stdClass();
            $ToStd($human,$ModHuman);
            $ModHuman->age=DatabaseHuman::BirthToAge($human);

            return $ModHuman;
        }
        elseif ($params=='sex'){
            $ModHuman=new stdClass();
            $ToStd($human,$ModHuman);
            $ModHuman->sex=DatabaseHuman::GetSex($human);

            return $ModHuman;
        }
        elseif ($params=='agesex'){
            $ModHuman=new stdClass();
            $ToStd($human,$ModHuman);
            $ModHuman->sex=DatabaseHuman::GetSex($human);
            $ModHuman->age=DatabaseHuman::BirthToAge($human);
            return $ModHuman;

        }
        else
            die("'incorrect argument in FormatHuman, 
                available: 'age', 'sex', 'agesex'");



    }
}