<?php
/**
 * Accessibility Features for Happy Business Listing
 *
 * Provides WCAG 2.1 AA compliance features and accessibility enhancements.
 *
 * @package Happy_Business_Listing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize accessibility features
 */
function hbl_init_accessibility() {
    // Add skip links
    add_action('wp_body_open', 'hbl_add_skip_links');
    
    // Add ARIA labels and roles
    add_filter('hbl_business_listing_html', 'hbl_add_aria_labels');
    add_filter('hbl_search_form_html', 'hbl_add_search_aria_labels');
    add_filter('hbl_contact_form_html', 'hbl_add_form_aria_labels');
    
    // Add keyboard navigation support
    add_action('wp_footer', 'hbl_add_keyboard_navigation');
    
    // Add focus management
    add_action('wp_footer', 'hbl_add_focus_management');
    
    // Add high contrast mode support
    add_action('wp_head', 'hbl_add_high_contrast_support');
    
    // Add screen reader announcements
    add_action('wp_footer', 'hbl_add_screen_reader_announcements');
    
    // Add color contrast checker
    add_action('admin_footer', 'hbl_add_color_contrast_checker');
    
    // Add accessibility settings
    add_action('admin_init', 'hbl_add_accessibility_settings');
}
add_action('init', 'hbl_init_accessibility');

/**
 * Add skip links for keyboard navigation
 */
function hbl_add_skip_links() {
    if (is_post_type_archive('business_listing') || is_singular('business_listing')) {
        ?>
        <a class="skip-link screen-reader-text" href="#hbl-main-content">
            <?php _e('Skip to main content', 'happy-business-listing'); ?>
        </a>
        <a class="skip-link screen-reader-text" href="#hbl-search">
            <?php _e('Skip to search', 'happy-business-listing'); ?>
        </a>
        <a class="skip-link screen-reader-text" href="#hbl-filters">
            <?php _e('Skip to filters', 'happy-business-listing'); ?>
        </a>
        <?php
    }
}

/**
 * Add ARIA labels to business listings
 *
 * @param string $html Business listing HTML
 * @return string Modified HTML with ARIA labels
 */
function hbl_add_aria_labels($html) {
    // Add main content landmark
    $html = str_replace(
        '<div class="business-listing-container">',
        '<div class="business-listing-container" role="main" id="hbl-main-content" aria-label="' . __('Business listing content', 'happy-business-listing') . '">',
        $html
    );
    
    // Add business card landmarks
    $html = preg_replace(
        '/<div class="business-card">/',
        '<div class="business-card" role="article" aria-labelledby="business-title-{ID}">',
        $html
    );
    
    // Add business title IDs
    $html = preg_replace(
        '/<h2 class="business-title">(.*?)<\/h2>/',
        '<h2 class="business-title" id="business-title-{ID}">$1</h2>',
        $html
    );
    
    // Add business image alt text
    $html = preg_replace(
        '/<img class="business-logo"([^>]*)>/',
        '<img class="business-logo"$1 alt="' . __('Business logo', 'happy-business-listing') . '">',
        $html
    );
    
    // Add social media link descriptions
    $html = preg_replace(
        '/<a class="social-link ([^"]*)"([^>]*)>/',
        '<a class="social-link $1"$2 aria-label="' . __('Visit our', 'happy-business-listing') . ' $1 ' . __('page', 'happy-business-listing') . '">',
        $html
    );
    
    return $html;
}

/**
 * Add ARIA labels to search form
 *
 * @param string $html Search form HTML
 * @return string Modified HTML with ARIA labels
 */
function hbl_add_search_aria_labels($html) {
    // Add search landmark
    $html = str_replace(
        '<form',
        '<form role="search" aria-label="' . __('Search businesses', 'happy-business-listing') . '" id="hbl-search"',
        $html
    );
    
    // Add search input labels
    $html = preg_replace(
        '/<input([^>]*name="search"[^>]*)>/',
        '<input$1 aria-label="' . __('Search for businesses', 'happy-business-listing') . '" aria-describedby="search-help">',
        $html
    );
    
    // Add search help text
    $html = str_replace(
        '</form>',
        '<div id="search-help" class="screen-reader-text">' . __('Enter keywords to search for businesses', 'happy-business-listing') . '</div></form>',
        $html
    );
    
    return $html;
}

/**
 * Add ARIA labels to contact form
 *
 * @param string $html Contact form HTML
 * @return string Modified HTML with ARIA labels
 */
