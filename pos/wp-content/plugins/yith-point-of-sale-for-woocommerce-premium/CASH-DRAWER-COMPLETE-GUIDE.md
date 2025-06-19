# YITH POS Cash Drawer Integration - Complete Documentation

## Overview
This comprehensive module provides automatic cash drawer opening functionality for the YITH Point of Sale system. When enabled, the cash drawer will automatically open when printing receipts, providing a seamless checkout experience for retail operations.

## Features
- **Automatic Opening**: Cash drawer opens when printing receipts
- **Manual Control**: Cashiers can manually open the drawer
- **Multiple Methods**: Supports Web Serial API, ESC/POS injection, and AJAX fallback
- **Admin Interface**: Easy configuration through WordPress admin
- **Test Functionality**: Built-in testing to verify setup
- **ESC/POS Compatible**: Works with standard ESC/POS printers and cash drawers
- **Multi-Browser Support**: Works across different browsers with fallback methods
- **Fullscreen Print Management**: Prevents Chrome from exiting fullscreen mode during printing
- **Kiosk Mode Support**: Maintains fullscreen mode for POS kiosk operations
- **Security**: Proper nonce validation and capability checks
- **Customizable**: Hooks and filters for developers

## Installation & Setup

### System Requirements
- YITH Point of Sale for WooCommerce Premium
- WordPress 5.0 or higher
- WooCommerce 3.0 or higher
- ESC/POS compatible receipt printer
- Cash drawer connected to printer's drawer port
- Modern web browser (Chrome 89+, Edge 89+, Firefox, Safari)

### Hardware Setup
1. **Printer Connection**
   - Connect your ESC/POS receipt printer to your computer
   - Install printer drivers and configure as default printer
   - Test basic printing functionality

2. **Cash Drawer Connection**
   - Connect cash drawer to printer's drawer port (usually RJ11/RJ12)
   - Ensure proper wiring (most drawers work with Pin 2 configuration)
   - Some drawers may require Pin 5 configuration

3. **Testing Hardware**
   - Print a test receipt from your printer software
   - Verify cash drawer opens when printing (if supported)

### Software Configuration
1. **WordPress Admin Setup**
   - Navigate to WordPress Admin → YITH POS → Cash Drawer
   - Enable "Cash Drawer" functionality
   - Choose appropriate pin configuration (Pin 2 is most common)
   - Enable "Auto Open on Print" for automatic operation
   - Save settings

2. **Testing Configuration**
   - Use the "Test Cash Drawer" button in admin
   - Verify successful operation
   - Check browser console for any errors
   - Enable test mode for debugging if needed

## Fullscreen Print Management

### The Chrome Fullscreen Issue
One critical challenge with browser-based POS systems is that Chrome's print dialog automatically exits fullscreen mode. This breaks the kiosk workflow by allowing users to access the desktop and other applications. Our solution addresses this by implementing intelligent print methods that preserve fullscreen mode.

### How It Works
The fullscreen print management system:
1. **Detects Fullscreen State**: Monitors when the POS is in fullscreen mode
2. **Intercepts Print Calls**: Captures `window.print()` calls and print button clicks
3. **Smart Print Method Selection**: Chooses the best printing approach for the browser
4. **Fullscreen Preservation**: Uses iframe or popup methods that don't exit fullscreen
5. **Automatic Restoration**: Restores fullscreen mode if it's accidentally lost

### Available Print Methods
- **IFrame Method**: Creates an invisible iframe for printing (best for Chrome/Edge)
- **Popup Method**: Uses a popup window for printing (fallback option)
- **Direct Method**: Standard print with fullscreen restoration
- **Auto Method**: Automatically selects the best method for the browser

### Configuration Options
Navigate to **WordPress Admin → YITH POS → Cash Drawer → Fullscreen Print Settings**:

- **Enable Fullscreen Print**: Master toggle for the functionality
- **Preserve Fullscreen Mode**: Automatically restore fullscreen after printing
- **Print Method**: Choose specific method or let system auto-detect
- **Print Delay**: Delay before showing print dialog (helps with timing issues)
- **Restore Delay**: Delay before attempting fullscreen restoration
- **Show Notifications**: Display status messages during printing
- **Debug Mode**: Enable detailed logging for troubleshooting

## Technical Implementation

