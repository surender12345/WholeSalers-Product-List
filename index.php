        <?php
        /**
        * Plugin Name: WholeSale Product List
        * Description: This plugin is used to store Wholesalers data.
        * Version: 2.0
        **/

        /*
        * Creating a function to create our CPT
        */
        // header();

        function permanences_custom_post_type() {
            $labels = array(
                'name'                => __( 'WholeSaler Products' ),
                'singular_name'       => __( 'WholeSaler Products'),
                'menu_name'           => __( 'WholeSaler Products'),
                'parent_item_colon'   => __( 'Parent WholeSaler Products'),
                'all_items'           => __( 'All WholeSaler Products'),
                'view_item'           => __( 'View WholeSaler Products'),
                'add_new_item'        => __( 'Add New WholeSaler Products'),
                'add_new'             => __( 'Add New'),
                'edit_item'           => __( 'Edit WholeSaler Products'),
                'update_item'         => __( 'Update WholeSaler Products'),
                'search_items'        => __( 'Search WholeSaler Products'),
                'not_found'           => __( 'Not Found'),
                'not_found_in_trash'  => __( 'Not found in Trash')
            );
            $args = array(
                'label'               => __( 'wholeSaler-Products'),
                'description'         => __( 'wholeSaler-Products'),
                'labels'              => $labels,
                'supports'            => array( 'title', 'custom-fields', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions'),
                'public'              => true,
                'hierarchical'        => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => true,
                'show_in_admin_bar'   => true,
                'has_archive'         => true,
                'can_export'          => true,
                'exclude_from_search' => false,
                'yarpp_support'       => true,
                'taxonomies'          => array('job_listing_type'),
                'publicly_queryable'  => true,
                'capability_type'     => 'page'
        );
            register_post_type( 'wholesale-product', $args );
        }
        add_action( 'init', 'permanences_custom_post_type', 0 );

    
       
        add_action('admin_menu','my_menu_page');
        function my_menu_page(){
        $hook = add_submenu_page(
            'edit.php?post_type=wholesale-product',
            'Fund Settings', /*page title*/
            'All Stock List', /*menu title*/
            'manage_options', /*roles and capabiliyt needed*/
            'wholesale_stock_list',
            'return_vals' /*replace with your own function*/
        );
               add_submenu_page(
            'edit.php?post_type=wholesale-product',
            'Fund Settings', /*page title*/
            'Settings', /*menu title*/
            'manage_options', /*roles and capabiliyt needed*/
            'wnm_fund_set',
            'books_ref_page_callback' /*replace with your own function*/
        );
            $hook = add_menu_page( 'Form', 'Form', 'activate_plugins', 'my_list_form', 'my_render_list_page' );
         
            add_action( "load-wholesale-product_page_wholesale_stock_list", 'add_options' );
        }
          

/**
        * Display callback for the submenu page. */
        function books_ref_page_callback() { 
                      // include('setting.php');
                        
        }
         function return_vals(){
                    //  include('wholesaleslist.php');
          }

          //// Show post in order by date ////
         function wpse_81939_post_types_admin_order( $wp_query ) {
                      if (is_admin()) {

                        // Get the post type from the query
                        $post_type = $wp_query->query['post_type'];
                        if ( $post_type == 'wholesale-product') {
                          $wp_query->set('orderby', 'date');
                          $wp_query->set('order', 'DESC');
                        }
                      }
                    }

        add_filter('pre_get_posts', 'wpse_81939_post_types_admin_order');
 
///////////////get data by api using cronjob @ 2o clock ////////////////////////////// 

            function my_cron_schedule_2o_clock($schedules){

                            if(!isset($schedules["Daily"])){
                                $schedules["Daily"] = array(
                                    'interval' => 55800,
                                    'display' => __('Daily At Midnight'));
                            }

                            return $schedules;
                        }

            add_filter('cron_schedules','my_cron_schedule_2o_clock');

                        if (!wp_next_scheduled('carbomatgroup_hook_2_oclock')) {
                            wp_schedule_event( time(), 'Daily', 'carbomatgroup_hook_2_oclock' );
                        }

            add_action ( 'carbomatgroup_hook_2_oclock', 'carbomatgroup_functions_2_oclock' );

            function carbomatgroup_functions_2_oclock() {

                    global $wpdb , $post;
                    $curlss = curl_init();
                    $todaydate = date("Y-m-d", time()-86400);
                    curl_setopt_array($curlss, array(
                    CURLOPT_URL => "https://api.carbomatgroup.com/v1/articles?sinceModifDate=$todaydate&skip=0&take=500&data=price%2C%20stock",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS =>"",
                          CURLOPT_HTTPHEADER => array(
                            'Accept: application/json',
                            'Authorization: Basic '. base64_encode("RQyR4rMSNbw6ZsHPX46FX74CAKkz9AQ9GveTFQRmJTvgMyd23Sfaq25689RExGYn:")
                          ),
                        ));
                    $responseqq = curl_exec($curlss);
                    $arrre = json_decode($responseqq);
                    foreach ($arrre->data as $value) {

                        $str2 = substr($value->id, 3);
                        $new_sk_id = trim($str2);

                        $peon_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wpm_gtin_code' AND meta_value='%s' LIMIT 1",  $value->ean ) );
                         if($peon_id == "74817") {  unset($peon_id);  } 

                                    if($peon_id){
                                        update_post_meta( $peon_id, '_custom_product_nettoprice', $value->priceNetto );
                                         update_post_meta( $peon_id, 'minimum_allowed_quantity', $value->moq );
                                         update_post_meta( $peon_id, '_stock', $value->stock->available );
                                         update_post_meta($peon_id, '_manage_stock', 'yes');
                                         $stock_statu = 'instock';
                                         update_post_meta( $peon_id, '_stock_status', $stock_statu );
                                         update_post_meta( $peon_id, 'pricenetto_field',  $value->priceNetto );

                                         if($value->moq == '0' || $value->moq == ''){
                                             update_post_meta( $peon_id, '_custom_product_bolprice', $value->priceNetto );
                                             update_post_meta( $peon_id, 'pricebol_field',  $value->priceNetto );
                                         }
                                         else{
                                            $nettobolprice =  $value->priceNetto * $value->moq;
                                             update_post_meta( $peon_id, '_custom_product_bolprice', $nettobolprice );
                                             update_post_meta( $peon_id, 'pricebol_field',  $nettobolprice );
                                         }
                                        
                                        $regular_price = get_post_meta($peon_id, '_regular_price', true);
                                        $vat = 1.21;
                                        $pricevat = $value->priceNetto * $vat;
                                        if($pricevat > $regular_price){
                                            $status = 'draft';
                                            $post = array( 'ID' => $peon_id, 'post_status' => $status );
                                            wp_update_post($post);

                                        }
                                    }
                                    
                                   $pro_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $new_sk_id ) );

                                   if($pro_id){
                                         update_post_meta( $pro_id, '_custom_product_nettoprice', $value->priceNetto );
                                         update_post_meta( $pro_id, 'minimum_allowed_quantity', $value->moq );
                                         update_post_meta( $pro_id, '_stock', $value->stock->available );
                                         update_post_meta($pro_id, '_manage_stock', 'yes');
                                           $stock_statu = 'instock';
                                         update_post_meta( $pro_id, '_stock_status', $stock_statu );
                                         update_post_meta( $pro_id, 'pricenetto_field',  $value->priceNetto );

                                         if($value->moq == '0' || $value->moq == ''){
                                             update_post_meta( $pro_id, '_custom_product_bolprice', $value->priceNetto );
                                             update_post_meta( $pro_id, 'pricebol_field',  $value->priceNetto );
                                         }
                                         else{
                                            $nettobolprice =  $value->priceNetto * $value->moq;
                                            update_post_meta( $pro_id, '_custom_product_bolprice', $nettobolprice );
                                             update_post_meta( $pro_id, 'pricebol_field',  $nettobolprice );
                                         }
                                        
                                        $regular_price = get_post_meta($pro_id, '_regular_price', true);
                                        $vat = 1.21;
                                        $pricevat = $value->priceNetto * $vat;
                                        if($pricevat > $regular_price){
                                            $status = 'draft';
                                            $post = array( 'ID' => $pro_id, 'post_status' => $status );
                                            wp_update_post($post);

                                        }
                                    }

                                    $results = $wpdb->get_results( "SELECT post_title FROM $wpdb->posts WHERE id= '$pro_id' ");
                                   foreach ($results as $values) {

                                   $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s
                                                AND post_type = 'wholesale-product' AND post_status= 'publish'", $values->post_title);
                                            $wpdb->query( $query );

                                    if ( $wpdb->num_rows ) {

                                           $post_id = $wpdb->get_var( $query );
                                            update_post_meta( $post_id, '_carbomat_product_sku', $value->id);
                                            update_post_meta( $post_id, '_carbomat_product_stock_avail', $value->stock->available);
                                            update_post_meta( $post_id, '_carbomat_product_netto_price', $value->priceNetto);
                                            update_post_meta( $post_id, '_carbomat_product_moq',$value->moq);
                                  }else{
                                       
                                           $post_id = wp_insert_post(array(
                                            'post_status' => 'publish',
                                            'post_type' => 'wholesale-product',
                                            'post_title' => $values->post_title,
                                            'post_content' => '',
                                            'post_author' => ''
                                            ));

                                        update_post_meta( $post_id, '_carbomat_product_sku', $value->id);
                                        update_post_meta( $post_id, '_carbomat_product_stock_avail', $value->stock->available);
                                        update_post_meta( $post_id, '_carbomat_product_netto_price', $value->priceNetto);
                                        update_post_meta( $post_id, '_carbomat_product_moq',$value->moq);
                                           
                                  }
                            }
                                 
                    }
                           
            }