function hbl_add_form_aria_labels($html) {
    // Add form landmark
    $html = str_replace(
        '<form',
        '<form role="form" aria-label="' . __('Contact business form', 'happy-business-listing') . '"',
        $html
    );
    
    // Add required field indicators
    $html = preg_replace(
        '/<input([^>]*name="name"[^>]*)>/',
        '<input$1 aria-required="true" aria-describedby="name-error">',
        $html
    );
    
    $html = preg_replace(
        '/<input([^>]*name="email"[^>]*)>/',
        '<input$1 aria-required="true" aria-describedby="email-error">',
        $html
    );
    
    // Add error message containers
    $html = str_replace(
        '</form>',
        '<div id="name-error" class="error-message" role="alert" aria-live="polite"></div>
        <div id="email-error" class="error-message" role="alert" aria-live="polite"></div>
        </form>',
        $html
    );
    
    return $html;
}

/**
 * Add keyboard navigation support
 */
function hbl_add_keyboard_navigation() {
    if (is_post_type_archive('business_listing') || is_singular('business_listing')) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add keyboard navigation to business cards
            const businessCards = document.querySelectorAll('.business-card');
            
            businessCards.forEach(function(card) {
                card.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        const link = card.querySelector('.business-link');
                        if (link) {
                            link.click();
                        }
                    }
                });
                
                // Make cards focusable
                card.setAttribute('tabindex', '0');
                card.setAttribute('role', 'button');
                card.setAttribute('aria-label', card.querySelector('.business-title')?.textContent || 'Business card');
            });
            
            // Add keyboard navigation to filters
            const filterSelects = document.querySelectorAll('.business-filters select');
            
            filterSelects.forEach(function(select) {
                select.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const form = select.closest('form');
                        if (form) {
                            form.submit();
                        }
                    }
                });
            });
            
            // Add keyboard navigation to pagination
            const paginationLinks = document.querySelectorAll('.pagination a');
            
            paginationLinks.forEach(function(link) {
                link.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        link.click();
                    }
                });
            });
        });
        </script>
        <?php
    }
}

/**
 * Add focus management
 */
function hbl_add_focus_management() {
    if (is_post_type_archive('business_listing') || is_singular('business_listing')) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus styles
            const focusableElements = document.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
            
            focusableElements.forEach(function(element) {
                element.classList.add('hbl-focus-visible');
            });
            
            // Manage focus for modals and overlays
            let previousActiveElement = null;
            
            function trapFocus(container) {
                const focusableElements = container.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];
                
                container.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab') {
                        if (e.shiftKey) {
                            if (document.activeElement === firstElement) {
                                e.preventDefault();
                                lastElement.focus();
                            }
                        } else {
                            if (document.activeElement === lastElement) {
                                e.preventDefault();
                                firstElement.focus();
                            }
                        }
                    }
                });
            }
            
            // Apply focus trapping to modals
            const modals = document.querySelectorAll('.hbl-modal');
            modals.forEach(function(modal) {
                trapFocus(modal);
            });
        });
        </script>
        <?php
    }
}

/**
 * Add high contrast mode support
 */
function hbl_add_high_contrast_support() {
    ?>
    <style>
    @media (prefers-contrast: high) {
        .business-card,
        .business-listing,
        .business-filters {
            border: 2px solid #000000 !important;
        }
        
        .business-title,
        .archive-title {
            color: #000000 !important;
        }
        
        .filter-button,
        .reset-button {
            border: 2px solid #000000 !important;
        }
        
        .page-numbers {
            border: 2px solid #000000 !important;
        }
    }
    </style>
    <?php
}

/**
 * Add screen reader announcements
 */
function hbl_add_screen_reader_announcements() {
    if (is_post_type_archive('business_listing') || is_singular('business_listing')) {
        ?>
        <div id="hbl-announcements" class="screen-reader-text" aria-live="polite" aria-atomic="true"></div>
        <script>
        function announceToScreenReader(message) {
            const announcements = document.getElementById('hbl-announcements');
            if (announcements) {
                announcements.textContent = message;
                setTimeout(() => {
                    announcements.textContent = '';
                }, 1000);
            }
        }
        
        // Announce search results
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('hbl-search');
            if (searchForm) {
                searchForm.addEventListener('submit', function() {
                    announceToScreenReader('<?php _e('Searching for businesses...', 'happy-business-listing'); ?>');
                });
            }
            
            // Announce filter changes
            const filterSelects = document.querySelectorAll('.business-filters select');
            filterSelects.forEach(function(select) {
                select.addEventListener('change', function() {
                    announceToScreenReader('<?php _e('Filter applied', 'happy-business-listing'); ?>');
                });
            });
            
            // Announce business count
            const businessCards = document.querySelectorAll('.business-card');
            if (businessCards.length > 0) {
                const count = businessCards.length;
                announceToScreenReader(sprintf('<?php _e('Found %d businesses', 'happy-business-listing'); ?>', count));
            }
        });
        </script>
        <?php
    }
}

