<?php class_exists('CMSOJ\Template') or exit; ?>
<form method="get" class="admin-search">
  <label for="q" class="visually-hidden">Search</label>

  <input
    type="search"
    id="q"
    name="q"
    value="<?= htmlspecialchars($query['q'] ?? '') ?>"
    placeholder="Search…"
  >

  <?php
    foreach (['sort', 'dir'] as $key) {
        if (!empty($query[$key])) {
            echo '<input type="hidden" name="'.$key.'" value="'.htmlspecialchars($query[$key]).'">';
        }
    }
  ?>

  <button type="submit">Search</button>

  <?php if (!empty($query['q'])): ?>
    <a href="<?= strtok($_SERVER['REQUEST_URI'], '?') ?>" class="reset">Reset</a>
  <?php endif; ?>
</form>

<?php if (!empty($meta)): ?>
  <p class="search-info">
    <?php if (!empty($query['q'])): ?>
    <!-- check if there are more than 1 page , and calculate how many result of total results are being shown -->
     <?php if ($meta['pages'] > 1): ?>
      Showing <strong><?= ($meta['page'] - 1) * $meta['per_page'] + 1 ?></strong> to <strong><?= min($meta['page'] * $meta['per_page'], $meta['totalFiltered']) ?></strong> of <strong><?= $meta['totalFiltered'] ?></strong> results for "<em><?= htmlspecialchars($query['q']) ?></em>"
      <?php else: ?>
      Showing <strong><?= $meta['totalFiltered'] ?></strong> results for "<em><?= htmlspecialchars($query['q']) ?></em>"
      <?php endif; ?>

    <?php else: ?>
      Showing <strong><?= $meta['total'] ?></strong> total results
    <?php endif; ?>
  </p>
<?php endif; ?>