///////////////get data by api using cronjob @ 3o clock ////////////////////////////// 

function my_cron_schedule_3_oclock($schedules){

                            if(!isset($schedules["3oclock"])){
                                $schedules["3oclock"] = array(
                                    'interval' => 59400,
                                    'display' => __('3o Clock'));
                            }

                            return $schedules;
                        }

            add_filter('cron_schedules','my_cron_schedule_3_oclock');

                        if (!wp_next_scheduled('carbomatgroup_hook_3_oclock')) {
                            wp_schedule_event( time(), '3oclock', 'carbomatgroup_hook_3_oclock' );
                        }

            add_action ( 'carbomatgroup_hook_3_oclock', 'carbomatgroup_functions_3_oclock' );

            function carbomatgroup_functions_3_oclock() {

                    global $wpdb , $post;
                    $curlss = curl_init();
                    $todaydate = date("Y-m-d", time()-86400);
                    curl_setopt_array($curlss, array(
                    CURLOPT_URL => "https://api.carbomatgroup.com/v1/articles?sinceModifDate=$todaydate&skip=500&take=500&data=price%2C%20stock",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS =>"",
                          CURLOPT_HTTPHEADER => array(
                            'Accept: application/json',
                            'Authorization: Basic '. base64_encode("RQyR4rMSNbw6ZsHPX46FX74CAKkz9AQ9GveTFQRmJTvgMyd23Sfaq25689RExGYn:")
                          ),
                        ));
                    $responseqq = curl_exec($curlss);
                    $arrre = json_decode($responseqq);
                    foreach ($arrre->data as $value) {

                        $str2 = substr($value->id, 3);
                        $new_sk_id = trim($str2);

                        $peon_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wpm_gtin_code' AND meta_value='%s' LIMIT 1",  $value->ean ) );
                         if($peon_id == "74817") {  unset($peon_id);  } 

                                    if($peon_id){
                                         update_post_meta( $peon_id, '_custom_product_nettoprice', $value->priceNetto );
                                         update_post_meta( $peon_id, 'minimum_allowed_quantity', $value->moq );
                                         update_post_meta( $peon_id, '_stock', $value->stock->available );
                                         update_post_meta($peon_id, '_manage_stock', 'yes');
                                         $stock_statu = 'instock';
                                         update_post_meta( $peon_id, '_stock_status', $stock_statu );
                                         update_post_meta( $peon_id, 'pricenetto_field',  $value->priceNetto );

                                         if($value->moq == '0' || $value->moq == ''){
                                             update_post_meta( $peon_id, '_custom_product_bolprice', $value->priceNetto );
                                             update_post_meta( $peon_id, 'pricebol_field',  $value->priceNetto );
                                         }
                                         else{
                                            $nettobolprice =  $value->priceNetto * $value->moq;
                                             update_post_meta( $peon_id, '_custom_product_bolprice', $nettobolprice );
                                             update_post_meta( $peon_id, 'pricebol_field',  $nettobolprice );
                                         }
                                        
                                        $regular_price = get_post_meta($peon_id, '_regular_price', true);
                                        $vat = 1.21;
                                        $pricevat = $value->priceNetto * $vat;
                                        if($pricevat > $regular_price){
                                            $status = 'draft';
                                            $post = array( 'ID' => $peon_id, 'post_status' => $status );
                                            wp_update_post($post);

                                        }
                                    }
                                    
                                   $pro_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $new_sk_id ) );

                                   if($pro_id){
                                         update_post_meta( $pro_id, '_custom_product_nettoprice', $value->priceNetto );
                                         update_post_meta( $pro_id, 'minimum_allowed_quantity', $value->moq );
                                         update_post_meta( $pro_id, '_stock', $value->stock->available );
                                         update_post_meta($pro_id, '_manage_stock', 'yes');
                                           $stock_statu = 'instock';
                                         update_post_meta( $pro_id, '_stock_status', $stock_statu );
                                         update_post_meta( $pro_id, 'pricenetto_field',  $value->priceNetto );

                                         if($value->moq == '0' || $value->moq == ''){
                                             update_post_meta( $pro_id, '_custom_product_bolprice', $value->priceNetto );
                                             update_post_meta( $pro_id, 'pricebol_field',  $value->priceNetto );
                                         }
                                         else{
                                            $nettobolprice =  $value->priceNetto * $value->moq;
                                            update_post_meta( $pro_id, '_custom_product_bolprice', $nettobolprice );
                                             update_post_meta( $pro_id, 'pricebol_field',  $nettobolprice );
                                         }
                                        
                                        $regular_price = get_post_meta($pro_id, '_regular_price', true);
                                        $vat = 1.21;
                                        $pricevat = $value->priceNetto * $vat;
                                        if($pricevat > $regular_price){
                                            $status = 'draft';
                                            $post = array( 'ID' => $pro_id, 'post_status' => $status );
                                            wp_update_post($post);

                                        }
                                    } 
                                    
                    }
                           
            }

