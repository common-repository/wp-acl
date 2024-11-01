<?php
/**
* Plugin Name: WP ACL
* Description: Plugin to handle Access control related functionality for pages
* Author: Mitesh Shah,Gautam Patel
* Version: 1.0
*/
if ( ! defined( 'ABSPATH' ) )
        exit; // Exit if accessed directly.
/**
     * wp-acl
     *
     * @author   Mitesh shah,Gautam Patel
     */        

class page_roles_acl {
    /**
         * Instance of this class.
         *
         * @var object
         *
         * @since 1.0.0
         */
        protected static $instance = null;

        /**
         * Slug.
         *
         * @var string
         *
         * @since 1.0.0
         */
        protected static $text_domain = 'wp-acl';

        /**
         * Initialize the plugin
         *
         * @since 1.0.0
         */

    private function __construct() {
            // Load styles and script
            add_action( 'admin_enqueue_scripts', array( $this, 'include_admin_script_sytles' ));

            // This will add a select box right before the submit button under admin page
            if ( is_admin() ) {
                add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );
            }

            // Save post
            add_action( 'save_post', array( $this, 'user_role_access' ) );

            // Check page is allowed or not from admin

            add_filter( 'the_content', array( $this, 'isAllowed' ) );

           
       }
       /**
         * Return an instance of this class.
         *
         *
         * @since 1.0.0
         *
         * @return object A single instance of this class.
         */
        public static function get_instance() {
            // If the single instance hasn't been set, set it now.
            if ( null == self::$instance ) {
                self::$instance = new self;
            }

            return self::$instance;
        }


     public function include_admin_script_sytles(){
            wp_enqueue_style( self::$text_domain . '_css_main', plugins_url( '/assets/css/acl.css', __FILE__ ), array(), null, 'all' );
            
        }   
  

       /**
         * Add custom field in meta box submitdiv.
         *
         * @since 1.0.0
         */
        public function post_submitbox_misc_actions() {
            $post = get_post();
            echo '<div class="public-post-preview">';
                $this->access_roles_html_select( $post );
            echo '</div>';

        }

        /**
         * Print the select with roles for define restrict page.
         *
         * @since 1.0.0
         *
         * @param WP_Post $post The post object.
         */
        private function access_roles_html_select( $post ) {
            global $wp_roles;
            $roles = $wp_roles->get_names();
            // Check if empty $post and define $post
            if ( empty( $post ) ) {
                $post = get_post();
            }
            $select_role = get_post_meta( $post->ID, self::$text_domain . '_select_role', true );
            
            // Field nonce for submit control
            wp_nonce_field( self::$text_domain . '_select_role', self::$text_domain . '_select_role_wpnonce' );
            echo '<div class="' . self::$text_domain . '_box-select-role">';
                echo '<p><strong>' . __( 'Please Select multiple role', 'wp-acl' ) . '</strong></p>';
                echo '<label class="screen-reader-text" for="' . self::$text_domain . '_select_role">' . __( 'Select role', 'wp-acl' ) . '</label>';
                echo '<select multiple name="' . self::$text_domain . '_select_role[]" id="'. self::$text_domain . '_select_role" class="' . self::$text_domain . '_select_role">'; ?>
                   <?php foreach($roles as $key=>$role) { 
                              if(!empty($select_role)){
                               if(in_array($key, $select_role)){
                                       $selected='selected';
                                      }else {
                                        $selected='';
                                      }   
                               }    ?>
                                   <option value="<?php echo $key; ?>" <?php echo $selected; ?>> <?php echo $role;?></option>
                                  <?php }//end foreach ?>
               <?php echo '</select>';
            echo '</div>';
        }  

        /**
         * Save select role for restrict access for page.
         *
         *
         * @since 1.0.0
         *
         * @param int $post_id The post id.
         * @param object $post The post object.
         * @return bool false or true
         */
        public function user_role_access( $post_id ) {
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                echo $post_id;
                return false;
            }

            if ( empty( $_POST[self::$text_domain . '_select_role_wpnonce'] ) || ! wp_verify_nonce( $_POST[self::$text_domain . '_select_role_wpnonce'], self::$text_domain . '_select_role' ) ) {
                return false;
            }

            $select_role = $_POST[self::$text_domain . '_select_role'];
            update_post_meta( $post_id, self::$text_domain . '_select_role', $select_role );

            return true;
        } 

        /**
         * Restrict content page
         *
         * @since 1.0.0
         *
         * @param string $content The content page
         * @return string $content
         */
        public function isAllowed( $content ) {
             $select_role = get_post_meta( get_the_ID(), self::$text_domain . '_select_role', true );
                if(!empty($select_role)){
                      foreach ( $select_role as $key => $value ) {
                       $roles[] = $value;
                     }
                    $roles_seprated = implode(", ", $roles);
                   
                    if ($roles_seprated ) {
                        if ( current_user_can( $roles_seprated ) || current_user_can( 'administrator') || current_user_can( 'super-admin') ) {
                            return $content;
                        } else {
                                wp_redirect(403);
                                exit();
                        }
                    }
                }else {
                   return $content;  
                }
            
        }
    } 
   add_action( 'plugins_loaded', array( 'page_roles_acl', 'get_instance' ), 0 ); 

   add_action('admin_menu', 'wp_acl_setup_menu');

function wp_acl_setup_menu() {
    add_menu_page('Wp Acl', 'Wp Acl','manage_options', 'wp-acl','view_wp_acl');
   
}

function view_wp_acl(){
     require_once 'view-wp-acl.php';
}