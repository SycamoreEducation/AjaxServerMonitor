<?

$dblink = mysql_connect("localhost", "topper","bubba123");
//print("DB: $dblink");
$sc = mysql_select_db("sls", $dblink);

$plot=$_GET["plot"];
$update=$_GET["update"];

if($plot==1){
    $dbnames = Array();

    $sql = "SELECT Name FROM ServerNames ";
    $rs = mysql_query($sql);
    $rsc = mysql_num_rows($rs);

    for ($i=0; $i<$rsc; $i++){
        $servername = mysql_result($rs,$i,'Name');
        $dbnames[] = $servername;
    }
    //$dbnames = Array("431212-db1.sycam","431214-db2.sycam","cs001","cs002","CS003","CS004","CS005","CS006","CS007","CS008","CS009","CS010");


    //var_dump($dbnames);
    $index = 0;
    $dbarray = Array(Array());
    $timearray = Array();
    $tickarray = Array();

    $gmthour = date('H');
    if($gmthour >= 5) $currenthour = $gmthour - 5;
    else $currenthour = $gmthour +19;
    $currentminute = date('i');
    //echo "Hour:$currenthour Minute:$currentminute <BR>";

    for($i=0; $i<$intervals; $i++){
        if($currentminute >= (10*$i)) {
            $minutetick=$currentminute-(10*$i);
            $hourtick=$currenthour;
        }else {
            $minutetick = ($currentminute-(10*$i))+60;
            $hourtick = $currenthour-1;
        }
        $hourtick = sprintf('%02d', $hourtick);
        $minutetick = sprintf('%02d', $minutetick);
        $timearray[] = "$hourtick:$minutetick";
        $newtick=Array(-$i*10,$timearray[$i]);
        if($i==0) $tickarray[] = Array($i,$timearray[$i]);
        else array_unshift($tickarray,$newtick);
        //echo "Time:$hourtick:$minutetick <br>";
    }
    $maxLoad = 0;
    foreach($dbnames as $key => $db){
        $sql .= "SELECT UNIX_TIMESTAMP(DateTime) Timestamp, ";
        $sql .= "LoadAverage ";
        $sql .= "FROM ServerLoad ";
        $sql .= "WHERE Hostname = '$db' ";
        $sql .= "ORDER BY -DateTime ";
        if($update == 1) $sql .= "LIMIT 1";
        else $sql .= "LIMIT 61";
        $rs = mysql_query($sql);
        if($rs) $rsc = mysql_num_rows($rs);
        if($db == 2) print("SQL: $sql");
    /*
        if($key == "DB1"){
            if($rs) echo "RS exists"; 
            echo "$sqli <br>";        
            $hour =  mysql_result($rs,0,'Hour'); 
            $minute = mysql_result($rs,0,'Minute'); 
            echo "Hour:$hour <br>";
            echo "Minute:$minute <br>";
        }
    */
        for($i=0; $i < $rsc; $i++){
            $loadavg = mysql_result($rs, $i, 'LoadAverage');

           
            
            settype($loadavg, "float");
            
            if($loadavg > $maxLoad) $maxLoad = $loadavg;
            //print("Load:$loadavg Max: $maxLoad <BR>");
            
            if($hour < 5) $hour = $hour + 19;
            else $hour = $hour - 5; 
            $mintotal = ($hour *60) + $minute; 
            //$time = round($hour + $minute/60,2);
            $timestr = "$hour:$minute";
            
            $timedif = $mintotal - $currentmintotal;
            //echo "Time:$timestr <br>";
            //$timelen=strlen($time);
            //$time=substr($time, 0, $timelen-3);
            //if($db=="cs001") print("TIME:$time,I:-$i <BR>"); 
            $dbarray[$index][] = Array(-($i), $loadavg);
        }
        //if($index == 11)var_dump($dbarray); 
        //var_dump($dbarray["cs001"][0]);
        //var_dump($dbarray["cs001"][59]);

        //echo $dbname;
        $index++;
    }
    //var_dump($dbarray);
    $ylimit = ceil($maxLoad*10)/10;
    if(($ylimit*10)%2 != 0) $ylimit += .1;
    
    
    $mainarray = Array();
    $mainarray[0]=$dbarray;
    $mainarray[1]=$dbnames;
    $mainarray[2]=$timearray;
    $mainarray[3]=$ylimit;
    $mainarray[4]=$tickarray;
    //print("YLIMIT: $ylimit Max:$maxLoad <br>");
    echo json_encode($mainarray);
    //echo json_encode($dbarray);
    //echo json_encode($dbnames);
    //echo json_encode($timearray);
    //print($dbjson);
    //var_dump($mainarray);
    exit;
}
?>

