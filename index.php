<?
require_once("servers.ajax.php");
require_once("passwords.inc");
?>

<?

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

$valid_passwords = array (AUTH_UN => AUTH_PW);
$valid_users = array_keys($valid_passwords);

$validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);

if (!$validated) {
  header('WWW-Authenticate: Basic realm="Authorization required"');
  header('HTTP/1.0 401 Unauthorized');
  die ("Not authorized");
}

?>

<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>Site Monitor</TITLE>
<META http-equiv="X-UA-Compatible" content="IE=9">
<META charset="UTF-8">

<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">

<META NAME='SLS' CONTENT='Dashboard' >
<link rel="shortcut icon" href="/images/sitemonitor_fav.ico">
<LINK rel='stylesheet' type='text/css' href='../../jqPlot/jquery.jqplot.css' />

<SCRIPT src="jqPlot/jquery.js"></SCRIPT>
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
<SCRIPT src="jqPlot/plugins/jqplot.enhancedLegendRenderer.min.js"></SCRIPT>

<STYLE>
@media (max-width: 1000px){
  .graphtitle {
    display: none;
  }
  
  .chart{
    height: 0px;
  }
}
  
select {
  padding: 5px;  
}

body {
  overflow:auto;
  margin: 0px;
  background-image: url("/images/background.jpg");
  background-size: 100%;
  background-height: 100%;
  background-repeat: no-repeat;
}

.chart{
  height: 0px;
  z-index: -200;
}

.container{
  width: 90%;
  margin: 0 auto;
  padding-top: 100px;
}

.jqplot-yaxis-label{
  text-align: center;
  padding-right: 10px;
}

.chartselect{
  text-align: right;
  height: 10px;
  margin-right: 9px;
  z-index: 100 !important;
}

.selectbox{
  float: right;
}

.plot {
  margin-bottom: 10px;
  z-index: -100 !important;
}

.graphtitle{
  font-family: Arial;
  font-weight: bold;
  font-size: 38px;
  margin: 0px;
  margin-left: 50px;
  color: #03602f;
}

.topleftcontainer{
  position: fixed;
  top: 20px;
  z-index: 100;
  width: 230px;
}

.sycamorelogo{
  position: fixed;
  top: 15px;
  right: 5%;
  z-index: 100;
}

.sidebarbuttondiv{
  width: 50%;
  float: left;
}

.hourselect{
  width: 50%;
  float: left;
}

.containersidebar{
  width: 75%;
  float: left;
}

.sidebar{
  margin-left: 5%;
  width: 20%;
  float: left;
  background-color: #4cb33f;
  height: 100%;
}

.sidebarheader {
  margin-left: 15px;
  margin-right: 15px;
  font-family: arial;
  color: white;
  font-size: 20px;
}

.sidebartext {
  margin-left: 15px;
  margin-right: 15px;
  font-family: arial;
  color: white;
  font-size: 16px;
}

#top{
  margin-top: 15px;
  margin-bottom: 10px;
  text-align: center;
  width: 100%;
  position: fixed;
  z-index: 50;
}

#topbar {
  height: 75px;
  width: 100%;
  top: 0px;
  background-color: white;
  border-bottom: 1px solid #d5d5d5;
  position: fixed;
}

#sidebarbutton{
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  margin-top: 1px;
  font-family: arial;
  color: white;
  background-color: #4cb13e;
  padding: 8px;
  border-radius: 3px;
  text-align: center;
  cursor: pointer;
}

#sidebarbutton:hover {
  background-color: #03602f;
}

#title{
  margin: 0px 0px 0px 0px;
  width: 160px;
}

#scalehoursdrop{
  padding: 5px;
  font-size: 16px;
}

#sidebarcontainerdiv {
  height: 10px;
}

</STYLE>

<SCRIPT>
  var sidebar = 1;
  sidebar = 1;
</SCRIPT>

</HEAD>

<BODY>

