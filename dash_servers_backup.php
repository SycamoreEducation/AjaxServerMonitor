<?
?>
<!DOCTYPE html>
<HTML>
<HEAD>
    <META http-equiv="X-UA-Compatible" content="IE=9">
    <META charset="UTF-8">

    <META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">

    <META NAME='SLS' CONTENT='Dashboard' >

    <LINK rel='stylesheet' type='text/css' href='../../jqPlot/jquery.jqplot.css' />

    <SCRIPT src="jquery.js"></SCRIPT>
    <SCRIPT src="jqPlot/jquery.jqplot.min.js"></SCRIPT>
    <SCRIPT src="jqPlot/plugins/jqplot.logAxisRenderer.js"></SCRIPT>
    <SCRIPT src="jqPlot/plugins/jqplot.canvasTextRenderer.min.js"></SCRIPT>
    <SCRIPT src="jqPlot/plugins/jqplot.canvasAxisLabelRenderer.min.js"></SCRIPT>
    <SCRIPT src="jqPlot/plugins/jqplot.barRenderer.min.js"></SCRIPT>
    <SCRIPT src="jqPlot/plugins/jqplot.categoryAxisRenderer.min.js"></SCRIPT>
    <SCRIPT src="jqPlot/plugins/jqplot.pointLabels.min.js"></SCRIPT>
    <SCRIPT src="jqPlot/plugins/jqplot.dateAxisRenderer.min.js"></SCRIPT>
    <SCRIPT src="jqPlot/plugins/jqplot.canvasAxisTickRenderer.min.js"></SCRIPT>
    <SCRIPT src="jqPlot/plugins/jqplot.highlighter.min.js"></SCRIPT>
    <SCRIPT src="jqPlot/plugins/jqplot.cursor.min.js"></SCRIPT>


</HEAD>
<BODY style='margin: 0px 10px 0px 10px;'>

<?

$dblink = mysql_connect("localhost", "topper","bubba123");
//print("DB: $dblink");

$sc = mysql_select_db("sls", $dblink);

function displayperformance(){
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
    $interval = 1;

    $index = 0;
    $dbarray = Array(Array(Array(1,-1)));
    $timearray = Array();

    $gmthour = date('H');
    if($gmthour >= 5) $currenthour = $gmthour - 5;
    else $currenthour = $gmthour +19;
    $currentminute = round(date('i')/$interval)*$interval;

    if($currenthour == 0) $currenthour = 24;
    $currentmintotal = ($currenthour * 60) + $currentminute;
    echo "Hour:$currenthour Minute:$currentminute <BR>";

    $maxLoad = 0;

    foreach($dbnames as $key => $db){
        $sql  = "SELECT TIME_FORMAT(CAST(DateTime as time), '%H') Hour, ";
        $sql .= "TIME_FORMAT(CAST(DateTime as time), '%i') Minute, ";
        $sql .= "LoadAverage ";
        $sql .= "FROM ServerLoad ";
        $sql .= "WHERE Hostname = '$db' ";
        $sql .= "ORDER BY -DateTime ";
        $sql .= "LIMIT 61";
        $rs = mysql_query($sql);
        if($rs) $rsc = mysql_num_rows($rs);
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
            if($i%$interval!=0) continue;
            $hour =  mysql_result($rs,$i,'Hour'); 
            $minute = mysql_result($rs,$i,'Minute'); 
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
            //echo "Time:$time <br>";
            //$timelen=strlen($time);
            //$time=substr($time, 0, $timelen-3);
            //if($db=="cs001") print("TIME:$time,I:-$i <BR>"); 
            $dbarray[$index][] = Array(-($i), $loadavg);
            $timearray [] = "$timestr";
        }
        //if($index == 11)var_dump($dbarray); 
        //var_dump($dbarray["cs001"][0]);
        //var_dump($dbarray["cs001"][59]);

        //echo $dbname;
        $index++;
    }
    
    $ylimit = round($maxLoad,1,PHP_ROUND_HALF_UP);
    if(($ylimit*10)%2 != 0) $ylimit += .1;
    print("YLIMIT: $ylimit Max:$maxLoad");
    error_log("YLIMIT: $ylimit");
    $dbjson = json_encode($dbarray);
    $dbnamesjson = json_encode($dbnames);
    $timestrjson = json_encode($timearray);
    //print($dbjson);
?>
<FORM name=theForm action='dev.threoze.com/dash_servers.php' method=POST>
<INPUT type=hidden name=task value=update>
<DIV id='chartdiv' style='float:left;height:400px;width:1000px;'></DIV>
</FORM>

<SCRIPT>

    var db = JSON.parse('<?=$dbjson ?>');
    var dbnames = JSON.parse('<?=$dbnamesjson ?>');
    var time = JSON.parse('<?=$timestrjson ?>');
    //document.write(db[0]);
    /*
    var db0 = db[0];
    var db1 = db[1];
    var db2 = db[2];
    var db3 = db[3];
    var db4 = db[4];
    var db5 = db[5];
    var db6 = db[6];
    var db7 = db[7];
    var db8 = db[8];
    var db9 = db[9];
    var db10 = db[10];
    var db11 = db[11];
    */
    var options = {
        axes: {
            xaxis: {
                label: 'Time',
                min: -60,
                max: 0,
                //ticks: [0,.5,1,1.5,2,2.5,3,3.5,4,4.5,5]
                ticks: [[-60,time[60]],[-50,time[50]],[-40,time[40]],[-30,time[30]],[-20,time[20]],[-10,time[10]],[0,time[0]]]
            },
            yaxis: {
                label: 'Server Load(%)',
                min: 0,
                max: '<?=$ylimit ?>',
                numberTicks: 5,
                
                //ticks: [0,10,20,30,40,50,60,70,80,90,100]
            }
        },
        legend: {
            show: true,
            placement: 'outsideGrid'
        }, 
        seriesDefaults: {
            lineWidth: 1,
            showMarker: false
        }
    };
            
    options.series = [];  
    dbnames.forEach(function(entry){
        options.series.push({"label": entry});
    });
    //$.jqplot('chartdiv', [db0, db1, db2, db3, db4, db5, db6, db7, db8, db9, db10, db11], options);
    var graph1 = $.jqplot('chartdiv', db, options);

</SCRIPT>
<?






}




//////////////////////////////////////////////////////////////////////////////////////
//// TASK Functions
//////////////////////////////////////////////////////////////////////////////////////

if($task == ""){
    displayperformance();
}

?>
</BODY>
</HTML>

