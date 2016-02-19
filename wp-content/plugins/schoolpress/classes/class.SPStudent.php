<?php
/*
	Student Wrapper for SchoolPress Student User Type
	/wp-content/plugins/schoolpress/classes/class.SPStudent.php
*/

//load hooks/etc on init
add_action('init', array('SPStudent', 'sp_init'));

//class
class SPStudent extends WP_User {
	//load hooks on init
	static function sp_init()
	{
		add_action('pmpro_confirmation_message', array('SPStudent', 'pmpro_confirmation_message'));
        add_action('show_user_profile',array('SPStudent','sp_user_profile' ));
        add_action( 'edit_user_profile_update', array('SPStudent','sp_user_profile' ) );
        // user's own profile
        add_action( 'personal_options_update', array('SPStudent','sp_profile_update' ) );
// admins editing
        add_action( 'edit_user_profile_update', array('SPStudent','sp_profile_update' ) );
        add_action( 'pre_user_query', array('SPStudent','sp_pre_user_query' ));

	}
	
	//Get the classes I'm a student of
	function getClassesForStudent()
	{		
		//need a user id for this
		if(empty($this->ID))
			return false;
		
		//get corresponding class ids for buddypress groups this user is a member of
		$class_ids = array();
		if(bp_has_groups(array('user_id'=> $this->ID)))
		{
			while(bp_groups())
			{				
				bp_the_group();				
				$class_id = groups_get_groupmeta(bp_get_group_id(), "class_id", true);								
				if($class_id)
					$class_ids[] = $class_id;
			}
		}
		
		//get classes
		$classes = get_posts( array(				
				'post__in' => $class_ids,
				'post_type' => 'class',
				'post_status' => 'published',
				'posts_per_page' => -1				
			) );
			
		//remove classes I'm teaching		
		foreach($classes as $key => $class)
		{
			if($class->post_author == $this->ID)
				unset($classes[$key]);
		}

		//make sure classes is an array at least
		if ( empty( $classes ) )
			$this->classes = array();				
		else
			$this->classes = $classes;
		
		return $this->classes;
	}
	
	//Get the groups I'm in (regardless of which school/blog)
	function getGroups($force = false)
	{		
		//need a user id for this
		if(empty($this->ID))
			return false;
		
		//check cache
		if(isset($this->groups) && !$force)
			return $this->groups;				
				
		//remove the bp-site-groups filter
		remove_filter('groups_get_groups', 'bpsg_groups_get_groups');
		
		//get corresponding class ids for buddypress groups this user is a member of		
		$groups = array();
		if(bp_has_groups(array('user_id'=> $this->ID)))
		{
			while(bp_groups())
			{				
				bp_the_group();
				$group_id = bp_get_group_id();
				$groups[] = groups_get_group(array('group_id'=>$group_id));				
			}
		}
		
		//add the bp-site-groups filter back
		add_filter('groups_get_groups', 'bpsg_groups_get_groups');
		
		$this->groups = $groups;		
		return $this->groups;
	}
	
	//get ids of schools (blogs) I'm in
	function getSchools($force = false)
	{		
		//check cache
		if(isset($this->schools) && !$force)
			return $this->schools;
	
		//get groups
		$groups = $this->getGroups();
				
		//which sites are these groups in
		$blog_ids = array();
		$sites = array();
		foreach($groups as $group)
		{			
			$blog_id = groups_get_groupmeta($group->id, 'blog_id', true);
			if(!in_array($blog_id, $blog_ids))
			{
				$blog_ids[] = $blog_id;
				$sites[] = get_blog_details($blog_id);
			}
		}
		
		//return
		$this->schools = $sites;
		return $this->schools;
	}
	
	//show classes in confirmation message
	static function pmpro_confirmation_message($message)
	{
		/*
			need getClassesForStudent to get classes across sites to do this
		*/		
		global $current_user;
		$student = new SPStudent($current_user->ID);
				
		$sites = $student->getSchools();
				
		if(!empty($sites))
		{
			if(count($sites) > 1)
				$new = "\n<p>You are already enrolled in classes for some schools at SchoolPress:</p>";
			else
				$new = "\n<p>You are already enrolled in classes for a school at SchoolPress:</p>";
				
			$new .= "\n<ul>";
			foreach($sites as $site)
				$new .= "\n<li><a href='" . get_site_url($site->blog_id, "/my-classes/") . "'>" . $site->blogname . "</a></li>";
			$new .= "\n</ul>";

			$message = str_replace("Below are details", $new . "Below are details", $message);
		}
	
		return $message;

	}
    function sp_user_profile( $user ){
// show input
        $age = esc_attr( $user->age );?>
        <table class="form-table">
            <tbody>
            <tr>
                <th><label for="agess">Agesss</label></th>
                <td>
                    <input type="text" name="age" id="age" class="input"
                           value="<?php echo $age; ?>"/>
                </td>
            </tr>
            </tbody>
        </table>
    <?php
    }

    function sp_profile_update( $user_id ){
//make sure the current user can edit this user
        if ( ! current_user_can( 'edit_user', $user_id ) )
            return false;
// check if value has been posted
        if ( isset( $_POST['age'] ) ){
// update user meta
            update_user_meta( $user_id, 'age', intval( $_POST['age'] ) );
        }
    }
    // update user_query if sorting by Age
    function sp_pre_user_query( $user_query )
    {
        global $wpdb, $current_screen, $user_search;
// make sure we are viewing the users list in the dashboard
        if ($current_screen->id != 'users') {
            return;
        }
// order by age
        if ($user_query->query_vars['orderby'] == 'Age') {
            $user_search = new WP_User_Query();
            $user_search->query_from .= " INNER JOIN $wpdb->usermeta m1
ON $wpdb->users u1
AND (u1.ID = m1.user_id)
AND (m1.meta_key = 'age')";
            $user_search->query_orderby = " ORDER BY m1.meta_value
" . $user_query->query_vars['order'];
        }
    }


}

