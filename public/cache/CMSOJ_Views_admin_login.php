<?php class_exists('CMSOJ\Template') or exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title> <?php echo $title; ?>  | CMSOJ </title>
  
<link rel="stylesheet" href='<?= \CMSOJ\Template::asset("/assets/css/classless.css") ?>' />
<link rel="stylesheet" href='<?= \CMSOJ\Template::asset("/assets/css/admin_new.css") ?>' />

<noscript>
  <link rel="stylesheet" href='<?php echo \CMSOJ\Template::asset("/assets/css/noscript.css"); ?>' />
</noscript>

</head>

<body class="<?php echo $body_class ?? 'admin-main'; ?>">
  <?php echo CMSOJ\Template::renderComponent('CMSOJ/Views/components/flash.html', []); ?>

  

  

<form action="/admin/login" method="post" class="">
  <h2>Admin Login</h2>
  <input type="email" name="admin_email" placeholder="Email" required>
  <input type="password" name="admin_password" placeholder="Password" required>
  <input type="submit" value="Login">
  <?php if (!empty($_SESSION['login_error'])): ?>
  <div class="error-msg">
    <?= htmlspecialchars($_SESSION['login_error']) ?>
  </div>
  <?php unset($_SESSION['login_error']); ?>
  <?php endif; ?>

  <p>
    <a href="/admin/forgot-password">Forgot Password?</a>
  </p>
</form>



  
<a id="scrolltop" ...></a>

  
<!-- JS includes -->
 <script>
setTimeout(() => {
  document.querySelectorAll('.flash').forEach(el => el.remove());
}, 3000);
</script>


</body> 
</html>










