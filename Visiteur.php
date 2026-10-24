<?php

namespace dokuwiki\template\edenya;

/**
 * Le visiteur du wiki vu du site : joue-t-il à Edenya ?
 *
 * Le wiki ne connaît pas les comptes du site (les siens sont ceux du staff) : il lit deux cookies
 * que le site pose sur tout le domaine, qui ne disent pas qui joue.
 *
 * - cache_connecte : un joueur est connecté au site. Le site le renouvelle à chaque page tant que
 *   la session vit et l'efface à la déconnexion (Joueurs::signalerConnexion()) ;
 * - cache_r : ce joueur est reconnecté tout seul (« se souvenir de moi ») dès sa prochaine page du
 *   site, tant qu'il ne se déconnecte pas.
 *
 * Qui joue a, en tête de la colonne, de quoi retourner sur le site ou en jeu ; qui ne joue pas,
 * en tête de la page, l'invitation à rejoindre Edenya (sauf le staff connecté au wiki). Ce n'est
 * qu'un indice, un cookie effacé fait revenir l'invitation : il ne protège rien.
 */
class Visiteur
{
    /** Les cookies du site (CookieCache, préfixe cache_) : la connexion, la connexion automatique. */
    public const COOKIE_CONNECTE = 'cache_connecte';
    public const COOKIE_AUTOMATIQUE = 'cache_r';

    /** Les pages du site, depuis sa racine : l'inscription, l'accueil (où l'on revient, où l'on se connecte), le jeu. */
    public const INSCRIPTION = 'inscription/';
    public const ACCUEIL = 'accueil/';
    public const JEU = 'jouer/';

    /** Les actions où l'on lit le wiki, les seules où l'invitation s'affiche : une page, la recherche, l'index. */
    public const ACTIONS_INVITATION = ['show', 'search', 'index'];

    /** Les icônes, des Material Design Icons (Apache 2.0) comme celles de Navigation.php : le site, le jeu. */
    protected const ICONE_SITE = 'M16.36,14C16.44,13.34 16.5,12.68 16.5,12C16.5,11.32 16.44,10.66 16.36,10H19.74C19.9,10.64 20,11.31 20,12C20,12.69 19.9,13.36 19.74,14M14.59,19.56C15.19,18.45 15.65,17.25 15.97,16H18.92C17.96,17.65 16.43,18.93 14.59,19.56M14.34,14H9.66C9.56,13.34 9.5,12.68 9.5,12C9.5,11.32 9.56,10.65 9.66,10H14.34C14.43,10.65 14.5,11.32 14.5,12C14.5,12.68 14.43,13.34 14.34,14M12,19.96C11.17,18.76 10.5,17.43 10.09,16H13.91C13.5,17.43 12.83,18.76 12,19.96M8,8H5.08C6.03,6.34 7.57,5.06 9.4,4.44C8.8,5.55 8.35,6.75 8,8M5.08,16H8C8.35,17.25 8.8,18.45 9.4,19.56C7.57,18.93 6.03,17.65 5.08,16M4.26,14C4.1,13.36 4,12.69 4,12C4,11.31 4.1,10.64 4.26,10H7.64C7.56,10.66 7.5,11.32 7.5,12C7.5,12.68 7.56,13.34 7.64,14M12,4.03C12.83,5.23 13.5,6.57 13.91,8H10.09C10.5,6.57 11.17,5.23 12,4.03M18.92,8H15.97C15.65,6.75 15.19,5.55 14.59,4.44C16.43,5.07 17.96,6.34 18.92,8M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z';
    protected const ICONE_JEU = 'M6.2,2.44L18.1,14.34L20.22,12.22L21.63,13.63L19.16,16.1L22.34,19.28C22.73,19.67 22.73,20.3 22.34,20.69L21.63,21.4C21.24,21.79 20.61,21.79 20.22,21.4L17,18.23L14.56,20.7L13.15,19.29L15.27,17.17L3.37,5.27V2.44H6.2M15.89,10L20.63,5.26V2.44H17.8L13.06,7.18L15.89,10M10.94,15L8.11,12.13L5.9,14.34L3.78,12.22L2.37,13.63L4.84,16.1L1.66,19.29C1.27,19.68 1.27,20.31 1.66,20.7L2.37,21.41C2.76,21.8 3.39,21.8 3.78,21.41L7,18.23L9.44,20.7L10.85,19.29L8.73,17.17L10.94,15Z';

    /** Un joueur connecté au site, ou qui le sera tout seul à sa prochaine page. */
    public static function estJoueur(): bool
    {
        return !empty($_COOKIE[self::COOKIE_CONNECTE]) || !empty($_COOKIE[self::COOKIE_AUTOMATIQUE]);
    }

    /** En tête du contenu, au-dessus de la page, pour qui ne joue pas : l'invitation à rejoindre Edenya. */
    public static function afficherInvitation(): void
    {
        global $ACT;
        if (!in_array($ACT, self::ACTIONS_INVITATION, true) || !empty($_SERVER['REMOTE_USER']) || self::estJoueur()) {
            return;
        }
        $site = Navigation::getAdresseDuSite();
        echo '<aside class="wiki-invitation" aria-label="' . hsc(tpl_getLang('invitation')) . '">';
        echo '<div class="wiki-invitation__texte">';
        echo '<p class="wiki-invitation__titre">' . hsc(tpl_getLang('invitation_titre')) . '</p>';
        echo '<p>' . hsc(tpl_getLang('invitation_texte')) . '</p>';
        echo '</div>';
        echo '<p class="wiki-invitation__liens">';
        echo '<a class="wiki-invitation__bouton" href="' . hsc($site . self::INSCRIPTION) . '">' . hsc(tpl_getLang('invitation_inscription')) . '</a>';
        echo '<a href="' . hsc($site . self::ACCUEIL) . '">' . hsc(tpl_getLang('invitation_connexion')) . '</a>';
        echo '</p></aside>';
    }

    /** En tête de la colonne, pour qui joue : retourner sur le site ou en jeu. */
    public static function afficherRetours(): void
    {
        if (!self::estJoueur()) {
            return;
        }
        $site = Navigation::getAdresseDuSite();
        echo '<nav class="wiki-boite wiki-retours" aria-label="' . hsc(tpl_getLang('retours')) . '"><ul>';
        echo '<li><a href="' . hsc($site . self::ACCUEIL) . '">' . Navigation::icone(self::ICONE_SITE) . hsc(tpl_getLang('retour_site')) . '</a></li>';
        echo '<li><a href="' . hsc($site . self::JEU) . '">' . Navigation::icone(self::ICONE_JEU) . hsc(tpl_getLang('retour_jeu')) . '</a></li>';
        echo '</ul></nav>';
    }
}
