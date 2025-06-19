/**
 * POS Theme JavaScript
 * Handles theme-specific functionality for the POS interface
 */

(function($) {
    'use strict';
    
    var PosTheme = {
        
        init: function() {
            this.bindEvents();
            this.setupFullscreen();
            this.setupTouchHandlers();
            this.setupFormValidation();
            this.setupAutoLogout();
        },
        
        bindEvents: function() {
            // Handle store/register selection
            $('#yith-pos-store-register-form__store').on('change', this.handleStoreChange);
            
            // Handle form submissions
            $('.yith-pos-form form').on('submit', this.handleFormSubmit);
            
            // Handle logout links
            $('.logout, .yith-pos-logout-row a').on('click', this.handleLogout);
            
            // Handle escape key
            $(document).on('keydown', this.handleKeydown);
        },
        
        handleStoreChange: function() {
            var storeId = $(this).val();
            var $registerSelect = $('#yith-pos-store-register-form__register');
            
            // Clear register options
            $registerSelect.empty().append('<option value="">' + posTheme.strings.loading + '</option>');
            
            if (storeId && window.yithPosStores) {
                var store = window.yithPosStores.find(function(s) { return s.id == storeId; });
                if (store && store.registers) {
                    $registerSelect.empty().append('<option value="">Choose a Register</option>');
                    store.registers.forEach(function(register) {
                        $registerSelect.append('<option value="' + register.id + '">' + register.name + '</option>');
                    });
                }
            }
        },
        
        handleFormSubmit: function(e) {
            var $form = $(this);
            var $submit = $form.find('button[type="submit"]');
            
            // Basic validation
            var isValid = true;
            $form.find('select[required], input[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('error');
                } else {
                    $(this).removeClass('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            $submit.prop('disabled', true).addClass('loading');
            
            // Allow form to submit normally
            return true;
        },
        
        handleLogout: function(e) {
            if (confirm('Are you sure you want to logout?')) {
                // Show loading state
                $(this).text(posTheme.strings.loading);
                return true;
            }
            e.preventDefault();
            return false;
        },
        
        handleKeydown: function(e) {
            // Handle escape key
            if (e.keyCode === 27) {
                // Close any open modals or dialogs
                $('.modal, .dialog').removeClass('active');
            }
            
            // Handle F11 for fullscreen
            if (e.keyCode === 122) {
                e.preventDefault();
                PosTheme.toggleFullscreen();
            }
        },
        
        setupFullscreen: function() {
            // Add fullscreen support for POS
            if (document.documentElement.requestFullscreen) {
                // Add fullscreen button if needed
                if ($('#yith-pos-root').length) {
                    // POS is loaded, enable fullscreen mode
                    this.enterFullscreen();
                }
            }
        },
        
        toggleFullscreen: function() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                document.documentElement.requestFullscreen().catch(function(err) {
                    console.log('Error attempting to enable fullscreen:', err);
                });
            }
        },
        
        enterFullscreen: function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(function(err) {
                    console.log('Error entering fullscreen:', err);
                });
            }
        },
        
        setupTouchHandlers: function() {
            if (posTheme.isTouch === 'true') {
                // Add touch-specific classes
                $('body').addClass('touch-device');
                
                // Improve touch targets
                $('.yith-pos-form-row button, .yith-pos-form-row select').addClass('touch-target');
                
                // Handle touch events for better UX
                $(document).on('touchstart', '.touch-target', function() {
                    $(this).addClass('touching');
                });
                
                $(document).on('touchend', '.touch-target', function() {
                    var $this = $(this);
                    setTimeout(function() {
                        $this.removeClass('touching');
                    }, 150);
                });
            }
        },
        
        setupFormValidation: function() {
            // Real-time validation
            $('.yith-pos-form input, .yith-pos-form select').on('blur', function() {
                var $this = $(this);
                var value = $this.val();
                
                if ($this.prop('required') && !value) {
                    $this.addClass('error');
                } else {
                    $this.removeClass('error');
                }
            });
            
            // Clear errors on focus
            $('.yith-pos-form input, .yith-pos-form select').on('focus', function() {
                $(this).removeClass('error');
            });
        },
        
        setupAutoLogout: function() {
            // Auto-logout after inactivity (30 minutes)
            var inactivityTime = 30 * 60 * 1000; // 30 minutes
            var inactivityTimer;
            
            function resetInactivityTimer() {
                clearTimeout(inactivityTimer);
                inactivityTimer = setTimeout(function() {
                    if (confirm('You have been inactive for 30 minutes. Do you want to stay logged in?')) {
                        resetInactivityTimer();
                    } else {
                        window.location.href = window.location.href.split('?')[0] + '?yith-pos-user-logout=true';
                    }
                }, inactivityTime);
            }
            
            // Only enable auto-logout on POS pages
            if ($('body').hasClass('yith-pos-page')) {
                // Reset timer on user activity
                $(document).on('mousedown keydown scroll touchstart', resetInactivityTimer);
                resetInactivityTimer();
            }
        },
        
        showMessage: function(message, type) {
            type = type || 'info';
            var $message = $('<div class="pos-message pos-message-' + type + '">' + message + '</div>');
            
            $('body').append($message);
            
            setTimeout(function() {
                $message.addClass('show');
            }, 100);
            
            setTimeout(function() {
                $message.removeClass('show');
                setTimeout(function() {
                    $message.remove();
                }, 300);
            }, 3000);
        },
        
        checkConnection: function() {
            // Check if we can reach the server
            $.ajax({
                url: posTheme.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'pos_theme_action',
                    pos_action: 'check_status',
                    nonce: posTheme.nonce
                },
                timeout: 5000
            }).fail(function() {
                PosTheme.showMessage('Connection lost. Please check your internet connection.', 'error');
            });
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        PosTheme.init();
        
        // Check connection every 5 minutes
        setInterval(PosTheme.checkConnection, 5 * 60 * 1000);
    });
    
    // Make PosTheme globally available
    window.PosTheme = PosTheme;
    
})(jQuery);
