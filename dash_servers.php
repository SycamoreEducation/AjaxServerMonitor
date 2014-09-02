<?
require_once("dash_servers.ajax.php");
?>

<?
$valid_passwords = array ("sycamore" => "sycamore123");
$valid_users = array_keys($valid_passwords);

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

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
<SCRIPT src="jqPlot/plugins/jqplot.enhancedLegendRenderer.min.js"></SCRIPT>
<STYLE>
body {
    background: #EBEBEB; 
    overflow:auto;
}

.chart{
    float:left;
    width:77%;
    margin-left:1%;
    margin-bottom: 10px;
}
.plot{
    opacity:1;
    margin-left:1%;
    width:90%;
    float:left;
    position:relative;
    /*text-shadow: -1px 0 white, 0 1px white, 1px 0 white, 0 -1px white;*/
}
.divbg{
    background-color:#A9A9A9;
    border-radius:5px;
    opacity:0.6;
}
.jqplot-title{
    /*left:25px;*/
}
#container {
    position: relative;
}

#chartbackground, 
#charts {
    /*left:0;*/
    position:absolute;
    min-width:750px;
    margin-right:1%;
}
#chartbackground{
    /*z-index:-1;*/
}
#charts{
    /*z-index:2;*/
}
#miscinfo {
    position:relative;
    float:right;
    min-height:100%;
    width:20%;
    min-width:185px;
    margin-right:1%;
}
#top {
    height:100%;
    background-color:Green;
    border-radius:5px;
    margin-left:1%;
    margin-bottom:1%;
    margin-top:1%;
    margin-right:1%;
    opacity:0.8;
}
#title{
    text-align:center;
    margin-top:0;
    padding-top:1%;
    padding-bottom:1%;
    margin-bottom:0%;
    /*top: 50%;
    transform: translateY(-50%);*/
    font-family:Verdana;
    color:white;
    /*text-shadow: -1px 0 white, 0 1px white, 1px 0 white, 0 -1px white;*/
    
}
#scalehoursdrop{
    float:left;
    margin-left:2%;
    margin-top:1%;
    margin-bottom:1%;
    margin-right:1%;
    padding-bottom:0;
    padding-right:0;
    border-bottom:0;
    border-right:0;
    position:absolute;
}
#cloudchart,#userchart{
    margin-top:.8%;
}
#dbchart{
    margin-top:1.5%;    
}
#db{
    float:left;
}
#cloud,#user{
    float:left;
}   
    
