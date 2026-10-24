# Thème « Edenya V5 » pour DokuWiki

Le thème du wiki d'Edenya, accordé au site hors jeu : la forêt du bandeau, le parchemin, les polices
Cinzel et EB Garamond, les couleurs du site en thème clair et sombre. Il dérive du thème `dokuwiki`
(2012) livré avec DokuWiki.

Ce qu'il ajoute au thème d'origine :

- **un sommaire** dans une colonne à gauche, tiré de la page d'accueil du wiki : une rubrique
  dépliable par grand titre, celle de la page affichée ouverte, la page marquée en gras ;
- **un fil d'Ariane** au-dessus du titre (« Accueil > Jouer > Les combats », puis l'action en
  cours : « Anciennes révisions ») ;
- **les pages consultées** dans leur propre boîte, sous le sommaire, la plus récente en tête ;
- **la table des matières** dans une colonne à droite, qui marque la partie en cours de lecture ;
- une grille qui s'adapte aux écrans, du téléphone (sommaire derrière un bouton) aux écrans de
  2560px, avec les mêmes paliers que le site et le jeu ;
- les liens du texte soulignés, comme sur le site, et un pied de page sans mention de licence ;
- **le thème du site**, clair, sombre ou celui du système, et le même bouton pour en changer : un
  thème choisi au wiki se retrouve sur le site, et l'inverse (voir « Le thème clair ou sombre ») ;
- **une invitation à jouer** en tête des pages, pour qui ne joue pas encore, et pour qui joue, le
  retour sur le site ou en jeu en tête de la colonne (voir « Qui joue, qui ne joue pas »).

Les règles de style du thème sont décrites dans la documentation du site, `.docs/DESIGN.md` §12.

## Prérequis

- DokuWiki 2026-07-14c « Mort » : c'est la version sur laquelle le thème est vérifié. Il s'appuie
  sur des API apparues en 2022 (`dokuwiki\File\PageResolver`) et ne fonctionne pas sur une version
  plus ancienne.
- PHP 8.2 à 8.5, comme le site.
- Un accès au dépôt : il est privé, il faut être membre de l'organisation `edenya` sur GitHub.

## Installation

Les chemins sont donnés depuis la racine du site d'Edenya, où DokuWiki est installé dans `wiki/`.
Pour un DokuWiki installé ailleurs, partir de son propre dossier.

1. **Cloner le thème** dans `wiki/lib/tpl/edenya`. Le dossier doit porter ce nom : c'est celui du
   thème pour DokuWiki.

   ```bash
   git clone https://github.com/edenya/edenya-dokuwiki-theme.git wiki/lib/tpl/edenya
   ```

   Cloner avec l'utilisateur qui fera les mises à jour (`git pull`). Le serveur web n'a besoin que
   de lire ces fichiers : le thème n'écrit rien dans son dossier, seulement dans le cache de
   DokuWiki (`wiki/data/cache`).

2. **Ne pas servir le dossier `.git`.** Sur le site d'Edenya, le `.htaccess` racine s'en charge
   (`RedirectMatch 404 /\.git`). Pour un wiki installé ailleurs, ajouter la même ligne à son
   `.htaccess` ou à la configuration d'Apache.

3. **Choisir le thème**, dans `wiki/conf/local.php` :

   ```php
   $conf['template'] = 'edenya';
   ```

   ou dans le wiki : Administration > Paramètres de configuration > Thème.

4. **Régler le wiki** (voir « Les réglages du wiki » plus bas) : au moins la page d'accueil, qui
   porte le sommaire.

5. **Vider les caches** de DokuWiki, pour qu'il recompose les feuilles de style et les pages :

   ```bash
   touch wiki/conf/local.php
   ```

6. **Vérifier** : ouvrir l'accueil du wiki, puis une page citée par son sommaire. Le sommaire doit
   ouvrir la rubrique de la page, le fil d'Ariane la situer, et la table des matières suivre le
   défilement. Regarder aussi une largeur de téléphone (le bouton « Sommaire ») et le thème sombre
   (le bouton à côté de la recherche).

## Les réglages du wiki

Ils se font dans `wiki/conf/local.php` ou par Administration > Paramètres de configuration.

| Réglage | Rôle dans le thème |
|---------|--------------------|
| `$conf['start']` | La page d'accueil. Son sommaire devient celui de la colonne (voir plus bas). Sur Edenya : `aide`. |
| `$conf['breadcrumbs']` | Le nombre de pages que DokuWiki retient pour la boîte « Pages consultées » ; `0` la retire. |
| `$conf['title']` | Le nom du wiki dans le titre des onglets. Vide, le thème écrit « Le wiki d'Edenya ». |
| `$conf['tagline']` | Une phrase sous le logo, si on en veut une. |
| `$conf['sidebar']` | Une page facultative (`sidebar` par défaut) : si elle existe, elle s'affiche dans la colonne, sous les pages consultées. |
| `$conf['youarehere']` | Inutile : le thème a son propre fil d'Ariane. |
| `$conf['license']` | Le thème ne l'affiche pas. DokuWiki la montre encore sous le formulaire de modification, à qui peut écrire. |

