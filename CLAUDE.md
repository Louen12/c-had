C'had — contexte projet (nom provisoire)

Plateforme web + application mobile qui répond à « qui vend quoi, sur quel marché, et quand ? ». Trois côtés : clients, commerçants (non sédentaires, sur les marchés), gestionnaires de marché (plus tard). Territoire pilote : Finistère et Côtes-d'Armor.

Je suis seul développeur (profil full stack Symfony / Vue / TypeScript). Objectif du MVP : recueillir des retours et présenter la solution à des commerçants et à des mairies. Pas de paiement en ligne dans le MVP.

Principes produit (à respecter dans chaque écran)
Mobile first partout (clients, commerçants, et plus tard gestionnaires).
Ultra simple, rien de superflu : l'information utile (où, quand, qui) visible tout de suite.
Les marchés d'abord : le client entre par les marchés ; les commerçants sont mis en valeur à l'intérieur.
Aucun compte pour consulter. Le compte n'est demandé qu'au premier « Suivre » ou à la première mise de côté.
Commerçant : chaque action quotidienne en moins de 30 secondes (présence/absence, annonce, « Épuisé », valider une mise de côté).
Gabarit unique pour toutes les fiches : le commerçant remplit des champs, il ne met jamais en page.
Pas d'avis clients. Pas de classement payant.
Stack
Couche	Choix
Back	Symfony 7 + API Platform, PHP 8.3
Base	PostgreSQL + PostGIS (recherches « marchés à moins de X km »)
Auth	Une seule table user avec rôles, une seule connexion ; JWT (+ refresh token) pour le web et l'app
Front	Nuxt (Vue 3, TypeScript, Composition API), mobile first
Mobile	Le front Nuxt empaqueté avec Capacitor (iOS / Android) : push, géolocalisation, appareil photo
Carte	MapLibre GL ou Leaflet + tuiles OpenStreetMap
Back-office	EasyAdmin, réservé à l'administrateur
Dev	Docker Compose (php, postgres/postgis, nuxt)
Tests	PHPUnit (API), Vitest (front)

Monorepo : /api (Symfony), /app (Nuxt), docker-compose.yml à la racine. Code et identifiants en anglais, interface utilisateur en français.

Glossaire domaine (FR → code)
Métier	Entité
Utilisateur (client et/ou commerçant)	User
Étal = fiche publique d'un commerçant	Stall
Membre d'un étal (propriétaire, membre)	StallMember
Famille / spécialité (listes fermées)	Family, Specialty
Commune	Municipality (code INSEE)
Marché	Market (point PostGIS, couvert ou non)
Horaire d'un marché (jour, heures, période de saison)	MarketSchedule
Passage récurrent d'un étal sur un horaire	StallAttendance
Absence ponctuelle	Absence (date, marché optionnel)
Produit	Product (type de prix : fixe, au kg, à partir de, prix au marché ; « épuisé »)
Annonce	Announcement (type, texte, photo optionnelle, date de fin)
Suivi d'un étal ou d'un marché	Follow
Mise de côté	Reservation, ReservationItem

Règles clés :

La personne (User) et l'étal (Stall) sont séparés. Un étal peut exister sans compte (créé par l'admin), puis être revendiqué par le commerçant (claimed).
Présence du jour = passages prévus sur l'horaire du jour − absences déclarées. Le commerçant règle son planning une fois.
Prévoir sans l'implémenter : Organisation (gestionnaire) reliée à Market, Pitch (emplacement sur le plan).
Périmètre du MVP

Inclus

Client : Découvrir (géolocalisation, 3 prochains jours, 30 km par défaut), recherche unique groupée (marchés / commerçants / catégories), page marché (vue liste), fiche commerçant, « Suivre », onglet Suivis (« Ma semaine » + fil d'annonces), notifications.
Commerçant : revendication de fiche, fiche, planning récurrent, présence/absence en un clic, annonces, catalogue simple, bouton « Épuisé », QR code imprimable vers la fiche, couleur d'auvent (palette fermée).
Mise de côté : demande client → validation par le commerçant → retrait et paiement au marché. Quantité et prix indicatifs (« environ 500 g, environ 12 € »). Anti-abus : téléphone vérifié par SMS pour réserver (fournisseur SMS derrière une interface, faux fournisseur en dev), plafond de mises de côté en attente par client (plus bas pour un compte récent), indice de fiabilité visible du seul commerçant, bouton « Non retirée » → suspension automatique après plusieurs signalements, rappels la veille et le matin, annulation en un clic jusqu'à une heure limite.
Annonces : absences gratuites et illimitées ; autres annonces soumises à un quota configurable (valeur à définir, ne pas coder en dur).
Back-office : marchés, horaires, étals, modération, signalements « Erreur sur ce marché ».
Import des marchés : OpenStreetMap (Overpass, amenity=marketplace), DATAtourisme, data.gouv ; dédoublonnage ; vérification manuelle. Ne jamais copier les données de Jours-de-marché.fr.

Hors MVP (ne pas développer, mais ne pas bloquer l'architecture) : paiement en ligne (Stripe Connect plus tard), plan des emplacements, espace gestionnaire, boost d'annonces, fidélité, statistiques avancées, édition photo IA, plusieurs membres par étal côté UI, abonnements et prix (modèle économique non défini).

Navigation
Client, 4 onglets : Découvrir, Suivis, Commandes, Profil.
Espace commerçant : écran « Aujourd'hui » (je suis là / absent, nouvelle annonce, mises de côté à valider), puis Ma fiche, Mon planning, Mon catalogue, Mes annonces.
Back-office : admin seulement.
Identité visuelle (provisoire)
Une seule couleur de marque : orange brûlé 
#C14A16, réservée à C'had et aux marchés : bouton principal de l'écran, onglet actif, badges d'état, auvent des pages marché.
Texte 
#2B211C (brun très foncé), texte secondaire 
#6B5E55, fond 
#F7F2EA, crème 
#F6E3CF, bordures 
#DDD3C6 / 
#ECE4D9, surfaces 
#FFFFFF.
Typo : Bricolage Grotesque (titres, logo), DM Sans (texte).
Élément signature : l'auvent rayé à bord festonné (bandes couleur / crème). En haut des pages marché et commerçant ; au défilement il se replie en un bandeau fin (≈ 56 px) qui reste visible avec retour + nom.
Couleur d'auvent du commerçant choisie dans une palette fermée : Rouge tomate 
#B8322A, Framboise 
#A3305B, Prune 
#6E3B6E, Bleu océan 
#1F5F8B, Bleu ardoise 
#3D5A73, Vert sapin 
#2F6B4F, Vert olive 
#6B7A2A, Moutarde 
#B8861B, Châtaigne 
#7A4A2B, Charbon 
#3A3330. Elle colore l'auvent et l'avatar du commerçant ; les boutons restent orange partout.
Lisible en plein soleil : contraste AA minimum, cibles tactiles ≥ 44 px, vrais <button> / <a> / <label>.
Façon de travailler
Avant chaque phase : proposer un plan court, attendre ma validation.
Petits incréments testés, un commit par étape logique (messages en anglais, conventionnels).
Ne pas inventer de règle métier : si ce fichier ne tranche pas, poser la question.
Données de démo réalistes mais fictives (commerçants), marchés réels du Finistère importés.
Signaler toute dépendance payante ou tout service tiers avant de l'ajouter.