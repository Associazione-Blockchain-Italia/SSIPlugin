<?php
/**
 * Plugin Name: SSI Plugin
 * Plugin URI: https://github.com/Associazione-Blockchain-Italia/SSIPlugin
 * Description:
 * Author: Araneum Group srl
 * Version: 1.0.0
 * Author URI: https://www.araneum.it/
 */

if (!defined('ABSPATH')) {
    exit;
}

function script_init()
{
    wp_enqueue_script('script', plugin_dir_url(__FILE__) . 'js/scripts.js', array('jquery'), true);
}

add_action('admin_enqueue_scripts', 'script_init');

function redirectPluginForm()
{
    ?>
    <div style="text-align: center"><a href="<?php echo plugin_dir_url(__FILE__) . 'pluginform.php'; ?>">Click here
            for the SSI Authentication</a></div>
    <?php
}

function redirectIfSSIlogin(): string
{
    return wp_login_url();
}

//Controlla che l'email finisca con @ssi.it e che non sia pending
function endsWith($haystack, $needle): bool
{
    $length = strlen($needle);
    if (!$length) {
        return true;
    }

    return substr($haystack, -$length) === $needle;
}

function verify($user_login, $user)
{
    global $wp;
    if ((endsWith($user->user_login, 'ssi@ssi') || $user->user_email == 'pending@pending.com') && $_SERVER['HTTP_REFERER'] == wp_login_url()) {
        add_filter('login_redirect', 'redirectIfSSIlogin', 10, 3);
        wp_logout();
    }
}

function wpdocs_register_my_custom_menu_page()
{
    add_menu_page('SSI Admin', 'SSI Settings', 'manage_options', 'ssiplugin', 'display', 'dashicons-admin-network', 90);
}

function display()
{
    ?>
    <div class="wrap">
        <div id="icon-tools" class="icon32"></div>
        <h2 style="font-weight: bold">SSI Settings</h2>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <h3>Access Token</h3>
            <input type="text" size="44" name="api_key" placeholder="Enter your Access Token">
            <input type="hidden" name="action" value="process_form">
            <input type="submit" name="submit" id="submit" class="update-button button button-primary"
                   value="Update Access Token"/>
        </form>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <h3>Definition Id</h3>
            <input type="text" size="44" name="definitionId" placeholder="Enter your definition id">
            <input type="hidden" name="action" value="process_form">
            <input type="submit" name="submit" id="submit" class="update-button button button-primary"
                   value="Update definition"/>
        </form>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script>
            let jsonObj;

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

            function offerCredential(identifier, id) {
                let credentialId;
                $.ajax({
                    url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php'?>',
                    type: 'POST',
                    data: {functionname: 'recuperaJSON', arguments: [identifier],},
                    success: function (result) {
                        jsonObj = JSON.parse(result);
                    },
                    complete: function () {
                        $.ajax({
                            url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php'?>',
                            type: 'POST',
                            data: {functionname: 'offerCredential', arguments: [identifier, jsonObj],},
                            success: function (result) {
                                const jsonResult = JSON.parse(result);
                                credentialId = jsonResult['credentialId'];
                            },
                            complete: function () {
                                jsonObj = Object.assign({credentialId: credentialId}, jsonObj);
                                createUser(identifier, jsonObj, id);
                                $('#' + id).hide();
                            }
                        });
                    }
                });
            }
            function createUser(identifier, jsonObj, id) {
                $.ajax({
                    url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php'?>',
                    type: 'POST',
                    data: {functionname: 'createUser', arguments: [identifier, jsonObj, id],},
                    success: function (result) {
                        console.log(result);
                    },
                });
            }

        </script>
        <ul class="list-group" style="font-size: 19px; font-weight: 500;padding-top: 9px;padding-bottom: 4px">User list
            <?php $users = getSSIUsers();
            foreach ($users as $user) {
                echo "<li id=\"" . $user->ID . "\" style='font-size: 16px; padding-top: 10px' class=\"list-group-item\">" . $user->user_login . "<button style=\"margin: 10px\" 
                        onclick=revokeCredential('" . $user->user_login . "'," . $user->ID . ") id=\"" . $user->ID . "\"' >Revoca</button></li>";
            }
            ?>
        </ul>
        <ul class="list-group" style="font-size: 19px; font-weight: 500;padding-top: 9px;padding-bottom: 4px">Pending
            user
            <?php $users = getSSIPendingUser();
            foreach ($users as $user) {
                echo "<li id=\"" . $user->ID . "\" style='font-size: 16px; padding-top: 10px' class=\"list-group-item\"> User login ID: " . $user->user_login . "
                        <div> Username: " . get_option($user->user_login . 'details')['username'] . "</div>
                        <div> Email: " . get_option($user->user_login . 'details')['email'] . "</div>
                        <div> Role: " . get_option($user->user_login . 'details')['role'] . "</div>
                        <div> Description : " . get_option($user->user_login . 'details')['description'] . "</div>
                        <button style=\"margin: 10px\" onclick=offerCredential('" . $user->user_login . "'," . $user->ID . ")>Emetti</button></li>";
            }
            ?>
        </ul>
    </div>
    <?php
}

function submit_api_key()
{
    if (isset($_POST['api_key'])) {
        $api_key = sanitize_text_field($_POST['api_key']);
        $api_exists = get_option('api_key');
        if (!empty($api_key) && !empty($api_exists)) {
            update_option('api_key', $api_key);
        } else {
            add_option('api_key', $api_key);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}


function submit_definitionId()
{
    if (isset($_POST['definitionId'])) {
        $definitionId = sanitize_text_field($_POST['definitionId']);
        $definition_exists = get_option('definitionId');
        if (!empty($definitionId) && !empty($definition_exists)) {
            update_option('definitionId', $definitionId);
        } else {
            add_option('definitionId', $definitionId);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}

function getSSIUsers()
{
    $search = 'ssi';
    $users = new WP_User_Query(array(
        'search' => '*' . $search,
        'search_columns' => array(
            'user_login',
        ),
    ));

    return $users->get_results();
}

function getSSIPendingUser()
{
    $search = 'ssi2';
    $users = new WP_User_Query(array(
        'search' => '*' . $search,
        'search_columns' => array(
            'user_login',
        ),
    ));

    return $users->get_results();
}


add_action('admin_post_nopriv_process_form', 'submit_definitionId');
add_action('admin_post_process_form', 'submit_definitionId');

add_action('admin_post_nopriv_process_form', 'submit_api_key');
add_action('admin_post_process_form', 'submit_api_key');

add_action('admin_menu', 'wpdocs_register_my_custom_menu_page');

add_action('wp_login', 'verify', 10, 2);
add_action('login_footer', 'redirectPluginForm');

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'salcode_add_plugin_page_settings_link');
function salcode_add_plugin_page_settings_link($links)
{
    $links[] = '<a href="' .
        admin_url('options-general.php?page=ssiplugin') .
        '">' . __('Settings') . '</a>';

    return $links;
}
