<?php

function buildMetabar($f) {
    include_once('src/UI/examples/MainControls/Prompts/NotificationCenter/nc_base.php');
    include_once('src/UI/examples/MainControls/Prompts/AwarenessTool/awt_base.php');


    $awt = buildAwarenessTool($f);
    $nc = buildNotificationCenter($f);

    $logo = $f->image()->responsive(
        "src/UI/examples/Image/HeaderIconLarge.svg",
        "Thumbnail Example"
    );

    return $f->layout()->metabar($logo)
        ->withElement($nc)
        ->withElement($awt);
}

function metabar() {
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
	return $renderer->render(buildMetabar($f));
}
