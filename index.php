<?php
require_once "vendor/autoload.php";

session_start();

if (isset($_GET["logout"])){
    session_destroy();
    header('Location: '.$_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["2fa"])){

    $db = new PDO('sqlite:login.db');

    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = :password LIMIT 1");
    $stmt->bindValue(":username", $_POST["username"], PDO::PARAM_STR);
    $stmt->bindValue(":password", md5($_POST["password"]),PDO::PARAM_STR);
    if ($stmt->execute()){
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        //Usuário existe.
        if (isset($user["id"])){
            $g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
            //Checa codigo de autenticação de 2 fatores
            if ($g->checkCode($user["auth_token"], $_POST["2fa"])){

                $_SESSION["id"] = $user["id"];
                $_SESSION["username"] = $_POST["username"];
                header('Location: '.$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "Codigo do 2FA invalido.";
            }
        } else {
            $error = "Login/Senha incorreto.";
        }
    } else {
        $error = "Erro interno.";
    }
}

?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Jekyll v3.8.5">
    <title>Login</title>
    <link href="login.css" rel="stylesheet">
</head>
<body class="text-center">
    <?php if (isset($_SESSION["id"])):?>
        <div class="container">
            <h1 >Logado com: <?=$_SESSION["username"]?></h1>
            <a href="?logout">Deslogar</a>
        </div>
    <?php else:?>
        <form method="POST" class="form-signin">
            <h1 class="h3 mb-3 font-weight-normal">Login 2 Fatores</h1>
            <?php if (isset($error) && $error):?>
                <div class="alert alert-danger" role="alert">
                    <?=$error?>
                </div>
            <?php endif;?>
            <label for="inputLogin" class="sr-only">Login</label>
            <input type="text" name="username" id="inputLogin" class="form-control" placeholder="Login" required="" autofocus="">
            <label for="inputPassword" class="sr-only">Senha</label>
            <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Senha" required="">
            <label for="inputPassword" class="sr-only">2FA</label>
            <input type="text" name="2fa" class="form-control" placeholder="Token 2FA" required="">
            <br>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Logar</button>
            <a href="register.php">Não possui conta? Registre-se</a>
            <p class="mt-5 mb-3 text-muted">© 2017-2019</p>



        </form>
    <?php endif?>
</body>
</html>