///////////////get data by api using cronjob @ 4o clock ////////////////////////////// 
 
 function my_cron_schedule_4_oclock($schedules){

                            if(!isset($schedules["4oclock"])){
                                $schedules["4oclock"] = array(
                                    'interval' => 63000,
                                    'display' => __('4o Clock'));
                            }

                            return $schedules;
                        }

            add_filter('cron_schedules','my_cron_schedule_4_oclock');

                        if (!wp_next_scheduled('carbomatgroup_hook_4_oclock')) {
                            wp_schedule_event( time(), '4oclock', 'carbomatgroup_hook_4_oclock' );
                        }

            add_action ( 'carbomatgroup_hook_4_oclock', 'carbomatgroup_functions_4_oclock' );

            function carbomatgroup_functions_4_oclock() {

                    global $wpdb , $post;
                    $curlss = curl_init();
                    $todaydate = date("Y-m-d", time()-86400);
                    curl_setopt_array($curlss, array(
                    CURLOPT_URL => "https://api.carbomatgroup.com/v1/articles?sinceModifDate=$todaydate&skip=1000&take=500&data=price%2C%20stock",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS =>"",
                          CURLOPT_HTTPHEADER => array(
                            'Accept: application/json',
                            'Authorization: Basic '. base64_encode("RQyR4rMSNbw6ZsHPX46FX74CAKkz9AQ9GveTFQRmJTvgMyd23Sfaq25689RExGYn:")
                          ),
                        ));
                    $responseqq = curl_exec($curlss);
                    $arrre = json_decode($responseqq);
                    foreach ($arrre->data as $value) {

                        $str2 = substr($value->id, 3);
                        $new_sk_id = trim($str2);

                        $peon_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wpm_gtin_code' AND meta_value='%s' LIMIT 1",  $value->ean ) );
                         if($peon_id == "74817") {  unset($peon_id);  } 

                                    if($peon_id){
                                        update_post_meta( $peon_id, '_custom_product_nettoprice', $value->priceNetto );
                                         update_post_meta( $peon_id, 'minimum_allowed_quantity', $value->moq );
                                         update_post_meta( $peon_id, '_stock', $value->stock->available );
                                         update_post_meta($peon_id, '_manage_stock', 'yes');
                                         $stock_statu = 'instock';
                                         update_post_meta( $peon_id, '_stock_status', $stock_statu );
                                         update_post_meta( $peon_id, 'pricenetto_field',  $value->priceNetto );

                                         if($value->moq == '0' || $value->moq == ''){
                                             update_post_meta( $peon_id, '_custom_product_bolprice', $value->priceNetto );
                                             update_post_meta( $peon_id, 'pricebol_field',  $value->priceNetto );
                                         }
                                         else{
                                            $nettobolprice =  $value->priceNetto * $value->moq;
                                             update_post_meta( $peon_id, '_custom_product_bolprice', $nettobolprice );
                                             update_post_meta( $peon_id, 'pricebol_field',  $nettobolprice );
                                         }
                                        
                                        $regular_price = get_post_meta($peon_id, '_regular_price', true);
                                        $vat = 1.21;
                                        $pricevat = $value->priceNetto * $vat;
                                        if($pricevat > $regular_price){
                                            $status = 'draft';
                                            $post = array( 'ID' => $peon_id, 'post_status' => $status );
                                            wp_update_post($post);

                                        }
                                    }
                                    
                                   $pro_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $new_sk_id ) );

                                   if($pro_id){
                                         update_post_meta( $pro_id, '_custom_product_nettoprice', $value->priceNetto );
                                         update_post_meta( $pro_id, 'minimum_allowed_quantity', $value->moq );
                                         update_post_meta( $pro_id, '_stock', $value->stock->available );
                                         update_post_meta($pro_id, '_manage_stock', 'yes');
                                           $stock_statu = 'instock';
                                         update_post_meta( $pro_id, '_stock_status', $stock_statu );
                                         update_post_meta( $pro_id, 'pricenetto_field',  $value->priceNetto );

                                         if($value->moq == '0' || $value->moq == ''){
                                             update_post_meta( $pro_id, '_custom_product_bolprice', $value->priceNetto );
                                             update_post_meta( $pro_id, 'pricebol_field',  $value->priceNetto );
                                         }
                                         else{
                                            $nettobolprice =  $value->priceNetto * $value->moq;
                                            update_post_meta( $pro_id, '_custom_product_bolprice', $nettobolprice );
                                             update_post_meta( $pro_id, 'pricebol_field',  $nettobolprice );
                                         }
                                        
                                        $regular_price = get_post_meta($pro_id, '_regular_price', true);
                                        $vat = 1.21;
                                        $pricevat = $value->priceNetto * $vat;
                                        if($pricevat > $regular_price){
                                            $status = 'draft';
                                            $post = array( 'ID' => $pro_id, 'post_status' => $status );
                                            wp_update_post($post);

                                        }
                                    } 
                                    
                    }
                           
            }

