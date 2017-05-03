<ul>
    <?php

    foreach ($sitemap->pages as $oPage) {

        if (!empty($oPage->breadcrumbs) && count($oPage->breadcrumbs) > 1) {

            echo '<li>';

            //	Breadcrumbs, fancy it up a little
            $aCrumbs = $oPage->breadcrumbs;
            array_pop($aCrumbs);

            foreach ($aCrumbs as $oCrumb) {
                echo '<span class="crumb">' . $oCrumb->title . '</span> ';
            }

            echo anchor($oPage->location, $oPage->title);
            echo '</li>';

        } else {

            //	No breadcrumbs, just use basic details
            echo '<li class="top-level">';
            echo anchor($oPage->location, $oPage->title);
            echo '</li>';
        }
    }

    ?>
</ul>
