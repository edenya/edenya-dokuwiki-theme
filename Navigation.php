<?php

namespace dokuwiki\template\edenya;

use dokuwiki\Cache\Cache;
use dokuwiki\File\PageResolver;
use dokuwiki\Utf8\PhpString;

/**
 * La navigation du thème : le sommaire du wiki, le fil d'Ariane (« Vous êtes ici ») et les pages
 * consultées.
 *
 * Le sommaire n'est écrit qu'à un endroit, la page d'accueil du wiki ($conf['start']) : chacun de
 * ses titres de niveau 2 (===== Jouer =====) ouvre une rubrique, et les liens internes qui le
 * suivent en sont les pages, dans leur ordre et sous le libellé du lien. Une page citée deux fois
 * reste dans la première rubrique qui la cite ; un lien vers une section d'une page déjà citée
 * (combats#les_tactiques_de_combats) n'ajoute rien. Le staff change le menu en changeant l'accueil.
 */
class Navigation
{
    /** Les actions qui portent sur tout le wiki : le fil d'Ariane ne cite pas la page d'où l'on vient. */
    public const ACTIONS_DU_WIKI = ['recent', 'index', 'search', 'login', 'register', 'resendpwd', 'profile', 'admin', 'media'];

    /** Les actions qui portent sur la page affichée, et la clé de leur libellé dans $lang. */
    public const ACTIONS_DE_LA_PAGE = [
        'edit' => 'btn_edit',
        'preview' => 'btn_preview',
        'draft' => 'btn_draft',
        'source' => 'btn_source',
        'revisions' => 'btn_revs',
        'diff' => 'diff',
        'backlink' => 'btn_backlink',
        'subscribe' => 'btn_subscribe',
    ];

    /** Les actions qui ont besoin de toute la largeur : ni sommaire ni pages consultées à côté. */
    public const ACTIONS_PLEINE_LARGEUR = ['edit', 'preview', 'draft', 'conflict', 'media', 'admin'];

    /** Les icônes, des Material Design Icons (Apache 2.0) comme celles des menus de DokuWiki. */
    protected const ICONE_ACCUEIL = 'M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z';
    protected const ICONE_HISTORIQUE = 'M13.5,8H12V13L16.28,15.54L17,14.33L13.5,12.25V8M13,3A9,9 0 0,0 4,12H1L4.96,16.03L9,12H6A7,7 0 0,1 13,5A7,7 0 0,1 20,12A7,7 0 0,1 13,19C11.07,19 9.32,18.21 8.06,16.94L6.64,18.36C8.27,20 10.5,21 13,21A9,9 0 0,0 22,12A9,9 0 0,0 13,3';

    /** @var array{rubriques: array<int, array{titre: string, ancre: string, pages: array<string, string>}>, pages: array<string, array{rubrique: int, libelle: string}>}|null */
    protected static $sommaire = null;

    /**
     * Les rubriques du sommaire, avec leurs pages que le visiteur peut lire (id => libellé) ; une
     * rubrique qui n'en a aucune n'y est pas. L'ancre est celle du titre dans la page d'accueil.
     *
     * @return array<int, array{titre: string, ancre: string, pages: array<string, string>}>
     */
    public static function getRubriques(): array
    {
        return self::lire()['rubriques'];
    }

    /** La rubrique du sommaire qui cite la page, null si aucune ne la cite. */
    public static function getRubrique(string $id): ?int
    {
        return self::lire()['pages'][$id]['rubrique'] ?? null;
    }

    /**
     * Le nom d'une page : son libellé dans le sommaire, sinon son premier titre, sinon son nom ;
     * « Accueil » pour la page d'accueil. Une page que le visiteur ne peut pas lire (l'espace du
     * staff) ne donne que son nom : son titre en dirait déjà trop.
     */
    public static function getLibelle(string $id): string
    {
        global $conf;
        if ($id === $conf['start']) {
            return tpl_getLang('home');
        }
        $nom = PhpString::ucfirst(strtr(noNS($id), '_', ' '));
        if (auth_quickaclcheck($id) < AUTH_READ) {
            return $nom;
        }
        return self::lire()['pages'][$id]['libelle'] ?? (p_get_first_heading($id) ?: $nom);
    }

