<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */




 //***************** creating a registration form in bellow  *******************
/*
* creating a shortcode for add registraion form 
*/
add_shortcode( 'registration_form', 'ced_register_short_callback_function' );
function ced_register_short_callback_function(){
	return'
	<table style="margin:0px auto;width:800px;">
	  <tr>
		<th><label for="firstname"><b>First Name</b></label></th>
		<td><input type="text" placeholder="Enter First Name" name="firstname" id="firstname" required><span id="fnamemsg" style="color:red;"></span></td>
	  </tr>
	  <tr>
		<th><label for="lastname"><b>Last Name</b></label></th>
		<td><input type="text" placeholder="Enter Last Name" name="lastname" id="lastname" required><span id="lnamemsg" style="color:red;"></span></td>
	  </tr>
	  <tr>
		<th><label for="email"><b>Email</b></label></th>
		<td><input type="email" placeholder="Enter Email" name="email" id="email" required><span id="emailmsg" style="color:red;"></span></td>
	  </tr>
	  <tr>
		<th><label for="password"><b>Password</b></label></th>
		<td><input type="password" placeholder="Enter Password" name="password" id="password" required><span id="passmsg" style="color:red;"></span><td>
	  </tr>
	  <tr>
	  	<td colspan="2"><button type="submit" id="registerbtn">Register</button></td>
	  </tr>
	</table>
  ';
}


add_action('wp_footer', 'ced_wp_footer');  //for write js we required 'wp_header' or 'wp_footer' hook in theme
function ced_wp_footer(){
?>
<script>
		jQuery(document).on('click','#registerbtn',function(){
			var firstname = jQuery('#firstname').val();
			var lastname = jQuery('#lastname').val();
			var email = jQuery('#email').val();
			var password = jQuery('#password').val();
			var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
			if(firstname == ""){
				jQuery("#fnamemsg").html("please enter your first name");
				return false;
			}else{
				jQuery("#fnamemsg").html("");
			}
			if(lastname == ""){
				jQuery("#lnamemsg").html("please enter your last name");
				return false;
			}else{
				jQuery("#lnamemsg").html("");
			}
			// for email Checking
            if(email=="")
            {
                jQuery('#emailmsg').html("Please enter Your email");
                return false;
            }
            else if(email!="")
            {
                var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
                if(email.match(mailformat))
                {
                    jQuery('#emailmsg').html("");
                }
                else
                {
                    jQuery('#emailmsg').html("Please enter Valid email");
                    return false;
                }
            }
			if(password == ""){
				jQuery("#passmsg").html("please enter your password");
				return false;
			}else{
				jQuery("#passmsg").html("");
			}
			jQuery.ajax({
				type: "POST",
				url:  "<?php echo admin_url('admin-ajax.php'); ?>",
				data: 
					{
						'action': 'ced_save_registration_data',
						firstname : firstname,
						lastname:lastname,
						email:email,
						password:password
					},
					success: function(response){
						alert(response);
						// window.location.reload(true);
					}
            });

		});
</script>

<?php
}
add_action('wp_ajax_ced_save_registration_data', 'ced_save_registration_data'); //its work If Login
add_action('wp_ajax_nopriv_ced_save_registration_data', 'ced_save_registration_data'); //its work if not login

function ced_save_registration_data(){
	// print_r($_POST);
	$firstname = isset($_POST['firstname'])?$_POST['firstname']:'';
	$lastname = isset($_POST['lastname'])?$_POST['lastname']:'';
	$email = isset($_POST['email'])?$_POST['email']:'';
	$password = isset($_POST['password'])?$_POST['password']:'';
	
	$user_data = array(
        'user_login' => $firstname,
        'user_pass'  => $password,
        'user_email' => $email,
        'role'       => 'customer',
        'first_name' => $firstname,
        'last_name'  => $lastname,
    );

    $user_id  = wp_insert_user( $user_data );
	// echo $user_id;
	die();
}


//***************** creating a login form in bellow  *******************

add_shortcode( 'login_form', 'ced_login_short_callback_function' );
function ced_login_short_callback_function(){
	return'
	<table style="margin:0px auto;width:800px;">
	  <tr>
		<th><label for="email"><b>Email</b></label></th>
		<td><input type="email" placeholder="Enter Email" name="email" id="email" required><span id="emailmsg" style="color:red;"></span></td>
	  </tr>
	  <tr>
		<th><label for="password"><b>Password</b></label></th>
		<td><input type="password" placeholder="Enter Password" name="password" id="password" required><span id="passmsg" style="color:red;"></span><td>
	  </tr>
	  <tr>
	  	<td colspan="2"><button type="submit" id="loginbtn">Login</button></td>
	  </tr>
	</table>
  ';
}


