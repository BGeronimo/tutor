<?php
    require_once 'conection.php';
    session_start();

    $traerAlumno = $pdo->query('SELECT * FROM  alumnos');
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
        $verificarEmailInde = $pdo->prepare('SELECT COUNT(indecisos.email) as conteoInde FROM indecisos WHERE indecisos.email=:email');
        $verificarEmailInde->bindParam(':email',$_POST['email']);
        $verificarEmailInde->execute();
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
        $conteoInde = 0;
        foreach($verificarEmailInde as $valor){
            $conteoInde = $valor['conteoInde'];
        }
        if($conteoAdmin == 0 && $conteoAlum == 0 && $conteoTuto ==0 && $conteoInde == 0){
            $sql = "INSERT INTO alumnos (email, password) values (:email,:password)";
            $stmt = $pdo->prepare($sql);
            $retenerEmail = $_POST['email'];
            $stmt->bindParam(':email', $_POST['email']);

            $compara= $_POST['confirmPassword'] === $_POST['password'];

            if(!empty($_POST['confirmPassword']) && $compara){
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $stmt->bindParam(':password', $password);
                if($stmt->execute()){
                    echo 'usuario creado correctamente';
                }else{
                    echo 'ups, ha ocurrido un error';
                }
            }else{
                echo 'las contraseñas no coinciden';
            }

            while($row = $traerAlumno->fetch(PDO::FETCH_ASSOC)){
                if($row['email'] = $retenerEmail){
                    $idAlumno = 1+$row['alumno_id'];
                    $_SESSION['user_id'] = $idAlumno;
                    header('Location: homealumno.php');
                }
            }
        }elseif($conteoInde == 1){
            $eleccion = 1;
            $actualizarIndeciso = $pdo->prepare('UPDATE indecisos SET eleccion=:eleccion WHERE email=:email');
            $actualizarIndeciso->bindParam(':eleccion',$eleccion);
            $actualizarIndeciso->bindParam(':email',$_POST['email']);
            $actualizarIndeciso->execute();
            $sql = "INSERT INTO alumnos (email, password) values (:email,:password)";
            $stmt = $pdo->prepare($sql);
            $retenerEmail = $_POST['email'];
            $stmt->bindParam(':email', $_POST['email']);

            $compara= $_POST['confirmPassword'] === $_POST['password'];

            if(!empty($_POST['confirmPassword']) && $compara){
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $stmt->bindParam(':password', $password);
                if($stmt->execute()){
                    echo 'usuario creado correctamente';
                }else{
                    echo 'ups, ha ocurrido un error';
                }
            }else{
                echo 'las contraseñas no coinciden';
            }

            while($row = $traerAlumno->fetch(PDO::FETCH_ASSOC)){
                if($row['email'] = $retenerEmail){
                    $idAlumno = 1+$row['alumno_id'];
                    $_SESSION['user_id'] = $idAlumno;
                    header('Location: homealumno.php');
                }
            }
        }else{
            echo 'este correo ya ha sido registrado';
        }
        

    }   
    ?>


