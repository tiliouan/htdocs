/**
 * YITH POS Cash Drawer Frontend JavaScript
 *
 * Handles cash drawer functionality on the frontend POS interface.
 *
 * @package YITH\POS\Assets
 * @version 1.0.0
 */

(function($, window, document) {
    'use strict';

    // Cash drawer controller
    var YithPosCashDrawer = {
        // Configuration
        config: {
            enabled: false,
            pin: 'pin2',
            autoOpen: true,
            ajaxUrl: '',
            nonce: ''
        },

        // ESC/POS commands for cash drawer
        commands: {
            pin2: '\x1B\x70\x00\x19\x64',
            pin5: '\x1B\x70\x01\x19\x64'
        },

        // Initialize the cash drawer functionality
        init: function(options) {
            if (options) {
                $.extend(this.config, options);
            }

            if (!this.config.enabled) {
                console.log('Cash drawer functionality is disabled');
                return;
            }

            this.bindEvents();
            this.addManualControls();
            this.hookIntoPrintEvents();

            console.log('YITH POS Cash Drawer initialized');
        },

        // Bind event handlers
        bindEvents: function() {
            var self = this;

            // Manual cash drawer open button
            $(document).on('click', '.yith-pos-open-drawer-btn', function(e) {
                e.preventDefault();
                self.openDrawer();
            });

            // Receipt print events
            $(document).on('click', '[data-action="print-receipt"]', function() {
                if (self.config.autoOpen) {
                    setTimeout(function() {
                        self.openDrawer();
                    }, 500);
                }
            });

            // Print button in order receipt print control
            $(document).on('click', '.yith-pos-order-receipt-print-control__standard-print', function() {
                if (self.config.autoOpen) {
                    setTimeout(function() {
                        self.openDrawer();
                    }, 300);
                }
            });

            // Browser print event
            window.addEventListener('beforeprint', function() {
                if (self.config.autoOpen) {
                    self.openDrawer();
                }
            });

            // Custom POS print event
            $(document).on('yith-pos-print-receipt', function() {
                if (self.config.autoOpen) {
                    self.openDrawer();
                }
            });
        },

        // Add manual cash drawer controls to the interface
        addManualControls: function() {
            var buttonHtml = '<button type="button" class="button yith-pos-open-drawer-btn" title="' + 
                yith_pos_cash_drawer_l10n.open_drawer + '">' +
                '<span class="dashicons dashicons-unlock" style="margin-right: 5px;"></span>' +
                yith_pos_cash_drawer_l10n.open_drawer +
                '</button>';

            // Add to various locations in the POS interface
            this.addButtonToReceiptControls(buttonHtml);
            this.addButtonToRegisterActions(buttonHtml);
        },

        // Add button to receipt print controls
        addButtonToReceiptControls: function(buttonHtml) {
            var $printControls = $('.yith-pos-order-receipt-print-control');
            if ($printControls.length > 0) {
                $printControls.append('<div style="margin-top: 10px;">' + buttonHtml + '</div>');
            }
        },

        // Add button to register actions
        addButtonToRegisterActions: function(buttonHtml) {
            var $registerActions = $('.yith-pos-register-actions, .pos-header__actions');
            if ($registerActions.length > 0) {
                $registerActions.append(buttonHtml);
            }
        },        // Hook into existing print functionality
        hookIntoPrintEvents: function() {
            var self = this;

            // Check if fullscreen print manager is available
            if (typeof window.YithPosFullscreenPrint !== 'undefined') {
                // Hook into fullscreen print events
                $(document).on('yith-pos-print-completed', function() {
                    if (self.config.autoOpen) {
                        setTimeout(function() {
                            self.openDrawer();
                        }, 100);
                    }
                });
            } else {
                // Fallback to original print interceptor
                var originalPrint = window.print;

                // Override window.print to trigger cash drawer
                window.print = function() {
                    if (self.config.autoOpen) {
                        self.openDrawer();
                    }
                    originalPrint.call(window);
                };
            }

            // Hook into React component events if available
            if (window.wp && window.wp.hooks) {
                window.wp.hooks.addAction('yith.pos.print.receipt', 'yith-pos-cash-drawer', function() {
                    if (self.config.autoOpen) {
                        self.openDrawer();
                    }
                });
            }
        },

        // Main function to open the cash drawer
        openDrawer: function() {
            var self = this;

            if (!this.config.enabled) {
                console.log('Cash drawer is disabled');
                return false;
            }

            console.log('Opening cash drawer...');

            // Try different methods to open the drawer
            var success = false;

            // Method 1: Web Serial API (modern browsers)
            if (this.supportsWebSerial()) {
                this.openViaWebSerial().then(function(result) {
                    if (result) {
                        console.log('Cash drawer opened via Web Serial API');
                        self.logDrawerEvent('web_serial');
                    } else {
                        self.fallbackToAjax();
                    }
                }).catch(function() {
                    self.fallbackToAjax();
                });
                return true;
            }

            // Method 2: Try to append ESC/POS command to print job
            if (this.appendToPrintJob()) {
                success = true;
                this.logDrawerEvent('print_job');
            }

            // Method 3: AJAX fallback
            if (!success) {
                this.fallbackToAjax();
            }

            return true;
        },

        // Check if Web Serial API is supported
        supportsWebSerial: function() {
            return 'serial' in navigator;
        },

        // Open drawer via Web Serial API
        openViaWebSerial: function() {
            var self = this;

            return new Promise(function(resolve, reject) {
                if (!self.supportsWebSerial()) {
                    resolve(false);
                    return;
                }

                navigator.serial.requestPort()
                    .then(function(port) {
                        return port.open({ baudRate: 9600 });
                    })
                    .then(function(port) {
                        var writer = port.writable.getWriter();
                        var command = self.commands[self.config.pin] || self.commands.pin2;
                        var encoder = new TextEncoder();
                        
                        return writer.write(encoder.encode(command))
                            .then(function() {
                                writer.releaseLock();
                                return port.close();
                            });
                    })
                    .then(function() {
                        resolve(true);
                    })
                    .catch(function(error) {
                        console.error('Web Serial error:', error);
                        resolve(false);
                    });
            });
        },

        // Try to append ESC/POS command to current print job
        appendToPrintJob: function() {
            try {
                var command = this.commands[this.config.pin] || this.commands.pin2;
                
                // Try to add the command to any receipt content being printed
                var $printableContent = $('#order-receipt-print, .receipt-container, .printable');
                if ($printableContent.length > 0) {
                    // Add hidden element with ESC/POS command
                    var $escCommand = $('<div style="display: none; white-space: pre;">' + command + '</div>');
                    $printableContent.prepend($escCommand);
                    
                    // Remove after a short delay
                    setTimeout(function() {
                        $escCommand.remove();
                    }, 1000);
                    
                    return true;
                }
            } catch (error) {
                console.error('Error appending to print job:', error);
            }
            
            return false;
        },

        // Fallback to AJAX method
        fallbackToAjax: function() {
            var self = this;

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'yith_pos_open_cash_drawer',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Cash drawer opened via server');
                        self.logDrawerEvent('ajax');
                        self.showNotification(yith_pos_cash_drawer_l10n.drawer_opened, 'success');
                    } else {
                        console.error('Server error:', response.data);
                        self.showNotification(yith_pos_cash_drawer_l10n.drawer_error + ': ' + response.data, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    self.showNotification(yith_pos_cash_drawer_l10n.connection_error, 'error');
                }
            });
        },

        // Log drawer opening event
        logDrawerEvent: function(method) {
            if (console && console.log) {
                console.log('Cash drawer opened via: ' + method);
            }

            // Send analytics event if tracking is available
            if (window.gtag) {
                window.gtag('event', 'cash_drawer_open', {
                    'method': method,
                    'event_category': 'pos'
                });
            }
        },

        // Show notification to user
        showNotification: function(message, type) {
            type = type || 'info';

            // Try to use existing notification system
            if (window.YithPosNotifications && window.YithPosNotifications.show) {
                window.YithPosNotifications.show(message, type);
                return;
            }

            // Fallback to simple alert or console log
            if (type === 'error') {
                if (confirm(message + '\n\n' + yith_pos_cash_drawer_l10n.try_manual)) {
                    this.showManualInstructions();
                }
            } else {
                // Create a simple notification
                var $notification = $('<div class="yith-pos-notification yith-pos-notification-' + type + '">' + 
                    message + '</div>');
                
                $notification.css({
                    'position': 'fixed',
                    'top': '20px',
                    'right': '20px',
                    'background': type === 'success' ? '#4CAF50' : '#f44336',
                    'color': 'white',
                    'padding': '10px 20px',
                    'border-radius': '4px',
                    'z-index': 99999
                });

                $('body').append($notification);

                setTimeout(function() {
                    $notification.fadeOut(function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        },

        // Show manual instructions for opening drawer
        showManualInstructions: function() {
            var instructions = yith_pos_cash_drawer_l10n.manual_instructions;
            alert(instructions);
        },

        // Test cash drawer functionality
        test: function() {
            console.log('Testing cash drawer...');
            this.openDrawer();
        }
    };

    // Expose to global scope
    window.YithPosCashDrawer = YithPosCashDrawer;

    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        // Check if configuration is available
        if (typeof yith_pos_cash_drawer_config !== 'undefined') {
            YithPosCashDrawer.init(yith_pos_cash_drawer_config);
        }
    });

})(jQuery, window, document);