Le manifeste (`lib/exe/manifest.php`) prend sa couleur de fond dans `style.ini`, où les couleurs
sont des variables CSS qu'il ne comprend pas (« 'var(--surface-haute)' is not a valid color »). Le
navigateur la lit avant toute feuille de style, pour l'écran de lancement du wiki installé comme
application : elle ne peut pas suivre le thème. Elle s'écrit donc en dur, dans
`wiki/conf/manifest.local.json` (que les mises à jour de DokuWiki gardent), la couleur du sol clair
du site :

```json
{
    "background_color": "#ad8a64"
}
```

Les largeurs de la page et de la colonne, et celle où le thème passe en mode téléphone, sont dans
`style.ini` (`__site_width__`, `__sidebar_width__`, `__tablet_width__`) ; les paliers des écrans
larges, dans `css/edenya.css`.

## Le sommaire

Le sommaire de la colonne n'est écrit qu'à un endroit : la page d'accueil du wiki. On change le
menu en modifiant cette page, comme n'importe quelle autre.

- Chaque titre de niveau 2 (`===== Jouer =====`) ouvre une rubrique. Un titre de niveau 1 n'en
  ouvre pas ; un sous-titre (niveau 3 et plus) reste dans sa rubrique.
- Les liens internes qui suivent le titre en sont les pages, dans leur ordre, sous le texte du
  lien. Un lien sans texte, ou dont le texte n'a pas de lettre (« [[nominationsea2017|2017]] »),
  prend le titre de la page.
- Une page citée deux fois reste dans la première rubrique qui la cite. Un lien vers une section
  d'une page déjà citée (`combats#les_tactiques_de_combats`) n'ajoute rien.
- Les liens externes, les pages qui n'existent pas, les pages cachées et celles que le visiteur ne
  peut pas lire n'y figurent pas. Une rubrique sans page n'y figure pas non plus.
- Une page que le sommaire ne cite pas se situe par ses espaces de noms dans le fil d'Ariane
  (« Accueil > Staff > Aide »).

Le thème garde le sommaire dans le cache de DokuWiki jusqu'au prochain enregistrement d'une page
(n'importe laquelle), au prochain changement de la configuration du wiki ou de `Navigation.php` :
une modification de l'accueil se voit aussitôt.

## Le thème clair ou sombre

Le wiki prend le thème du site d'Edenya, qui le retient à trois endroits : le cookie `edenya_theme`
(tout le domaine), la mémoire du navigateur (`localStorage['edenya-theme']`) et, pour un joueur
connecté au site, ses préférences.

- La page est rendue dans le thème du cookie (`Theme.php`) ; son amorce, en tête de page, prend
  avant l'affichage celui de la mémoire du navigateur, où le site recopie la préférence du joueur
  connecté. Sans choix, c'est le thème par défaut du site, le clair.
- Le bouton, à côté de la recherche, passe du clair au sombre et inversement (`script.js`) : il
  écrit le cookie et la mémoire du navigateur, et envoie le choix au service du site
  (`_services/theme.php`, à la racine du domaine), qui l'enregistre dans les préférences d'un
  joueur connecté au site. Le wiki doit donc être servi sur le même domaine que le site, dans son
  dossier.
- « Comme mon système » se choisit sur le site (Votre compte, Affichage) ; le wiki le suit.

Pour un wiki installé hors du site d'Edenya, le service ne répond pas (le bouton n'en tient pas
compte) et le thème reste retenu par le navigateur.

## Qui joue, qui ne joue pas

Le wiki ne connaît pas les comptes du site : les siens sont ceux du staff. Pour savoir si le
visiteur joue, il lit deux cookies que le site pose sur tout le domaine, qui ne disent pas qui
joue (`Visiteur.php`) :

| Cookie | Ce qu'il dit |
|--------|--------------|
| `cache_connecte` | Un joueur est connecté au site. Le site le renouvelle à chaque page tant que la session vit, il dure ce que dure une session sans activité, et la déconnexion l'efface. |
| `cache_r` | Ce joueur sera reconnecté tout seul (« se souvenir de moi ») à sa prochaine page du site, tant qu'il ne se déconnecte pas. |

- **Qui joue** (l'un ou l'autre cookie) a, en tête de la colonne, une boîte pour retourner sur le
  site (son accueil) ou en jeu (la page « Jouer » du site, qui ouvre le choix du personnage).
  Elle est avant le bouton « Sommaire » : sur un téléphone, elle reste visible.
