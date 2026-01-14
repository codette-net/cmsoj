<?php class_exists('CMSOJ\Template') or exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Manezinho |  <?php echo $title; ?>  </title>
  
  <meta name="description" content="Welcome to Art Restaurant Manezinho, a unique dining experience in São Jorge, Azores. Enjoy exquisite cuisine in an artistic setting. Book your table now!">

  
<link rel="stylesheet" href='<?php echo \CMSOJ\Template::asset("/assets/css/main.css"); ?>' />
<link rel="stylesheet" href='<?php echo \CMSOJ\Template::asset("/assets/css/components.css"); ?>'>
<link rel="stylesheet" href='<?php echo \CMSOJ\Template::asset("/assets/css/style.css"); ?>'>
<noscript>
  <link rel="stylesheet" href='<?php echo \CMSOJ\Template::asset("/assets/css/noscript.css"); ?>' />
</noscript>

  <!-- here is the end of head  -->
</head>


<body class="">
  

  

<?php \CMSOJ\Template::partial('CMSOJ/Views/partials/nav.html'); ?>
<h1>CMSOJ</h1>



  
<a id="scrolltop" href="#" title="Back to top" style="display: none;"></a>

  

<script src='<?php echo \CMSOJ\Template::asset("/assets/js/main.js"); ?>''></script>
<script src='<?php echo \CMSOJ\Template::asset("/assets/js/reservation.js"); ?>''></script>

<script src="/assets/js/imgscroller.js"></script>
<script src="/assets/js/wordslider.js"></script>

</body>

</html>















<?php \CMSOJ\Template::partial('footer'); ?>  



