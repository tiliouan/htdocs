# POS Theme

A minimal WordPress theme designed specifically for the **YITH Point of Sale for WooCommerce** plugin. This theme provides a clean, distraction-free interface focused entirely on POS operations.

## Features

- **POS-Focused Design**: Minimal interface that prioritizes POS functionality
- **Full-Screen POS Mode**: Dedicated full-screen interface for POS operations
- **User Role Integration**: Seamlessly integrates with YITH POS user roles (cashiers, managers)
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Clean Login Interface**: Beautiful login and register selection screens
- **Admin Bar Removal**: Automatically hides WordPress admin bar for POS users
- **Theme Customization**: Built-in customizer options for logo and colors
- **Security Focused**: Restricts access to authorized POS users only

## Installation

1. Upload the `pos-theme` folder to your `/wp-content/themes/` directory
2. Activate the theme from WordPress Admin → Appearance → Themes
3. Ensure YITH Point of Sale for WooCommerce plugin is installed and activated
4. Configure your POS settings in the YITH POS admin panel

## Configuration

### Theme Customization
Go to **Appearance → Customize → POS Theme Options** to configure:
- POS Logo
- Primary Color Scheme

### POS Page Setup
The theme automatically works with the POS page created by the YITH POS plugin. If you need to manually assign the POS template:
1. Go to **Pages → Edit** your POS page
2. Select "POS Page" from the Page Template dropdown
3. Update the page

## Theme Structure

```
pos-theme/
├── style.css           # Main theme stylesheet
├── functions.php       # Theme functions and customizations
├── index.php          # Main template file
├── header.php         # Header template
├── footer.php         # Footer template
├── pos-page.php       # Dedicated POS page template
├── assets/
│   └── pos.css        # POS-specific styles
└── README.md          # This file
```

## User Permissions

The theme integrates with YITH POS user roles:
- **POS Managers**: Full access to POS and admin functions
- **POS Cashiers**: Access to POS interface only
- **Regular Users**: Redirected to login or access denied

## Customization

### Adding Custom Styles
Add custom CSS in **Appearance → Customize → Additional CSS** or modify `/assets/pos.css`

### Modifying Templates
All template files can be customized to match your specific needs:
- `pos-page.php` - Main POS interface template
- `header.php` - Header (hidden on POS pages)
- `footer.php` - Footer (hidden on POS pages)

### Hooks and Filters
The theme provides several hooks for customization:
- `pos_theme_before_pos_content` - Before POS content
- `pos_theme_after_pos_content` - After POS content
- `pos_theme_login_form` - Custom login form

## Security Features

- Automatic redirection for unauthorized users
- Admin bar removal for POS users  
- WordPress update notifications disabled for POS users
- Clean URLs without exposing WordPress structure

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### POS Interface Not Loading
1. Verify YITH POS plugin is active
2. Check user has proper POS permissions
3. Clear browser cache
4. Check for plugin conflicts

### Login Issues
1. Ensure users have `yith_pos_cashier` or `yith_pos_manager` capabilities
2. Check WordPress user roles are properly configured
3. Verify POS page is set correctly in YITH POS settings

### Styling Issues
1. Clear any caching plugins
2. Check for theme conflicts
3. Verify custom CSS is not overriding theme styles

## Support

This theme is designed to work specifically with YITH Point of Sale for WooCommerce. For plugin-related issues, please refer to the YITH documentation.

## Version History

### 1.0.0
- Initial release
- Full POS integration
- Responsive design
- Theme customizer options
- Security enhancements

## License

This theme is released under GPL v2 or later license.

## Requirements

- WordPress 5.0+
- YITH Point of Sale for WooCommerce plugin
- WooCommerce 4.0+
- PHP 7.4+