</STYLE>
<SCRIPT>
$(document).ready(function(){
    var height = $(window).height()*.88;
    var cssheight = height + 'px';
    var dropheight1 = height * .064;
    var dropheight1 = dropheight1 + 'px';
    var dropheight2 = height *.055;
    var dropheight2 = dropheight2 + 'px';
    //alert(cssheight);
    $('#miscinfo').css('height',cssheight); 
    $('#chartbackground').css('height',cssheight); 
    $('#charts').css('height',cssheight); 
    $('#db').css('padding-top',dropheight1); 
    $('#cloud').css('padding-top',dropheight2); 
    $('#user').css('padding-top',dropheight2); 
});
</SCRIPT>
</HEAD>
<BODY style='margin: 0px 0px 0px 0px;'>
<?
function displayperformance(){
?>

<DIV id='top' >
<H1 id='title'>Sycamore Site Monitor</H1>
</DIV>

<DIV id='miscinfo' class='divbg'></DIV>
<DIV id='container'>
<SELECT id='scalehoursdrop' name='scalehoursdrop' style='z-index:1;'onChange=changeXScale(this)>
    <OPTION SELECTED value=1>1 hour</OPTION>
    <OPTION value=5>5 hours</OPTION>
    <OPTION value=10>10 hours</OPTION>
    <OPTION value=24>24 hours</OPTION>
</SELECT> <DIV id='chartbackground' class='chart divbg'></DIV>
<DIV id='charts' class='chart'>

<DIV id='dbchart' class='plot'></DIV>
<DIV id='db'>
<SELECT id='dbdrop' name='dbdrop'  onChange=changeYAxisDB(this)>
    <OPTION value=1>1</OPTION>
    <OPTION value=2>2</OPTION>
    <OPTION SELECTED value=5>5</OPTION>
    <OPTION value=10>10</OPTION>
    <OPTION value=20>20</OPTION>
    <OPTION value=50>50</OPTION>
</SELECT> 
</DIV>

<DIV id='cloudchart' class='plot'></DIV>
<DIV id='cloud'>
<SELECT id='clouddrop' name='clouddrop' onChange=changeYAxisCloud(this)>
    <OPTION value=1>1</OPTION>
    <OPTION value=2>2</OPTION>
    <OPTION SELECTED value=5>5</OPTION>
    <OPTION value=10>10</OPTION>
    <OPTION value=20>20</OPTION>
    <OPTION value=50>50</OPTION>
</SELECT> 
</DIV>

<DIV id='userchart' class='plot'></DIV>
<DIV id='user'>
<SELECT id='userdrop' name='userdrop' onChange=changeYAxisUser(this)>
    <OPTION value=500>500</OPTION>
    <OPTION value=1000>1000</OPTION>
    <OPTION value=1500>1500</OPTION>
    <OPTION value=2000>2000</OPTION>
    <OPTION value=3000>3000</OPTION>
    <OPTION value=4000>4000</OPTION>
    <OPTION SELECTED value=5000>5000</OPTION>
    <OPTION value=10000>10000</OPTION>
</SELECT> 
<!--</DIV>-->
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
    var updateint = Number(xscale * 30000);
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

function builddbtickarray(thearray, scale){
    var tickarray = [];
    if(scale%5==0){
        var filter = 12;
    }else{ 
        var filter = 10;
    }
    //alert(scale);
    //alert(filter);
    //alert(thearray.length);
    var count = 0;
    thearray.forEach(function(entry){
        if(thearray.indexOf(entry)%filter == 0){
            var d = new Date(entry[0] *1000);
            var hours = d.getHours();
            var minutes = d.getMinutes();
            hours = ((hours < 10) ? '0'+hours : hours);
            minutes = ((minutes < 10) ? '0'+minutes : minutes);
            var time = hours + ":" + minutes;
            tickarray.push([entry[0],time]);
        }
        count++;
    });
    //alert(count);
    return tickarray;
}
function buildusertickarray(thearray, scale){
    var tickarray = [];
    //alert(filter);
    if(scale%5==0){
        //alert(scale);
        filter = Number(scale)/5 + Number(scale);
    }else{ 
        filter = Number(scale);
    }
    //alert(filter);
    thearray.forEach(function(entry){
        if(thearray.indexOf(entry)%filter == 0){
            var d = new Date(entry[0]*1000);
            var hours = d.getHours();
            var minutes = d.getMinutes();
            hours = ((hours < 10) ? '0'+hours : hours);
            minutes = ((minutes < 10) ? '0'+minutes : minutes);
            var time = hours + ":" + minutes;
            tickarray.push([entry[0],time]);
        }
    });
    //alert(tickarray);
    return tickarray;
}
function checkdanger(data, threshold){
    var maxy = 0;
    var color;
    dbarray[0].forEach(function(entry){
        if(entry[1] > maxy){
            maxy = entry[1];
        }
    });   
    //alert(maxy);
    if(maxy > threshold){
        color =  '#FF6347';
        //chartlinecolor = ((maxy > 1) ? '#cfcfcf' : '#cfcfcf');
        alert("Server is over danger threshold!");
    }else{
        color = '#FFFDF6';
    }
    return color;
}

function drawplots(xscale, plot){
    if(plot == 'db' || plot == 'all'){
        //alert(plot);
        $.getJSON("dash_servers.php?plot=db&xscale="+xscale,function(dbresults){
            var e = document.getElementById("dbdrop");
            var yscale = e.options[e.selectedIndex].value;

            currentdbresults = deepObjCopy(dbresults);
            dbarray = currentdbresults[0]; 
            //alert(dbarray.length);
            dbnames = currentdbresults[1]; 
            dbtickarray = builddbtickarray(dbarray[0],xscale);
            //alert(dbtickarray);
            chartbgcolor = checkdanger(dbarray,10);

            
            dboptions = {
                textColor:'#AAAAAA',
                title: {
                    text: 'Database Servers',
                    textAlign: 'center',
                },
                grid: {
                    background: chartbgcolor,
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
        $.getJSON("dash_servers.php?plot=cloud&xscale="+xscale,function(cloudresults){
            var e = document.getElementById("clouddrop");
            var yscale = e.options[e.selectedIndex].value;

            currentcloudresults = deepObjCopy(cloudresults);
            cloudarray = currentcloudresults[0]; 
            cloudnames = currentcloudresults[1]; 
            cloudtickarray = builddbtickarray(cloudarray[0],xscale);

            chartbgcolor = checkdanger(dbarray,5);

            cloudoptions = {
                title: {
                    text: 'Cloud Servers',
                    textAlign: 'center',
                },
                grid: {
                    background: chartbgcolor,
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
                        numberRows: 1,
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
        $.getJSON("dash_servers.php?plot=user&xscale="+xscale,function(userresults){
            var e = document.getElementById("userdrop");
            var yscale = e.options[e.selectedIndex].value;

            userarray = deepObjCopy(userresults);
            usertickarray = buildusertickarray(userarray, xscale); 

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
    $.getJSON("dash_servers.php?plot=db&update=1",function(update){
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
            //alert(xscale);
            chartbgcolor = checkdanger(dbarray,5);
            dboptions.grid.background = chartbgcolor;
            dboptions.axes.xaxis.ticks = builddbtickarray(dbarray[0],xscale);
            dbplot = $.jqplot ('dbchart', dbarray, dboptions);
        };
    });
    $.getJSON("dash_servers.php?plot=cloud&update=1",function(update){
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
            chartbgcolor = checkdanger(dbarray,5);
            dboptions.grid.background = chartbgcolor;
            cloudoptions.axes.xaxis.ticks = builddbtickarray(cloudarray[0],xscale);
            var width = $('#dbchart').width();
            titleleft = width * .03;
            //$('.jqplot-title').css('left',titleleft);
            cloudplot = $.jqplot ('cloudchart', cloudarray, cloudoptions);
            $('.jqplot-title').css('left',titleleft);
        };
    });

}
function doUpdateUser() {
            //alert(xscale);
    $.getJSON("dash_servers.php?plot=user&update=1",function(update){
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
            useroptions.axes.xaxis.ticks = buildusertickarray(userarray,xscale);
            userplot = $.jqplot ('userchart', [userarray], useroptions);
        }; 
        
    });
}
//alert(xscale);
$(window).resize(function() {
    var height = Number($(window).height());
    plotheight = height * .274;
    var bgheight = height * .88;
    var dropheight1 = height * .064;
    var dropheight1 = dropheight1 + 'px';
    var dropheight2 = height *.055;
    var dropheight2 = dropheight2 + 'px';
    $('#dbchart').height(plotheight);
    $('#cloudchart').height(plotheight);
    $('#userchart').height(plotheight);
    $('#miscinfo').css('height',bgheight); 
    $('#chartbackground').css('height',bgheight); 
    $('#charts').css('height',bgheight); 
    $('#db').css('padding-top',dropheight1); 
    $('#cloud').css('padding-top',dropheight2); 
    $('#user').css('padding-top',dropheight2); 
    dbplot.replot();
    cloudplot.replot();
    userplot.replot();
});
$(document).ready(function() {
    h = document.getElementById("scalehoursdrop");
    xscale = Number(h.options[h.selectedIndex].value);
    height = Number($(window).height());
    plotheight = height * .274;


    //alert(xscale);
    drawplots(xscale, 'all');
    var width = $('#dbchart').width();
    titleleft = width * .03;
    $('.jqplot-title').css('left',titleleft);
    var updateint = Number(xscale * 30000);
    dbintervalID = setInterval(doUpdateDB, updateint);
    userintervalID = setInterval(doUpdateUser, updateint);
});



</SCRIPT>


</BODY>
</HTML>
