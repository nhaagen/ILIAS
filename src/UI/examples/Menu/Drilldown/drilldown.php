<?php
function drilldown()
{

    /**
        0 Tier des Jahres
        1    Schweiz
        1.1        Bachflohkrebs
        1.1.1        Bachflohkrebs
        1.2        Wildkatze
        1.2.1            gewöhnliche Wildkatze
        1.2.2            große Wildkatze
        2    Deutschland
        2.1        Fischotter
        2.2        Maulwurf
        2.3        Reh
    */


    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $ico = $f->symbol()->icon()->standard('', '')->withSize('small')->withAbbreviation('+');
    $image = $f->image()->responsive("src/UI/examples/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
    $page = $f->modal()->lightboxImagePage($image, 'Mountains');
    $modal = $f->modal()->lightbox($page);
    $button = $f->button()->bulky($ico->withAbbreviation('>'), 'Modal', '')
        ->withOnClick($modal->getShowSignal());

    $uri = new \ILIAS\Data\URI('https://ilias.de');
    $link = $f->link()->bulky($ico->withAbbreviation('>'), 'Link', $uri);

    $items = [
        $f->menu()->sub('Schweiz (1)', [
            $f->menu()->sub('Bachflohkrebs (1.1)', [$button, $link])
            ->withInitiallyActive(),

            $f->menu()->sub('Wildkatze (1.2)', [
                $f->menu()->sub('gewöhnliche Wildkatze (1.2.1)', [$button, $link]),
                $f->menu()->sub('große Wildkatze (1.2.2)', [$button, $link])
            ]),
            $button,
            $link
        ]),

        $f->menu()->sub('Deutschland (2)', [
            $f->menu()->sub('Fischotter (2.1)', [$button, $link]),
            $f->menu()->sub('Maulwurf (2.2)', [$button, $link]),
            $f->menu()->sub('Reh (2.3)', [$button, $link])
        ])
    ];

    $dd = $f->menu()->drilldown('Tier des Jahres (0)', $items);

    return $renderer->render([
        $dd,
        $modal
    ]);
}


function toBulky(string $label) : \ILIAS\UI\Component\Button\Bulky
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $ico = $f->symbol()->icon()->standard('', '')
        ->withSize('small')
        ->withAbbreviation('+');

    return $f->button()->bulky($ico, $label, '');
}
