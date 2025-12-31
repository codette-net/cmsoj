<?php
require 'config.php';

try {
  $pdo = new PDO('mysql:host=' . db_host . ';dbname=' . db_name . ';charset=' . db_charset, db_user, db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
  // If there is an error with the connection, stop the script and display the error.
  exit('Failed to connect to database: ' . $exception->getMessage());
}

$lang = isset($_GET['lang']) && $_GET['lang'] === 'pt' ? 'pt' : 'en';

// Fetch sections
$stmt = $pdo->query("
    SELECT * FROM menu_sections
    WHERE is_active = 1
    ORDER BY parent_id ASC, sort_order ASC, id ASC
");
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tree = [];
foreach ($sections as $s) {
  if (empty($s['parent_id'])) {
    $tree[$s['id']] = $s;
    $tree[$s['id']]['children'] = [];
  } else {
    $tree[$s['parent_id']]['children'][] = $s;
  }
}

// Fetch items
$stmt = $pdo->query("
    SELECT * FROM menu_items
    WHERE is_active = 1
    ORDER BY section_id ASC, sort_order ASC, id ASC
");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$items_by_section = [];
foreach ($items as $i) {
  $items_by_section[$i['section_id']][] = $i;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
  <meta charset="utf-8">
  <title>Menu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: system-ui, sans-serif;
      background: #fff;
      margin: 0;
      padding: 0;
      color: #222;
    }

    .wrapper {
      max-width: 900px;
      margin: 0 auto;
      padding: 20px;
    }

    .menu-section {
      margin-bottom: 40px;
    }

    .menu-section h2 {
      font-size: 1.8rem;
      border-bottom: 2px solid #333;
      padding-bottom: 5px;
      margin-bottom: 10px;
    }

    .menu-subsection h3 {
      font-size: 1.3rem;
      margin-top: 25px;
      margin-bottom: 10px;
      border-left: 3px solid #999;
      padding-left: 10px;
      color: #444;
    }

    .menu-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
    }

    .menu-table th,
    .menu-table td {
      padding: 6px 8px;
      text-align: left;
      vertical-align: top;
    }

    .menu-table th[colspan] {
      text-align: left;
      font-weight: bold;
      padding-top: 10px;
      border-bottom: 1px solid #ccc;
    }

    .menu-table td.price {
      text-align: right;
      white-space: nowrap;
      width: 10%;
    }

    .menu-table td:first-child {
      width: 70%;
    }

    .menu-table small em {
      color: #666;
      font-size: 0.9em;
    }

    .lang-switch {
      text-align: center;
      margin: 20px 0;
    }

    .lang-switch a {
      padding: 6px 12px;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin: 0 5px;
      text-decoration: none;
      color: #333;
    }

    .lang-switch a.active {
      background: #333;
      color: #fff;
    }
  </style>
</head>

<body>
  <div class="lang-switch">
    <a href="?lang=en" class="<?= $lang == 'en' ? 'active' : '' ?>">English</a>
    <a href="?lang=pt" class="<?= $lang == 'pt' ? 'active' : '' ?>">Português</a>
  </div>
  <div class="wrapper">

    <?php
  function render_items_table($section_id, $items, $lang) {
    if (empty($items[$section_id])) return;
    echo "<table class='menu-table'>\n";
    $thead_open = false;

    foreach ($items[$section_id] as $it) {
        $type = $it['display_type'] ?: 'item';
        $name = trim($it["name_$lang"] ?: $it["name_en"]);
        $desc = trim($it["description_$lang"] ?: $it["description_en"]);
        $p1 = trim($it['price_1']);
        $p2 = trim($it['price_2']);
        $u1 = trim($it['unit_1_label']);
        $u2 = trim($it['unit_2_label']);

        // ========== HEADERS ==========
        if ($type === 'thead') {
            if ($thead_open) echo "</tbody>\n"; // close previous body
            echo "<thead><tr>";
            echo "<th></th><th>".htmlspecialchars($u1)."</th><th>".htmlspecialchars($u2)."</th>";
            echo "</tr></thead><tbody>";
            $thead_open = true;
            continue;
        }

        if ($type === 'th') {
            echo "<tr><th>".htmlspecialchars($name)."</th>";
            echo "<th>".htmlspecialchars($u1)."</th><th>".htmlspecialchars($u2)."</th></tr>";
            continue;
        }

        if ($type === 'divider') {
            echo "<tr class='tr-divider'><th colspan='3'>".htmlspecialchars($name)."</th></tr>";
            continue;
        }

        // ========== NORMAL ITEM ==========
        echo "<tr>";
        echo "<td>";

        // --- NAME & DESCRIPTION RENDER LOGIC ---
        if ($name && $desc) {
            // ensure proper spacing and parenthesis format
            $desc_fmt = $desc;
            if ($desc[0] !== '(' && $desc[0] !== '[') {
                $desc_fmt = '(' . $desc . ')';
            }
            echo htmlspecialchars($name);
            echo " <small><em>" . htmlspecialchars($desc_fmt) . "</em></small>";
        } elseif ($name) {
            echo htmlspecialchars($name);
        } elseif ($desc) {
            echo "<small><em>" . htmlspecialchars($desc) . "</em></small>";
        } else {
            echo "&nbsp;"; // empty row fallback
        }

        echo "</td>";

        // --- PRICE CELLS ---
        if ($p1 && $p2) {
            echo "<td class='price'>".htmlspecialchars($p1)."</td>";
            echo "<td class='price'>".htmlspecialchars($p2)."</td>";
        } elseif (!$p1 && $p2) {
            echo "<td></td><td class='price'>".htmlspecialchars($p2)."</td>";
        } elseif ($p1 && !$p2) {
            echo "<td class='price'>".htmlspecialchars($p1)."</td><td></td>";
        } else {
            echo "<td></td><td></td>";
        }

        echo "</tr>\n";
    }

    if ($thead_open) echo "</tbody>\n";
    echo "</table>\n";
}

    ?>

    <?php foreach ($tree as $main): ?>
      <div class="menu-section">
        <h2><?= htmlspecialchars($main["name_$lang"] ?: $main["name_en"]) ?></h2>
        <?php if ($main["description_$lang"]): ?>
          <p><?= nl2br(htmlspecialchars($main["description_$lang"])) ?></p>
        <?php endif; ?>

        <?php if (!empty($main['children'])): ?>
          <?php foreach ($main['children'] as $sub): ?>
            <div class="menu-subsection">
              <h3><?= htmlspecialchars($sub["name_$lang"] ?: $sub["name_en"]) ?></h3>
              <?php if ($sub["description_$lang"]): ?>
                <p><?= nl2br(htmlspecialchars($sub["description_$lang"])) ?></p>
              <?php endif; ?>
              <?php render_items_table($sub['id'], $items_by_section, $lang); ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <?php render_items_table($main['id'], $items_by_section, $lang); ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

  </div>
</body>

</html>