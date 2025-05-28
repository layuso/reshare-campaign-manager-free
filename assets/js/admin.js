// Create a namespace for our plugin
const ReshareManager = {
    Modal: {
        init: function() {
            this.createModalHTML();
            this.bindEvents();
        },

        createModalHTML: function() {
            if (jQuery('#reshare-modal-wrapper').length === 0) {
                jQuery('body').append(`
                    <div id="reshare-modal-wrapper" class="reshare-modal-wrapper" style="display: none;">
                        <div id="reshare-modal" class="reshare-modal">
                            <div class="reshare-modal-content"></div>
                        </div>
                    </div>
                `);
            }
        },

        bindEvents: function() {
            const $ = jQuery;
            
            $('#reshare-new-campaign').on('click', (e) => {
                e.preventDefault();
                this.loadContent();
            });

            $('#reshare-modal-wrapper').on('click', (e) => {
                if (e.target === e.currentTarget) {
                    this.hide();
                }
            });
        },

        loadContent: function() {
            this.showLoading();
            jQuery.ajax({
                url: reshareAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'reshare_get_wizard_html',
                    nonce: reshareAdmin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        jQuery('.reshare-modal-content').html(response.data.html);
                        this.show();
                        ReshareManager.Wizard.init();
                    } else {
                        alert('Failed to load wizard.');
                        this.hide();
                    }
                },
                error: () => {
                    alert('Failed to load wizard.');
                    this.hide();
                }
            });
        },

        show: function() {
            jQuery('#reshare-modal-wrapper').fadeIn(200);
            jQuery('body').addClass('modal-open');
        },

        hide: function() {
            jQuery('#reshare-modal-wrapper').fadeOut(200);
            jQuery('body').removeClass('modal-open');
        },

        showLoading: function() {
            jQuery('.reshare-modal-content').html(`
                <div class="reshare-loading">
                    <span class="spinner is-active"></span>
                    <p>Loading wizard...</p>
                </div>
            `);
            this.show();
        }
    },

    Wizard: {
        currentStep: 1,
        totalSteps: 7,
        selectedPosts: [],
        selectedAccounts: [],

        init: function() {
            this.reset();
            this.bindEvents();
            this.showStep(1);
        },

        reset: function() {
            this.currentStep = 1;
            this.selectedPosts = [];
            this.selectedAccounts = [];
            this.initDatepicker();
        },

        bindEvents: function() {
            const $ = jQuery;
            
            // Navigation
            $('#next-step').on('click', (e) => {
                e.preventDefault();
                const isValid = this.validateStep(this.currentStep);
                if (isValid) {
                    this.showStep(this.currentStep + 1);
                }
            });

            $('#prev-step').on('click', (e) => {
                e.preventDefault();
                this.showStep(this.currentStep - 1);
            });

            // Social accounts
            $(document).on('change', '.social-account-checkbox', (e) => {
                const $checkbox = $(e.target);
                const accountId = parseInt($checkbox.val(), 10);
                
                if ($checkbox.is(':checked')) {
                    if (!this.selectedAccounts.includes(accountId)) {
                        this.selectedAccounts.push(accountId);
                    }
                } else {
                    this.selectedAccounts = this.selectedAccounts.filter(id => id !== accountId);
                }
            });

            // Posts
            $(document).on('click', '.add-post-button', (e) => {
                e.preventDefault();
                const $button = $(e.target);
                const postId = parseInt($button.attr('data-post-id'), 10);
                const postTitle = $button.attr('data-post-title');

                if (!postId || this.selectedPosts.some(p => p.id === postId)) {
                    return;
                }

                this.addPost({ id: postId, title: postTitle });
                $button.addClass('button-disabled').prop('disabled', true).text('Added');
            });

            $(document).on('click', '.remove-post', (e) => {
                e.preventDefault();
                const $row = $(e.target).closest('tr');
                const postId = parseInt($row.data('post-id'), 10);
                this.removePost(postId);
            });
        },

        initDatepicker: function() {
            const $ = jQuery;
            const $datepicker = $('#start-date');
            
            if ($datepicker.length && $.fn.datepicker) {
                $datepicker.datepicker('destroy');
                $datepicker.datepicker({
                    dateFormat: 'yy-mm-dd',
                    minDate: 0,
                    beforeShow: () => {
                        $('.ui-datepicker').addClass('reshare-datepicker');
                    },
                    onClose: () => {
                        $('.ui-datepicker').removeClass('reshare-datepicker');
                        $('.ui-datepicker').not('#ui-datepicker-div').remove();
                    }
                });
            }
        },

        validateStep: function(step) {
            const $ = jQuery;
            let isValid = true;
            let message = '';

            switch(step) {
                case 1:
                    const name = $('#campaign-name').val().trim();
                    if (!name) {
                        isValid = false;
                        message = 'Please enter a campaign name.';
                    }
                    break;

                case 2:
                    const date = $('#start-date').val();
                    const time = $('#start-time').val();
                    const frequency = $('#frequency-value').val();
                    if (!date || !time) {
                        isValid = false;
                        message = 'Please select both start date and time.';
                    } else if (!frequency || frequency < 1) {
                        isValid = false;
                        message = 'Please enter a valid frequency value.';
                    }
                    break;

                case 3:
                    if (this.selectedAccounts.length === 0) {
                        isValid = false;
                        message = 'Please select at least one social account.';
                    }
                    break;

                case 4:
                    if (this.selectedPosts.length === 0) {
                        isValid = false;
                        message = 'Please select at least one post.';
                    }
                    break;
            }

            if (!isValid) {
                alert(message);
            }

            return isValid;
        },

        showStep: function(step) {
            const $ = jQuery;
            
            if (step < 1 || step > this.totalSteps) {
                return;
            }

            $('.wizard-step').hide();
            $(`[data-step="${step}"]`).show();
            
            $('#prev-step').toggle(step > 1);
            $('#next-step').toggle(step < this.totalSteps);
            $('#start-campaign').toggle(step === this.totalSteps);
            
            this.currentStep = step;
            
            if (step === 3) {
                this.loadSocialAccounts();
            } else if (step === 4) {
                this.loadPosts();
            }
        },

        addPost: function(post) {
            const $ = jQuery;
            
            if (!post || !post.id || !post.title) {
                return;
            }

            if (this.selectedPosts.some(p => p.id === post.id)) {
                return;
            }

            this.selectedPosts.push(post);
            
            const $row = $(`
                <tr data-post-id="${post.id}">
                    <td class="column-handle">
                        <span class="dashicons dashicons-menu"></span>
                    </td>
                    <td class="column-title">${post.title}</td>
                    <td class="column-remove">
                        <button type="button" class="button-link-delete remove-post">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </td>
                </tr>
            `);
            
            $('#selected-posts-list').append($row);
            this.updateSelectedPostsCount();
        },

        removePost: function(postId) {
            const $ = jQuery;
            
            if (!postId) {
                return;
            }

            this.selectedPosts = this.selectedPosts.filter(p => p.id !== postId);
            $(`tr[data-post-id="${postId}"]`).remove();
            
            $(`.add-post-button[data-post-id="${postId}"]`)
                .removeClass('button-disabled')
                .prop('disabled', false)
                .text('Add');
            
            this.updateSelectedPostsCount();
        },

        updateSelectedPostsCount: function() {
            const count = this.selectedPosts.length;
            jQuery('.reshare-selected-posts .hndle span').text(
                `${count} ${count === 1 ? 'Post' : 'Posts'} Selected`
            );
        },

        loadSocialAccounts: function() {
            jQuery.ajax({
                url: reshareAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'reshare_get_social_accounts',
                    nonce: reshareAdmin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        const $list = jQuery('#social-accounts-list');
                        $list.empty();

                        response.data.forEach(account => {
                            const isSelected = this.selectedAccounts.includes(account.id);
                            $list.append(`
                                <div class="social-account-item">
                                    <label>
                                        <input type="checkbox" 
                                               class="social-account-checkbox" 
                                               value="${account.id}" 
                                               ${isSelected ? 'checked' : ''}>
                                        ${account.name} (${account.type})
                                    </label>
                                </div>
                            `);
                        });
                    }
                }
            });
        },

        loadPosts: function() {
            jQuery.ajax({
                url: reshareAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'reshare_search_posts',
                    nonce: reshareAdmin.nonce,
                    search: jQuery('#post-search').val(),
                    category: jQuery('#category-filter').val(),
                    tag: jQuery('#tag-filter').val(),
                    page: 1
                },
                success: (response) => {
                    if (response.success) {
                        jQuery('#posts-list').html(response.data.html);
                        
                        // Update button states
                        this.selectedPosts.forEach(post => {
                            jQuery(`button[data-post-id="${post.id}"]`)
                                .addClass('button-disabled')
                                .prop('disabled', true)
                                .text('Added');
                        });
                    }
                }
            });
        }
    }
};

jQuery(document).ready(() => {
    ReshareManager.Modal.init();
}); 