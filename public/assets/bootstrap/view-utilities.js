
/* Called while sorting */
function sortRecord(sort_by) {
   
   if(sort_by != $('#sort_by').val()){
       $('#sort_by').val(sort_by);
       $('#order_by').val('');
   }

   var countPerPage = $('#count_PerPage').val();
   
   var order_by = $('#order_by').val();
   if(order_by!=''){
        if(order_by=='ASC'){
            order_by='DESC'
        }else if(order_by=='DESC'){
            order_by='ASC';
        }
   }else{
      order_by = 'ASC'
   }
   $('#order_by').val(order_by);
   $('#countPerPage').val(countPerPage);
   fetchRecords(1);
}

/* Called while searching, using view form  */
function searchRecords(elm, obj, exval){
    if(($.trim(obj.value)!="" && exval!=$.trim(obj.value)) || ($.trim(obj.value)=="" && exval!=$.trim(obj.value))){
        $('#advSearch #'+elm).val(obj.value);
        fetchRecords(1);
    }
}

/* Used to reset the search form */
function clearRecords(){
    $(':input').not(':button, :submit, :reset, :hidden').val('');
    fetchRecords(1);
}

/* Fetches the data set  */
function fetchRecords(page, addVal){ 
    addVal = (typeof addVal === 'undefined') ? '' : addVal;
  
    $('#page').val(page);
    var frmaction = $('#advSearch').attr("action");
    var frmdata = $('#advSearch').serialize();
    if(addVal!==''){
        frmdata +=addVal;
    }
    //alert(frmdata);return false;
    $('#loader').css('display','block');
    $('#loader').removeClass('hidden');
    $.ajax
        ({
            type: "POST",
            url: frmaction,
            data: frmdata,
            success: function(resData)
            {
//alert(resData);
//                $(document).ajaxComplete(function(event, request, settings)
//                {
                    $('#loader').css('display','none');
                    $('#loader').addClass('hidden');
                    $("#paged-data-container").html('');
                    $("#paged-data-container").html(resData);
//                });
            }
        });
}

/* Called when check all & uncheck all is used */
function checkUncheckAll(checkedStatus, elmToChkUnchk){
    var obj = document.getElementsByName(elmToChkUnchk);
    len = obj.length;
    
    if(checkedStatus==true){
        for(i=0;i<len;i++){
            obj[i].checked=true;
        }
    }else if(checkedStatus==false){
        for(i=0;i<len;i++){
            obj[i].checked=false;
        }
    }
    
    toggleActionBlock(elmToChkUnchk);
    //implodeCheckedCheckbox(elmToChkUnchk, 'chkbx');
        
}

/* Gives comma seprated values */
function implodeCheckedCheckbox(elmChk, assignTxt){
    
   /* var chkedVal = $("#"+assignTxt).val();
    if(chkedVal!=''){
       chkedVal =  chkedVal.split(",");
    }//alert(chkedVal.length);*/
    
    var output = $.map($("input[name='"+elmChk+"']:checked"), function(n, i){
        return n.value;
       /* if(chkedVal!=''){//alert(chkedVal.length);
            for(i=0; i<chkedVal.length;i++){
                alert(chkedVal[i]);
                if(n.value == chkedVal[i]){
                    return n.value;
                }
            }
        }else{
            return n.value;
        }*/
    }).join(',');
    
    //$("#"+assignTxt).val(output);
    toggleActionBlock(elmChk);
}

/* Enable or disable action block */
function toggleActionBlock(elmChk){
    var obj = document.getElementsByName(elmChk);
    len = obj.length;
    j = 0;
    for(i=0;i<len;i++){
        if(obj[i].checked){
            j++;
            break;
        }
        
    }
    
    if(j>0){
        $('.actionBlock').css('display', 'block');
    }else{
        $('.actionBlock').css('display', 'none');
    }
}
//to get date picker
function datePicker(date)
{
    
}
/* Execute the action block */
function executeViewAction(page, url, addVal){
    addVal = (typeof addVal === 'undefined') ? '' : addVal;
    var frmdata = $('#viewSearch').serialize();
    frmdata += "&page="+page;
    if(addVal!==''){
        frmdata += addVal;
    }
        
    $('#ajax-load').css('display','block');
    $.ajax
        ({
            type: "POST",
            url: url,
            data: frmdata,
            success: function(msg)
            {

                $(document).ajaxComplete(function(event, request, settings)
                {
                    $('#ajax-load').css('display','none');
                    $("#paged-data-container").html(msg);
                });
            }
        });
}


$(document).ready(function()
{

$(".user_btn").click(function()
{
var X=$(this).attr('id');
if(X==1)
{
$(".submenu_user_bt").hide();
$(this).attr('id', '0');
}
else
{
$(".submenu_user_bt").show();
$(this).attr('id', '1');
}

});

//Mouse click on sub menu
$(".submenu_user_bt").mouseup(function()
{
return false
});

//Mouse click on my account link
$(".user_btn").mouseup(function()
{
return false
});


//Document Click
$(document).mouseup(function()
{
$(".submenu_user_bt").hide();
$(".user_btn").attr('id', '');
});
});


    
 
