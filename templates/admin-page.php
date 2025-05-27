<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo esc_html__('ReShare Campaign Manager', 'reshare-campaign-manager'); ?>
    </h1>
    
    <a href="#" class="page-title-action" id="reshare-new-campaign">
        <?php echo esc_html__('Add New Campaign', 'reshare-campaign-manager'); ?>
    </a>

    <hr class="wp-header-end">

    <div id="reshare-admin-app">
        <!-- React app will be mounted here -->
        <div class="reshare-loading">
            <span class="spinner is-active"></span>
            <?php echo esc_html__('Loading...', 'reshare-campaign-manager'); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    // This will be replaced by the React app
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof reshareAdmin !== 'undefined' && typeof wp !== 'undefined' && wp.element) {
            // React app will initialize here
        }
    });
</script> 