<?php
if (!class_exists('starfish')) { die(); }

/**
 * Wordpress object - handling inserts
 *
 * @package starfish
 * @subpackage starfish.objects.wordpress
 */
class wordpress
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		// Die if wp is not loaded
		if (!isset(ABSPATH)) { die('Please load the WP environment.'); }

		return true;
	}

	/*
	 * Insert new post
	 * 
	 * @param mixed $post_id Number/null representing the post id
	 * @param string $title Post title
	 * @param string $content Post content
	 * @param string $tags Tag list
	 * 
	 * @return number ID of the new/updated post
	 */
	function insert_post($post_id, $title, $content='', $tags=array())
	{
		if (strlen($title) == 0)    { return false; }
		if (strlen($content) == 0)  { return false; }

		$categories = $this->insert_tags($tags);

		// Do the insert
		$post = array(
			'post_content'   => $content, // The full text of the post.
			'post_name'      => sanitize_title( $title ), // The name (slug) for your post
			'post_title'     => $title, // The title of your post.
			'post_status'    => 'publish', // Default 'draft'.
			'post_type'      => 'post', // Default 'post'.
			'ping_status'    => 'closed', // Pingbacks or trackbacks allowed. Default is the option 'default_ping_status'.
			'post_parent'    => 0, // Sets the parent of the new post, if any. Default 0.
			'menu_order'     => 0, // If new post is a page, sets the order in which it should appear in supported menus. Default 0.
			//'post_date'      => date("Y-m"), // The time post was made.
			'post_category'  => $categories
		);
		if (is_numeric($post_id)) { $post['ID'] = $post_id; }

		// Add the information
		$post_id = wp_insert_post( $post, true );

		// Show the message
		return $post_id;
	}

	/*
	 * Delete post
	 * 
	 * @param number $post_id Post ID
	 * @return mixed Post object if deleted / false if error
	 */
	function delete_post($post_id) {
		return wp_delete_post( $post_id, true );
	}

	/*
	 * Insert tags
	 * 
	 * @param mixed $tags Tag string / List of tags
	 * @param mixed $taxonomy The taxonomy name to use 
	 * @return array List of tag IDs
	 */
	function insert_tags($tags, $taxonomy='category', $insert_if_new=true) 
	{
		$list = array();
		if (!is_array($tags)) { $tags = array($tags); }

		foreach ($tags as $key=>$value)
		{
			$return = term_exists( $value, $taxonomy );
			if (!$return && $insert_if_new == true)
			{
				wp_insert_term( $value, $taxonomy );
				$return = term_exists( $value, $taxonomy );
			}

			if (isset($return['term_id']))
			{
				$list[] = $return['term_id'];
			}
		}

		return $list;
	}

	/*
	 * Insert attachment
	 * 
	 * @param string $filename The name of the file
	 * @param blob $image_data The content of the filename
	 * @param number $post_id The ID of the post to associate the filename with
	 * @param boolean $thumbnail If true, the new image is set as the post image
	 * 
	 * @return number Post id or 0 on failure
	 */
	function insert_attachment($filename, $image_data, $post_id=false, $thumbnail=false)
	{
		$upload_dir = @wp_upload_dir(); // Set upload folder

		// Check folder permission and define file location
		if( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		// Create the image  file on the server
		@file_put_contents( $file, $image_data );

		// Check image file type
		$wp_filetype = wp_check_filetype( $filename, null );

		// Set attachment data
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		// Create the attachment
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

		if ($attach_id != 0)
		{
			// Include image.php
			require_once(ABSPATH . 'wp-admin/includes/image.php');

			// Define attachment metadata
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

			// Assign metadata to attachment
			wp_update_attachment_metadata( $attach_id, $attach_data );

			// And finally assign featured image to post
			if ($post_id != false && $thumbnail != false)
			{
				set_post_thumbnail( $post_id, $attach_id );
			}
		}

		return $attach_id;
	}
}
?>