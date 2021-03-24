<?php
require_once('../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/user.php');

if (!defined('ABSPATH')) {
    exit;
}

define('ACCESS_TOKEN', get_option('wordpressi_trinsic_api_key'));

$arg0 = $_POST["arguments"][0];
$arg1 = $_POST["arguments"][1];
$arg2 = $_POST["arguments"][2];

function createConnection()
{

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/connections');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"multiParty\":false}");
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
    $headers[] = 'Content-Type: application/*+json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    if (!empty(ACCESS_TOKEN) || !empty($wordpressi_trinsic_definition_id)) {

        echo $result;
    } else {
        echo "Api key inesistente";
    }
}

function getConnection($connectionId)
{

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/connections/' . $connectionId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


    $result = curl_exec($ch);
    curl_close($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    if (!empty(ACCESS_TOKEN) || !empty($wordpressi_trinsic_definition_id)) {
        echo $result;
    } else {
        echo "Api key inesistente";
    }
}

function offerCredential($identifier, $jsonObj)
{
    endsWith($identifier, 'ssi2') ? $ide = substr($identifier, 0, -1) : $ide = $identifier;
    $role = $jsonObj['role'];
    $connectionId = $jsonObj['connectionId'];
    $wordpressi_trinsic_definition_id = get_option('wordpressi_trinsic_definition_id');
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/credentials');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"credentialValues\":{\"Identifier\":\"$ide\",\"Role\":\"$role\"},\"wordpressi_trinsic_definition_id\":\"$wordpressi_trinsic_definition_id\",\"connectionId\":\"$connectionId\",\"automaticIssuance\":true}");
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
    $headers[] = 'Content-Type: application/*+json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);


    if (!empty(ACCESS_TOKEN) || !empty($wordpressi_trinsic_definition_id)) {
        echo $result;
    } else {
        echo "Api key inesistente";
    }
}

function createUser($identifier, $jsonObj, $id)
{
    if (!empty(get_option($identifier . 'details'))) {
        update_option($identifier . 'details', $jsonObj);
    } else {
        add_option($identifier . 'details', $jsonObj);
    }
    $id == 0 ? $ide = $identifier : $ide = substr($identifier, 0, -1);
    echo $ide;
    $creds = array(
        'user_login' => $ide,
        'user_pass' => 'pippo',
        'user_email' => $jsonObj['email'],
        'display_name' => $jsonObj['username'],
        'role' => $jsonObj['role'],
    );
    wp_delete_user($id);
    delete_option($identifier . 'details');
    add_option($ide . 'details', $jsonObj);
    echo 'Ho Aggiunto un nuovo user con id: ' . wp_insert_user($creds);

}

function verifyCredential()
{
    $dt = new DateTime();
    $dt->setTimezone(new DateTimeZone('UTC'));
    $data = $dt->format('Y-m-d\TH:i:s\Z');
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/verifications/policy');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"attributes\":[{\"attributeNames\":[\"Identifier\",\"Role\"],\"policyName\":\"Credenziale\"}],\"revocationRequirement\":{\"validAt\":\"" . $data . "\"},\"name\":\"Credenziale\",\"version\":\"1.0.0\"}");


    $headers = array();
    $headers[] = 'Accept: text/plain';
    $headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
    $headers[] = 'Content-Type: application/*+json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    echo $result;
}

function getVerification($verificationId)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/verifications/' . $verificationId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


    $headers = array();
    $headers[] = 'Accept: text/plain';
    $headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    echo $result;
}

function authenticateUser($identifier)
{
    wp_signon(array('user_login' => $identifier, 'user_password' => 'pippo', 'remember' => true));
    echo $identifier . " autenticato";
}

function revokeCredential($identifier, $id)
{

    $ch = curl_init();
    $credentialId = get_option($identifier . 'details')['credentialId'];
    curl_setopt($ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/credentials/' . $credentialId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


    $headers = array();
    $headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    wp_delete_user($id);
    delete_option($identifier . 'details');
    echo $result;

}

function recuperaJSON($identifier)
{
    $lt = get_option($identifier . 'details');
    echo json_encode($lt);
}

switch ($_POST["functionname"]) {
    case 'createConnection':
        createConnection();
        break;
    case'offerCredential';
        offerCredential($arg0, $arg1);
        break;
    case 'createUser':
        createUser($arg0, $arg1, $arg2);
        break;
    case 'getConnection':
        getConnection($arg0);
        break;
    case 'verifyCredential':
        verifyCredential();
        break;
    case 'getVerification':
        getVerification($arg0);
        break;
    case 'authenticateUser':
        authenticateUser($arg0);
        break;
    case 'revokeCredential':
        revokeCredential($arg0, $arg1);
        break;
    case 'recuperaJSON':
        recuperaJSON($arg0);
        break;
}
