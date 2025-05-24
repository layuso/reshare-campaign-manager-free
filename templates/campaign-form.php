<?php
if (!defined('ABSPATH')) exit;

$editing = isset($_GET['campaign_id']);
$campaign_id = $editing ? absint($_GET['campaign_id']) : 0;
$campaign = $editing ? \RCM\Campaign_Manager::get_campaign($campaign_id) : null;
$title = $editing ? esc_attr($campaign['title']) : '';
$post_ids = $editing ? (array) $campaign['post_ids'] : [];
$global_prepend = $editing ? get_post_meta($campaign_id, '_rcm_global_prepend_text', true) : '';
$frequency = $editing ? esc_attr(get_post_meta($campaign_id, '_rcm_frequency', true)) : '+1 day';

?>

<div class="wrap">
    <h1><?php echo $editing ? esc_html__('Edit Campaign', 'reshare-campaign-manager') : esc_html__('Add New Campaign', 'reshare-campaign-manager'); ?></h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('rcm_save_campaign', 'rcm_nonce'); ?>
        <input type="hidden" name="action" value="rcm_save_campaign">
        <?php if ($editing): ?>
            <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th><label for="rcm_title"><?php esc_html_e('Campaign Title', 'reshare-campaign-manager'); ?></label></th>
                <td><input type="text" name="rcm_title" id="rcm_title" value="<?php echo $title; ?>" class="regular-text" required></td>
            </tr>

            <tr>
                <th><label for="rcm_global_prepend"><?php esc_html_e('Prepend Text (All Posts)', 'reshare-campaign-manager'); ?></label></th>
                <td>
                    <input type="text" name="rcm_global_prepend" id="rcm_global_prepend" value="<?php echo esc_attr($global_prepend); ?>" class="regular-text" maxlength="280">
                    <p class="description"><?php esc_html_e('Max 280 characters (ideal for social sharing).', 'reshare-campaign-manager'); ?></p>
                </td>
            </tr>

            <tr>
                <th><label for="rcm_frequency"><?php esc_html_e('Frequency (e.g., +1 day, +2 hours)', 'reshare-campaign-manager'); ?></label></th>
                <td><input type="text" name="rcm_frequency" id="rcm_frequency" value="<?php echo $frequency; ?>" class="regular-text"></td>
            </tr>

            <tr>
                <th><label><?php esc_html_e('Select Posts', 'reshare-campaign-manager'); ?></label></th>
                <td>
                    <fieldset>
                        <?php
                        $posts = \RCM\Post_Filter::query_posts();
                        if ($posts) {
                            foreach ($posts as $post) {
                                $checked = in_array($post->ID, $post_ids) ? 'checked' : '';
                                echo '<p><label><input type="checkbox" name="rcm_post_ids[]" value="' . esc_attr($post->ID) . '" ' . $checked . '> ' . esc_html($post->post_title) . '</label></p>';
                            }
                        } else {
                            echo '<p>' . esc_html__('No posts found.', 'reshare-campaign-manager') . '</p>';
                        }
                        ?>
                    </fieldset>
                </td>
            </tr>
        </table>

        <?php submit_button($editing ? __('Update Campaign', 'reshare-campaign-manager') : __('Create Campaign', 'reshare-campaign-manager')); ?>
    </form>
</div>

