<?php
use AndreasGlaser\Helpers\HtmlHelper;

?>

<div id="page-index">
    <p>This is a simple tool to delete all your Google Photos / Picasa Webalbums Photos</p>
    <?php if ($hasAccessToken): ?>
        <?php print HtmlHelper::a('/delete', 'Start Deletion'); ?>
    <?php else: ?>
        <?php print HtmlHelper::a($authUrl, 'Authenticate with Google'); ?>
    <?php endif; ?>
</div>