<?
function displayperformance(){
?>
  <DIV id='top'>
    <P class="graphtitle">Sycamore Site Monitor</P>
  </DIV>

  <DIV id="topbar"></DIV>
  
<DIV class="container">

<DIV class="topleftcontainer">
  <DIV class="hourselect">
    <SELECT id='scalehoursdrop' name='scalehoursdrop' onChange=changeXScale(this)>
        <OPTION SELECTED value=1>1 Hour</OPTION>
        <OPTION value=5>5 Hours</OPTION>
        <OPTION value=10>10 Hours</OPTION>
        <OPTION value=24>24 Hours</OPTION>
    </SELECT>
  </DIV>
  <DIV class="sidebarbuttondiv">
    <DIV onClick=doSidebar(); id="sidebarbutton">Sidebar</DIV>
  </DIV>
</DIV>

  <DIV class="sycamorelogo">
    <IMG src="/images/logo.png" id='title'>
  </DIV>

<!--<DIV id="sidebarcontainerdiv" class="containersidebar">-->
<DIV id="sidebarcontainerdiv" class="">
  
  <DIV id='charts' class='chart'>

    <DIV class="chartselect" id='db'>
    <SELECT class="selectbox" id='dbdrop' name='dbdrop' onChange=changeYAxisDB(this)>
        <OPTION value=1>1</OPTION>
        <OPTION value=2>2</OPTION>
        <OPTION SELECTED value=5>5</OPTION>
        <OPTION value=10>10</OPTION>
        <OPTION value=20>20</OPTION>
        <OPTION value=50>50</OPTION>
    </SELECT>
    </DIV>

    <DIV id='dbchart' class='plot'></DIV>

    <DIV class="chartselect" id='cloud'>
    <SELECT class="selectbox" id='clouddrop' name='clouddrop' onChange=changeYAxisCloud(this)>
        <OPTION value=1>1</OPTION>
        <OPTION value=2>2</OPTION>
        <OPTION SELECTED value=5>5</OPTION>
        <OPTION value=10>10</OPTION>
        <OPTION value=20>20</OPTION>
        <OPTION value=50>50</OPTION>
    </SELECT> 
    </DIV>

    <DIV id='cloudchart' class='plot'></DIV>
  
    <DIV class="chartselect" id='user'>
    <SELECT class="selectbox" id='userdrop' name='userdrop' onChange=changeYAxisUser(this)>
        <OPTION value=500>500</OPTION>
        <OPTION value=1000>1000</OPTION>
        <OPTION value=1500>1500</OPTION>
        <OPTION value=2000>2000</OPTION>
        <OPTION value=3000>3000</OPTION>
        <OPTION value=4000>4000</OPTION>
        <OPTION SELECTED value=5000>5000</OPTION>
        <OPTION value=10000>10000</OPTION>
    </SELECT> 
    </DIV>

    <DIV id='userchart' class='plot'></DIV>
  
  </DIV>
  
</DIV>

  <DIV id="sidebar" class="sidebar" style="display: none;">
    <p class="sidebarheader">Information</p>
    <p class="sidebartext">
      This is Sycamore Site Monitor built for Sycamore Education. This is designed to be a real time monitor of our various servers, both database servers and cloud servers, as well as a monitor of our current users.
    </p>

    <p class="sidebartext">
      What is monitored by this can be changed, but currently it is set up to benefit the developers at Sycamore Education in allowing them to see the current state of the servers, as well as the history of the servers.
    </p>

    <p class="sidebartext">
      The variable inputs of this monitor are as follows. In the top left corner you will see an hour variable. This changes the amount of time represented by the graphs. This variable input applies to all three of the graphs shown.
      On the top right of all three of the graphs you will see another variable input. This input effects the vertical variable range of the applicable graph. For instance if one would want to view current users over time up to the 10,000 range you could select 10,000 from the list of options. This will change the Current Users graph Users field from 5,000 to 10,000.
    </p>
  </DIV>
</DIV>
<?
}

//////////////////////////////////////////////////////////////////////////////////////
//// TASK Functions
//////////////////////////////////////////////////////////////////////////////////////

if($task == "")
{
  displayperformance();
}
?>


<SCRIPT>

