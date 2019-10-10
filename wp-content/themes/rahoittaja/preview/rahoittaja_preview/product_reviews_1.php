<?php 
	if (!defined('ABSPATH')) exit; // Exit if accessed directly
	global $woocommerce;
    if (!function_exists('theme_product_review_1')) {
        function theme_product_review_1() {
            global $post, $comment;
            ?>
            <li itemprop="reviews" itemscope itemtype="http://schema.org/Review" <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
                <div  id="comment-<?php comment_ID(); ?>" class="comment_container data-control-id-3185 bd-productreview-1">
                    <div class="comment-text">
                        <div class="data-control-id-3100 bd-layoutcontainer-30 bd-columns bd-no-margins">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row ">
                <div class="data-control-id-3096 bd-columnwrapper-68 
 col-md-1">
    <div class="bd-layoutcolumn-68 bd-column" ><div class="bd-vertical-align-wrapper"><div class="data-control-id-3094 bd-reviewavatar-1"><?php echo theme_get_avatar(array('class' => 'data-control-id-3086 bd-imagestyles', 'attributes' => ''), true); ?></div></div></div>
</div>
	
		<div class="data-control-id-3098 bd-columnwrapper-69 
 col-md-11">
    <div class="bd-layoutcolumn-69 bd-column" ><div class="bd-vertical-align-wrapper"><div class="data-control-id-3075 bd-reviewmetadata-1">
    <?php if ($comment->comment_approved == '0') { ?>
        <p class="meta"><em><?php _e('Your comment is awaiting approval', 'woocommerce'); ?></em></p>
    <?php } else { ?>
        <p class="meta"><strong itemprop="author"><?php comment_author(); ?></strong><?php
            if (get_option('woocommerce_review_rating_verification_label') == 'yes'
                && (function_exists('wc_customer_bought_product') ? wc_customer_bought_product($comment->comment_author_email, $comment->user_id, $post->ID)
                    : woocommerce_customer_bought_product($comment->comment_author_email, $comment->user_id, $post->ID))
            ) {
                echo '(' . __('verified owner', 'woocommerce') . ') ';
            }
            ?>&ndash; <time itemprop="datePublished" time datetime="<?php echo get_comment_date('c'); ?>"><?php echo get_comment_date(__('M jS Y', 'woocommerce')); ?></time>:</p>
    <?php } ?>
</div>
	
		<div class='data-control-id-3084 bd-rating' itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
<?php
    $rating = esc_attr(get_comment_meta($GLOBALS['comment']->comment_ID, 'rating', true));
    for ($i = 1; $i <= 5; $i++) {
		$active_class = $i <= $rating ? 'active' : '';
        echo '<span class="data-control-id-3083  bd-icon bd-icon-3 ' . $active_class . '"></span>';
    }
    echo '<strong itemprop="ratingValue" style="display:none">' . $rating . '</strong>';
?>
</div>
	
		<div itemprop="description" class="description data-control-id-3076 bd-reviewtext-1"><?php comment_text(); ?></div></div></div>
</div>
            </div>
        </div>
    </div>
</div>
                    </div>
                </div>
        <?php
            // end-callback will close <li>
        }
    }
?>
<div class="data-control-id-3241 bd-productreviews-1">
    <?php if ( comments_open() ) {
        $title_reply = '';
        if ( have_comments() ) { ?>
            <div class="data-control-id-3183 bd-container-38 bd-tagstyles">
    <h2><?php _e('Reviews', 'woocommerce') ?></h2>
</div>
            <ul class="comments-list">
                <?php wp_list_comments( array( 'callback' => 'theme_product_review_1' ) ); ?>
            </ul>
            <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) { ?>
                <div class="navigation">
                    <div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Previous', 'woocommerce' ) ); ?></div>
                    <div class="nav-next"><?php next_comments_link( __( 'Next <span class="meta-nav">&rarr;</span>', 'woocommerce' ) ); ?></div>
                </div>
            <?php }
            $title_reply = __('Add a review', 'woocommerce');
        } else {
            $title_reply = __('Be the first to review', 'woocommerce').' &ldquo;'.$post->post_title.'&rdquo;';
        }
        ?>
        <div id="review_form_1" class="data-control-id-3240 bd-reviewform-1">
    <?php
    $commenter = wp_get_current_commenter();
    $comment_form = array(
        'title_reply' => $title_reply,
        'comment_notes_before' => '',
        'comment_notes_after' => '',
        'fields' => array(
            'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name', 'woocommerce' ) . '</label> ' . '<span class="required">*</span>' .
                '<input name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" /></p>',
            'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email', 'woocommerce' ) . '</label> ' . '<span class="required">*</span>' .
                '<input name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" aria-required="true" /></p>',
        ),
        'label_submit' => __('Submit Review', 'woocommerce'),
        'id_submit' => 'submit_1',
        'logged_in_as' => '',
        'comment_field' => ''
    );
    if ( get_option('woocommerce_enable_review_rating') == 'yes' ) {
        $comment_form['comment_field'] = '<p class="comment-form-rating"><label for="rating">' . __('Rating', 'woocommerce') .'</label><select name="rating" id="rating_1">
        <option value="">'.__('Rate&hellip;', 'woocommerce').'</option>
        <option value="5">'.__('Perfect', 'woocommerce').'</option>
        <option value="4">'.__('Good', 'woocommerce').'</option>
        <option value="3">'.__('Average', 'woocommerce').'</option>
        <option value="2">'.__('Not that bad', 'woocommerce').'</option>
        <option value="1">'.__('Very Poor', 'woocommerce').'</option>
    </select></p>';
    }
    $comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . __( 'Your Review', 'woocommerce' ) . '</label><textarea name="comment" cols="45" rows="8" aria-required="true"></textarea></p>' . wp_nonce_field('woocommerce-comment_rating', '_n', true, false);
    ob_start();
    if (!function_exists('theme_comment_form_field_comment') && theme_get_option('theme_comment_use_smilies')) {
        function theme_comment_form_field_comment($form_field) {
            theme_include_lib('smiley.php');
            return theme_get_smilies_js() . '<p class="smilies">' . theme_get_smilies() . '</p>' . $form_field;
        }
        add_filter('comment_form_field_comment', 'theme_comment_form_field_comment');
    }
    comment_form( $comment_form );
    echo str_replace(
        array(
            'class="comment-respond',
            '<h3',
            '</h3>',
            'type="text"',
            '<textarea',
            'type="submit"'
        ),
        array(
            ' class="comment-respond data-control-id-3240 bd-reviewform-1',
            '<div  class="data-control-id-3217 bd-container-39 bd-tagstyles"><h2',
            '</h2></div>',
            'type="text"  class="data-control-id-3218 bd-bootstrapinput form-control"',
            '<textarea id="comment_area_1"  class="data-control-id-3218 bd-bootstrapinput form-control"',
            'class="data-control-id-3222 bd-button"  type="submit"'
        ),
        ob_get_clean());
    ?>
</div>
    <?php } ?>
</div>