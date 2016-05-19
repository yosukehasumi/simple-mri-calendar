<?php
/*
Plugin Name:  Simple Medium Rare Calendar
Plugin URI:
Description:  Seriously simple boilerplate calendar
Version:      1.0.0
Author:       Yosuke Hasumi
Author URI:   http://yosuke.ca
*/

class SimpleMRICalendar {

  private static $post_type_slug = 'calendar';
  public  static $timezone       = 'America/New_York';

  public static function init() {
    self::register_mri_event();
    self::register_mri_acf();
    flush_rewrite_rules();
  }

  //---------------------------------------------------------------
  private static function register_mri_event() {
    register_post_type( 'mri_event', array(
      'public'      => true,
      'has_archive' => false,
      'menu_icon'   => 'dashicons-calendar-alt',
      'label'       => 'Calendar',
      'supports'    => array('title'),
      'rewrite'     => array('slug' => $post_type_slug)
    ));
  }
  //---------------------------------------------------------------
  private static function register_mri_acf() {
    if( function_exists('acf_add_local_field_group') ) {
      acf_add_local_field_group(array (
        'key' => 'group_56df2fc564831',
        'title' => 'Event Date',
        'fields' => array (
          array (
            'key' => 'field_56df2fcdbe7af',
            'label' => 'Date',
            'name' => 'date',
            'type' => 'date_time_picker',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'show_date' => 'true',
            'date_format' => 'm/d/y',
            'time_format' => 'h:mm tt',
            'show_week_number' => 'false',
            'picker' => 'slider',
            'save_as_timestamp' => 'true',
            'get_as_timestamp' => 'true',
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'mri_event',
            ),
          ),
        ),
        'position' => 'side',
      ));
    }
  }
}
add_action( 'init', array( 'SimpleMRICalendar', 'init' ) );
//---------------------------------------------------------------
function simple_mri_calendar() {
  global $post;
  function get_post_ids($post) { return $post->ID; }

  $today       = new DateTime();
  $today->setTimezone(new DateTimeZone(SimpleMRICalendar::$timezone));
  $query_cal   = ($_REQUEST['cal'] != '' ? $_REQUEST['cal'] : $today->format('Y-m'));
  $year        = explode('-', $query_cal)[0];
  $month       = explode('-', $query_cal)[1];
  $this_month  = new DateTime(implode('-', array($year,$month,10)));
  $this_month->setTimezone(new DateTimeZone(SimpleMRICalendar::$timezone));
  if($_REQUEST['prod'] != ''){
    $production = get_post($_REQUEST['prod']);
    $production_ids = array($production->ID);
  }else{
    $productions = get_posts('post_type=production&posts_per_page=-1');
    $production_ids = array_map("get_post_ids", $productions);
  }

  $last_month = clone $this_month;
  $last_month->modify('last day of last month');
  $last_month_url = add_query_arg( 'cal', $last_month->format('Y') . '-' . $last_month->format('m'), get_permalink($post) );
  if($_REQUEST['prod'] != '') $last_month_url = add_query_arg( 'prod', implode(',',$production_ids), $last_month_url );

  $next_month = clone $this_month;
  $next_month->modify('first day of next month');
  $next_month_url = add_query_arg( 'cal', $next_month->format('Y') . '-' . $next_month->format('m'), get_permalink($post) );
  if($_REQUEST['prod'] != '') $next_month_url = add_query_arg( 'prod', implode(',',$production_ids), $next_month_url );

  $first_day = clone $this_month;
  $first_day->modify('first day of this month');

  $last_day = clone $this_month;
  $last_day->modify('last day of this month');

  $html = '';

  $html .= '<div id="mri-calendar">';
  $html .= '<div id="mri-calendar-mobile-events-container"></div>';
  $html .= '<div class="mri-calendar-header">';
  $html .= '<div class="mri-calendar-header-inside">';
  $html .= '<a href="'.$last_month_url.'"><i class="fa fa-chevron-left"></i></a>';
  $html .= '<span class="uppercase">'.$this_month->format('F Y').'</span>';
  $html .= '<a href="'.$next_month_url.'"><i class="fa fa-chevron-right"></i></a>';
  $html .= '</div>';
  $html .= '</div>';


  $html .= '<table class="mri-calendar-body">';
  $html .= '<thead><tr>';
  foreach(array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat') as $dayname) $html .= '<th>'.$dayname.'</th>';
  $html .= '</tr></thead>';

  $html .= '<tbody>';
  $html .= '<tr>';

  //--------------------------------------------------------------- Empties
  if($first_day->format('N') != 7){
    for ($i = 0 ; $i < $first_day->format('N'); $i++){
      $html .= '<td class="empty"><div class="cell-border-bottom"></div></td>';
    }
  }

  //--------------------------------------------------------------- Dates
  for ($i = 1 ; $i <= $last_day->format('j'); $i++){
    $date = new DateTime(implode('-', array($year,$month,$i)));
    $today->setTimezone(new DateTimeZone(SimpleMRICalendar::$timezone));
    $classes = array();
    if($today->format('Ymd') == $date->format('Ymd')) $classes[] = 'today';
    $events = get_posts(array(
      'post_type'      => 'mri_event',
      'posts_per_page' => -1,
      'order'          => 'ASC',
      'meta_key'       => 'date',
      'orderby'        => 'meta_value',
      'meta_query' => array(
        array(
          'key'     => 'production',
          'compare' => 'IN',
          'value'   => $production_ids
        ),
        array(
          'key'     => 'date',
          'compare' => '<',
          'value'   =>  mktime(0, 0, 0, $date->format('n'), $date->format('j') + 1, $date->format('Y') )
        ),
        array(
          'key'     => 'date',
          'compare' => '>',
          'value'   =>  mktime(0, 0, 0, $date->format('n'), $date->format('j'), $date->format('Y') )
        ),
      ),
    ));

    if(count($events) > 0) $classes[] = 'has-events';

    $html .= '<td class="'.implode(' ', $classes).'">';
    $html .= '<div class="mri-calendar-date">'.$date->format('j').'</div>';
    $html .= '<div class="mri-calendar-events">';

    foreach($events as $event){
      $html .= '<div class="mri-calendar-event">';
      if( function_exists('mri_calendar_event') ) $html .= mri_calendar_event($date, $event);
      $html .= '</div>';
    }

    $html .= '</div>';
    $html .= '<div class="cell-border-bottom"></div>';
    $html .= '</td>';
    if($date->format('N') == 6){
      $html .= '</tr><tr>';
    }
  }

  //--------------------------------------------------------------- Empties
  for ($i = $last_day->format('N') ; $i < 6; $i++){
    $html .= '<td class="empty"><div class="cell-border-bottom"></div></td>';
  }

  $html .= '</tr>';
  $html .= '</tbody>';

  $html .= '</table>';
  $html .= '<div class="mri-calendar-footer">';
  $html .= '</div>';

  $html .= '</div>';

  return $html;
}
?>
