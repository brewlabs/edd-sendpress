<?php
class SendPress_EDD_Signup{

	private static $instance;

	function __construct() {
		add_action('plugins_loaded',array( $this, 'load' ));
	}

	function load(){
		if( $this->check_required_classes() ){
			add_action('init',array( $this, 'init' ));
		}
	}

	function init(){
		//both plugins are here, lets set up everything
		add_filter('edd_settings_misc', array( $this, 'add_settings' ));

		add_action('edd_purchase_form_before_submit', array( $this, 'edd_fields' ), 100);
		//add_action('edd_checkout_before_gateway', array( $this, 'sendpress_edd_check_for_email_signup'), 5, 2);

	}

	static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			$class_name = __CLASS__;
			self::$instance = new $class_name;
		}
		return self::$instance;
	}

	// adds the settings to the Misc section
	function add_settings($settings) {
	  
	  $sp_settings = array(
			array(
				'id' => 'sendpress_edd_settings',
				'name' => '<strong>' . __('SendPress Settings', 'sendpress_edd') . '</strong>',
				'desc' => __('Configure SendPress Integration Settings', 'sendpress_edd'),
				'type' => 'header'
			),
/*
array(
	'id' => 'sendpress_edd_license_key',
	'name' => __('License Key', 'sendpress_edd'),
	'desc' => __('Enter your license for EDD SendPress to receive automatic upgrades', 'sendpress_edd'),
	'type' => 'text',
	'size' => 'regular'
),
**/
			array(
				'id' => 'sendpress_edd_list',
				'name' => __('Choose a list', 'sendpress_edd'),
				'desc' => __('Select the list you wish to subscribe buyers to', 'sendpress_edd'),
				'type' => 'select',
				'options' => $this->get_sendpress_lists()
			),
			array(
				'id' => 'sendpress_edd_label',
				'name' => __('Checkout Label', 'sendpress_edd'),
				'desc' => __('This is the text shown next to the signup option', 'sendpress_edd'),
				'type' => 'text',
				'size' => 'regular'
			)
		);
		
		return array_merge($settings, $sp_settings);
	}

	function get_sendpress_lists(){

		$lists = array();
		$args = array(
        	'post_type' => 'sendpress_list',
        	'post_status' => array('publish','draft')
        );
        $query = new WP_Query( $args );

		if($query->have_posts()){
			while($query->have_posts()){
				$query->the_post();
				$lists[get_the_id()] = get_the_title();
			}
		}
		wp_reset_postdata();

		return $lists;

	}

	// displays the sendpress checkbox
	function edd_fields() {
		global $edd_options;
		if( isset( $edd_options['sendpress_edd_list'] ) && strlen( trim( $edd_options['sendpress_edd_list'] ) ) > 0 ) {
		ob_start(); ?>
			<p>
				<input name="sendpress_edd_signup" id="sendpress_edd_signup" type="checkbox" checked="checked"/>
				<label for="sendpress_edd_signup"><?php echo !empty($edd_options['sendpress_edd_label']) ? $edd_options['sendpress_edd_label'] : __('Sign up for our mailing list', 'sendpress_edd'); ?></label>
			</p>
			<?php
		}
		echo ob_get_clean();
	}

	// checks whether a user should be signed up for the sendpress list
	function sendpress_edd_check_for_email_signup($posted, $user_info) {

		if( isset($posted['sendpress_edd_signup']) ) {
			$email = $user_info['email'];
			$first = $user_info['first_name'];
			$last = $user_info['last_name'];
			SendPress_EDD_Signup::subscribe_email($email, $first, $last);
		}
	}

	function subscribe_email($email, $first, $last){
		global $edd_options;
		//error_log('SendPress List = '.$edd_options['sendpress_edd_list']);
		if( isset( $edd_options['sendpress_edd_list'] ) && strlen( trim( $edd_options['sendpress_edd_list'] ) ) > 0 ) {
			SendPress_Data::subscribe_user($edd_options['sendpress_edd_list'], $email, $first, $last);
		}
	}

	function check_required_classes(){

		$ret = true;
		if( !class_exists('SendPress') ){
			add_action('admin_notices',array( $this, 'notice_sedpress_missing' ));
			$ret = false;
		}
		if( !class_exists('Easy_Digital_Downloads') ){
			add_action('admin_notices',array( $this, 'notice_edd_missing' ));
			$ret = false;
		}

		return $ret;
	}

	function notice_sedpress_missing(){
	    echo '<div class="error">
	       <p><a href="http://sendpress.com" target="_blank">SendPress</a> needs to be installed for Easy Digital Downloads - SendPress to work.</p>
	    </div>';
	}

	function notice_edd_missing(){
	    echo '<div class="error">
	       <p><a href="http://easydigitaldownloads.com" target="_blank">Easy Digital Downloads</a> needs to be installed for Easy Digital Downloads - SendPress to work.</p>
	    </div>';
	}

	/**
     * plugin_activation
     * 
     * @access public
     *
     * @return mixed Value.
     */
	function plugin_activation(){
		
	}

	/**
	*
	*	Nothing going on here yet
	*	@static
	*/
	function plugin_deactivation(){
		
	} 

}
//moving this outside the class, hopefully to fix this issue
add_action('edd_checkout_before_gateway', array( 'SendPress_EDD_Signup', 'sendpress_edd_check_for_email_signup'), 5, 2);

