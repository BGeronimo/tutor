<?php
    require_once 'conection.php';
    session_start();

    if($_GET['response']==1){
        $token = $_SESSION['orderid'];
        $cantidad = $_SESSION['amount'];
        $response = 1;
        $transactionid = $_GET['transactionid'];
        $avsresponse = $_GET['avsresponse'];
        $cvvresponse = $_GET['cvvresponse'];
        $time = $_SESSION['time'];
        $key = "3213118984651231819815661";
        
        $hash = $_GET["hash"];
        $generarHash = md5($token."|".$cantidad."|".$response."|".$transactionid."|".$avsresponse."|".$cvvresponse."|".$time."|".$key.);
        if($generarHash === $hash){
            $traerPuntos = $pdo->query('SELECT puntos FROM alumnos WHERE alumnos='.$_SESSION['user_id'].'');
            $puntosAnteriores = 0;
            foreach($traerPuntos as $valor){
                $puntosAnteriores = $valor['puntos'];
            }

            $puntosActualizados = $puntosAnteriores+$cantidad;

            $ingresarPuntos = $pdo->prepare('UPDATE alumnos SET puntos=:puntos WHERE alumno_id=:alumno_id');
            $ingresarPuntos->bindParam(':puntos', $puntosActualizados);
            $ingresarPuntos->bindParam(':alumno_id', $_SESSION['user_id']);
            if($ingresarPuntos->execute()){
                $_SESSION['orderid'] = "";
                $_SESSION['amount'] = 0;
                $_SESSION['time'] = 0;

            }

        }else{
            echo 'subplantacion de identidad';
        }
    }

    if($_GET['response']==2){
        echo '';
    }

    if($_GET['response']==3){
        echo '';
    }


?>
