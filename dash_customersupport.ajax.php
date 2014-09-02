<?
if($graph == 1){
    Global $EntityColumn, $EntityValue;  
    
    $y = date('Y');
    $data = array();
    
    for($k=0; $k < 3; $k++){
    
        $m = 1;
        $dataset = array();
    
        for($i=0; $i < 12; $i++){
    
            if($m <= 9) $m = "0" . $m;
        
            $sql = "SELECT COUNT(STID) AS Count ";
            $sql .= "FROM SupportTickets ";
            $sql .= "WHERE CompanyID = 1000 ";
            $sql .= "AND CreatedDT >= '$y-$m-01' ";
            $sql .= "AND CreatedDT <= '$y-$m-31' ";
            //error_log("SQL: $sql");
            $rs = mysql_query($sql);
            if($rs) $rsc = mysql_num_rows($rs);
            $count = mysql_result($rs, 0, "Count");
            if(empty($count)) $count = 0;
                                            
            //$labels[] = $month;
            $dataset[] = intval($count);
                 
            $m++;
        }
                    
        $data[] = $dataset;
        
        $y = $y - 01;
    
    }
        
    echo json_encode($data);
    exit;
}

if($graph == 2){
    Global $EntityColumn, $EntityValue;
    Global $colors;
    
    $m = date('m');
    $y = date('Y');
    $data = array();
    $dataset = array();
    
    $sql = "SELECT COUNT(STID) AS Count, FirstName ";
    $sql .= "FROM SupportTickets st, Users u ";
    $sql .= "WHERE st.CompanyID = 1000 ";
    $sql .= "AND CreatedDT >= '$y-$m-01' ";
    $sql .= "AND CreatedDT <= '$y-$m-31' ";
    $sql .= "AND u.UserID = OwnerID ";
    $sql .= "GROUP BY OwnerID";
    //error_log("SQL: $sql");
    $rs = mysql_query($sql);
    if($rs) $rsc = mysql_num_rows($rs);
    for($i=0; $i < $rsc; $i++){
        $fname = mysql_result($rs, $i, "FirstName");
        $count = mysql_result($rs, $i, "Count");
        if(empty($count)) $count = 0;
        $dataset = array();
            
        $dataset[] = array("$fname", intval($count) );
        
        $data[] = $dataset;
    }
        
    echo json_encode($data);
    exit;
}


if($graph == 3){
    Global $EntityColumn, $EntityValue;
        
    $data = array();
    $y = date('Y');
            
    for($k=0; $k < 3; $k++){
        
        //$lc = date('n');
        $m = 1;
        $year = array();
        
        for($i=0; $i < 12; $i++){
        
            $month = array();
            if($m <= 9) $m = "0" . $m;
            
            $sql = "SELECT AVG(count) average ";
            $sql .= "FROM ( SELECT DATE(CreatedDT) date, COUNT(STID) count ";
            $sql .= "FROM SupportTickets ";
            $sql .= "WHERE $EntityColumn = $EntityValue ";
            $sql .= "AND CreatedDT >= '$y-$m-01' ";
            $sql .= "AND CreatedDT <= '$y-$m-31' ";
            $sql .= "GROUP BY date ) tmp; ";
            //error_log("SQL: $sql");
            $rs = mysql_query($sql);
            
            $average = mysql_result($rs, 0, "average");
            if(empty($average)) $average = 0;
                        
            $label = date("M", mktime(0,0,0,$m,1,0));
                        
            $month = array( $label, intval($average) );
            
            $year[] = $month;
            
            $m++;
     
        }
        
        $data[] = $year;
        
        $y = $y - 01;
    
    }
        
    echo json_encode($data);
    exit;
}

if($graph == 4){
    Global $EntityColumn, $EntityValue;  
    
    $data = array();
    $y = date('Y');
    $m = date('m');
    
    $sql = "SELECT COUNT(STID) count, st.CCID, cc.Name ";
    $sql .= "FROM SupportTickets st, ContactCompanies cc ";
    $sql .= "WHERE CreatedDT >= '$y-$m-01' ";
    $sql .= "AND CreatedDT <= '$y-$m-31' ";
    $sql .= "AND st.CCID = cc.CCID ";
    $sql .= "GROUP BY CCID ";
    $sql .= "ORDER BY count ";
    $sql .= "DESC LIMIT 10";
    //error_log("SQL: $sql");
    $rs = mysql_query($sql);
    if($rs) $rsc = mysql_num_rows($rs);
    for($i=0; $i< $rsc; $i++){
        $name = mysql_result($rs, $i, "Name");
        $count = mysql_result($rs, $i, "count");
        if(empty($count)) $count = 0;
        
        $dataset = array();
        $dataset[] = array("$name", intval($count) );
        
        $data[] = $dataset;
    }
                        
    echo json_encode($data);
    exit;
}



?>