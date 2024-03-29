<?php

require get_theme_file_path('/includes/woobazaar-like.php');
require get_theme_file_path('/includes/woobazaar-route.php');

function woobazaar_custom_rest() {
    register_rest_field('post', 'authorName', array(
       'get_callback' => function() {return get_the_author();} 
    ));
    
    register_rest_field('note', 'userNoteCount', array(
       'get_callback' => function() {return count_user_posts(get_current_user_id(), 'note');} 
    ));
    register_rest_field('product', 'thisifForTODO', array(
        'get_callback' => function() {return 'This is test TODO';}
    ));
}

add_action('rest_api_init', 'woobazaar_custom_rest');

function pageBanner($args = NULL) {
  
  if (!$args['title']) {
    $args['title'] = get_the_title();
  }

  if (!$args['subtitle']) {
    $args['subtitle'] = get_field('page_banner_subtitle');
  }

  if (!$args['photo']) {
    if (get_field('page_banner_background_image')) {
      $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
    } else {
      $args['photo'] = get_theme_file_uri('/images/woobazaar.jpg');
    }
  }

  ?>
  <div class="page-banner">
    <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
    <div class="page-banner__content container container--narrow">
      <h1 class="page-banner__title"><?php echo $args['title'] ?></h1>
      <div class="page-banner__intro">
        <p><?php echo $args['subtitle']; ?></p>
      </div>
    </div>  
  </div>
<?php

    }
function woobazaar_scripts() {
    wp_enqueue_script('googleMap', '//maps.googleapis.com/maps/api/js?key=AIzaSyDuXoEMRUhJnPU06gUv_jsfo2DIsCxGag8', NULL, microtime(), true);
    wp_enqueue_script('main-woobazaar-js', get_theme_file_uri('/js/scripts-bundled.js'), NULL, microtime(), true);
    wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('woobazaar-main-styles', get_stylesheet_uri(), NULL, microtime());
    wp_localize_script('main-woobazaar-js', 'woobazaarData', array(
        'root_url' => get_site_url(),
        'nonce' => wp_create_nonce('wp_rest')
    ));
}

add_action('wp_enqueue_scripts', 'woobazaar_scripts');

function woobazaar_features() {
    register_nav_menu( 'woobazaarHeaderMenu', 'Woobazaar Header Menu' );
    register_nav_menu( 'woobazaarFooterMenuOne', 'Woobazaar Footer Menu One' );
    register_nav_menu( 'woobazaarFooterMenuTwo', 'Woobazaar Footer Menu Two' );
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('professorLandscape', 400, 260, true);
    add_image_size('professorPortrait', 480, 650, true);
    add_image_size('pageBanner', 1500, 350, true);
}

add_action('after_setup_theme', 'woobazaar_features');

function woobazaar_adjust_queries($query) {
    
    if(!is_admin() AND is_post_type_archive('campus') AND $query->is_main_query()) {
        $query->set('posts_per_page', -1);
    }
    
    if(!is_admin() AND is_post_type_archive('program') AND $query->is_main_query()) {
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', -1);
    }
    if (!is_admin() AND is_post_type_archive('event') AND $query->is_main_query()) {
        $today = date('Ymd');
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
        $query->set('meta_query', array(
              array(
                'key' => 'event_date',
                'compare' => '>=',
                'value' => $today,
                'type' => 'numeric'
            )
        ));

    }

}

add_action( 'pre_get_posts', 'woobazaar_adjust_queries' );

function woobazaarMapKey($api) {
  $api['key'] = 'AIzaSyDuXoEMRUhJnPU06gUv_jsfo2DIsCxGag8';
  return $api;
}

add_filter('acf/fields/google_map/api', 'woobazaarMapKey');

// Redirect subscriber account out of admin adn onto homepage
add_action('admin_init', 'redirectSubToFrontend');

function redirectSubToFrontend() {

  $ourCurrentUser = wp_get_current_user();
  if(count($ourCurrentUser->roles) == 1 AND $ourCurrentUser->roles[0] == 'subscriber') {
    wp_redirect(site_url('/'));
    exit;

  }
}

add_action('wp_loaded', 'noSubAdminBar');

function noSubAdminBar() {

  $ourCurrentUser = wp_get_current_user();
  if(count($ourCurrentUser->roles) == 1 AND $ourCurrentUser->roles[0] == 'subscriber') {
    show_admin_bar(false);

  }
}

// Customize Login Screen
add_filter('login_headerurl', 'woobazaarHeaderUrl');

function woobazaarHeaderUrl() {
  return esc_url(site_url('/'));
}

add_action('login_enqueue_scripts', 'woobazaarLoginCSS');

function woobazaarLoginCSS() {
  wp_enqueue_style('woobazaar-main-styles', get_stylesheet_uri());
  wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
}

add_filter('login_headertitle', 'woobazaarLoginTitle');

function woobazaarLoginTitle() {
  return get_bloginfo('name');
}

// Force note posts to be private
add_filter('wp_insert_post_data', 'makeNotePrivate', 10, 2);

function makeNotePrivate($data, $postarr) {
  if ($data['post_type'] == 'note') {
    if(count_user_posts(get_current_user_id(), 'note') > 4 AND !$postarr['ID']) {
      die("You have reached your note limit.");
    }

    $data['post_content'] = sanitize_textarea_field($data['post_content']);
    $data['post_title'] = sanitize_text_field($data['post_title']);
  }

  if($data['post_type'] == 'note' AND $data['post_status'] != 'trash') {
    $data['post_status'] = "private";
  }
  
  return $data;
}