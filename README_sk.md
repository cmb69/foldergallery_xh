# Foldergallery_XH

Foldergallery_XH umožňuje zobrazovanie obrázkových galérií
na webstránkach CMSimple_XH. Na rozdiel od množstva existujúcich
pluginov sú galérie vytvárané automaticky zo všetkých obrázkov 
v uvedenom adresári, takže sa nemusia vytvárať v správcovskom
prostredí. To robí Foldergallery_XH vhodnou hlavne pre veľké zbierky
a pre použitie s [Extedit_XH](https://github.com/cmb69/extedit_xh).

- [Požiadavky](#požiadavky)
- [Download](#download)
- [Inštalácia](#inštalácia)
- [Nastavenie](#nastavenie)
- [Použitie](#použitie)
- [Obmedzenia](#obmedzenia)
- [Troubleshooting](#troubleshooting)
- [Licencia](#licencia)
- [Záruky](#záruky)

## Požiadavky

Foldergallery_XH is a plugin for [CMSimple_XH](https://www.cmsimple-xh.org/).
It requires CMSimple_XH ≥ 1.7.0, and PHP ≥ 7.1.0 with the `gd` and `json` extensions.
The PHP `exif` extension is recommended.

## Download

The [lastest release](https://github.com/cmb69/foldergallery_xh/releases/latest)
is available for download on Github.

## Inštalácia

Inštalácia prebieha rovnako ako pri väčšine CMSimple_XH pluginov. Viac na
[CMSimple_XH Wiki](https://wiki.cmsimple-xh.org/?for-users/working-with-the-cms/plugins).

1. **Urobte najprv zálohu dát na Vašom serveri.**
1. Rozbaľte archív vo Vašom počítači.
1. Uložte celý adresár `foldergallery/` na Váš server do adresára `plugins/`.
1. Nastavte prístupové práva pre podadresáre `cache/`, `config/`, `css/` a `languages/`.
1. Otvorte v správcovskom prostredí `Plugins` → `Foldergallery` a uistite sa,
   že sú splnené všetky potrebné podmienky.

## Nastavenie

Nastavenie pluginu sa robí rovnako ako pri iných pluginoch CMSimple_XH
v správcovskom prostredí stránky. Otvorte `Plugins` → `Foldergallery`.

Zmeny nastavení môžete urobiť v Foldergallery_XH v `Config`.
Vysvetlivky sa zobrazia prechodom na ikonu pomocníka.

Jazykové prostredie si aktuaizujete prekladom položiek v `Language`.
Formulácie môžete prepísať do Vášho jazyka, ak tento nie je v archíve,
alebo ich môžete upraviť podľa Vašich potrieb.

Vzhľad Foldergallery_XH môžete upraviť pod `Stylesheet`.

## Použitie

Galérie pripravíte uložením obrázkov do adresára `userfiles/images/`,
alebo umiestnením celých "obrázkových" podadresárov pomocou FTP alebo správcu súborov.
Názvy obrázkov preberá Foldergallery_XH z názvov súborov,
alebo z Exif `ImageDescription`, ak je táto informácia uložená v obrázku.

Ak chcete zobraziť všetky obrázky z adresára `userfiles/images/`,
použite na stránke príkaz::

    {{{foldergallery()}}}

Ak chcete zobraziť všetky obrázky z konkrétneho podadresára/podadresárov, 
použite:

    {{{foldergallery('%NAZOV PODADRESAR%'}}}

## Obmedzenia

Zatiaľ sa dá na jednej stránke použiť iba jedna galéria.

Only JPEG images are supported; other image formats are ignored.

If the PHP exif extension is not enabled, thumbnails of images with Exif
`Orientation` tags will not be displayed properly (they are rotated and or flipped).

## Troubleshooting

Report bugs and ask for support either on
[Github](https://github.com/cmb69/foldergallery_xh/issues)
or in the [CMSimple_XH Forum](https://cmsimpleforum.com/).

## Licencia

Foldergallery_XH je slobodný softvér: môžete ho šíriť a upravovať podľa ustanovení
Všeobecnej verejnej licencie GNU (GNU General Public Licence), vydávanej nadáciou
Free Software Foundation a to buď podľa 3. verzie tejto Licencie, alebo
(podľa vášho uváženia) ktorejkoľvek neskoršej verzie.

Foldergallery_XH je rozširovaný v nádeji, že bude užitočný, avšak *BEZ AKEJKOĽVEK ZÁRUKY*.
Neposkytujú sa ani odvodené záruky *PREDAJNOSTI* alebo *VHODNOSTI PRE URČITÝ ÚČEL*.
Ďalšie podrobnosti hľadajte vo Všeobecnej verejne licencii GNU.

Kópiu Všeobecnej verejnej licencie GNU ste mali dostať spolu s Foldergallery_XH.
Ak sa tak nestalo, nájdete ju tu: <https://www.gnu.org/licenses/>.

&copy; 2017 Christoph M. Becker

Slovak translation © 2017 Dr. Martin Sereday

## Záruky

Plugin je inšpirovaný [Fast Gallery](https://www.drupal.org/project/fast_gallery)
Drupal module.

Foldergallery_XH využíva [Colorbox](https://www.jacklmoore.com/colorbox/) by Jack Moore
and [PhotoSwipe](https://photoswipe.com/) by Dmitry Semenov.
Thanks for releasing these great scripts under MIT license.

Logo pluginu navrhol [Everaldo Coelho](http://www.everaldo.com/).
Ďakujem za pockytnutie ikony v zmysle licencie GPL.

Ďakujem celej komunite [CMSimple_XH Forum](https://www.cmsimpleforum.com) za návrhy,
rady a testovanie. Osobitne ďakujem *frase* a *lck* za testovanie a postrehy v začiatkoch.
Also many thanks to *bemerkenswelt* who inspired the use of [PhotoSwipe](https://photoswipe.com/).
And many thanks to *frase* for pointing me to the flex layout model.

Nakoniec, nemenej, veľka patrí [Petrovi Hartegovi](https://harteg.dk/),
otcovi CMSimple a všetkým vývojárom [CMSimple_XH](https://www.cmsimple-xh.org),
bez pomoci ktorých by tento skvelý CMSimple_XH nikdy neexistoval.
