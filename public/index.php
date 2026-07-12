<?php




session_manager_start();

route('GET', '/login', 'connexion');
route('POST', '/login', 'connexion');
route('GET', '/logout', 'deconnexion');

route('GET', '/gerant/dashboard', 'gerantDashboard');
route('POST', '/gerant/dashboard', 'gerantDashboard');
route('GET', '/gerant/apprenants', 'gerantApprenants');
route('POST', '/gerant/apprenants', 'gerantApprenants');
route('GET', '/gerant/campagnes/create', 'gerantCampagnes');
route('POST', '/gerant/campagnes/create', 'gerantCampagnes');
route('GET', '/gerant/paiements/create', 'gerantPaiement');
route('POST', '/gerant/paiements/create', 'gerantPaiement');

route('GET', '/apprenant/dashboard', 'apprenantDashboard');

route('GET', '/', 'connexion');

router_dispatch();