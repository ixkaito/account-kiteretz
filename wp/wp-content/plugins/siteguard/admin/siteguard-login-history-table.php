<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SiteGuard_LoginHistory_Table extends WP_List_Table {

	function __construct( ) {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'event',   //singular name of the listed records
			'plural'   => 'events',  //plural name of the listed records
			'ajax'	   => false,     //does this table support ajax?
		) );
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'operation':
				return SiteGuard_LoginHistory::convert_operation( $item[ $column_name ] );
			case 'time':
			case 'login_name':
			case 'ip_address':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function get_columns( ) {
		$columns = array(
			#'cb'         => '<input type="checkbox" />', //Render a checkbox instead of text
			'time'       => esc_html__( 'Date Time',  'siteguard' ),
			'operation'  => esc_html__( 'Operation',  'siteguard' ),
			'login_name' => esc_html__( 'Login Name', 'siteguard' ),
			'ip_address' => esc_html__( 'IP Address', 'siteguard' ),
		);
		return $columns;
	}

	function get_sortable_columns( ) {
		$sortable_columns = array(
			'time'       => array( 'id', true ),    //true means it's already sorted
			'operation'  => array( 'operation', false ), //true means it's already sorted
			'login_name' => array( 'login_name', false ),
			'ip_address' => array( 'ip_address', false ),
		);
		return $sortable_columns;
	}

	function get_bulk_actions( ) {
		#$actions = array(
		#	'delete' => __( 'Delete' ),
		#);
		$actions = array();
		return $actions;
	}


	function process_bulk_action( ) {
		return;
	}

	function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to id
		$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc'; //If no order, default to desc
		if ( 'id' == $orderby ) {
			$result = ( $a > $b ? 1 : ( $a < $b ? -1 : 0 ) );
		} else {
			$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); //Determine sort order
		}
		return ( 'asc' == $order ) ? $result : -$result; //Send final sort direction to usort
	}

	function prepare_items( ) {
		global $login_history;

		$per_page = 10;

		$columns  = $this->get_columns( );
		$hidden   = array();
		$sortable = $this->get_sortable_columns( );

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action( );

		$data = $login_history->get_history( );

		$total_items = count( $data );
		$current_page = $this->get_pagenum( );

		if ( $total_items > 0 ) {
			usort( $data, array( $this, 'usort_reorder' ) );
			$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		}

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,                     //WE have to calculate the total number of items
			'per_page'    => $per_page,                        //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page ), //WE have to calculate the total number of pages
		) );
	}
}
?>
