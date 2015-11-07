<?php
/**
 * Plugin Name: WPLMS CoAuthor Plus Add-On
 * Plugin URI: http://www.vibethemes.com/
 * Description: Integrates CoAuthor Plus with WPLMS
 * Author: VibeThemes
 * Version: 1.1
 * Author URI: https://vibethemes.com/
 * License: GNU AGPLv3
 * License URI: http://www.gnu.org/licenses/agpl-3.0.html
 */
/* ===== INTEGRATION with WP Coauthor plugin =========
 *==============================================*/
if ( !defined( 'ABSPATH' ) ) exit;

//require_once( dirname( __FILE__ ) . '/../co-authors-plus/co-authors-plus.php' );
class WPLMS_Coauthors_Plus { //extends coauthors_plus{
  private $version = 1.0;

  public function __construct(){
    if($this -> meet_requirements()){
      $this->init();
    }
  }

  function meet_requirements(){
      if ( in_array( 'co-authors-plus/co-authors-plus.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
        return true;
      else
        return false;
  }

  function init(){
    add_filter('wplms_display_course_instructor',array($this,'wplms_coauthor_plus_instructor'),10,2);
    add_filter('wplms_course_instructors',array($this,'wplms_coauthor_plus_course_instructor'),10,2);
    add_filter('wplms_dashboard_courses_instructors',array($this,'wplms_dashboard_instructors_courses'),10,2);
  }

  function wplms_coauthor_plus_instructor($instructor, $id,$r = null){

    if ( function_exists('get_coauthors')) {
      $coauthors = get_coauthors( $id );
      $instructor ='';
      foreach($coauthors as $k=>$inst){
        $instructor_id = $inst->ID;
        $displayname = bp_core_get_user_displayname($instructor_id);
        if(function_exists('vibe_get_option'))
          $field = vibe_get_option('instructor_field');

        
        $special='';
        if(bp_is_active('xprofile'))
        $special = bp_get_profile_field_data('field='.$field.'&user_id='.$instructor_id);
        $r = array('item_id'=>$instructor_id,'object'=>'user');
        $instructor .= '<div class="instructor_course"><div class="item-avatar">'.bp_core_fetch_avatar( $r ).'</div>';
        $instructor .= '<h5 class="course_instructor"><a href="'.bp_core_get_user_domain($instructor_id) .'">'.$displayname.'<span>'.$special.'</span></a></h5>';
        $instructor .= apply_filters('wplms_instructor_meta','',$instructor_id,$r);
        $instructor .=  '</div>';
        
      }
    }
    return $instructor;
   }

   public function wplms_coauthor_plus_course_instructor($authors,$post_id){
     if ( function_exists('get_coauthors')) {
        $coauthors = get_coauthors( $post_id );
        if(isset($coauthors) && is_array($coauthors)){
          $authors=array();
          foreach($coauthors as $author){
            if(!in_array($author->ID,$authors))
              $authors[]=$author->ID;
          }
        }
    }
    return $authors;
  }
  function wplms_dashboard_instructors_courses($query,$user_id=0){
    if(!isset($user_id) || !is_numeric($user_id) || !$user_id)
      $user_id=get_current_user_id();

    global $wpdb;
    $user_info = get_userdata($user_id);
    $s='cap-'.$user_info->user_nicename;
    $query = $wpdb->prepare("SELECT posts.ID as course_id
                            FROM {$wpdb->posts} AS posts
                            LEFT JOIN {$wpdb->term_relationships} txr ON posts.ID = txr.object_id
                            LEFT JOIN {$wpdb->term_taxonomy} tx ON txr.term_taxonomy_id = tx.term_taxonomy_id
                            LEFT JOIN {$wpdb->terms} trm ON tx.term_id = trm.term_id
                            WHERE (tx.taxonomy= 'author' AND trm.slug LIKE '%s')
                            AND posts.post_status = 'publish'
                            AND posts.post_type = 'course'
                            GROUP BY posts.ID
                            ORDER BY posts.post_date DESC",$s);
    return $query;
  }
}


add_action('init','wplms_coauthors_plus_function');
function wplms_coauthors_plus_function(){
  if(class_exists('WPLMS_Coauthors_Plus')){ 
    $wplms_events = new WPLMS_Coauthors_Plus();
  }
}

?>