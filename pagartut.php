<?php
    require_once 'conection.php';
    require_once 'zonahoraria.php';
    session_start();

    if(isset($_POST['pagar'])){
        if(!empty($_POST['nocheque'])){
            $fecha = zonaHoraria("Y-m-d H:i:s");
            $id = $_POST['tutor_id'];
            $cantidad = (int)$_POST['cantidad'];
            $nocheque = $_POST['nocheque'];

            $comprobarPuntos = $pdo->query('SELECT puntos FROM tutores WHERE tutor_id='.$_POST['tutor_id'].'');
            $puntosValidos = 0;
            foreach($comprobarPuntos as $valor){
                $puntosValidos = $valor['puntos']-($valor['puntos']*0.15);
            }


            if($puntosValidos>=$cantidad){
                $puntos = $puntosValidos-$cantidad;

                $ingresarRegistro = $pdo->prepare('INSERT INTO pagostutor (tutor_id,cantidad,fecha,nocheque,administrador_id)
                VALUES (:tutor_id,:cantidad,:fecha,:nocheque,:administrador_id)');
                $ingresarRegistro->bindParam(':tutor_id', $id);
                $ingresarRegistro->bindParam(':cantidad',$cantidad);
                $ingresarRegistro->bindParam(':fecha',$fecha);
                $ingresarRegistro->bindParam(':nocheque',$nocheque);
                $ingresarRegistro->bindParam(':administrador_id',$_SESSION['user_id']);
                $ingresarRegistro->execute();
    
                $actualizarTutor = $pdo->prepare('UPDATE tutores SET puntos=:puntos WHERE tutor_id=:tutor_id');
                $actualizarTutor->bindParam(':puntos',$puntos);
                $actualizarTutor->bindParam(':tutor_id',$id);
                if($actualizarTutor->execute()){
                    echo'<script>
                    alert("se ha hecho todo correctamente");
                    </script>';
                }
            }else{
                echo'<script>
                    alert("ingresa una cantidad valida");
                    </script>';
            }
        }
    }

    if(isset($_POST['hacerCheque'])){
        if(!empty($_POST['nocheque'])){
            $fecha = zonaHoraria("Y-m-d H:i:s");
            $nocheque = $_POST['nocheque'];
            $trarePuntos = $pdo->prepare('SELECT puntos FROM tutores WHERE tutor_id=:tutor_id');
            $trarePuntos->bindParam(':tutor_id',$_SESSION['idTutor']);
            $trarePuntos->execute();
            $puntosAntiguos = 0;
            foreach($trarePuntos as $valor){
                $puntosAntiguos = $valor['puntos'];
            }

            $puntos = $puntosAntiguos-$_SESSION['cantidad'];

            $ingresarRegistro = $pdo->prepare('INSERT INTO pagostutor (tutor_id,cantidad,fecha,nocheque,administrador_id)
            VALUES (:tutor_id,:cantidad,:fecha,:nocheque,:administrador_id)');
            $ingresarRegistro->bindParam(':tutor_id', $_SESSION['idTutor']);
            $ingresarRegistro->bindParam(':cantidad',$_SESSION['cantidad']);
            $ingresarRegistro->bindParam(':fecha',$fecha);
            $ingresarRegistro->bindParam(':nocheque',$nocheque);
            $ingresarRegistro->bindParam(':administrador_id',$_SESSION['user_id']);
            $ingresarRegistro->execute();

            $actualizarTutor = $pdo->prepare('UPDATE tutores SET puntos=:puntos WHERE tutor_id=:tutor_id');
            $actualizarTutor->bindParam(':puntos',$puntos);
            $actualizarTutor->bindParam(':tutor_id',$_SESSION['idTutor']);
            if($actualizarTutor->execute()){
                echo'<script>
                alert("se ha hecho todo correctamente");
                </script>';
                $mensajeVisto = $pdo->query('UPDATE mensajes SET visto=1 WHERE mensajes_id='.$_SESSION['mensaje'].'');
            }
        }else{
            echo'<script>
            alert("ingresa el numero de cheque");
            </script>'; 
        }
    }
?>


<!DOCTYPE html>
	<html lang="zxx" class="no-js">
	<head>
		<!-- Mobile Specific Meta -->
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- Favicon-->
		<link rel="shortcut icon" href="img/fav.png">
		<!-- Author Meta -->
		<meta name="author" content="colorlib">
		<!-- Meta Description -->
		<meta name="description" content="">
		<!-- Meta Keyword -->
		<meta name="keywords" content="">
		<!-- meta character set -->
		<meta charset="UTF-8">
		<!-- Site Title -->
		<title>Materias | Tutoeri</title>
		<!-- Autenticacion con firebase -->
		<script type="text/javascript" src="auth.js"></script>

		<link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet"> 
			<!--
			CSS
			============================================= -->
			<link rel="stylesheet" href="css/linearicons.css">
			<link rel="stylesheet" href="css/font-awesome.min.css">
			<link rel="stylesheet" href="css/bootstrap.css">
			<link rel="stylesheet" href="css/magnific-popup.css">
			<link rel="stylesheet" href="css/jquery-ui.css">				
			<link rel="stylesheet" href="css/nice-select.css">							
			<link rel="stylesheet" href="css/animate.min.css">
			<link rel="stylesheet" href="css/owl.carousel.css">				
			<link rel="stylesheet" href="css/main.css">
		</head>
		<body>

		<!---->
			<header id="header">
                <div class="header-top">
				<div class="container main-menu">
					<div class="row align-items-center justify-content-between d-flex">
				      <div id="logo">
				        <a href="index.html"><img src="img/logo.png" alt="" title="" /></a>
				      </div>
				      <nav id="nav-menu-container">
				        <ul class="nav-menu">
				          <li class="activo"><a href="homeadministrador.php">Inicio</a></li>
				          <li><a href="materias.php">Materias</a></li>
				          <li><a href="pagartutor.php">Pagar a tutor</a></li>
			          					          		          
				          <li style="border: 1px dashed white; border-radius: 3px;"><a href="logout.php" onClick="logOut();">Salir</a></li>
				        </ul>
				      </nav><!-- #nav-menu-container -->					      		  
					</div>
				</div>

			</header><!-- #header -->
			  
			<!-- start banner Area -->
			<section class="about-banner relative">
				<div class="overlay overlay-bg"></div>
				<div class="container">				
					<div class="row d-flex align-items-center justify-content-center">
						<div class="about-content col-lg-12">
							<h1 class="text-white">
								Pagar a tutor				
							</h1>	
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->	

			<!-- Start destinations Area -->
			<section class="destinations-area section-gap">

                    <form action="pagartutor.php" method="post">
                        <div>
                            <label>correo del profesor</label>
                            <input type="email" name="email">
                            <input type="submit" value="buscar" name="buscar">
                        </div>
                    </form>
                    <?php
                    if(isset($_POST['buscar'])){
                        echo $_POST['email'];
                        $buscarTutores = $pdo->prepare('SELECT * FROM tutores WHERE email=:email');
                        $buscarTutores->bindParam(':email',$_POST['email']);
                        $buscarTutores->execute();

                        foreach($buscarTutores as $valor){
                            $valorAPagar = $valor['puntos']-($valor['puntos']*0.15);
                            if($valor['puntos']<200){
                                echo '<div class="alert alert-primary" role="alert">';
                                echo 'este tutor no tiene los puntos suficientes para cobrar su pago';
                                echo '</div>';
                            }
                            echo '<form action="pagartutor.php" method="post">';
                            echo '<div>';
                            echo '<h3>'.$valor['puntos'].' puntos ------ equivale a Q.'.$valorAPagar.'</h3>';
                            echo '<h4>'.$valor['nombres'].', '.$valor['apellidos'].'</h4>';
                            echo '<p>'.$valor['horasclase'].' horas de clase</p>';
                            echo '</div>';
                            echo '<div>';
                            echo '<label>Numero de cheque que se le entrega</label>';
                            echo '<input type="text" onkeypress="return valida(event)" name="nocheque" maxlength="12" onkeyup="validar(this.form)">';
                            echo '</div>';
                            echo '<input type="text" value="'.$valor['tutor_id'].'" name="tutor_id" hidden="true">';
                            echo '<input type="text"  name="cantidad" onkeypress="return valida2(event)" placeholder="cantidad a pagar">';
                            if($valor['puntos']<200){
                                echo '<input type="submit" value="cobrar su paga" disabled>';
                            }else{
                                echo '<input type="submit" value="cobrar su paga" name="pagar" disabled="disabled">';
                            }
                            echo '</form>';
                            echo '';
                            
                        }
                    }
                    if(isset($_GET['id'])){
                        echo '<hr>';
                        $_SESSION['idTutor'] = $_GET['id'];
                        $_SESSION['cantidad'] = $_GET['cantidad'];
                        $_SESSION['direccion'] = $_GET['direccion'];
                        $_SESSION['mensaje'] = $_GET['mensaje'];
                        
                        echo '<form action="pagartutor.php" method="post">';
                        echo '<input type="text" onkeypress="return valida(event)" name="nocheque" maxlength="12" onkeyup="validar(this.form)">';
                        echo '<input type="text"  name="cantidad" value="'.$_SESSION['cantidad'].'" onkeypress="return valida2(event)" disabled>';
                        echo '<input type="text" name="direccion" value="'.$_SESSION['direccion'].'" disabled>';
                        echo '<input type="text" name="mensaje" value="'.$_SESSION['mensaje'].'" hidden="true">';
                        echo '<input type="submit" value="hacer registro de cheque" name="hacerCheque">';
                        echo '</form>';
                    }

                    ?>
			</section>
			<!-- End destinations Area -->
			

			<!-- Start home-about Area -->
			<!--<section class="home-about-area">
				<div class="container-fluid">
					<div class="row align-items-center justify-content-end">
						<div class="col-lg-6 col-md-12 home-about-left">
							<h1>
								Did not find your Package? <br>
								Feel free to ask us. <br>
								We‘ll make it for you
							</h1>
							<p>
								inappropriate behavior is often laughed off as “boys will be boys,” women face higher conduct standards especially in the workplace. That’s why it’s crucial that, as women, our behavior on the job is beyond reproach. inappropriate behavior is often laughed.
							</p>
							<a href="#" class="primary-btn text-uppercase">request custom price</a>
						</div>
						<div class="col-lg-6 col-md-12 home-about-right no-padding">
							<img class="img-fluid" src="img/hotels/about-img.jpg" alt="">
						</div>
					</div>
				</div>	
			</section>-->
			<!-- End home-about Area -->

		<!-- start footer Area -->		
		<footer class="footer-area section-gap">
			<div class="container">

				<div class="row">
					<div class="col-lg-4  col-md-6 col-sm-6">
						<div class="single-footer-widget">
							<h6>Acerca de Tutoeri</h6>
							<p>
								The world has become so fast paced that people don’t want to stand by reading a page of information, they would much rather look at a presentation and understand the message. It has come to a point 
							</p>
						</div>
					</div>
					<div class="col-lg-4 col-md-6 col-sm-6">
						<div class="single-footer-widget">
							<h6>Links</h6>
							<div class="row">
								<div class="col">
									<ul>
										<li><a href="#">Home</a></li>
										<li><a href="#">Feature</a></li>
										<li><a href="#">Services</a></li>
										<li><a href="#">Portfolio</a></li>
									</ul>
								</div>
								<div class="col">
									<ul>
										<li><a href="#">Team</a></li>
										<li><a href="#">Pricing</a></li>
										<li><a href="#">Blog</a></li>
										<li><a href="#">Contact</a></li>
									</ul>
								</div>										
							</div>							
						</div>
					</div>							
					<div class="col-lg-4  col-md-6 col-sm-6">
						<div class="single-footer-widget">
							<h6>Redes Sociales</h6>
							<p>
								Encuéntranos en nuestras redes sociales para más información.									
							</p>								
							<div class=" footer-social">
									<a href="#"><i class="fa fa-facebook"></i></a>
									<a href="#"><i class="fa fa-twitter"></i></a>
									<a href="#"><i class="fa fa-dribbble"></i></a>
									<a href="#"><i class="fa fa-behance"></i></a>
								</div>
						</div>
					</div>
										
				</div>

				<div class="footer-bottom text-center align-items-center">
					<p class=" footer-text text-center m-0">
							&copy;	Copyright <script>document.write(new Date().getFullYear());</script> | Todos los derechos reservados.</p>
					
				</div>
			</div>
		</footer>
		<!-- End footer Area -->	
			

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h2 class="modal-title" id="exampleModalLabel">Registrarse</h2>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
			<div class="">
				<ul class="nav nav-tabs" id="myTab" role="tablist">
					<li class="nav-item " style="position: relative; width: 100%; min-height: 1px; flex: 0 0 50%; max-width: 50%;">
					<a class="nav-link active" style="font-family: 'K2D', sans-serif; font-size: 19px;" id="flight-tab" data-toggle="tab" href="#flight" role="tab" aria-controls="flight" aria-selected="true">Alumno</a>
					</li>
					<li class="nav-item " style="position: relative; width: 100%; min-height: 1px; flex: 0 0 50%; max-width: 50%;">
					<a class="nav-link" style="font-family: 'K2D', sans-serif; font-size: 19px;" id="hotel-tab" data-toggle="tab" href="#hotel" role="tab" aria-controls="hotel" aria-selected="false">Tutor</a>
					</li>
				</ul>
				<div class="tab-content" id="myTabContent">
					<br>
					<a href="" class="btn btn-block" style="background-color: #DD4B39; color: white;">Iniciar con Google</a>
					<a href="" class="btn btn-block" style="background-color: #3B5998; color: white;">Iniciar con Facebook</a>
					<a href="" class="btn btn-block" style="background-color: #55ACEE; color: white;">Iniciar con Twitter</a>
					<div class="text-center" style="margin-top: 10px;">
						<h6>- o -</h6>
					</div>
					<div class="tab-pane fade show active" id="flight" role="tabpanel" aria-labelledby="flight-tab">
						<form action="#">
							<div class="input-group-icon mt-10">
									<div class="icon"><span class="lnr lnr-envelope" style="font-size: 19px;"></span></div>
									<input type="email" name="email" placeholder="Correo electrónico" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Correo'" required class="single-input">
								</div>
							<div class="input-group-icon mt-10">
									<div class="icon"><span class="lnr lnr-lock" style="font-size: 19px;"></span></div>
									<input type="password" name="contrasena" placeholder="Contraseña" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Contraseña'" required class="single-input">
								</div>
							<div class="input-group-icon mt-10">
								<div class="icon"><span class="lnr lnr-sync" style="font-size: 18px;"></span></div>
								<input type="password" name="repass" placeholder="Repite la contraseña" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Repetir contraseña'" required class="single-input">
							</div>
							<div class="text-center" style="margin-top: 10px;">
								<input type="submit" name="entrar" value="Entrar" class="primary-btn text-uppercase">
							</div>
						</form>
					</div>
					<div class="tab-pane fade" id="hotel" role="tabpanel" aria-labelledby="hotel-tab">
						<form action="#">
							<div class="input-group-icon mt-10">
									<div class="icon"><span class="lnr lnr-envelope" style="font-size: 19px;"></span></div>
									<input type="email" name="email" placeholder="Correo electrónico" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Correo'" required class="single-input">
								</div>
							<div class="input-group-icon mt-10">
									<div class="icon"><span class="lnr lnr-lock" style="font-size: 19px;"></span></div>
									<input type="password" name="contrasena" placeholder="Contraseña" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Contraseña'" required class="single-input">
								</div>
							<div class="input-group-icon mt-10">
								<div class="icon"><span class="lnr lnr-sync" style="font-size: 18px;"></span></div>
								<input type="password" name="repass" placeholder="Repite la contraseña" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Repetir contraseña'" required class="single-input">
							</div>
							<div class="text-center" style="margin-top: 10px;">
								<input type="submit" name="entrar" value="Entrar" class="primary-btn text-uppercase">
							</div>
						</form>							  	
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>
  </div>
  
  		<!-- preloader -->
		  <div id='preloader'><div class='preloader'></div></div>
		  <!-- /preloader -->

			<script src="js/vendor/jquery-2.2.4.min.js"></script>
			<script src="js/popper.min.js"></script>
			<script src="js/vendor/bootstrap.min.js"></script>			
			<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBhOdIF3Y9382fqJYt5I_sswSrEw5eihAA"></script>		
 			<script src="js/jquery-ui.js"></script>					
  			<script src="js/easing.min.js"></script>			
			<script src="js/hoverIntent.js"></script>
			<script src="js/superfish.min.js"></script>	
			<script src="js/jquery.ajaxchimp.min.js"></script>
			<script src="js/jquery.magnific-popup.min.js"></script>						
			<script src="js/jquery.nice-select.min.js"></script>					
			<script src="js/owl.carousel.min.js"></script>							
			<script src="js/mail-script.js"></script>	
			<script src="js/main.js"></script>	
			<script src="https://cdn.linearicons.com/free/1.0.0/svgembedder.min.js"></script>
			<link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">

            <script type="text/javascript" src="auth.js"></script>
		</body>
	
		<script>
		(function($) {
	"use strict"
	
	// Preloader
	$(window).on('load', function() {
		$("#preloader").delay(350).fadeOut();
	});

	// Mobile Toggle Btn
	$('.navbar-toggle').on('click',function(){
		$('#header').toggleClass('nav-collapse')
	});
	
})(jQuery);


    function valida(e){
        tecla = (document.all) ? e.keyCode : e.which;

        //Tecla de retroceso para borrar, siempre la permite
        if (tecla==8){
            return true;
        }

        // Patron de entrada, en este caso solo acepta numeros
        patron =/[0-9]/;
        tecla_final = String.fromCharCode(tecla);
        return patron.test(tecla_final);
    }

    function valida2(e){
        tecla = (document.all) ? e.keyCode : e.which;

        //Tecla de retroceso para borrar, siempre la permite
        if (tecla==8){
            return true;
        }

        // Patron de entrada, en este caso solo acepta numeros
        patron =/[0-9-.]/;
        tecla_final = String.fromCharCode(tecla);
        return patron.test(tecla_final);
    }

    function validar(frm) {
    frm.pagar.disabled = false;
    for (i=0; i<3; i++)
        if (frm['txt'+i].value =='') return
    frm.pagar.disabled = true;
    }

		</script>
		<style>
		.ec-stars-wrapper {
	/* Espacio entre los inline-block (los hijos, los `a`) 
	   http://ksesocss.blogspot.com/2012/03/display-inline-block-y-sus-empeno-en.html */
	font-size: 0;
	/* Podríamos quitarlo, 
		pero de esta manera (siempre que no le demos padding), 
		sólo aplicará la regla .ec-stars-wrapper:hover a cuando
		también se esté haciendo hover a alguna estrella */
	display: inline-block;
}
.ec-stars-wrapper a {
	text-decoration: none;
    display: inline-block;
    font-size: 20px;
    font-size: 25px;
    color: #bababc;
}

.ec-stars-wrapper:hover a {
	color: #3D9F97;
}
/*
 * El selector de hijo, es necesario para aumentar la especifidad
 */
.ec-stars-wrapper > a:hover ~ a {
	color: #bababc;
}

.activo{
	border: 1px outset #288894;
    border-radius: 3px;
    background: linear-gradient(#3D9F97, #00548F);
}
		</style>
	</html>

