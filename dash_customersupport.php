<?
require_once("secure.inc");
require_once("common.inc");
require_once("users.inc");
require_once("entity.inc");
require_once("tab.inc");
require_once("crumb.inc");
require_once("dash_customersupport.ajax.php");
require_once("companies.inc");
require_once("projects.inc");

require_once("script_begin.inc");

require_once("header_open.inc");
?>

<link rel="stylesheet" type="text/css" href="jqPlot/jquery.jqplot.css" />
<!--[if IE]><script language="javascript" type="text/javascript" src="jqPlot/excanvas.js"></script><![endif]-->
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="jqPlot/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="jqPlot/plugins/jqplot.logAxisRenderer.js"></script>
<script type="text/javascript" src="jqPlot/plugins/jqplot.canvasTextRenderer.min.js"></script>
<script type="text/javascript" src="jqPlot/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
<script type="text/javascript" src="jqPlot/plugins/jqplot.barRenderer.min.js"></script>
<script type="text/javascript" src="jqPlot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
<script type="text/javascript" src="jqPlot/plugins/jqplot.pointLabels.min.js"></script>
<script type="text/javascript" src="jqPlot/plugins/jqplot.dateAxisRenderer.min.js"></script>
<script type="text/javascript" src="jqPlot/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
<script type="text/javascript" src="jqPlot/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="jqPlot/plugins/jqplot.cursor.min.js"></script>


<STYLE>
    .leftside {
        float:left;
        min-width:45%;
        min-height:45%;
        margin-left:2%;
        margin-right:2%;
        margin-bottom: 10px;
    }
    
    .rightside {
        float:left;
        min-width:45%;
        min-height:45%;
        margin-bottom: 10px;
    }
</STYLE>

<?
require_once("header_close.inc");

//////////////////////////////////////////////////////////////////////////////
// Display Department User list 
//////////////////////////////////////////////////////////////////////////////
function displaydashboard()
{

    Global $PHP_SELF;
   	Global $EntityColumn, $EntityValue;
    Global $titlecolor;
    Global $titlefont;
    Global $titlefontsize;
    Global $CoService, $SuperUser;
    Global $userid;

?>
<SCRIPT type="text/javascript"> 
</SCRIPT>
<?   
    $cr = new CrumbObj();
    $cr->crumb_array = Array('Customer Suppport', 'Dashboard');
    $cr->Display();

    $tab = 0;
    $t = new TabObj2();
    $t->menu_title_array = Array("Graphs");
    $t->menu_link_array = Array("" );
    $t->selected = $tab;
    $t->fontsize = 2;
    $t->width = 98;
    $t->tabmsg = "";
    $t->DisplayTop();

    Global $MainHeight;
    if($MainHeight) {
        $h = $MainHeight - 110 . "px";
        print("<DIV class='tab-content' style='width:100%;height:$h;'>\n");
    }

    print("<div class='leftside'>");
    //print("<div class='title'><font face=$titlefont size=1><B>Total Tickets per Month</B></font></div>");
    print("<div id='placeholder1' style='float:left;width:100%;height:100%;'></div>");
    print("</div>");
    
    print("<div class='rightside'>");
    //print("<div class='title'><font face=$titlefont size=1><B>Total Tickets by Employee</B></font></div>");
    print("<div id='placeholder2' style='float:left;width:100%;height:100%;'></div>");
    print("</div>");
    
    print("<div class='clear'></div>");
    
    print("<div class='leftside'>");
    //print("<div class='title'><font face=$titlefont size=1><B>Average Tickets Per Day</B></font></div>");
    print("<div id='placeholder3' style='float:left;width:100%;height:100%;'></div>");
    print("</div>");
    
    print("<div class='rightside'>");
    //print("<div class='title'><font face=$titlefont size=1><B>TBD</B></font></div>");
    print("<div id='placeholder4' style='float:left;width:100%;height:100%;'></div>");
    print("</div>");
    
    if($MainHeight) print("</DIV>\n");

}

//////////////////////////////////////////////////////////////////////////////
// TASK functions 
//////////////////////////////////////////////////////////////////////////////
if($task == "")
{
    displaydashboard();
}

?>

<SCRIPT type="text/javascript" src=""></SCRIPT>
<SCRIPT type="text/javascript">
    $(document).ready(function(){        
            
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "July", "Aug", "Sept", "Oct", "Nov", "Dec"];
            
        var year3 = new Date().getFullYear();
        var year2 = year3 - 1;
        var year1 = year2 - 1;
        
        var options1 = {
            title: "Total Tickets per Month",
            axesDefaults: {
                labelRenderer: $.jqplot.CanvasAxisLabelRenderer
            },
            series: [
                { label: year3 },
                { label: year2 },
                { label: year1 }     

            ],
            legend: {
                show: true,
                placement: 'outsideGrid'
            },
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    ticks: months,
                    tickOptions:{
                        formatString: '%'
                    }
                },
                yaxis: {
                    pad: 0
                }
            }
        };
        
        var options2 = {
            title: "Tickets by Employee",
            seriesDefaults: {
                renderer:$.jqplot.BarRenderer,
                shadowAngle: 135,
                rendererOptions: {
                    barMargin: 30,
                    highlightMouseDown: false,
                    fillToZero: true,
                }
            },
            axesDefaults: {
                labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                tickOptions: {
                    fontFamily: 'Verdana',
                    fontSize: '8pt',
                }
            },
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    tickOptions: {
                        angle: -30
                    }
                }
            }
        };
        
        var options3 = {
            title: "Average Ticket Count by Month",
            seriesDefaults: {
                renderer:$.jqplot.BarRenderer,
                shadowAngle: 135,
                rendererOptions: {
                    barMargin: 30,
                    highlightMouseDown: false   
                }
            },
            axesDefaults: {
                labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                tickOptions: {
                    fontFamily: 'Verdana',
                    fontSize: '8pt',
                }
            },
            series: [
                { label: year3 },
                { label: year2 },
                { label: year1 }     

            ],
            legend: {
                show: true,
                placement: 'outsideGrid'
            },
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                }
            }
        };
        
        var options4 = {
            title: "Most Tickets By Entity Per Month",
            seriesDefaults:{
                renderer:$.jqplot.BarRenderer,
                rendererOptions: {
                    barPadding: 0,
                    barMargin: 0,
                    fillToZero: true
                }
            },
            axesDefaults: {
                labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                tickOptions: {
                    fontFamily: 'Verdana',
                    fontSize: '8pt',
                }
            },
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                }
            }
        };
                
        $.getJSON("dash_customersupport.php?graph=1", function(data){
            //console.log(data);
            var graph1 = $.jqplot('placeholder1', data, options1);
        });
        
        $.getJSON("dash_customersupport.php?graph=2", function(data){
            //console.log(data);
            var graph2 = $.jqplot('placeholder2', data, options2);
        });
        
        $.getJSON("dash_customersupport.php?graph=3", function(data){
            //console.log(data);
            var graph3 = $.jqplot('placeholder3', data, options3);
        });
        
        $.getJSON("dash_customersupport.php?graph=4", function(data){
            //console.log(data);
            var graph4 = $.jqplot('placeholder4', data, options4);
        });
        
    });
</SCRIPT>
</HTML>
</BODY>
 
<?
require("script_end.inc");
?>
