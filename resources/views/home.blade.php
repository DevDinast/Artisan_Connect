<!DOCTYPE html> 
<html lang="fr">
     <head> 
        <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>ArtisanConnect - Accueil</title>
           <!-- Bootstrap CSS --> 
           <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Gravitas+One&display=swap" rel="stylesheet">
            <style>
           .hero {
            background-image: url("{{ asset('images/fond.avif') }}"); 
            background-size:100%; 
            background-repeat: no-repeat; 
            background-position: top center;
            height: 800px;

            } 
            h1 {
            font-family: 'Gravitas One', cursive;
             font-size: 4rem; 
        }
           </style> 
           </head> 
           <body> <!-- Section Hero avec image de fond --> 
            <header class="hero d-flex flex-column justify-content-center align-items-center text-white text-center"> 
                <nav class="navbar navbar-expand-sm navbar-dark bg-dark w-100 fixed-top"> 
                    <div class="container-fluid"> 
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
                             <span class="navbar-toggler-icon"></span> 
                            </button> <div class="collapse navbar-collapse" id="mynavbar">
                                 <ul class="navbar-nav me-auto">
                                     <li class="nav-item">
                                        <a class="nav-link" href="#">Accueil</a></li> 
                                        <li class="nav-item"><a class="nav-link" href="#">Inscription</a></li> 
                                        <li class="nav-item"><a class="nav-link" href="#">Profil artisan</a></li> 
                                    </ul> <form class="d-flex"> <input class="form-control me-2" type="text" placeholder="Rechercher par métier ou localisation"> 
                                        <button class="btn btn-primary" type="button">Rechercher</button>
                                     </form> 
                                    </div> 
                                </div> 
                            </nav> 
                            <!-- Texte centré sur l'image --> 
                            <div class="mt-5"> 
                                <h1 >Bienvenue sur ArtisanConnect</h1>
                                 <h3 class="display-5">Le lien direct entre artisans qualifiés et clients près de chez vous.</h3> 
                                 <h5 class="display-5">Trouvez rapidement des artisans de confiance ou proposez vos services en toute simplicité.</h5> 
                                </div> 
                            
                        </header>
 
                </div>
                
                
                <br>
                <br>
   
                
       <div class="container mt-5">
        <div class="row">
        <div class="col-sm-4">
            <h3> Artisan Batiment</h3>
             <img src="images/image.jpg" 
             class="img-thumbnail" 
             alt="Artisan de batiment" 
             width="250" 
             height="190"
            ;>
        </div>
        <div class="col-sm-4">
        <h3> Artisan textile</h3>    

            <img src="images/image1.jpg" class="img-thumbnail" alt="Artisan textile" width="250" height="190">
        </div>
        <div class="col-sm-4">
            <h3> Artisan Alimentaire</h3>
            <img src="images/image5.jpg" class="img-thumbnail" alt="Artisan alimentaire" width="250" height="190">
        </div>
        <br>
        <br>
        <br>
        <div class="row mt-4">
        <div class="col-sm-4">
            <h3> Artisan Numérique</h3>
         <img src="images/image2.jpg" class="img-thumbnail" alt="Artisan numérique" width="250" height="190">
        </div>
        <div class="col-sm-4">
            <h3> Artisan de services</h3>
            <img src="images/image4.jpg" class="img-thumbnail" alt="Artisan de services" width="200" height="190">
        </div>
        <div class="col-sm-4">
            <h3> Artisan Divers</h3>
            <img src="images/image3.jpg" class="img-thumbnail" alt="Artisan divers" width="200" height="190">
    </div>
<div class="text-center mt-4">
  <a href="{{ route('login') }}" class="btn btn-outline-primary">Créer un profil artisan</a>
 </div>   
 <div class="text-center mt-4">
   <button type="button" class="btn btn-outline-primary">Se connecter</button>
   </div>             
    </div>
    </div>
    <br> 
    <br>
   <footer class="bg-dark text-white pt-4 pb-4">
  <div class="container">

    <div class="row">
      
      <!-- Identité -->
      <div class="col-md-4">
        <h5>ArtisanConnect</h5>
        <p>Relier artisans et clients pour des services de qualité.</p>
        <p>&copy; 2026 ArtisanConnect - Tous droits réservés</p>
      </div>
      
      <!-- Liens utiles -->
      <div class="col-md-4">
        <h5>Liens utiles</h5>
        <ul class="list-unstyled">
          <li><a href="/about" class="text-white">À propos</a></li>
          <li><a href="/contact" class="text-white">Contact</a></li>
          <li><a href="/faq" class="text-white">FAQ</a></li>
          <li><a href="/mentions-legales" class="text-white">Mentions légales</a></li>
        </ul>
      </div>
      
      <!-- Contact & réseaux -->
      <div class="col-md-4">
        <h5>Contact</h5>
        <p>Email : contact@artisanconnect.com</p>
        <p>Tél : +229 01 62 67 46 71</p>
        <div>
          <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
          <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
          <a href="#" class="text-white"><i class="bi bi-linkedin"></i></a>
        </div>
      </div>
      
    </div>
  </div>
</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

