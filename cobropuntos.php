<?php
    require_once 'conection.php';
    require 'zonahoraria.php';
    session_start();
    $porcentaje = 0.15;

    $traerTutor = $pdo->prepare('SELECT *  FROM tutores WHERE tutor_id=:tutor_id');
    $traerTutor->bindParam(':tutor_id', $_SESSION['user_id']);
    $traerTutor->execute();

    if(isset($_POST['enviar'])){
        $token = $_POST['token'];
        $confirmacion = $_POST['confirmacion'];

        $verificar = $pdo->prepare('SELECT COUNT(bancopuntos_id) AS conteo, horasclase as horas, puntos as puntos FROM bancopuntos 
        WHERE tutor_id=:tutor_id AND bancopuntos_id=:token AND claveconfirmacion=:confirmacion AND cobropuntos=0');
        $verificar->bindParam(':tutor_id', $_SESSION['user_id']);
        $verificar->bindParam(':token', $token);
        $verificar->bindParam(':confirmacion', $confirmacion);
        $verificar->execute();

        $traerPuntosAnteriores = $pdo->query('SELECT puntos, horasclase FROM tutores WHERE tutor_id='.$_SESSION['user_id'].'');
        $puntosAnteriores = 0;
        $horasAnteriores = 0;
        foreach($traerPuntosAnteriores as $valor){
            $puntosAnteriores = $valor['puntos'];
            $horasAnteriores = $valor['horasclase'];
        }

        foreach($verificar as $valor){

            if($valor['conteo']==1){
                $fecha = zonaHoraria("Y-m-d H:i:s");
                $cobro=1;
                $cobrarPuntos = $pdo->prepare('UPDATE bancopuntos SET fechafin=:fecha, cobropuntos=:cobro WHERE bancopuntos_id=:token');
                $cobrarPuntos->bindParam(':fecha',$fecha);
                $cobrarPuntos->bindParam(':cobro',$cobro);
                $cobrarPuntos->bindParam(':token',$token);
                $cobrarPuntos->execute();


                $puntosActualizados = $puntosAnteriores + ($valor['puntos']- ($valor['puntos']*$porcentaje));
                $horasActualizadas = $horasAnteriores + $valor['horas'];

                $actualizarPerfil = $pdo->prepare('UPDATE tutores SET horasclase=:horas, puntos=:puntos WHERE tutor_id=:tutor_id');
                $actualizarPerfil->bindParam(':horas',$horasActualizadas);
                $actualizarPerfil->bindParam(':puntos',$puntosActualizados);
                $actualizarPerfil->bindParam(':tutor_id',$_SESSION['user_id']);
                if($actualizarPerfil->execute()){
                    $sesion = 0;
                    $actualizarCalificaciones = $pdo->prepare('UPDATE calificaciones SET sesion=:sesion WHERE claveconfirmacion=:claveconfirmacion AND sesion=1');
                    $actualizarCalificaciones->bindParam(':sesion',$sesion);
                    $actualizarCalificaciones->bindParam(':claveconfirmacion',$confirmacion);
                    $actualizarCalificaciones->execute();

                    echo'<script type="text/javascript">
                    alert("tu cobro se ha realizado exitosamente");
                    location.href = "hometutor.php";
                    </script>';
                }
            }else{

            }
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
	<title>Cobrar Puntos | Tutoeri</title>

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
		<body>	
		<header id="header">
			<?php
				while($row = $traerTutor->fetch(PDO::FETCH_ASSOC)){
					if($row['datoscompletos'] == 0){
						echo '
						<div class="header-top">
							<div class="container">
								<div class="row align-items-center">
									<div class="col-lg-6 col-sm-6 col-6 header-top-left">
										<ul>
											<li><a href="">['.$row['puntos'].'] Puntos</a></li>
										</ul>			
									</div>
									<div class="col-lg-6 col-sm-6 col-6 header-top-right">
										<ul>
											<li>
												<a href="pedircheque.php">Pedir Cheque</a>
											</li>
										</ul>
									</div>
								</div>			  					
							</div>
						</div>

					<div class="container main-menu">
						<div class="row align-items-center justify-content-between d-flex">
							<div id="logo">
								<a href="index.html"><img src="img/logo.png" alt="" title="" /></a>
							</div>
							<nav id="nav-menu-container">
								<ul class="nav-menu">
								<li class="activo"><a href="homeTutor.html">Inicio</a></li>
								<li><a href="actualizarTutor.html">Mi Cuenta</a></li>		          					          		          
								<li style="border: 1px dashed white; border-radius: 3px;"><a href="logout.php" onClick="logOut();">Salir</a></li>
								</ul>
							</nav><!-- #nav-menu-container -->					      		  
						</div>
					</div>
						
						';
					}else{
						echo '
						<div class="header-top">
						<div class="container">
						  <div class="row align-items-center">
							  <div class="col-lg-6 col-sm-6 col-6 header-top-left">
								  <ul>
									  <li><a href="#">['.$row['puntos'].'] Puntos</a></li>
								  </ul>			
							  </div>
							  <div class="col-lg-6 col-sm-6 col-6 header-top-right">
								<ul>
									<li>
										<a href="pedircheque.php">Pedir Cheque</a>
									</li>
								</ul>
							  </div>
						  </div>			  					
						</div>
					</div>
	
					<div class="container main-menu">
						<div class="row align-items-center justify-content-between d-flex">
						  <div id="logo">
							<a href="index.html"><img src="img/logo.png" alt="" title="" /></a>
						  </div>
						  <nav id="nav-menu-container">
							<ul class="nav-menu">
							  <li><a href="hometutor.php">Inicio</a></li>
							  <li><a href="actualizartutor.php">Mi Cuenta</a></li>
							  <li><a href="materiastutor.php">Materias</a></li>
							  <li><a href="pupilos.php">Pupilos</a></li>
							  <li class="activo"><a href="cobropuntos.php">Cobrar Puntos</a></li>			          					          		          
							  <li style="border: 1px dashed white; border-radius: 3px;"><a href="logout.php" onClick="logOut();">Salir</a></li>
							</ul>
						  </nav><!-- #nav-menu-container -->					      		  
						</div>
					</div>';
						$email = $row['email'];

					}
				}
			?>
				
			</header><!-- #header -->
			  
			<!-- start banner Area -->
			<section class="about-banner relative">
				<div class="overlay overlay-bg"></div>
				<div class="container">				
					<div class="row d-flex align-items-center justify-content-center">
						<div class="about-content col-lg-12">
							<h1 class="text-white">
								Cobrar Puntos				
							</h1>	
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->	

			<!-- Start destinations Area -->
			<section class="destinations-area section-gap">
					<div class="container">						
						<div class="">
								<div class="section-top-border">
										<div class="">
											<div class="col-lg-12 col-md-12">
												<form action="cobropuntos.php" method="post">
													<label for="">Selecciona el Token:</label>
													<div class="input-group-icon mt-10">
														<div class="icon"><i class="fa fa-money" aria-hidden="true"></i></div>
														<div class="form-select" id="default-select2">
															<select name="token" style="display: none;">
															<?php
																$traerTokens = $pdo->query('SELECT bancopuntos_id,puntos,fechainicio FROM bancopuntos WHERE cobropuntos=0 AND tutor_id='.$_SESSION['user_id'].'');
																foreach($traerTokens as $valor){
																	echo '<option value="'.$valor['bancopuntos_id'].'">'.$valor['bancopuntos_id'].' || por Q.'.$valor['puntos'].' || del '.$valor['fechainicio'].'</option>';
																} 
															?>
															</select>
														</div>
													</div>
													<br>
													<label for="">Ingrese el código de confirmación:</label>
													<div class="mt-10">
														<input type="text" name="confirmacion" placeholder="Código de confirmación" onfocus="this.placeholder = ''" onblur="this.placeholder = ''" required="" class="single-input">
													</div>
													<br>
													<div class="text-center">
														<input type="submit" value="Enviar" name="enviar" class="primary-btn col-lg-6">
													</div>
												</form>
											</div>
										</div>
									</div>
							
																																					
						</div>
					</div>	
				</section>

		<!-- start footer Area -->		
		<footer class="footer-area section-gap">
			<div class="container">


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
			
			.activ{
				border: 1px solid #01548f;
				border-radius: 3px;
				background: transparent;
				/*color: white;*/
			}
			
			#map_container{
			  position: relative;
			}
			#map{
				height: 0;
				overflow: hidden;
				padding-bottom: 22.25%;
				padding-top: 30px;
				position: relative;
			}
					</style>
					<script>
					$( document ).ready( function() {
			
			  
				//Google Maps JS
				//Set Map
				function initialize() {
						var myLatlng = new google.maps.LatLng(53.3333,-3.08333);
						var imagePath = 'http://m.schuepfen.ch/icons/helveticons/black/60/Pin-location.png'
						var mapOptions = {
							zoom: 11,
							center: myLatlng,
							mapTypeId: google.maps.MapTypeId.ROADMAP
						}
			
					var map = new google.maps.Map(document.getElementById('map'), mapOptions);
					//Callout Content
					var contentString = 'Some address here..';
					//Set window width + content
					var infowindow = new google.maps.InfoWindow({
						content: contentString,
						maxWidth: 500
					});
			
					//Add Marker
					var marker = new google.maps.Marker({
						position: myLatlng,
						map: map,
						icon: imagePath,
						title: 'image title'
					});
			
					google.maps.event.addListener(marker, 'click', function() {
						infowindow.open(map,marker);
					});
			
					//Resize Function
					google.maps.event.addDomListener(window, "resize", function() {
						var center = map.getCenter();
						google.maps.event.trigger(map, "resize");
						map.setCenter(center);
					});
				}
			
				google.maps.event.addDomListener(window, 'load', initialize);
			
			});
					</script>
			
			<style type="text/css">
				.tg  {
				border-collapse: collapse;
				border-spacing: 0;
				border-radius: 3px;
				width: 100%;
				margin-bottom: 60px;
				}
				.tg td{
				font-size: 14px;
				padding: 20px 20px;
				border: 3px solid #eff5fd;
				border-radius: 3px;
				overflow: hidden;
				text-align: center;
				vertical-align: middle;  
				}
				.tg th{
				font-size: 14px;
				padding: 20px 20px;
				border: 3px solid #015e9a;
				border-radius: 3px;
				overflow: hidden;
				background: #015e9a;
				color: white;
				}
				.tg .tg-zc5o{
				/* 	background-color: #ffffff;
				border-color: #999999; */
				text-align: center;
				text-align: center;
				vertical-align: middle;  
				}
				</style>
	</html>