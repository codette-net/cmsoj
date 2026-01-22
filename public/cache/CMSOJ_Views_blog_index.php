<?php class_exists('CMSOJ\Template') or exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Manezinho | Blog </title>
  
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

  
<link rel="stylesheet" href='<?php echo \CMSOJ\Template::asset("/assets/css/main.css"); ?>' />
<link rel="stylesheet" href='<?php echo \CMSOJ\Template::asset("/assets/css/components.css"); ?>'>
<link rel="stylesheet" href='<?php echo \CMSOJ\Template::asset("/assets/css/style.css"); ?>'>
<noscript>
  <link rel="stylesheet" href='<?php echo \CMSOJ\Template::asset("/assets/css/noscript.css"); ?>' />
</noscript>

  <!-- here is the end of head  -->
</head>


<body class="">
  

  
<main class="container">
  <header>
    <h1>Blog</h1>
  </header>


  <section aria-label="Posts">
    <h2>Latest posts</h2>

    <?php if ($posts): ?>
      <ul class="post-list">
        <?php foreach ($posts as $post): ?>
          <li class="post-item">
            <a href="/blog/post/<?php echo $post['slug']; ?>">
              <h3><?php echo htmlentities($post['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <?php if ($post['published_at']): ?>
                <p><small>Published: <?php echo $post['published_at']; ?></small></p>
              <?php endif ?>
              <?php if ($post['introduction']): ?>
                <p><?php echo $post['introduction']; ?></p>
              <?php endif ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No posts yet.</p>
    <?php endif ?>
  </section>
</main>


  
<a id="scrolltop" href="#" title="Back to top" style="display: none;"></a>

  
<script src='<?php echo \CMSOJ\Template::asset("/assets/js/main.js"); ?>''></script>
<script src='<?php echo \CMSOJ\Template::asset("/assets/js/reservation.js"); ?>''></script>

</body>

</html>