### File Structure
```
wp-content/plugins/yith-point-of-sale-for-woocommerce-premium/
├── includes/
│   ├── class.yith-pos-cash-drawer.php          # Main cash drawer class
│   ├── class.yith-pos-fullscreen-print.php     # Fullscreen print management
│   └── yith-pos-receipt-escpos.php             # ESC/POS integration
├── assets/
│   ├── js/yith-pos-cash-drawer.js              # Frontend JavaScript
│   ├── js/yith-pos-fullscreen-print.js         # Fullscreen print manager
│   ├── css/yith-pos-cash-drawer.css            # Styling
│   └── css/yith-pos-fullscreen-print.css       # Fullscreen print styles
├── CASH-DRAWER-COMPLETE-GUIDE.md               # This documentation
├── test-cash-drawer.php                        # Cash drawer test interface
└── test-fullscreen-print.php                   # Fullscreen print test interface
```

### Core Classes

#### 1. YITH_POS_Cash_Drawer
**Main functionality class**
- Manages cash drawer operations
- Handles ESC/POS commands (Pin 2 and Pin 5)
- Integrates with YITH POS hooks and events
- Provides AJAX endpoints for frontend communication
- Enqueues necessary JavaScript and CSS assets

**Key Methods:**
- `open_cash_drawer()` - Opens the cash drawer
- `handle_ajax_open_drawer()` - AJAX endpoint handler
- `enqueue_frontend_scripts()` - Loads frontend assets
- `add_receipt_hooks()` - Integrates with receipt printing

#### 2. YITH_POS_Cash_Drawer_Admin
**Admin interface class**
- Provides settings page in WordPress admin
- Handles configuration options
- Includes test functionality
- Manages admin-specific assets

**Key Methods:**
- `add_admin_menu()` - Creates admin menu
- `admin_page()` - Renders settings page
- `test_cash_drawer()` - Handles test requests

#### 3. Frontend JavaScript Controller
**Browser-side functionality**
- Detects print events from multiple sources
- Manages different opening methods (Web Serial, AJAX)
- Provides user feedback and error handling
- Handles browser compatibility

**Key Features:**
- Web Serial API integration for modern browsers
- AJAX fallback for older browsers
- Event hooks for print functionality
- Manual drawer opening controls

## ESC/POS Integration

### Standard ESC/POS Commands
- **Pin 2**: `\x1B\x70\x00\x19\x64` (Most common configuration)
- **Pin 5**: `\x1B\x70\x01\x19\x64` (Alternative configuration)

### Supported Hardware
**Printers:**
- Epson TM series (TM-T20, TM-T88, etc.)
- Star TSP series (TSP100, TSP650, etc.)
- Citizen CT-S series (CT-S310, CT-S4000, etc.)
- Bixolon SRP series
- Any ESC/POS compatible printer with drawer port

**Cash Drawers:**
- Standard RJ11/RJ12 connected drawers
- 12V and 24V models
- Both 3-position and 5-position drawers

## Browser Compatibility

### Modern Browsers (Web Serial API Support)
- **Chrome 89+**: Full support with Web Serial API
- **Edge 89+**: Full support with Web Serial API
- **Opera 75+**: Full support with Web Serial API

### Fallback Support
- **Firefox**: AJAX method with server-side processing
- **Safari**: AJAX method with server-side processing
- **Internet Explorer**: Basic AJAX support
- **Mobile browsers**: Limited support (manual only)

### Feature Detection
The system automatically detects browser capabilities and uses the best available method:
1. Web Serial API (preferred)
2. ESC/POS command injection into print jobs
3. AJAX server-side processing
4. Manual opening instructions

## Usage Guide

### Automatic Operation
When properly configured, the cash drawer will automatically open in these scenarios:
- Printing receipts via "Print Receipt" button
- Using browser print functionality (Ctrl+P)
- Completing cash transactions
- Order completion events
- Manual triggers from POS interface

### Manual Operation
Cashiers can manually control the drawer through:
- "Open Drawer" button in POS interface
- Test button in admin settings
- Keyboard shortcuts (if configured)
- Custom buttons added to interface

### Integration with YITH POS
The system seamlessly integrates with existing YITH POS workflows:
- Order completion processes
- Receipt printing functionality
- Register session management
- Payment processing events

## Configuration Options

### Available Settings
**Basic Settings:**
- **Enable Cash Drawer**: Master on/off switch
- **Auto Open on Print**: Automatic opening when printing
- **Pin Configuration**: Pin 2 or Pin 5 selection
- **Test Mode**: Enable detailed logging for debugging

