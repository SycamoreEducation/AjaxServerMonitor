<?

require_once("passwords.inc");

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];
$pass = $_SERVER['PHP_AUTH_PW'];


$dblink = mysql_connect(DB_HOST, DB_USER,DB_PASS);
$sc = mysql_select_db(DB_NAME, $dblink);

$update=$_GET["update"];
$plot=$_GET["plot"];
$xscale=$_GET["xscale"];
if(empty($xscale)) $xscale = 1;
if(empty($update)) $update = 0;
//$plot='db';

//get timestamp
$newtime = time();
//round down to nearest minute or nearest 10 minutes for user graph
if($plot == 'user') $endtime = $newtime-$newtime%600;
else $endtime = $newtime-$newtime%60;

//get relevant start time for the x scale
if($update==0)$starttime = $endtime-3660*$xscale;
//only get data from the last minute for update
if($update==1)$starttime = $endtime-60*$xscale;
//echo "Start: $starttime <br> End: $endtime <br> ";
function formattime($time) {
    settype($time,'int');
    // force all times to nearest minute
    if($time%60 >= 30){
        $time += 60-$time%60;
    }elseif($time%60<30){
        $time-= $time%60;
    }
    return $time; 
}

if($plot == 'db' || $plot=='cloud') {

    if($plot == 'db') $type = 1;
    elseif($plot == 'cloud') $type = 2;
    $dbscale = $xscale *60 +1+2;
    if($xscale == 1 || $xscale == 24) $filter = 10;
    else $filter = 12;

    $data = Array(Array());
    $dbnames = Array();
    $dbarray = Array();
    $dbticks = Array();

    $sql  = "SELECT Label, Name FROM ServerNames ";
    $sql .= "WHERE Type = $type ";
    $sql .= "ORDER BY Name ";
    $rs = mysql_query($sql);
    $rsc = mysql_num_rows($rs);

    for ($i=0; $i<$rsc; $i++){
        $serverlabel = mysql_result($rs,$i,'Label');
        $servername = mysql_result($rs,$i,'Name');
        $dbnames[0][$i] = $servername;
        $dbnames[1][$i] = $serverlabel;
    }
    foreach($dbnames[0] as $key => $db){
        $sql  = "SELECT UNIX_TIMESTAMP(DateTime) UnixTime";
        $sql .= ", LoadAverage, DateTime ";
        $sql .= "FROM ServerLoad ";
        $sql .= "WHERE Hostname = '$db' ";
        $sql .= "HAVING UnixTime >= $starttime "; 
        $sql .= "AND UnixTime <= $endtime ";
        $sql .= "ORDER BY UnixTime ";
        if($update) $sql .= "DESC LIMIT 1";
        else $sql .= "ASC";
        $rs = mysql_query($sql);
        if($rs) $rsc = mysql_num_rows($rs);
        settype($key, "int");

        $count=0;
        for($i=0; $i < $rsc; $i++){
            if($i%$xscale==0){
                $loadavg = mysql_result($rs, $i, 'LoadAverage');
                $unixtime = mysql_result($rs, $i, 'UnixTime');
                $testtime = mysql_result($rs, $i, 'DateTime');

                $converted=formattime($unixtime);
                settype($loadavg, "float");
                settype($converted, "int");
                settype($unixtime, "int");
                $thisindex = count($dbarray[$key])-1;
                $converted+=60*$xscale;
                if($update!=1 && $i>0){
                    $lasttime=$dbarray[$key][$thisindex][0];
                    settype($lasttime, "int");
                    //if($key==1)echo "LAST: $lasttime Current: $converted Count $thisindex  I $i<br>";
                    while($converted-$lasttime > $xscale*60){
                        $diff=$converted - $lasttime;
                        //if($key==1)echo "LAST: $lasttime Con: $converted Diff: $diff<br>";
                        $lasttime+=$xscale*60;
                        $dbarray[$key][]=Array($lasttime,0);
                    }
                }
                $dbarray[$key][]=Array($converted,$loadavg);
            }
        }
    }
    if(!$update){
        foreach($dbarray as $key => $db){
            $dataend = $dbarray[$key][count($db)-1][0];
            $datastart = $dbarray[$key][0][0];
            while($endtime - $dataend >= ($xscale*60)){
                $dataend+=$xscale*60;
                $fakearray1=array($dataend,0);
                array_push($dbarray[$key],$fakearray1);
            }
            while($datastart - $starttime >= $xscale*60){
                $datastart-=$xscale*60;
                $fakearray2=array($datastart,0);
                array_unshift($dbarray[$key],$fakearray2);
            }
        }
    }
    if($update==1){
        foreach($dbnames[0]as $key => $db){
            if(!array_key_exists($key,$dbarray)){
                settype($endtime,'int');
                $dbarray[$key][]=Array($endtime,0);
                //if(empty($dbarray)) $dbarray[$key][]=Array($endtime,0);
                //else array_splice($dbarray,$key,0,array(array(array($endtime,0))));
            }
        }
    }
    $data[0]= $dbarray;
    $data[1]= $dbnames[1];
    
    if(empty($update)) echo json_encode($data);
    else echo json_encode($dbarray);
    exit;
}
if($plot=='user'){
    $data=Array(Array());
    $userarray=Array();
    $userticks=Array();

    $userscale = $xscale *6 +1;
    //if($xscale == 1 || $xscale == 24) $filter=$xscale;
    //else $filter = $xscale/5 + $xscale;
    
    $sql  = "SELECT UNIX_TIMESTAMP(Date) UnixTime, Count ";
    $sql .= "FROM Whoson ";
    $sql .= "HAVING UnixTime >= $starttime "; 
    $sql .= "AND UnixTime <= $newtime ";
    $sql .= "ORDER BY UnixTime ASC "; 
    if($update) $sql .= "LIMIT 1 ";
    //else $sql .= "LIMIT $userscale ";
    //echo "$sql <br>";
    $rs = mysql_query($sql);
    if($rs) $rsc = mysql_num_rows($rs);
    
    
    for($i=0; $i < $rsc; $i++){
        $usercount = mysql_result($rs, $i, 'Count');
        $time = mysql_result($rs, $i, 'UnixTime');
        $converted=formattime($time);
        settype($usercount,"int");
        settype($time,"int");
        settype($converted,"int");
        $userarray[]=Array($converted,$usercount);
    }
    //$data = array_reverse($userarray);
    $data = $userarray;
    echo json_encode($data);
    exit;
    
}
?>