    /**
     * Les étapes du fil d'Ariane, de l'accueil à ce qui est affiché : la rubrique qui cite la page
     * (sinon les espaces de noms qui la contiennent), la page, puis l'action en cours (anciennes
     * révisions, liens de retour…). Une action sur tout le wiki (recherche, derniers changements)
     * suit directement l'accueil. Seule la dernière étape n'a pas de lien.
     *
     * @return array<int, array{libelle: string, lien: ?string}>
     */
    public static function getFilAriane(): array
    {
        global $ID, $ACT, $conf, $lang;
        $etapes = [['libelle' => tpl_getLang('home'), 'lien' => wl($conf['start'])]];

        if (in_array($ACT, self::ACTIONS_DU_WIKI)) {
            $etapes[] = ['libelle' => self::getLibelleAction(), 'lien' => null];
            return self::terminer($etapes);
        }

        if ($ID !== $conf['start']) {
            $rubrique = self::getRubrique($ID);
            if ($rubrique !== null) {
                $titre = self::lire()['rubriques'][$rubrique];
                $etapes[] = ['libelle' => $titre['titre'], 'lien' => wl($conf['start']) . '#' . $titre['ancre']];
            } else {
                array_push($etapes, ...self::getEspaces($ID));
            }
            $etapes[] = ['libelle' => self::getLibelle($ID), 'lien' => wl($ID)];
        }

        if (isset(self::ACTIONS_DE_LA_PAGE[$ACT])) {
            $etapes[] = ['libelle' => $lang[self::ACTIONS_DE_LA_PAGE[$ACT]], 'lien' => null];
        }
        return self::terminer($etapes);
    }

    /**
     * Les pages que le visiteur a consultées (la piste de DokuWiki, $conf['breadcrumbs'] pages au
     * plus), la plus récente d'abord, sans la page affichée.
     *
     * @return array<string, string> id => libellé
     */
    public static function getConsultees(): array
    {
        global $ID, $conf;
        if (!$conf['breadcrumbs']) {
            return [];
        }
        $pages = [];
        foreach (array_reverse(array_keys(breadcrumbs())) as $id) {
            // Une page au nom numérique (2017) est une clé entière du tableau
            $id = (string) $id;
            if ($id !== $ID && page_exists($id) && auth_quickaclcheck($id) >= AUTH_READ) {
                $pages[$id] = self::getLibelle($id);
            }
        }
        return $pages;
    }

    /**
     * Le titre du document (onglet du navigateur, historique, favoris) : ce qui est affiché, puis
     * le nom du wiki. « Les combats - Le wiki d'Edenya », « Les combats - Anciennes révisions -
     * Le wiki d'Edenya ».
     */
    public static function getTitreDocument(): string
    {
        global $ID, $ACT, $lang;
        $parties = [];
        if (in_array($ACT, self::ACTIONS_DU_WIKI)) {
            $parties[] = self::getLibelleAction();
        } else {
            $parties[] = self::getLibelle($ID);
            if (isset(self::ACTIONS_DE_LA_PAGE[$ACT])) {
                $parties[] = $lang[self::ACTIONS_DE_LA_PAGE[$ACT]];
            }
        }
        $parties[] = self::getNomDuWiki();
        return implode(' - ', $parties);
    }

    /** Le nom du wiki : son titre dans la configuration, sinon « Le wiki d'Edenya ». */
    public static function getNomDuWiki(): string
    {
        global $conf;
        return trim(strip_tags($conf['title'])) ?: tpl_getLang('nom_du_wiki');
    }

    /**
     * Le sommaire : l'accueil, puis chaque rubrique dépliable, ouverte sur la page affichée (aucune
     * pendant une action sur tout le wiki, qui ne se passe dans aucune rubrique).
     */
    public static function afficherSommaire(): void
    {
        global $ID, $ACT, $conf;
        $courante = in_array($ACT, self::ACTIONS_DU_WIKI) ? null : self::getRubrique($ID);
        echo '<nav class="wiki-boite wiki-sommaire" aria-labelledby="wiki-sommaire-titre">';
        echo '<h2 id="wiki-sommaire-titre">' . hsc(tpl_getLang('sommaire')) . '</h2>';
        echo '<ul class="wiki-sommaire__accueil"><li>' . self::lien($conf['start'], self::getLibelle($conf['start']), self::icone(self::ICONE_ACCUEIL)) . '</li></ul>';
        foreach (self::getRubriques() as $numero => $rubrique) {
            $ici = $numero === $courante;
            echo '<details class="wiki-rubrique"' . ($ici ? ' open' : '') . '>';
            echo '<summary' . ($ici ? ' aria-current="true"' : '') . '>' . hsc($rubrique['titre']) . '</summary>';
            echo '<ul>';
            foreach ($rubrique['pages'] as $id => $libelle) {
                echo '<li>' . self::lien((string) $id, $libelle) . '</li>';
            }
            echo '</ul></details>';
        }
        echo '</nav>';
    }

