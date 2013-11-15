<?php 

class w2dc_media_manager {
	
	public function __construct() {
		global $pagenow;

		if ($pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'admin-ajax.php') {
			add_action('add_meta_boxes', array($this, 'addMediaMetabox'));
			add_action('wp_ajax_upload_image', array($this, 'upload_image'));
			add_action('wp_ajax_nopriv_upload_image', array($this, 'upload_image'));
		} 
		add_filter('posts_where', array($this, 'prevent_users_see_other_media'));
	}
	
	public function prevent_users_see_other_media($where){
		global $current_user;

		if(is_user_logged_in() && isset($_POST['action']) && $_POST['action'] == 'query-attachments' && !current_user_can('edit_others_posts'))
			$where .= ' AND post_author='.$current_user->data->ID;

		return $where;
	}

	public function addMediaMetabox($post_type) {
		if ($post_type == W2DC_POST_TYPE && ($level = w2dc_getCurrentListingInAdmin()->level) && ($level->images_number > 0 || $level->videos_number > 0)) {
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_styles'));

			add_meta_box('w2dc_media_metabox',
					__('Listing media', 'W2DC'),
					array($this, 'mediaMetabox'),
					W2DC_POST_TYPE,
					'normal',
					'high');
		}
	}

	public function mediaMetabox($post) {
		$listing = w2dc_getCurrentListingInAdmin();

		w2dc_renderTemplate('media_metabox.tpl.php', array('listing' => $listing));
	}

	public function upload_image() {
		$result = array('error_msg' => '', 'uploaded_file' => '');

		if (wp_verify_nonce($_GET['_wpnonce'], 'upload_images') && isset($_FILES['browse_file']) && ($_FILES['browse_file']['size'] > 0)) {
			// Get the type of the uploaded file. This is returned as "type/extension"
			$arr_file_type = wp_check_filetype(basename($_FILES['browse_file']['name']));
			$uploaded_file_type = $arr_file_type['type'];
		
			// Set an array containing a list of acceptable formats
			$allowed_file_types = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png');
		
			// If the uploaded file is the right format
			if (in_array($uploaded_file_type, $allowed_file_types)) {
				// Options array for the wp_handle_upload function. 'test_upload' => false
				$upload_overrides = array('test_form' => false);
		
				// Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
				$uploaded_file = wp_handle_upload($_FILES['browse_file'], $upload_overrides);

				// If the wp_handle_upload call returned a local path for the image
				if (isset($uploaded_file['file'])) {
					// The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
					$file_name_and_location = $uploaded_file['file'];

					// Generate a title for the image that'll be used in the media library
					$file_title_for_media_library = 'your title here';
		
					// Set up options array to add this file as an attachment
					$attachment = array(
							'post_mime_type' => $uploaded_file_type,
							'post_title' => '',
							'post_content' => '',
							'post_status' => 'inherit'
					);
					
					if (isset($_GET['post_id']) && $_GET['post_id'])
						$post_id = $_GET['post_id'];
					else
						$post_id = 0;

					$real_crop_option = get_option('thumbnail_crop');
					if (!isset($_GET['crop']) && get_option('thumbnail_crop'))
						update_option('thumbnail_crop', 0);
					elseif (isset($_GET['crop']) && !get_option('thumbnail_crop'))
						update_option('thumbnail_crop', 1);

					// Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
					$attach_id = wp_insert_attachment($attachment, $file_name_and_location, $post_id);
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attach_data = wp_generate_attachment_metadata($attach_id, $file_name_and_location);
					wp_update_attachment_metadata($attach_id,  $attach_data);
					
					update_option('thumbnail_crop', $real_crop_option);

					// insert attachment ID to the post meta
					add_post_meta($post_id, '_attached_image', $attach_id);
					
					$src = wp_get_attachment_image_src($attach_id, 'thumbnail');
					
					$result['uploaded_file'] = $src[0];
					$result['attachment_id'] = $attach_id;
				} else { // wp_handle_upload returned some kind of error. the return does contain error details, so you can use it here if you want.
					$result['error_msg'] = 'There was a problem with your upload.';
				}
			} else { // wrong file type
				$result['error_msg'] = 'Please upload only image files (jpg, gif or png).';
			}
		
		} else { // No file was passed
			$result['error_msg'] = __('Choose image to upload first!', 'W2DC');
		}
		
		echo json_encode($result);
		die();
	}
	
