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

    <style>
        <?php include 'css/style.css'; ?>
    </style>

    <div class="wrap">

        <div id="icon-tools" class="icon32"></div>
        <h2 style="font-weight: bold">SSI Settings</h2>
        <br>
        <div class="btn-group">
            <button onclick="changeToTrinsic()">Trinsic</button>
            <button onclick="changeToEvernym()">Evernym</button>
        </div>


        <!-- Effettuo la chiamata al DB e mi preparo per le varie Query  -->

        <?php

        // Mi preparo la connection con il Database

        $con = mysqli_connect("localhost", "root", "");
        if (!$con) {
            die('Could not connect: ' . mysqli_error($con));
        }

        mysqli_select_db($con, "wamp-demo"); //Seleziono il DB che voglio usare

        //Tabella riassuntiva delle API che trattiamo che può tornare utile per il deploy. Togliere i commenti per usarla.

        $result = mysqli_query($con, "SELECT * FROM wp_options WHERE option_name ='wordpressi_trinsic_api_key' OR
        option_name ='wordpressi_trinsic_definition_id' OR
        option_name ='wordpressi_evernym_api_key' OR
        option_name ='wordpressi_evernym_domain_did' OR
        option_name ='wordpressi_evernym_definition_id' OR
        option_name ='wordpressi_evernym_webhook'
        
        ");

        echo "<br><br>";
        echo "<table border='1'>
              <tr>
             <th>Chiave</th>
            <th>Valore</th>
             </tr>";

         while ($row = mysqli_fetch_array($result)) {
            echo "<tr>";
            echo "<td>" . $row['option_name'] . "</td>";
             echo "<td>" . $row['option_value'] . "</td>";
            echo "</tr>";
        }
          echo "</table>";

        $prov = mysqli_query($con, "SELECT * FROM wp_options WHERE option_name ='wordpressi_trinsic_api_key'");
        while ($row = mysqli_fetch_array($prov)) {
            $current_wordpressi_trinsic_api_key = $row['option_value']; // Mi salvo il valore corrente in una variabile
        }

        $prov = mysqli_query($con, "SELECT * FROM wp_options WHERE option_name ='wordpressi_trinsic_definition_id'");
        while ($row = mysqli_fetch_array($prov)) {
            $current_wordpressi_trinsic_definition_id = $row['option_value']; // Mi salvo il valore corrente in una variabile
        }

        $prov = mysqli_query($con, "SELECT * FROM wp_options WHERE option_name ='wordpressi_evernym_api_key'");
        while ($row = mysqli_fetch_array($prov)) {
            $current_wordpressi_evernym_api_key = $row['option_value']; // Mi salvo il valore corrente in una variabile
        }

        $prov = mysqli_query($con, "SELECT * FROM wp_options WHERE option_name ='wordpressi_evernym_domain_did'");
        while ($row = mysqli_fetch_array($prov)) {
            $current_wordpressi_evernym_domain_did = $row['option_value']; // Mi salvo il valore corrente in una variabile
        }

        $prov = mysqli_query($con, "SELECT * FROM wp_options WHERE option_name ='wordpressi_evernym_definition_id'");
        while ($row = mysqli_fetch_array($prov)) {
            $current_wordpressi_evernym_definition_id = $row['option_value']; // Mi salvo il valore corrente in una variabile
        }

        $prov = mysqli_query($con, "SELECT * FROM wp_options WHERE option_name ='wordpressi_evernym_webhook'");
        while ($row = mysqli_fetch_array($prov)) {
            $current_wordpressi_evernym_webhook = $row['option_value']; // Mi salvo il valore corrente in una variabile
        }




        mysqli_close($con); //Chiudo la connection con il Database


        ?>




        <!-- Questa è la div di Trinsic che poi verrà nascosta -->

        <br>

        <div id="trinsicSettings" class="trinsic-settings">

            <h1 style="font-weight: bold">[ Trinsic Settings ]</h1>
            <h3>Enable/Disable the library</h3>

            <label class="switch">
                <input type="checkbox">
                <span class="slider round"></span>
            </label>


            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <h3>Access Token</h3>
                <input type="text" size="44" name="wordpressi_trinsic_api_key" placeholder="<?php echo $current_wordpressi_trinsic_api_key; ?>">
                <input type="hidden" name="action" value="process_form">
                <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Access Token" />
            </form>


            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <h3>Definition ID</h3>
                <input type="text" size="44" name="wordpressi_trinsic_definition_id" placeholder="<?php echo $current_wordpressi_trinsic_definition_id; ?>">
                <input type="hidden" name="action" value="process_form">
                <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Definition ID" />
            </form>

            <h3>Test the library</h3>

            <div>
                <button onclick="testSwitchTrinsic()" class="update-button button-primary">Begin test</button>
                <span id="trinsic-test-ok" class="test-result-ok">
                    OK
                </span>
                <span class="test-result-fail" id="trinsic-test-fail">
                    FAIL
                </span>
            </div>

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script>
                let jsonObj;

                function revokeCredential(identifier, id) {
                    $.ajax({
                        url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php' ?>',
                        type: 'POST',
                        data: {
                            functionname: 'revokeCredential',
                            arguments: [identifier, id],
                        },
                        success: function(result) {
                            console.log(result)
                            $('#' + id).hide();
                        },
                    });
                }

                function offerCredential(identifier, id) {
                    let credentialId;
                    $.ajax({
                        url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php' ?>',
                        type: 'POST',
                        data: {
                            functionname: 'recuperaJSON',
                            arguments: [identifier],
                        },
                        success: function(result) {
                            jsonObj = JSON.parse(result);
                        },
                        complete: function() {
                            $.ajax({
                                url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php' ?>',
                                type: 'POST',
                                data: {
                                    functionname: 'offerCredential',
                                    arguments: [identifier, jsonObj],
                                },
                                success: function(result) {
                                    const jsonResult = JSON.parse(result);
                                    credentialId = jsonResult['credentialId'];
                                },
                                complete: function() {
                                    jsonObj = Object.assign({
                                        credentialId: credentialId
                                    }, jsonObj);
                                    createUser(identifier, jsonObj, id);
                                    $('#' + id).hide();
                                }
                            });
                        }
                    });
                }

                function createUser(identifier, jsonObj, id) {
                    $.ajax({
                        url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php' ?>',
                        type: 'POST',
                        data: {
                            functionname: 'createUser',
                            arguments: [identifier, jsonObj, id],
                        },
                        success: function(result) {
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

        <!-- Questa è la div di Evernym che poi verrà nascosta -->

        <div id="evernymSettings" class="evernym-settings">

            <h1 style="font-weight: bold">[ Evernym Settings ]</h1>
            <h3>Enable/Disable the library</h3>

            <label class="switch">
                <input type="checkbox">
                <span class="slider round"></span>
            </label>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <h3>Access Token</h3>
                <input type="text" size="44" name="wordpressi_evernym_api_key" placeholder="<?php echo $current_wordpressi_evernym_api_key; ?>">
                <input type="hidden" name="action" value="process_form">
                <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Access Token" />
            </form>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <h3>Domain DID</h3>
                <input type="text" size="44" name="wordpressi_evernym_domain_did" placeholder="<?php echo $current_wordpressi_evernym_domain_did; ?>">
                <input type="hidden" name="action" value="process_form">
                <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Domain DID" />
            </form>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <h3>Definition ID</h3>
                <input type="text" size="44" name="wordpressi_evernym_definition_id" placeholder="<?php echo $current_wordpressi_evernym_definition_id; ?>">
                <input type="hidden" name="action" value="process_form">
                <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Definition ID" />
            </form>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <h3>Local Test Webhook</h3>
                <input type="text" size="44" name="wordpressi_evernym_webhook" placeholder="<?php echo $current_wordpressi_evernym_webhook; ?>">
                <input type="hidden" name="action" value="process_form">
                <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Local Test Webhook" />
            </form>


            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">

                <h3>Chiamiamo Evernym</h3>
                <input type="text" size="44" name="evernym_test" placeholder="<?php echo $current_wordpressi_evernym_webhook; ?>">
                <input type="hidden" name="action" value="process_form">
                <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="prova a chiamare evernym" />
            
            </form>

            <h3>Test the library</h3>

            <div>
                <button onclick="testSwitchEvernym()" class="update-button button-primary">Begin test</button>

                <span id="evernym-test-ok" class="test-result-ok">
                    OK
                </span>
                <span id="evernym-test-fail" class="test-result-fail">
                    FAIL
                </span>
            </div>

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script>
                let jsonObj;

                function revokeCredential(identifier, id) {
                    $.ajax({
                        url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php' ?>',
                        type: 'POST',
                        data: {
                            functionname: 'revokeCredential',
                            arguments: [identifier, id],
                        },
                        success: function(result) {
                            console.log(result)
                            $('#' + id).hide();
                        },
                    });
                }

                function offerCredential(identifier, id) {
                    let credentialId;
                    $.ajax({
                        url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php' ?>',
                        type: 'POST',
                        data: {
                            functionname: 'recuperaJSON',
                            arguments: [identifier],
                        },
                        success: function(result) {
                            jsonObj = JSON.parse(result);
                        },
                        complete: function() {
                            $.ajax({
                                url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php' ?>',
                                type: 'POST',
                                data: {
                                    functionname: 'offerCredential',
                                    arguments: [identifier, jsonObj],
                                },
                                success: function(result) {
                                    const jsonResult = JSON.parse(result);
                                    credentialId = jsonResult['credentialId'];
                                },
                                complete: function() {
                                    jsonObj = Object.assign({
                                        credentialId: credentialId
                                    }, jsonObj);
                                    createUser(identifier, jsonObj, id);
                                    $('#' + id).hide();
                                }
                            });
                        }
                    });
                }

                function createUser(identifier, jsonObj, id) {
                    $.ajax({
                        url: '<?php echo home_url() . '/wp-content/plugins/SSIPlugin/ajax.php' ?>',
                        type: 'POST',
                        data: {
                            functionname: 'createUser',
                            arguments: [identifier, jsonObj, id],
                        },
                        success: function(result) {
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


    </div>

    <!-- Tutto quello che sta qua sotto si sposterà in un JS esterno -->

    <script>
        function changeToTrinsic() {
            var x = document.getElementById("trinsicSettings");
            var y = document.getElementById("evernymSettings");
            x.style.display = "block";
            y.style.display = "none";
        }

        function changeToEvernym() {
            var x = document.getElementById("evernymSettings");
            var y = document.getElementById("trinsicSettings");
            x.style.display = "block";
            y.style.display = "none";

        }

        function testSwitchTrinsic() {
            var x = document.getElementById("trinsic-test-ok");
            var y = document.getElementById("trinsic-test-fail");
            if (x.style.display === "none") {
                x.style.display = "inline";
                y.style.display = "none";
            } else {

                x.style.display = "none";
                y.style.display = "inline";

            }

        }

        function testSwitchEvernym() {
            var x = document.getElementById("evernym-test-ok");
            var y = document.getElementById("evernym-test-fail");
            if (x.style.display === "none") {
                x.style.display = "inline";
                y.style.display = "none";
            } else {

                x.style.display = "none";
                y.style.display = "inline";

            }

        }
    </script>

    <!-- Fino a qua -->




    <?php

}

// Registrazione in Database di variabili per Trinsic

function submit_wordpressi_trinsic_api_key()
{
    if (isset($_POST['wordpressi_trinsic_api_key'])) {
        $wordpressi_trinsic_api_key = sanitize_text_field($_POST['wordpressi_trinsic_api_key']);
        $trinsic_api_exists = get_option('wordpressi_trinsic_api_key');
        if (!empty($wordpressi_trinsic_api_key) && !empty($trinsic_api_exists)) {
            update_option('wordpressi_trinsic_api_key', $wordpressi_trinsic_api_key);
        } else {
            add_option('wordpressi_trinsic_api_key', $wordpressi_trinsic_api_key);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}


function submit_wordpressi_trinsic_definition_id()
{
    if (isset($_POST['wordpressi_trinsic_definition_id'])) {
        $wordpressi_trinsic_definition_id = sanitize_text_field($_POST['wordpressi_trinsic_definition_id']);
        $wordpressi_trinsic_definition_id_exists = get_option('wordpressi_trinsic_definition_id');
        if (!empty($wordpressi_trinsic_definition_id) && !empty($wordpressi_trinsic_definition_id_exists)) {
            update_option('wordpressi_trinsic_definition_id', $wordpressi_trinsic_definition_id);
        } else {
            add_option('wordpressi_trinsic_definition_id', $wordpressi_trinsic_definition_id);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}


// Registrazione in Database di variabili per Evernym


function submit_wordpressi_evernym_api_key()
{
    if (isset($_POST['wordpressi_evernym_api_key'])) {
        $wordpressi_evernym_api_key = sanitize_text_field($_POST['wordpressi_evernym_api_key']);
        $wordpressi_evernym_api_key_exists = get_option('wordpressi_evernym_api_key');
        if (!empty($wordpressi_evernym_api_key) && !empty($wordpressi_evernym_api_key_exists)) {
            update_option('wordpressi_evernym_api_key', $wordpressi_evernym_api_key);
        } else {
            add_option('wordpressi_evernym_api_key', $wordpressi_evernym_api_key);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}

function submit_wordpressi_evernym_domain_did()
{
    if (isset($_POST['wordpressi_evernym_domain_did'])) {
        $wordpressi_evernym_domain_did = sanitize_text_field($_POST['wordpressi_evernym_domain_did']);
        $wordpressi_evernym_domain_did_exists = get_option('wordpressi_evernym_domain_did');
        if (!empty($wordpressi_evernym_domain_did) && !empty($wordpressi_evernym_domain_did_exists)) {
            update_option('wordpressi_evernym_domain_did', $wordpressi_evernym_domain_did);
        } else {
            add_option('wordpressi_evernym_domain_did', $wordpressi_evernym_domain_did);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}

function submit_wordpressi_evernym_definition_id()
{
    if (isset($_POST['wordpressi_evernym_definition_id'])) {
        $wordpressi_evernym_definition_id = sanitize_text_field($_POST['wordpressi_evernym_definition_id']);
        $wordpressi_evernym_definition_id_exists = get_option('wordpressi_evernym_definition_id');
        if (!empty($wordpressi_evernym_definition_id) && !empty($wordpressi_evernym_definition_id_exists)) {
            update_option('wordpressi_evernym_definition_id', $wordpressi_evernym_definition_id);
        } else {
            add_option('wordpressi_evernym_definition_id', $wordpressi_evernym_definition_id);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}

function submit_wordpressi_evernym_webhook()
{
    if (isset($_POST['wordpressi_evernym_webhook'])) {
        $wordpressi_evernym_webhook = sanitize_text_field($_POST['wordpressi_evernym_webhook']);
        $wordpressi_evernym_webhook_exists = get_option('wordpressi_evernym_webhook');
        if (!empty($wordpressi_evernym_webhook) && !empty($wordpressi_evernym_webhook_exists)) {
            update_option('wordpressi_evernym_webhook', $wordpressi_evernym_webhook);
        } else {
            add_option('wordpressi_evernym_webhook', $wordpressi_evernym_webhook);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}



function submit_evernym_test() 
{

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://vas.pps.evernym.com/api/TVs7RkcH6crEbMJU8hgqWY/configs/0.6/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
    "@id": "9643c27a-5adf-469e-b9a2-8461cf52b7b0",
    "@type": "did:sov:123456789abcdefghi1234;spec/configs/0.6/UPDATE_COM_METHOD",
    "comMethod": {
    "id": "webhook",
    "value": "http://e6bd39e84e93.ngrok.io/demo-wamp/wp-content/plugins/SSIPlugin-features/webhook.php",
    "type": 2,
    "packaging": {
    "pkgType": "plain"
    }
    }
    }',
    CURLOPT_HTTPHEADER => array(
    'X-API-KEY: CNaN5SXFBamGU5jkXx9rnZ1L66zvv8Rz3fdf2duDuvKp:XrWmwiBXHPSyQMNmhUs6DWimvNnWquVu2Sh8xoFFDEFA1D93UbZCLArJyKUY7NyoVCLjWdfrQsBXQAmLpApkZXv',
    'Content-Type: application/json',
    'Authorization: Bearer CNaN5SXFBamGU5jkXx9rnZ1L66zvv8Rz3fdf2duDuvKp:XrWmwiBXHPSyQMNmhUs6DWimvNnWquVu2Sh8xoFFDEFA1D93UbZCLArJyKUY7NyoVCLjWdfrQsBXQAmLpApkZXv'
    ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    echo $response;
    file_put_contents(time()."-resp.txt", print_r($response,true));

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


add_action('admin_post_nopriv_process_form', 'submit_wordpressi_trinsic_api_key');
add_action('admin_post_process_form', 'submit_wordpressi_trinsic_api_key');

add_action('admin_post_nopriv_process_form', 'submit_wordpressi_trinsic_definition_id');
add_action('admin_post_process_form', 'submit_wordpressi_trinsic_definition_id');

add_action('admin_post_nopriv_process_form', 'submit_wordpressi_evernym_api_key');
add_action('admin_post_process_form', 'submit_wordpressi_evernym_api_key');

add_action('admin_post_nopriv_process_form', 'submit_wordpressi_evernym_domain_did');
add_action('admin_post_process_form', 'submit_wordpressi_evernym_domain_did');

add_action('admin_post_nopriv_process_form', 'submit_wordpressi_evernym_definition_id');
add_action('admin_post_process_form', 'submit_wordpressi_evernym_definition_id');

add_action('admin_post_nopriv_process_form', 'submit_wordpressi_evernym_webhook');
add_action('admin_post_process_form', 'submit_wordpressi_evernym_webhook');

add_action('admin_post_nopriv_process_form', 'submit_evernym_test');
add_action('admin_post_process_form', 'submit_evernym_test');






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
