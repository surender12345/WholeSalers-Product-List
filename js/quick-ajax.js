function setdefautval($id,$this){

var value;
        if( jQuery($this ).attr( 'type' ) === 'checkbox' ) {
            value = jQuery($this).is( ':checked' ) ? 1: 0;
        }else
        {
            value = jquery($this).val();
        }
       // alert(value);

    
    var xp = value;
   jQuery.ajax({
    type: 'POST',
    url: '/wp-admin/admin-ajax.php',
    dataType: 'json',
    data: {
      action: 'setdefaut_value',
      id: $id,
      status:xp
    },
    success: function(res) {
    // console.log(res);
   
    }
})
  }
  jQuery(document).ready(function(){
        jQuery(".dSuggests").on("change", function() {
     var our_stock = jQuery(this).val();
 //jQuery(this).next().toggle();
    // alert(our_stock);
      var price = jQuery(this).parent().parent().find('input[name=\'woostock\']').val();  
     //alert(price);
     var total=parseFloat(our_stock)+parseFloat(price);
     //alert(total);
    // jQuery(".total_stocks").html(total);
     jQuery(this).parent().next('td').html(total);

    
});
  });



 //          jQuery(".dSuggests").on("change", function() {
 //     var our_stock = jQuery(this).val();
 // //jQuery(this).next().toggle();
 //    // alert(our_stock);
 //      var price = jQuery(this).parent().parent().find('input[name=\'woostock\']').val();  
 //     //alert(price);
 //     var total=parseFloat(our_stock)+parseFloat(price);
 //     //alert(total);
 //    // jQuery(".total_stocks").html(total);
 //     jQuery(this).parent().next('td').html(total);
 //     jQuery(this).parent().next("td").next('input[name=\'total_skt\']').val(total);
 //        var wo_product_id=jQuery('.wo_product_id').val();
 //        var wholesale_product_id= jQuery('.wholesale_product_id').val();
 //        //var total_skt = jQuery('.total_skt').val();
 //                   // var total_stks=totals
 //                   jQuery.ajax({
 //              type: 'POST',
 //              url: '/wp-admin/admin-ajax.php',
 //              dataType: 'json',
 //              data: {
 //                action: 'update_stock_value',
 //                wo_product_id: wo_product_id,
 //                wholesale_product_id:wholesale_product_id,
 //                total_skt:total_skt
 //              },
 //              success: function(res) {
 //               //console.log(res);
 //              }
 //          })
 //  });
       
  

        
        
       