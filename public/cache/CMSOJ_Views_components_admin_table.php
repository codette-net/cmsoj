<?php class_exists('CMSOJ\Template') or exit; ?>
<?php
$hasBulk = isset($bulk) && !empty($bulk['actions']);

?>

<?php if ($hasBulk): ?>

<form method="post" action="<?= htmlspecialchars($bulk['endpoint']) ?>" class="form-table">
  <input type="hidden" name="_csrf" value="<?= \CMSOJ\Helpers\Csrf::token() ?>">
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <?php if ($hasBulk): ?>
        <th>
          <input type="checkbox" aria-label="Select all rows"
            onclick="document.querySelectorAll('input[name=&quot;ids[]&quot;]').forEach(cb => cb.checked = this.checked)">
        </th>
        <?php endif; ?>
        <?php foreach ($headers as $key => $label): ?>
        <th>
          <?php if (in_array($key, $sortable, true)): ?>

          <?php
        $isActive = ($query['sort'] ?? '') === $key;
        $dir = $isActive && ($query['dir'] ?? 'asc') === 'asc'
            ? 'desc'
            : 'asc';

        $icon = '';
        if ($isActive) {
          $icon = ($query['dir'] ?? 'asc') === 'asc' ? ' ↑' : ' ↓';
        }
      ?>

          <a href="?<?= http_build_query(array_merge($query, [
          'sort' => $key,
          'dir'  => $dir,
          'page' => 1
      ])) ?>">
            <?= $label . $icon ?>
          </a>

          <?php else: ?>
          <?= $label ?>
          <?php endif; ?>
        </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
      <tr data-id="<?= (int)$row['id'] ?>">

        <?php if ($hasBulk): ?>
        <td>
          <input type="checkbox" name="ids[]" value="<?= (int)$row['id'] ?>"
            aria-label="Select row <?= (int)$row['id'] ?>">
        </td>
        <?php endif; ?>

        <?php foreach ($row['cells'] as $cell): ?>
        <td>
          <?= $cell ?>
        </td>
        <?php endforeach; ?>

      </tr>
      <?php endforeach; ?>
    </tbody>

  </table>

  <?php if ($hasBulk): ?>
  <div class="bulk-actions">
    <label for="bulk-action" class="visually-hidden">Bulk actions</label>

    <select id="bulk-action" name="action" required>
      <option value="">Bulk actions</option>
      <?php foreach ($bulk['actions'] as $key => $action): ?>
      <option value="<?= htmlspecialchars($key) ?>">
        <?= htmlspecialchars($action['label']) ?>
      </option>
      <?php endforeach; ?>
    </select>

    <button type="submit">Apply</button>
  </div>
</form>
<?php endif; ?>