<?php
    require_once 'conection.php';
    session_start();

    $traerTutor = $pdo->prepare('SELECT *  FROM tutores WHERE tutor_id=:tutor_id');
    $traerTutor->bindParam(':tutor_id', $_SESSION['user_id']);
	$traerTutor->execute();
	
	

    //prueba
    $traerMateriasDisponibles = $pdo->prepare('SELECT materias.materia_id as id, materias.nombre as nombre, materias.descripcion as descripcion, materias.imagenmateria as imagen 
    FROM materias WHERE materias.materia_id NOT IN(SELECT materiatutor.materia_id 
    FROM materiatutor WHERE materias.materia_id =materiatutor.materia_id AND materiatutor.activo=1 AND materiatutor.tutor_id = :tutor_id)');
    $traerMateriasDisponibles->bindParam(':tutor_id',$_SESSION['user_id']);
    $traerMateriasDisponibles->execute();

    $traerMateriasAsignadas = $pdo->prepare('SELECT materias.materia_id as id, materias.nombre as nombre, materias.descripcion as descripcion, materias.imagenmateria as imagen 
    FROM materias WHERE materias.materia_id IN(SELECT materiatutor.materia_id 
    FROM materiatutor WHERE materias.materia_id =materiatutor.materia_id AND materiatutor.activo=1 AND materiatutor.tutor_id = :tutor_id)');
    $traerMateriasAsignadas->bindParam(':tutor_id',$_SESSION['user_id']);
    $traerMateriasAsignadas->execute();
    /////


    if(isset($_POST['escogerMateria'])){
        $contador = count($_POST['materia']);
        if($contador>0){
			$base = "";
			$baseActualizar = ""; 
			$vuelta = 0;
			
			$traerMateriasDesactivadas = $pdo->query('SELECT materiatutor.materia_id
			FROM materiatutor WHERE materiatutor.activo = 0 AND materiatutor.tutor_id='.$_SESSION['user_id'].'');

			$arrayValores = array();
			foreach($traerMateriasDesactivadas as $valor){
				array_push($arrayValores,$valor['materia_id']);
			}

			$hacerInsert = 0;
            foreach($_POST['materia'] as $valor){
                $vuelta +=1; 
                if($contador>1){
					if(in_array($valor,$arrayValores)){
						
						if($contador==$vuelta){
							$baseActualizar .= "materia_id=".$valor;
							$actualizacionMasiva = $pdo->prepare('UPDATE materiatutor SET activo=1 WHERE tutor_id=:tutor_id  AND '.$baseActualizar.' ');
				$actualizacionMasiva->bindParam(':tutor_id', $_SESSION['user_id']);
				$actualizacionMasiva->execute(); 
						}else{
							$baseActualizar .= "materia_id=".$valor." OR ";
						}

					}else{
						if($contador==$vuelta){
							$base .= "(".$valor.",".$_SESSION['user_id'].")";
							$escogerMateria = $pdo->query('INSERT INTO materiatutor (materia_id, tutor_id) VALUES '.$base.'');
						}else{
							$base .= "(".$valor.",".$_SESSION['user_id']."),";
						}
					}
                    
                }else{
					if(in_array($valor,$arrayValores)){
						$activarMateria = $pdo->query('UPDATE materiatutor SET activo=1 WHERE materia_id='.$valor.' AND tutor_id='.$_SESSION['user_id'].'');
					}else{
						$base = "(".$valor.",".$_SESSION['user_id'].")";
					}
                    
                }
			}

            header('Location: materiastutor.php');
        }else{
			
            echo 'elige una o mas materias primero';
        }
    }

    if(isset($_POST['desligar'])){
		$actualizarMateriaTutor = $pdo->prepare('UPDATE materiatutor SET activo=0 WHERE materia_id=:materia_id AND tutor_id=:tutor_id');
		$actualizarMateriaTutor->bindParam(':materia_id', $_POST['materia_id']);
		$actualizarMateriaTutor->bindParam(':tutor_id', $_SESSION['user_id']);
		if($actualizarMateriaTutor->execute()){
			header('Location: materiastutor.php');
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
				while($row = $traerTutor->fetch(PDO::FETCH_ASSOC)){
					if($row['datoscompletos'] == 0){
						echo '
						<div class="header-top">
							<div class="container">
							<div class="row align-items-center">
								<div class="col-lg-6 col-sm-6 col-6 header-top-left">
									<ul>
                                        <li><a >['.$row[puntos].'] Puntos</a></li>
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
							  <li class="activo"><a href="materiastutor.php">Materias</a></li>
							  
							  <li><a href="pupilos.php">Pupilos</a></li>
							  <li><a href="cobropuntos.php">Cobrar Puntos</a></li>
							  			          					          		          
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
								Materias				
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
							<div class="">
									<div class="row text-center">
										<div class="col-md-6 typo-sec">
											<h2 class="mb-20">Materias Disponibles</h2>
											<form action="materiastutor.php" method="post">
											<div class="text-center" style="margin-bottom: 19px;">
													<input type="submit" value="Asignar" class="primary-btn col-lg-5" class="btn btn-primary" name="escogerMateria">
											</div>
												
														<?php
														$contadorMateria = 0;
														while($row = $traerMateriasDisponibles->fetch(PDO::FETCH_ASSOC)){
															$contadorMateria +=1;
															echo '
															<div class="progress-table-wrap">
																<div class="progress-table">
																	<div class="table-head">
																		<div class="country">Materias</div>
																		<div class="visit">Seleccionar</div>
																	</div>
																	<div class="table-row">
																		<div class="country"> <img class="imgPerfil" src="./imagenmateria/'.$row['imagen'].'">'.$row['nombre'].'</div>
																		<div class="visit primary-checkbox">
																			<input type="checkbox" name="materia[]" value="'.$row['id'].'" id="default-checkbox'.$contadorMateria.'">
																			<label for="default-checkbox'.$contadorMateria.'"></label>
																			</div>
																	</div>
																</div>
															</div>

															';
														}
														?>
														
												</div>
											</form>




										<div class="col-md-6 mt-sm-30 typo-sec">
											<h2 class="mb-20">Materias Asignadas</h2>

											<div class="">
													<div class="progress-table-wrap">
															<div class="progress-table">
																<div class="table-head">
																	<div class="country">Materia(s)</div>
																	
																</div>
																<?php
																	while($row = $traerMateriasAsignadas->fetch(PDO::FETCH_ASSOC)){
																		echo '
																		<form action="materiastutor.php" method="post">
																			<div class="table-row">
																				<div class="country"> <img class="imgPerfil" alt="Card image cap" src="./imagenmateria/'.$row['imagen'].'">'.$row['nombre'].'</div>
																				<div>
																					<input type="text" name="materia_id" value="'.$row['id'].'" hidden="true">
																					<input type="submit" class="primary-btn" value="desligar" class="btn btn-secondary" name="desligar">
																				</div>
																			</div>
																		</form>';
																	}
																	?>
															</div>
														</div>	
											</div>
										</div>
										
									</div>
								</div>
		            </div>
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