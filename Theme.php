<?php

namespace dokuwiki\template\edenya;

/**
 * Le thème clair ou sombre du wiki, partagé avec le site d'Edenya.
 *
 * Le site retient le choix du visiteur dans le cookie edenya_theme (tout le domaine) et dans
 * localStorage['edenya-theme'], celui d'un joueur connecté dans ses préférences
 * (_services/theme.php), et aligne la mémoire du navigateur sur cette préférence à chaque page.
 * Le wiki rend la page dans le thème du cookie, son amorce reprend celui de la mémoire du
 * navigateur avant l'affichage, et sa bascule écrit aux trois endroits : un thème choisi au wiki
 * se retrouve sur le site, et l'inverse.
 */
class Theme
{
    /** Les thèmes du site (Templater::$themes) : auto suit le système. */
    public const THEMES = ['clair', 'sombre', 'auto'];

    /** Le thème de qui n'a rien choisi, celui du site (Templater::THEME_DEFAUT). */
    public const DEFAUT = 'clair';

    /** Où le site retient le choix : le cookie (lu par PHP) et la mémoire du navigateur. */
    public const COOKIE = 'edenya_theme';
    public const STOCKAGE = 'edenya-theme';

    /** Le service du site qui enregistre le choix d'un joueur connecté, depuis la racine du site. */
    public const SERVICE = '_services/theme.php';

    /** La lune et le soleil, des Material Design Icons : l'icône montre le thème où l'on va. */
    protected const ICONE_SOMBRE = 'M2 12A10 10 0 0 0 15 21.54A10 10 0 0 1 15 2.46A10 10 0 0 0 2 12Z';
    protected const ICONE_CLAIR = 'M3.55 19.09L4.96 20.5L6.76 18.71L5.34 17.29M12 6C8.69 6 6 8.69 6 12S8.69 18 12 18 18 15.31 18 12C18 8.68 15.31 6 12 6M20 13H23V11H20M17.24 18.71L19.04 20.5L20.45 19.09L18.66 17.29M20.45 5L19.04 3.6L17.24 5.39L18.66 6.81M13 1H11V4H13M6.76 5.39L4.96 3.6L3.55 5L5.34 6.81L6.76 5.39M1 13H4V11H1M13 20H11V23H13';

    /** Le thème que le cookie du site demande, celui du site par défaut. */
    public static function getTheme(): string
    {
        $theme = $_COOKIE[self::COOKIE] ?? null;
        return in_array($theme, self::THEMES, true) ? $theme : self::DEFAUT;
    }

    /**
     * L'amorce, dans le <head> : le thème que le navigateur a retenu (celui du joueur connecté,
     * que le site y recopie) remplace celui du cookie avant que la page s'affiche.
     */
    public static function afficherAmorce(): void
    {
        echo '<script>(function () { try { var theme = localStorage.getItem(' . json_encode(self::STOCKAGE) . '); '
            . 'if (' . json_encode(self::THEMES) . '.indexOf(theme) !== -1) document.documentElement.setAttribute(\'data-theme\', theme); '
            . '} catch (e) {} })();</script>' . "\n";
    }

    /** Le bouton qui passe du thème clair au sombre et inversement (script.js), comme celui du site. */
    public static function afficherBascule(): void
    {
        $libelle = hsc(tpl_getLang('bascule_theme'));
        echo '<button type="button" class="wiki-theme" data-service="' . hsc(Navigation::getAdresseDuSite() . self::SERVICE) . '"'
            . ' data-cookie="' . self::COOKIE . '" data-stockage="' . self::STOCKAGE . '"'
            . ' title="' . $libelle . '" aria-label="' . $libelle . '">'
            . Navigation::icone(self::ICONE_SOMBRE, 'wiki-theme__sombre')
            . Navigation::icone(self::ICONE_CLAIR, 'wiki-theme__clair')
            . '</button>';
    }
}