function doPageRefresh() {
    if (typeof dbplot !== 'undefined') {
        dbplot.destroy();
    }
    if (typeof cloudplot !== 'undefined') {
        cloudplot.destroy();
    }
    if (typeof userplot !== 'undefined') {
        userplot.destroy();
    }
    drawplots(xscale, 'all');
    
    //window.location = "index.php?plot=all&xscale="+xscale;
}
function doSidebar(){
  if (sidebar == 0) {
    $("#sidebarcontainerdiv").removeClass("containersidebar");
    $("#sidebar").hide();
    sidebar = 1;
    dbplot.replot();
    cloudplot.replot();
    userplot.replot();
  }else{
    $("#sidebarcontainerdiv").addClass("containersidebar");
    $("#sidebar").show();
    sidebar = 0;
    dbplot.replot();
    cloudplot.replot();
    userplot.replot();
  }
}

function changeYAxisDB(x){
    dboptions.axes.yaxis.max = x.value;
    if (typeof dbplot !== 'undefined') {
        dbplot.destroy();
    }
    dbplot = $.jqplot('dbchart', dbarray, dboptions);
}
function changeYAxisCloud(x){
    cloudoptions.axes.yaxis.max = x.value;
    if (typeof cloudplot !== 'undefined') {
        cloudplot.destroy();
    }
    cloudplot = $.jqplot('cloudchart', cloudarray, cloudoptions);

}
function changeYAxisUser(x){
    useroptions.axes.yaxis.max = x.value;
    if (typeof userplot !== 'undefined') {
        userplot.destroy();
    }
    userplot = $.jqplot('userchart', [userarray], useroptions);
}
function deepObjCopy (dupeObj) {
    var retObj = new Object();
    if (typeof(dupeObj) == 'object') {
        if (typeof(dupeObj.length) != 'undefined')
            var retObj = new Array();
        for (var objInd in dupeObj) {   
            if (typeof(dupeObj[objInd]) == 'object') {
                retObj[objInd] = deepObjCopy(dupeObj[objInd]);
            } else if (typeof(dupeObj[objInd]) == 'string') {
                retObj[objInd] = dupeObj[objInd];
            } else if (typeof(dupeObj[objInd]) == 'number') {
                retObj[objInd] = dupeObj[objInd];
            } else if (typeof(dupeObj[objInd]) == 'boolean') {
                ((dupeObj[objInd] == true) ? retObj[objInd] = true : retObj[objInd] = false);
            }
        }
    }
    return retObj;
}
function changeXScale(x){
    xscale = x.value;
    clearInterval(userintervalID);
    clearInterval(dbintervalID);
    var updateint = 30000;
    if(xscale==1) updateint = 30000;
    else if(xscale>1) updateint = (xscale * 60000);
    dbintervalID = setInterval(doUpdateDB, updateint);
    userintervalID = setInterval(doUpdateUser, updateint);
    if (typeof dbplot !== 'undefined') {
        dbplot.destroy();
    }
    if (typeof cloudplot !== 'undefined') {
        cloudplot.destroy();
    }
    if (typeof userplot !== 'undefined') {
        userplot.destroy();
    }
    drawplots(xscale, 'all');
    //alert(xscale);
}

function buildtickarray(start, scale){
    var tickarray = [];
    var count = 0;
    var tzstart = start;
    // get local tz
    var offset = new Date().getTimezoneOffset();
    // convert to seconds
    offset *= 60;
    // convert to local unix timestamp
    tzstart -= offset;
    // eliminate extra seconds 
    var seconds=tzstart-tzstart%60;
    // get total minutes
    var minutes = seconds/60;
    // get extra minutes
    var extraminutes = minutes%60;
    // convert to hours
    var hours = Math.floor(minutes/60);
    // extra hours 
    var extrahours = hours%24;
    //alert(extrahours);

    var numticks = ((scale%5==0) ? 6 : 7);
    //alert(extrahours);
    for(var i=0;i<numticks;i++){
        // increment timestamp
        var newtime=start-(600*i*scale);
        var newhour = extrahours;
        var newminute = extraminutes;
        //alert(newminute);
        // increment tick minutes and hours based on X scale
        if(scale==1) {
            newminute=extraminutes-(10*i*scale);
            if(newminute < 0) {
                newminute+=60;
                newhour-=1;
            }
        }else if(scale==5 || scale==10){
            //alert(newhour);
            newhour = newhour-(i*scale/5);
        }else if(scale==24){
            newhour = newhour-4*i;
        }
        // account for negative values
        if(newhour==24) newhour=0;
        if(newhour<0) newhour+=24;
        // fix single digit minutes
        newminute = ("0" + newminute).slice(-2);
        // format tick
        var newtick=newhour+":"+newminute;
        tickarray[i] = [newtime,newtick];
    }
                    
    tickarray.reverse();
    return tickarray;
}

