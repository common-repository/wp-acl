<?php 
$page_count = 5;
// find out on which page are we
$paging =  isset( $_GET['list'] ) && ! empty( $_GET['list'] ) ? $_GET['list'] : 1 ;
// arguments for listed pages
$args = array(
    'posts_per_page' => $page_count,
    'post_type' => 'page',
    'paged' => $paging,
);
// get all roles availible
global $wp_roles;
$roles = $wp_roles->get_names();

//Update post meta as data submitted from save button
if(isset($_POST['submit'])){
      foreach($_POST['wp_select_role'] as $key => $value){
        update_post_meta($key, 'wp-acl_select_role',$value);
      }
echo "<span class='message success alert'>Page Level Access Control has been Updated</span>";
}
?>
<h1>WP ACL PAGE LIST</h2>
  <form name="update_option" action="" method="post">
        <table class="display table table-bordered wp-list-table widefat fixed striped users" id="page_list_table"  cellpadding="0" cellspacing="0">
          <thead>
              <tr>
                  <th class="page_name_custom">Page Name</th>
                  <th class="page_name_custom">Roles</th>
              </tr>
           
            
                  <?php $pages = get_posts( $args ); 
                      foreach ( $pages as $post ) {
                       $post_id= $post->ID;
                       $select_role = get_post_meta($post_id, 'wp-acl_select_role',true);

                     ?>   
                  <tr>
                            <td class="page_name_custom"> <?php echo $post->post_title; ?></td>
                            <td class="roles_custom">          
                                  <?php echo '<select multiple="multiple" name="wp_select_role['.$post_id.'][]" id="wp_select_role" class="wp_select_role">'; ?>
                                  <?php 
                                 // $selected='';
                                  foreach($roles as $key=>$role) { 
                                  if(!empty($select_role)){
                                 if(in_array($key, $select_role)){
                                         $selected='selected';
                                        }else {
                                          $selected='';
                                        }   
                                 }    ?>
                                   <option value="<?php echo isset($key) ? $key:''; ?>"  <?php echo isset($selected)?$selected:''; ?>><?php echo isset($role)?$role:'';?></option>
                                  <?php }//end foreach ?>
                                <?php  echo '</select>'; ?>
                            </td>

                  </tr>
                  <?php  } ?>      
         </thead>
        </table>
        <div class="clearfix"></div>
        <input type="submit" name="submit"  class="margin-center button button-primary s_btn" value="Save"/>
    </form>
<?php
    // how many pages do we need?
$count_pages = ceil( count( get_pages($args) ) / $page_count );
// display the navigation
if ($count_pages > 0 ) {
    echo '<div class="footer-pagination-custom">';
    for ($i = 1; $i <= $count_pages; $i++) {
        $separator = ( $i < $count_pages ) ? ' | ' : '';
        $url_args = add_query_arg( 'list', $i );
        echo  "<a href='$url_args'>Page $i</a>".$separator;
    }
    echo '</div>';
}

wp_reset_postdata();

?>