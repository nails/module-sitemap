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

if (userHasPermission('admin:sitemap:sitemap:generate')) {
    echo '<hr>';
    echo anchor(
        'admin/sitemap/sitemap/generate',
        'Re-generate Sitemap',
        'class="btn btn-primary"'
    );
}

