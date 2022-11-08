<?php

include 'BDhumanList.class.php';

if (class_exists('DatabaseHuman')){
    echo "class included<br>";
}
//null, 'ktaka', 'sutulooo', '06.09.2003', 1, 'MINSK'
$human=new DatabaseHuman(5);
echo $human->name.'<br><br>';

$years=DatabaseHuman::BirthToAge($human);
echo $years;

echo DatabaseHuman::GetSex($human);
$human->FormatHuman($human, 'agesex');
$newCHEL=$human->FormatHuman($human, 'sex');


$list=new ListOfPeople('id','>=','63');

$list->DeleteUsersArray();

