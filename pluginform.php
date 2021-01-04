<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login SSI</title>
    <link rel="stylesheet" href="css/style.css?<?php echo time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="js/scripts.js"></script>
</head>

<body>
<p class="">Self Sovereign Identity</p>
<div class="">
    <?php
    //error_reporting(E_ALL);
    //ini_set("display_errors", 1);
    //var_dump($_SERVER);
    ?>
    <button onclick="showForm()">Registrazione</button>
    <button onclick="authenticateUser();" type="button"
            class="button">Autenticazione
    </button>
    <form class="form">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username"><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email"><br>
        <label for="description">Description:</label>
        <textarea id="description" name="description"></textarea><br>
        <label for="role">Choose a role:</label>
        <select name="role" id="role">
            <option value="subscriber">Subscriber</option>
            <option value="author">Author</option>
            <option value="editor">Editor</option>
            <option value="administrator">Administrator</option>
        </select><br>
        <button type="button" onclick="createConnection();">Invia
    </form>
</div>
<img width="370" height="370" class="qrcode" src="" alt="" style="display: none">
<div class="testo"></div>
</body>
</html>