- **Qui ne joue pas** a, au-dessus de la page, un bandeau qui invite à rejoindre Edenya
  (l'inscription du site), et un lien vers la connexion, pour qui a déjà un compte. Il ne
  s'affiche qu'à la lecture (une page, la recherche, l'index), jamais en modification ni dans
  l'administration, ni pour le staff connecté au wiki, et pas à l'impression.

Ce n'est qu'un indice : un cookie effacé fait revenir le bandeau, et il ne protège rien. Les
textes sont dans `lang/fr/lang.php` (`invitation_*`, `retour*`). Pour un wiki installé hors du
site d'Edenya, ces cookies n'existent pas : tous les visiteurs voient le bandeau.

## Mettre à jour

```bash
git -C wiki/lib/tpl/edenya pull --ff-only
```

Si un changement ne se voit pas, `touch wiki/conf/local.php` vide tous les caches de DokuWiki.

Les mises à jour de DokuWiki (sur Edenya : `php _batch/wiki.php maj`, voir le `README.md` du site)
ne touchent jamais au thème. Après une nouvelle version de DokuWiki, regarder quelques pages et
leurs actions (révisions, modification, recherche) : le thème dérive du thème `dokuwiki`, et une
version peut demander de l'adapter.

## Les fichiers

| Fichier | Rôle |
|---------|------|
| `main.php` | Le gabarit des pages : le contenu d'abord dans le code, puis la colonne de navigation et la table des matières, que la grille remet à leur place. |
| `tpl_header.php`, `tpl_footer.php` | Le bandeau (logo, connexion, recherche, outils) et le pied de page, partagés avec `detail.php` (le détail d'une image) et `mediamanager.php`. |
| `Navigation.php` | Le sommaire, le fil d'Ariane, les pages consultées et le titre des onglets (`dokuwiki\template\edenya\Navigation`, chargée par DokuWiki). |
| `Theme.php` | Le thème clair ou sombre du site : celui de la page, l'amorce et le bouton de bascule. |
| `Visiteur.php` | Qui joue, d'après les cookies du site : l'invitation à jouer en tête des pages, ou le retour sur le site et en jeu en tête de la colonne. |
| `script.js` | Le bouton « Sommaire » sur téléphone, la bascule du thème, la table des matières au clavier et la partie en cours de lecture. |
| `style.ini` | La liste des feuilles de style et les couleurs de DokuWiki, renvoyées vers les jetons du site. |
| `css/edenya.css` | Les jetons du site (couleurs, polices, ombres, largeurs) et tout ce qui est propre à Edenya. |
| `css/structure.less` | La grille de la page et ses paliers. |
| Les autres `css/*` | Les feuilles du thème `dokuwiki`, adaptées là où il le fallait (plus de flottants). |
| `lang/` | Les textes du thème ; ceux d'Edenya sont en fin de `lang/fr/lang.php` et `lang/en/lang.php`. |
| `fonts/`, `images/` | Les polices, le logo, les textures et la forêt du bandeau. |

## Modifier le thème

- Le wiki ne charge pas les feuilles du site : `css/edenya.css` recopie les jetons de
  `_styles/tokens.css`, sous les mêmes noms. Une couleur changée sur le site se reporte ici, dans
  les trois blocs, comme sur le site : le clair (`:root`), le sombre choisi
  (`:root[data-theme='sombre']`) et le sombre du système (`auto`, sous `prefers-color-scheme`).
- Une icône prend la couleur d'un jeton : un SVG en ligne (`fill: currentColor`) ou, quand DokuWiki
  la dessine par une feuille (la loupe de la recherche), un masque (`mask-image`) teint par le fond.
  Jamais une image d'une seule couleur, qui disparaît dans l'un des deux thèmes.
- DokuWiki fait passer toutes les feuilles, `.css` compris, par son compilateur LESS. Deux pièges :
  ne jamais écrire `@media only screen` dans une feuille déjà réservée à l'écran (cela donne une
  requête invalide, ignorée par le navigateur), et protéger un `calc()` qui mélange les unités :
  `~"calc(100vh - 2em)"` (sinon il devient `calc(98vh)`).
- Les règles du site valent ici : pas de tiret long dans les textes, des fichiers en fins de ligne
  LF, la mise en page en grille ou en flex, jamais en flottants.
- Vérifier un changement dans les deux thèmes (clair et sombre) et à plusieurs largeurs : 390px
  (téléphone), 1100px (la table des matières sous le sommaire), 1440px (trois colonnes), 1920px et
  2560px (les paliers larges).
- Lancer du PHP de DokuWiki en ligne de commande dans le conteneur Docker (en root) écrit des
  fichiers de cache que le serveur web ne peut plus réécrire : les pages affichent alors
  « Writing … failed ». Supprimer ces fichiers de `wiki/data/cache` ensuite.

## Licences

- Le thème dérive du thème `dokuwiki` d'Anika Henke, Andreas Gohr et Clarence Lee, sous licence
  GPL 2 : il reste sous cette licence.
- Les polices Cinzel et EB Garamond sont sous SIL Open Font License 1.1.
- Les icônes du sommaire et du fil d'Ariane viennent des Material Design Icons (Apache 2.0), comme
  celles des menus de DokuWiki.
- Les icônes reprises du thème `dokuwiki` gardent les licences de `images/license.txt` et
  `images/pagetools/license.txt`.
- Le logo, les textures et la forêt du bandeau sont ceux du site d'Edenya.
