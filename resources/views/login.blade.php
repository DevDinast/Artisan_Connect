<!DOCTYPE html> 
<html lang="fr">
     <head> 
        <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>ArtisanConnect - Inscription</title>
           <!-- Bootstrap CSS --> 
           <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
           
  </header>
     </head>
     <body>
      <h2 class="text-center mt-4">Créer votre profil artisan</h2>
     <div class="container mt-5">
     <form action="/action_page.php">
  <div class="mb-3 mt-3">
    <label for="Name" class="form-label">Nom:</label>
    <input type="text" class="form-control" id="Name" placeholder="Enter votre nom" name="Name">
  </div>
 
   <div class="mb-3">
    <label for="firstName" class="form-label">Prénom:</label>
    <input type="text" class="form-control" id="firstName" placeholder="Entrer votre prénom" name="firstName">
  </div>
   <div class="mb-3">
    <label for="pwd" class="form-label">Mot de passe:</label>
    <input type="password" class="form-control" id="pwd" placeholder="Entrer votre mot de passe" name="pswd">
  </div>
   <div class="mb-3">
    <label for="cmpt" class="form-label">Compétence/Métier:</label>
    <input type="text" class="form-control" id="cmpt" placeholder="Entrer votre compétence ou métier" name="cmpt">
  </div>
  <div class="mb-3">
    <label for="sex" class="form-label">Sexe:</label> <br>
    <input type="radio" class="form-check-input" id="M" name="optradio" value="M"> Masculin
     <input type="radio" class="form-check-input" id="F" name="optradio" value="F"> Féminin
  </div>
   <div class="mb-3">
   <label for="location" class="form-label">Localisation:</label>
<input class="form-control" list="locations" name="location" id="location">
<datalist id="locations">
  <option value="Cotonou">
  <option value="Porto-Novo">
  <option value="Parakou">
  <option value="Abomey-Calavi">
  <option value="Djougou">
  <option value="Bohicon">
  <option value="Lokossa">      
  <option value="Ouidah">
</datalist>

  </div>
  <label for="comment">Bio:</label>
<textarea class="form-control" rows="5" id="comment" placeholder="Faites une description de vos compétences ou métiers" name="text"></textarea>
<div class="form-check mb-3">
   <div class="mb-3">
    <label for="Lwhat" class="form-label">Lien whatsapp:</label>
    <input type="text" class="form-control" id="Lwhat" placeholder="Entrer votre lien whatsapp" name="Lwhat">
  </div>
    <img class="card-img-top" src="image5.jpg" alt="Card image"><br>
  <form method="POST" enctype="multipart/form-data">
  <input type="file" name="photo_artisan" accept="image/*">  <br><br>
  <button type="submit">Enregistrer</button><br>
  
</form>
  
  
    <label class="form-check-label"><br>
    
      <input class="form-check-input" type="checkbox" name="remember"> Remember me
    </label>
  </div>
  <button type="submit" class="btn btn-primary">Soumettre</button>
</form>
<div>
 <footer class="bg-dark text-white text-center p-3 mt-5">
        &copy; 2026 ArtisanConnect. Tous droits réservés.
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </div>
     