///////////////get data by api using cronjob @ 5o clock ////////////////////////////// 
 
 function my_cron_schedule_5_oclock($schedules){

                            if(!isset($schedules["5oclock"])){
                                $schedules["5oclock"] = array(
                                    'interval' => 66600,
                                    'display' => __('5o Clock'));
                            }

                            return $schedules;
                        }

            add_filter('cron_schedules','my_cron_schedule_5_oclock');

                        if (!wp_next_scheduled('carbomatgroup_hook_5_oclock')) {
                            wp_schedule_event( time(), '5oclock', 'carbomatgroup_hook_5_oclock' );
                        }

            add_action ( 'carbomatgroup_hook_5_oclock', 'carbomatgroup_functions_5_oclock' );

            function carbomatgroup_functions_5_oclock() {

                    global $wpdb , $post;
                    $curlss = curl_init();
                    $todaydate = date("Y-m-d", time()-86400);
                    curl_setopt_array($curlss, array(
                    CURLOPT_URL => "https://api.carbomatgroup.com/v1/articles?sinceModifDate=$todaydate&skip=500&take=1500&data=price%2C%20stock",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS =>"",
                          CURLOPT_HTTPHEADER => array(
                            'Accept: application/json',
                            'Authorization: Basic '. base64_encode("RQyR4rMSNbw6ZsHPX46FX74CAKkz9AQ9GveTFQRmJTvgMyd23Sfaq25689RExGYn:")
                          ),
                        ));
                    $responseqq = curl_exec($curlss);
                    $arrre = json_decode($responseqq);
                    foreach ($arrre->data as $value) {

                        $str2 = substr($value->id, 3);
                        $new_sk_id = trim($str2);

                        $peon_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wpm_gtin_code' AND meta_value='%s' LIMIT 1",  $value->ean ) );
                         if($peon_id == "74817") {  unset($peon_id);  } 

                                    if($peon_id){
                                        update_post_meta( $peon_id, '_custom_product_nettoprice', $value->priceNetto );
                                         update_post_meta( $peon_id, 'minimum_allowed_quantity', $value->moq );
                                         update_post_meta( $peon_id, '_stock', $value->stock->available );
                                         update_post_meta($peon_id, '_manage_stock', 'yes');
                                         $stock_statu = 'instock';
                                         update_post_meta( $peon_id, '_stock_status', $stock_statu );
                                         update_post_meta( $peon_id, 'pricenetto_field',  $value->priceNetto );

                                         if($value->moq == '0' || $value->moq == ''){
                                             update_post_meta( $peon_id, '_custom_product_bolprice', $value->priceNetto );
                                             update_post_meta( $peon_id, 'pricebol_field',  $value->priceNetto );
                                         }
                                         else{
                                            $nettobolprice =  $value->priceNetto * $value->moq;
                                             update_post_meta( $peon_id, '_custom_product_bolprice', $nettobolprice );
                                             update_post_meta( $peon_id, 'pricebol_field',  $nettobolprice );
                                         }
                                        
                                        $regular_price = get_post_meta($peon_id, '_regular_price', true);
                                        $vat = 1.21;
                                        $pricevat = $value->priceNetto * $vat;
                                        if($pricevat > $regular_price){
                                            $status = 'draft';
                                            $post = array( 'ID' => $peon_id, 'post_status' => $status );
                                            wp_update_post($post);

                                        }
                                    }
                                    
                                   $pro_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $new_sk_id ) );

                                   if($pro_id){
                                         update_post_meta( $pro_id, '_custom_product_nettoprice', $value->priceNetto );
                                         update_post_meta( $pro_id, 'minimum_allowed_quantity', $value->moq );
                                         update_post_meta( $pro_id, '_stock', $value->stock->available );
                                         update_post_meta($pro_id, '_manage_stock', 'yes');
                                           $stock_statu = 'instock';
                                         update_post_meta( $pro_id, '_stock_status', $stock_statu );
                                         update_post_meta( $pro_id, 'pricenetto_field',  $value->priceNetto );

                                         if($value->moq == '0' || $value->moq == ''){
                                             update_post_meta( $pro_id, '_custom_product_bolprice', $value->priceNetto );
                                             update_post_meta( $pro_id, 'pricebol_field',  $value->priceNetto );
                                         }
                                         else{
                                            $nettobolprice =  $value->priceNetto * $value->moq;
                                            update_post_meta( $pro_id, '_custom_product_bolprice', $nettobolprice );
                                             update_post_meta( $pro_id, 'pricebol_field',  $nettobolprice );
                                         }
                                        
                                        $regular_price = get_post_meta($pro_id, '_regular_price', true);
                                        $vat = 1.21;
                                        $pricevat = $value->priceNetto * $vat;
                                        if($pricevat > $regular_price){
                                            $status = 'draft';
                                            $post = array( 'ID' => $pro_id, 'post_status' => $status );
                                            wp_update_post($post);

                                        }
                                    } 
                                    
                    }
                           
            }