add_action('wp_footer', 'ced_wp_log_footer');  //for write js we required 'wp_header' or 'wp_footer' hook in theme
function ced_wp_log_footer(){
?>
<script>
		jQuery(document).on('click','#loginbtn',function(){
			var email = jQuery('#email').val();
			var password = jQuery('#password').val();
            if(email=="")
            {
                jQuery('#emailmsg').html("Please enter Your email");
                return false;
            }
            else if(email!="")
            {
                var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
                if(email.match(mailformat))
                {
                    jQuery('#emailmsg').html("");
                }
                else
                {
                    jQuery('#emailmsg').html("Please enter Valid email");
                    return false;
                }
            }
			if(password == ""){
				jQuery("#passmsg").html("please enter your password");
				return false;
			}else{
				jQuery("#passmsg").html("");
			}
			jQuery.ajax({
				type: "POST",
				url:  "<?php echo admin_url('admin-ajax.php'); ?>",
				data: 
					{
						'action': 'ced_user_login_data',
						email:email,
						password:password
					},
					success: function(response){
						// alert(response);
						document.location.href = response;
						// window.location.replace(response);
						// window.location.reload(true);
					}
            });

		});
</script>
<?php
}
add_action('wp_ajax_ced_user_login_data', 'ced_user_login_data'); 
add_action('wp_ajax_nopriv_ced_user_login_data', 'ced_user_login_data'); //its work if not login

function ced_user_login_data(){
	// print_r($_POST);
	$email = isset($_POST['email'])?$_POST['email']:'';
	$password = isset($_POST['password'])?$_POST['password']:'';
	$user = get_user_by('email', $email);
	wp_clear_auth_cookie();
	wp_set_current_user($user->data->ID);
	wp_set_auth_cookie($user->data->ID);

	// $redirect_to = home_url();
	// wp_safe_redirect( $redirect_to );
	if ( in_array( 'administrator', $user->roles ) || in_array( 'editor', $user->roles ) || in_array( 'author', $user->roles ) ) {
		// Admins, editors and authors are redirected to the WordPress dashboard
		$redirect_to = admin_url();
	echo $redirect_to;
	} else if ( in_array( 'customer', $user->roles ) || in_array( 'shop_manager', $user->roles ) ) {
		// Customers and shop managers are redirected to the Shop page
		$redirect_to = home_url( '/my-account' );
		echo $redirect_to;
	} else {
		// Everybody else is redirected to the homepage
		$redirect_to = home_url();
		echo $redirect_to;
	}
	exit();
	
}







/*
*Add extra fields in woocommerce Registration Form
*/

add_action( 'woocommerce_register_form_start', 'ced_extra_register_fields' );
  function ced_extra_register_fields() {?>
	<p class="form-row form-row-wide">
	<label for="reg_billing_username"><?php _e( 'Username', 'woocommerce' ); ?><span class="required">*</span></label>
	<input type="text" class="input-text" name="billing_username" id="reg_billing_username" value="<?php esc_attr_e( $_POST['billing_username'] ); ?>" />
	</p>
	<p class="form-row form-row-wide">
	<label for="reg_billing_password"><?php _e( 'Password', 'woocommerce' ); ?><span class="required">*</span></label>
	<input type="text" class="input-text" name="billing_password" id="reg_billing_password" value="<?php if ( ! empty( $_POST['billing_password'] ) ) esc_attr_e( $_POST['billing_password'] ); ?>" />
	</p>
	<div class="clear"></div>
	<?php
}

/**
* register fields Validating.
*/
function ced_validate_extra_register_fields( $username, $email, $validation_errors ) {
	if ( isset( $_POST['billing_username'] ) && empty( $_POST['billing_username'] ) ) {
		   $validation_errors->add( 'billing_username_error', __( '<strong>Error</strong>: Username is required!', 'woocommerce' ) );
	}
	if ( isset( $_POST['billing_password'] ) && empty( $_POST['billing_password'] ) ) {
		   $validation_errors->add( 'billing_password_error', __( '<strong>Error</strong>: password is required!.', 'woocommerce' ) );
	}
	   return $validation_errors;
}
add_action( 'woocommerce_register_post', 'ced_validate_extra_register_fields', 10, 3 );

/**
* Below code save extra fields.
*/
function ced_save_extra_register_fields( $customer_id ) {;
	// print_r($_POST);
    if ( isset( $_POST['billing_username'] ) && isset( $_POST['billing_password'] )) {
			$username      = sanitize_text_field( $_POST['billing_username'] );
			$password      = esc_attr( $_POST['billing_password']);
			$email_address = sanitize_email( $_POST['email']);
			wp_set_password( $password, $customer_id );
			wp_update_user( array ('ID' => $customer_id, 'display_name' => $username));
		//  print_r($username);die;
		//  $user_id = wp_create_user( $username, $password, $email_address );
		//  $user_id = wp_insert_user ( array('user_login' =>$username, 'user_pass'  =>$password, 'user_email' =>$email_address));
	}
}
add_action( 'woocommerce_created_customer', 'ced_save_extra_register_fields' );

