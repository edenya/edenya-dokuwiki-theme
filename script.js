/**
 *  We handle several device classes based on browser width.
 *
 *  - desktop:   > __tablet_width__ (as set in style.ini)
 *  - mobile:
 *    - tablet   <= __tablet_width__
 *    - phone    <= __phone_width__
 */
var device_class = ''; // not yet known
var device_classes = 'desktop mobile tablet phone';

function tpl_dokuwiki_mobile(){

    // the z-index in mobile.css is (mis-)used purely for detecting the screen mode here
    var screen_mode = jQuery('#screen__mode').css('z-index') + '';

    // determine our device pattern
    // TODO: consider moving into dokuwiki core
    switch (screen_mode) {
        case '1':
            if (device_class.match(/tablet/)) return;
            device_class = 'mobile tablet';
            break;
        case '2':
            if (device_class.match(/phone/)) return;
            device_class = 'mobile phone';
            break;
        default:
            if (device_class == 'desktop') return;
            device_class = 'desktop';
    }

    jQuery('html').removeClass(device_classes).addClass(device_class);

    // La table des matières est dépliée à côté de la page, repliée au-dessus d'elle sur un téléphone
    var $toc = jQuery('#dw__toc h3');
    if ($toc.length && $toc[0].setState) {
        $toc[0].setState(device_class == 'desktop' ? 1 : -1);
    }
}

/**
 * Sur un téléphone, la colonne de navigation (sommaire, pages consultées) se replie derrière son
 * bouton ; la feuille de style ne la cache que si ce script tourne (classe js).
 */
function tpl_edenya_bascule(){
    jQuery('#dokuwiki__aside .wiki-bascule').on('click', function () {
        this.setAttribute('aria-expanded', this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true');
    });
}

/**
 * La bascule du thème, celle du site (_scripts/edenya.ui.js) : elle passe du clair au sombre et
 * inversement, et retient le choix là où le site le lit (Theme.php) : le cookie de tout le
 * domaine, la mémoire du navigateur et, pour un joueur connecté au site, ses préférences.
 */
function tpl_edenya_theme(){
    var racine = document.documentElement;

    jQuery('#dokuwiki__header .wiki-theme').on('click', function () {
        var theme = racine.getAttribute('data-theme');
        if (theme === 'auto') {
            theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'sombre' : 'clair';
        }
        theme = theme === 'sombre' ? 'clair' : 'sombre';
        racine.setAttribute('data-theme', theme);

        try {
            localStorage.setItem(this.getAttribute('data-stockage'), theme);
        } catch (e) {}
        document.cookie = this.getAttribute('data-cookie') + '=' + theme + ';path=/;max-age=31536000;samesite=lax' +
            (location.protocol === 'https:' ? ';secure' : '');

        if (window.fetch) {
            fetch(this.getAttribute('data-service'), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'theme=' + encodeURIComponent(theme)
            }).catch(function () {});
        }
    });
}

/**
 * Le titre de la table des matières la replie et la déplie : DokuWiki n'y répond qu'à la souris,
 * il se prend aussi au clavier.
 */
function tpl_edenya_toc_clavier(){
    jQuery('#dw__toc h3.toggle').attr({role: 'button', tabindex: 0}).on('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
        }
    });
}

/**
 * Dans la table des matières, la partie de la page qu'on lit (aria-current) : celle dont le titre
 * est passé en haut de l'écran, la dernière quand on est au bas de la page. La colonne défile
 * d'elle-même pour la garder en vue.
 */
function tpl_edenya_lecture(){
    var liens = jQuery('#dw__toc a[href^="#"]').get();
    var titres = liens.map(function (lien) {
        return document.getElementById(decodeURIComponent(lien.hash.slice(1)));
    });
    if (!liens.length) return;

    var colonne = document.querySelector('.wiki-toc');
    var courant = null;
    var attente = 0;

    function marquer() {
        attente = 0;
        var rang = -1;
        for (var i = 0; i < titres.length; i++) {
            if (titres[i] && titres[i].getBoundingClientRect().top <= 90) rang = i;
        }
        var racine = document.documentElement;
        if (window.scrollY > 0 && window.innerHeight + window.scrollY >= racine.scrollHeight - 2) {
            rang = liens.length - 1;
        }
        var lien = rang >= 0 ? liens[rang] : null;
        if (lien === courant) return;
        if (courant) courant.removeAttribute('aria-current');
        courant = lien;
        if (!lien) return;
        lien.setAttribute('aria-current', 'true');
        // La colonne seule défile, jamais la page
        if (colonne && colonne.scrollHeight > colonne.clientHeight) {
            var place = colonne.getBoundingClientRect();
            var item = lien.getBoundingClientRect();
            if (item.top < place.top || item.bottom > place.bottom) {
                colonne.scrollTop += item.top - place.top - colonne.clientHeight / 3;
            }
        }
    }

    window.addEventListener('scroll', function () {
        if (!attente) attente = window.requestAnimationFrame(marquer);
    }, {passive: true});
    marquer();
}

jQuery(function(){
    var resizeTimer;

    tpl_dokuwiki_mobile();
    jQuery(window).on('resize',
        function(){
            if (resizeTimer) clearTimeout(resizeTimer);
            resizeTimer = setTimeout(tpl_dokuwiki_mobile,200);
        }
    );

    tpl_edenya_bascule();
    tpl_edenya_theme();
    tpl_edenya_toc_clavier();
    tpl_edenya_lecture();

    // blur when clicked
    jQuery('#dokuwiki__pagetools div.tools>ul>li>a').on('click', function(){
        this.blur();
    });
});
