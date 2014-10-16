
function maxy(array,ticks){
    var maxy=0;
    var divisor=ticks-1;
    array.forEach(function(entry)){
        if(entry > maxy){
            maxy=Math.ceil(entry);
        }
    });
    maxy++;
    maxy+=(divisor-maxy%divisor);

    return maxy;  

}
