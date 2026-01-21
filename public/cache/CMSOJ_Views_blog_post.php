<?php class_exists('CMSOJ\Template') or exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Manezinho | <?php echo $post->title; ?> </title>
  

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

<?php if ($post->description): ?>
  <meta name="description" content="<?php echo $post->description; ?>">
<?php endif ?>
<?php if ($post->keywords): ?>
  <meta name="keywords" content="<?php echo ($post->keywords); ?>">
<?php endif ?>

  
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
  <article>
    <header>
      <h1><?php echo $post->title; ?></h1>
      <?php if ($post->published_at): ?>
        <p><small>Published: <?php echo $post->published_at; ?></small></p>
      <?php endif ?>
      <?php if ($post->introduction): ?>
        <div class="intro">
          <p><?php echo $post->introduction; ?></p>
        </div>
      <?php endif ?>
    </header>

    <section class="richtext">
      <?php echo $post->content; ?>
    </section>
  </article>
</main>


  
<a id="scrolltop" href="#" title="Back to top" style="display: none;"></a>

  
<script src='<?php echo \CMSOJ\Template::asset("/assets/js/main.js"); ?>''></script>
<script src='<?php echo \CMSOJ\Template::asset("/assets/js/reservation.js"); ?>''></script>

</body>

</html>














