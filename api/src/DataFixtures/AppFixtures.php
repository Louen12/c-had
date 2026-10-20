<?php

namespace App\DataFixtures;

use App\Entity\Absence;
use App\Entity\Announcement;
use App\Entity\Family;
use App\Entity\Follow;
use App\Entity\Market;
use App\Entity\MarketSchedule;
use App\Entity\Municipality;
use App\Entity\Product;
use App\Entity\Specialty;
use App\Entity\Stall;
use App\Entity\StallAttendance;
use App\Entity\StallMember;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // --- Families & Specialties ---
        $families = $this->loadFamilies($manager);
        $specialties = $this->loadSpecialties($manager, $families);

        // --- Municipalities ---
        $municipalities = $this->loadMunicipalities($manager);

        // --- Markets (real Finistère markets) ---
        $markets = $this->loadMarkets($manager, $municipalities);

        // --- Schedules ---
        $schedules = $this->loadSchedules($manager, $markets);

        // --- Users ---
        $users = $this->loadUsers($manager);

        // --- Stalls ---
        $stalls = $this->loadStalls($manager, $families, $specialties, $users);

        // --- Attendances ---
        $this->loadAttendances($manager, $stalls, $schedules);

        // --- Products ---
        $this->loadProducts($manager, $stalls);

        // --- Announcements ---
        $this->loadAnnouncements($manager, $stalls);

        // --- Absences ---
        $this->loadAbsences($manager, $stalls, $markets);

        // --- Follows ---
        $this->loadFollows($manager, $users, $stalls, $markets);

        $manager->flush();
    }

    private function loadFamilies(ObjectManager $manager): array
    {
        $data = [
            ['Alimentation', 'alimentation', 'shopping-basket', 0],
            ['Boissons', 'boissons', 'wine', 1],
            ['Fleurs & Plantes', 'fleurs-plantes', 'flower', 2],
            ['Artisanat', 'artisanat', 'palette', 3],
            ['Textile & Accessoires', 'textile-accessoires', 'shirt', 4],
        ];

        $families = [];
        foreach ($data as [$name, $slug, $icon, $pos]) {
            $f = new Family();
            $f->setName($name)->setSlug($slug)->setIcon($icon)->setPosition($pos);
            $manager->persist($f);
            $families[$slug] = $f;
        }

        return $families;
    }

    private function loadSpecialties(ObjectManager $manager, array $families): array
    {
        $data = [
            ['alimentation', 'Boulangerie / Pâtisserie', 'boulangerie-patisserie', 0],
            ['alimentation', 'Fromagerie', 'fromagerie', 1],
            ['alimentation', 'Poissonnerie', 'poissonnerie', 2],
            ['alimentation', 'Fruits & Légumes', 'fruits-legumes', 3],
            ['alimentation', 'Charcuterie / Traiteur', 'charcuterie-traiteur', 4],
            ['alimentation', 'Volailles & Œufs', 'volailles-oeufs', 5],
            ['boissons', 'Cidre & Jus', 'cidre-jus', 0],
            ['boissons', 'Vin', 'vin', 1],
            ['fleurs-plantes', 'Fleuriste', 'fleuriste', 0],
            ['artisanat', 'Poterie / Céramique', 'poterie-ceramique', 0],
        ];

        $specialties = [];
        foreach ($data as [$familySlug, $name, $slug, $pos]) {
            $s = new Specialty();
            $s->setName($name)->setSlug($slug)->setPosition($pos);
            $s->setFamily($families[$familySlug]);
            $manager->persist($s);
            $specialties[$slug] = $s;
        }

        return $specialties;
    }

    private function loadMunicipalities(ObjectManager $manager): array
    {
        $data = [
            ['29232', 'Quimper', '29', ['29000']],
            ['29019', 'Brest', '29', ['29200']],
            ['29151', 'Morlaix', '29', ['29600']],
            ['29039', 'Concarneau', '29', ['29900']],
            ['29103', 'Landerneau', '29', ['29800']],
        ];

        $municipalities = [];
        foreach ($data as [$insee, $name, $dept, $postalCodes]) {
            $m = new Municipality();
            $m->setInseeCode($insee)->setName($name)->setDepartmentCode($dept);
            foreach ($postalCodes as $pc) {
                $m->addPostalCode($pc);
            }
            $manager->persist($m);
            $municipalities[$insee] = $m;
        }

        return $municipalities;
    }

    private function loadMarkets(ObjectManager $manager, array $municipalities): array
    {
        $data = [
            ['Marché des Halles Saint-François', 'marche-halles-saint-francois', '29232', 47.9942, -4.1018, 'Place Saint-François, Quimper', true],
            ['Marché de Brest – Saint-Louis', 'marche-brest-saint-louis', '29019', 48.3904, -4.4861, 'Rue de Lyon, Brest', false],
            ['Marché de Morlaix', 'marche-morlaix', '29151', 48.5772, -3.8271, 'Place Allende, Morlaix', false],
            ['Marché de Concarneau – Ville Close', 'marche-concarneau-ville-close', '29039', 47.8740, -3.9188, 'Place Jean Jaurès, Concarneau', false],
            ['Marché de Landerneau', 'marche-landerneau', '29103', 48.4527, -4.2484, 'Place du Général de Gaulle, Landerneau', false],
        ];

        $markets = [];
        foreach ($data as [$name, $slug, $insee, $lat, $lng, $addr, $covered]) {
            $m = new Market();
            $m->setName($name)
                ->setSlug($slug)
                ->setMunicipality($municipalities[$insee])
                ->setLatitude($lat)
                ->setLongitude($lng)
                ->setAddress($addr)
                ->setIsCovered($covered)
                ->setSource(Market::SOURCE_MANUAL)
                ->setVerified(true);
            $manager->persist($m);
            $markets[$slug] = $m;
        }

        return $markets;
    }

    private function loadSchedules(ObjectManager $manager, array $markets): array
    {
        $schedules = [];

        $add = function (string $marketSlug, int $day, string $start, string $end) use ($manager, $markets, &$schedules) {
            $s = new MarketSchedule();
            $s->setMarket($markets[$marketSlug]);
            $s->setDayOfWeek($day);
            $s->setStartTime(new \DateTime($start));
            $s->setEndTime(new \DateTime($end));
            $markets[$marketSlug]->addSchedule($s);
            $manager->persist($s);
            $key = $marketSlug.'-'.$day;
            $schedules[$key] = $s;
        };

        // Quimper: daily except Monday
        foreach ([2, 3, 4, 5, 6, 7] as $d) {
            $add('marche-halles-saint-francois', $d, '07:00', '13:30');
        }
        // Brest: daily
        foreach (range(1, 7) as $d) {
            $add('marche-brest-saint-louis', $d, '07:00', '13:00');
        }
        // Morlaix: Saturday
        $add('marche-morlaix', 6, '07:30', '13:00');
        // Concarneau: Mon, Fri
        $add('marche-concarneau-ville-close', 1, '07:00', '13:00');
        $add('marche-concarneau-ville-close', 5, '07:00', '13:00');
        // Landerneau: Tuesday, Friday
        $add('marche-landerneau', 2, '08:00', '13:00');
        $add('marche-landerneau', 5, '08:00', '13:00');

        return $schedules;
    }

    private function loadUsers(ObjectManager $manager): array
    {
        $users = [];

        // Admin
        $admin = new User();
        $admin->setEmail('admin@chad.local')
            ->setFirstName('Admin')
            ->setLastName('C\'had')
            ->setRoles(['ROLE_ADMIN'])
            ->setPhone('+33600000000')
            ->setPhoneVerified(true);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin'));
        $manager->persist($admin);
        $users['admin'] = $admin;

        // Client users
        $client1 = new User();
        $client1->setEmail('marie@example.com')
            ->setFirstName('Marie')
            ->setLastName('Le Bihan')
            ->setPhone('+33601010101')
            ->setPhoneVerified(true);
        $client1->setPassword($this->hasher->hashPassword($client1, 'password'));
        $manager->persist($client1);
        $users['client1'] = $client1;

        $client2 = new User();
        $client2->setEmail('yann@example.com')
            ->setFirstName('Yann')
            ->setLastName('Kervella')
            ->setPhone('+33602020202')
            ->setPhoneVerified(true);
        $client2->setPassword($this->hasher->hashPassword($client2, 'password'));
        $manager->persist($client2);
        $users['client2'] = $client2;

        // Merchant users
        $merchantNames = [
            'm1' => ['Jean-Pierre', 'Corre', 'jp.corre@example.com'],
            'm2' => ['Nolwenn', 'Tanguy', 'nolwenn.t@example.com'],
            'm3' => ['Hervé', 'Le Gall', 'herve.legall@example.com'],
            'm4' => ['Soizic', 'Morvan', 'soizic.m@example.com'],
            'm5' => ['Erwan', 'Quéré', 'erwan.q@example.com'],
            'm6' => ['Maëlle', 'Prigent', 'maelle.p@example.com'],
            'm7' => ['Ronan', 'Stéphan', 'ronan.s@example.com'],
            'm8' => ['Gwenaëlle', 'Abgrall', 'gwen.a@example.com'],
            'm9' => ['Loïc', 'Coat', 'loic.c@example.com'],
            'm10' => ['Annick', 'Calvez', 'annick.c@example.com'],
        ];

        foreach ($merchantNames as $key => [$first, $last, $email]) {
            $u = new User();
            $u->setEmail($email)
                ->setFirstName($first)
                ->setLastName($last)
                ->setRoles(['ROLE_MERCHANT'])
                ->setPhone('+336'.str_pad((string) rand(10000000, 99999999), 8, '0'))
                ->setPhoneVerified(true);
            $u->setPassword($this->hasher->hashPassword($u, 'password'));
            $manager->persist($u);
            $users[$key] = $u;
        }

        return $users;
    }

    private function loadStalls(ObjectManager $manager, array $families, array $specialties, array $users): array
    {
        $stallsData = [
            ['Boulangerie Corre', 'boulangerie-corre', '#B8322A', ['alimentation'], ['boulangerie-patisserie'], 'm1', 'Pain au levain, viennoiseries et pâtisseries bretonnes.'],
            ['Fromagerie Tanguy', 'fromagerie-tanguy', '#6B7A2A', ['alimentation'], ['fromagerie'], 'm2', 'Fromages fermiers de la région.'],
            ['Poissonnerie Le Gall', 'poissonnerie-le-gall', '#1F5F8B', ['alimentation'], ['poissonnerie'], 'm3', 'Pêche du jour, crustacés et coquillages.'],
            ['Les Légumes de Soizic', 'legumes-de-soizic', '#2F6B4F', ['alimentation'], ['fruits-legumes'], 'm4', 'Maraîchère bio à Plomelin.'],
            ['Charcuterie Quéré', 'charcuterie-quere', '#7A4A2B', ['alimentation'], ['charcuterie-traiteur'], 'm5', 'Charcuterie artisanale et plats cuisinés.'],
            ['Ferme de Prigent', 'ferme-de-prigent', '#B8861B', ['alimentation'], ['volailles-oeufs'], 'm6', 'Poulets fermiers, canards et œufs plein air.'],
            ['Cidrerie Stéphan', 'cidrerie-stephan', '#6E3B6E', ['boissons'], ['cidre-jus'], 'm7', 'Cidres et jus de pomme du Finistère.'],
            ['Fleurs d\'Armorique', 'fleurs-armorique', '#A3305B', ['fleurs-plantes'], ['fleuriste'], 'm8', 'Bouquets de saison et compositions florales.'],
            ['Poterie de la Rade', 'poterie-de-la-rade', '#3D5A73', ['artisanat'], ['poterie-ceramique'], 'm9', 'Céramiques utilitaires et décoratives, bols bretons.'],
            ['Crêperie Calvez', 'creperie-calvez', '#3A3330', ['alimentation'], ['charcuterie-traiteur'], 'm10', 'Galettes de sarrasin et crêpes sur le marché.'],
        ];

        $stalls = [];
        foreach ($stallsData as [$name, $slug, $color, $familySlugs, $specSlugs, $userKey, $desc]) {
            $s = new Stall();
            $s->setName($name)
                ->setSlug($slug)
                ->setAwningColor($color)
                ->setDescription($desc)
                ->setClaimed(true);

            foreach ($familySlugs as $fs) {
                $s->addFamily($families[$fs]);
            }
            foreach ($specSlugs as $ss) {
                $s->addSpecialty($specialties[$ss]);
            }

            $manager->persist($s);

            $member = new StallMember();
            $member->setStall($s)->setUser($users[$userKey])->setRole(StallMember::ROLE_OWNER);
            $manager->persist($member);

            $stalls[$slug] = $s;
        }

        return $stalls;
    }

    private function loadAttendances(ObjectManager $manager, array $stalls, array $schedules): void
    {
        $mapping = [
            'boulangerie-corre' => ['marche-halles-saint-francois-3', 'marche-halles-saint-francois-6', 'marche-morlaix-6'],
            'fromagerie-tanguy' => ['marche-halles-saint-francois-2', 'marche-halles-saint-francois-5', 'marche-brest-saint-louis-6'],
            'poissonnerie-le-gall' => ['marche-halles-saint-francois-2', 'marche-halles-saint-francois-4', 'marche-halles-saint-francois-6', 'marche-concarneau-ville-close-5'],
            'legumes-de-soizic' => ['marche-halles-saint-francois-3', 'marche-halles-saint-francois-6', 'marche-landerneau-2', 'marche-landerneau-5'],
            'charcuterie-quere' => ['marche-brest-saint-louis-3', 'marche-brest-saint-louis-6', 'marche-morlaix-6'],
            'ferme-de-prigent' => ['marche-halles-saint-francois-6', 'marche-concarneau-ville-close-1', 'marche-concarneau-ville-close-5'],
            'cidrerie-stephan' => ['marche-halles-saint-francois-6', 'marche-morlaix-6'],
            'fleurs-armorique' => ['marche-halles-saint-francois-3', 'marche-halles-saint-francois-6', 'marche-brest-saint-louis-6'],
            'poterie-de-la-rade' => ['marche-morlaix-6', 'marche-concarneau-ville-close-1'],
            'creperie-calvez' => ['marche-halles-saint-francois-6', 'marche-brest-saint-louis-6', 'marche-landerneau-5'],
        ];

        foreach ($mapping as $stallSlug => $scheduleKeys) {
            foreach ($scheduleKeys as $key) {
                if (!isset($schedules[$key])) {
                    continue;
                }
                $att = new StallAttendance();
                $att->setStall($stalls[$stallSlug]);
                $att->setMarketSchedule($schedules[$key]);
                $manager->persist($att);
            }
        }
    }

    private function loadProducts(ObjectManager $manager, array $stalls): void
    {
        $productsData = [
            'boulangerie-corre' => [
                ['Pain au levain', Product::PRICE_TYPE_FIXED, '4.50', null, 'Notre spécialité, fermentation longue.'],
                ['Kouign-amann', Product::PRICE_TYPE_FIXED, '3.80', null, null],
                ['Far breton', Product::PRICE_TYPE_FIXED, '3.50', null, null],
                ['Baguette tradition', Product::PRICE_TYPE_FIXED, '1.30', null, null],
            ],
            'fromagerie-tanguy' => [
                ['Tomme de Bretagne', Product::PRICE_TYPE_PER_KG, '18.00', 'kg', 'Affinée 3 mois minimum.'],
                ['Beurre de baratte', Product::PRICE_TYPE_FIXED, '4.50', null, 'Demi-sel, au sel de Guérande.'],
                ['Fromage blanc fermier', Product::PRICE_TYPE_FIXED, '3.20', null, null],
            ],
            'poissonnerie-le-gall' => [
                ['Bar de ligne', Product::PRICE_TYPE_PER_KG, '28.00', 'kg', null],
                ['Coquilles Saint-Jacques', Product::PRICE_TYPE_AT_MARKET, null, null, 'Selon arrivage, prix au marché.'],
                ['Langoustines', Product::PRICE_TYPE_FROM, '15.00', 'kg', null],
                ['Soupe de poisson maison', Product::PRICE_TYPE_FIXED, '6.50', null, null],
            ],
            'legumes-de-soizic' => [
                ['Panier de saison', Product::PRICE_TYPE_FROM, '8.00', null, 'Environ 3 kg de légumes de saison.'],
                ['Pommes de terre Désirée', Product::PRICE_TYPE_PER_KG, '2.50', 'kg', null],
                ['Salades', Product::PRICE_TYPE_FIXED, '1.50', null, null],
                ['Courge butternut', Product::PRICE_TYPE_PER_KG, '3.00', 'kg', null],
            ],
            'charcuterie-quere' => [
                ['Pâté Hénaff maison', Product::PRICE_TYPE_FIXED, '5.50', null, null],
                ['Andouille de Guéméné', Product::PRICE_TYPE_PER_KG, '22.00', 'kg', null],
                ['Kig ha farz (part)', Product::PRICE_TYPE_FIXED, '8.50', null, 'Le vendredi uniquement.'],
            ],
            'ferme-de-prigent' => [
                ['Poulet fermier', Product::PRICE_TYPE_PER_KG, '12.50', 'kg', 'Élevé en plein air à Plougastel.'],
                ['Œufs plein air (x6)', Product::PRICE_TYPE_FIXED, '3.00', null, null],
                ['Canard entier', Product::PRICE_TYPE_PER_KG, '14.00', 'kg', null],
            ],
            'cidrerie-stephan' => [
                ['Cidre brut', Product::PRICE_TYPE_FIXED, '4.50', null, 'Bouteille 75 cl.'],
                ['Cidre doux', Product::PRICE_TYPE_FIXED, '4.50', null, 'Bouteille 75 cl.'],
                ['Jus de pomme', Product::PRICE_TYPE_FIXED, '3.80', null, 'Pur jus, bouteille 1 L.'],
            ],
            'fleurs-armorique' => [
                ['Bouquet de saison', Product::PRICE_TYPE_FROM, '8.00', null, null],
                ['Composition florale', Product::PRICE_TYPE_FROM, '25.00', null, null],
            ],
            'poterie-de-la-rade' => [
                ['Bol breton personnalisé', Product::PRICE_TYPE_FIXED, '12.00', null, null],
                ['Plat à gratin', Product::PRICE_TYPE_FIXED, '28.00', null, null],
            ],
            'creperie-calvez' => [
                ['Galette complète', Product::PRICE_TYPE_FIXED, '4.50', null, 'Œuf, jambon, fromage.'],
                ['Crêpe beurre-sucre', Product::PRICE_TYPE_FIXED, '2.50', null, null],
                ['Crêpe caramel au beurre salé', Product::PRICE_TYPE_FIXED, '3.50', null, null],
            ],
        ];

        $pos = 0;
        foreach ($productsData as $stallSlug => $products) {
            $pos = 0;
            foreach ($products as [$name, $priceType, $price, $unit, $desc]) {
                $p = new Product();
                $p->setStall($stalls[$stallSlug])
                    ->setName($name)
                    ->setPriceType($priceType)
                    ->setPrice($price)
                    ->setUnit($unit)
                    ->setDescription($desc)
                    ->setPosition($pos++);
                $manager->persist($p);
            }
        }
    }

    private function loadAnnouncements(ObjectManager $manager, array $stalls): void
    {
        $a1 = new Announcement();
        $a1->setStall($stalls['boulangerie-corre'])
            ->setType(Announcement::TYPE_NEW_PRODUCT)
            ->setContent('Nouveau : pain aux algues de Roscoff, disponible chaque samedi !')
            ->setEndsAt(new \DateTimeImmutable('+14 days'));
        $manager->persist($a1);

        $a2 = new Announcement();
        $a2->setStall($stalls['legumes-de-soizic'])
            ->setType(Announcement::TYPE_PROMOTION)
            ->setContent('Les courges butternut à 2 €/kg cette semaine !')
            ->setEndsAt(new \DateTimeImmutable('+7 days'));
        $manager->persist($a2);

        $a3 = new Announcement();
        $a3->setStall($stalls['cidrerie-stephan'])
            ->setType(Announcement::TYPE_INFO)
            ->setContent('Dégustation gratuite de notre nouveau cidre rosé samedi matin.');
        $manager->persist($a3);
    }

    private function loadAbsences(ObjectManager $manager, array $stalls, array $markets): void
    {
        $abs = new Absence();
        $abs->setStall($stalls['poissonnerie-le-gall'])
            ->setDate(new \DateTimeImmutable('next Wednesday'))
            ->setReason('Congés');
        $manager->persist($abs);
    }

    private function loadFollows(ObjectManager $manager, array $users, array $stalls, array $markets): void
    {
        $f1 = new Follow();
        $f1->setUser($users['client1'])->setStall($stalls['boulangerie-corre']);
        $manager->persist($f1);

        $f2 = new Follow();
        $f2->setUser($users['client1'])->setMarket($markets['marche-halles-saint-francois']);
        $manager->persist($f2);

        $f3 = new Follow();
        $f3->setUser($users['client2'])->setStall($stalls['poissonnerie-le-gall']);
        $manager->persist($f3);

        $f4 = new Follow();
        $f4->setUser($users['client2'])->setStall($stalls['legumes-de-soizic']);
        $manager->persist($f4);
    }
}
