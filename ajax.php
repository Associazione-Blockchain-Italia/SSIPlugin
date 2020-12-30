<?php
require_once( '../../../wp-load.php' );
require_once( ABSPATH . 'wp-admin/includes/user.php' );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACCESS_TOKEN', get_option( 'api_key' ) );

$arg0 = $_POST["arguments"][0];
$arg1 = $_POST["arguments"][1];
$arg2 = $_POST["arguments"][2];

/**
 * Generate a random string, using a cryptographically secure
 * pseudorandom number generator (random_int)
 *
 * For PHP 7, random_int is a PHP core function
 * For PHP 5.x, depends on https://github.com/paragonie/random_compat
 *
 * @param int $length      How many characters do we want?
 * @param string $keyspace A string of all possible characters
 *                         to select from
 * @return string
 */
function random_str(
    $length,
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
) {
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    if ($max < 1) {
        throw new Exception('$keyspace must be at least two characters long');
    }
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}


function createUser( $identifier, $credentialId, $role ) {

	$password = random_str(80);

	$creds = array(
		'user_login' => $identifier,
		'user_pass'  => '$password',
		'user_email' => $identifier . '@ssi.it',
		'role'       => $role
	);
	add_option( $identifier, $credentialId );
	echo 'Ho Aggiunto un nuovo user con id: ' . wp_insert_user( $creds );
}

function authenticateUser( $identifier ) {
	wp_signon( array( 'user_login' => $identifier, 'user_password' => 'pippo', 'remember' => true ) );
	echo $identifier . " autenticato";
}

function createAndOfferCredential( $identifier, $role ) {
	$ch           = curl_init();
	$definitionId = get_option( "definitionId" );
	curl_setopt( $ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/credentials' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, "{\"credentialValues\":{\"Identifier\":\"$identifier\",\"Role\":\"$role\"},\"definitionId\":\"$definitionId\",\"automaticIssuance\":true}" );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	$headers   = array();
	$headers[] = 'Accept: application/json';
	$headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
	$headers[] = 'Content-Type: application/*+json';
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

	$result = curl_exec( $ch );
	if ( curl_errno( $ch ) ) {
		echo 'Error:' . curl_error( $ch );
	}
	curl_close( $ch );
	if(!empty(ACCESS_TOKEN) || !empty($definitionId))
	echo $result;
	else echo "Api key inesistente";

}

function getCredential( $credentialId ) {
	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/credentials/' . $credentialId );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );


	$headers   = array();
	$headers[] = 'Accept: application/json';
	$headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

	$result = curl_exec( $ch );
	if ( curl_errno( $ch ) ) {
		echo 'Error:' . curl_error( $ch );
	}
	curl_close( $ch );
	echo $result;
}

function verifyCredential() {
	$dt = new DateTime();
	$dt->setTimezone( new DateTimeZone( 'UTC' ) );
	$data = $dt->format( 'Y-m-d\TH:i:s\Z' );
	$ch   = curl_init();

	curl_setopt( $ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/verifications/policy' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );


	curl_setopt( $ch, CURLOPT_POSTFIELDS, "{\"attributes\":[{\"attributeNames\":[\"Identifier\",\"Role\"],\"policyName\":\"Credenziale\"}],\"revocationRequirement\":{\"validAt\":\"" . $data . "\"},\"name\":\"Credenziale\",\"version\":\"1.0.0\"}" );


	$headers   = array();
	$headers[] = 'Accept: text/plain';
	$headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
	$headers[] = 'Content-Type: application/*+json';
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

	$result = curl_exec( $ch );
	if ( curl_errno( $ch ) ) {
		echo 'Error:' . curl_error( $ch );
	}
	curl_close( $ch );
	echo $result;
}

function getVerification( $verificationId ) {
	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/verifications/' . $verificationId );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );


	$headers   = array();
	$headers[] = 'Accept: text/plain';
	$headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

	$result = curl_exec( $ch );
	if ( curl_errno( $ch ) ) {
		echo 'Error:' . curl_error( $ch );
	}
	curl_close( $ch );
	echo $result;
}

function revokeCredential( $identifier, $id ) {
	// Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
	$ch           = curl_init();
	$credentialId = get_option( $identifier );
	curl_setopt( $ch, CURLOPT_URL, 'https://api.trinsic.id/credentials/v1/credentials/' . $credentialId );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'DELETE' );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );


	$headers   = array();
	$headers[] = 'Authorization: Bearer ' . ACCESS_TOKEN;
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

	$result = curl_exec( $ch );
	if ( curl_errno( $ch ) ) {
		echo 'Error:' . curl_error( $ch );
	}
	curl_close( $ch );
	wp_delete_user( $id );
	delete_option($identifier);
	echo $result;

}

switch ( $_POST["functionname"] ) {
	case 'createUser':
		createUser( $arg0, $arg1, $arg2 );
		break;
	case 'createAndOfferCredential':
		createAndOfferCredential( $arg0, $arg1 );
		break;
	case 'getCredential':
		getCredential( $arg0 );
		break;
	case 'verifyCredential':
		verifyCredential();
		break;
	case 'getVerification':
		getVerification( $arg0 );
		break;
	case 'authenticateUser':
		authenticateUser( $arg0 );
		break;
	case 'revokeCredential':
		revokeCredential( $arg0, $arg1 );
		break;
}
