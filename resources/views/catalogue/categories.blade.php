<head>
  ...
  <!-- Tailwind via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>


@extends('layouts.app')

@section('title', 'Catalogue des catégories')

@section('content')

<div class="text-center mx-auto mb-6">
    <h1 class="text-3xl font-bold">Catalogue des oeuvres</h1>
    <h5 class="text-gray-500">Explorez notre collection de créations uniques.</h5>
</div>

<!-- Barre filtres -->
<div class="flex flex-wrap gap-4 mb-6 justify-between items-center">

  <!-- Barre de recherche -->
  <input 
    type="text"
    placeholder="🔍 Rechercher une œuvre..."
    class="border border-gray-300 rounded-full px-4 py-2 w-full md:w-4/12 focus:outline-none focus:ring-2 focus:ring-blue-500"
  >

  <!-- Filtre Catégorie -->
  <select id="categorie" class="border border-gray-300 px-3 py-2 rounded-lg">
    <option value="">Toutes</option>
    <option value="peinture">Peinture</option>
    <option value="sculpture">Sculpture</option>
    <option value="bijoux">Bijoux</option>
  </select>

  <!-- Tri -->
  <div class="flex items-center gap-2">
    <span class="text-gray-600">Trier par :</span>
    <select id="tri" class="border border-gray-300 px-3 py-2 rounded-lg">
      <option value="">Par défaut</option>
      <option value="recentes">Plus récentes</option>
      <option value="croissant">Prix croissant</option>
      <option value="decroissant">Prix décroissant</option>
    </select>
  </div>

</div>

<!-- Grille catalogue -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
  <!-- Carte d'œuvre -->
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://www.lacaleauxepices.com/wp-content/uploads/mortier-gm.jpg" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Mortier en bois</h3>
      <p class="text-gray-600 mb-4">Catégorie : Sculpture</p>
      <p class="text-gray-800 font-bold mb-4">10000FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://tse1.mm.bing.net/th/id/OIP.A2tFd-E4KJxGFdPJxMkkuAHaHa?rs=1&pid=ImgDetMain&o=7&rm=3" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Collier</h3>
      <p class="text-gray-600 mb-4">Catégorie : Bijouterie</p>
      <p class="text-gray-800 font-bold mb-4">5430FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://tse4.mm.bing.net/th/id/OIP.Zvx3z02IuwPZ_pQNB2nZ8wHaHa?w=640&h=640&rs=1&pid=ImgDetMain&o=7&rm=3" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Tableau Peinture</h3>
      <p class="text-gray-600 mb-4">Catégorie : Peinture</p>
      <p class="text-gray-800 font-bold mb-4">14050FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://tse4.mm.bing.net/th/id/OIP.Xm1g6kRAovfGQMYpRykQmgHaHa?rs=1&pid=ImgDetMain&o=7&rm=3" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Bijou en or</h3>
      <p class="text-gray-600 mb-4">Catégorie : Bijouterie</p>
      <p class="text-gray-800 font-bold mb-4">102500FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://images.coinafrique.com/4762710_uploaded_image1_1716710530.jpg" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Tableau Peint</h3>
      <p class="text-gray-600 mb-4">Catégorie : Peinture</p>
      <p class="text-gray-800 font-bold mb-4">25000FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://images.coinafrique.com/3993740_uploaded_image1_1670187850.jpg" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Statuette Amazone</h3>
      <p class="text-gray-600 mb-4">Catégorie : Sculpture</p>
      <p class="text-gray-800 font-bold mb-4">15000FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://www.bazar-design.com/wp-content/uploads/2024/03/1710117085_pots-de-fleurs-design-pour-un-interieur-vegetalise-tendance.jpg" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">pot de fleurs</h3>
      <p class="text-gray-600 mb-4">Catégorie : Sculpture</p>
      <p class="text-gray-800 font-bold mb-4">10000FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://tse2.mm.bing.net/th/id/OIP.wL_prVyh_0WJIq6IZ9ucAAHaG2?rs=1&pid=ImgDetMain&o=7&rm=3" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Pagne tissé</h3>
      <p class="text-gray-600 mb-4">Catégorie : Textile</p>
      <p class="text-gray-800 font-bold mb-4">30000FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
   <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://th.bing.com/th/id/R.221f7b42584033fcb2989e2c0b96e815?rik=ubG%2fkLliVbkMNg&pid=ImgRaw&r=0" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Panier</h3>
      <p class="text-gray-600 mb-4">Catégorie : Sculpture</p>
      <p class="text-gray-800 font-bold mb-4">1000FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
   <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://tse2.mm.bing.net/th/id/OIP.KOzQbYajvlMqPWPRJ-byYAHaF4?w=680&h=540&rs=1&pid=ImgDetMain&o=7&rm=3" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Bijou artisanal</h3>
      <p class="text-gray-600 mb-4">Catégorie : Bijouterie</p>
      <p class="text-gray-800 font-bold mb-4">15700FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
   <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://tse1.explicit.bing.net/th/id/OIP.AgPM5f8Xe0zvFuLRp0o82gAAAA?w=400&h=400&rs=1&pid=ImgDetMain&o=7&rm=3" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Plat en bois</h3>
      <p class="text-gray-600 mb-4">Catégorie : Sculpture</p>
      <p class="text-gray-800 font-bold mb-4">2000FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>
   <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="https://tse4.mm.bing.net/th/id/OIP.6S56Kttr7dAR4BBFR0kghwHaJ4?rs=1&pid=ImgDetMain&o=7&rm=3" alt="Œuvre d'art" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="text-lg font-semibold mb-2">Plat en arfile</h3>
      <p class="text-gray-600 mb-4">Catégorie : Sculpture</p>
      <p class="text-gray-800 font-bold mb-4">1000FCFA</p>
      <a href="#" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
    </div>
  </div>

<div class="flex justify-center">
  <a href="{{ route('dashboard.acheteur') }}" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Mon espace</a>
</div>
  <!-- Répétez la carte pour chaque œuvre -->
  <!-- ... -->

</div>



@endsection