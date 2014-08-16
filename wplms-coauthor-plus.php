<?php
/**
 * Plugin Name: WPLMS CoAuthor Plus Add-On
 * Plugin URI: http://www.vibethemes.com/
 * Description: Integrates CoAuthor Plus with WPLMS
 * Author: VibeThemes
 * Version: 1.0.0
 * Author URI: https://vibethemes.com/
 * License: GNU AGPLv3
 * License URI: http://www.gnu.org/licenses/agpl-3.0.html
 */
/* ===== INTEGRATION with WP Coauthor plugin =========
 *==============================================*/

class WPLMS_Coauthors_Plus{
  private $version = 1.0;

  function _construct(){
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
  }

  function wplms_coauthor_plus_instructor($instructor, $id){
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
        $instructor .= '<h5 class="course_instructor"><a href="'.bp_core_get_user_domain($instructor_id) .'">'.$displayname.'<span>'.$special.'</span></a></h5>
        </div>';
        
      }
    }
    return $instructor;
 }

 function wplms_coauthor_plus_course_instructor($authors,$post_id){
   if ( function_exists('get_coauthors')) {
      $coauthors = get_coauthors( $post_id );
      if(isset($coauthors) && is_array($coauthors)){
        foreach($coauthors as $author){
          if(!in_array($author->ID,$authors))
            $authors[]=$author->ID;
        }
      }
  }
  return $authors;
}


}


new WPLMS_Coauthors_Plus( $config );


?>