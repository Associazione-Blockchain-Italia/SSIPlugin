<?php 
    define('WP_USE_THEMES', false);

    require_once( '../../../wp-load.php' );    
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login SSI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css?<?php echo time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="js/scripts.js"></script>
</head>

<body>
<p class="container center">Self Sovereign Identity</p>
<div class="container">
    <div class="d-flex flex-column">
<?php
    ///error_reporting(E_ALL);
    //ini_set("display_errors", 1);
    //var_dump($_SERVER);
?>
<?php $ruolo =  get_option("default_role"); ?>
        <button onclick="createAndOfferCredential('<?php $bytes = random_bytes( 5 );
		echo bin2hex( $bytes ); ?>', '<?php echo $ruolo; ?>')"
                type="button" class="btn btn-outline-primary btn-block ">Registrazione <?php echo $ruolo; ?>
        </button>
        <button onclick="verifyCredential();" type="button"
                class="btn btn-outline-primary btn-block">Autenticazione
        </button>
        <img class="qrcode" src="" alt="">
        <div class="testo center">
        </div>
    </div>
</div>
</body>
</html>
