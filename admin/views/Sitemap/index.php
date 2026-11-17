<table>
    <thead>
        <tr>
            <th>URL</th>
            <th>Last Modified</th>
            <th>Change Frequency</th>
            <th>Priority</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($aUrls)) {
            foreach ($aUrls as $oUrl) {
                ?>
                <tr>
                    <td><?=anchor($oUrl->loc, null, 'target="_blank"')?></td>
                    <td><?=$oUrl->lastmod?></td>
                    <td><?=$oUrl->changefreq?></td>
                    <td><?=$oUrl->priority?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="4" class="no-data">
                    No URLs in Sitemap
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<?php

if (userHasPermission(\Nails\SiteMap\Admin\Permission\Generate::class)) {
    echo '<hr>';
    echo anchor(
        \Nails\SiteMap\Admin\Controller\Sitemap::url('generate'),
        'Re-generate Sitemap',
        'class="btn btn-primary"'
    );
}

