<?php
session_start();
require_once 'conection.php';

if(isset($_GET['indeciso'])){


        $traerAlumnos = $pdo->prepare('SELECT COUNT(email) AS EMAIL, alumno_id as id ,email AS correo, firebase_id as firebase FROM alumnos WHERE email=:email');
        $traerAlumnos->bindParam(':email', $_GET['email']);
        $traerAlumnos->execute();
        $encontrado = 0;
        foreach($traerAlumnos as $valor){
            if($valor['EMAIL']>0 && $valor['firebase'] == $_GET['firebase_id']){
                $_SESSION['user_id'] = $valor['id'];
                header('Location: homealumno2.php');
                $encontrado = 1;
            }elseif($valor['EMAIL']>0 && $valor['firebase']==0){
                $insertFirebase = $pdo->prepare('UPDATE alumnos SET firebase_id=:firebase_id WHERE email=:email');
                $insertFirebase->bindParam(':firebase_id', $_GET['firebase_id']);
                $insertFirebase->bindParam(':email', $_GET['email']);
                $insertFirebase->execute();
                $_SESSION['user_id'] = $valor['id'];
                header('Location: homealumno2.php');
                $encontrado = 1;
            }
        }
        
        if($encontrado == 0){

            $traerTutores = $pdo->prepare('SELECT COUNT(email) AS EMAIL, tutor_id as id ,email AS correo, firebase_id as firebase FROM tutores WHERE email=:email');
            $traerTutores->bindParam(':email', $_GET['email']);
            $traerTutores->execute();
    
            foreach($traerTutores as $valor){
                if($valor['EMAIL']>0 && $valor['firebase'] == $_GET['firebase_id']){
                    $_SESSION['user_id'] = $valor['id'];
                    header('Location: hometutor.php');
                    $encontrado = 1;
                }elseif($valor['EMAIL']>0 && empty($valor['firebase'])){
                    $insertFirebase = $pdo->prepare('UPDATE tutores SET firebase_id =:firebase_id WHERE email=:email');
                    $insertFirebase->bindParam(':firebase_id', $_GET['firebase_id']);
                    $insertFirebase->bindParam(':email', $_GET['email']);
                    $insertFirebase->execute();
                    $_SESSION['user_id'] = $valor['id'];
                    header('Location: hometutor.php');
                    $encontrado = 1;
                }
            }  
        }

        if($encontrado == 0){
            $traerAdmin = $pdo->prepare('SELECT COUNT(email) AS EMAIL, administrador_id as id ,email AS correo FROM administradores WHERE email=:email');
            $traerAdmin->bindParam(':email', $_GET['email']);
            $traerAdmin->execute();
    
            foreach($traerAdmin as $valor){
                if($valor['EMAIL']>0 && $valor['correo'] == $_GET['email']){
                    $_SESSION['user_id'] = $valor['id'];
                    header('Location: homeadministrador.php');
                    $encontrado = 1;
                }
            }  
        }

        if($encontrado == 0){
            $verificar = $pdo->prepare('SELECT COUNT(firebase_id) AS ID, eleccion AS completo FROM indecisos WHERE firebase_id=:firebase_id');
            $verificar->bindParam(':firebase_id', $_GET['firebase_id']);
            $verificar->execute();
            foreach($verificar as $valor){
                echo 'entro al ciclo';
                if($valor['ID'] == 0){
                    $indeciso = $pdo->prepare('INSERT INTO indecisos (email, firebase_id) VALUES (:email, :firebase_id)');
                    $indeciso->bindParam(':email', $_GET['email']);
                    $indeciso->bindParam(':firebase_id', $_GET['firebase_id']);
                    $indeciso->execute();
                    header('Location: hometutor.php?indeciso=1&email='.$_GET['email'].'&firebase_id='.$_GET['firebase_id']);
                    $encontrado = 1;
                }elseif($valor['ID']>0 && $valor['completo']==0){
                    header('Location: hometutor.php?indeciso=1');
                    $encontrado = 1;
                }
            }

        }

        
    }
?>
