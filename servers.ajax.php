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

$newtime = time();
$endtime = $newtime-$newtime%60+30;
if($update==0)$starttime = $endtime-3780*$xscale;
if($update==1)$starttime = $endtime-60*$xscale;

function formattime($time) {
    settype($time,'int');
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
        $sql .= "ORDER BY -UnixTime ";
        $rs = mysql_query($sql);
        if($rs) $rsc = mysql_num_rows($rs);
        settype($key, "int");

        for($i=0; $i < $rsc; $i++){
            if($i%$xscale==0){
                $loadavg = mysql_result($rs, $i, 'LoadAverage');
                $unixtime = mysql_result($rs, $i, 'UnixTime');
                $testtime = mysql_result($rs, $i, 'DateTime');

                $converted=formattime($unixtime);
                settype($loadavg, "float");
                settype($converted, "int");
                settype($unixtime, "int");

                $dbarray[$key][]=Array($converted,$loadavg);
            }
        }
    }
    $oldkey=-1;
    if($update==1){
        foreach($dbnames[0]as $key => $db){
            if(!array_key_exists($key,$dbarray)){
                array_splice($dbarray,$key,0,array(array(array(0,0))));
            }
        }
    }
    foreach($dbarray as $key => $a){ 
        $dbarray[$key] = array_reverse($a);
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
    if($xscale == 1 || $xscale == 24) $filter=$xscale;
    else $filter = $xscale/5 + $xscale;
    
    $sql  ="SELECT UNIX_TIMESTAMP(Date) UnixTime, Count ";
    $sql .="FROM Whoson ";
    $sql .="ORDER BY -UnixTime "; 
    if($update) $sql .="LIMIT 1 ";
    else $sql .="LIMIT $userscale ";
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
    $data = array_reverse($userarray);
    echo json_encode($data);
    exit;
    
}
?>

