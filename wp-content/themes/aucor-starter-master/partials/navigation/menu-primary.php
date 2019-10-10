<?php
/**
 * Template part: Primary menu
 *
 * @package aucor_starter
 */

?>

<nav id="primary-navigation" class="primary-navigation" aria-label="<?php ask_e('Menu: Primary Menu'); ?>" itemscope itemtype="http://schema.org/SiteNavigationElement">

  <?php
  wp_nav_menu(array(
    'theme_location' => 'primary',
    'container'      => '',
    'menu_id'        => 'primary-navigation__items',
    'menu_class'     => 'primary-navigation__items',
    'link_before'    => '',
    'link_after'     => '',
    'fallback_cb'    => '',
  ));
  ?>

</nav><!-- #primary-navigation -->