////////////////create meta fields//////////////////

        function hcf_register_meta_boxes() {
            add_meta_box( 'hcf-1', __( 'Products', 'hcf' ), 'hcf_display_callback', 'wholesale-product' );
        }
        add_action( 'add_meta_boxes', 'hcf_register_meta_boxes'  );

        function hcf_display_callback( $post ) {
            if($post->post_type == 'wholesale-product'){ 
               $carbomat_product_sku = get_post_meta( $post->ID, '_carbomat_product_sku' ,true);
               $carbomat_product_stock_avail = get_post_meta( $post->ID, '_carbomat_product_stock_avail',true );
               $carbomat_product_netto_price = get_post_meta( $post->ID, '_carbomat_product_netto_price',true );
               $carbomat_product_moq = get_post_meta( $post->ID, '_carbomat_product_moq' ,true);


                ?>
                <div class="column-4">
                <label for="featuret-group">Carbomat Product Sku</label>
                <input id="" value = "<?php echo $carbomat_product_sku; ?>" class="" type="text" name="carbomat_product_sku"></div>
                <div class="column-4">
                <label for="featuret-group">Carbomat Product Netto Price </label>
                <input id="" value = "<?php echo $carbomat_product_netto_price; ?>" class="" type="text" name="carbomat_product_netto_price"></div>
                <div class="column-4">
                <label for="featuret-group">Carbomat Product Moq</label>
                <input id="" value = "<?php echo $carbomat_product_moq; ?>" class="" type="text" name="carbomat_product_moq"></div>
                <div class="column-4">
                <label for="featuret-group">Carbomat Product Available Stock</label>
                <input id="" value = "<?php echo $carbomat_product_stock_avail; ?>" class="" type="text" name="carbomat_product_stock_avail"></div>
           <?php }

        }
        function hcfd_save_meta_box( $post_id) {
            global $post;
            if(isset($_POST)&& !empty($_POST)){
                update_post_meta( $post_id, '_carbomat_product_sku', $_POST['carbomat_product_sku'] );
                update_post_meta( $post_id, '_carbomat_product_stock_avail', $_POST['carbomat_product_stock_avail'] );
                update_post_meta( $post_id, '_carbomat_product_netto_price', $_POST['carbomat_product_netto_price'] );
                update_post_meta( $post_id, '_carbomat_product_moq',$_POST['carbomat_product_moq'] );
            }
        }
        add_action( 'save_post', 'hcfd_save_meta_box' );
        
        // Display Fields
        add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_customs_fields');
        add_action('woocommerce_process_product_meta', 'woocommerce_product_customs_fields_save');
        

        function woocommerce_product_customs_fields()
        {
            global $woocommerce, $post;
            echo '<div class="product_custom_field">';
            woocommerce_wp_text_input(
                array(
                    'id' => '_custom_product_nettoprice',
                    'placeholder' => 'Product Netto Price',
                    'label' => __('Product Netto Price', 'woocommerce'),
                    'desc_tip' => 'true'
                )
            );
            woocommerce_wp_text_input(
                array(
                    'id' => '_custom_product_bolprice',
                    'placeholder' => 'Product Bol Price',
                    'label' => __('Product Bol Price', 'woocommerce'),
                    'desc_tip' => 'true'
                )
            );
            echo '</div>';

        }

        function woocommerce_product_customs_fields_save($post_id)
        {
            // Custom Product Text Field
            $woocommerce_custom_product_nettoprice = $_POST['_custom_product_nettoprice'];
            $woocommerce_custom_product_bolprice = $_POST['_custom_product_bolprice'];
            if (!empty($woocommerce_custom_product_nettoprice))
                update_post_meta($post_id, '_custom_product_nettoprice', esc_attr($woocommerce_custom_product_nettoprice));
            if (!empty($woocommerce_custom_product_bolprice))
                update_post_meta($post_id, '_custom_product_bolprice', esc_attr($woocommerce_custom_product_bolprice));
        }


        add_action( 'woocommerce_variation_options_pricing', 'bbloomer_add_custom_field_to_variations', 10, 3 );
 
        function bbloomer_add_custom_field_to_variations( $loop, $variation_data, $variation ) {
            echo '<div class="product_custom_field form-row">';
               woocommerce_wp_text_input( array(
                    'id' => 'pricenetto_field[' . $loop . ']',
                    'class' => 'short',
                    'label' => __( 'Price Netto Field', 'woocommerce' ),
                    'value' => get_post_meta( $variation->ID, 'pricenetto_field', true )
                ) );

               woocommerce_wp_text_input( array(
                    'id' => 'pricebol_field[' . $loop . ']',
                    'class' => 'short',
                    'label' => __( 'Price Bol Field', 'woocommerce' ),
                    'value' => get_post_meta( $variation->ID, 'pricebol_field', true )
                ) );

           echo '</div>';
        }
         
        // -----------------------------------------
        // 2. Save custom field on product variation save
         
        add_action( 'woocommerce_save_product_variation', 'bbloomer_save_custom_field_variations', 10, 2 );
         
        function bbloomer_save_custom_field_variations( $variation_id, $i ) {
           $pricenetto_field = $_POST['pricenetto_field'][$i];
           $pricebol_field = $_POST['pricebol_field'][$i];
           if ( isset( $pricenetto_field ) ) update_post_meta( $variation_id, 'pricenetto_field', esc_attr( $pricenetto_field ) );
           if ( isset( $pricebol_field ) ) update_post_meta( $variation_id, 'pricebol_field', esc_attr( $pricebol_field ) );
        }
         
        // -----------------------------------------
        // 3. Store custom field value into variation data
         
        add_filter( 'woocommerce_available_variation', 'bbloomer_add_custom_field_variation_data' );
         
        function bbloomer_add_custom_field_variation_data( $variations ) {
           $variations['pricenetto_field'] = '<div class="woocommerce_custom_field">Price Netto Field: <span>' . get_post_meta( $variations[ 'variation_id' ], 'pricenetto_field', true ) . '</span></div>';
           $variations['pricebol_field'] = '<div class="woocommerce_custom_field">Price Bol Field: <span>' . get_post_meta( $variations[ 'variation_id' ], 'pricebol_field', true ) . '</span></div>';
           return $variations;
        }

       /* Carbomat product custom tab */
           add_filter('woocommerce_product_data_tabs', 'custom_carbomat_product_tab');

            function custom_carbomat_product_tab($tabs) {
                  $tabs['stockbeheer_info'] = [
                    'label' => __('Stockbeheer', 'txtdomain'),
                    'target' => 'stockbeheer_product_data',
                    'class' => ['hide_if_external'],
                    'priority' => 50
                  ];
                  return $tabs;
          } 

          add_action('woocommerce_product_data_panels', 'stockbeheer_product_data_fields');
          function stockbeheer_product_data_fields() { 
                global $wbdp, $post;
                 $products = wc_get_product( $post->ID );
                 $pro_type = $products->get_type();
                if($pro_type == 'simple'){
                  include('carbomat_simple.php'); }
               elseif($pro_type == 'variable'){ include('carbomat_variable.php'); }
               else{ }
          }

  /* Sync single simple product ajax action */
  
    add_action( 'wp_ajax_sync_single_product', 'sync_single_product');
    add_action( 'wp_ajax_nopriv_sync_single_product', 'sync_single_product'); 

    add_action( 'wp_ajax_sync_variable_product', 'sync_variable_product');
    add_action( 'wp_ajax_nopriv_sync_variable_product', 'sync_variable_product'); 


    function  sync_single_product(){
      global $wpdb;

      if(isset($_POST['id'])){
        $simplecarboid = $_POST['id'];
        $simple_eon = get_post_meta($simplecarboid, '_wpm_gtin_code', true);
         $curlss = curl_init();
                    curl_setopt_array($curlss, array(
                    CURLOPT_URL => "https://api.carbomatgroup.com/v1/articles/$simple_eon?data=price,stock",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS =>"",
                          CURLOPT_HTTPHEADER => array(
                            'Accept: application/json',
                            'Authorization: Basic '. base64_encode("RQyR4rMSNbw6ZsHPX46FX74CAKkz9AQ9GveTFQRmJTvgMyd23Sfaq25689RExGYn:")
                          ),
                        ));
                    $responseqq = curl_exec($curlss);
                    $simple_carbomat = json_decode($responseqq);
                    if($simple_carbomat != ''){
                     update_post_meta( $simplecarboid, '_custom_product_nettoprice', $simple_carbomat->priceNetto );
                     update_post_meta( $simplecarboid, 'minimum_allowed_quantity', $simple_carbomat->moq );
                     update_post_meta( $simplecarboid, '_stock', $simple_carbomat->stock->available );
                     update_post_meta($simplecarboid, '_manage_stock', 'yes');
                     $stock_statu = 'instock';
                     update_post_meta( $simplecarboid, '_stock_status', $stock_statu );
                     update_post_meta( $simplecarboid, 'pricenetto_field',  $simple_carbomat->priceNetto );

                     if($simple_carbomat->moq == '0' || $simple_carbomat->moq == ''){
                         update_post_meta( $simplecarboid, '_custom_product_bolprice', $simple_carbomat->priceNetto ); }
                     else{
                          $nettobolprice =  $simple_carbomat->priceNetto * $simple_carbomat->moq;
                          update_post_meta( $simplecarboid, '_custom_product_bolprice', $nettobolprice );
                        }
                                        
                          $regular_price = get_post_meta($simplecarboid, '_regular_price', true);
                          $vat = 1.21;
                          $pricevat = $value->priceNetto * $vat;
                            if($pricevat > $regular_price){
                                  $status = 'draft';
                                  $post = array( 'ID' => $simplecarboid, 'post_status' => $status );
                                  wp_update_post($post);

                            }
                }
                else{
                    $products = wc_get_product($simplecarboid);
                    $simple_sku = $products->get_sku();
                    $sim_brandname = get_the_terms($simplecarboid,'product_brand');
                    $sim_brandsku = substr($sim_brandname[0]->name, 0, 3);
                    $newsim_sku = strtoupper($sim_brandsku).$simple_sku;
                    $curlss = curl_init();
                    curl_setopt_array($curlss, array(
                    CURLOPT_URL => "https://api.carbomatgroup.com/v1/articles/$newsim_sku?data=price,stock",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS =>"",
                          CURLOPT_HTTPHEADER => array(
                            'Accept: application/json',
                            'Authorization: Basic '. base64_encode("RQyR4rMSNbw6ZsHPX46FX74CAKkz9AQ9GveTFQRmJTvgMyd23Sfaq25689RExGYn:")
                          ),
                        ));
                    $responseqq = curl_exec($curlss);
                    $simple_carbomat = json_decode($responseqq);
                    if($simple_carbomat != '') {                  
                     update_post_meta( $simplecarboid, '_custom_product_nettoprice', $simple_carbomat->priceNetto );
                     update_post_meta( $simplecarboid, 'minimum_allowed_quantity', $simple_carbomat->moq );
                     update_post_meta( $simplecarboid, '_stock', $simple_carbomat->stock->available );
                     update_post_meta($simplecarboid, '_manage_stock', 'yes');
                     $stock_statu = 'instock';
                     update_post_meta( $simplecarboid, '_stock_status', $stock_statu );
                     update_post_meta( $simplecarboid, 'pricenetto_field',  $simple_carbomat->priceNetto );

                     if($simple_carbomat->moq == '0' || $simple_carbomat->moq == ''){
                         update_post_meta( $simplecarboid, '_custom_product_bolprice', $simple_carbomat->priceNetto ); }
                     else{
                          $nettobolprice =  $simple_carbomat->priceNetto * $simple_carbomat->moq;
                          update_post_meta( $simplecarboid, '_custom_product_bolprice', $nettobolprice );
                        }
                                        
                          $regular_price = get_post_meta($simplecarboid, '_regular_price', true);
                          $vat = 1.21;
                          $pricevat = $value->priceNetto * $vat;
                            if($pricevat > $regular_price){
                                  $status = 'draft';
                                  $post = array( 'ID' => $simplecarboid, 'post_status' => $status );
                                  wp_update_post($post);

                            }
                    }
                    else{
                        $stock_onbackorder = 'onbackorder';
                     update_post_meta( $simplecarboid, '_stock_status', $stock_onbackorder );
                    } 

                } 
              echo '1';          
                  
          }
          die();
    }

