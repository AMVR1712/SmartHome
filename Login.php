<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Allow, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Allow: GET, POST, PUT, DELETE, OPTIONS, HEAD");
require_once "Conexion.php";
require_once "jwt.php";

if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(isset($_GET['user']) && isset($_GET['pass'])){
        $c = conexion();
        $s = $c->prepare("SELECT * FROM usuarios WHERE user=:user AND pass=:pass");
        $s->bindValue(":user", $_GET['user']);
        $s->bindValue(":pass", sha1( $_GET['pass']));
        $s->execute();
        $s->setFetchMode(PDO::FETCH_ASSOC);
        $r = $s->fetch();
        if($r){
            $t = JWT::create(array("user"=>$_GET['user'], "rol" => $r['rol']), Config::SECRET);
        $result = array("login"=>"Y", "token"=>$t);
        }else{
        $result = array("login"=>"N", "token"=>"Error de usuario/contraseña");
        }
        header("HTTP/1.1 200 OK");
        echo json_encode($result);
    }else{
        header("HTT/1.1 400 Bad Request");
    }
}else{
    header("HTT/1.1 400 Bad Request");
}