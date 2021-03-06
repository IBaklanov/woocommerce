<?php
/**
 * Admin Reports
 *
 * Functions used for displaying sales and customer reports in admin.
 *
 * @author 		WooThemes
 * @category 	Admin
 * @package 	WooCommerce/Admin/Reports
 * @version     1.7
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Reports page
 *
 * Handles the display of the reports page in admin.
 *
 * @access public
 * @return void
 */
function woocommerce_reports() {

	$current_tab 	= isset( $_GET['tab'] ) ? sanitize_title( urldecode( $_GET['tab'] ) ) : 'sales';
	$current_chart 	= isset( $_GET['chart'] ) ? absint( urldecode( $_GET['chart'] ) ) : 0;

	$charts = apply_filters( 'woocommerce_reports_charts', array(
		'sales' => array(
			'title' 	=>  __( 'Sales', 'woocommerce' ),
			'charts' 	=> array(
				array(
					'title' => __( 'Overview', 'woocommerce' ),
					'description' => '',
					'hide_title' => true,
					'function' => 'woocommerce_sales_overview'
				),
				array(
					'title' => __( 'Sales by day', 'woocommerce' ),
					'description' => '',
					'function' => 'woocommerce_daily_sales'
				),
				array(
					'title' => __( 'Sales by month', 'woocommerce' ),
					'description' => '',
					'function' => 'woocommerce_monthly_sales'
				),
				array(
					'title' => __( 'Taxes by month', 'woocommerce' ),
					'description' => '',
					'function' => 'woocommerce_monthly_taxes'
				),
				array(
					'title' => __( 'Product Sales', 'woocommerce' ),
					'description' => '',
					'function' => 'woocommerce_product_sales'
				),
				array(
					'title' => __( 'Top sellers', 'woocommerce' ),
					'description' => '',
					'function' => 'woocommerce_top_sellers'
				),
				array(
					'title' => __( 'Top earners', 'woocommerce' ),
					'description' => '',
					'function' => 'woocommerce_top_earners'
				),
				array(
					'title' => __( 'Sales by category', 'woocommerce' ),
					'description' => '',
					'function' => 'woocommerce_category_sales'
				),
				array(
					'title' => __( 'Sales by coupon', 'woocommerce' ),
					'description' => '',
					'function' => 'woocommerce_coupon_sales'
				)
			)
		),
		'customers' => array(
			'title' 	=>  __( 'Customers', 'woocommerce' ),
			'charts' 	=> array(
				array(
					'title' => __( 'Overview', 'woocommerce' ),
					'description' => '',
					'hide_title' => true,
					'function' => 'woocommerce_customer_overview'
				),
			)
		),
		'stock' => array(
			'title' 	=>  __( 'Stock', 'woocommerce' ),
			'charts' 	=> array(
				array(
					'title' => __( 'Overview', 'woocommerce' ),
					'description' => '',
					'hide_title' => true,
					'function' => 'woocommerce_stock_overview'
				),
			)
		)
	) );
    ?>
	<div class="wrap woocommerce">
		<div class="icon32 icon32-woocommerce-reports" id="icon-woocommerce"><br /></div><h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php
				foreach ( $charts as $key => $chart ) {
					echo '<a href="'.admin_url( 'admin.php?page=woocommerce_reports&tab=' . urlencode( $key ) ).'" class="nav-tab ';
					if ( $current_tab == $key ) echo 'nav-tab-active';
					echo '">' . esc_html( $chart[ 'title' ] ) . '</a>';
				}
			?>
			<?php do_action('woocommerce_reports_tabs'); ?>
		</h2>

		<?php if ( sizeof( $charts[ $current_tab ]['charts'] ) > 1 ) {
			?>
			<ul class="subsubsub">
				<li><?php

					$links = array();

					foreach ( $charts[ $current_tab ]['charts'] as $key => $chart ) {

						$link = '<a href="admin.php?page=woocommerce_reports&tab=' . urlencode( $current_tab ) . '&amp;chart=' . urlencode( $key ) . '" class="';

						if ( $key == $current_chart ) $link .= 'current';

						$link .= '">' . $chart['title'] . '</a>';

						$links[] = $link;

					}

					echo implode(' | </li><li>', $links);

				?></li>
			</ul>
			<br class="clear" />
			<?php
		}

		if ( isset( $charts[ $current_tab ][ 'charts' ][ $current_chart ] ) ) {

			$chart = $charts[ $current_tab ][ 'charts' ][ $current_chart ];

			if ( ! isset( $chart['hide_title'] ) || $chart['hide_title'] != true )
				echo '<h3>' . $chart['title'] . '</h3>';

			if ( $chart['description'] )
				echo '<p>' . $chart['description'] . '</p>';

			$func = $chart['function'];
			if ( $func && function_exists( $func ) )
				$func();
		}
		?>
	</div>
	<?php
}


/**
 * Output JavaScript for highlighting weekends on charts.
 *
 * @access public
 * @return void
 */
function woocommerce_weekend_area_js() {
	?>
	function weekendAreas(axes) {
        var markings = [];
        var d = new Date(axes.xaxis.min);
        // go to the first Saturday
        d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
        d.setUTCSeconds(0);
        d.setUTCMinutes(0);
        d.setUTCHours(0);
        var i = d.getTime();
        do {
            markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
            i += 7 * 24 * 60 * 60 * 1000;
        } while (i < axes.xaxis.max);

        return markings;
    }
    <?php
}


/**
 * Output JavaScript for chart tooltips.
 *
 * @access public
 * @return void
 */
function woocommerce_tooltip_js() {
	?>
	function showTooltip(x, y, contents) {
        jQuery('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 5,
		    padding: '5px 10px',
			border: '3px solid #3da5d5',
			background: '#288ab7'
        }).appendTo("body").fadeIn(200);
    }
    var previousPoint = null;
    jQuery("#placeholder").bind("plothover", function (event, pos, item) {
        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;

                jQuery("#tooltip").remove();

                if (item.series.label=="<?php echo esc_js( __( 'Sales amount', 'woocommerce' ) ) ?>") {

                	var y = item.datapoint[1].toFixed(2);
                	showTooltip(item.pageX, item.pageY, item.series.label + " - " + "<?php echo get_woocommerce_currency_symbol(); ?>" + y);

                } else if (item.series.label=="<?php echo esc_js( __( 'Number of sales', 'woocommerce' ) ) ?>") {

                	var y = item.datapoint[1];
                	showTooltip(item.pageX, item.pageY, item.series.label + " - " + y);

                } else {

                	var y = item.datapoint[1];
                	showTooltip(item.pageX, item.pageY, y);

                }
            }
        }
        else {
            jQuery("#tooltip").remove();
            previousPoint = null;
        }
    });
    <?php
}


/**
 * Output Javascript for date ranges.
 *
 * @access public
 * @return void
 */