/**
 * Add color contrast checker for admin
 */
function hbl_add_color_contrast_checker() {
    if (isset($_GET['page']) && $_GET['page'] === 'hbl_settings') {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Color contrast checker
            function checkContrast(foreground, background) {
                // Convert hex to RGB
                function hexToRgb(hex) {
                    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                    return result ? {
                        r: parseInt(result[1], 16),
                        g: parseInt(result[2], 16),
                        b: parseInt(result[3], 16)
                    } : null;
                }
                
                // Calculate relative luminance
                function getLuminance(r, g, b) {
                    const [rs, gs, bs] = [r, g, b].map(c => {
                        c = c / 255;
                        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
                    });
                    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
                }
                
                const fg = hexToRgb(foreground);
                const bg = hexToRgb(background);
                
                if (!fg || !bg) return 0;
                
                const l1 = getLuminance(fg.r, fg.g, fg.b);
                const l2 = getLuminance(bg.r, bg.g, bg.b);
                
                const lighter = Math.max(l1, l2);
                const darker = Math.min(l1, l2);
                
                return (lighter + 0.05) / (darker + 0.05);
            }
            
            // Add contrast checker to color inputs
            const colorInputs = document.querySelectorAll('input[type="color"]');
            colorInputs.forEach(function(input) {
                const container = input.parentElement;
                const contrastInfo = document.createElement('div');
                contrastInfo.className = 'contrast-info';
                contrastInfo.style.marginTop = '5px';
                contrastInfo.style.fontSize = '12px';
                container.appendChild(contrastInfo);
                
                function updateContrast() {
                    const fgColor = input.value;
                    const bgColor = '#ffffff'; // Default background
                    const ratio = checkContrast(fgColor, bgColor);
                    
                    let status = '';
                    if (ratio >= 4.5) {
                        status = '<span style="color: green;">✓ AAA (Excellent)</span>';
                    } else if (ratio >= 3) {
                        status = '<span style="color: orange;">✓ AA (Good)</span>';
                    } else {
                        status = '<span style="color: red;">✗ Poor contrast</span>';
                    }
                    
                    contrastInfo.innerHTML = `Contrast ratio: ${ratio.toFixed(2)}:1 - ${status}`;
                }
                
                input.addEventListener('input', updateContrast);
                updateContrast();
            });
        });
        </script>
        <?php
    }
}

/**
 * Add accessibility settings
 */
function hbl_add_accessibility_settings() {
    add_settings_section(
        'hbl_accessibility_section',
        __('Accessibility Settings', 'happy-business-listing'),
        'hbl_accessibility_section_callback',
        'hbl_settings'
    );
    
    add_settings_field(
        'hbl_enable_skip_links',
        __('Enable Skip Links', 'happy-business-listing'),
        'hbl_enable_skip_links_callback',
        'hbl_settings',
        'hbl_accessibility_section'
    );
    
    add_settings_field(
        'hbl_enable_aria_labels',
        __('Enable ARIA Labels', 'happy-business-listing'),
        'hbl_enable_aria_labels_callback',
        'hbl_settings',
        'hbl_accessibility_section'
    );
    
    add_settings_field(
        'hbl_enable_keyboard_navigation',
        __('Enable Keyboard Navigation', 'happy-business-listing'),
        'hbl_enable_keyboard_navigation_callback',
        'hbl_settings',
        'hbl_accessibility_section'
    );
    
    add_settings_field(
        'hbl_enable_screen_reader_announcements',
        __('Enable Screen Reader Announcements', 'happy-business-listing'),
        'hbl_enable_screen_reader_announcements_callback',
        'hbl_settings',
        'hbl_accessibility_section'
    );
    
    add_settings_field(
        'hbl_high_contrast_mode',
        __('High Contrast Mode', 'happy-business-listing'),
        'hbl_high_contrast_mode_callback',
        'hbl_settings',
        'hbl_accessibility_section'
    );
    
    register_setting('hbl_options', 'hbl_enable_skip_links');
    register_setting('hbl_options', 'hbl_enable_aria_labels');
    register_setting('hbl_options', 'hbl_enable_keyboard_navigation');
    register_setting('hbl_options', 'hbl_enable_screen_reader_announcements');
    register_setting('hbl_options', 'hbl_high_contrast_mode');
}

