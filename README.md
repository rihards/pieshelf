pieshelf
=========

pieshelf is a static gallery generator written in PHP. It's inspired by [gerallery](https://github.com/joonasfrom/gerallery), but tries to be entirely a CLI tool.

### Demo

You can check out a demo gallery over here: [rihards.github.io/pieshelf](http://rihards.github.io/pieshelf).

The demo was generated with the default theme using the following command:

`php pieshelf.php --input=/tmp/input --output=/tmp/output --thumb=180 --full=800 --name="Preview Gallery"`

Images courtesy of [/r/quotesporn](http://www.reddit.com/r/quotesporn) and [/r/wallpapers](http://www.reddit.com/r/wallpapers)

### Requirements

pieshelf requires PHP 5.3+ and ImageMagick.

### Usage

To generate a gallery type:

`php pieshelf.php --input=<directory with images> --output=<directory where the gallery should be generated>`

### Optional Parameters

`--name="Gallery Name"`

This will set the title of the page in the default theme.

`--theme=default`

You can specify which theme you'd like, you can fine more info about making those inside themes/default/index.tpl.php.

`--thumbnail=200`

The size that you want your thumbnails to be at.

`--full=1400`

If you'd like to resize the original/full images you can specify it here.

`--force=yes`

With the force option you can force the script to regenerate the thumbnails and the index.html file.

`--copyright="&copy; 2014"`

Let's you specify a copyright for the gallery. In default theme this is used in the footer.
