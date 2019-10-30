<?php

add_action('rest_api_init', 'woobazaarApi');

function woobazaarApi() {
    register_rest_route('woobazaar/v1', 'search', array(
       'methods' => WP_REST_SERVER::READABLE,
        'callback' => 'woobazaarApiResults'
    ));
}

function woobazaarApiResults($fonebay) {
    $woobazaarQuery = new WP_Query(array(
        'post_type' => array('product','post', 'page', 'professor', 'program', 'event', 'campus'),
        's' => sanitize_text_field($fonebay['term'])
    ));
    
    $results = array(
        'product' => array(),
        'generalInfo' => array(),
        'professors' => array(),
        'programs' => array(),
        'events' => array(),
        'campuses' => array()
    );
    
    while ($woobazaarQuery->have_posts()) {
        $woobazaarQuery->the_post();
        
        // Products
        if(get_post_type() == 'product') {
            array_push($results['product'], array(
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'image' => get_the_post_thumbnail(0)
        ));
        }
        
        // Posts and pages
        if(get_post_type() == 'post' OR get_post_type() == 'page') {
            array_push($results['generalInfo'], array(
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'postType' => get_post_type(),
            'authorName' => get_the_author()
        ));
        }
        
        // Professors
        if(get_post_type() == 'professor') {
            array_push($results['professors'], array(
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'image' => get_the_post_thumbnail(0, 'professorLandscape')
        ));
        }
        
        // Programs
        if(get_post_type() == 'program') {
            $relatedCampuses = get_field('related_campus');
            
            if($relatedCampuses) {
                foreach ($relatedCampuses as $campus) {
                    array_push($results['campuses'], array(
                       'title' => get_the_title($campus),
                        'permalink' => get_the_permalink($campus)
                    ));
                }
            }   
            
            array_push($results['programs'], array(
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'id' => get_the_id()
        ));
        }
        
        // Events
        if (get_post_type() == 'event') {
      $eventDate = new DateTime(get_field('event_date'));
      $description = null;
      if (has_excerpt()) {
        $description = get_the_excerpt();
      } else {
        $description = wp_trim_words(get_the_content(), 18);
      }
      
      array_push($results['events'], array(
        'title' => get_the_title(),
        'permalink' => get_the_permalink(),
        'month' => $eventDate->format('M'),
        'day' => $eventDate->format('d'),
        'description' => $description
      ));
    }
        
        // Campuses
        if(get_post_type() == 'campus') {
            array_push($results['campuses'], array(
            'title' => get_the_title(),
            'permalink' => get_the_permalink()
        ));
        }
    }
    
    if($results['programs']) {
        $programsMetaQuery = array('relation' => 'OR');
    
    foreach ($results['programs'] as $item) {
        array_push($programsMetaQuery, array(
            'key' => 'related_programs',
            'compare' => 'LIKE',
            'value' => '"' . $item['id'] . '"'
              ));
    }
    
    $programRelationshipQuery = new WP_Query(array(
       'post_type' => array('professor', 'event'),
        'meta_query' => $programsMetaQuery
    ));
    
    while ($programRelationshipQuery->have_posts()) {
        $programRelationshipQuery->the_post();
        
      if (get_post_type() == 'event') {
      $eventDate = new DateTime(get_field('event_date'));
      $description = null;
      if (has_excerpt()) {
        $description = get_the_excerpt();
      } else {
        $description = wp_trim_words(get_the_content(), 18);
      }
      
      array_push($results['events'], array(
        'title' => get_the_title(),
        'permalink' => get_the_permalink(),
        'month' => $eventDate->format('M'),
        'day' => $eventDate->format('d'),
        'description' => $description
      ));
    }
        
        if(get_post_type() == 'professor') {
            array_push($results['professors'], array(
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'image' => get_the_post_thumbnail(0, 'professorLandscape')
        ));
        }
    }
    
    $results['professors'] = array_values(array_unique($results['professors'], SORT_REGULAR));
    $results['events'] = array_values(array_unique($results['events'], SORT_REGULAR));
    }
    
    return $results;
}