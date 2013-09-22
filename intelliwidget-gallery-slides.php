<?php
/**
 * @package IntelliWidget_Gallery_Slides
 * @version 1.0.0
 */
/*
Plugin Name: IntelliWidget Gallery Slides
Plugin URI: http://www.lilaeamedia.com/plugins/intelliwidget-gallery-slides
Description: Converts default WP gallery shortcode into ul/li list for slideshows
Author: Lilaea Media
Version: 1.0.0
Author URI: http://www.lilaeamedia.com
*/

if ( !defined('ABSPATH')) exit;

function intelliwidget_gallery_slides($empty, $attr) {

	static $instance = 0;
	$instance++;
    if (empty($attr['ul_class'])) return '';
	if ( ! empty( $attr['ids'] ) ) {
		// 'ids' is explicitly ordered, unless you specify otherwise.
		if ( empty( $attr['orderby'] ) )
			$attr['orderby'] = 'post__in';
		$attr['include'] = $attr['ids'];
	}
	// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}
	if ( isset( $attr['ul_class'] ) ) {
		$attr['orderby'] = sanitize_text_field( $attr['ul_class'] );
		if ( !$attr['ul_class'] )
			unset( $attr['ul_class'] );
	}
	if ( isset( $attr['li_class'] ) ) {
		$attr['li_class'] = sanitize_text_field( $attr['li_class'] );
		if ( !$attr['li_class'] )
			unset( $attr['li_class'] );
	}

	extract(shortcode_atts(array(
		'order'     => 'ASC',
		'orderby'   => 'menu_order ID',
		'size'      => 'full',
		'include'   => '',
        'ul_class'  => 'slides',
        'li_class'  => '',
	), $attr, 'gallery'));

	if ( 'RAND' == $order )
		$orderby = 'none';

	if ( empty($include) ):
        return '';
    else:
		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ):
			$attachments[$val->ID] = $_attachments[$key];
		endforeach;
	endif;
	if ( empty($attachments) )
		return '';

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
		return $output;
	}

    $itemtag = 'li';

	$selector = "gallery-{$instance}";

	$size_class = sanitize_html_class( $size );
	$output = "<ul id='$selector' class='$ul_class'>";

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		if ( ! empty( $attr['link'] ) && 'file' === $attr['link'] )
			$image_output = wp_get_attachment_link( $id, $size, false, false );
		elseif ( ! empty( $attr['link'] ) && 'none' === $attr['link'] )
			$image_output = wp_get_attachment_image( $id, $size, false );
		else
			$image_output = wp_get_attachment_link( $id, $size, true, false );

		$output .= "<li class='$li_class'>";
		$output .= $image_output;
		$output .= "</li>";
	}

	$output .= "</ul>\n";

	return $output;
}
add_filter('post_gallery', 'intelliwidget_gallery_slides', 10, 2);
?>
