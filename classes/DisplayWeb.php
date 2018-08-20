<?php
namespace AssetFinder;
/**
* Next steps:
* 	- add changes to options
* 	- read changes from options
* 	- parse JSON and remove/late-load styles and scripts as requested
*/

$asset_finder = new \AssetFinder\DisplayWeb();

class DisplayWeb {
	private $debug = true;
	private $styles = [];

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		add_action( 'wp_print_styles', array( $this, 'modify_asset_loading' ), 100, 0 );
		add_action( 'wp_footer', array( $this, 'lateload_styles' ), 10, 0 );
		wp_enqueue_script( 'asset_finder_lateload', ASSET_FINDER_URI . 'js/web.js', array(), 'v.1.0.0', false );
		wp_enqueue_script( 'asset_finder_webtest', ASSET_FINDER_URI . 'js/test.js', array(), 'v.1.0.1', false );
	}

	/**
	*
	*/
	public function modify_asset_loading() {
		// get option
		// iterate through styles and scripts
		// foreach style { $this->handle_style( $handle, $action ) }
		// foreach script { $this->handle_script( $handle, $action ) }
		// $this->handle_style( 'twentyseventeen-style', 1 ); //twentyseventeen/style.css
		// $this->handle_script( 'asset_finder_webtest', 1 );
	}

	private function handle_style( $handle, $action ) {
		if ( 0 === $action ) {
			$this->remove_style( $handle );
		} else {
			$this->delay_style( $handle );
		}
	}

	private function handle_script( $handle, $action ) {
		if ( 0 === $action ) {
			$this->remove_script( $handle );
		} else {
			$this->delay_script( $handle );
		}
	}

	private function remove_style( $handle ) {
		wp_dequeue_style( $handle ); // remove style
		wp_deregister_style( $handle );
	}

	private function delay_style( $handle ) {
		global $wp_styles;
		// get original media type so it can be set back
		$media = $wp_styles->registered[ $handle ]->args;
		// change the media type so it loads but isn't processed on screen
		$wp_styles->registered[ $handle ]->args = 'lateload';
		$this->styles[ $handle ] = $media;
	}

	public function lateload_styles() {
		// cmj: enqueue this inline
		echo '<script type="text/javascript">' . "\n";
		foreach ( $this->styles as $handle => $media ) {
			echo "af_lateload( '" . esc_js( $handle ) . "', '" . esc_js( $media ) . "' )\n";
		}
		echo '</script>';
	}

	private function remove_script( $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}

	private function delay_script( $handle ) {
		global $wp_scripts;
		$wp_scripts->registered[ $handle ]->extra['group'] = 1;
	}

}