<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * @todo replace all occurrences of the string "template" with a string of your choice
 * @todo also rename in charts-loader.php
 */

class DT_Template_Metrics_Chart_Template extends DT_Metrics_Chart_Base
{
    public $base_slug = 'example_metrics'; // lowercase
    public $base_title = "Example Metrics";

    public $title = 'Template';
    public $slug = 'template'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = 'one-page-chart-template.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $url_path = dt_get_url_path();

        // only load scripts if exact url
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }


    /**
     * Load scripts for the plugin
     */
    public function scripts() {
        wp_enqueue_script( 'dt_'.$this->slug.'_script', trailingslashit( plugin_dir_url( __FILE__ ) ) . $this->js_file_name, [
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts'
        ], filemtime( plugin_dir_path( __FILE__ ) .$this->js_file_name ), true );

        // Localize script with array data
        wp_localize_script(
            'dt_'.$this->slug.'_script', $this->js_object_name, [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug",
                'base_slug' => $this->base_slug,
                'slug' => $this->slug,
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'stats' => [
                    // add preload stats data into arrays here
                ],
                'translations' => [
                    "title" => $this->title,
                    "Sample API Call" => __( "Sample API Call" )
                ]
            ]
        );
    }

    public function add_api_routes() {
        $namespace = "dt-metrics/$this->base_slug/$this->slug";
        register_rest_route(
            $namespace, '/sample', [
                'methods'  => 'POST',
                'callback' => [ $this, 'sample' ],
            ]
        );
    }

    public function sample( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, 'Missing auth.' );
        }
        $params = $request->get_params();
        if ( isset( $params['button_data'] ) ) {
            // Do something
            $results = $params['button_data'];
            return $results;
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

}