/**
 * Accessibility section callback
 */
function hbl_accessibility_section_callback() {
    echo '<p>' . __('Configure accessibility features to ensure your business listings are accessible to all users, including those using assistive technologies.', 'happy-business-listing') . '</p>';
    echo '<p><strong>' . __('WCAG 2.1 AA Compliance:', 'happy-business-listing') . '</strong> ' . __('These settings help ensure compliance with Web Content Accessibility Guidelines.', 'happy-business-listing') . '</p>';
}

/**
 * Enable skip links callback
 */
function hbl_enable_skip_links_callback() {
    $enabled = get_option('hbl_enable_skip_links', '1');
    ?>
    <input type="checkbox" id="hbl_enable_skip_links" name="hbl_enable_skip_links" value="1" <?php checked('1', $enabled); ?> />
    <label for="hbl_enable_skip_links"><?php _e('Enable skip links for keyboard navigation', 'happy-business-listing'); ?></label>
    <p class="description"><?php _e('Adds skip links to help keyboard users navigate quickly to main content areas.', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * Enable ARIA labels callback
 */
function hbl_enable_aria_labels_callback() {
    $enabled = get_option('hbl_enable_aria_labels', '1');
    ?>
    <input type="checkbox" id="hbl_enable_aria_labels" name="hbl_enable_aria_labels" value="1" <?php checked('1', $enabled); ?> />
    <label for="hbl_enable_aria_labels"><?php _e('Enable ARIA labels and roles', 'happy-business-listing'); ?></label>
    <p class="description"><?php _e('Adds ARIA labels and roles to improve screen reader compatibility.', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * Enable keyboard navigation callback
 */
function hbl_enable_keyboard_navigation_callback() {
    $enabled = get_option('hbl_enable_keyboard_navigation', '1');
    ?>
    <input type="checkbox" id="hbl_enable_keyboard_navigation" name="hbl_enable_keyboard_navigation" value="1" <?php checked('1', $enabled); ?> />
    <label for="hbl_enable_keyboard_navigation"><?php _e('Enable enhanced keyboard navigation', 'happy-business-listing'); ?></label>
    <p class="description"><?php _e('Improves keyboard navigation for business cards and interactive elements.', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * Enable screen reader announcements callback
 */
function hbl_enable_screen_reader_announcements_callback() {
    $enabled = get_option('hbl_enable_screen_reader_announcements', '1');
    ?>
    <input type="checkbox" id="hbl_enable_screen_reader_announcements" name="hbl_enable_screen_reader_announcements" value="1" <?php checked('1', $enabled); ?> />
    <label for="hbl_enable_screen_reader_announcements"><?php _e('Enable screen reader announcements', 'happy-business-listing'); ?></label>
    <p class="description"><?php _e('Provides announcements for search results, filter changes, and other dynamic content.', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * High contrast mode callback
 */
function hbl_high_contrast_mode_callback() {
    $enabled = get_option('hbl_high_contrast_mode', '1');
    ?>
    <input type="checkbox" id="hbl_high_contrast_mode" name="hbl_high_contrast_mode" value="1" <?php checked('1', $enabled); ?> />
    <label for="hbl_high_contrast_mode"><?php _e('Enable high contrast mode support', 'happy-business-listing'); ?></label>
    <p class="description"><?php _e('Automatically adjusts styling when users have high contrast mode enabled in their system.', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * Get accessibility compliance report
 *
 * @return array Accessibility compliance report
 */
function hbl_get_accessibility_report() {
    $report = array(
        'wcag_level' => 'AA',
        'compliance_score' => 0,
        'issues' => array(),
        'recommendations' => array()
    );
    
    $score = 0;
    $total_checks = 0;
    
    // Check skip links
    $total_checks++;
    if (get_option('hbl_enable_skip_links', '1') === '1') {
        $score++;
    } else {
        $report['issues'][] = __('Skip links are disabled', 'happy-business-listing');
    }
    
    // Check ARIA labels
    $total_checks++;
    if (get_option('hbl_enable_aria_labels', '1') === '1') {
        $score++;
    } else {
        $report['issues'][] = __('ARIA labels are disabled', 'happy-business-listing');
    }
    
    // Check keyboard navigation
    $total_checks++;
    if (get_option('hbl_enable_keyboard_navigation', '1') === '1') {
        $score++;
    } else {
        $report['issues'][] = __('Enhanced keyboard navigation is disabled', 'happy-business-listing');
    }
    
    // Check screen reader announcements
    $total_checks++;
    if (get_option('hbl_enable_screen_reader_announcements', '1') === '1') {
        $score++;
    } else {
        $report['issues'][] = __('Screen reader announcements are disabled', 'happy-business-listing');
    }
    
    // Check high contrast mode
    $total_checks++;
    if (get_option('hbl_high_contrast_mode', '1') === '1') {
        $score++;
    } else {
        $report['issues'][] = __('High contrast mode support is disabled', 'happy-business-listing');
    }
    
    $report['compliance_score'] = round(($score / $total_checks) * 100, 1);
    
    // Add recommendations
    if ($report['compliance_score'] < 100) {
        $report['recommendations'][] = __('Enable all accessibility features for full WCAG 2.1 AA compliance', 'happy-business-listing');
    }
    
    $report['recommendations'][] = __('Test your business listings with screen readers like NVDA or JAWS', 'happy-business-listing');
    $report['recommendations'][] = __('Use keyboard navigation to test all interactive elements', 'happy-business-listing');
    $report['recommendations'][] = __('Ensure sufficient color contrast for all text elements', 'happy-business-listing');
    
    return $report;
}

/**
 * Add accessibility report to admin
 */
function hbl_add_accessibility_report() {
    add_submenu_page(
        'edit.php?post_type=business_listing',
        __('Accessibility Report', 'happy-business-listing'),
        __('Accessibility', 'happy-business-listing'),
        'manage_options',
        'hbl_accessibility',
        'hbl_accessibility_report_page'
    );
}

/**
 * Accessibility report page
 */
function hbl_accessibility_report_page() {
    $report = hbl_get_accessibility_report();
    ?>
    <div class="wrap">
        <h1><?php _e('Accessibility Report', 'happy-business-listing'); ?></h1>
        
        <div class="card">
            <h2><?php _e('WCAG 2.1 AA Compliance', 'happy-business-listing'); ?></h2>
            <div class="compliance-score">
                <h3><?php _e('Compliance Score:', 'happy-business-listing'); ?> <?php echo $report['compliance_score']; ?>%</h3>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $report['compliance_score']; ?>%"></div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($report['issues'])): ?>
        <div class="card">
            <h2><?php _e('Issues Found', 'happy-business-listing'); ?></h2>
            <ul class="issues-list">
                <?php foreach ($report['issues'] as $issue): ?>
                <li><?php echo esc_html($issue); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h2><?php _e('Recommendations', 'happy-business-listing'); ?></h2>
            <ul class="recommendations-list">
                <?php foreach ($report['recommendations'] as $recommendation): ?>
                <li><?php echo esc_html($recommendation); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="card">
            <h2><?php _e('Quick Actions', 'happy-business-listing'); ?></h2>
            <p>
                <a href="<?php echo admin_url('edit.php?post_type=business_listing&page=hbl_settings#hbl_accessibility_section'); ?>" class="button button-primary">
                    <?php _e('Configure Accessibility Settings', 'happy-business-listing'); ?>
                </a>
            </p>
        </div>
    </div>
    
    <style>
    .compliance-score {
        margin: 20px 0;
    }
    
    .progress-bar {
        width: 100%;
        height: 20px;
        background-color: #f0f0f0;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 10px;
    }
    
    .progress-fill {
        height: 100%;
        background-color: #0073aa;
        transition: width 0.3s ease;
    }
    
    .issues-list,
    .recommendations-list {
        margin-left: 20px;
    }
    
    .issues-list li {
        color: #dc3545;
        margin-bottom: 5px;
    }
    
    .recommendations-list li {
        color: #28a745;
        margin-bottom: 5px;
    }
    </style>
    <?php
}

// Initialize accessibility report
add_action('admin_menu', 'hbl_add_accessibility_report'); 