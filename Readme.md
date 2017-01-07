# Instructions

This is the source repository for the LabPal web site. The site uses the
[FantasticWindmill](https://sylvainhalle.github.io/FantasticWindmill) static
web site generator to generate the pages.

## How to build the site

Clone both this repository and the [labpal](https://github.com/liflab/labpal)
side by side in the same root folder, like this:

    /somefolder
      /labpal
      /labpal-site

In the `labpal-site`, go to the `FantasticWindmill` folder. To generate the
HTML files from the sources, type

    $ make

This will put all the static web site in the `public_html` folder.

To put the site "online", you need to copy the contents of this folder to the
`docs` folder of the `labpal` repository. Run:

    $ make github

This command will only work if the `labpal` repo is properly located relative
to `labpal-site`, as mentioned above.

## How to modify the site

The contents of the site are in the `content` folder. Markdown files are
automatically converted to HTML when building. The structre of this folder
is faithfully mirrored in `public_html`: if you create folders in `content`,
there will be the same folders in `public_html`, etc.

In addition to copying the HTML files to the `labpal` repository, don't forget
to commit/push your modifications to the sources in the `labpal-site` repo.

## Javadoc

This site does *not* contain the Javadoc generated from the LabPal sources.
To update the sources and push them online, go to the `labpal` repository and
run

    $ ant javadoc

This will regenerate the Javadoc ant put it in the `docs/doc` folder. Doing
a commit/push will update the Javadoc online.