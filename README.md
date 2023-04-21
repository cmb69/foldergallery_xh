# Foldergallery_XH

Foldergallery_XH facilitates the presentation of image galleries on a
CMSimple_XH website. Contrary to most other existing gallery plugins, the
galleries are created completely automatically from existing images in a
given folder, so that the galleries do not have to be prepared in the
back-end. That makes Foldergallery_XH especially suited for large amounts of
images, and also for use with [Extedit_XH](https://github.com/cmb69/extedit_xh).

- [Requirements](#requirements)
- [Download](#download)
- [Installation](#installation)
- [Settings](#settings)
- [Usage](#usage)
- [Limitations](#limitations)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Credits](#credits)

## Requirements

Foldergallery_XH is a plugin for [CMSimple_XH](https://www.cmsimple-xh.org/).
It requires CMSimple_XH ≥ 1.6.3, and PHP ≥ 5.4.0 with the GD and JSON extensions.

## Download

The [lastest release](https://github.com/cmb69/foldergallery_xh/releases/latest)
is available for download on Github.

## Installation

The installation is done as with many other CMSimple_XH plugins. See the
[CMSimple_XH Wiki](https://wiki.cmsimple-xh.org/?for-users/working-with-the-cms/plugins)
for further details.

1. **Backup the data on your server.**
1. Unzip the distribution on your computer.
1. Upload the whole folder `foldergallery/` to your server into the `plugins/`
   folder of CMSimple_XH.
1. Set write permissions for the subfolders `cache/`, `config/`, `css/` and
   `languages/`.
1. Go to `Plugins` → `Foldergallery` in the back-end to check if
   all requirements are fulfilled.

## Settings

The configuration of the plugin is done as with many other CMSimple_XH plugins in
the back-end of the Website. Select `Plugins` → `Foldergallery`.

You can change the default settings of Foldergallery_XH under
`Config`. Hints for the options will be displayed when hovering over
the help icon with your mouse.

Localization is done under `Language`. You can translate the character
strings to your own language if there is no appropriate language file available,
or customize them according to your needs.

The look of Foldergallery_XH can be customized under `Stylesheet`.

## Usage

Prepare the galleries by uploading images into the `userfiles/images/` folder
of CMSimple_XH, or a subfolder thereof, either via FTP or a filebrowser.
The image captions shown by Foldergallery_XH are retrieved from the filename or,
if available, the Exif `ImageDescription` tag stored in the image file.

To present all images in the image folder on a page, insert the following
plugin call:

    {{{foldergallery()}}}

To present only the images of a subfolder (and all subfolders of that
folder), use something like the following (replace `%SUBFOLDERNAME%`
with the actual name of the subfolder):

    {{{foldergallery(%)'SUBFOLDERNAME%)'}}}

## Limitations

Only a single gallery can be shown on each page.

Only JPEG images are supported; other image formats are ignored.

It takes some time to create the thumbnails, so many large images in the
same folder may trigger a PHP timeout (CMSimple_XH reports a fatal error in
this case). You may have to refresh your browser (`F5`) several times,
until all thumbnails have been created. To avoid that
this happens to visitors of your Website, it is recommended to have a look
at the folder preview immediately after uploading a bunch of images.

## Troubleshooting

Report bugs and ask for support either on
[Github](https://github.com/cmb69/foldergallery_xh/issues)
or in the [CMSimple_XH Forum](https://cmsimpleforum.com/).

## License

Foldergallery_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Foldergallery_XH is distributed in the hope that it will be useful,
but *without any warranty*; without even the implied warranty of
*merchantibility* or *fitness for a particular purpose*. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foldergallery_XH.  If not, see <https://www.gnu.org/licenses/>.

Copyright © 2017 Christoph M. Becker

Slovak translation © 2017 Dr. Martin Sereday

## Credits

This plugin is inspired by the [Fast Gallery](https://www.drupal.org/project/fast_gallery)
Drupal module.

Foldergallery_XH uses [Colorbox](https://www.jacklmoore.com/colorbox/) by Jack Moore
and [PhotoSwipe](https://photoswipe.com/) by Dmitry Semenov.
Thanks for releasing these great scripts under MIT license.

The plugin logo is designed by [Everaldo Coelho](https://www.everaldo.com/).
Many thanks for publishing this icon under GPL.

Many thanks to the community at the [CMSimple_XH Forum](https://www.cmsimpleforum.com)
for tips, suggestions and testing. Particularly, I like to thank `frase` and `lck`
for testing and feedback on an early development preview. Also many thanks to
`bemerkenswelt` who inspired the use of [PhotoSwipe](https://photoswipe.com/).
And many thanks to `frase` for pointing me to the flex layout model.

And last but not least many thanks to [Peter Harteg](https://www.harteg.dk/),
the “father” of CMSimple, and all developers of [CMSimple_XH](https://www.cmsimple-xh.org/)
without whom this amazing CMS would not exist.
