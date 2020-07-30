<?php
/**
 * Gravity Forms Bootstrap Hooks
 *
 * Actions & filters for using Gravityforms in your Bootstrap enabled theme.
 *
 * @package     WordPress
 * @subpackage  GravityForms
 * @link        https://github.com/MoshCat/gravityforms-bootstrap-hooks
 */

if (class_exists('GFCommon')) {
    /** Disable Gravity Forms CSS. */
    add_filter('pre_option_rg_gforms_disable_css', '__return_true');

    /** Enable HTML5. */
    add_filter('pre_option_rg_gforms_enable_html5', '__return_true');

    /** Enable the shortcode preview */
    add_filter('gform_shortcode_preview_disabled', '__return_false');

    /** Disable Gravity Forms CSS in Admin and allow custom styles in shortcode preview. */
    remove_filter('tiny_mce_before_init', array('GFForms', 'modify_tiny_mce_4'), 20);

    /** Style Gravity Forms preview pages. */
    add_filter('gform_preview_styles', function ($styles, $form) {
        wp_register_style('gf_styles', get_stylesheet_directory_uri() . '/dist/css/styles.css', array(), '1.0');
        $styles = array('gf_styles');
        return $styles;
    }, 10, 2);

    /** Grant Editors access to Gravityforms. */
    // add_action('admin_init', function () {
    //     $role = get_role('editor');
    //     $role->add_cap('gform_full_access'); // To disable use: $role->remove_cap.
    // });

    /** Place Gravityforms jQuery In Footer. */
    add_filter('gform_cdata_open', function ($content = '') {
        $content = 'document.addEventListener("DOMContentLoaded", function() { ';
        return $content;
    });
    add_filter('gform_cdata_close', function ($content = '') {
        $content = ' }, false);';
        return $content;
    });
    add_filter('gform_init_scripts_footer', '__return_true');

    /** change ul to div */
    add_filter( 'gform_get_form_filter', function($form_string, $form) {
        $form_string = str_replace( '<ul', '<div', $form_string);
        $form_string = str_replace( '</ul>', '</div>', $form_string);
        return $form_string;

    }, 10, 2 );

    /** change li to div */
    add_filter( 'gform_field_container', function ( $field_container, $field, $form, $css_class, $style, $field_content ) {
        $field_container = str_replace('<li','<div',$field_container);
        $field_container = str_replace('</li>','</div>',$field_container);
        return $field_container;
    }, 10, 6 );

    /** Add .mb-3 to .gfield. */
    add_filter('gform_field_css_class', function ($classes, $field, $form) {
        $classes .= ' mb-3';
        return $classes;
    }, 10, 3);

    /** Modify the fields classes to Bootstrap classes. */
    add_filter('gform_field_content', function ($content, $field, $value, $lead_id, $form_id) {
        // Exclude field types for later formisation
        $exclude_formcontrol = array(
            'hidden',
            'email',
            'select',
            'multiselect',
            'checkbox',
            'radio',
            'password',
            'fileupload',
            'list',
            'html',
            'address',
            'post_image',
            'post_category',
            'product',
            'option',
        );

        // Add .form-control to most inputs except those listed
        if (!in_array($field['type'], $exclude_formcontrol, true)) {
            $content = str_replace('class=\'small', 'class=\'form-control form-control-sm', $content);
            $content = str_replace('class=\'medium', 'class=\'form-control', $content);
            $content = str_replace('class=\'large', 'class=\'form-control form-control-lg', $content);
        }

        // Field labels
        $content = str_replace( '<label class=\'gfield_label\'', '<label class=\'form-label\'', $content );

        // Required fields
        $content = str_replace('<span class=\'gfield_required\'>*</span>', '<span class=\'text-danger\'>*</span>', $content);

        // Descriptions
        $content = str_replace('gfield_description', 'gfield_description small', $content);

        // Number fields.
        $content = str_replace('ginput_quantity', 'form-control', $content);
        $content = str_replace('ginput_amount', 'form-control', $content);

        // Select fields.
        $content = str_replace('gfield_select', 'form-select ', $content);
        if ('select' === $field['type'] || 'post_category' === $field['type']) {
            $content = str_replace('class=\'small', 'class=\'form-select-sm', $content);
            $content = str_replace('class=\'medium', 'class=\'', $content);
            $content = str_replace('class=\'large', 'class=\'form-select-lg', $content);
        }

        // Select fields with multiselect option.
        if ('multiselect' === $field['type']) {
            $content = str_replace('form-select', 'form-control', $content);
        }

        // Textarea fields.
        if ('textarea' === $field['type'] || 'post_content' === $field['type'] || 'post_excerpt' === $field['type']) {
            $content = str_replace('class=\'textarea small', 'class=\'form-control form-control-sm textarea', $content);
            $content = str_replace('class=\'textarea medium', 'class=\'form-control textarea', $content);
            $content = str_replace('class=\'textarea large', 'class=\'form-control form-control-lg textarea', $content);
        }

        // Checkbox fields.
        if ('checkbox' === $field['type'] || $field['inputType'] === 'checkbox') {
            $content = str_replace( 'li class=\'', 'li class=\'form-control form-checkbox ', $content );
            $content = str_replace( '<input ', '<input class=\'form-control-input\' ', $content );
//            $content = str_replace( '<label for', '<label class=\'form-control-label\' for', $content );
        }

        // Radio fields.
        if ('radio' === $field['type'] || $field['inputType'] === 'radio') {
            $content = str_replace('li class=\'', 'li class=\'form-control form-radio ', $content);
            $content = str_replace('<input name=', '<input class=\'form-control-input\' name=', $content);
//            $content = str_replace('<label for', '<label class=\'form-control-label\' for', $content);
            $content = str_replace('<input id', '<input class=\'form-control form-control-sm\' id', $content); // 'Other' option field
        }

        // Post Image fields.
        if ('post_image' === $field['type']) {
            $content = str_replace('type=\'text\'', 'type=\'text\' class=\'form-control form-control-sm\'', $content);
        }

        // Date & Time fields.
        if ('date' === $field['type'] || 'time' === $field['type']) {
            $content = str_replace('<select', '<select class=\'form-select\'', $content);
            $content = str_replace('type=\'number\'', 'type=\'number\' class=\'form-control\'', $content);
            $content = str_replace('class=\'datepicker medium', 'class=\'form-control datepicker', $content);
        }

        // Complex fields.
        if ('name' === $field['type'] || 'address' === $field['type'] || 'email' === $field['type'] || 'password' === $field['type']) {
            $content = str_replace('class=\'ginput_complex', 'class=\'ginput_complex form-row', $content);
            $content = str_replace('class=\'ginput_left', 'class=\'ginput_left col-6', $content);
            $content = str_replace('class=\'ginput_right', 'class=\'ginput_right col-6', $content);
            $content = str_replace('class=\'ginput_full', 'class=\'ginput_full col-12', $content);

            // Password fields.
            if ('password' === $field['type']) {
                $content = str_replace('type=\'password\'', 'type=\'password\' class=\'form-control\' ', $content);
            }

            // Email fields.
            if ('email' === $field['type']) {
                $content = str_replace('<input class=\'', '<input class=\'form-control ', $content);
                $content = str_replace('class=\'small', 'class=\'small form-control form-control-sm', $content);
                $content = str_replace('class=\'medium', 'class=\'medium form-control', $content);
                $content = str_replace('class=\'large', 'class=\'large form-control form-control-lg', $content);
            }

            // Name & Address fields.
            if ('name' === $field['type'] || 'address' === $field['type']) {
                $content = str_replace('<input ', '<input class=\'form-control\' ', $content);
                $content = str_replace('<select ', '<select class=\'form-select\' ', $content);
            }
        }

        // Consent fields.
        if ('consent' === $field['type']) {
            $content = str_replace('ginput_container_consent', 'ginput_container_consent form-checkbox', $content);
            $content = str_replace('gfield_consent_label', 'gfield_consent_label custom-control-label', $content);
            $content = str_replace('type=\'checkbox\'', 'type=\'checkbox\' class=\'custom-control-input\' ', $content);
        }

        // List fields.
        if ('list' === $field['type']) {
            $content = str_replace('type=\'text\'', 'type=\'text\' class=\'form-control\' ', $content);
        }
        // Fileupload fields. Add class 'preview' to the field to enable the image preview
        if ('fileupload' === $field['type'] || 'post_image' === $field['type']) {
            // Single file uploads
            if (!is_admin() && false === $field['multipleFiles']) {
                // Check if the field is required and create red asterix.
                $required = ($field['isRequired']) ? '<span class=\'text-danger\'>*</span>' : '';
                // Add a div spanning the label and input with .form-file
                $content = str_replace('<label class=\'gfield_label\'', '<div class=\'ginput_container\'><div class=\'form-file\'><label class=\'form-file-label\'', $content);
                // If Preview is enabled add an image without src.
                if (strpos($field['cssClass'], 'preview') !== false) {
                    $content .= '</div><img id=\'output_' . $form_id . '_' . $field['id'] . '\'></div>';
                } else {
                    $content .= '</div></div>';
                }
                // Add a new 'fake' label
                $content = str_replace('<div class=\'ginput_container\'>', '<label class=\'gfield_label\'>' . $field['label'] . $required . '</label><div class=\'ginput_container\'>', $content);
                // Add .form-file-input class to the file-input
                $content = str_replace('type=\'file\' class=\'medium\'', 'type=\'file\' class=\'form-file-input\'', $content);
                // Add javascript to show filename after upload.
                $content .= '<script>
                    document.getElementById(\'input_' . $form_id . '_' . $field['id'] . '\').addEventListener(\'change\', function (e) {
                        var fileName = e.target.files[0].name;';
                if ('post_image' === $field['type']) {
                    $content .= 'var fileLabel = e.target.parentElement.parentElement.previousElementSibling;';
                } else {
                    $content .= 'var fileLabel = e.target.parentElement.previousElementSibling;';
                }
                $content .= 'fileLabel.innerText = fileName;';
                // If Preview is enabled add javascript to show image preview.
                if (strpos($field['cssClass'], 'preview') !== false) {
                    $content .= 'var input = e.target;
                        var reader = new FileReader();
                        reader.onload = function () {
                            var dataURL = reader.result;
                            var output = document.getElementById(\'output_' . $form_id . '_' . $field['id'] . '\');
                            output.src = dataURL;
                            output.className = \'preview_img\';
                        };
                        reader.readAsDataURL(input.files[0]);';
                }
                $content .= '})
                </script>';

                // Mutli file upload
            } else {
                $content = str_replace('class=\'button', 'class=\'btn btn-primary btn-sm', $content);
            }
        }
        return $content;

    }, 10, 5);

    /** Change the main validation message. */
    add_filter('gform_validation_message', function ($message, $form) {
        return '<div class=\'validation_error alert alert-danger\'>' . esc_html__('There was a problem with your submission.', 'gravityforms') . ' ' . esc_html__('Errors have been highlighted below.', 'gravityforms') . '</div>'; // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
    }, 10, 2);

    /** Change classes on Submit button. */
    add_filter('gform_submit_button', function ($button, $form) {
        $button = str_replace('class=\'gform_button', 'class=\'gform_button btn btn-primary', $button);
        return $button;
    }, 10, 2);

    /** Change classes on Next button. */
    add_filter('gform_next_button', function ($button, $form) {
        $button = str_replace('class=\'gform_next_button', 'class=\'gform_next_button btn btn-secondary', $button);
        return $button;
    }, 10, 2);

    /** Change classes on Previous button. */
    add_filter('gform_previous_button', function ($button, $form) {
        $button = str_replace('class=\'gform_previous_button', 'class=\'gform_previous_button btn btn-outline-secondary', $button);
        return $button;
    }, 10, 2);

    /** Change classes on progressbars */
    add_filter('gform_progress_bar', function ($progress_bar, $form, $confirmation_message) {
        $progress_bar = str_replace('progress_wrapper', 'progress_wrapper mb-3', $progress_bar);
        $progress_bar = str_replace('gf_progressbar', 'gf_progressbar progress', $progress_bar);
        $progress_bar = str_replace('progress_percentage', 'progress_percentage progress-bar progress-bar-striped progress-bar-animated', $progress_bar);
        $progress_bar = str_replace('percentbar_blue', 'percentbar_blue bg-primary', $progress_bar);
        $progress_bar = str_replace('percentbar_gray', 'percentbar_gray bg-secondary', $progress_bar);
        $progress_bar = str_replace('percentbar_green', 'percentbar_green bg-success', $progress_bar);
        $progress_bar = str_replace('percentbar_orange', 'percentbar_orange bg-warning', $progress_bar);
        $progress_bar = str_replace('percentbar_red', 'percentbar_red bg-danger', $progress_bar);
        return $progress_bar;
    }, 10, 3);

    /** Hide Gravityforms Spinner. */
    add_filter('gform_ajax_spinner_url', function () {
        return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
    });
}