function woocommerce_datepicker_js() {
	global $woocommerce;
	?>
	var dates = jQuery( "#from, #to" ).datepicker({
		defaultDate: "",
		dateFormat: "yy-mm-dd",
		//changeMonth: true,
		//changeYear: true,
		numberOfMonths: 1,
		minDate: "-12M",
		maxDate: "+0D",
		showButtonPanel: true,
		showOn: "button",
		buttonImage: "<?php echo $woocommerce->plugin_url(); ?>/assets/images/calendar.png",
		buttonImageOnly: true,
		onSelect: function( selectedDate ) {
			var option = this.id == "from" ? "minDate" : "maxDate",
				instance = jQuery( this ).data( "datepicker" ),
				date = jQuery.datepicker.parseDate(
					instance.settings.dateFormat ||
					jQuery.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});
	<?php
}


/**
 * Output the sales overview chart.
 *
 * @access public
 * @return void
 */
function woocommerce_sales_overview() {

	global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale;

	$total_sales = $total_orders = $order_items = $discount_total = $shipping_total = 0;

	$order_totals = $wpdb->get_row( $wpdb->prepare( "
		SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	meta.meta_key 		= '_order_total'
		AND 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
	" ) );

	$total_sales 	= $order_totals->total_sales;
	$total_orders 	= absint( $order_totals->total_orders );

	$discount_total = $wpdb->get_var( $wpdb->prepare( "
		SELECT SUM(meta.meta_value) AS total_sales FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	meta.meta_key 		IN ('_order_discount', '_cart_discount')
		AND 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
	" ) );

	$shipping_total = $wpdb->get_var( $wpdb->prepare( "
		SELECT SUM(meta.meta_value) AS total_sales FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	meta.meta_key 		= '_order_shipping'
		AND 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
	" ) );

	$order_items_serialized = $wpdb->get_col( $wpdb->prepare( "
		SELECT meta.meta_value AS items FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	meta.meta_key 		= '_order_items'
		AND 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
	" ) );

	if ( $order_items_serialized ) {
		foreach ( $order_items_serialized as $order_items_array ) {
			$order_items_array = maybe_unserialize( $order_items_array );
			if ( is_array( $order_items_array ) ) 
				foreach ( $order_items_array as $item ) 
					$order_items += absint( $item['qty'] );
		}
	}
	?>
	<div id="poststuff" class="woocommerce-reports-wrap">
		<div class="woocommerce-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e( 'Total sales', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo woocommerce_price($total_sales); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total orders', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_orders > 0 ) echo $total_orders . ' (' . $order_items . ' ' . __( 'items', 'woocommerce' ) . ')'; else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Average order total', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_orders>0) echo woocommerce_price($total_sales/$total_orders); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Average order items', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_orders>0) echo number_format($order_items/$total_orders, 2); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Discounts used', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($discount_total>0) echo woocommerce_price($discount_total); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total shipping costs', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($shipping_total>0) echo woocommerce_price($shipping_total); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
		</div>
		<div class="woocommerce-reports-main">
			<div class="postbox">
				<h3><span><?php _e( 'This month\'s sales', 'woocommerce' ); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php

	$start_date = strtotime( date('Ymd', strtotime( date('Ym', current_time('timestamp') ) . '01' ) ) );
	$end_date = strtotime( date('Ymd', current_time( 'timestamp' ) ) );
	
	// Blank date ranges to begin
	$order_counts = $order_amounts = array();

	$count = 0;
	
	$days = ( $end_date - $start_date ) / ( 60 * 60 * 24 );
	
	if ( $days == 0 ) 
		$days = 1;

	while ( $count < $days ) {
		$time = strtotime( date( 'Ymd', strtotime( '+ ' . $count . ' DAY', $start_date ) ) ) . '000';

		$order_counts[ $time ] = $order_amounts[ $time ] = 0;

		$count++;
	}
	
	// Get order ids and dates in range
	$orders = $wpdb->get_results( $wpdb->prepare( "
		SELECT posts.ID, posts.post_date FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
		AND 	post_date > '" . date('Y-m-d', $start_date ) . "'
		AND 	post_date < '" . date('Y-m-d', strtotime('+1 day', $end_date ) ) . "'
		ORDER BY post_date ASC
	" ) );

	if ( $orders ) {
		foreach ( $orders as $order ) {

			$order_total = get_post_meta( $order->ID, '_order_total', true );
			$time = strtotime( date( 'Ymd', strtotime( $order->post_date ) ) ) . '000';

			if ( isset( $order_counts[ $time ] ) )
				$order_counts[ $time ]++;
			else
				$order_counts[ $time ] = 1;

			if ( isset( $order_amounts[ $time ] ) )
				$order_amounts[ $time ] = $order_amounts[ $time ] + $order_total;
			else
				$order_amounts[ $time ] = floatval( $order_total );
		}
	}

	$order_counts_array = $order_amounts_array = array();
	
	foreach ( $order_counts as $key => $count )
		$order_counts_array[] = array( esc_js( $key ), esc_js( $count ) );
	
	foreach ( $order_amounts as $key => $amount )
		$order_amounts_array[] = array( esc_js( $key ), esc_js( $amount ) );

	$order_data = array( 'order_counts' => $order_counts_array, 'order_amounts' => $order_amounts_array );

	$chart_data = json_encode( $order_data );
	?>
	<script type="text/javascript">
		jQuery(function(){
			var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );

			var d = order_data.order_counts;
		    var d2 = order_data.order_amounts;

			for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
		    for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;

			var placeholder = jQuery("#placeholder");

			var plot = jQuery.plot(placeholder, [ { label: "<?php echo esc_js( __( 'Number of sales', 'woocommerce' ) ) ?>", data: d }, { label: "<?php echo esc_js( __( 'Sales amount', 'woocommerce' ) ) ?>", data: d2, yaxis: 2 } ], {
				series: {
					lines: { show: true, fill: true },
					points: { show: true }
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#aaa',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#aaa',
					clickable: false,
					hoverable: true,
					markings: weekendAreas
				},
				xaxis: {
					mode: "time",
					timeformat: "%d %b",
					monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
					tickLength: 1,
					minTickSize: [1, "day"]
				},
				yaxes: [ { min: 0, tickSize: 10, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
		   		colors: ["#8a4b75", "#47a03e"]
		 	});

		 	placeholder.resize();

			<?php woocommerce_weekend_area_js(); ?>
			<?php woocommerce_tooltip_js(); ?>
		});
	</script>
	<?php
}


/**
 * Output the daily sales chart.
 *
 * @access public
 * @return void
 */
function woocommerce_daily_sales() {

	global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale;

	$start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
	$end_date	= isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';

	if ( ! $start_date) 
		$start_date = date( 'Ymd', strtotime( date('Ym', current_time( 'timestamp' ) ) . '01' ) );
	if ( ! $end_date) 
		$end_date = date( 'Ymd', current_time( 'timestamp' ) );

	$start_date = strtotime( $start_date );
	$end_date = strtotime( $end_date );
	
	$total_sales = $total_orders = $order_items = 0;

	// Blank date ranges to begin
	$order_counts = $order_amounts = array();

	$count = 0;
	
	$days = ( $end_date - $start_date ) / ( 60 * 60 * 24 );
	
	if ( $days == 0 ) 
		$days = 1;

	while ( $count < $days ) {
		$time = strtotime( date( 'Ymd', strtotime( '+ ' . $count . ' DAY', $start_date ) ) ) . '000';

		$order_counts[ $time ] = $order_amounts[ $time ] = 0;

		$count++;
	}
	
	// Get order ids and dates in range
	$orders = $wpdb->get_results( $wpdb->prepare( "
		SELECT posts.ID, posts.post_date FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
		AND 	post_date > '" . date('Y-m-d', $start_date ) . "'
		AND 	post_date < '" . date('Y-m-d', strtotime('+1 day', $end_date ) ) . "'
		ORDER BY post_date ASC
	" ) );

	if ( $orders ) {
		foreach ( $orders as $order ) {

			$order_total = get_post_meta($order->ID, '_order_total', true);
			$time = strtotime( date( 'Ymd', strtotime( $order->post_date ) ) ) . '000';

			$order_items_array = (array) get_post_meta( $order->ID, '_order_items', true );
			foreach ( $order_items_array as $item ) 
				$order_items += absint( $item['qty'] );
			$total_sales += $order_total;
			$total_orders++;

			if ( isset( $order_counts[ $time ] ) )
				$order_counts[ $time ]++;
			else
				$order_counts[ $time ] = 1;

			if ( isset( $order_amounts[ $time ] ) )
				$order_amounts[ $time ] = $order_amounts[ $time ] + $order_total;
			else
				$order_amounts[ $time ] = floatval( $order_total );
		}
	}
	?>
	<form method="post" action="">
		<p><label for="from"><?php _e( 'From:', 'woocommerce' ); ?></label> <input type="text" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $start_date) ); ?>" /> <label for="to"><?php _e( 'To:', 'woocommerce' ); ?></label> <input type="text" name="end_date" id="to" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $end_date) ); ?>" /> <input type="submit" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" /></p>
	</form>

	<div id="poststuff" class="woocommerce-reports-wrap">
		<div class="woocommerce-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e( 'Total sales in range', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo woocommerce_price($total_sales); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total orders in range', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_orders > 0 ) echo $total_orders . ' (' . $order_items . ' ' . __( 'items', 'woocommerce' ) . ')'; else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Average order total in range', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_orders>0) echo woocommerce_price($total_sales/$total_orders); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Average order items in range', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_orders>0) echo number_format($order_items/$total_orders, 2); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
		</div>
		<div class="woocommerce-reports-main">
			<div class="postbox">
				<h3><span><?php _e( 'Sales in range', 'woocommerce' ); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php

	$order_counts_array = $order_amounts_array = array();
	
	foreach ( $order_counts as $key => $count )
		$order_counts_array[] = array( esc_js( $key ), esc_js( $count ) );

	foreach ( $order_amounts as $key => $amount )
		$order_amounts_array[] = array( esc_js( $key ), esc_js( $amount ) );

	$order_data = array( 'order_counts' => $order_counts_array, 'order_amounts' => $order_amounts_array );

	$chart_data = json_encode($order_data);
	?>
	<script type="text/javascript">
		jQuery(function(){
			var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );

			var d = order_data.order_counts;
		    var d2 = order_data.order_amounts;

			for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
		    for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;

			var placeholder = jQuery("#placeholder");

			var plot = jQuery.plot(placeholder, [ { label: "<?php echo esc_js( __( 'Number of sales', 'woocommerce' ) ) ?>", data: d }, { label: "<?php echo esc_js( __( 'Sales amount', 'woocommerce' ) ) ?>", data: d2, yaxis: 2 } ], {
				series: {
					lines: { show: true, fill: true },
					points: { show: true }
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#aaa',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#aaa',
					clickable: false,
					hoverable: true,
					markings: weekendAreas
				},
				xaxis: {
					mode: "time",
					timeformat: "%d %b",
					monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
					tickLength: 1,
					minTickSize: [1, "day"]
				},
				yaxes: [ { min: 0, tickSize: 10, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
		   		colors: ["#8a4b75", "#47a03e"]
		 	});

		 	placeholder.resize();

			<?php woocommerce_weekend_area_js(); ?>
			<?php woocommerce_tooltip_js(); ?>
			<?php woocommerce_datepicker_js(); ?>
		});
	</script>
	<?php
}


/**
 * Output the monthly sales chart.
 *
 * @access public
 * @return void
 */
function woocommerce_monthly_sales() {

	global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale;

	$first_year = $wpdb->get_var( $wpdb->prepare( "SELECT post_date FROM $wpdb->posts WHERE post_date != 0 ORDER BY post_date ASC LIMIT 1;" ) );
	
	$first_year = $first_year ? date( 'Y', strtotime( $first_year ) ) : date('Y');

	$current_year 	= isset( $_POST['show_year'] ) ? $_POST['show_year'] : date( 'Y', current_time( 'timestamp' ) );
	$start_date 	= strtotime( $current_year . '0101' );

	$total_sales = $total_orders = $order_items = 0;
	$order_counts = $order_amounts = array();

	for ( $count = 0; $count < 12; $count++ ) {
		$time = strtotime( date('Ym', strtotime( '+ ' . $count . ' MONTH', $start_date ) ) . '01' ) . '000';

		if ( $time > current_time( 'timestamp' ) . '000' )
			continue;

		$month = date( 'Ym', strtotime(date('Ym', strtotime('+ '.$count.' MONTH', $start_date)).'01') );

		$months_orders = $wpdb->get_row( $wpdb->prepare( "
			SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts

			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )

			WHERE 	meta.meta_key 		= '_order_total'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
			AND		'{$month}' 			= date_format(posts.post_date,'%Y%m')
		" ) );

		$order_counts[ $time ] 	= (int) $months_orders->total_orders;
		$order_amounts[ $time ] 	= (float) $months_orders->total_sales;

		$total_orders			+= (int) $months_orders->total_orders;
		$total_sales			+= (float) $months_orders->total_sales;

		// Count order items
		$order_items_serialized = $wpdb->get_col( $wpdb->prepare( "
			SELECT meta.meta_value AS items FROM {$wpdb->posts} AS posts

			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )

			WHERE 	meta.meta_key 		= '_order_items'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
			AND		'{$month}' 			= date_format(posts.post_date,'%Y%m')
		" ) );

		if ($order_items_serialized) {
			foreach ( $order_items_serialized as $order_items_array ) {
				$order_items_array = maybe_unserialize($order_items_array);
				if ( is_array( $order_items_array ) ) 
					foreach ( $order_items_array as $item ) 
						$order_items += (int) $item['qty'];
			}
		}

	}
	?>
	<form method="post" action="">
		<p><label for="show_year"><?php _e( 'Year:', 'woocommerce' ); ?></label>
		<select name="show_year" id="show_year">
			<?php
				for ( $i = $first_year; $i <= date( 'Y' ); $i++ ) 
					printf('<option value="%s" %s>%s</option>', $i, selected( $current_year, $i, false ), $i );
			?>
		</select> <input type="submit" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" /></p>
	</form>
	<div id="poststuff" class="woocommerce-reports-wrap">
		<div class="woocommerce-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e( 'Total sales for year', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo woocommerce_price($total_sales); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total orders for year', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_orders > 0 ) echo $total_orders . ' (' . $order_items . ' ' . __( 'items', 'woocommerce' ) . ')'; else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Average order total for year', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_orders>0) echo woocommerce_price($total_sales/$total_orders); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Average order items for year', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_orders>0) echo number_format($order_items/$total_orders, 2); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
		</div>
		<div class="woocommerce-reports-main">
			<div class="postbox">
				<h3><span><?php _e( 'Monthly sales for year', 'woocommerce' ); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php

	$order_counts_array = $order_amounts_array = array();
	
	foreach ( $order_counts as $key => $count )
		$order_counts_array[] = array( esc_js( $key ), esc_js( $count ) );
	
	foreach ( $order_amounts as $key => $amount )
		$order_amounts_array[] = array( esc_js( $key ), esc_js( $amount ) );

	$order_data = array( 'order_counts' => $order_counts_array, 'order_amounts' => $order_amounts_array );

	$chart_data = json_encode( $order_data );
	?>
	<script type="text/javascript">
		jQuery(function(){
			var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );

			var d = order_data.order_counts;
			var d2 = order_data.order_amounts;

			var placeholder = jQuery("#placeholder");

			var plot = jQuery.plot(placeholder, [ { label: "<?php echo esc_js( __( 'Number of sales', 'woocommerce' ) ) ?>", data: d }, { label: "<?php echo esc_js( __( 'Sales amount', 'woocommerce' ) ) ?>", data: d2, yaxis: 2 } ], {
				series: {
					lines: { show: true, fill: true },
					points: { show: true, align: "left" }
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#aaa',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#aaa',
					clickable: false,
					hoverable: true
				},
				xaxis: {
					mode: "time",
					timeformat: "%b %y",
					monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
					tickLength: 1,
					minTickSize: [1, "month"]
				},
				yaxes: [ { min: 0, tickSize: 10, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
		   		colors: ["#8a4b75", "#47a03e"]
		 	});

		 	placeholder.resize();

			<?php woocommerce_tooltip_js(); ?>
		});
	</script>
	<?php
}


/**
 * Output the top sellers chart.
 *
 * @access public
 * @return void
 */
function woocommerce_top_sellers() {

	global $start_date, $end_date, $woocommerce, $wpdb;

	$start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
	$end_date	= isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';

	if ( ! $start_date ) 
		$start_date = date( 'Ymd', strtotime( date( 'Ym', current_time( 'timestamp' ) ) . '01' ) );
	if ( ! $end_date )
		 $end_date = date( 'Ymd', current_time( 'timestamp' ) );

	$start_date = strtotime( $start_date );
	$end_date = strtotime( $end_date );

	// Get order ids and dates in range
	$orders = $wpdb->get_results( $wpdb->prepare( "
		SELECT posts.ID, posts.post_date FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
		AND 	post_date > '" . date('Y-m-d', $start_date ) . "'
		AND 	post_date < '" . date('Y-m-d', strtotime('+1 day', $end_date ) ) . "'
		ORDER BY post_date ASC
	" ) );

	$found_products = array();

	if ( $orders ) {
		foreach ($orders as $order) {
			$order_items = (array) get_post_meta( $order->ID, '_order_items', true );
			foreach ( $order_items as $item )
				$found_products[ $item['id'] ] = isset( $found_products[ $item['id'] ] ) ? $found_products[ $item['id'] ] + $item['qty'] : $item['qty'];
		}
	}

	asort( $found_products );
	$found_products = array_reverse( $found_products, true );
	$found_products = array_slice( $found_products, 0, 25, true );
	reset( $found_products );
	?>
	<form method="post" action="">
		<p><label for="from"><?php _e( 'From:', 'woocommerce' ); ?></label> <input type="text" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $start_date) ); ?>" /> <label for="to"><?php _e( 'To:', 'woocommerce' ); ?></label> <input type="text" name="end_date" id="to" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $end_date) ); ?>" /> <input type="submit" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" /></p>
	</form>
	<table class="bar_chart">
		<thead>
			<tr>
				<th><?php _e( 'Product', 'woocommerce' ); ?></th>
				<th><?php _e( 'Sales', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$max_sales = current( $found_products );
				foreach ( $found_products as $product_id => $sales ) {
					$width = $sales > 0 ? ( $sales / $max_sales ) * 100 : 0;
					$product_title = get_the_title( $product_id );
					
					if ( $product_title ) {
						$product_name = '<a href="' . get_permalink( $product_id ) . '">'. __( $product_title ) .'</a>';
						$orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=' . urlencode( $product_title ) . '&shop_order_status=completed,processing,on-hold' );
					} else {
						$product_name = __( 'Product does not exist', 'woocommerce' );
						$orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=&shop_order_status=completed,processing,on-hold' );
					}

					echo '<tr><th>' . $product_name . '</th><td width="1%"><span>' . esc_html( $sales ) . '</span></td><td class="bars"><a href="' . esc_url( $orders_link ) . '" style="width:' . esc_attr( $width ) . '%">&nbsp;</a></td></tr>';
				}
			?>
		</tbody>
	</table>
	<script type="text/javascript">
		jQuery(function(){
			<?php woocommerce_datepicker_js(); ?>
		});
	</script>
	<?php
}


/**
 * Output the top earners chart.
 *
 * @access public
 * @return void
 */
function woocommerce_top_earners() {

	global $start_date, $end_date, $woocommerce, $wpdb;

	$start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
	$end_date	= isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';

	if ( ! $start_date ) 
		$start_date = date( 'Ymd', strtotime( date('Ym', current_time( 'timestamp' ) ) . '01' ) );
	if ( ! $end_date ) 
		$end_date = date( 'Ymd', current_time( 'timestamp' ) );

	$start_date = strtotime( $start_date );
	$end_date = strtotime( $end_date );

	// Get order ids and dates in range
	$orders = $wpdb->get_results( $wpdb->prepare( "
		SELECT posts.ID, posts.post_date FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
		AND 	post_date > '" . date('Y-m-d', $start_date ) . "'
		AND 	post_date < '" . date('Y-m-d', strtotime('+1 day', $end_date ) ) . "'
		ORDER BY post_date ASC
	" ) );

	$found_products = array();

	if ( $orders ) {
		foreach ($orders as $order) {
			$order_items = (array) get_post_meta( $order->ID, '_order_items', true );
			foreach ( $order_items as $item ) {
				if ( isset( $item['line_total'] ) ) 
					$row_cost = $item['line_total'];
				else 
					$row_cost = $item['cost'] * $item['qty'];
				$found_products[ $item['id'] ] = isset( $found_products[ $item['id'] ] ) ? $found_products[ $item['id'] ] + $row_cost : $row_cost;
			}
		}
	}

	asort( $found_products );
	$found_products = array_reverse( $found_products, true );
	$found_products = array_slice( $found_products, 0, 25, true );
	reset( $found_products );
	?>
	<form method="post" action="">
		<p><label for="from"><?php _e( 'From:', 'woocommerce' ); ?></label> <input type="text" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $start_date) ); ?>" /> <label for="to"><?php _e( 'To:', 'woocommerce' ); ?></label> <input type="text" name="end_date" id="to" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $end_date) ); ?>" /> <input type="submit" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" /></p>
	</form>
	<table class="bar_chart">
		<thead>
			<tr>
				<th><?php _e( 'Product', 'woocommerce' ); ?></th>
				<th colspan="2"><?php _e( 'Sales', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$max_sales = current( $found_products );
				foreach ( $found_products as $product_id => $sales ) {
					$width = $sales > 0 ? ( round( $sales ) / round( $max_sales ) ) * 100 : 0;

					$product_title = get_the_title( $product_id );
					
					if ( $product_title ) {
						$product_name = '<a href="'.get_permalink( $product_id ).'">'. __( $product_title ) .'</a>';
						$orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=' . urlencode( $product_title ) . '&shop_order_status=completed,processing,on-hold' );
					} else {
						$product_name = __( 'Product no longer exists', 'woocommerce' );
						$orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=&shop_order_status=completed,processing,on-hold' );
					}

					echo '<tr><th>' . $product_name . '</th><td width="1%"><span>' . woocommerce_price( $sales ) . '</span></td><td class="bars"><a href="' . esc_url( $orders_link ) . '" style="width:' . esc_attr( $width ) . '%">&nbsp;</a></td></tr>';
				}
			?>
		</tbody>
	</table>
	<script type="text/javascript">
		jQuery(function(){
			<?php woocommerce_datepicker_js(); ?>
		});
	</script>
	<?php
}


/**
 * Output the product sales chart for single products.
 *
 * @access public
 * @return void
 */
function woocommerce_product_sales() {

	global $wpdb, $woocommerce;

	$chosen_product_ids = ( isset( $_POST['product_ids'] ) ) ? array_map( 'absint', (array) $_POST['product_ids'] ) : '';

	if ( $chosen_product_ids && is_array( $chosen_product_ids ) ) {

		$start_date = date( 'Ym', strtotime( '-12 MONTHS', current_time('timestamp') ) ) . '01';
		$end_date 	= date( 'Ymd', current_time( 'timestamp' ) );

		$max_sales = $max_totals = 0;
		$product_sales = $product_totals = array();

		// Get titles and ID's related to product
		$chosen_product_titles = array();
		$children_ids = array();

		foreach ( $chosen_product_ids as $product_id ) {
			$children = (array) get_posts( 'post_parent=' . $product_id . '&fields=ids&post_status=any&numberposts=-1' );
			$children_ids = $children_ids + $children;
			$chosen_product_titles[] = get_the_title( $product_id );
		}

		// Get order items
		$order_items = $wpdb->get_results( $wpdb->prepare( "
			SELECT meta.meta_value AS items, posts.post_date FROM {$wpdb->posts} AS posts

			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )

			WHERE 	meta.meta_key 		= '_order_items'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
			AND		posts.post_date		> date_sub( NOW(), INTERVAL 1 YEAR )
			ORDER BY posts.post_date ASC
		" ) );

		if ( $order_items ) {

			foreach ( $order_items as $order_item ) {

				$date 	= date( 'Ym', strtotime( $order_item->post_date ) );
				$items 	= maybe_unserialize( $order_item->items );

				foreach ( $items as $item ) {

					if ( ! in_array( $item['id'], $chosen_product_ids ) && ! in_array( $item['id'], $children_ids ) )
						continue;

					if ( isset( $item['line_total'] ) ) $row_cost = $item['line_total'];
					else $row_cost = $item['cost'] * $item['qty'];

					if ( ! $row_cost ) continue;

					$product_sales[ $date ] = isset( $product_sales[ $date ] ) ? $product_sales[$date] + $item['qty'] : $item['qty'];
					$product_totals[ $date ] = isset( $product_totals[ $date ] ) ? $product_totals[ $date ] + $row_cost : $row_cost;

					if ( $product_sales[ $date ] > $max_sales ) $max_sales = $product_sales[ $date ];
					if ( $product_totals[ $date ] > $max_totals ) $max_totals = $product_totals[ $date ];
				}

			}

		}
		?>
		<h4><?php printf( __( 'Sales for %s:', 'woocommerce' ), implode( ', ', $chosen_product_titles ) ); ?></h4>
		<table class="bar_chart">
			<thead>
				<tr>
					<th><?php _e( 'Month', 'woocommerce' ); ?></th>
					<th colspan="2"><?php _e( 'Sales', 'woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					if ( sizeof( $product_sales ) > 0 ) {
						foreach ( $product_sales as $date => $sales ) {
							$width = ($sales>0) ? (round($sales) / round($max_sales)) * 100 : 0;
							$width2 = ($product_totals[$date]>0) ? (round($product_totals[$date]) / round($max_totals)) * 100 : 0;
	
							$orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=' . urlencode( implode( ' ', $chosen_product_titles ) ) . '&m=' . date( 'Ym', strtotime( $date . '01' ) ) . '&shop_order_status=completed,processing,on-hold' );
	
							echo '<tr><th><a href="' . esc_url( $orders_link ) . '">' . date_i18n( 'F', strtotime( $date . '01' ) ) . '</a></th>
							<td width="1%"><span>' . esc_html( $sales ) . '</span><span class="alt">' . woocommerce_price( $product_totals[ $date ] ) . '</span></td>
							<td class="bars">
								<span style="width:' . esc_attr( $width ) . '%">&nbsp;</span>
								<span class="alt" style="width:' . esc_attr( $width2 ) . '%">&nbsp;</span>
							</td></tr>';
						}
					} else {
						echo '<tr><td colspan="3">' . __( 'No sales :(', 'woocommerce' ) . '</td></tr>';
					}
				?>
			</tbody>
		</table>
		<?php

	} else {
		?>
		<form method="post" action="">
			<p><select id="product_ids" name="product_ids[]" class="ajax_chosen_select_products" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>" style="width: 400px;"></select> <input type="submit" style="vertical-align: top;" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" /></p>
			<script type="text/javascript">
				jQuery(function(){
					// Ajax Chosen Product Selectors
					jQuery("select.ajax_chosen_select_products").ajaxChosen({
					    method: 	'GET',
					    url: 		'<?php echo admin_url('admin-ajax.php'); ?>',
					    dataType: 	'json',
					     afterTypeDelay: 100,
					    data:		{
					    	action: 		'woocommerce_json_search_products',
							security: 		'<?php echo wp_create_nonce("search-products"); ?>'
					    }
					}, function (data) {

						var terms = {};

					    jQuery.each(data, function (i, val) {
					        terms[i] = val;
					    });

					    return terms;
					});
				});
			</script>
		</form>
		<?php
	}
}


/**
 * Output the customer overview stats.
 *
 * @access public
 * @return void
 */
function woocommerce_customer_overview() {

	global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale;

	$total_customers = 0;
	$total_customer_sales = 0;
	$total_guest_sales = 0;
	$total_customer_orders = 0;
	$total_guest_orders = 0;

	$users_query = new WP_User_Query( array(
		'fields' => array('user_registered'),
		'role' => 'customer'
		) );
	$customers = $users_query->get_results();
	$total_customers = (int) sizeof($customers);

	$customer_orders = $wpdb->get_row( $wpdb->prepare( "
		SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	meta.meta_key 		= '_order_total'
		AND 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
		AND		posts.ID			IN (
			SELECT post_id FROM {$wpdb->postmeta}
			WHERE 	meta_key 		= '_customer_user'
			AND		meta_value		> 0
		)
	" ) );

	$total_customer_sales	= $customer_orders->total_sales;
	$total_customer_orders	= absint( $customer_orders->total_orders );

	$guest_orders = $wpdb->get_row( $wpdb->prepare( "
		SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	meta.meta_key 		= '_order_total'
		AND 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
		AND		posts.ID			IN (
			SELECT post_id FROM {$wpdb->postmeta}
			WHERE 	meta_key 		= '_customer_user'
			AND		meta_value		= 0
		)
	" ) );

	$total_guest_sales	= $guest_orders->total_sales;
	$total_guest_orders	= absint( $guest_orders->total_orders );
	?>
	<div id="poststuff" class="woocommerce-reports-wrap">
		<div class="woocommerce-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e( 'Total customers', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_customers>0) echo $total_customers; else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total customer sales', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_customer_sales>0) echo woocommerce_price($total_customer_sales); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total guest sales', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_guest_sales>0) echo woocommerce_price($total_guest_sales); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total customer orders', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_customer_orders>0) echo $total_customer_orders; else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total guest orders', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_guest_orders>0) echo $total_guest_orders; else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Average orders per customer', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_customer_orders>0 && $total_customers>0) echo number_format($total_customer_orders/$total_customers, 2); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
		</div>
		<div class="woocommerce-reports-main">
			<div class="postbox">
				<h3><span><?php _e( 'Signups per day', 'woocommerce' ); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php

	$start_date = strtotime('-30 days', current_time('timestamp'));
	$end_date = current_time('timestamp');
	$signups = array();

	// Blank date ranges to begin
	$count = 0;
	$days = ($end_date - $start_date) / (60 * 60 * 24);
	if ($days==0) $days = 1;

	while ($count < $days) :
		$time = strtotime(date('Ymd', strtotime('+ '.$count.' DAY', $start_date))).'000';

		$signups[ $time ] = 0;

		$count++;
	endwhile;

	foreach ($customers as $customer) :
		if (strtotime($customer->user_registered) > $start_date) :
			$time = strtotime(date('Ymd', strtotime($customer->user_registered))).'000';

			if (isset($signups[ $time ])) :
				$signups[ $time ]++;
			else :
				$signups[ $time ] = 1;
			endif;
		endif;
	endforeach;

	$signups_array = array();
	foreach ($signups as $key => $count) :
		$signups_array[] = array( esc_js( $key ), esc_js( $count ) );
	endforeach;

	$chart_data = json_encode($signups_array);
	?>
	<script type="text/javascript">
		jQuery(function(){
			var d = jQuery.parseJSON( '<?php echo $chart_data; ?>' );

			for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;

			var placeholder = jQuery("#placeholder");

			var plot = jQuery.plot(placeholder, [ { data: d } ], {
				series: {
					bars: {
						barWidth: 60 * 60 * 24 * 1000,
						align: "center",
						show: true
					}
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#aaa',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#aaa',
					clickable: false,
					hoverable: true,
					markings: weekendAreas
				},
				xaxis: {
					mode: "time",
					timeformat: "%d %b",
					monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
					tickLength: 1,
					minTickSize: [1, "day"]
				},
				yaxes: [ { position: "right", min: 0, tickSize: 1, tickDecimals: 0 } ],
		   		colors: ["#8a4b75"]
		 	});

		 	placeholder.resize();

			<?php woocommerce_weekend_area_js(); ?>
		});
	</script>
	<?php
}


/**
 * Output the stock overview stats.
 *
 * @access public
 * @return void
 */
function woocommerce_stock_overview() {

	global $start_date, $end_date, $woocommerce, $wpdb;

	// Low/No stock lists
	$lowstockamount = get_option('woocommerce_notify_low_stock_amount');
	if (!is_numeric($lowstockamount)) $lowstockamount = 1;

	$nostockamount = get_option('woocommerce_notify_no_stock_amount');
	if (!is_numeric($nostockamount)) $nostockamount = 0;

	$outofstock = array();
	$lowinstock = array();

	// Get low in stock simple/downloadable/virtual products. Grouped don't have stock. Variations need a separate query.
	$args = array(
		'post_type'			=> 'product',
		'post_status' 		=> 'publish',
		'posts_per_page' 	=> -1,
		'meta_query' => array(
			array(
				'key' 		=> '_manage_stock',
				'value' 	=> 'yes'
			),
			array(
				'key' 		=> '_stock',
				'value' 	=> $lowstockamount,
				'compare' 	=> '<=',
				'type' 		=> 'NUMERIC'
			)
		),
		'tax_query' => array(
			array(
				'taxonomy' 	=> 'product_type',
				'field' 	=> 'slug',
				'terms' 	=> array('simple'),
				'operator' 	=> 'IN'
			)
		)
	);
	$low_stock_products = (array) get_posts($args);

	// Get low stock product variations
	$args = array(
		'post_type'			=> 'product_variation',
		'post_status' 		=> 'publish',
		'posts_per_page' 	=> -1,
		'meta_query' => array(
			array(
				'key' 		=> '_stock',
				'value' 	=> $lowstockamount,
				'compare' 	=> '<=',
				'type' 		=> 'NUMERIC'
			),
			array(
				'key' 		=> '_stock',
				'value' 	=> array('', false, null),
				'compare' 	=> 'NOT IN'
			)
		)
	);
	$low_stock_variations = (array) get_posts($args);

	// Finally, get low stock variable products (where stock is set for the parent)
	$args = array(
		'post_type'			=> array('product'),
		'post_status' 		=> 'publish',
		'posts_per_page' 	=> -1,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' 		=> '_manage_stock',
				'value' 	=> 'yes'
			),
			array(
				'key' 		=> '_stock',
				'value' 	=> $lowstockamount,
				'compare' 	=> '<=',
				'type' 		=> 'NUMERIC'
			)
		),
		'tax_query' => array(
			array(
				'taxonomy' 	=> 'product_type',
				'field' 	=> 'slug',
				'terms' 	=> array('variable'),
				'operator' 	=> 'IN'
			)
		)
	);
	$low_stock_variable_products = (array) get_posts($args);

	// Merge results
	$low_in_stock = array_merge($low_stock_products, $low_stock_variations, $low_stock_variable_products);

	?>
	<div id="poststuff" class="woocommerce-reports-wrap halved">
		<div class="woocommerce-reports-left">
			<div class="postbox">
				<h3><span><?php _e( 'Low stock', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<?php
					if ( $low_in_stock ) {
						echo '<ul class="stock_list">';
						foreach ( $low_in_stock as $product ) {

							$stock 	= (int) get_post_meta( $product->ID, '_stock', true );
							$sku	= get_post_meta( $product->ID, '_sku', true );

							if ( $stock <= $nostockamount ) continue;

							$title = esc_html__( $product->post_title );

							if ( $sku )
								$title .= ' (' . __( 'SKU', 'woocommerce' ) . ': ' . esc_html( $sku ) . ')';

							if ( $product->post_type=='product' )
								$product_url = admin_url( 'post.php?post=' . $product->ID . '&action=edit' );
							else
								$product_url = admin_url( 'post.php?post=' . $product->post_parent . '&action=edit' );

							printf( '<li><a href="%s"><small>' .  _n('%d in stock', '%d in stock', $stock, 'woocommerce') . '</small> %s</a></li>', $product_url, $stock, $title );

						}
						echo '</ul>';
					} else {
						echo '<p>'.__( 'No products are low in stock.', 'woocommerce' ).'</p>';
					}
					?>
				</div>
			</div>
		</div>
		<div class="woocommerce-reports-right">
			<div class="postbox">
				<h3><span><?php _e( 'Out of stock', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<?php
					if ( $low_in_stock ) {
						echo '<ul class="stock_list">';
						foreach ( $low_in_stock as $product ) {

							$stock 	= (int) get_post_meta( $product->ID, '_stock', true );
							$sku	= get_post_meta( $product->ID, '_sku', true );

							if ( $stock > $nostockamount ) continue;

							$title = esc_html__( $product->post_title );

							if ( $sku )
								$title .= ' (' . __( 'SKU', 'woocommerce' ) . ': ' . esc_html( $sku ) . ')';

							if ( $product->post_type=='product' )
								$product_url = admin_url( 'post.php?post=' . $product->ID . '&action=edit' );
							else
								$product_url = admin_url( 'post.php?post=' . $product->post_parent . '&action=edit' );

							printf( '<li><a href="%s"><small>' .  _n('%d in stock', '%d in stock', $stock, 'woocommerce') . '</small> %s</a></li>', $product_url, $stock, $title );

						}
						echo '</ul>';
					} else {
						echo '<p>'.__( 'No products are out in stock.', 'woocommerce' ).'</p>';
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}


/**
 * Output the monthly tax stats.
 *
 * @access public
 * @return void
 */
function woocommerce_monthly_taxes() {
	global $start_date, $end_date, $woocommerce, $wpdb;

	$first_year = $wpdb->get_var( $wpdb->prepare( "SELECT post_date FROM $wpdb->posts WHERE post_date != 0 ORDER BY post_date ASC LIMIT 1;" ) );

	if ( $first_year )
		$first_year = date( 'Y', strtotime( $first_year ) );
	else
		$first_year = date( 'Y' );

	$current_year 	= isset( $_POST['show_year'] ) 	? $_POST['show_year'] 	: date( 'Y', current_time( 'timestamp' ) );
	$start_date 	= strtotime( $current_year . '0101' );

	$total_tax = $total_sales_tax = $total_shipping_tax = $count = 0;
	$taxes = $tax_rows = $tax_row_labels = array();

	for ( $count = 0; $count < 12; $count++ ) {

		$time = strtotime( date('Ym', strtotime( '+ ' . $count . ' MONTH', $start_date ) ) . '01' );

		if ( $time > current_time( 'timestamp' ) )
			continue;

		$month = date( 'Ym', strtotime( date( 'Ym', strtotime( '+ ' . $count . ' MONTH', $start_date ) ) . '01' ) );

		$gross = $wpdb->get_var( $wpdb->prepare( "
			SELECT SUM( meta.meta_value ) AS order_tax
			FROM {$wpdb->posts} AS posts
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )
			WHERE 	meta.meta_key 		= '_order_total'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
			AND		'{$month}' 			= date_format(posts.post_date,'%Y%m')
		" ) );

		$shipping = $wpdb->get_var( $wpdb->prepare( "
			SELECT SUM( meta.meta_value ) AS order_tax
			FROM {$wpdb->posts} AS posts
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )
			WHERE 	meta.meta_key 		= '_order_shipping'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
			AND		'{$month}' 			= date_format(posts.post_date,'%Y%m')
		" ) );

		$order_tax = $wpdb->get_var( $wpdb->prepare( "
			SELECT SUM( meta.meta_value ) AS order_tax
			FROM {$wpdb->posts} AS posts
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )
			WHERE 	meta.meta_key 		= '_order_tax'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
			AND		'{$month}' 			= date_format(posts.post_date,'%Y%m')
		" ) );

		$shipping_tax = $wpdb->get_var( $wpdb->prepare( "
			SELECT SUM( meta.meta_value ) AS order_tax
			FROM {$wpdb->posts} AS posts
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )
			WHERE 	meta.meta_key 		= '_order_shipping_tax'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
			AND		'{$month}' 			= date_format(posts.post_date,'%Y%m')
		" ) );

		$order_taxes = $wpdb->get_col( $wpdb->prepare( "
			SELECT meta.meta_value AS order_tax
			FROM {$wpdb->posts} AS posts
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )
			WHERE 	meta.meta_key 		= '_order_taxes'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
			AND		'{$month}' 			= date_format(posts.post_date,'%Y%m')
		" ) );

		$tax_rows = array();

		if ( $order_taxes ) {
			foreach ( $order_taxes as $order_tax_rows ) {
				$order_tax_rows = maybe_unserialize( $order_tax_rows );
				if ( $order_tax_rows )
					foreach ( $order_tax_rows as $tax_row )
						if ( isset( $tax_row['cart_tax'] ) ) {

							$tax_row_labels[] = $tax_row['label'];

							$tax_rows[ $tax_row['label'] ] = isset( $tax_rows[ $tax_row['label'] ] ) ? $tax_rows[ $tax_row['label'] ] + $tax_row['cart_tax'] + $tax_row['shipping_tax'] : $tax_row['cart_tax'] + $tax_row['shipping_tax'];

						}
			}
		}

		$taxes[ date( 'M', strtotime( $month . '01' ) ) ] = array(
			'gross'			=> $gross,
			'shipping'		=> $shipping,
			'order_tax' 	=> $order_tax,
			'shipping_tax' 	=> $shipping_tax,
			'total_tax' 	=> $shipping_tax + $order_tax,
			'tax_rows'		=> $tax_rows
		);

		$total_sales_tax += $order_tax;
		$total_shipping_tax += $shipping_tax;
	}
	$total_tax = $total_sales_tax + $total_shipping_tax;
	?>
	<form method="post" action="">
		<p><label for="show_year"><?php _e( 'Year:', 'woocommerce' ); ?></label>
		<select name="show_year" id="show_year">
			<?php
				for ( $i = $first_year; $i <= date('Y'); $i++ )
					printf( '<option value="%s" %s>%s</option>', $i, selected( $current_year, $i, false ), $i );
			?>
		</select> <input type="submit" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" /></p>
	</form>
	<div id="poststuff" class="woocommerce-reports-wrap">
		<div class="woocommerce-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e( 'Total taxes for year', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
						if ( $total_tax > 0 )
							echo woocommerce_price( $total_tax );
						else
							_e( 'n/a', 'woocommerce' );
					?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total product taxes for year', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
						if ( $total_sales_tax > 0 )
							echo woocommerce_price( $total_sales_tax );
						else
							_e( 'n/a', 'woocommerce' );
					?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total shipping tax for year', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
						if ( $total_shipping_tax > 0 )
							echo woocommerce_price( $total_shipping_tax );
						else
							_e( 'n/a', 'woocommerce' );
					?></p>
				</div>
			</div>
		</div>
		<div class="woocommerce-reports-main">
			<table class="widefat">
				<thead>
					<tr>
						<th><?php _e( 'Month', 'woocommerce' ); ?></th>
						<th class="total_row"><?php _e( 'Total Sales', 'woocommerce' ); ?> <a class="tips" data-tip="<?php _e("This is the sum of the 'Order Total' field within your orders.", 'woocommerce'); ?>" href="#">[?]</a></th>
						<th class="total_row"><?php _e( 'Total Shipping', 'woocommerce' ); ?> <a class="tips" data-tip="<?php _e("This is the sum of the 'Shipping Total' field within your orders.", 'woocommerce'); ?>" href="#">[?]</a></th>
						<th class="total_row"><?php _e( 'Total Product Taxes', 'woocommerce' ); ?> <a class="tips" data-tip="<?php _e("This is the sum of the 'Cart Tax' field within your orders.", 'woocommerce'); ?>" href="#">[?]</a></th>
						<th class="total_row"><?php _e( 'Total Shipping Taxes', 'woocommerce' ); ?> <a class="tips" data-tip="<?php _e("This is the sum of the 'Shipping Tax' field within your orders.", 'woocommerce'); ?>" href="#">[?]</a></th>
						<th class="total_row"><?php _e( 'Total Taxes', 'woocommerce' ); ?> <a class="tips" data-tip="<?php _e("This is the sum of the 'Cart Tax' and 'Shipping Tax' fields within your orders.", 'woocommerce'); ?>" href="#">[?]</a></th>
						<th class="total_row"><?php _e( 'Net profit', 'woocommerce' ); ?> <a class="tips" data-tip="<?php _e("Total sales minus shipping and tax.", 'woocommerce'); ?>" href="#">[?]</a></th>
						<?php
							$tax_row_labels = array_filter( array_unique( $tax_row_labels ) );
							foreach ( $tax_row_labels as $label )
								echo '<th class="tax_row">' . $label . '</th>';
						?>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<?php
							$total = array();

							foreach ( $taxes as $month => $tax ) {
								$total['gross'] = isset( $total['gross'] ) ? $total['gross'] + $tax['gross'] : $tax['gross'];
								$total['shipping'] = isset( $total['shipping'] ) ? $total['shipping'] + $tax['shipping'] : $tax['shipping'];
								$total['order_tax'] = isset( $total['order_tax'] ) ? $total['order_tax'] + $tax['order_tax'] : $tax['order_tax'];
								$total['shipping_tax'] = isset( $total['shipping_tax'] ) ? $total['shipping_tax'] + $tax['shipping_tax'] : $tax['shipping_tax'];
								$total['total_tax'] = isset( $total['total_tax'] ) ? $total['total_tax'] + $tax['total_tax'] : $tax['total_tax'];

								foreach ( $tax_row_labels as $label )
									if ( isset( $tax['tax_rows'][ $label ] ) )
										$total['tax_rows'][ $label ] = isset( $total['tax_rows'][ $label ] ) ? $total['tax_rows'][ $label ] + $tax['tax_rows'][ $label ] : $tax['tax_rows'][ $label ];

							}

							echo '
								<td>' . __( 'Total', 'woocommerce' ) . '</td>
								<td class="total_row">' . woocommerce_price( $total['gross'] ) . '</td>
								<td class="total_row">' . woocommerce_price( $total['shipping'] ) . '</td>
								<td class="total_row">' . woocommerce_price( $total['order_tax'] ) . '</td>
								<td class="total_row">' . woocommerce_price( $total['shipping_tax'] ) . '</td>
								<td class="total_row">' . woocommerce_price( $total['total_tax'] ) . '</td>
								<td class="total_row">' . woocommerce_price( $total['gross'] - $total['shipping'] - $total['total_tax'] ) . '</td>';

							foreach ( $tax_row_labels as $label )
								if ( isset( $total['tax_rows'][ $label ] ) )
									echo '<td class="tax_row">' . woocommerce_price( $total['tax_rows'][ $label ] ) . '</td>';
								else
									echo '<td class="tax_row">' .  woocommerce_price( 0 ) . '</td>';
						?>
					</tr>
					<tr>
						<th colspan="<?php echo 7 + sizeof( $tax_row_labels ); ?>"><button class="button toggle_tax_rows"><?php _e( 'Toggle tax rows', 'woocommerce' ); ?></button></th>
					</tr>
				</tfoot>
				<tbody>
					<?php
						foreach ( $taxes as $month => $tax ) {
							$alt = ( isset( $alt ) && $alt == 'alt' ) ? '' : 'alt';
							echo '<tr class="' . $alt . '">
								<td>' . $month . '</td>
								<td class="total_row">' . woocommerce_price( $tax['gross'] ) . '</td>
								<td class="total_row">' . woocommerce_price( $tax['shipping'] ) . '</td>
								<td class="total_row">' . woocommerce_price( $tax['order_tax'] ) . '</td>
								<td class="total_row">' . woocommerce_price( $tax['shipping_tax'] ) . '</td>
								<td class="total_row">' . woocommerce_price( $tax['total_tax'] ) . '</td>
								<td class="total_row">' . woocommerce_price( $tax['gross'] - $tax['shipping'] - $tax['total_tax'] ) . '</td>';

							foreach ( $tax_row_labels as $label )
								if ( isset( $tax['tax_rows'][ $label ] ) )
									echo '<td class="tax_row">' . woocommerce_price( $tax['tax_rows'][ $label ] ) . '</td>';
								else
									echo '<td class="tax_row">' .  woocommerce_price( 0 ) . '</td>';

							echo '</tr>';
						}
					?>
				</tbody>
			</table>
			<script type="text/javascript">
				jQuery('.toggle_tax_rows').click(function(){
					jQuery('.tax_row').toggle();
					jQuery('.total_row').toggle();
				});
				jQuery('.tax_row').hide();
			</script>
		</div>
	</div>
	<?php
}


/**
 * woocommerce_category_sales function.
 * 
 * @access public
 * @return void
 */
function woocommerce_category_sales() {
	global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale;

	$first_year = $wpdb->get_var( $wpdb->prepare( "SELECT post_date FROM $wpdb->posts WHERE post_date != 0 ORDER BY post_date ASC LIMIT 1;" ) );
	$first_year = ( $first_year ) ? date( 'Y', strtotime( $first_year ) ) : date( 'Y' );

	$current_year 	= isset( $_POST['show_year'] ) 	? $_POST['show_year'] 	: date( 'Y', current_time( 'timestamp' ) );
	$start_date 	= strtotime( $current_year . '0101' );
	
	$categories = get_terms( 'product_cat', array( 'parent' => 0 ) );
	?>
	<form method="post" action="" class="report_filters">
		<p>
			<label for="show_year"><?php _e( 'Show:', 'woocommerce' ); ?></label>
			<select name="show_year" id="show_year">
				<?php
					for ( $i = $first_year; $i <= date( 'Y' ); $i++ ) 
						printf( '<option value="%s" %s>%s</option>', $i, selected( $current_year, $i, false ), $i );
				?>
			</select>
		
			<select multiple="multiple" class="chosen_select" id="show_categories" name="show_categories[]" style="width: 300px;">
				<?php
					foreach ( $categories as $category ) {
						if ( $category->parent > 0 )
							$prepend = '&mdash; ';
						else
							$prepend = '';
						
						echo '<option value="' . $category->term_id . '" ' . selected( ! empty( $_POST['show_categories'] ) && in_array( $category->term_id, $_POST['show_categories'] ), true ) . '>' . $prepend . $category->name . '</option>';
					}
				?>
			</select>

			<input type="submit" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" />
		</p>
	</form>
	<?php

	$item_sales = array();

	for ( $count = 0; $count < 12; $count++ ) {
		$time = strtotime( date( 'Ym', strtotime( '+ ' . $count . ' MONTH', $start_date ) ) . '01' ) . '000';

		if ( $time > current_time( 'timestamp' ) . '000' )
			continue;

		$month = date( 'Ym', strtotime( date( 'Ym', strtotime( '+ '. $count . ' MONTH', $start_date ) ) . '01' ) );

		// Get order items
		$order_items_serialized = $wpdb->get_col( $wpdb->prepare( "
			SELECT meta.meta_value AS items FROM {$wpdb->posts} AS posts

			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )

			WHERE 	meta.meta_key 		= '_order_items'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
			AND		'{$month}' 			= date_format(posts.post_date,'%Y%m')
		" ) );

		if ( $order_items_serialized ) {
			foreach ( $order_items_serialized as $order_items_array ) {
				$order_items_array = maybe_unserialize( $order_items_array );
				if ( is_array( $order_items_array ) ) 
					foreach ( $order_items_array as $item ) {
						
						if ( ! isset( $item_sales[ $count ][ $item['id'] ] ) )
							$item_sales[ $count ][ $item['id'] ] = 0;
						
						if ( ! empty( $item['line_total'] ) )
							$item_sales[ $count ][ $item['id'] ] += $item['line_total'];
						
					}
			}
		}
	}
	
	if ( ! empty( $_POST['show_categories'] ) && sizeof( $_POST['show_categories'] ) > 0 ) {
	
	$show_categories = $include_categories = array_map( 'absint', $_POST['show_categories'] );
	
	foreach( $show_categories as $cat )
		$include_categories = array_merge( $include_categories, get_term_children( $cat, 'product_cat' ) );
	
	$categories = get_terms( 'product_cat', array( 'include' => array_unique( $include_categories ) ) );
	?>
	<div class="woocommerce-wide-reports-wrap">
		<table class="widefat">
			<thead>
				<tr>
					<th><?php _e( 'Category', 'woocommerce' ); ?></th>
					<?php 
						$column_count = 0;
						for ( $count = 0; $count < 12; $count++ ) : 
							if ( $count >= date ( 'm' ) && $current_year == date( 'Y' ) )	
								continue;
							$column_count++;
							?>
							<th><?php echo date( 'F', strtotime( '2012-' . ( $count + 1 ) . '-01' ) ); ?></th>
					<?php endfor; ?>
					<th><strong><?php _e( 'Total', 'woocommerce' ); ?></strong></th>
				</tr>
			</thead>
			<!--<tfoot>
				<tr>
					<th colspan="<?php echo $column_count + 2; ?>">
						<a class="button export-data" href="<?php echo add_query_arg( 'export', 'true' ); ?>"><?php _e( 'Export data', 'woocommerce' ); ?></a>
					</th>
				</tr>
			</tfoot>-->
			<tbody><?php
				// While outputting, lets store them for the chart
				$chart_data = $month_totals = $category_totals = array();
				$top_cat = $bottom_cat = $top_cat_name = $bottom_cat_name = '';
				
				for ( $count = 0; $count < 12; $count++ )
					if ( $count >= date( 'm' ) && $current_year == date( 'Y' ) )
						break;
					else
						$month_totals[ $count ] = 0;
				
				foreach ( $categories as $category ) {
					
					$cat_total = 0;
					$category_chart_data = $term_ids = array();
					
					$term_ids 		= get_term_children( $category->term_id, 'product_cat' );
					$term_ids[] 	= $category->term_id;
					$product_ids 	= get_objects_in_term( $term_ids, 'product_cat' );
					
					if ( $category->parent > 0 )
						$prepend = '&mdash; ';
					else
						$prepend = '';
					
					$category_sales_html = '<tr><th>' . $prepend . $category->name . '</th>';
	
					for ( $count = 0; $count < 12; $count++ ) {
						
						if ( $count >= date( 'm' ) && $current_year == date( 'Y' ) )	
							continue;
					
						if ( ! empty( $item_sales[ $count ] ) ) {
							$matches = array_intersect_key( $item_sales[ $count ], array_flip( $product_ids ) );
							$total = array_sum( $matches );
							$cat_total += $total;
						} else {
							$total = 0;
						}
							
						$month_totals[ $count ] += $total;
						
						$category_sales_html .= '<td>' . woocommerce_price( $total ) . '</td>';
						
						$category_chart_data[] = array( strtotime( date( 'Ymd', strtotime( '2012-' . ( $count + 1 ) . '-01' ) ) ) . '000', $total );
					}
					
					if ( $cat_total == 0 )
						continue;
					
					$category_totals[] = $cat_total;
					
					$category_sales_html .= '<td><strong>' . woocommerce_price( $cat_total ) . '</strong></td>';
					
					$category_sales_html .= '</tr>';
					
					echo $category_sales_html;
					
					$chart_data[ $category->name ] = $category_chart_data;
					
					if ( $cat_total > $top_cat ) {
						$top_cat = $cat_total;
						$top_cat_name = $category->name;
					}
					
					if ( $cat_total < $bottom_cat || $bottom_cat === '' ) {
						$bottom_cat = $cat_total;
						$bottom_cat_name = $category->name;
					}
	
				}
				
				sort( $category_totals );
				
				echo '<tr><th><strong>' . __( 'Total', 'woocommerce' ) . '</strong></th>';
				for ( $count = 0; $count < 12; $count++ )
					if ( $count >= date( 'm' ) && $current_year == date( 'Y' ) )
						break;
					else
						echo '<td><strong>' . woocommerce_price( $month_totals[ $count ] ) . '</strong></td>';
				echo '<td><strong>' .  woocommerce_price( array_sum( $month_totals ) ) . '</strong></td></tr>';
				
			?></tbody>
		</table>
	</div>
	
	<div id="poststuff" class="woocommerce-reports-wrap">
		<div class="woocommerce-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e( 'Top category', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
						echo $top_cat_name . ' (' . woocommerce_price( $top_cat ) . ')';
					?></p>
				</div>
			</div>
			<?php if ( sizeof( $category_totals ) > 1 ) : ?>
			<div class="postbox">
				<h3><span><?php _e( 'Worst category', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
						echo $bottom_cat_name . ' (' . woocommerce_price( $bottom_cat ) . ')';
					?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Category sales average', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
						if ( sizeof( $category_totals ) > 0 )
							echo woocommerce_price( array_sum( $category_totals ) / sizeof( $category_totals ) );
						else
							echo __( 'N/A', 'woocommerce' );
					?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Category sales median', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
						if ( sizeof( $category_totals ) == 0 )
							echo __( 'N/A', 'woocommerce' );
						elseif ( sizeof( $category_totals ) % 2 )
							echo woocommerce_price( 
								( 
									$category_totals[ floor( sizeof( $category_totals ) / 2 ) ] + $category_totals[ ceil( sizeof( $category_totals ) / 2 ) ] 
								) / 2 
							);
						else
							echo woocommerce_price( $category_totals[ sizeof( $category_totals ) / 2 ] );
					?></p>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<div class="woocommerce-reports-main">
			<div class="postbox">
				<h3><span><?php _e( 'Monthly sales by category', 'woocommerce' ); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(function(){
			
			<?php
				// Variables
				foreach ( $chart_data as $name => $data ) {
					$varname = 'cat_' . str_replace( '-', '_', sanitize_title( $name ) ) . '_data';
					echo 'var ' . $varname . ' = jQuery.parseJSON( \'' . json_encode( $data ) . '\' );';
				}
			?>

			var placeholder = jQuery("#placeholder");

			var plot = jQuery.plot(placeholder, [ 
				<?php 
				$labels = array();
				
				foreach ( $chart_data as $name => $data ) {
					$labels[] = '{ label: "' . esc_js( $name ) . '", data: ' . 'cat_' . str_replace( '-', '_', sanitize_title( $name ) ) . '_data }';
				}
				
				echo implode( ',', $labels );
				?>
			], {
				series: {
					lines: { show: true, fill: true },
					points: { show: true, align: "left" }
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#aaa',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#aaa',
					clickable: false,
					hoverable: true
				},
				xaxis: {
					mode: "time",
					timeformat: "%b %y",
					monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
					tickLength: 1,
					minTickSize: [1, "month"]
				},
				yaxes: [ { min: 0, tickDecimals: 2 } ]
		 	});

		 	placeholder.resize();

			<?php woocommerce_tooltip_js(); ?>
		});
	</script>
	<?php
	}
	?>
	<script type="text/javascript">
		jQuery(function(){
			jQuery("select.chosen_select").chosen();
		});
	</script>
	<?php
}

/**
 * woocommerce_coupon_sales function.
 * 
 * @access public
 * @return void
 */
function woocommerce_coupon_sales() {
	global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale;

	$first_year = $wpdb->get_var( $wpdb->prepare( "SELECT post_date FROM $wpdb->posts WHERE post_date != 0 AND post_type='shop_order' ORDER BY post_date ASC LIMIT 1;" ) );
	$first_year = ( $first_year ) ? date( 'Y', strtotime( $first_year ) ) : date( 'Y' );

	$current_year 	= isset( $_POST['show_year'] ) 	? $_POST['show_year'] 	: date( 'Y', current_time( 'timestamp' ) );
	$start_date 	= strtotime( $current_year . '0101' );
	
	$order_statuses = implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) );
	
	
	$coupons = $wpdb->get_col( $wpdb->prepare( "
								SELECT DISTINCT meta.meta_value FROM {$wpdb->postmeta} AS meta
								LEFT JOIN {$wpdb->posts} AS posts ON posts.ID = meta.post_id
								LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
								LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
								LEFT JOIN {$wpdb->terms} AS term USING( term_id )
								
								WHERE	meta.meta_key		= 'coupons'
								AND		posts.post_type		= 'shop_order'
								AND		posts.post_status	= 'publish'
								AND		tax.taxonomy		= 'shop_order_status'
								AND		term.slug			IN ('{$order_statuses}')
							" ) );
	
	 
	
	?>
	
	<form method="post" action="" class="report_filters">
		<p>
			<label for="show_year"><?php _e( 'Show:', 'woocommerce' ); ?></label>
			<select name="show_year" id="show_year">
				<?php
					for ( $i = $first_year; $i <= date( 'Y' ); $i++ ) 
						printf( '<option value="%s" %s>%s</option>', $i, selected( $current_year, $i, false ), $i );
				?>
			</select>
		
			<select multiple="multiple" class="chosen_select" id="show_coupons" name="show_coupons[]" style="width: 300px;">
				<?php
					foreach ( $coupons as $coupon ) {
						
						echo '<option value="' . $coupon . '" ' . selected( ! empty( $_POST['show_coupons'] ) && in_array( $coupon, $_POST['show_coupons'] ), true ) . '>' . $coupon . '</option>';
					}
				?>
			</select>

			<input type="submit" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" />
		</p>
	</form>
	
	<?php
	
	if ( ! empty( $_POST['show_coupons'] ) && count( $_POST['show_coupons'] ) > 0 ) :
	
	$coupons = $_POST['show_coupons'];
	
	$coupon_sales = $monthly_totals = array();

	foreach( $coupons as $coupon ) :
	
		$monthly_sales = $wpdb->get_results( $wpdb->prepare( "
			SELECT SUM(postmeta.meta_value) AS order_total, date_format(posts.post_date, '%%Y%%m') as month FROM {$wpdb->posts} AS posts
			
			INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID=postmeta.post_ID
			INNER JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			INNER JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS term USING( term_id )
			
			WHERE	postmeta.meta_key	= '_order_total'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('{$order_statuses}')
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND		'{$current_year}'	= date_format(posts.post_date,'%%Y')
			AND		posts.ID			IN (
												SELECT post_id FROM {$wpdb->postmeta} AS meta
												
												WHERE 	meta.meta_key 		= 'coupons'
												AND		meta.meta_value		= '%s'
											)
			GROUP BY month", $coupon ), OBJECT );
		
		foreach( $monthly_sales as $sales ) {
			$month = $sales->month;
			$coupon_sales[$coupon][$month] = $sales->order_total;
		}
		
		
	endforeach;
	?>
	<div class="woocommerce-wide-reports-wrap">
		<table class="widefat">
			<thead>
				<tr>
					<th><?php _e( 'Coupon', 'woocommerce' ); ?></th>
					<?php 
						$column_count = 0;
						for ( $count = 0; $count < 12; $count++ ) : 
							if ( $count >= date ( 'm' ) && $current_year == date( 'Y' ) )	
								continue;
							$month = date( 'Ym', strtotime( date( 'Ym', strtotime( '+ '. $count . ' MONTH', $start_date ) ) . '01' ) );
							
							// set elements before += them below
							$monthly_totals[$month] = 0;
							
							$column_count++;
							?>
							<th><?php echo date( 'F', strtotime( '2012-' . ( $count + 1 ) . '-01' ) ); ?></th>
					<?php endfor; ?>
					<th><strong><?php _e( 'Total', 'woocommerce' ); ?></strong></th>
				</tr>
			</thead>
			
			<tbody><?php
				
				// save data for chart while outputting
				$chart_data = $coupon_totals = array();
				
				foreach( $coupon_sales as $coupon_code => $sales ) {
					
					echo '<tr><th>' . esc_html( $coupon_code ) . '</th>';
					
					for ( $count = 0; $count < 12; $count ++ ) {
						
						if ( $count >= date ( 'm' ) && $current_year == date( 'Y' ) )	
								continue;
						
						$month = date( 'Ym', strtotime( date( 'Ym', strtotime( '+ '. $count . ' MONTH', $start_date ) ) . '01' ) );
						
						$amount = isset( $sales[$month] ) ? $sales[$month] : 0;
						echo '<td>' . woocommerce_price( $amount ) . '</td>';
						
						$monthly_totals[$month] += $amount;
						
						$chart_data[$coupon_code][] = array( strtotime( date( 'Ymd', strtotime( $month . '01' ) ) ) . '000', $amount );
				
					}
						
					echo '<td><strong>' . woocommerce_price( array_sum( $sales ) ) . '</strong></td>';
					
					// total sales across all months
					$coupon_totals[$coupon_code] = array_sum( $sales );
					
					echo '</tr>';
					
				}
				
				$top_coupon_name = current( array_keys( $coupon_totals, max( $coupon_totals ) ) );
				$top_coupon_sales = $coupon_totals[$top_coupon_name];
				
				$worst_coupon_name = current( array_keys( $coupon_totals, min( $coupon_totals ) ) );
				$worst_coupon_sales = $coupon_totals[$worst_coupon_name];
				
				$median_coupon_sales = array_values( $coupon_totals );
				sort($median_coupon_sales);
				
				echo '<tr><th><strong>' . __( 'Total', 'woocommerce' ) . '</strong></th>';
				
				foreach( $monthly_totals as $month => $totals )
					echo '<td><strong>' . woocommerce_price( $totals ) . '</strong></td>';
				
				echo '<td><strong>' .  woocommerce_price( array_sum( $monthly_totals ) ) . '</strong></td></tr>';
				
			?></tbody>
		</table>
	</div>
	
	<div id="poststuff" class="woocommerce-reports-wrap">
		<div class="woocommerce-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e( 'Top coupon', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
						echo $top_coupon_name . ' (' . woocommerce_price( $top_coupon_sales ) . ')';
					?></p>
				</div>
			</div>
			<?php if ( sizeof( $coupon_totals ) > 1 ) : ?>
			<div class="postbox">
				<h3><span><?php _e( 'Worst coupon', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
						echo $worst_coupon_name . ' (' . woocommerce_price( $worst_coupon_sales ) . ')';
					?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Coupon sales average', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
							echo woocommerce_price( array_sum( $coupon_totals ) / count( $coupon_totals ) );
					?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Coupon sales median', 'woocommerce' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php
						if ( count( $median_coupon_sales ) == 2 )
							echo __( 'N/A', 'woocommerce' );
						elseif ( count( $median_coupon_sales ) % 2 )
							echo woocommerce_price( 
								( 
									$median_coupon_sales[ floor( count( $median_coupon_sales ) / 2 ) ] + $median_coupon_sales[ ceil( count( $median_coupon_sales ) / 2 ) ] 
								) / 2 
							);
						else
							
							echo woocommerce_price( $median_coupon_sales[ count( $median_coupon_sales ) / 2 ] );
					?></p>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<div class="woocommerce-reports-main">
			<div class="postbox">
				<h3><span><?php _e( 'Monthly sales by coupon', 'woocommerce' ); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(function(){
			
			<?php
				// Variables
				foreach ( $chart_data as $name => $data ) {
					$varname = 'coupon_' . str_replace( '-', '_', sanitize_title( $name ) ) . '_data';
					echo 'var ' . $varname . ' = jQuery.parseJSON( \'' . json_encode( $data ) . '\' );';
				}
			?>

			var placeholder = jQuery("#placeholder");

			var plot = jQuery.plot(placeholder, [ 
				<?php 
				$labels = array();
				
				foreach ( $chart_data as $name => $data ) {
					$labels[] = '{ label: "' . esc_js( $name ) . '", data: ' . 'coupon_' . str_replace( '-', '_', sanitize_title( $name ) ) . '_data }';
				}
				
				echo implode( ',', $labels );
				?>
			], {
				series: {
					lines: { show: true, fill: true },
					points: { show: true, align: "left" }
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#aaa',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#aaa',
					clickable: false,
					hoverable: true
				},
				xaxis: {
					mode: "time",
					timeformat: "%b %y",
					monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
					tickLength: 1,
					minTickSize: [1, "month"]
				},
				yaxes: [ { min: 0, tickDecimals: 2 } ]
		 	});

		 	placeholder.resize();

			<?php woocommerce_tooltip_js(); ?>
		});
	</script>
	<?php
	endif; // end POST check
	?>
	<script type="text/javascript">
		jQuery(function(){
			jQuery("select.chosen_select").chosen();
		});
	</script>
	<?php
}