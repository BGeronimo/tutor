<?php
    require_once 'conection.php';
    session_start();

    
    $retenerEmail = "";

    if(!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['confirmPassword'])){

        $verificarEmailAdmin = $pdo->prepare('SELECT COUNT(administradores.email) as conteoAdmin FROM administradores WHERE administradores.email=:email');
        $verificarEmailAdmin->bindParam(':email',$_POST['email']);
        $verificarEmailAdmin->execute();
        $verificarEmailAlum = $pdo->prepare('SELECT COUNT(alumnos.email) as conteoAlum FROM alumnos WHERE alumnos.email=:email');
        $verificarEmailAlum->bindParam(':email',$_POST['email']);
        $verificarEmailAlum->execute();
        $verificarEmailTuto = $pdo->prepare('SELECT COUNT(tutores.email) as conteoTuto FROM tutores WHERE tutores.email=:email');
        $verificarEmailTuto->bindParam(':email',$_POST['email']);
        $verificarEmailTuto->execute();
        $conteoAdmin = 0;
        foreach($verificarEmailAdmin as $valor){
            $conteoAdmin = $valor['conteoAdmin'];
        }
        $conteoAlum = 0;
        foreach($verificarEmailAlum as $valor){
            $conteoAlum = $valor['conteoAlum'];
        }
        $conteoTuto = 0;
        foreach($verificarEmailTuto as $valor){
            $conteoTuto = $valor['conteoTuto'];
        }


        if($conteoAdmin == 0 && $conteoAlum == 0 && $conteoTuto ==0){
            $registrarTutor = $pdo->prepare('INSERT INTO tutores (email, password) values (:email,:password)');
            $retenerEmail = $_POST['email'];
            $registrarTutor->bindParam(':email', $_POST['email']);

            $compara= $_POST['confirmPassword'] === $_POST['password'];

            if(!empty($_POST['confirmPassword']) && $compara){
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $registrarTutor->bindParam(':password', $password);
                if($registrarTutor->execute()){
                    $message = 'usuario creado correctamente';
                }else{
                    $message = 'ups, ha ocurrido un error';
                }
            }else{
                $messagePass = 'las contraseñas no coinciden';
            }

            $traerTutor = $pdo->query('SELECT * FROM  tutores');
            while($row = $traerTutor->fetch(PDO::FETCH_ASSOC)){
                if($row['email'] = $retenerEmail){
                    $idTuto = $row['tutor_id'];
                    $_SESSION['user_id'] = $idTuto;
                    header('Location: actualizartutor.php');
                }
            }

        }else{
            echo 'este correo ya ha sido registrado';
        }


    }   
    ?>


