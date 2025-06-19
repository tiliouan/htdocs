<?php
/**
 * Test Cash Drawer Functionality
 * 
 * This is a temporary test file to verify the cash drawer integration works correctly.
 * It can be accessed via: yourdomain.com/pos/wp-content/plugins/yith-point-of-sale-for-woocommerce-premium/test-cash-drawer.php
 */

// Include WordPress
$wp_load_path = '../../../wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    die('WordPress not found. Please check the path.');
}

// Check if we're in admin or have proper permissions
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Include the YITH POS classes if not already loaded
if (!class_exists('YITH_POS_Cash_Drawer')) {
    require_once(dirname(__FILE__) . '/includes/class.yith-pos-cash-drawer.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YITH POS Cash Drawer Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
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
            background-color: #0073aa;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 5px;
        }
        .button:hover {
            background-color: #005a87;
        }
        .result {
            margin-top: 15px;
            padding: 10px;
            border-radius: 3px;
            background-color: #f9f9f9;
            border-left: 4px solid #0073aa;
        }
        .success {
            border-left-color: #46b450;
            background-color: #f0f9ff;
        }
        .error {
            border-left-color: #d63638;
            background-color: #fff5f5;
        }
        .escpos-commands {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>YITH POS Cash Drawer Integration Test</h1>
        <p>This page tests the cash drawer functionality integration with YITH Point of Sale system.</p>
        
        <div class="test-section">
            <h3>1. Class Loading Test</h3>
            <p>Testing if the YITH_POS_Cash_Drawer class is properly loaded:</p>
            <div class="result <?php echo class_exists('YITH_POS_Cash_Drawer') ? 'success' : 'error'; ?>">
                <?php if (class_exists('YITH_POS_Cash_Drawer')): ?>
                    ✓ YITH_POS_Cash_Drawer class loaded successfully
                <?php else: ?>
                    ✗ YITH_POS_Cash_Drawer class not found
                <?php endif; ?>
            </div>
        </div>

        <div class="test-section">
            <h3>2. ESC/POS Commands Test</h3>
            <p>Display the ESC/POS commands used for opening cash drawers:</p>
            <?php if (class_exists('YITH_POS_Cash_Drawer')): ?>
                <div class="escpos-commands">
                    <strong>Pin 2 Command:</strong> <?php echo bin2hex(YITH_POS_Cash_Drawer::ESC_POS_DRAWER_OPEN_PIN2); ?><br>
                    <strong>Pin 5 Command:</strong> <?php echo bin2hex(YITH_POS_Cash_Drawer::ESC_POS_DRAWER_OPEN_PIN5); ?>
                </div>
                <div class="result success">
                    ✓ ESC/POS commands are properly defined
                </div>
            <?php else: ?>
                <div class="result error">
                    ✗ Cannot access ESC/POS commands - class not loaded
                </div>
            <?php endif; ?>
        </div>

        <div class="test-section">
            <h3>3. WordPress Hooks Test</h3>
            <p>Check if WordPress hooks are properly registered:</p>
            <?php
            $hooks_to_check = [
                'wp_ajax_yith_pos_open_cash_drawer',
                'wp_ajax_nopriv_yith_pos_open_cash_drawer',
                'yith_pos_receipt_printed',
                'woocommerce_order_status_completed',
                'woocommerce_payment_complete'
            ];
            
            $hooks_registered = 0;
            foreach ($hooks_to_check as $hook) {
                if (has_action($hook)) {
                    $hooks_registered++;
                }
            }
            ?>
            <div class="result <?php echo $hooks_registered > 0 ? 'success' : 'error'; ?>">
                <?php if ($hooks_registered > 0): ?>
                    ✓ <?php echo $hooks_registered; ?> out of <?php echo count($hooks_to_check); ?> hooks are registered
                <?php else: ?>
                    ✗ No hooks are registered - cash drawer may not be initialized
                <?php endif; ?>
            </div>
        </div>

        <div class="test-section">
            <h3>4. JavaScript and CSS Assets Test</h3>
            <p>Check if the required assets are properly enqueued:</p>
            <?php
            $assets_path = dirname(__FILE__) . '/assets/';
            $js_exists = file_exists($assets_path . 'js/yith-pos-cash-drawer.js');
            $css_exists = file_exists($assets_path . 'css/yith-pos-cash-drawer.css');
            ?>
            <div class="result">
                JavaScript File: <?php echo $js_exists ? '✓ Found' : '✗ Missing'; ?><br>
                CSS File: <?php echo $css_exists ? '✓ Found' : '✗ Missing'; ?>
            </div>
        </div>

        <div class="test-section">
            <h3>5. Manual Cash Drawer Test</h3>
            <p>Test opening the cash drawer manually (requires printer connection):</p>
            <button class="button" onclick="testCashDrawer()">Test Cash Drawer Opening</button>
            <div id="manual-test-result" class="result" style="display: none;"></div>
        </div>

        <div class="test-section">
            <h3>6. Settings Test</h3>
            <p>Check if cash drawer settings are available:</p>
            <?php
            $settings = [
                'yith_pos_cash_drawer_enabled' => get_option('yith_pos_cash_drawer_enabled', 'no'),
                'yith_pos_cash_drawer_auto_open' => get_option('yith_pos_cash_drawer_auto_open', 'yes'),
                'yith_pos_cash_drawer_pin' => get_option('yith_pos_cash_drawer_pin', 'pin2'),
                'yith_pos_cash_drawer_test_mode' => get_option('yith_pos_cash_drawer_test_mode', 'no')
            ];
            ?>
            <div class="result">
                <?php foreach ($settings as $key => $value): ?>
                    <strong><?php echo $key; ?>:</strong> <?php echo $value; ?><br>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function testCashDrawer() {
            const resultDiv = document.getElementById('manual-test-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing cash drawer...';
            
            // Make AJAX request to test cash drawer
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=yith_pos_open_cash_drawer&test=1&_wpnonce=<?php echo wp_create_nonce('yith_pos_cash_drawer_nonce'); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = 'result success';
                    resultDiv.innerHTML = '✓ ' + data.data.message;
                } else {
                    resultDiv.className = 'result error';
                    resultDiv.innerHTML = '✗ ' + (data.data ? data.data.message : 'Unknown error');
                }
            })
            .catch(error => {
                resultDiv.className = 'result error';
                resultDiv.innerHTML = '✗ Error: ' + error.message;
            });
        }
    </script>
</body>
</html>
