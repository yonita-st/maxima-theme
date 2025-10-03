<?php
/* ============================================================
   БЛОК 2.0 – Минимален шаблон (index.php)
   ------------------------------------------------------------
   Основен fallback шаблон: извежда съдържанието на страницата.
   ============================================================ */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php bloginfo('name'); ?></title>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

  <?php
  if ( have_posts() ) {
      while ( have_posts() ) {
          the_post();
          the_content();
      }
  } else {
      echo '<p>' . __( 'Няма съдържание за показване.', 'maxima' ) . '</p>';
  }
  ?>

  <?php wp_footer(); ?>
</body>
</html>
