<?php
/**
 * Plugin Name: SSI Plugin
 * Plugin URI: http://www.araneum.it/
 * Description:
 * Author: Araneum
 * Version: 1.0.0
 * Author URI: http://www.araneum.it/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function redirectPluginForm() {
	?>
    <div style="text-align: center"><a href="<?php echo plugin_dir_url( __FILE__ ) . 'pluginform.php'; ?>">Click here
            for the SSI Authentication</a></div>
	<?php
}

function redirectIfSSIlogin(): string {
	return wp_login_url();
}

//Controlla che l'email finisca con @ssi.it
function endsWith( $haystack, $needle ): bool {
	$length = strlen( $needle );
	if ( ! $length ) {
		return true;
	}

	return substr( $haystack, - $length ) === $needle;
}

function verify( $user_login, $user ) {
	global $wp;
	if ( endsWith( $user->user_email, '@ssi.it' ) && $_SERVER['HTTP_REFERER'] == wp_login_url() ) {
		add_filter( 'login_redirect', 'redirectIfSSIlogin', 10, 3 );
		wp_logout();
	}
}

function wpdocs_register_my_custom_menu_page() {
	add_menu_page( 'SSI Admin', 'SSI Settings', 'manage_options', 'custompage', 'display', 'dashicons-admin-network', 90 );
}

// Funzione che mostra la pagina di amministrazione
function display() {
	?>
    <div class="wrap">
        <div id="icon-tools" class="icon32"></div>
        <h2 style ="font-weight: bold">SSI Settings</h2>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
            <h3>Access Token</h3>
            <input type="text" size="44" name="api_key" placeholder="Enter your Access Token">
            <input type="hidden" name="action" value="process_form">
            <input type="submit" name="submit" id="submit" class="update-button button button-primary"
                   value="Update Access Token"/>
        </form>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
            <h3>Definition Id</h3>
            <input type="text" size="44" name="definitionId" placeholder="Enter your definition id">
            <input type="hidden" name="action" value="process_form">
            <input type="submit" name="submit" id="submit" class="update-button button button-primary"
                   value="Update definition"/>
        </form>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script>
            function revokeCredential(identifier, id) {
                $.ajax({
                    url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php'?>',
                    type: 'POST',
                    data: {functionname: 'revokeCredential', arguments: [identifier, id],},
                    success: function (result) {
                        console.log(result)
                        $('#' + id).hide();
                    },
                });
            }
        </script>
        <ul class="list-group" style="font-size: 19px; font-weight: 500;padding-top: 9px;padding-bottom: 4px">User list
			<?php $users = getSSIUsers();
			foreach ( $users as $user ) {
				echo "<li id=\"" . $user->ID . "\" style='font-size: 16px; padding-top: 10px' class=\"list-group-item\">" . $user->user_login . "<button style=\"margin: 10px\" 
                        onclick=revokeCredential('" . $user->user_login . "'," . $user->ID . ") id=\"" . $user->ID . "\"' >Revoca</button></li>";
			}
			?>
        </ul>
    </div>
	<?php
}

// Submit functionality
function submit_api_key() {
	if ( isset( $_POST['api_key'] ) ) {
		$api_key    = sanitize_text_field( $_POST['api_key'] );
		$api_exists = get_option( 'api_key' );
		if ( ! empty( $api_key ) && ! empty( $api_exists ) ) {
			update_option( 'api_key', $api_key );
		} else {
			add_option( 'api_key', $api_key );
		}
	}
	wp_redirect( $_SERVER['HTTP_REFERER'] );
}

// Submit functionality
function submit_definitionId() {
	if ( isset( $_POST['definitionId'] ) ) {
		$definitionId      = sanitize_text_field( $_POST['definitionId'] );
		$definition_exists = get_option( 'definitionId' );
		if ( ! empty( $definitionId ) && ! empty( $definition_exists ) ) {
			update_option( 'definitionId', $definitionId );
		} else {
			add_option( 'definitionId', $definitionId );
		}
	}
	wp_redirect( $_SERVER['HTTP_REFERER'] );
}

//Funzione che ritorna gl utenti SSI
function getSSIUsers() {
	$search = '@ssi.it';
	$users  = new WP_User_Query( array(
		'search'         => '*' . $search,
		'search_columns' => array(
			'user_email',
		),
	) );

	return $users->get_results();
}

add_action( 'admin_post_nopriv_process_form', 'submit_definitionId' );
add_action( 'admin_post_process_form', 'submit_definitionId' );

add_action( 'admin_post_nopriv_process_form', 'submit_api_key' );
add_action( 'admin_post_process_form', 'submit_api_key' );

add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );
add_action( 'wp_login', 'verify', 10, 2 );
add_action( 'login_footer', 'redirectPluginForm' );
