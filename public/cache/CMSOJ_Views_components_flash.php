<?php class_exists('CMSOJ\Template') or exit; ?>
<?php 
$messages = \CMSOJ\Helpers\Flash::all();
\CMSOJ\Helpers\Flash::clear(); 
?>

<?php if (!empty($messages)): ?>
<div class="flash-container">

    <?php foreach ($messages as $type => $msg): ?>

        <div class="flash flash-<?= $type ?>">
            <span><?= $msg ?></span>
            <button type="button" class="flash-close" onclick="this.parentElement.remove()">×</button>
        </div>

    <?php endforeach; ?>

</div>
<?php endif; ?>
