<?php
use AndreasGlaser\Helpers\HtmlHelper;

?>

<div id="page-id">

    <p>These albums will be deleted.</p>

    <table>
        <thead>
        <tr>
            <th>Title</th>
            <th>Published</th>
            <th>Updated</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($albums AS $album): ?>
            <tr>
                <td><?php print $album['title']; ?></td>
                <td><?php print $album['published']; ?></td>
                <td><?php print $album['updated']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>


    <?php print HtmlHelper::a('/delete?confirm=true', 'Confirm deletion'); ?>
</div>
