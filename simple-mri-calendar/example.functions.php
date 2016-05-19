<?php
#  //---------------------------------------------------------------
#  function mri_calendar_event($date, $event){
#    $date = new DateTime();
#    // $date->setTimezone(new DateTimeZone(SimpleMRICalendar::$timezone));
#    $date->setTimestamp(get_field('date', $event->ID));
#    $production = get_field('production', $event->ID);
#    if(is_string($production)) $production = get_post($production);
#    $ticket_url = get_field('ticket_url', $event->ID);
#    $html = '';
#    $html .= '<div class="mri-calendar-production-title">';
#    $html .= '<a href="'.get_permalink($production).'"><strong>'.$event->post_title.'</strong></a><br/>';
#    $html .= '<div>'.$date->format('g:i a').'</div>';
#    if($cost = get_field('cost', $event->ID)){
#      $html .= '<div>'.$cost.'</div>';
#    }
#    $html .= '</div>';
#
#    if(get_field('sold_out', $event->ID)){
#      $html .= '<p class="uppercase margin-top-one"><strong>Sold Out</strong></p>';
#    }else{
#      $today = new DateTime();
#      $today->setTimezone(new DateTimeZone(SimpleMRICalendar::$timezone));
#
#      if($today->format('YmdGi') < $date->format('YmdGi')){
#        $html .= '<div class="fancy-button-wrapper"><a href="'.$ticket_url.'" class="fancy-button" target="_blank">Get Tickets</a></div>';
#      }
#    }
#
#    return $html;
#  }
#  //---------------------------------------------------------------
#  function simple_mri_calendar_filter(){
#    $productions = get_posts('post_type=production&posts_per_page=-1');
#    $production = ($_REQUEST['prod'] != '' ? get_post($_REQUEST['prod']) : false);
#
#    $html = '';
#    $html .= '<div id="simple-mri-calendar-filter">';
#
#    $html .= '<div class="uppercase clear">';
#    $html .= '<strong>Select a show</strong>';
#    $html .= simple_mri_calendar_filter_no_touch($production, $productions);
#    $html .= simple_mri_calendar_filter_touch($production, $productions);
#    $html .= '<hr class="margin-top-one margin-bottom-one"/>';
#    $html .= '</div>';
#
#    $html .= simple_mri_calendar_filter_toolbar($production);
#
#    $html .= simple_mri_calendar_filter_date_range($production, $productions);
#
#    $html .= '</div>';
#    return $html;
#  }
#  //---------------------------------------------------------------
#  function simple_mri_calendar_filter_toolbar($production){
#    $html = '';
#    if($production){
#      $html .= '<div class="uppercase clear"><strong>';
#      $html .= 'SHOWING ALL DATES FOR ' . $production->post_title . ' | ';
#      $html .= '<a href="'.remove_query_arg('prod').'">SHOW ALL UPCOMING EVENTS</a>';
#      $html .= '</strong>';
#      $html .= '</div>';
#    }
#    return $html;
#  }
#  //---------------------------------------------------------------
#  function simple_mri_calendar_filter_no_touch($production, $productions){
#    $html = '';
#    $html .= '<div class="dropdown-wrapper inline-block except-touch">';
#    if($production){
#      $html .= '<div class="current-production">'.$production->post_title.' <i class="fa fa-caret-down"></i></div>';
#    }else{
#      $html .= '<div class="current-production">Select a show <i class="fa fa-caret-down"></i></div>';
#    }
#    $html .= '<ul>';
#    foreach($productions as $prod){
#      $production_link = add_query_arg( 'prod', $prod->ID);
#      $html .= '<li><a href="'.$production_link.'">'.$prod->post_title.'</a></li>';
#    }
#    $html .= '</ul>';
#    $html .= '</div>';
#    return $html;
#  }
#  //---------------------------------------------------------------
#  function simple_mri_calendar_filter_touch($production, $productions){
#    $html = '';
#    $html .= '<select class="only-touch" id="mobile-select-a-show">';
#    $html .= '<option value="">Select A Show</option>';
#    foreach($productions as $prod){
#      $production_link = add_query_arg( 'prod', $prod->ID);
#      $html .= '<option value="'.$production_link.'">'.$prod->post_title.'</option>';
#    }
#    $html .= '</select>';
#    return $html;
#  }
#  //---------------------------------------------------------------
#  function simple_mri_calendar_filter_date_range($production, $productions){
#    $html = '';
#    if($production){
#      $events = get_posts(array(
#        'post_type'      => 'mri_event',
#        'posts_per_page' => -1,
#        'order'          => 'ASC',
#        'meta_key'       => 'date',
#        'orderby'        => 'meta_value',
#        'meta_query' => array(
#          array(
#            'key'     => 'production',
#            'value'   => $production->ID
#          )
#        )
#      ));
#
#      $first_event = $events[0];
#      $last_event  = end($events);
#      $date        = new DateTime();
#      $date->setTimezone(new DateTimeZone(SimpleMRICalendar::$timezone));
#
#      $first_event_date = clone $date;
#      $first_event_date->setTimestamp(get_field('date', $first_event->ID));
#
#      $last_event_date = clone $date;
#      $last_event_date->setTimestamp(get_field('date', $last_event->ID));
#
#      if($first_event_date->format('Y') == $last_event_date->format('Y')){
#        $datestring = $first_event_date->format('F j').' &mdash; '.$last_event_date->format('F j, Y');
#      }else{
#        $datestring = $first_event_date->format('F j, Y').' &mdash; '.$last_event_date->format('F j, Y');
#      }
#
#      $html .= '<div class="uppercase clear margin-top-one"><strong>';
#      $html .= 'DATES:&nbsp;&nbsp;'.$datestring;
#
#      $month = '';
#      $html .= '<div class="clear calendar-month-links">';
#      foreach($events as $event){
#        $event_date = clone $date;
#        $event_date->setTimestamp(get_field('date', $event->ID));
#        if($month == $event_date->format('F')){
#          continue;
#        }else{
#          $month = $event_date->format('F');
#        }
#        $link = add_query_arg( 'cal', $event_date->format('Y') . '-' . $event_date->format('m') );
#        $html .= '<a href="'.$link.'" class="fancy-button margin-right-one">'.$month.'</a>';
#      }
#      $html .= '</div>';
#
#      $html .= '</strong></div>';
#    }
#    return $html;
#  }