    /** Le fil d'Ariane, en tête de la page : où l'on est dans le sommaire du wiki. */
    public static function afficherFilAriane(): void
    {
        echo '<nav class="wiki-fil-ariane" aria-label="' . hsc(tpl_getLang('vous_etes_ici')) . '"><ol>';
        foreach (self::getFilAriane() as $rang => $etape) {
            $icone = $rang === 0 ? self::icone(self::ICONE_ACCUEIL) : '';
            echo '<li>' . ($etape['lien'] === null
                ? '<span aria-current="page">' . $icone . hsc($etape['libelle']) . '</span>'
                : '<a href="' . $etape['lien'] . '">' . $icone . hsc($etape['libelle']) . '</a>') . '</li>';
        }
        echo '</ol></nav>';
    }

    /** Les pages consultées, dans la colonne, sous le sommaire ; rien si l'on n'a vu que celle-ci. */
    public static function afficherConsultees(): void
    {
        $pages = self::getConsultees();
        if (!$pages) {
            return;
        }
        echo '<nav class="wiki-boite wiki-consultees" aria-labelledby="wiki-consultees-titre">';
        echo '<h2 id="wiki-consultees-titre">' . self::icone(self::ICONE_HISTORIQUE) . hsc(tpl_getLang('consultees')) . '</h2>';
        echo '<ol>';
        foreach ($pages as $id => $libelle) {
            echo '<li>' . self::lien((string) $id, $libelle) . '</li>';
        }
        echo '</ol></nav>';
    }

    /**
     * Le sommaire que le visiteur peut lire. Le sommaire de l'accueil est gardé dans le cache de
     * DokuWiki jusqu'à ce que le wiki change (purgefile : toute page enregistrée, créée ou
     * supprimée), sa configuration ou ce fichier ; seuls les droits se vérifient à chaque page.
     *
     * @return array{rubriques: array<int, array{titre: string, ancre: string, pages: array<string, string>}>, pages: array<string, array{rubrique: int, libelle: string}>}
     */
    protected static function lire(): array
    {
        global $conf;
        if (self::$sommaire !== null) {
            return self::$sommaire;
        }
        $accueil = $conf['start'];
        $cache = new Cache('edenya-sommaire:' . $accueil, '.sommaire');
        $fichiers = array_merge([wikiFN($accueil), $conf['cachedir'] . '/purgefile', __FILE__], getConfigFiles('main'));
        $sommaire = $cache->useCache(['files' => $fichiers]) ? unserialize($cache->retrieveCache(false), ['allowed_classes' => false]) : null;
        if (!is_array($sommaire)) {
            $sommaire = self::construire($accueil);
            $cache->storeCache(serialize($sommaire));
        }

        $lisible = ['rubriques' => [], 'pages' => []];
        foreach ($sommaire['rubriques'] as $numero => $rubrique) {
            $rubrique['pages'] = array_filter($rubrique['pages'], fn ($id) => auth_quickaclcheck((string) $id) >= AUTH_READ, ARRAY_FILTER_USE_KEY);
            if ($rubrique['pages'] !== []) {
                $lisible['rubriques'][$numero] = $rubrique;
                foreach ($rubrique['pages'] as $id => $libelle) {
                    $lisible['pages'][$id] = ['rubrique' => $numero, 'libelle' => $libelle];
                }
            }
        }
        return self::$sommaire = $lisible;
    }

