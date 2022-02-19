<?php
require_once 'Conexion.php';
require_once 'jwt.php';

/************************ BLOQUE DE ACCESDO DE SEGURIDAD */
$headers = apache_request_headers();
$tmp = $headers["Authorization"];
$jwt = str_replace("Bearer ", "", $tmp);
if(JWT::verify($jwt, Config::SECRET) != 0){
    header("http/1.1 401 Unauthorized");
    exit;
}


$user = JWT::get_data($jwt, Config::SECRET)["user"];

$metodo = $_SERVER["REQUEST_METHOD"];
switch($metodo){
    case 'GET':
        $c = conexion();
        if(isset($_GET['id'])){
            $s = $c -> prepare("SELECT * FROM registro WHERE id = id");
            $s ->bindValue(":id", $_GET['id']);
        }else{
            $s = $c -> prepare("SELECT * FROM registro");
        }
        $s -> execute();
        $s -> setFetchMode(PDO::FETCH_ASSOC);
        $r = $s ->fetchAll();
        header("http/1.1 200 OK");
        echo json_encode($r);
        break;

    case 'POST':
        if(isset($_POST['sensor']) && isset($_POST['valor'])){
            $c = conexion();
            $s = $c -> prepare ("INSERT INTO registro(user, sensor, valor, fecha) VALUES (:u, :s, :v, NOW())");
            $s -> bindValue(":u", $user);
            $s -> bindValue(":s", $_POST['sensor']);
            $s -> bindValue(":v", $_POST['valor']);
            $s -> execute();
            if($s -> rowCount() > 0){
                header("http/1.1 201 created");
                echo json_encode(["add" => "Y", "id" => $c -> lastInsertId()]);
            }else{
                header("http/1.1 400 Bad Request");
                echo json_encode(["add" => "N"]);
            }
        }else{
            header("http/1.1 400 Bad Request");
            echo "Faltan Datos";
        }
        break;
        
    case 'PUT':
        if(isset($_GET['id'])){
            $sql = "UPDATE registro SET ";
            (isset($_GET['user'])) ?  $sql .= "user = :u, " : null;
            (isset($_GET['sensor'])) ?  $sql .= "sensor = :s, " : null;
            (isset($_GET['valor'])) ?  $sql .= "valor = :v, " : null;
            (isset($_GET['fecha'])) ?  $sql .= "fecha = :f, " : null;
            $sql = substr($sql, 0, -2);
            $sql .= "WHERE id = :id"
            $c = conexion();
            $s = $c -> prepare("");
            (isset($_GET['user'])) ? $s ->bindValue(":u", $_GET['user']) : null;
            (isset($_GET['sensor'])) ?$s ->bindValue(":s", $_GET['sensor']) : null;
            (isset($_GET['valor'])) ?$s ->bindValue(":v", $_GET['valor']) : null;
            (isset($_GET['fecha'])) ? $s ->bindValue(":f", $_GET['fecha']) : null;
            $s ->bindValue(":id", $_GET['id']);
            $s -> execute();
            if($s -> rowCount() > 0){
                header("http/1.1 200 Ok");
                echo json_encode(["add" => "Y", "id" => $c -> lastInsertId()]);
            }else{
                header("http/1.1 400 Bad Request");
                echo json_encode(["Update" => "N"]);
            }
        }else{
            header("http/1.1 400 Bad Request");
            echo "Faltan Datos";
        }
        break;
        
    case 'DELETE':
        if(isset($_GET['id'])){
            $c = conexion();
            $s = $c -> prepare("DELETE FROM registro WHERE id = :id");
            $s ->bindValue(":id", $_GET{'id'});
            $s -> execute();
            if($s -> rowCount() > 0){
                header("http/1.1 200 Ok");
                echo json_encode(["add" => "Y", "id" => $c -> lastInsertId()]);
            }else{
                header("http/1.1 400 Bad Request");
                echo json_encode(["Delete" => "N"]);
        }else{
            header("http/1.1 400 Bad Request");
            echo "Faltan Datos";
        }
        break;

    default:
        break;
}