/* Sync single variable product ajax action */

    function  sync_variable_product(){
      global $wpdb;

      if(isset($_POST['id'])){
        $variablecarboid = $_POST['id'];
        $pro_variable_eon = get_post_meta($variablecarboid, '_wpm_gtin_code', true);
         $curlss = curl_init();
                    curl_setopt_array($curlss, array(
                    CURLOPT_URL => "https://api.carbomatgroup.com/v1/articles/$pro_variable_eon?data=price,stock",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS =>"",
                          CURLOPT_HTTPHEADER => array(
                            'Accept: application/json',
                            'Authorization: Basic '. base64_encode("RQyR4rMSNbw6ZsHPX46FX74CAKkz9AQ9GveTFQRmJTvgMyd23Sfaq25689RExGYn:")
                          ),
                        ));
                    $responseqq = curl_exec($curlss);
                    $varibale_carbomat = json_decode($responseqq);
                if($varibale_carbomat != ''){
                     update_post_meta( $variablecarboid, 'minimum_allowed_quantity', $varibale_carbomat->moq );
                     update_post_meta( $variablecarboid, '_stock', $varibale_carbomat->stock->available );
                     update_post_meta( $variablecarboid, '_manage_stock', 'yes');
                     $stock_statu = 'instock';
                     update_post_meta( $variablecarboid, '_stock_status', $stock_statu );
                     update_post_meta( $variablecarboid, 'pricenetto_field',  $varibale_carbomat->priceNetto );

                     if($varibale_carbomat->moq == '0' || $varibale_carbomat->moq == ''){
                         update_post_meta( $simplecarboid, 'pricebol_field',  $varibale_carbomat->priceNetto ); }
                     else{
                          $nettobolprice =  $varibale_carbomat->priceNetto * $varibale_carbomat->moq;
                          update_post_meta( $variablecarboid, 'pricebol_field',  $nettobolprice );
                        }
                                        
                          $regular_price = get_post_meta($variablecarboid, '_regular_price', true);
                          $vat = 1.21;
                          $pricevat = $value->priceNetto * $vat;
                            if($pricevat > $regular_price){
                                  $status = 'draft';
                                  $post = array( 'ID' => $variablecarboid, 'post_status' => $status );
                                  wp_update_post($post);

                            }
                }
                else{
                    $products = wc_get_product($variablecarboid);
                    $pro_variable_sku = $products->get_sku();
                    $pro_brandname = get_the_terms($products->parent_id,'product_brand');
                    $pro_brandsku = substr($pro_brandname[0]->name, 0, 3);
                    $newpro_sku = strtoupper($pro_brandsku).$pro_variable_sku;
                    $curlss = curl_init();
                    curl_setopt_array($curlss, array(
                    CURLOPT_URL => "https://api.carbomatgroup.com/v1/articles/$newpro_sku?data=price,stock",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS =>"",
                          CURLOPT_HTTPHEADER => array(
                            'Accept: application/json',
                            'Authorization: Basic '. base64_encode("RQyR4rMSNbw6ZsHPX46FX74CAKkz9AQ9GveTFQRmJTvgMyd23Sfaq25689RExGYn:")
                          ),
                        ));
                    $responseqq = curl_exec($curlss);
                    $varibale_carbomat = json_decode($responseqq);
                    if($varibale_carbomat != '') { 
                     update_post_meta( $variablecarboid, 'minimum_allowed_quantity', $varibale_carbomat->moq );
                     update_post_meta( $variablecarboid, '_stock', $varibale_carbomat->stock->available );
                     update_post_meta($variablecarboid, '_manage_stock', 'yes');
                     $stock_statu = 'instock';
                     update_post_meta( $variablecarboid, '_stock_status', $stock_statu );
                     update_post_meta( $variablecarboid, 'pricenetto_field',  $varibale_carbomat->priceNetto );

                     if($varibale_carbomat->moq == '0' || $varibale_carbomat->moq == ''){
                         update_post_meta( $variablecarboid, 'pricebol_field',  $varibale_carbomat->priceNetto ); }
                     else{
                          $nettobolprice =  $varibale_carbomat->priceNetto * $varibale_carbomat->moq;
                          update_post_meta( $variablecarboid, 'pricebol_field',  $nettobolprice );
                        }
                                        
                          $regular_price = get_post_meta($variablecarboid, '_regular_price', true);
                          $vat = 1.21;
                          $pricevat = $value->priceNetto * $vat;
                            if($pricevat > $regular_price){
                                  $status = 'draft';
                                  $post = array( 'ID' => $variablecarboid, 'post_status' => $status );
                                  wp_update_post($post);

                            }
                        }
                        else{
                        $stock_onbackorder = 'onbackorder';
                     update_post_meta( $variablecarboid, '_stock_status', $stock_onbackorder );
                    } 
               }
               echo '1'; 
        }
        die();
    }

    function wpb_lastupdated_posts() { 
         
        global $pagenow;
        if ( $pagenow == 'index.php' ) {
            $lastupdated_args = array(
                'post_type' => 'product',
                'posts_per_page' => 5,
                'orderby' => 'modified',
                'order' => 'DESC'
            
            );
             
            $lastupdated_loop = new WP_Query( $lastupdated_args );
            $counter = 1;
            //$string .= '<p>Latest Sync Products</p>';
            $string .= '<ul>';
            while( $lastupdated_loop->have_posts()) : $lastupdated_loop->the_post();
            $string .= '<li>' .get_the_title( $lastupdated_loop->post->ID ) . '  ( '. get_the_modified_date() .') </li>';
            $counter++;
            endwhile; 
            $string .= '</ul>';
            $class = 'notice notice-info';
            //printf( '<div class="%1$s"><p>Latest Sync Products</p></div>', esc_attr( $class ),  $string  ); 
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ),  $string  ); 
            wp_reset_postdata(); 
       }

     } 
         
      add_action('admin_notices', 'wpb_lastupdated_posts');

       