**Advanced Settings:**
- **Opening Delay**: Delay between print and drawer opening
- **Retry Attempts**: Number of retry attempts for failed operations
- **Error Handling**: How to handle failed drawer operations
- **Notification Settings**: User feedback options

### WordPress Database Options
Settings are stored as WordPress options with these keys:
- `yith_pos_cash_drawer_enabled` - Enable/disable functionality
- `yith_pos_cash_drawer_auto_open` - Auto-open setting
- `yith_pos_cash_drawer_pin` - Pin configuration (pin2/pin5)
- `yith_pos_cash_drawer_test_mode` - Debug mode setting

## Developer Integration

### WordPress Hooks and Filters

#### Actions
```php
// Before drawer opens
do_action('yith_pos_cash_drawer_opening', $context);

// After drawer opens
do_action('yith_pos_cash_drawer_opened', $context);

// On drawer open failure
do_action('yith_pos_cash_drawer_failed', $error, $context);

// Receipt printing events
do_action('yith_pos_receipt_printed', $order_id);
```

#### Filters
```php
// Modify cash drawer command
add_filter('yith_pos_cash_drawer_command', function($command, $pin) {
    // Custom command modification
    return $command;
}, 10, 2);

// Modify drawer opening conditions
add_filter('yith_pos_should_open_drawer', function($should_open, $context) {
    // Custom logic for when to open
    return $should_open;
}, 10, 2);

// Customize drawer button HTML
add_filter('yith_pos_drawer_button_html', function($html) {
    // Custom button markup
    return $html;
});
```

### JavaScript Events

#### Custom Events
```javascript
// Listen for cash drawer events
$(document).on('yith-pos-cash-drawer-open', function(event, context) {
    console.log('Cash drawer opened:', context);
});

$(document).on('yith-pos-cash-drawer-failed', function(event, error) {
    console.log('Cash drawer failed:', error);
});

// Trigger manual opening
$(document).trigger('yith-pos-open-cash-drawer', ['manual']);
```

#### Integration with Existing Events
```javascript
// Hook into print events
$(document).on('click', '.print-receipt-btn', function() {
    // Custom logic before printing
});

// Override browser print
var originalPrint = window.print;
window.print = function() {
    // Custom logic
    originalPrint.call(window);
};
```

## Troubleshooting Guide

### Common Issues and Solutions

#### 1. Cash Drawer Not Opening
**Symptoms:** Drawer doesn't respond to commands
**Possible Causes:**
- Hardware connection issues
- Wrong pin configuration
- Printer not ESC/POS compatible
- Power supply problems

**Solutions:**
1. Check physical connections
2. Try different pin configuration (Pin 2 vs Pin 5)
3. Test with printer manufacturer's software
4. Verify power supply to drawer
5. Check printer ESC/POS compatibility

#### 2. Intermittent Operation
**Symptoms:** Drawer sometimes works, sometimes doesn't
**Possible Causes:**
- Timing issues with print jobs
- Browser compatibility problems
- Network connectivity issues
- JavaScript errors

**Solutions:**
1. Enable test mode for debugging
2. Check browser console for errors
3. Increase opening delay in settings
4. Update browser to latest version
5. Check WordPress error logs

#### 3. Browser Compatibility Issues
**Symptoms:** Works in some browsers but not others
**Possible Causes:**
- Web Serial API not supported
- AJAX functionality blocked
- JavaScript disabled
- Security restrictions

**Solutions:**
1. Use Chrome or Edge for best results
2. Enable JavaScript in browser
3. Check browser security settings
4. Try AJAX fallback method
5. Update browser to latest version

#### 4. Permission Errors
**Symptoms:** "Access denied" or permission errors
**Possible Causes:**
- Insufficient WordPress permissions
- Browser security restrictions
- AJAX nonce validation failures

**Solutions:**
1. Check user capabilities in WordPress
2. Verify nonce validation
3. Check browser permissions
4. Review WordPress security plugins

### Debugging Tools

#### Enable Debug Mode
1. Go to Admin → YITH POS → Cash Drawer
2. Enable "Test Mode"
3. Check WordPress debug logs
4. Monitor browser console

#### Testing Procedures
1. **Hardware Test**: Use printer software to test drawer
2. **Basic Test**: Use admin test button
3. **Integration Test**: Print actual receipt
4. **Browser Test**: Try different browsers
5. **Error Test**: Check error handling

