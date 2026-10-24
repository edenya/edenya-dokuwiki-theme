<?php

/**
 * Thème « Edenya V5 » du wiki, dérivé du thème DokuWiki 2012
 *
 * Le contenu vient d'abord dans le code, puis la colonne de navigation (le sommaire du wiki et les
 * pages consultées, Navigation.php) et la table des matières de la page : la grille d'edenya.css
 * les remet à leur place. Le fil d'Ariane ouvre la page.
 *
 * @link     http://dokuwiki.org/template
 * @author   Anika Henke <anika@selfthinker.org>
 * @author   Clarence Lee <clarencedglee@gmail.com>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

use dokuwiki\template\edenya\Navigation;
use dokuwiki\template\edenya\Theme;
use dokuwiki\template\edenya\Visiteur;

if (!defined('DOKU_INC')) die(); /* must be run from within DokuWiki */

$hasSidebar = page_findnearest($conf['sidebar']);
$avecNavigation = !in_array($ACT, Navigation::ACTIONS_PLEINE_LARGEUR);
?><!DOCTYPE html>
<html lang="<?php echo $conf['lang'] ?>" dir="<?php echo $lang['direction'] ?>" class="no-js" data-theme="<?php echo hsc(Theme::getTheme()) ?>">
<head>
    <meta charset="utf-8" />
    <?php Theme::afficherAmorce() ?>
    <title><?php echo hsc(Navigation::getTitreDocument()) ?></title>
    <?php tpl_metaheaders() ?>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <?php echo tpl_favicon(['favicon', 'mobile']) ?>
    <?php tpl_includeFile('meta.html') ?>
</head>

<body>
    <div id="dokuwiki__site"><div id="dokuwiki__top" class="site <?php echo tpl_classes(); ?> <?php
        echo $avecNavigation ? 'wiki-avec-navigation' : ''; ?> <?php echo ($hasSidebar) ? 'hasSidebar' : ''; ?>">

        <?php include(__DIR__ . '/tpl_header.php') ?>

        <div class="wrapper group">

            <!-- ********** CONTENT ********** -->
            <main id="dokuwiki__content"><div class="pad group">
                <?php html_msgarea() ?>

                <?php Visiteur::afficherInvitation() ?>

                <div class="page group">
                    <?php tpl_flush() ?>
                    <?php Navigation::afficherFilAriane() ?>
                    <?php tpl_includeFile('pageheader.html') ?>
                    <!-- wikipage start -->
                    <?php tpl_content(false) ?>
                    <!-- wikipage stop -->
                    <?php tpl_includeFile('pagefooter.html') ?>
                </div>

                <div class="docInfo"><?php tpl_pageinfo() ?></div>

                <?php tpl_flush() ?>

                <hr class="a11y" />
            </div></main><!-- /content -->

            <?php if ($avecNavigation) : ?>
                <!-- ********** NAVIGATION ********** -->
                <aside id="dokuwiki__aside" aria-label="<?php echo hsc(tpl_getLang('navigation')) ?>">
                    <?php Visiteur::afficherRetours() ?>
                    <button type="button" class="wiki-bascule" aria-expanded="false" aria-controls="wiki-navigation">
                        <?php echo hsc(tpl_getLang('sommaire')) ?>
                    </button>
                    <div id="wiki-navigation" class="wiki-navigation">
                        <?php Navigation::afficherSommaire() ?>
                        <?php Navigation::afficherConsultees() ?>
                        <?php if ($hasSidebar) : ?>
                            <div class="wiki-boite aside include group">
                                <?php tpl_flush() ?>
                                <?php tpl_includeFile('sidebarheader.html') ?>
                                <?php tpl_include_page($conf['sidebar'], true, true) ?>
                                <?php tpl_includeFile('sidebarfooter.html') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </aside><!-- /aside -->
            <?php endif; ?>

            <?php $toc = tpl_toc(true); ?>
            <?php if ($toc) : ?>
                <!-- ********** TABLE DES MATIÈRES ********** -->
                <aside class="wiki-toc" aria-label="<?php echo hsc($lang['toc']) ?>"><?php echo $toc ?></aside>
            <?php endif; ?>

            <!-- PAGE ACTIONS -->
            <nav id="dokuwiki__pagetools" aria-labelledby="dokuwiki__pagetools__heading">
                <h3 class="a11y" id="dokuwiki__pagetools__heading"><?php echo $lang['page_tools']; ?></h3>
                <div class="tools">
                    <ul>
                        <?php echo (new \dokuwiki\Menu\PageMenu())->getListItems(); ?>
                    </ul>
                </div>
            </nav>
        </div><!-- /wrapper -->

        <?php include(__DIR__ . '/tpl_footer.php') ?>
    </div></div><!-- /site -->

    <div class="no"><?php tpl_indexerWebBug() /* provide DokuWiki housekeeping, required in all templates */ ?></div>
    <div id="screen__mode" class="no"></div><?php /* helper to detect CSS media query in script.js */ ?>
</body>
</html>
