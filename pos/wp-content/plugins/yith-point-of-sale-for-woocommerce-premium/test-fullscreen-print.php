<?php
/**
 * Test Fullscreen Print Functionality
 * 
 * This file provides a test interface for the fullscreen print system
 * to verify that it works correctly before deploying to production.
 */

// Only allow access if user is logged in and has appropriate permissions
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized access');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YITH POS Fullscreen Print Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f1f1f1;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .test-section h3 {
            margin-top: 0;
            color: #333;
        }
        .button {
            background: #0073aa;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .button:hover {
            background: #005a87;
        }
        .button.secondary {
            background: #666;
        }
        .button.success {
            background: #46b450;
        }
        .button.warning {
            background: #ffb900;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .test-receipt {
            width: 300px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            background: white;
        }
        .test-receipt .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .test-receipt .line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .test-receipt .total {
            border-top: 1px solid #000;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
        }
        .fullscreen-controls {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 10000;
        }
        #fullscreen-indicator {
            position: fixed;
            top: 10px;
            left: 10px;
            padding: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            border-radius: 4px;
            z-index: 10000;
            display: none;
        }
    </style>
</head>
<body>
    <div id="fullscreen-indicator">
        <strong>FULLSCREEN MODE ACTIVE</strong><br>
        Press ESC to exit or click the Exit Fullscreen button
    </div>

    <div class="container">
        <h1>YITH POS Fullscreen Print Test</h1>
        
        <div class="fullscreen-controls">
            <button class="button secondary" onclick="toggleFullscreen()">Toggle Fullscreen</button>
            <button class="button secondary" onclick="exitFullscreen()" style="display: none;" id="exit-fullscreen-btn">Exit Fullscreen</button>
        </div>

        <div class="test-section">
            <h3>1. Browser Compatibility Test</h3>
            <p>Check if the current browser supports the required APIs for fullscreen printing.</p>
            <button class="button" onclick="testBrowserSupport()">Test Browser Support</button>
            <div id="browser-support-result"></div>
        </div>

        <div class="test-section">
            <h3>2. Fullscreen API Test</h3>
            <p>Test entering and exiting fullscreen mode.</p>
            <button class="button" onclick="testFullscreenAPI()">Test Fullscreen API</button>
            <div id="fullscreen-api-result"></div>
        </div>

        <div class="test-section">
            <h3>3. Print Methods Test</h3>
            <p>Test different printing methods to see which works best in your browser.</p>
            <button class="button" onclick="testDirectPrint()">Test Direct Print</button>
            <button class="button" onclick="testIframePrint()">Test IFrame Print</button>
            <button class="button" onclick="testPopupPrint()">Test Popup Print</button>
            <div id="print-methods-result"></div>
        </div>

        <div class="test-section">
            <h3>4. Fullscreen Print Integration Test</h3>
            <p>Test the complete fullscreen printing workflow.</p>
            <button class="button success" onclick="testFullscreenPrint()">Test Fullscreen Print</button>
            <div id="fullscreen-print-result"></div>
        </div>

        <div class="test-section">
            <h3>5. Sample Receipt</h3>
            <p>This is what will be printed during the test.</p>
            <div id="order-receipt-print" class="test-receipt">
                <div class="header">
                    <strong>TEST STORE</strong><br>
                    123 Main Street<br>
                    Test City, TC 12345<br>
                    Phone: (555) 123-4567
                </div>
                <hr>
                <div class="line">
                    <span>Receipt #:</span>
                    <span>TEST-001</span>
                </div>
                <div class="line">
                    <span>Date:</span>
                    <span><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
                <div class="line">
                    <span>Cashier:</span>
                    <span>Test User</span>
                </div>
                <hr>
                <div class="line">
                    <span>Test Product 1</span>
                    <span>$10.00</span>
                </div>
                <div class="line">
                    <span>Test Product 2</span>
                    <span>$15.50</span>
                </div>
                <div class="line">
                    <span>Test Product 3</span>
                    <span>$8.25</span>
                </div>
                <hr>
                <div class="line">
                    <span>Subtotal:</span>
                    <span>$33.75</span>
                </div>
                <div class="line">
                    <span>Tax:</span>
                    <span>$2.70</span>
                </div>
                <div class="line total">
                    <span>TOTAL:</span>
                    <span>$36.45</span>
                </div>
                <hr>
                <div class="line">
                    <span>Payment Method:</span>
                    <span>Cash</span>
                </div>
                <div class="line">
                    <span>Amount Paid:</span>
                    <span>$40.00</span>
                </div>
                <div class="line">
                    <span>Change:</span>
                    <span>$3.55</span>
                </div>
                <hr>
                <div style="text-align: center; margin-top: 20px;">
                    <strong>Thank you for your business!</strong><br>
                    Visit us again soon!
                </div>
            </div>
        </div>

        <div class="test-section">
            <h3>Debug Information</h3>
            <button class="button secondary" onclick="showDebugInfo()">Show Debug Info</button>
            <div id="debug-info" style="display: none;"></div>
        </div>
    </div>

    <script>
        // Test variables
        var testResults = {
            browserSupport: null,
            fullscreenAPI: null,
            printMethods: {},
            fullscreenPrint: null
        };

        // Fullscreen management
        function toggleFullscreen() {
            if (document.fullscreenElement) {
                exitFullscreen();
            } else {
                enterFullscreen();
            }
        }

        function enterFullscreen() {
            var docEl = document.documentElement;
            var requestFullscreen = docEl.requestFullscreen || 
                                  docEl.mozRequestFullScreen || 
                                  docEl.webkitRequestFullscreen || 
                                  docEl.msRequestFullscreen;

            if (requestFullscreen) {
                requestFullscreen.call(docEl).then(function() {
                    console.log('Entered fullscreen mode');
                    updateFullscreenIndicator(true);
                }).catch(function(err) {
                    console.error('Failed to enter fullscreen:', err);
                    showStatus('fullscreen-api-result', 'error', 'Failed to enter fullscreen: ' + err.message);
                });
            } else {
                showStatus('fullscreen-api-result', 'error', 'Fullscreen API not supported');
            }
        }

        function exitFullscreen() {
            var exitFullscreen = document.exitFullscreen || 
                                document.mozCancelFullScreen || 
                                document.webkitExitFullscreen || 
                                document.msExitFullscreen;

            if (exitFullscreen) {
                exitFullscreen.call(document).then(function() {
                    console.log('Exited fullscreen mode');
                    updateFullscreenIndicator(false);
                }).catch(function(err) {
                    console.error('Failed to exit fullscreen:', err);
                });
            }
        }

        function updateFullscreenIndicator(isFullscreen) {
            var indicator = document.getElementById('fullscreen-indicator');
            var exitBtn = document.getElementById('exit-fullscreen-btn');
            
            if (isFullscreen) {
                indicator.style.display = 'block';
                exitBtn.style.display = 'inline-block';
            } else {
                indicator.style.display = 'none';
                exitBtn.style.display = 'none';
            }
        }

        // Listen for fullscreen changes
        document.addEventListener('fullscreenchange', function() {
            updateFullscreenIndicator(!!document.fullscreenElement);
        });

        // Test functions
        function testBrowserSupport() {
            var support = {
                fullscreen: !!(document.documentElement.requestFullscreen || 
                             document.documentElement.mozRequestFullScreen || 
                             document.documentElement.webkitRequestFullscreen || 
                             document.documentElement.msRequestFullscreen),
                print: !!(window.print),
                iframe: true,
                popup: true
            };

            var messages = [];
            var allSupported = true;

            for (var feature in support) {
                if (support[feature]) {
                    messages.push('✓ ' + feature.charAt(0).toUpperCase() + feature.slice(1) + ' API supported');
                } else {
                    messages.push('✗ ' + feature.charAt(0).toUpperCase() + feature.slice(1) + ' API not supported');
                    allSupported = false;
                }
            }

            testResults.browserSupport = support;
            showStatus('browser-support-result', allSupported ? 'success' : 'error', messages.join('<br>'));
        }

        function testFullscreenAPI() {
            if (document.fullscreenElement) {
                showStatus('fullscreen-api-result', 'info', 'Already in fullscreen mode. Click "Exit Fullscreen" to test exit.');
            } else {
                enterFullscreen();
                setTimeout(function() {
                    if (document.fullscreenElement) {
                        showStatus('fullscreen-api-result', 'success', 'Successfully entered fullscreen mode. You can now exit fullscreen to complete the test.');
                        testResults.fullscreenAPI = true;
                    } else {
                        showStatus('fullscreen-api-result', 'error', 'Failed to enter fullscreen mode');
                        testResults.fullscreenAPI = false;
                    }
                }, 500);
            }
        }

        function testDirectPrint() {
            try {
                window.print();
                testResults.printMethods.direct = true;
                showStatus('print-methods-result', 'success', 'Direct print method executed successfully');
            } catch (error) {
                testResults.printMethods.direct = false;
                showStatus('print-methods-result', 'error', 'Direct print failed: ' + error.message);
            }
        }

        function testIframePrint() {
            try {
                var iframe = document.createElement('iframe');
                iframe.style.position = 'absolute';
                iframe.style.top = '-10000px';
                iframe.style.left = '-10000px';
                iframe.style.width = '1px';
                iframe.style.height = '1px';
                
                document.body.appendChild(iframe);

                iframe.onload = function() {
                    try {
                        var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        iframeDoc.open();
                        iframeDoc.write(getReceiptHTML());
                        iframeDoc.close();

                        setTimeout(function() {
                            try {
                                iframe.contentWindow.print();
                                testResults.printMethods.iframe = true;
                                showStatus('print-methods-result', 'success', 'IFrame print method executed successfully');
                            } catch (e) {
                                testResults.printMethods.iframe = false;
                                showStatus('print-methods-result', 'error', 'IFrame print failed: ' + e.message);
                            }
                            
                            setTimeout(function() {
                                if (iframe.parentNode) {
                                    iframe.parentNode.removeChild(iframe);
                                }
                            }, 1000);
                        }, 200);
                    } catch (e) {
                        testResults.printMethods.iframe = false;
                        showStatus('print-methods-result', 'error', 'IFrame setup failed: ' + e.message);
                        if (iframe.parentNode) {
                            iframe.parentNode.removeChild(iframe);
                        }
                    }
                };

                iframe.src = 'about:blank';
            } catch (error) {
                testResults.printMethods.iframe = false;
                showStatus('print-methods-result', 'error', 'IFrame print setup failed: ' + error.message);
            }
        }

        function testPopupPrint() {
            try {
                var popup = window.open('', 'print-test', 'width=800,height=600,scrollbars=yes');
                
                if (!popup) {
                    testResults.printMethods.popup = false;
                    showStatus('print-methods-result', 'error', 'Popup blocked by browser');
                    return;
                }

                popup.document.open();
                popup.document.write(getReceiptHTML());
                popup.document.close();

                popup.onload = function() {
                    setTimeout(function() {
                        popup.print();
                        popup.close();
                        testResults.printMethods.popup = true;
                        showStatus('print-methods-result', 'success', 'Popup print method executed successfully');
                    }, 200);
                };
            } catch (error) {
                testResults.printMethods.popup = false;
                showStatus('print-methods-result', 'error', 'Popup print failed: ' + error.message);
            }
        }

        function testFullscreenPrint() {
            // First enter fullscreen
            enterFullscreen();
            
            setTimeout(function() {
                if (!document.fullscreenElement) {
                    showStatus('fullscreen-print-result', 'error', 'Could not enter fullscreen mode for test');
                    return;
                }

                showStatus('fullscreen-print-result', 'info', 'Entered fullscreen mode. Testing print while maintaining fullscreen...');

                // Test printing while in fullscreen
                setTimeout(function() {
                    var wasFullscreen = !!document.fullscreenElement;
                    
                    // Try iframe print method (best for maintaining fullscreen)
                    testIframePrint();
                    
                    // Check if still in fullscreen after a delay
                    setTimeout(function() {
                        var stillFullscreen = !!document.fullscreenElement;
                        
                        if (wasFullscreen && stillFullscreen) {
                            testResults.fullscreenPrint = true;
                            showStatus('fullscreen-print-result', 'success', 
                                'Success! Fullscreen mode maintained during printing. ' +
                                'The fullscreen print system is working correctly.');
                        } else if (wasFullscreen && !stillFullscreen) {
                            testResults.fullscreenPrint = false;
                            showStatus('fullscreen-print-result', 'error', 
                                'Print dialog exited fullscreen mode. This is the exact issue the fullscreen print system is designed to fix. ' +
                                'Make sure the YITH POS Fullscreen Print plugin is properly loaded and configured.');
                        } else {
                            showStatus('fullscreen-print-result', 'error', 'Test failed - could not establish fullscreen baseline');
                        }
                    }, 2000);
                }, 1000);
            }, 500);
        }

        function getReceiptHTML() {
            var receiptElement = document.getElementById('order-receipt-print');
            
            var html = '<!DOCTYPE html><html><head>' +
                '<title>Test Receipt</title>' +
                '<meta charset="utf-8">' +
                '<style>' +
                'body { margin: 0; padding: 20px; font-family: "Courier New", monospace; font-size: 12px; }' +
                '.test-receipt { width: 100%; max-width: none; }' +
                '@media print { body { margin: 0; padding: 20px; } * { -webkit-print-color-adjust: exact; color-adjust: exact; } }' +
                '</style>' +
                '</head><body>' +
                receiptElement.outerHTML +
                '</body></html>';

            return html;
        }

        function showDebugInfo() {
            var info = {
                userAgent: navigator.userAgent,
                platform: navigator.platform,
                fullscreenEnabled: document.fullscreenEnabled,
                fullscreenElement: !!document.fullscreenElement,
                testResults: testResults,
                apis: {
                    requestFullscreen: !!document.documentElement.requestFullscreen,
                    mozRequestFullScreen: !!document.documentElement.mozRequestFullScreen,
                    webkitRequestFullscreen: !!document.documentElement.webkitRequestFullscreen,
                    msRequestFullscreen: !!document.documentElement.msRequestFullscreen,
                    print: !!window.print
                }
            };

            var debugDiv = document.getElementById('debug-info');
            debugDiv.innerHTML = '<pre>' + JSON.stringify(info, null, 2) + '</pre>';
            debugDiv.style.display = 'block';
        }

        function showStatus(elementId, type, message) {
            var element = document.getElementById(elementId);
            element.innerHTML = '<div class="status ' + type + '">' + message + '</div>';
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            showStatus('browser-support-result', 'info', 'Click "Test Browser Support" to check compatibility');
            showStatus('fullscreen-api-result', 'info', 'Click "Test Fullscreen API" to test fullscreen functionality');
            showStatus('print-methods-result', 'info', 'Click print method buttons to test different printing approaches');
            showStatus('fullscreen-print-result', 'info', 'Click "Test Fullscreen Print" to test the complete workflow');
        });
    </script>
</body>
</html>
