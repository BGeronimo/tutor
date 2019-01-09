<?php   
    require_once 'conection.php';
    session_start();

    $traerMaterias = $pdo->query('SELECT * FROM materias');


    if(isset($_POST['actualizar'])){
        $_SESSION['materiaId'] = $_POST['id'];
        header('location: actualizarmateria.php?id='.$_POST['id']);
	}
	
	if(isset($_POST['subirImagen'])){
        
        $tipoImagen = $_FILES['imagen']['type'];
        $nombreImagen = str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789".uniqid());
        $sizeImagen = $_FILES['imagen']['size'];

        if($sizeImagen<3000000){
            if($tipoImagen == "image/jpeg" || $tipoImagen == "image/jpg" || $tipoImagen == "image/png"){
                $lugarGuardado = 'imagenmateria/';
                switch($tipoImagen){
                    case "image/jpeg":
                        move_uploaded_file($_FILES['imagen']['tmp_name'],$lugarGuardado.$nombreImagen.'.jpeg');
                        $nombreArreglado = $nombreImagen.'.jpeg';
                    break;
                    case "image/jpg":
                        move_uploaded_file($_FILES['imagen']['tmp_name'],$lugarGuardado.$nombreImagen.'.jpg');
                        $nombreArreglado = $nombreImagen.'.jpg';
                    break;
                    case "image/png":
                        move_uploaded_file($_FILES['imagen']['tmp_name'],$lugarGuardado.$nombreImagen.'.png');
                        $nombreArreglado = $nombreImagen.'.png';
                    break;
				}

				$traerFotoActual = $pdo->query('SELECT imagenmateria FROM materias WHERE materia_id='.$_POST['idMateria'].'');
				$imagenAntigua = "";
				foreach($traerFotoActual as $valor){
					$imagenAntigua = $valor['imagenperfil'];
				}
                
                $actualizarFoto = $pdo->prepare('UPDATE materias SET imagenmateria=:imagenmateria WHERE materia_id=:materia_id');
                $actualizarFoto->bindParam(':imagenmateria', $nombreArreglado);
                $actualizarFoto->bindParam(':materia_id', $_POST['idMateria']);
                if($actualizarFoto->execute()){
					unlink('./imagentutor/'.$imagenAntigua);
                    header('Location: materias.php');
                }
                
               
            }else{
                echo 'solo se permite subir archivos de tipo .jpeg, .jpg y .png';
            }
        }else{
            echo 'la imagen es demasiado grande';
        }
	}
	
	if(isset($_POST['datos'])){
		if(isset($_POST['nombreMateria']) || isset($_POST['descripcion'])){
			$actualizarDatos = $pdo->prepare('UPDATE materias SET nombre=:nombre, descripcion=:descripcion WHERE materia_id=:materia_id');
			$actualizarDatos->bindParam(':nombre', $_POST['nombreMateria'] );
			$actualizarDatos->bindParam(':descripcion', $_POST['descripcion']);
			$actualizarDatos->bindParam(':materia_id', $_POST['idMateria']);
			if($actualizarDatos->execute()){
				header('Location: materias.php');
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
		<title>Materias | Tutoeri</title>
		<!-- Autenticacion con firebase -->
		<script type="text/javascript" src="auth.js"></script>

		<script type="text/javascript" src="jquery-1.10.2.min.js"></script>

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
				
				<div class="container main-menu">
					<div class="row align-items-center justify-content-between d-flex">
				      <div id="logo">
				        <a href="index.html"><img src="img/logo.png" alt="" title="" /></a>
				      </div>
				      <nav id="nav-menu-container">
				        <ul class="nav-menu">
				          <li ><a href="homeadministrador.php">Inicio</a></li>
				          <li class="activo"><a href="materias.php">Materias</a></li>
				          <li><a href="pagartutor.php">Pagar a Tutor</a></li>	          					          		          
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
						<div class="about-content col-lg-12" style="margin-top: 0px; padding: 36px 0px">
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
								<h1 class="mb-10">Materias</h1>
								<p>Estas son las materias disponibles</p>
		                    </div>
		                </div>
					</div>
					<div class="row d-flex justify-content-center">
						
						<?php
						$contador = 0;
						foreach($traerMaterias as $valor){
							$contador +=1;

							echo '
								<div class="card text-center" style="width: 18rem; margin: 3px 3px 3px 3px;">
									<div class="card-body">
										<h5 class="card-title">'. $valor['nombre'] .'</h5>
										<p class="card-text">'. $valor['descripcion'] .'</p>
										<input type="text" value="'.$valor['materia_id'].'" hidden="true">
										<button class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg'.$contador.'" onClick="boton();" id="'.$valor['materia_id'].'">Actualizar</button>
									</div>
								</div>




								<!-- Modal -->
								<div class="modal fade bd-example-modal-lg'.$contador.'" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
								  <div class="modal-dialog modal-lg" role="document">
									<div class="modal-content">
									  <div class="modal-header">
										<h5 class="modal-title" id="exampleModalLabel">Actualizar [Materia]</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										  <span aria-hidden="true">&times;</span>
										</button>
									  </div>
									  <div class="modal-body">
										<div class="row">
													<div class="col-lg-6">
														<div class="single-destinations">
															<!--<div class="thumb">
																<img src="img/hotels/d1.jpg" alt="">
															</div>-->
															<div class="perfil text-center">
																<h4 class=" text-center ">
																	<span class="text-center">Foto de la materia</span>       
																</h4>
																<p style="color: black;">
																	Elige una imagen para la materia.
																</p>
																<div>
																	<div class="sidebar-widgets" style="padding-bottom: 2px;">
																		<div class="widget-wrap">
																			<div class="single-sidebar-widget user-info-widget">
																				<img class="imgPerfil" src="./imagenmateria/'.$valor['imagenmateria'].'" alt="">
																				<br>
																				<form action="materias.php" method="post" enctype="multipart/form-data">
																					<div class="form-group text-center">
																						<label for="exampleFormControlFile1"></label>
																						<input type="file" class="" name="imagen" id="exampleFormControlFile1" style="margin-top: 35px; border: 2px dashed #3b9f97; border-radius: 3px;">
																						<input type="text" name="idMateria" value="'.$valor['materia_id'].'" hidden="true">
																					</div>
																					<div class="text-center">
																						<input type="submit" value="Guardar Imagen" name="subirImagen" class="btn btn-primary">
																					</div>
																				</form>
																			</div>
																		</div>
																	</div>		
																</div>
																
															</div>
														</div>
													</div>
													<div class="col-lg-6">
														<div class="single-destinations">
															<!--<div class="thumb">
																<img src="img/hotels/d2.jpg" alt="">
															</div>-->
															<div class="perfil text-center">
																<h4 class="text-center">
																	<span class="text-center">información</span>      
																</h4>
																<p style="color: black;">
																	Actualiza la información de la materia.
																</p>
																<div>
																	<div class="sidebar-widgets" style="padding-bottom: 10px;">
																		<div class="widget-wrap" style="padding: 0px 0px;">
																			<div class="single-sidebar-widget user-info-widget">
																				<form action="materias.php" method="post">
																					<div class="mt-10">
																						<input type="text" name="nombreMateria" placeholder="Nombre de la materia" value="'.$valor['nombre'].'"  required="" class="single-input">
																					</div>
																					<div class="mt-10">
																						<input type="text" name="descripcion" placeholder="Descripción" value="'.$valor['descripcion'].'" required="" class="single-input">
																					</div>
																					<div class="text-center">
																						<br>
																						<input type="submit" value="Actualizar Información" class="btn btn-primary" name="datos">
																					</div>
																					<input type="text" name="idMateria" value="'.$valor['materia_id'].'" hidden="true">
																				</form>
																			</div>
																		</div>
																	</div>		
																</div>
																
															</div>
														</div>
													</div>
													
																																											
												</div>
									  </div>
									</div>
								  </div>
								</div>

							';

						}
						?>
					</div>
				</div>
			</section>
			<!-- End destinations Area -->

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


<script>
	function boton(){
	var id = document.getElementById("id").value;
	localStorage.setItem("id", id);
	}
</script>