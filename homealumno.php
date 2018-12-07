<?php
    require_once 'conection.php';
    session_start();

    $traerAlumno = $pdo->query('SELECT *  FROM alumnos WHERE alumno_id='.$_SESSION['user_id'].'');
    
    $traerAlumno2 = $pdo->query('SELECT *  FROM alumnos WHERE alumno_id='.$_SESSION['user_id'].'');

    if(isset($_POST['enterado'])){
        $visto = 1;
        $actualizarMensaje = $pdo->prepare('UPDATE mensajes SET visto=1 WHERE mensajes_id=:mensajes_id');
        $actualizarMensaje->bindParam(':mensajes_id',$_POST['idMensaje']);
        if($actualizarMensaje->execute()){
            header('Location: homealumno.php');
        }
	}
	
	if(isset($_POST['perfil'])){
		header('Location: perfiltutor.php?id='.$_POST['idperfil']);
	}

    $traerNoCalificados = $pdo->prepare('SELECT tutores.tutor_id as id, tutores.nombres AS nombre, tutores.apellidos AS apellido, tutores.imagenperfil AS imagen 
    FROM tutores, calificaciones WHERE calificaciones.alumno_id=:alumno_id AND calificaciones.calificacion=0 AND sesion=0 AND calificaciones.tutor_id=tutores.tutor_id');
    $traerNoCalificados->bindParam(':alumno_id',$_SESSION['user_id']);
    $traerNoCalificados->execute();

    if(isset($_POST['calificar'])){
        if(!empty($_POST['punteo'])){
            $calificar = $pdo->prepare('UPDATE calificaciones SET calificacion=:calificacion, comentario=:comentario, revision=1 WHERE tutor_id=:tutor AND alumno_id=:alumno AND calificacion=0');
            $calificar->bindParam(':calificacion', $_POST['punteo']);
            $calificar->bindParam(':comentario', $_POST['comentario']);
            $calificar->bindParam(':tutor', $_POST['idtutor']);
            $calificar->bindParam(':alumno', $_SESSION['user_id']);
            $calificar->execute();
            header('Location: homealumno.php');
        }else{
            echo 'la calificacion es obligatoria';
        }

	}
	
	$receptor = "Alumno|".$_SESSION['user_id'];
	$traerMensajes = $pdo->prepare('SELECT mensajes_id, texto FROM mensajes WHERE receptor=:receptor AND visto=0');
	$traerMensajes->bindParam(':receptor',$receptor);
	$traerMensajes->execute();

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
		<title>Inicio | Tutoeri</title>
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
				<?php
				$correo = "";
				while($row = $traerAlumno->fetch(PDO::FETCH_ASSOC)){
					if($row['datoscompletos'] == 0){
						echo '
						<div class="header-top">
							<div class="container">
							<div class="row align-items-center">
								<div class="col-lg-6 col-sm-6 col-6 header-top-left">
									<ul>
										<li><a >['.$row[puntos].'] Puntos</a></li>
										<li><a href="#">Comprar Puntos</a></li>
									</ul>			
								</div>
							</div>			  					
							</div>
						</div>
						';
					}else{
						//si tiene los datos completos
						echo '
						<div class="header-top">
						<div class="container">
						  <div class="row align-items-center">
							  <div class="col-lg-6 col-sm-6 col-6 header-top-left">
								  <ul>
									  <li><a href="">['.$row['puntos'].'] Puntos</a></li>
									  <li><a href="comprarpuntos.php">Comprar Puntos</a></li>
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
							  <li class="activo"><a href="homealumno.php">Inicio</a></li>
							  <li><a href="actualizaralumno.php">Mi Cuenta</a></li>
							  <li><a href="materiasalumno.php">Materias</a></li>
							  <!-- 
							  <li><a href="hotels.html">Pupilos</a></li>
							  <li><a href="insurance.html">Cobrar Puntos</a></li>
							  -->
							  <li class="menu-has-children"><a href="">Profesores</a>
								<ul>
								  <li><a href="elegirtutor.php">Elegir Tutor</a></li>
								  <li><a href="mistutores.php">Mis Tutores</a></li>
								  <li><a href="historialtutores.php">Historial de tutores</a></li>
								</ul>
							  </li>				          					          		          
							  <li style="border: 1px dashed white; border-radius: 3px;"><a href="logout.php" onClick="logOut();">Salir</a></li>
							</ul>
						  </nav><!-- #nav-menu-container -->					      		  
						</div>
					</div>
						';

					}
					$correo = $row['email'];
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
								Hola				
							</h1>	
							<p class="text-white link-nav"><a href="index.html">Bienvenido, <?php echo $correo; ?></a></p>
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->	



			<!-- Start destinations Area -->
			<section class="destinations-area section-gap">
			<div class="container">
		            <div class="row d-flex justify-content-center">
		                <div class="menu-content pb-40 col-lg-8">
		                    <div class="title text-center">
		                        <h1 class="mb-10">Mensajes / Notificaciones</h1>
		                        <p>Cuando tengas algún mensaje o notificación aparecerán debajo...</p>
		                    </div>
		                </div>
					</div>
					<div class="row">

			<?php
			    foreach($traerMensajes as $valor){
				$mensaje = $valor['texto'];
				$separado = explode("|",$mensaje);
				$mensajeid = $valor['mensajes_id'];

				if($separado[0]==1){          
				echo '
				
				<div class="col-lg-4">
					<div class="single-destinations">
					<form action="homealumno.php" method="post">
						<div class="details">
							<h4 class="d-flex justify-content-between">
								<span>Hola, '.$correo.'</span>                              	
							</h4>
							<p style="color: white;">
							'.$separado[1].' te ha enviado el siguiente mensaje:
							</p>
							<div>
								<div class="sidebar-widgets">
									<div class="widget-wrap">
										<div class="single-sidebar-widget user-info-widget">
										<img style="height: 100px;" src="./imagentutor/'.$separado[8].'">
											<a href=""><h4 style="color: black;">'.$separado[4].', '.$separado[5].'</h4></a>
											
											<div class="star">
												<span class="fa fa-star checked"></span>
												<span class="fa fa-star checked"></span>
												<span class="fa fa-star checked"></span>
												<span class="fa fa-star checked"></span>
												<span class="fa fa-star"></span>				
											</div>	
											<br>
											<p>
											'.$separado[2].'
											</p>
										</div>
									</div>
								</div>		
							</div>
							<input type="text" name="idMensaje" value="'.$mensajeid.'" hidden="true">
							<div class="text-center">
								<input type="text" name="idperfil" value="'.$separado[3].'" hidden="true">
								<input class="price-btn col-lg-5" type="submit" value="Ir al perfil" name="perfil">
								<input class="price-btn col-lg-5" type="submit" value="Enterado" name="enterado">
							</div>
							
						</div>
						</form>
					</div>
				</div>	
				';
				

				}elseif($separado[0]==2){
					$traerTutor = $pdo->prepare('SELECT nombres,apellidos,email FROM tutores WHERE tutor_id=:tutor_id');
					$traerTutor->bindParam(':tutor_id',$separado[2]);
					$traerTutor->execute();
					$nombres = "";
					$apellidos = "";
					$email = "";
					foreach($traerTutor as $valor){
						$nombres = $valor['nombres'];
						$apellidos = $valor['apellidos'];
						$email = $valor['email'];
					}

					echo '
						<div class="col-lg-4">
						<div class="single-destinations">
							<form action="homealumno.php" method="post">
							<div class="alerta">
								<h4 class="d-flex justify-content-between">
									<span>Hola, '.$correo.'</span>                              	
								</h4>
								<p style="color: white;">
									'.$email.' ha dicho:  
								</p>
								<div>
									<div class="sidebar-widgets">
										<div class="widget-wrap">
											<div class="single-sidebar-widget user-info-widget">
												<img src="img/blog/user-info.png" alt="">
												<a href="#"><h4 style="color: black;">'.$nombres.', '.$apellidos.'</h4></a>

												<div class="star">
													<span class="fa fa-star checked"></span>
													<span class="fa fa-star checked"></span>
													<span class="fa fa-star checked"></span>
													<span class="fa fa-star checked"></span>
													<span class="fa fa-star"></span>				
												</div>	
												<br>
												<p>
												'.$separado[1].'
												</p>
											</div>
										</div>
									</div>		
								</div>
								<input type="text" name="idMensaje" value="'.$mensajeid.'" hidden="true">
								<div class="text-center">
								<input class="price-btn col-lg-5" type="submit" value="Enterado" name="enterado">
								</div>
							</div>
							</form>
						</div>
					</div>
					
					';
				}
				

			}


			foreach($traerNoCalificados as $valor){
				echo '
				<div class="col-lg-4">
				<div class="single-destinations">
					<form action="homealumno.php" method="post">
					<div class="tutoria">
						<h4 class="d-flex justify-content-between">
							<span>Hola '.$correo.'</span>                              		
						</h4>
						<p style="color: white;">
							Gracias por usar Tutoeri, llena el siguiente formulario:
						</p>
						<div>
							<div class="sidebar-widgets">
								<div class="widget-wrap">
									<div class="single-sidebar-widget user-info-widget">
									<img src="./imagentutor/'.$valor['imagen'].'" style="height: 100px;">
										<a href="#"><h4 style="color: black;">'.$valor['nombre'].' '.$valor['apellido'].'</h4></a>
										<p>[Fecha de la tutoría]</p>
										<form action="">
												<div class="ec-stars-wrapper">

														<input type="radio" name="punteo" value="1">&#9733;</input>
														<input type="radio" name="punteo" value="2">&#9733;</input>
														<input type="radio" name="punteo" value="3">&#9733;</input>
														<input type="radio" name="punteo" value="4">&#9733;</input>
														<input type="radio" name="punteo" value="5">&#9733;</input>

													</div>
											<div class="form-group">
											<input type="text" name="idtutor" value="'.$valor['id'].'" hidden="true">
												<label for="exampleFormControlTextarea1">Comentarios (Opcional): </label>
												<textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="comentario"></textarea>
											</div>
										</form>
									</div>
								</div>
							</div>		
						</div>
						<div class="text-center">
						<input class="price-btn col-lg-5" type="submit" value="Calificar" name="calificar">

						</div>
					</div>
					</form>
				</div>
			</div>
				
				
				';


			}
		?>
			<!---->						

					<!---->																															
					</div>
				</div>	
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
		</style>
	</html>