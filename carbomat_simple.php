<?php
 global $wbdp, $post; ?>

            <div id="stockbeheer_product_data" class="panel woocommerce_options_panel hidden">
                <form action="" method="post" name="simplecarbomat">
                    <div class="stockbeheer_content">
                    <table>
                        <tr>
                            <td></td>
                            <td><h3>CARBOMAT <span>GROUP</span></h3></td>
                        </tr>
                        <tr>
                            <td><?php _e('Inkoop prijs', 'carbomat_price_api');?></td> 
                                <?php  $price_carbomat = get_post_meta($post->ID, '_custom_product_nettoprice', true); ?>
                            <td><span><?php echo $price_carbomat; ?></span></td>
                        </tr>
                        <tr> 
                            <td><?php _e('Minimum bestelaantal', 'carbomat_price_api');?></td>
                               <?php  $moq_carbomat = get_post_meta($post->ID, 'minimum_allowed_quantity', true); ?> 
                            <td><span><?php echo $moq_carbomat; ?></span></td>
                        </tr>
                        <tr>
                            <td><?php _e('Inkoop + btw', 'carbomat_price_api');?></td>
                             <?php $inkoop_btw =  $price_carbomat * $moq_carbomat * 1.21 ; ?>
                            <td><span><?php echo $inkoop_btw; ?></span></td>
                        </tr>
                        <tr>
                            <td><?php _e('Verkoopprijs', 'carbomat_price_api');?></td>
                              <?php $sale_price = get_post_meta($post->ID, '_regular_price', true); ?>
                            <td><span><?php echo $sale_price; ?></span></td>
                        </tr>
                        <tr> 
                            <td><?php _e('Stock', 'carbomat_price_api');?></td>
                             <?php $stock_carbomat = get_post_meta($post->ID, '_stock', true); ?>
                            <td><span><?php echo $stock_carbomat; ?></span></td>
                        </tr>
                        <tr>  
                            <td><?php _e('Winst', 'carbomat_price_api');?></td>
                             <?php $profit = $sale_price - $inkoop_btw; 
                                     $newprofit = ( $profit / $sale_price ) * 100 ;
                                     $percentage = round($newprofit);
                                     $newprofit1 = number_format($profit, 3, '.', ''); ?>
                            <td><span><?php echo '€'. $newprofit1.      '  ( '. $percentage. '% )'; ?></span></td>
                        </tr>
                       <tr>
                            <td></td>
                            <td class="product-btn"><input type="button" value="Syncroniseer product" class="sync_single_product" data-id="<?php echo $post->ID; ?>">
                              <div class="loader"></div>
                            </td>
                        </tr>
                </table>
              </div>
            </form>
        </div>
 <style>
    .loader {
        display:    none; position: fixed; z-index: 1000; top: 0; left: 0; height:100%; width: 100%; background:rgba( 255, 255, 255, .8 ) url('http://i.stack.imgur.com/FhHRx.gif') 50% 50% no-repeat; }
    #stockbeheer_product_data .stockbeheer_content table { border-collapse: collapse; }
    #stockbeheer_product_data .stockbeheer_content table td{ border: 1px solid #f5f5f5; padding: 9px 25px 9px 41px; font-weight: 600; color: #000; font-size: 14px; }
    #stockbeheer_product_data .stockbeheer_content table td h3{ color: #28508d; font-size: 21px; margin: 0px; }
    #stockbeheer_product_data .stockbeheer_content table td h3 span{ color: #92cb18; }
    #stockbeheer_product_data .stockbeheer_content table tr:first-child td{ border-top: 0px; }
    #stockbeheer_product_data .stockbeheer_content{ padding: 25px; }
    #stockbeheer_product_data .stockbeheer_content table tr td:first-child{ border-left: 0px; padding-left: 0px; }
    #stockbeheer_product_data .stockbeheer_content table tr:last-child td{ border-bottom: 0px; }
    #stockbeheer_product_data .stockbeheer_content table tr td:last-child{ border-right: 0px; text-align: center; }
    #stockbeheer_product_data .sync_single_product{ background-color: #ffda00; color: #000; border-radius: 4px; padding: 7px 15px; border: 0px; margin-top: 12px!important; }
    #stockbeheer_product_data .sync_single_product:focus{ outline: none; }
 </style>
 <script>
	jQuery(document).on('click', '.sync_single_product', function() {
	var ajaxurl=	'<?php echo admin_url( 'admin-ajax.php' ); ?>';
    var id= jQuery(this).data('id');
	jQuery.ajax({       
		 	url: ajaxurl, 
		    type: 'POST',
			data:{ 
				action: 'sync_single_product',
				id:id
				},
				dataType:'html',
				beforeSend: function () {
           			jQuery(".loader").show();
        			},
				success: function( data ){
						jQuery(".loader").hide();
						if(data == 1)
						{
						alert('Product Sync Sucessfully');
						location.reload();
						}
						}
  				});

  });
 </script>