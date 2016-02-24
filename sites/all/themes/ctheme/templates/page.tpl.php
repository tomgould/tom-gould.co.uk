<!-- Header. -->
<div id="header">
  <div id="header-inside">
    <div id="header-inside-left">
      
      <?php if (!empty($logo)): ?>
      <a href="<?php print check_url($front_page); ?>" title="<?php print t('Home') . " | " . variable_get('site_name'); ?>"><img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" /></a>
      <?php endif; ?>
   
      <?php if ($site_name || $site_slogan): ?>
      <div class="clearfix">
      <?php if ($site_name): ?>
      <span id="site-name"><a href="<?php print check_url($front_page); ?>" title="<?php print t('Home') . " | " . variable_get('site_name'); ?>"><?php print $site_name; ?></a></span>
      <?php endif; ?>
      <?php if ($site_slogan): ?>
      <span id="slogan"><?php print $site_slogan; ?></span>
      <?php endif; ?>
      </div><!-- /site-name-wrapper -->
      <?php endif; ?>

    </div>
  </div><!-- EOF: #header-inside -->
</div><!-- EOF: #header -->

<!-- Header Menu. -->
<div id="header-menu">
  <div id="header-menu-inside">
    <?php
    if (module_exists('i18n')) {
      $main_menu_tree = i18n_menu_translated_tree(variable_get('menu_main_links_source', 'main-menu'));
    }
    else {
      $main_menu_tree = caching_cache_menu_tree(variable_get('menu_main_links_source', 'main-menu'));
}
    print drupal_render($main_menu_tree);
    ?>
    <div class="search-area">
      <?php print caching_cache_render($page['search_area']); ?>
    </div>
  </div><!-- EOF: #header-menu-inside -->
</div><!-- EOF: #header-menu -->

<!-- Banner. -->
<div id="banner">
	<?php print caching_cache_render($page['banner']); ?>
  <?php if (theme_get_setting('slideshow_display','ctheme')): ?>
  <?php if ($is_front): ?>
    <?php print ($slideshow); ?>
  
  <?php endif; ?>
  
	<?php endif; ?>  

</div><!-- EOF: #banner -->


<!-- Content. -->
<div id="content">

  <div id="content-inside" class="inside">
  
    <div id="main">
      
      <?php if (theme_get_setting('breadcrumb_display','ctheme')): print $breadcrumb; endif; ?>
      
      <?php if ($page['highlighted']): ?><div id="highlighted"><?php print caching_cache_render($page['highlighted']); ?></div><?php endif; ?>
     
      <?php if ($messages): ?>
      <div id="console" class="clearfix">
      <?php print $messages; ?>
      </div>
      <?php endif; ?>
   
      <?php if ($page['help']): ?>
      <div id="help">
      <?php print caching_cache_render($page['help']); ?>
      </div>
      <?php endif; ?>
      
      <?php if ($action_links): ?>
      <ul class="action-links">
      <?php print caching_cache_render($action_links); ?>
      </ul>
      <?php endif; ?>
      
			<?php print caching_cache_render($title_prefix); ?>
      <?php if ($title): ?>
      <h1><?php print $title ?></h1>
      <?php endif; ?>
      <?php print caching_cache_render($title_suffix); ?>
      
      <?php if ($tabs): ?><?php print caching_cache_render($tabs); ?><?php endif; ?>
      
      <?php print caching_cache_render($page['content']); ?>
      
      <?php print $feed_icons; ?>
      
    </div><!-- EOF: #main -->
    
    <div id="sidebar">
       
      <?php print caching_cache_render($page['sidebar_first']); ?>

    </div><!-- EOF: #sidebar -->

  </div><!-- EOF: #content-inside -->

</div><!-- EOF: #content -->

<!-- Footer -->  
<div id="footer">

  <div id="footer-inside">
  
    <div class="footer-area first">
    <?php print caching_cache_render($page['footer_first']); ?>
    </div><!-- EOF: .footer-area -->
    
    <div class="footer-area second">
    <?php print caching_cache_render($page['footer_second']); ?>
    </div><!-- EOF: .footer-area -->
    
    <div class="footer-area third">
    <?php print caching_cache_render($page['footer_third']); ?>
    </div><!-- EOF: .footer-area -->
     
  </div><!-- EOF: #footer-inside -->

</div><!-- EOF: #footer -->

<!-- Footer -->  
<div id="footer-bottom">

  <div id="footer-bottom-inside">
  
  	<div id="footer-bottom-left">
    
      <?php print theme('links__system_secondary_menu', array('links' => $secondary_menu, 'attributes' => array('class' => array('secondary-menu', 'links', 'clearfix')))); ?>
      
      <?php print caching_cache_render($page['footer']); ?>
      
    </div>
    
    <div id="footer-bottom-right">
    
    	<?php print caching_cache_render($page['footer_bottom_right']); ?>
    
    </div><!-- EOF: #footer-bottom-right -->
     
  </div><!-- EOF: #footer-bottom-inside -->

</div><!-- EOF: #footer -->