    /**
     * Lit le sommaire dans les instructions de la page d'accueil, que DokuWiki garde en cache : ses
     * rubriques, et leurs pages qui existent. Les ancres des titres sont calculées comme le fait le
     * rendu de la page (sectionID(), titre après titre), pour que le lien d'une rubrique mène à son
     * titre dans l'accueil.
     *
     * @return array{rubriques: array<int, array{titre: string, ancre: string, pages: array<string, string>}>}
     */
    protected static function construire(string $accueil): array
    {
        $rubriques = [];
        $citees = [];
        $instructions = page_exists($accueil) ? p_cached_instructions(wikiFN($accueil), false, $accueil) : null;
        $resolveur = new PageResolver($accueil);
        $ancres = [];
        $rubrique = null;
        foreach ($instructions ?? [] as $instruction) {
            if ($instruction[0] === 'header' && !blank($instruction[1][0])) {
                [$titre, $niveau] = $instruction[1];
                $ancre = sectionID($titre, $ancres);
                // Un sous-titre (niveau 3 et plus) reste dans sa rubrique ; un titre 1 n'en ouvre pas
                if ($niveau <= 2) {
                    $rubrique = $niveau === 2 ? count($rubriques) : null;
                    if ($rubrique !== null) {
                        $rubriques[$rubrique] = ['titre' => $titre, 'ancre' => $ancre, 'pages' => []];
                    }
                }
            } elseif ($instruction[0] === 'internallink' && $rubrique !== null) {
                [$lien, $texte] = $instruction[1];
                $id = $resolveur->resolveId(explode('#', $lien, 2)[0]);
                if ($id === $accueil || isset($citees[$id]) || !page_exists($id) || isHiddenPage($id)) {
                    continue;
                }
                $citees[$id] = true;
                // Le texte du lien, sinon le titre de la page : pour un lien sans texte, une image, ou
                // un texte sans lettre (« Edenya Awards [[nominationsea2017|2017]] »)
                $rubriques[$rubrique]['pages'][$id] = is_string($texte) && preg_match('/\p{L}/u', $texte)
                    ? PhpString::ucfirst(trim($texte))
                    : (p_get_first_heading($id) ?: noNS($id));
            }
        }
        return ['rubriques' => $rubriques];
    }

    /**
     * Les espaces de noms d'une page que le sommaire ne cite pas (staff:magie : « Staff »), chacun
     * vers sa page d'accueil quand elle existe et se lit.
     *
     * @return array<int, array{libelle: string, lien: ?string}>
     */
    protected static function getEspaces(string $id): array
    {
        $etapes = [];
        $espace = '';
        foreach (array_slice(explode(':', $id), 0, -1) as $nom) {
            $espace .= $nom . ':';
            $page = (new PageResolver($id))->resolveId(':' . $espace);
            $lisible = $page !== $id && page_exists($page) && auth_quickaclcheck($page) >= AUTH_READ;
            $etapes[] = [
                'libelle' => $lisible ? self::getLibelle($page) : PhpString::ucfirst(strtr($nom, '_', ' ')),
                'lien' => $lisible ? wl($page) : null,
            ];
        }
        return $etapes;
    }

    /** Le libellé de l'action en cours sur tout le wiki (« Derniers changements », un outil d'administration). */
    protected static function getLibelleAction(): string
    {
        global $ACT, $conf, $lang;
        if ($ACT === 'admin' && ($plugin = plugin_getRequestAdminPlugin())) {
            return $plugin->getMenuText($conf['lang']) ?: $plugin->getPluginName();
        }
        return $lang['btn_' . $ACT] ?? $ACT;
    }

    /**
     * La dernière étape est ce qui est affiché : sans lien, même quand c'est une page (la page
     * affichée, l'accueil quand on y est).
     *
     * @param array<int, array{libelle: string, lien: ?string}> $etapes
     * @return array<int, array{libelle: string, lien: ?string}>
     */
    protected static function terminer(array $etapes): array
    {
        $etapes[array_key_last($etapes)]['lien'] = null;
        return $etapes;
    }

    /** Un lien vers une page, marqué aria-current="page" quand c'est la page affichée. */
    protected static function lien(string $id, string $libelle, string $icone = ''): string
    {
        global $ID, $ACT;
        $courante = $id === $ID && !in_array($ACT, self::ACTIONS_DU_WIKI);
        return '<a href="' . wl($id) . '"' . ($courante ? ' aria-current="page"' : '') . '>' . $icone . hsc($libelle) . '</a>';
    }

    /** L'adresse du site : il est à la racine du domaine, le wiki dans son dossier /wiki/. */
    public static function getAdresseDuSite(): string
    {
        return preg_replace('#[^/]+/$#', '', DOKU_BASE);
    }

    public static function icone(string $trace, string $classe = ''): string
    {
        return '<svg class="wiki-icone' . ($classe !== '' ? ' ' . $classe : '') . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="' . $trace . '"/></svg>';
    }
}
