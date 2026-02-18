/**
 * Advanced Shortcode Builder JavaScript
 */

(function($) {
    'use strict';

    // Main Shortcode Builder class
    var TwitchShortcodeBuilder = {
        currentShortcode: null,
        currentParameters: {},
        autoRefreshEnabled: true,
        refreshTimeout: null,

        init: function() {
            this.bindEvents();
            this.initCategories();
            this.initPresets();
            this.showWelcomeMessage();
        },

        bindEvents: function() {
            var self = this;

            // Category tabs
            $(document).on('click', '.twitch-category-tab', function(e) {
                e.preventDefault();
                var category = $(this).data('category');
                self.switchCategory(category);
            });

            // Shortcode selection
            $(document).on('click', '.twitch-shortcode-item', function(e) {
                e.preventDefault();
                var shortcode = $(this).data('shortcode');
                self.selectShortcode(shortcode);
            });

            // Preset selection
            $(document).on('click', '.twitch-preset-btn', function(e) {
                e.preventDefault();
                var preset = $(this).data('preset');
                self.loadPreset(preset);
            });

            // Form input changes
            $(document).on('input change', '.twitch-builder-form input, .twitch-builder-form select, .twitch-builder-form textarea', function() {
                self.updateCurrentParameters();
                if (self.autoRefreshEnabled) {
                    self.debouncedRefresh();
                }
            });

            // Generate button
            $(document).on('click', '.twitch-generate-btn', function(e) {
                e.preventDefault();
                self.generateShortcode();
            });

            // Preview button
            $(document).on('click', '.twitch-preview-btn', function(e) {
                e.preventDefault();
                self.updatePreview();
            });

            // Copy buttons
            $(document).on('click', '.twitch-builder-copy, .twitch-output-copy, .twitch-copy-shortcode, .twitch-copy-preset', function(e) {
                e.preventDefault();
                var $button = $(this);
                var code = $button.data('code') || self.getCurrentOutput();
                self.copyToClipboard(code, $button);
            });

            // Reset button
            $(document).on('click', '.twitch-builder-reset', function(e) {
                e.preventDefault();
                self.resetBuilder();
            });

            // Test button
            $(document).on('click', '.twitch-output-test', function(e) {
                e.preventDefault();
                self.testShortcode();
            });

            // Preview toggle
            $(document).on('change', '.twitch-preview-toggle input', function() {
                self.autoRefreshEnabled = $(this).is(':checked');
            });

            // Refresh preview
            $(document).on('click', '.twitch-preview-refresh', function(e) {
                e.preventDefault();
                self.updatePreview();
            });

            // Use preset buttons
            $(document).on('click', '.twitch-use-preset', function(e) {
                e.preventDefault();
                var preset = $(this).data('preset');
                self.loadPreset(preset);
            });

            // Generator form inputs
            $(document).on('input change', '.twitch-generator-form input, .twitch-generator-form select, .twitch-generator-form textarea', function() {
                var $generator = $(this).closest('.twitch-shortcode-generator');
                self.updateGeneratorOutput($generator);
            });

            // Generator generate button
            $(document).on('click', '.twitch-generate-btn', function(e) {
                e.preventDefault();
                var $generator = $(this).closest('.twitch-shortcode-generator');
                self.updateGeneratorOutput($generator);
                self.showMessage('Shortcode generated successfully!', 'success');
            });

            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Ctrl/Cmd + Enter to generate
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    if ($('.twitch-builder-form').length) {
                        self.generateShortcode();
                    }
                }

                // Ctrl/Cmd + R to reset
                if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                    e.preventDefault();
                    self.resetBuilder();
                }
            });
        },

        initCategories: function() {
            // Set default category
            var defaultCategory = $('.twitch-shortcode-builder').data('default-category') || 'stream';
            this.switchCategory(defaultCategory);
        },

        initPresets: function() {
            // Initialize preset functionality
            this.loadSavedPresets();
        },

        switchCategory: function(category) {
            // Update active tab
            $('.twitch-category-tab').removeClass('active');
            $('.twitch-category-tab[data-category="' + category + '"]').addClass('active');

            // Filter shortcodes
            $('.twitch-shortcode-item').hide();
            $('.twitch-shortcode-item[data-category="' + category + '"]').show();

            // Update URL hash for deep linking
            if (window.history.replaceState) {
                window.history.replaceState(null, null, '#' + category);
            }
        },

        selectShortcode: function(shortcodeKey) {
            var self = this;

            // Update selection
            $('.twitch-shortcode-item').removeClass('selected');
            $('.twitch-shortcode-item[data-shortcode="' + shortcodeKey + '"]').addClass('selected');

            // Set current shortcode
            this.currentShortcode = shortcodeKey;
            this.currentParameters = {};

            // Load shortcode definition
            var shortcodeData = twitchShortcodeBuilder.shortcodes[shortcodeKey];
            if (!shortcodeData) {
                this.showMessage('Shortcode definition not found', 'error');
                return;
            }

            // Build form
            this.buildShortcodeForm(shortcodeData);

            // Update preview
            this.updatePreview();

            // Scroll to form
            $('.twitch-builder-form')[0].scrollIntoView({ behavior: 'smooth' });
        },

        buildShortcodeForm: function(shortcodeData) {
            var self = this;
            var $form = $('.twitch-builder-form');

            // Clear existing form
            $form.empty();

            // Add form header
            var headerHtml = '<div class="twitch-form-header">' +
                '<h4><span class="twitch-form-icon">' + shortcodeData.icon + '</span>' + shortcodeData.name + '</h4>' +
                '<p>' + shortcodeData.description + '</p>' +
                '</div>';

            $form.append(headerHtml);

            // Build form fields
            $.each(shortcodeData.parameters, function(paramKey, param) {
                var fieldHtml = self.buildFormField(paramKey, param);
                $form.append(fieldHtml);
            });

            // Add generator actions
            var actionsHtml = '<div class="twitch-generator-actions">' +
                '<button class="twitch-generate-btn">' +
                '<span class="twitch-btn-icon">⚡</span>' +
                'Generate Shortcode' +
                '</button>' +
                '<button class="twitch-preview-btn">' +
                '<span class="twitch-btn-icon">👁️</span>' +
                'Preview' +
                '</button>' +
                '</div>';

            $form.append(actionsHtml);
        },

        buildFormField: function(paramKey, param) {
            var fieldId = 'twitch-param-' + paramKey;
            var isRequired = param.required ? 'required' : '';
            var defaultValue = param.default !== undefined ? param.default : '';

            var fieldHtml = '<div class="twitch-form-field twitch-field-' + param.type + '">';

            // Label
            fieldHtml += '<label for="' + fieldId + '">';
            fieldHtml += param.label;
            if (param.required) {
                fieldHtml += ' <span class="twitch-required">*</span>';
            }
            fieldHtml += '</label>';

            // Input field based on type
            switch (param.type) {
                case 'text':
                case 'email':
                case 'url':
                    fieldHtml += '<input type="' + param.type + '" ' +
                        'id="' + fieldId + '" ' +
                        'data-param="' + paramKey + '" ' +
                        'placeholder="' + (param.placeholder || '') + '" ' +
                        'value="' + defaultValue + '" ' +
                        isRequired + '>';
                    break;

                case 'number':
                    var min = param.min !== undefined ? 'min="' + param.min + '" ' : '';
                    var max = param.max !== undefined ? 'max="' + param.max + '" ' : '';
                    fieldHtml += '<input type="number" ' +
                        'id="' + fieldId + '" ' +
                        'data-param="' + paramKey + '" ' +
                        'value="' + defaultValue + '" ' +
                        min + max + isRequired + '>';
                    break;

                case 'textarea':
                    fieldHtml += '<textarea id="' + fieldId + '" ' +
                        'data-param="' + paramKey + '" ' +
                        'placeholder="' + (param.placeholder || '') + '" ' +
                        'rows="3" ' + isRequired + '>' + defaultValue + '</textarea>';
                    break;

                case 'select':
                    fieldHtml += '<select id="' + fieldId + '" data-param="' + paramKey + '" ' + isRequired + '>';
                    $.each(param.options, function(optionKey, optionLabel) {
                        var selected = (defaultValue === optionKey) ? 'selected' : '';
                        fieldHtml += '<option value="' + optionKey + '" ' + selected + '>' + optionLabel + '</option>';
                    });
                    fieldHtml += '</select>';
                    break;

                case 'multiselect':
                    fieldHtml += '<select id="' + fieldId + '" data-param="' + paramKey + '" multiple ' + isRequired + '>';
                    $.each(param.options, function(optionKey, optionLabel) {
                        var selected = (defaultValue && defaultValue.includes(optionKey)) ? 'selected' : '';
                        fieldHtml += '<option value="' + optionKey + '" ' + selected + '>' + optionLabel + '</option>';
                    });
                    fieldHtml += '</select>';
                    break;

                case 'checkbox':
                    var checked = defaultValue ? 'checked' : '';
                    fieldHtml += '<label class="twitch-checkbox-label">' +
                        '<input type="checkbox" ' +
                        'id="' + fieldId + '" ' +
                        'data-param="' + paramKey + '" ' +
                        checked + '>' +
                        '<span class="twitch-checkbox-text">' + param.label + '</span>' +
                        '</label>';
                    break;
            }

            // Description
            if (param.description) {
                fieldHtml += '<p class="twitch-field-description">' + param.description + '</p>';
            }

            fieldHtml += '</div>';

            return fieldHtml;
        },

        updateCurrentParameters: function() {
            var self = this;
            this.currentParameters = {};

            $('.twitch-builder-form input, .twitch-builder-form select, .twitch-builder-form textarea').each(function() {
                var $field = $(this);
                var paramKey = $field.data('param');
                var value;

                if ($field.is(':checkbox')) {
                    value = $field.is(':checked');
                } else if ($field.is('select[multiple]')) {
                    value = $field.val() || [];
                } else {
                    value = $field.val();
                }

                // Only include non-empty values (except for boolean false)
                if (value !== '' && value !== null && value !== undefined) {
                    self.currentParameters[paramKey] = value;
                }
            });
        },

        generateShortcode: function() {
            var self = this;

            if (!this.currentShortcode) {
                this.showMessage('Please select a shortcode first', 'error');
                return;
            }

            this.updateCurrentParameters();

            // Validate parameters
            this.validateParameters(function(isValid, errors) {
                if (!isValid) {
                    var errorMessage = 'Validation errors: ' + errors.join(', ');
                    self.showMessage(errorMessage, 'error');
                    return;
                }

                // Generate shortcode via AJAX
                self.generateShortcodeAjax();
            });
        },

        validateParameters: function(callback) {
            var self = this;
            var shortcodeData = twitchShortcodeBuilder.shortcodes[this.currentShortcode];
            var errors = [];

            // Check required fields
            $.each(shortcodeData.parameters, function(paramKey, param) {
                if (param.required) {
                    var value = self.currentParameters[paramKey];
                    if (!value || value === '' || (Array.isArray(value) && value.length === 0)) {
                        errors.push(param.label + ' is required');
                    }
                }
            });

            // Type validation
            $.each(shortcodeData.parameters, function(paramKey, param) {
                if (self.currentParameters[paramKey] !== undefined) {
                    var value = self.currentParameters[paramKey];
                    var error = self.validateParameterValue(param, value);
                    if (error) {
                        errors.push(error);
                    }
                }
            });

            callback(errors.length === 0, errors);
        },

        validateParameterValue: function(param, value) {
            switch (param.type) {
                case 'email':
                    if (!this.isValidEmail(value)) {
                        return param.label + ' must be a valid email address';
                    }
                    break;
                case 'url':
                    if (!this.isValidUrl(value)) {
                        return param.label + ' must be a valid URL';
                    }
                    break;
                case 'number':
                    if (isNaN(value)) {
                        return param.label + ' must be a number';
                    }
                    if (param.min !== undefined && value < param.min) {
                        return param.label + ' must be at least ' + param.min;
                    }
                    if (param.max !== undefined && value > param.max) {
                        return param.label + ' must be no more than ' + param.max;
                    }
                    break;
            }
            return null;
        },

        isValidEmail: function(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        isValidUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        },

        generateShortcodeAjax: function() {
            var self = this;

            $.ajax({
                url: twitchShortcodeBuilder.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_shortcode_builder',
                    builder_action: 'generate_shortcode',
                    shortcode: this.currentShortcode,
                    parameters: this.currentParameters,
                    nonce: twitchShortcodeBuilder.nonce
                },
                beforeSend: function() {
                    self.showLoading(true);
                },
                success: function(response) {
                    if (response.success) {
                        self.updateOutput(response.data.shortcode, response.data.formatted);
                        self.showMessage('Shortcode generated successfully!', 'success');
                    } else {
                        self.showMessage('Failed to generate shortcode', 'error');
                    }
                },
                error: function() {
                    self.showMessage('AJAX error occurred', 'error');
                },
                complete: function() {
                    self.showLoading(false);
                }
            });
        },

        updateOutput: function(shortcode, formatted) {
            $('.twitch-shortcode-output').html(formatted || '<code>' + this.escapeHtml(shortcode) + '</code>');
            $('.twitch-output-copy').data('code', shortcode);
        },

        getCurrentOutput: function() {
            return $('.twitch-shortcode-output code').text() || '';
        },

        updatePreview: function() {
            var self = this;

            if (!this.currentShortcode) {
                $('.twitch-preview-content').html('<div class="twitch-preview-placeholder">' +
                    '<span class="twitch-preview-icon">👀</span>' +
                    '<p>Select a shortcode to see the preview</p>' +
                    '</div>');
                return;
            }

            $.ajax({
                url: twitchShortcodeBuilder.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'twitch_shortcode_builder',
                    builder_action: 'preview_shortcode',
                    shortcode: this.currentShortcode,
                    parameters: this.currentParameters,
                    nonce: twitchShortcodeBuilder.nonce
                },
                beforeSend: function() {
                    $('.twitch-preview-content').html('<div class="twitch-preview-loading">Loading preview...</div>');
                },
                success: function(response) {
                    if (response.success) {
                        $('.twitch-preview-content').html(response.data.preview);
                    } else {
                        $('.twitch-preview-content').html('<div class="twitch-preview-error">Preview not available</div>');
                    }
                },
                error: function() {
                    $('.twitch-preview-content').html('<div class="twitch-preview-error">Failed to load preview</div>');
                }
            });
        },

        loadPreset: function(presetKey) {
            var preset = twitchShortcodeBuilder.presets[presetKey];

            if (!preset) {
                this.showMessage('Preset not found', 'error');
                return;
            }

            // Set shortcode and parameters
            this.selectShortcode(preset.shortcode);
            this.currentParameters = preset.parameters || {};

            // Update form fields
            this.populateFormFields(this.currentParameters);

            // Update preview and output
            this.updatePreview();
            this.generateShortcodeAjax();

            this.showMessage('Preset "' + preset.name + '" loaded successfully!', 'success');
        },

        populateFormFields: function(parameters) {
            var self = this;

            $.each(parameters, function(paramKey, value) {
                var $field = $('[data-param="' + paramKey + '"]');

                if ($field.is(':checkbox')) {
                    $field.prop('checked', value);
                } else if ($field.is('select[multiple]')) {
                    $field.val(value);
                } else {
                    $field.val(value);
                }
            });
        },

        resetBuilder: function() {
            this.currentShortcode = null;
            this.currentParameters = {};

            // Clear form
            $('.twitch-builder-form').html('<div class="twitch-form-header">' +
                '<h4>Select a shortcode to configure</h4>' +
                '<p>Choose from the sidebar or use presets below</p>' +
                '</div>');

            // Clear output
            $('.twitch-shortcode-output').html('<code>// Select a shortcode to generate code</code>');

            // Clear preview
            $('.twitch-preview-content').html('<div class="twitch-preview-placeholder">' +
                '<span class="twitch-preview-icon">👀</span>' +
                '<p>Select a shortcode to see the preview</p>' +
                '</div>');

            // Clear selection
            $('.twitch-shortcode-item').removeClass('selected');

            this.showMessage('Builder reset successfully', 'success');
        },

        testShortcode: function() {
            var shortcode = this.getCurrentOutput();

            if (!shortcode) {
                this.showMessage('No shortcode to test', 'error');
                return;
            }

            // Open test window with shortcode
            var testUrl = window.location.href.split('#')[0] + '&twitch_test_shortcode=' + encodeURIComponent(shortcode);
            window.open(testUrl, '_blank', 'width=800,height=600');
        },

        copyToClipboard: function(text, $button) {
            var self = this;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    self.showCopySuccess($button);
                });
            } else {
                // Fallback for older browsers
                var textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    document.execCommand('copy');
                    self.showCopySuccess($button);
                } catch (err) {
                    self.showMessage('Failed to copy to clipboard', 'error');
                }

                document.body.removeChild(textArea);
            }
        },

        showCopySuccess: function($button) {
            var originalText = $button.html();
            $button.html('<span class="twitch-btn-icon">✓</span>Copied!');

            setTimeout(function() {
                $button.html(originalText);
            }, 2000);

            this.showMessage(twitchShortcodeBuilder.strings.copySuccess, 'success');
        },

        updateGeneratorOutput: function($generator) {
            var shortcode = $generator.data('shortcode');
            var parameters = {};

            $generator.find('input, select, textarea').each(function() {
                var $field = $(this);
                var paramKey = $field.data('param');
                var value;

                if ($field.is(':checkbox')) {
                    value = $field.is(':checked');
                } else if ($field.is('select[multiple]')) {
                    value = $field.val() || [];
                } else {
                    value = $field.val();
                }

                if (value !== '' && value !== null && value !== undefined) {
                    parameters[paramKey] = value;
                }
            });

            var generatedShortcode = this.buildShortcode(shortcode, parameters);
            $generator.find('.twitch-shortcode-result').html('<code>' + this.escapeHtml(generatedShortcode) + '</code>');
            $generator.find('.twitch-copy-shortcode').data('code', generatedShortcode);
        },

        buildShortcode: function(shortcode, parameters) {
            var atts = [];

            $.each(parameters, function(key, value) {
                if (Array.isArray(value)) {
                    value = value.join(',');
                } else if (typeof value === 'boolean') {
                    value = value ? 'true' : 'false';
                }
                atts.push(key + '="' + value + '"');
            });

            var attsString = atts.length ? ' ' + atts.join(' ') : '';
            return '[' + shortcode + attsString + ']';
        },

        loadSavedPresets: function() {
            // Load user-saved presets from localStorage
            var savedPresets = localStorage.getItem('twitch_shortcode_presets');
            if (savedPresets) {
                try {
                    var presets = JSON.parse(savedPresets);
                    // Merge with default presets
                    twitchShortcodeBuilder.presets = $.extend({}, twitchShortcodeBuilder.presets, presets);
                } catch (e) {
                    console.warn('Failed to load saved presets');
                }
            }
        },

        debouncedRefresh: function() {
            var self = this;
            clearTimeout(this.refreshTimeout);
            this.refreshTimeout = setTimeout(function() {
                self.updatePreview();
            }, 500);
        },

        showLoading: function(show) {
            if (show) {
                $('.twitch-builder-main').addClass('twitch-builder-loading');
            } else {
                $('.twitch-builder-main').removeClass('twitch-builder-loading');
            }
        },

        showMessage: function(message, type) {
            var $message = $('<div class="twitch-builder-message ' + type + '">' + message + '</div>');

            $('body').append($message);

            setTimeout(function() {
                $message.fadeOut(function() {
                    $message.remove();
                });
            }, 3000);
        },

        showWelcomeMessage: function() {
            // Show welcome message on first visit
            if (!localStorage.getItem('twitch_builder_welcome_shown')) {
                setTimeout(function() {
                    TwitchShortcodeBuilder.showMessage('Welcome to the Twitch Shortcode Builder! Select a shortcode to get started.', 'success');
                }, 1000);
                localStorage.setItem('twitch_builder_welcome_shown', 'true');
            }
        },

        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        TwitchShortcodeBuilder.init();
    });

    // Expose globally
    window.TwitchShortcodeBuilder = TwitchShortcodeBuilder;

})(jQuery);