#### Log Analysis
```php
// Enable WordPress debug logging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Check logs at: wp-content/debug.log
// Look for entries starting with "YITH POS Cash Drawer"
```

## Security Considerations

### Data Protection
- No sensitive financial data is transmitted
- All AJAX requests use WordPress nonces
- Proper capability checks for admin functions
- Sanitized input validation throughout

### Browser Security
- Web Serial API requires user permission
- Secure contexts (HTTPS) recommended for production
- No persistent storage of sensitive data
- Graceful degradation for unsupported features

### WordPress Security
- Follows WordPress coding standards
- Proper escaping of output
- Capability-based access control
- Nonce validation for all AJAX requests

## Performance Optimization

### Frontend Performance
- Minimal JavaScript footprint (~15KB minified)
- Conditional script loading (only when needed)
- Efficient event binding and delegation
- Non-blocking operations

### Backend Performance
- Cached settings retrieval
- Minimal database queries
- Efficient hook implementation
- Lazy loading of admin functionality

### Browser Performance
- Uses modern APIs when available
- Progressive enhancement approach
- Minimal DOM manipulation
- Optimized for mobile devices

## Customization Examples

### Custom Drawer Commands
```php
// Add custom ESC/POS command
add_filter('yith_pos_cash_drawer_command', function($command, $pin) {
    if ($pin === 'custom') {
        return "\x1B\x70\x02\x32\x96"; // Custom command
    }
    return $command;
}, 10, 2);
```

### Custom Opening Conditions
```php
// Only open drawer for cash payments
add_filter('yith_pos_should_open_drawer', function($should_open, $context) {
    if (isset($context['payment_method']) && $context['payment_method'] !== 'cash') {
        return false;
    }
    return $should_open;
}, 10, 2);
```

### Custom UI Integration
```php
// Add drawer button to custom location
add_action('yith_pos_after_total', function() {
    if (get_option('yith_pos_cash_drawer_enabled') === 'yes') {
        echo '<button class="yith-pos-open-drawer-btn">Open Drawer</button>';
    }
});
```

## Future Roadmap

### Planned Features
- **Multiple Drawer Support**: Support for multiple cash drawers
- **Advanced Scheduling**: Time-based drawer operations
- **Reporting Integration**: Drawer opening logs and reports
- **Mobile App Support**: Native mobile app integration
- **Advanced Hardware**: Support for newer cash drawer models

### Compatibility Updates
- Regular browser compatibility updates
- New ESC/POS printer support
- WordPress and WooCommerce compatibility
- Performance optimizations

## Support and Maintenance

### Getting Help
1. **Documentation**: Start with this comprehensive guide
2. **Testing Tools**: Use built-in test functionality
3. **Debug Mode**: Enable for detailed logging
4. **Community Support**: WordPress and WooCommerce forums
5. **Professional Support**: YITH support channels

### Regular Maintenance
- **Updates**: Keep plugin and WordPress updated
- **Testing**: Regularly test functionality
- **Monitoring**: Check error logs periodically
- **Backup**: Regular backups of settings
- **Hardware**: Maintain printer and drawer hardware

### Version History
- **1.0.0**: Initial release with core functionality
- **1.0.1**: Bug fixes and browser compatibility improvements
- **1.1.0**: Added admin interface and advanced settings
- **1.2.0**: Enhanced ESC/POS support and error handling

---

## Quick Start Checklist

### Hardware Setup
- [ ] Receipt printer connected and configured
- [ ] Cash drawer connected to printer
- [ ] Test print from printer software
- [ ] Verify drawer opens with test print

### Software Setup
- [ ] YITH POS plugin installed and active
- [ ] Navigate to Cash Drawer settings
- [ ] Enable cash drawer functionality
- [ ] Select appropriate pin configuration
- [ ] Enable auto-open on print
- [ ] Test functionality with admin button

### Verification
- [ ] Print test receipt from POS
- [ ] Verify drawer opens automatically
- [ ] Test manual drawer opening
- [ ] Check different browsers
- [ ] Train staff on manual operation

### Production Ready
- [ ] Document settings for team
- [ ] Create backup of configuration
- [ ] Monitor operation for first week
- [ ] Address any issues promptly
- [ ] Regular maintenance schedule

---

*This implementation provides a complete, production-ready cash drawer integration for YITH Point of Sale systems. The modular design allows for easy customization and maintenance while ensuring reliable operation across different hardware configurations and browser environments.*
