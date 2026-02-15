/**
 * KSP Framework Enhancement - Validation Module
 * Real-time form validation with immediate feedback
 */

// Extend existing KSP framework with validation capabilities
(function() {
    'use strict';
    
    // Only extend if KSP exists
    if (typeof window.KSP === 'undefined') {
        console.warn('KSP Framework not found, loading validation module standalone');
        window.KSP = {};
    }
    
    // Validation Module
    KSP.Validation = {
        rules: {},
        messages: {},
        
        // Initialize validation on forms
        init: function() {
            if (typeof $ === 'undefined') return;
            
            var self = this;
            
            // Auto-setup validation on forms with data-validation
            $('form[data-validation]').each(function() {
                self.setupForm($(this));
            });
            
            // Setup real-time validation
            $(document).on('blur', 'input[data-validate], select[data-validate], textarea[data-validate]', function() {
                self.validateField($(this));
            });
            
            $(document).on('input', 'input[data-validate-realtime]', function() {
                self.validateField($(this));
            });
            
            console.log('KSP Validation module initialized');
        },
        
        // Setup validation for a form
        setupForm: function($form) {
            var self = this;
            
            // Add validation on submit
            $form.on('submit', function(e) {
                if (!self.validateForm($(this))) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Setup field validation
            $form.find('input, select, textarea').each(function() {
                var $field = $(this);
                var rules = $field.data('validate');
                if (rules) {
                    self.setupFieldValidation($field, rules);
                }
            });
        },
        
        // Setup validation for individual field
        setupFieldValidation: function($field, rules) {
            var self = this;
            
            // Parse rules
            var ruleList = rules.split('|');
            $field.data('validation-rules', ruleList);
            
            // Add validation container
            if (!$field.next('.validation-message').length) {
                $field.after('<small class="validation-message text-muted"></small>');
            }
        },
        
        // Validate individual field
        validateField: function($field) {
            var rules = $field.data('validation-rules') || [];
            var value = $field.val().trim();
            var fieldName = $field.attr('name') || $field.attr('id');
            var isValid = true;
            var message = '';
            
            for (var i = 0; i < rules.length; i++) {
                var rule = rules[i];
                var result = this.applyRule(rule, value, $field);
                
                if (!result.valid) {
                    isValid = false;
                    message = result.message;
                    break;
                }
            }
            
            this.showFieldValidation($field, isValid, message);
            return isValid;
        },
        
        // Apply validation rule
        applyRule: function(rule, value, $field) {
            var parts = rule.split(':');
            var ruleName = parts[0];
            var ruleParam = parts[1];
            
            switch (ruleName) {
                case 'required':
                    return {
                        valid: value.length > 0,
                        message: 'Field ini wajib diisi'
                    };
                    
                case 'email':
                    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return {
                        valid: !value || emailRegex.test(value),
                        message: 'Format email tidak valid'
                    };
                    
                case 'min':
                    return {
                        valid: value.length >= parseInt(ruleParam),
                        message: 'Minimal ' + ruleParam + ' karakter'
                    };
                    
                case 'max':
                    return {
                        valid: value.length <= parseInt(ruleParam),
                        message: 'Maksimal ' + ruleParam + ' karakter'
                    };
                    
                case 'numeric':
                    return {
                        valid: !value || !isNaN(value),
                        message: 'Hanya angka yang diperbolehkan'
                    };
                    
                case 'phone':
                    var phoneRegex = /^[0-9\-\+\(\)\s]+$/;
                    return {
                        valid: !value || phoneRegex.test(value),
                        message: 'Format nomor telepon tidak valid'
                    };
                    
                case 'nik':
                    var nikRegex = /^[0-9]{16}$/;
                    return {
                        valid: !value || nikRegex.test(value),
                        message: 'NIK harus 16 digit angka'
                    };
                    
                case 'matches':
                    var targetField = $('[name="' + ruleParam + '"]');
                    var targetValue = targetField.val();
                    return {
                        valid: value === targetValue,
                        message: 'Field tidak cocok dengan ' + targetField.attr('placeholder') || ruleParam
                    };
                    
                default:
                    return { valid: true, message: '' };
            }
        },
        
        // Show field validation result
        showFieldValidation: function($field, isValid, message) {
            var $message = $field.next('.validation-message');
            var $formGroup = $field.closest('.form-group, .mb-3');
            
            if (isValid) {
                $field.removeClass('is-invalid').addClass('is-valid');
                $message.removeClass('text-danger').addClass('text-success').text('✓ Valid');
                $formGroup.removeClass('has-error').addClass('has-success');
            } else {
                $field.removeClass('is-valid').addClass('is-invalid');
                $message.removeClass('text-success').addClass('text-danger').text(message);
                $formGroup.removeClass('has-success').addClass('has-error');
            }
        },
        
        // Validate entire form
        validateForm: function($form) {
            var self = this;
            var isValid = true;
            
            $form.find('input[data-validate], select[data-validate], textarea[data-validate]').each(function() {
                if (!self.validateField($(this))) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                // Focus on first invalid field
                $form.find('.is-invalid').first().focus();
                
                // Show form-level error
                if (window.KSP && window.KSP.Notification) {
                    KSP.Notification.show('Mohon perbaiki error yang ditandai', 'error');
                }
            }
            
            return isValid;
        },
        
        // Add custom validation rule
        addRule: function(name, callback, message) {
            this.rules[name] = callback;
            this.messages[name] = message;
        }
    };
    
    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        if (window.KSP && window.KSP.Validation) {
            KSP.Validation.init();
        }
    });
    
})();

console.log('KSP Validation module loaded');
