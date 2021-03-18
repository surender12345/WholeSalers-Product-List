
          <script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
          <script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css" type="text/css" media="all">
          <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css" type="text/css" media="all">
          
          <table id="wholesale_details" class="table table-striped table-bordered">
          <thead>
          <tr>
          <th colspan="3">Woocommerce</th>
          <th colspan="2">Carbomatgroup</th>
          <th>Own price</th>
          </tr>
          <tr>
          <th>Title</th>
          <th>Sku</th>
          <th>Ean</th>
          <th>Moq</th>
          <th>Netto Price</th>
          <th>Sale Price</th>
          <th></th>

          </tr>
          </thead>
          <tbody>
          <?php

          $args = array(
          'post_type'=>'product',
           'posts_per_page' => 1500
          );

          $loop = new WP_Query( $args );
          while ( $loop->have_posts() ) : $loop->the_post();
          global $wpdb, $product;
            
          $Woo_product_name =    $product->name;
          $Woo_product_id =  $product->id;
          $Woo_product_sku = $product->sku;
          $Woo_product_price =$product->price;
          $woo_netto_price =get_post_meta($product->id, "_custom_product_nettoprice", true);
          $woo_moq_quanity =get_post_meta($product->id, "_minimum_allowed_quantity", true);
          
          $argss = array (
          'post_type' => 'wholesale-product',
          'meta_query' => array(
          array(
          'key' => '_carbomat_product_sku',
          'value' => $Woo_product_sku,
          'compare' => 'LIKE'

          )
          )
          );

          $loopss = new WP_Query( $argss );
          $p_id = $loopss->post->ID;
          $carbomat_product_sku = get_post_meta($p_id, "_carbomat_product_sku", true);
          $carbomat_product_moq = get_post_meta($p_id, "_carbomat_product_moq", true);
          $carbomat_product_netto_price = get_post_meta($p_id, "_carbomat_product_netto_price", true);
          $Woo_product_post_author= $product->post_author;
          $product_ean = get_post_meta($Woo_product_id, "_wpm_gtin_code", true);

          $Woo_product_stock = get_post_meta($Woo_product_id, "_stock", true);

           if (!empty($Woo_product_stock)){
               $woo_pro = $Woo_product_stock;
           }else{
                 $woo_pro =0;
           }
               $total_stock=$woo_pro+$wholesale_stock;
          if($Woo_product_sku == $wholesale_sku){
                $woo_stock = $wholesale_stock;  
                $wholesale_product_prices= $wholesale_price;
          }else{
                 $woo_stock=0;
                 $wholesale_product_prices='';
          }
           ?>

          <tr>
          <td><?php echo $Woo_product_name ?></td>
          <td><?php echo $Woo_product_sku ?></td>
          <td><?php echo $product_ean ?></td>
          <td><?php echo $woo_moq_quanity ?></td>
          <td><?php echo $woo_netto_price ?></td>
          <td><?php echo $Woo_product_price ?></td>
          </tr>
          <?php
          endwhile; wp_reset_postdata();
          ?>
          </tbody>
        </table>
    <!-- <script type="text/javascript">
      $(document).ready(function () {
        $('#wholesale_details').dataTable({
        });
      });
    </script> -->