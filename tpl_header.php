<?php

/**
 * Template header, included in the main and detail files
 *
 * Le logo d'Edenya mène à l'accueil du wiki ; c'est une image dans un lien, pas un titre (le h1
 * est celui de la page, comme sur le site). Où l'on est dans le wiki se lit dans le fil d'Ariane,
 * en tête de la page, et les pages consultées dans la colonne : le bandeau n'a plus de piste.
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();
?>

<!-- ********** HEADER ********** -->
<header id="dokuwiki__header"><div class="pad group">

    <?php tpl_includeFile('header.html') ?>

    <ul class="a11y skip">
        <li><a href="#dokuwiki__content"><?php echo $lang['skip_to_content']; ?></a></li>
    </ul>

    <div class="headings group">
        <a class="logo" href="<?php echo wl() ?>" rel="home" accesskey="h" title="<?php
            echo hsc(tpl_getLang('accueil_du_wiki')) ?> [h]"><img src="<?php echo tpl_basedir() ?>images/edenya.webp"
            width="460" height="200" alt="Edenya" /><span><?php echo hsc(tpl_getLang('le_wiki')) ?></span></a>
        <?php if ($conf['tagline']) : ?>
            <p class="claim"><?php echo $conf['tagline']; ?></p>
        <?php endif ?>
    </div>

    <div class="tools group">
        <!-- USER TOOLS -->
        <?php if ($conf['useacl']) : ?>
            <div id="dokuwiki__usertools">
                <h3 class="a11y"><?php echo $lang['user_tools']; ?></h3>
                <ul>
                    <?php
                    if (!empty($_SERVER['REMOTE_USER'])) {
                        echo '<li class="user">';
                        tpl_userinfo(); /* 'Logged in as ...' */
                        echo '</li>';
                    }
                        echo (new \dokuwiki\Menu\UserMenu())->getListItems('action ');
                    ?>
                </ul>
            </div>
        <?php endif ?>

        <!-- SITE TOOLS -->
        <div id="dokuwiki__sitetools">
            <h3 class="a11y"><?php echo $lang['site_tools']; ?></h3>
            <?php tpl_searchform(); ?>
            <div class="mobileTools">
                <?php echo (new \dokuwiki\Menu\MobileMenu())->getDropdown($lang['tools']); ?>
            </div>
            <ul>
                <li class="wiki-site"><a href="<?php
                    // Le site est à la racine du domaine, le wiki dans son dossier /wiki/
                    echo hsc(preg_replace('#[^/]+/$#', '', DOKU_BASE)) ?>"><?php echo hsc(tpl_getLang('le_site')) ?></a></li>
                <?php echo (new \dokuwiki\Menu\SiteMenu())->getListItems('action ', false); ?>
            </ul>
        </div>

    </div>

    <hr class="a11y" />
</div></header><!-- /header -->