function drawplots(xscale, plot){
    if(plot == 'db' || plot == 'all'){
        //alert(plot);
        $.getJSON("index.php?plot=db&xscale="+xscale,function(dbresults){
            //alert(xscale);
            var e = document.getElementById("dbdrop");
            var yscale = e.options[e.selectedIndex].value;

            currentdbresults = deepObjCopy(dbresults);
            dbarray = currentdbresults[0]; 
            //alert(dbarray[0][0][0]);
            //alert(dbarray[0][59][0]);
            //alert(dbarray.length);
            dbnames = currentdbresults[1]; 
            dbtickarray = buildtickarray(dbarray[0][60][0],xscale);
            //alert(dbtickarray);

            
            dboptions = {
                textColor:'#AAAAAA',
                title: {
                    text: 'Database Servers',
                    textAlign: 'center',
                },
                grid: {
                    //background: chartbgcolor,
                },    
                axes: {
                    xaxis: {
                        //label: 'Time',
                        renderer:$.jqplot.DateAxisRenderer,
                        //tickOptions:{formatString: '%H:%M'},
                        //tickInterval: intervalx,
                        //min: minx,
                        //max: maxx,
                        ticks: dbtickarray,
                    },
                    yaxis: {
                        label: 'Server Load',
                        tickOptions:{formatString: '%#.1f'},
                        min: 0,
                        max: yscale,
                        numberTicks: 6,
                        //ticks: [0,10,20,30,40,50,60,70,80,90,100]
                    }
                },
                legend: {
                    renderer: $.jqplot.EnhancedLegendRenderer,
                    show: true,
                    //placement: 'outsideGrid',
                    location: 'nw',
                    rendererOptions: {
                        numberRows: 1,
                        seriesToggle: 1,
                        disableIEFading: true,
                    },
                }, 
                highlighter: {
                    show: true,
                    tooltipLocation:'n',
                    tooltipOffset:20,
                    bringSeriesToFront: true,
                    tooltipContentEditor: function(str, seriesIndex, pointIndex, plot){
                        return plot.series[seriesIndex]["label"] + ", " + plot.data[seriesIndex][pointIndex][1];
                    } 
                },
                seriesDefaults: {
                    lineWidth: 1,
                    showMarker: false
                }
            };
        
            dboptions.series = [];  

            dbnames.forEach(function(entry){
                dboptions.series.push({
                    "label": entry,
                    "show": true,
                });
            });
            
            $('#dbchart').height(plotheight);
            //alert(titleleft);
            //$('.jqplot-title').css('left',titleleft);
            dbplot = $.jqplot('dbchart', dbarray, dboptions);
            $('.jqplot-title').css('left',titleleft);
        });
    }
    if(plot == 'cloud' || plot == 'all'){
        $.getJSON("index.php?plot=cloud&xscale="+xscale,function(cloudresults){
            var e = document.getElementById("clouddrop");
            var yscale = e.options[e.selectedIndex].value;

            currentcloudresults = deepObjCopy(cloudresults);
            cloudarray = currentcloudresults[0]; 
            cloudnames = currentcloudresults[1]; 
            //alert(xscale);
            //alert(cloudarray[0][60][0]);
            cloudtickarray = buildtickarray(cloudarray[0][60][0],xscale);


            cloudoptions = {
                title: {
                    text: 'Cloud Servers',
                    textAlign: 'center',
                },
                grid: {
                    //background: chartbgcolor,
                },    
                axes: {
                    xaxis: {
                        //label: 'Time',
                        renderer:$.jqplot.DateAxisRenderer,
                        //tickOptions:{formatString: '%H:%M'},
                        //tickInterval: intervalx,
                        //min: minx,
                        //max: maxx,
                        ticks: cloudtickarray,
                    },
                    yaxis: {
                        label: 'Server Load',
                        tickOptions:{formatString: '%#.1f'},
                        min: 0,
                        max: yscale,
                        numberTicks: 6,
                        //ticks: [0,10,20,30,40,50,60,70,80,90,100]
                    }
                },
                legend: {
                    renderer: $.jqplot.EnhancedLegendRenderer,
                    show: true,
                    //placement: 'outsideGrid',
                    location: 'nw',
                    rendererOptions: {
                        numberRows: 2,
                        seriesToggle: 1,
                        disableIEFading: true,
                    },
                }, 
                highlighter: {
                    show:true,
                    tooltipLocation:'n',
                    tooltipOffset:20,
                    bringSeriesToFront: true,
                    tooltipContentEditor: function(str, seriesIndex, pointIndex, plot){
                        return plot.series[seriesIndex]["label"] + ", " + plot.data[seriesIndex][pointIndex][1];
                    } 
                },
                seriesDefaults: {
                    lineWidth: 1,
                    showMarker: false
                }
            };
        
            cloudoptions.series = [];  

            cloudnames.forEach(function(entry){
                cloudoptions.series.push({
                    "label": entry,
                    "show": true,
                });
            });
            

            $('#cloudchart').height(plotheight);
            //$('.jqplot-title').css('left',titleleft);
            cloudplot = $.jqplot('cloudchart', cloudarray, cloudoptions);
            $('.jqplot-title').css('left',titleleft);

        });
    }
    if(plot == 'user' || plot == 'all'){
        $.getJSON("index.php?plot=user&xscale="+xscale,function(userresults){
            var e = document.getElementById("userdrop");
            var yscale = e.options[e.selectedIndex].value;

            userarray = deepObjCopy(userresults);
            //alert(userarray[0][0]);
            var start = userarray[6*xscale][0];
            usertickarray = buildtickarray(start, xscale); 

            //alert(plotheight);
            useroptions = {
                title: {
                    text: 'Current Users',
                    textAlign: 'center',
                },
                axes: {
                    xaxis: {
                        //label: 'Time',
                        renderer:$.jqplot.DateAxisRenderer,
                        //tickOptions:{formatString: '%H:%M'},
                        //tickInterval: intervalx,
                        //numberTicks:7,
                        //min: minx,
                        //max: maxx, 
                        ticks: usertickarray,
                    },
                    yaxis: {
                        label: 'Users',
                        tickOptions:{formatString: '%d'},
                        min: 0,
                        max: yscale,
                        numberTicks: 6,
                        //ticks: [0,10,20,30,40,50,60,70,80,90,100]
                    }
                },
                highlighter: {
                    show:true,
                    tooltipLocation:'n',
                    tooltipOffset:20,
                    tooltipContentEditor: function(str, seriesIndex, pointIndex, plot){
                        return plot.data[seriesIndex][pointIndex][1];
                    } 
                },
                seriesDefaults: {
                    lineWidth: 3,
                    showMarker: false,
                    fillAlpha:.3,
                    lineAlpha:1,
                    fillAndStroke:true,
                },
                series: [
                    {    
                        color:'#33CC00',
                        label: 'Current Users',
                        show: true,
                        fill: true,
                        rendererOptions: {
                            smooth:false,
                        }
                    }
                ]
            };
            

            $('#userchart').height(plotheight);
            //$('.jqplot-title').css('left',titleleft);
            userplot = $.jqplot('userchart', [userarray], useroptions);
            $('.jqplot-title').css('left',titleleft);
        });
    }
};    
function doUpdateDB() {      
    $.getJSON("index.php?plot=db&update=1",function(update){
         //alert(xscale);
        var change = false;
        //alert(update.length);
        for(i = 0; i < update.length; i++) {
            var newmin = update[i][0][0];
            var oldmin = dbarray[i][dbarray[i].length-1][0];
            //alert(newmin);
            //alert(oldmin);
            //alert(dbarray[i]);
            if(newmin-oldmin == xscale*60){
                dbarray[i].shift();
                dbarray[i].push(update[i][0]);
                change = true;
                //alert(dbarray[i].length);
            }
        };
         
        if (typeof dbplot !== 'undefined' && change == true) {
            dbplot.destroy();
            //alert("here");
        }
        if(change == true){
            dboptions.axes.xaxis.ticks = buildtickarray(update[0][0][0],xscale);
            //alert(update[0][0][0]);
            //alert(xscale);
            dbplot = $.jqplot ('dbchart', dbarray, dboptions);
        };
    });
    $.getJSON("index.php?plot=cloud&update=1",function(update){
        var change = false;
        for(i = 0; i < update.length; i++) {
            var newmin = update[i][0][0];
            var oldmin = cloudarray[i][cloudarray[i].length-1][0];
            if(newmin-oldmin == xscale *60){
                cloudarray[i].shift();
                cloudarray[i].push(update[i][0]);
                change = true;
            }
        };
         
        if (typeof cloudplot !== 'undefined' && change==true) {
            cloudplot.destroy();
        }

        if(change == true){
            cloudoptions.axes.xaxis.ticks = buildtickarray(update[0][0][0],xscale);
            var width = $('#cloudchart').width();
            titleleft = width * .03;
            //$('.jqplot-title').css('left',titleleft);
            cloudplot = $.jqplot ('cloudchart', cloudarray, cloudoptions);
            $('.jqplot-title').css('left',titleleft);
        };
    });
}
function doUpdateUser() {
            //alert(xscale);
    $.getJSON("index.php?plot=user&update=1",function(update){
        if(xscale > 10){
            var interval = 1440;
        }else{
            var interval = 600;
        }  
        var change = false;
        var newmin = update[0][0];
        var oldmin = userarray[userarray.length-1][0];
        //alert(newmin-oldmin);
        if(newmin-oldmin == interval){
            userarray.shift();
            userarray.push(update[0]);
            change = true;
            //alert(userarray);
        }
        if (typeof userplot !== 'undefined' && change==true) {
            userplot.destroy();
        }
        if(change == true){
            useroptions.axes.xaxis.ticks = buildtickarray(update[0][0],xscale);
            userplot = $.jqplot ('userchart', [userarray], useroptions);
        }; 
        
    });
}
//alert(xscale);
$(window).resize(function() {
    var height = Number($(window).height());
    var bodyheight = height * .9;
    bodyheight = bodyheight + 'px';
    plotheight = height * .240;
    var bgheight = height * .50;
    var dropheight1 = height * .064;
    var dropheight1 = dropheight1 + 'px';
    var dropheight2 = height *.055;
    var dropheight2 = dropheight2 + 'px';
    $('#dbchart').height(plotheight);
    $('#cloudchart').height(plotheight);
    $('#userchart').height(plotheight);
    $('#miscinfo').css('height',bgheight); 
    $('#chartbackground').css('height',bgheight); 
    //$('#db').css('padding-top',dropheight1); 
    //$('#cloud').css('padding-top',dropheight2); 
    //$('#user').css('padding-top',dropheight2); 
    dbplot.replot();
    cloudplot.replot();
    userplot.replot();
});
$(document).ready(function() {
    h = document.getElementById("scalehoursdrop");
    xscale = Number(h.options[h.selectedIndex].value);
    height = Number($(window).height());
    plotheight = height * .240;

    //alert(xscale);
    drawplots(xscale, 'all');
    var width = $('#dbchart').width();
    titleleft = width * .03;
    $('.jqplot-title').css('left',titleleft);
    var updateint = 30000;
    if(xscale==1) updateint = 30000;
    else if(xscale>1) updateint = (xscale * 60000);
    dbintervalID = setInterval(doUpdateDB, updateint);
    userintervalID = setInterval(doUpdateUser, updateint);
    //setTimeout(doPageRefresh(xscale), 3600000);
});

</SCRIPT>

</BODY>
</HTML>