	public function validateAttachments($level, &$errors) {
		if ($level->images_number || $level->videos_number) {
			$validation = new form_validation();
			if ($level->images_number) {
				$validation->set_rules('attached_image_id[]', __('Listing images ID', 'W2DC'));
				$validation->set_rules('attached_image_title[]', __('Listing images caption', 'W2DC'));
				if ($level->logo_enabled)
					$validation->set_rules('attached_image_as_logo', __('Logo image selection', 'W2DC'));
			}
			if ($level->videos_number) {
				$validation->set_rules('attached_video_id[]', __('Listing video ID', 'W2DC'));
				$validation->set_rules('attached_video_title[]', __('Listing video caption', 'W2DC'));
			}
	
			if (!$validation->run())
				$errors[] = $validation->error_string();
			
			return $validation->result_array();
		}
	}
	
	public function saveAttachments($level, $post_id, $validation_results) {
		if ($level->images_number) {
			if (!$existed_images = get_post_meta($post_id, '_attached_image'))
				$existed_images = array();

			if ($validation_results['attached_image_id[]']) {
				// remove unauthorized images
				$validation_results['attached_image_id[]'] = array_slice($validation_results['attached_image_id[]'], 0, $level->images_number, true);
	
				foreach ($validation_results['attached_image_id[]'] AS $key=>$attachment_id) {
					if (in_array($attachment_id, $existed_images))
						unset($existed_images[array_search($attachment_id, $existed_images)]);
		
					wp_update_post(array('ID' => $attachment_id, 'post_title' => $validation_results['attached_image_title[]'][$key]));
				}
			}

			foreach ($existed_images AS $attachment_id) {
				wp_delete_attachment($attachment_id, true);
				delete_post_meta($post_id, '_attached_image', $attachment_id);
			}
	
			if ($level->logo_enabled) {
				if (isset($validation_results['attached_image_as_logo']) && is_numeric($validation_results['attached_image_as_logo']))
					$logo_id = $validation_results['attached_image_as_logo'];
				elseif ($existed_images = get_post_meta($post_id, '_attached_image'))
					$logo_id = $existed_images[0];
		
				if (isset($logo_id))
					update_post_meta($post_id, '_attached_image_as_logo', $logo_id);
				else
					delete_post_meta($post_id, '_attached_image_as_logo');
			}
		}

		delete_post_meta($post_id, '_attached_video_id');
		delete_post_meta($post_id, '_attached_video_caption');
		if ($level->videos_number && $validation_results['attached_video_id[]']) {
			// remove unauthorized videos
			$validation_results['attached_video_id[]'] = array_slice($validation_results['attached_video_id[]'], 0, $level->videos_number, true);

			foreach ($validation_results['attached_video_id[]'] AS $key=>$video_id) {
				add_post_meta($post_id, '_attached_video_id', $video_id);
				add_post_meta($post_id, '_attached_video_caption', $validation_results['attached_video_title[]'][$key]);
			}
		}
	}
	
	public function admin_enqueue_scripts_styles() {
		wp_enqueue_style('media_styles', W2DC_RESOURCES_URL . 'lightbox/css/lightbox.css');
		wp_enqueue_script('media_scripts_lightbox', W2DC_RESOURCES_URL . 'lightbox/js/lightbox-2.6.min.js', array('jquery'));
		wp_enqueue_script('media_scripts', W2DC_RESOURCES_URL . 'js/ajaxfileupload.js', array('jquery'));
	}
}

?>