<?php
namespace AssetFinder;

$asset_finder = new \AssetFinder\AssetCollector();

class AssetCollector {
	private $debug = true;

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		$this->add_test_assets();
		$now_timestamp = current_time( 'timestamp' );
		$qs_timestamp = isset( $_GET[ 'afts' ] ) ? intval( $_GET[ 'afts' ] ) : 0;
		if ( $now_timestamp < $qs_timestamp ) {
			// the query string time stamp is in the future - this will only be true for 5 minutes after the admin settings page is loaded and should prevent execution on accidentally indexed/bookmarked URLs
			show_admin_bar( false );
			add_action( 'wp_head', array( $this, 'get_assets_in_page' ) );
		}
	}

	public function get_assets_in_page() {
		$json = '';
		$assets = array();
		$assets['scripts'] = $this->get_scripts_in_page();
		$assets['styles'] = $this->get_styles_in_page();
		$json = json_encode( $assets );
		$this->create_web_script( $json );
	}

	private function create_web_script( $message ) {
		echo "<script type='text/javascript'>
		var sendMessage = function ( msg ) {
			window.parent.postMessage( msg, '*' );
		};
		sendMessage( JSON.stringify(" . $message . ") );
		</script>";
	}

	private function get_scripts_in_page() {
		$all = wp_scripts()->registered;
		$output = array();
		foreach( $all as $slug => $elem ) {
			if ( ( '' !== trim( $elem->src ) ) && ( false === strpos( $elem->src, 'wp-admin/' ) ) ) {
				$footer = 0;
				if ( isset( $elem->extra['group'] ) && ( 1 === intval( $elem->extra['group'] ) ) ) {
					$footer = 1;
				}
				$output[ $slug ] = array(
					'handle' => $elem->handle,
					'src' => $elem->src,
					'footer' => $footer
				);
			}
		}
		return $output;
	}

	private function get_styles_in_page() {
		$all = wp_styles()->registered;
		$output = array();
		foreach( $all as $slug => $elem ) {
			if ( ( '' !== trim( $elem->src ) ) && ( false === strpos( $elem->src, 'wp-admin/' ) ) ) {
				$media = '';
				if ( isset( $elem->args ) ) {
					$media = trim( $elem->args );
				}
				$output[ $slug ] = array(
					'handle' => $elem->handle,
					'src' => $elem->src,
					'media' => $media
				);
			}
		}
		return $output;
	}

	/**
	* Add some styles and scripts to the queue to test
	*/
	private function add_test_assets() {
		if ( true === $this->debug ) {
			wp_enqueue_style( 'asset_finder_style_test', ASSET_FINDER_URI . 'css/af_test.css', array(), 'v.1.0.0', 'screen' );
			wp_enqueue_script( 'asset_finder_script_head', ASSET_FINDER_URI . 'js/af_test_head.js', array(), 'v.1.0.1', false );
			wp_enqueue_script( 'asset_finder_script_foot', ASSET_FINDER_URI . 'js/af_test_foot.js', array(), 'v.1.0.5', true );
		}
	}

}