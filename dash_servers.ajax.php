<?

$dblink = mysql_connect("localhost", "topper","bubba123");
//print("DB: $dblink");
$sc = mysql_select_db("sls", $dblink);

$update=$_GET["update"];
$plot=$_GET["plot"];
$xscale=$_GET["xscale"];
if(empty($xscale)) $xscale = 1;
//error_log("Update: $update Plot: $plot Xscale: $xscale");

function formattime($time) {
    settype($time,'int');
    while($time%60 != 00){
        if($time%60 >= 30) $time++;
        else $time--;
    }
    $utctime = DateTime::createFromFormat('U',$time, new DateTimeZone('UTC'));
    $csttime = $utctime;
    $csttime->setTimeZone(new DateTimeZone('America/Chicago'));
    return $time; 
}
if($plot == 'db' || $plot=='cloud') {

    if($plot == 'db') $type = 1;
    elseif($plot == 'cloud') $type = 2;
    $dbscale = $xscale *60 +1;
    if($xscale == 1 || $xscale == 24) $filter = 10;
    else $filter = 12;
    //error_log($dbscale);

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
    //echo "XSCALE:$xscale<br>";
    foreach($dbnames[0] as $key => $db){
        //$sql  = "SELECT DATE_FORMAT(DateTime, '%H:%i') Time, ";
        $sql  = "SELECT UNIX_TIMESTAMP(DateTime) UnixTime, ";
        $sql .= "LoadAverage, DateTime  ";
        $sql .= "FROM ServerLoad ";
        $sql .= "WHERE Hostname = '$db' ";
        $sql .= "ORDER BY -UnixTime ";
        if($update == 1) $sql .= "LIMIT 1";
        else $sql .= "LIMIT $dbscale ";
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
                //if($i%($filter*$xscale/4)==0) echo "DB: $db  Time: $testtime <br>";

                if($key==0) $dbarray[$key][]=Array($converted,$loadavg);
                else $dbarray[$key][]=Array($dbarray[0][$i/$xscale][0],$loadavg);
            }
        }
    }
    foreach($dbarray as $key => $a){ 
        $dbarray[$key] = array_reverse($a);
    }
    $data[0]= $dbarray;
    $data[1]= $dbnames[1];
    //var_dump($data);
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
    //error_log("USQL:$sql");
    
    
    for($i=0; $i < $rsc; $i++){
        $usercount = mysql_result($rs, $i, 'Count');
        $time = mysql_result($rs, $i, 'UnixTime');
        settype($usercount,"int");
        $converted = formattime($time);
        settype($converted,"int");
        $userarray[]=Array($converted,$usercount);
    }
    $data = array_reverse($userarray);
    echo json_encode($data);
    exit;
    
}
?>

