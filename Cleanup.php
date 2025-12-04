<?php
/*
Plugin Name: Plugin Cleanup (Secure Edition)
Description: Find and safely delete inactive plugins with all their files and detected data — including orphaned files from previously removed plugins.
Version: 2.0 (Security Hardened)
Author: Fahad4x4 (Security Enhanced)
Author URI: https://getsimple-ce.ovh/
License: MIT
*/

// ============================================================================
// SECURITY: Prevent Direct Access
// ============================================================================
if (!defined('IN_GS')) {
    die('Direct access not allowed');
}

// Ensure session is started for CSRF tokens
if (function_exists('session_status')) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
} elseif (session_id() === '') {
    @session_start();
}

$thisfile = basename(__FILE__, ".php");

// ============================================================================
// CONFIGURATION
// ============================================================================
define('CLEANUP_LOG_FILE', GSDATAOTHERPATH . 'cleanup_audit.log');
define('CLEANUP_MAX_FILE_SIZE', 10485760); // 10MB limit for single file operations
define('CLEANUP_VERSION', '2.0');

// ============================================================================
// TRANSLATIONS
// ============================================================================
$translations = array(
    'en' => array(
        'PLUGIN_TITLE'        => 'Plugin Cleanup',
        'PLUGIN_DESC'         => 'Find and safely delete inactive plugins with all their files and orphaned data',
        'MENU_TITLE'          => 'Plugin Cleanup',
        'PAGE_TITLE'          => 'Plugin Cleanup Tool',
        'TOTAL_PLUGINS'       => 'Total Plugins',
        'ACTIVE'              => 'Active',
        'INACTIVE'            => 'Inactive',
        'ACTIVE_PLUGINS'      => 'Active Plugins',
        'NO_ACTIVE'           => 'No active plugins found',
        'INACTIVE_PLUGINS'    => 'Inactive Plugins',
        'NO_INACTIVE'         => 'No inactive plugins found - Your system is clean!',
        'PLUGIN_NAME'         => 'Plugin Name',
        'VERSION'             => 'Version',
        'STATUS'              => 'Status',
        'FILES'               => 'Files',
        'FOLDER_FILES'        => 'Folder Contents',
        'DATA'                => 'Data & Settings',
        'SIZE'                => 'Size',
        'TOTAL_SIZE'          => 'Total Size',
        'TOTAL_FILES'         => 'Total Files',
        'DELETE_BUTTON'       => 'Delete Selected Plugins',
        'NO_SELECTED'         => 'No plugins or files selected for deletion',
        'WARNING'             => 'Warning',
        'WARNING_DESC'        => 'This action cannot be undone. Make sure you select only the correct plugins.',
        'SUCCESS'             => 'Success',
        'PLUGINS_DELETED'     => 'plugins deleted successfully',
        'ERROR'               => 'Errors',
        'DETECTED_DATA'       => 'Detected Data Storage Methods',
        'AUTO_DETECTED'       => 'Auto-detected from plugin code',
        'SELECT_DATA'         => 'Select data to delete:',
        'SELECT_ALL_DATA'     => 'Select All Detected Files',
        'NO_DATA_FOUND'       => 'No data storage detected',
        'ORPHANED_DATA'       => 'Orphaned Data Files',
        'ORPHANED_DESC'       => 'These files don\'t belong to any plugin listed in plugins.xml — likely from previously removed plugins.',
        'DELETE_ORPHANED_BUTTON' => 'Delete Selected Orphaned Files',
        'SECURITY_ERROR'      => 'Security validation failed',
        'INVALID_TOKEN'       => 'Invalid security token. Please refresh the page and try again.',
        'UNAUTHORIZED'        => 'Unauthorized access attempt',
        'UNAUTHORIZED_MESSAGE'=> 'You do not have permission to access this page.',
        'CRITICAL_FILE_PROTECTED' => 'Critical system file protected from deletion',

        // New keys for report / UI
        'DELETION_REPORT'     => 'Deletion Report',
        'DETAILS'             => 'Details',
        'ITEMS_DELETED'       => 'Items Deleted',
        'ERRORS'              => 'Errors',
        'PROTECTED_SKIPPED'   => 'Protected / Skipped',
        'NOTE_LOGGED'         => 'Note: All operations have been logged for security audit.',
        'DETECTED_METHODS'    => 'Detected methods',
        'REFRESH_PAGE'        => 'Refresh Page',
    ),
    'ar' => array(
        'PLUGIN_TITLE'        => 'تنظيف الإضافات',
        'PLUGIN_DESC'         => 'البحث عن الإضافات غير المفعلة وحذفها بأمان مع جميع ملفاتها والبيانات اليتيمة',
        'MENU_TITLE'          => 'تنظيف الإضافات',
        'PAGE_TITLE'          => 'أداة تنظيف الإضافات',
        'TOTAL_PLUGINS'       => 'إجمالي الإضافات',
        'ACTIVE'              => 'النشطة',
        'INACTIVE'            => 'غير النشطة',
        'ACTIVE_PLUGINS'      => 'الإضافات النشطة',
        'NO_ACTIVE'           => 'لا توجد إضافات نشطة',
        'INACTIVE_PLUGINS'    => 'الإضافات غير النشطة',
        'NO_INACTIVE'         => 'لا توجد إضافات غير نشطة - نظامك نظيف!',
        'PLUGIN_NAME'         => 'اسم الإضافة',
        'VERSION'             => 'الإصدار',
        'STATUS'              => 'الحالة',
        'FILES'               => 'الملفات',
        'FOLDER_FILES'        => 'محتويات المجلد',
        'DATA'                => 'البيانات والإعدادات',
        'SIZE'                => 'الحجم',
        'TOTAL_SIZE'          => 'الحجم الكلي',
        'TOTAL_FILES'         => 'إجمالي الملفات',
        'DELETE_BUTTON'       => 'حذف الإضافات المختارة',
        'NO_SELECTED'         => 'لم يتم اختيار أي إضافة أو ملف للحذف',
        'WARNING'             => 'تحذير',
        'WARNING_DESC'        => 'لا يمكن التراجع عن هذه العملية. تأكد من اختيار الإضافات الصحيحة فقط.',
        'SUCCESS'             => 'نجح',
        'PLUGINS_DELETED'     => 'إضافات تم حذفها بنجاح',
        'ERROR'               => 'أخطاء',
        'DETECTED_DATA'       => 'طرق تخزين البيانات المكتشفة',
        'AUTO_DETECTED'       => 'مكتشفة تلقائياً من كود الإضافة',
        'SELECT_DATA'         => 'اختر البيانات للحذف:',
        'SELECT_ALL_DATA'     => 'تحديد جميع الملفات المكتشفة',
        'NO_DATA_FOUND'       => 'لم يتم اكتشاف تخزين بيانات',
        'ORPHANED_DATA'       => 'البيانات اليتيمة',
        'ORPHANED_DESC'       => 'هذه الملفات لا تنتمي إلى أي إضافة مسجلة في النظام (ربما لإضافات تم حذفها يدويًا سابقًا).',
        'DELETE_ORPHANED_BUTTON' => 'حذف الملفات اليتيمة المختارة',
        'SECURITY_ERROR'      => 'فشل التحقق الأمني',
        'INVALID_TOKEN'       => 'رمز الأمان غير صالح. يرجى تحديث الصفحة والمحاولة مرة أخرى.',
        'UNAUTHORIZED'        => 'محاولة وصول غير مصرح بها',
        'UNAUTHORIZED_MESSAGE'=> 'لا تملك صلاحية الوصول إلى هذه الصفحة.',
        'CRITICAL_FILE_PROTECTED' => 'ملف نظام حرج محمي من الحذف',

        // New keys for report / UI
        'DELETION_REPORT'     => 'تقرير الحذف',
        'DETAILS'             => 'التفاصيل',
        'ITEMS_DELETED'       => 'عناصر تم حذفها',
        'ERRORS'              => 'أخطاء',
        'PROTECTED_SKIPPED'   => 'محمي / تم تخطيه',
        'NOTE_LOGGED'         => 'ملاحظة: تم تسجيل جميع العمليات لأغراض التدقيق الأمني.',
        'DETECTED_METHODS'    => 'الطرق المكتشفة',
        'REFRESH_PAGE'        => 'تحديث الصفحة',
    )
);

$lang = substr($LANG, 0, 2);
$lang = isset($translations[$lang]) ? $lang : 'en';
$t    = $translations[$lang];

// ============================================================================
// PLUGIN REGISTRATION
// ============================================================================
register_plugin(
    $thisfile,
    $t['PLUGIN_TITLE'],
    CLEANUP_VERSION,
    'Fahad4x4',
    'https://getsimple-ce.ovh/',
    $t['PLUGIN_DESC'],
    'pages',
    'cleanup_plugins_show'
);

add_action('pages-sidebar', 'createSideMenu', array($thisfile, $t['MENU_TITLE']));

// ============================================================================
// SECURITY FUNCTIONS
// ============================================================================
/**
 * Generate secure CSRF token
 */
function cleanup_generate_token() {
    if (function_exists('session_status')) {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    } elseif (session_id() === '') {
        @session_start();
    }

    if (!isset($_SESSION['cleanup_token'])) {
        $_SESSION['cleanup_token']      = bin2hex(random_bytes(32));
        $_SESSION['cleanup_token_time'] = time();
    }
    return $_SESSION['cleanup_token'];
}

/**
 * Verify CSRF token
 */
function cleanup_verify_token($token) {
    if (function_exists('session_status')) {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    } elseif (session_id() === '') {
        @session_start();
    }

    if (!isset($_SESSION['cleanup_token']) || !isset($_SESSION['cleanup_token_time'])) {
        return false;
    }
    // Token expires after 2 hours
    if (time() - $_SESSION['cleanup_token_time'] > 7200) {
        unset($_SESSION['cleanup_token']);
        unset($_SESSION['cleanup_token_time']);
        return false;
    }
    return hash_equals($_SESSION['cleanup_token'], $token);
}

/**
 * Check if user has admin permissions
 */
function cleanup_check_permissions() {
    // GetSimple specific permission check
    if (!defined('GSADMIN')) {
        return false;
    }
    // Additional check: verify user is logged in
    if (!isset($_COOKIE['GS_ADMIN_USERNAME']) || $_COOKIE['GS_ADMIN_USERNAME'] === '') {
        return false;
    }
    return true;
}

/**
 * Validate file path is within allowed directory
 */
function cleanup_validate_path($file_path, $allowed_dir) {
    $real_path    = realpath($file_path);
    $real_allowed = realpath($allowed_dir);
    if (!$real_path || !$real_allowed) {
        return false;
    }
    // Ensure path is within allowed directory
    if (strpos($real_path, $real_allowed) !== 0) {
        cleanup_log_security_event("Path traversal attempt: $file_path", 'high');
        return false;
    }
    // Additional check: no parent directory references
    if (strpos($file_path, '..') !== false) {
        cleanup_log_security_event("Directory traversal detected: $file_path", 'high');
        return false;
    }
    return $real_path;
}

/**
 * Check if file is critical system file
 */
function cleanup_is_critical_file($file_path) {
    $basename = basename($file_path);
    // Critical file patterns
    $critical_patterns = array(
        '/^(pages|plugins|website|authorization|components|users|backups)\.xml$/i',
        '/^gsconfig\.php$/i',
        '/^\.htaccess$/i',
        '/^404\.xml$/i',
        '/^theme\.xml$/i',
        '/^settings\.xml$/i',
        '/^backup_/i',
        '/^restore_/i',
        '/^cache_/i',
    );
    foreach ($critical_patterns as $pattern) {
        if (preg_match($pattern, $basename)) {
            return true;
        }
    }
    // Additional check: files in root data directory (not subdirectories)
    $parent_dir = dirname($file_path);
    if (realpath($parent_dir) === realpath(GSDATAOTHERPATH)) {
        // Allow only plugin-specific files in root
        if (!preg_match('/^[a-z0-9_-]+_[a-z0-9_-]+\.(txt|json|xml|db|dat)$/i', $basename)) {
            return true;
        }
    }
    return false;
}

/**
 * Sanitize plugin ID
 */
function cleanup_sanitize_plugin_id($plugin_id) {
    // Allow only alphanumeric, dash, underscore
    return preg_replace('/[^a-zA-Z0-9_-]/', '', $plugin_id);
}

/**
 * Log security events
 */
function cleanup_log_security_event($message, $severity = 'medium') {
    $log_entry = sprintf(
        "[%s] [%s] [IP: %s] [User: %s] %s\n",
        date('Y-m-d H:i:s'),
        strtoupper($severity),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_COOKIE['GS_ADMIN_USERNAME'] ?? 'unknown',
        $message
    );
    error_log($log_entry, 3, CLEANUP_LOG_FILE);
}

/**
 * Log deletion operations
 */
function cleanup_log_deletion($plugin_id, $files, $success = true) {
    $status    = $success ? 'SUCCESS' : 'FAILED';
    $log_entry = sprintf(
        "[%s] [%s] [User: %s] Deleted plugin: %s | Files: %d | List: %s\n",
        date('Y-m-d H:i:s'),
        $status,
        $_COOKIE['GS_ADMIN_USERNAME'] ?? 'unknown',
        $plugin_id,
        count($files),
        implode(', ', array_slice($files, 0, 10)) . (count($files) > 10 ? '...' : '')
    );
    error_log($log_entry, 3, CLEANUP_LOG_FILE);
}

// ============================================================================
// MAIN DISPLAY FUNCTION
// ============================================================================
function cleanup_plugins_show() {
    global $LANG, $translations;

    // Security check #1: Verify permissions
    if (!cleanup_check_permissions()) {
        $lang = substr($LANG, 0, 2);
        $lang = isset($translations[$lang]) ? $lang : 'en';
        $t    = $translations[$lang];

        echo '<div style="background: #ffcdd2; padding: 15px; margin: 15px 0; border-radius: 4px; color: #c62828;">';
        echo '🔒 <strong>' . htmlspecialchars($t['UNAUTHORIZED']) . '</strong><br>';
        echo htmlspecialchars($t['UNAUTHORIZED_MESSAGE']);
        echo '</div>';
        cleanup_log_security_event("Unauthorized access attempt to Plugin Cleanup", 'high');
        return;
    }

    $lang = substr($LANG, 0, 2);
    $lang = isset($translations[$lang]) ? $lang : 'en';
    $t    = $translations[$lang];

    // Generate CSRF token
    $token = cleanup_generate_token();

    // Process deletion
    if (isset($_POST['cleanup_action'])) {
        // Security check #2: Verify CSRF token
        if (!isset($_POST['cleanup_token']) || !cleanup_verify_token($_POST['cleanup_token'])) {
            echo '<div style="background: #ffcdd2; padding: 15px; margin: 15px 0; border-radius: 4px; color: #c62828;">';
            echo '🔒 <strong>' . htmlspecialchars($t['SECURITY_ERROR']) . ':</strong> ' . htmlspecialchars($t['INVALID_TOKEN']);
            echo '</div>';
            cleanup_log_security_event("CSRF token validation failed", 'high');
            return;
        }
        cleanup_process_deletion($_POST, $t);
        return;
    }

    $all_plugins    = cleanup_get_all_plugins();
    $active_plugins = cleanup_get_active_plugins();
    $inactive_plugins = array_diff_key($all_plugins, $active_plugins);
    $all_known_ids    = array_keys($all_plugins);
    $orphaned_files   = cleanup_get_orphaned_data_files($all_known_ids);
    ?>
    <div style="max-width: 1200px;">
        <h2><?php echo htmlspecialchars($t['PAGE_TITLE']); ?> <span style="font-size: 14px; color: #999;">v<?php echo CLEANUP_VERSION; ?></span></h2>
        <!-- Security Notice -->
        <div style="background: #e3f2fd; padding: 12px; margin: 15px 0; border-radius: 4px; border-left: 4px solid #2196F3;">
            <small style="font-size: 13px; color: #1565c0;">
                🔒 <strong>Security:</strong> All operations are logged and protected with CSRF tokens.
                Critical system files are automatically protected from deletion.
            </small>
        </div>
        <!-- Statistics -->
        <div style="background: #f0f0f0; padding: 12px; margin: 15px 0; border-radius: 4px; border-left: 4px solid #2196F3;">
            <small style="font-size: 14px;">
                📊 <?php echo htmlspecialchars($t['TOTAL_PLUGINS']); ?>: <strong><?php echo count($all_plugins); ?></strong> |
                ✓ <?php echo htmlspecialchars($t['ACTIVE']); ?>: <strong style="color: green;"><?php echo count($active_plugins); ?></strong> |
                ✗ <?php echo htmlspecialchars($t['INACTIVE']); ?>: <strong style="color: red;"><?php echo count($inactive_plugins); ?></strong> |
                🗑️ <?php echo htmlspecialchars($t['ORPHANED_DATA']); ?>: <strong style="color: #ff9800;"><?php echo count($orphaned_files); ?></strong>
            </small>
        </div>
        <!-- Active Plugins -->
        <h3 style="color: green; border-bottom: 2px solid green; padding-bottom: 10px;">
            ✓ <?php echo htmlspecialchars($t['ACTIVE_PLUGINS']); ?> (<?php echo count($active_plugins); ?>)
        </h3>
        <?php if (empty($active_plugins)): ?>
            <p style="color: #999; padding: 10px; background: #f5f5f5; border-radius: 4px;"><?php echo htmlspecialchars($t['NO_ACTIVE']); ?></p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background: #e8f5e9;">
                        <th style="padding: 12px; text-align: left; border: 1px solid #81c784;"><?php echo htmlspecialchars($t['PLUGIN_NAME']); ?></th>
                        <th style="padding: 12px; text-align: center; border: 1px solid #81c784; width: 120px;"><?php echo htmlspecialchars($t['VERSION']); ?></th>
                        <th style="padding: 12px; text-align: center; border: 1px solid #81c784; width: 120px;"><?php echo htmlspecialchars($t['STATUS']); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_plugins as $id => $plugin): ?>
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td style="padding: 12px; border: 1px solid #e0e0e0;">
                                <strong><?php echo htmlspecialchars($plugin['info']['name'] ?? $id); ?></strong>
                            </td>
                            <td style="padding: 12px; text-align: center; border: 1px solid #e0e0e0;">
                                v<?php echo htmlspecialchars($plugin['info']['version'] ?? 'N/A'); ?>
                            </td>
                            <td style="padding: 12px; text-align: center; border: 1px solid #e0e0e0; color: green;">
                                ✓ <?php echo htmlspecialchars($t['ACTIVE']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <!-- Inactive Plugins -->
        <h3 style="color: red; border-bottom: 2px solid red; padding-bottom: 10px; margin-top: 30px;">
            ✗ <?php echo htmlspecialchars($t['INACTIVE_PLUGINS']); ?> (<?php echo count($inactive_plugins); ?>)
        </h3>
        <?php if (empty($inactive_plugins)): ?>
            <p style="color: #666; padding: 10px; background: #f5f5f5; border-radius: 4px;"><?php echo htmlspecialchars($t['NO_INACTIVE']); ?></p>
        <?php else: ?>
            <form method="POST" onsubmit="return confirm(<?php echo htmlspecialchars(json_encode($t['WARNING_DESC']), ENT_QUOTES); ?>);">
                <input type="hidden" name="cleanup_token" value="<?php echo htmlspecialchars($token); ?>">
                <?php foreach ($inactive_plugins as $id => $plugin): ?>
                    <div style="background: #fff; border: 1px solid #e0e0e0; padding: 15px; margin: 15px 0; border-radius: 4px;">
                        <div style="display: flex; align-items: center;">
                            <input type="checkbox" name="plugins_to_delete[]" value="<?php echo htmlspecialchars($id); ?>" style="margin-right: 10px;">
                            <strong style="font-size: 16px;"><?php echo htmlspecialchars($plugin['info']['name'] ?? $id); ?></strong>
                            <span style="color: #999; margin-left: 10px;">v<?php echo htmlspecialchars($plugin['info']['version'] ?? 'N/A'); ?></span>
                            <span style="color: red; margin-left: 10px;">✗ <?php echo htmlspecialchars($t['INACTIVE']); ?></span>
                        </div>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f0f0f0;">
                            <?php
                            $plugin_info   = cleanup_get_plugin_details($id);
                            $detected_data = cleanup_analyze_plugin_code($id);
                            ?>
                            <!-- Main File -->
                            <p style="margin: 8px 0; color: #333;">
                                <strong>📄 <?php echo htmlspecialchars($t['FILES']); ?>:</strong>
                            </p>
                            <ul style="margin: 8px 0 8px 20px; list-style: disc; color: #666;">
                                <li><?php echo htmlspecialchars($plugin_info['main_file']); ?>
                                    <span style="color: #999; font-size: 12px;">(<?php echo htmlspecialchars($plugin_info['main_file_size']); ?>)</span>
                                </li>
                            </ul>
                            <!-- Folder Contents -->
                            <?php if (!empty($plugin_info['folder_files'])): ?>
                                <p style="margin: 8px 0; color: #333;">
                                    <strong>📁 <?php echo htmlspecialchars($t['FOLDER_FILES']); ?>:</strong>
                                </p>
                                <ul style="margin: 8px 0 8px 20px; list-style: disc; color: #666; max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($plugin_info['folder_files'] as $file_path): ?>
                                        <li style="font-size: 12px;"><?php echo $file_path; /* Already escaped in cleanup_scan_dir */ ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <!-- Detected Data -->
                            <?php if (!empty($detected_data['files'])): ?>
                                <p style="margin: 8px 0; color: #333;">
                                    <strong>💾 <?php echo htmlspecialchars($t['DETECTED_DATA']); ?>:</strong>
                                    <span style="color: #4CAF50; font-size: 12px; margin-left: 10px;">✓ <?php echo htmlspecialchars($t['AUTO_DETECTED']); ?></span>
                                </p>
                                <div style="background: #f0f8ff; padding: 10px; border: 1px solid #81d4fa; border-radius: 4px; margin: 8px 0 8px 20px;">
                                    <?php if (!empty($detected_data['methods'])): ?>
                                        <p style="margin: 5px 0; font-size: 13px; color: #0277bd;">
                                            <strong>🔍 <?php echo htmlspecialchars($t['DETECTED_METHODS']); ?>:</strong>
                                            <?php echo htmlspecialchars(implode(', ', $detected_data['methods'])); ?>
                                        </p>
                                    <?php endif; ?>
                                    <label style="display: block; margin: 8px 0;">
                                        <input type="checkbox" class="select-data-for-<?php echo htmlspecialchars($id); ?>" value="all" onchange="toggleAllData('<?php echo htmlspecialchars($id, ENT_QUOTES); ?>')">
                                        <strong><?php echo htmlspecialchars($t['SELECT_ALL_DATA']); ?></strong>
                                    </label>
                                    <?php foreach ($detected_data['files'] as $file_info): ?>
                                        <label style="display: block; margin: 5px 0;">
                                            <input type="checkbox" name="data_to_delete[<?php echo htmlspecialchars($id); ?>][]" 
                                                   value="<?php echo htmlspecialchars($file_info['path']); ?>" 
                                                   class="select-data-for-<?php echo htmlspecialchars($id); ?>">
                                            <?php echo htmlspecialchars(basename($file_info['path'])); ?>
                                            <span style="color: #999; font-size: 11px;">(<?php echo htmlspecialchars($file_info['size']); ?> - <?php echo htmlspecialchars($file_info['type']); ?>)</span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="margin: 8px 0; color: #999;">
                                    💾 <?php echo htmlspecialchars($t['NO_DATA_FOUND']); ?>
                                </p>
                            <?php endif; ?>
                            <!-- Summary -->
                            <p style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f0f0f0; color: #999; font-size: 13px;">
                                📊 <?php echo htmlspecialchars($t['TOTAL_SIZE']); ?>: <strong><?php echo htmlspecialchars($plugin_info['total_size']); ?></strong> |
                                <?php echo htmlspecialchars($t['TOTAL_FILES']); ?>: <strong><?php echo htmlspecialchars($plugin_info['total_files']); ?></strong>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div style="padding: 15px; background: #fff3e0; border: 1px solid #ffe0b2; border-radius: 4px; margin: 15px 0;">
                    <p style="margin: 0 0 10px 0; color: #e65100;">
                        ⚠️ <strong><?php echo htmlspecialchars($t['WARNING']); ?>:</strong> <?php echo htmlspecialchars($t['WARNING_DESC']); ?>
                    </p>
                    <input type="hidden" name="cleanup_action" value="1">
                    <button type="submit" class="submit" style="background: #f44336; color: white; padding: 10px 25px; border: none; cursor: pointer; border-radius: 4px; font-size: 14px;">
                        🗑️ <?php echo htmlspecialchars($t['DELETE_BUTTON']); ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>
        <!-- Orphaned Data Section -->
        <?php if (!empty($orphaned_files)): ?>
            <h3 style="color: #ff9800; border-bottom: 2px solid #ff9800; padding-bottom: 10px; margin-top: 30px;">
                🗑️ <?php echo htmlspecialchars($t['ORPHANED_DATA']); ?> (<?php echo count($orphaned_files); ?>)
            </h3>
            <div style="background: #fff8e1; border: 1px solid #ffe0b2; padding: 15px; margin: 15px 0; border-radius: 4px;">
                <p style="color: #e65100; margin-top: 0;">
                    ⚠️ <?php echo htmlspecialchars($t['ORPHANED_DESC']); ?>
                </p>
                <form method="POST" onsubmit="return confirm(<?php echo htmlspecialchars(json_encode($t['WARNING_DESC']), ENT_QUOTES); ?>);">
                    <input type="hidden" name="cleanup_token" value="<?php echo htmlspecialchars($token); ?>">
                    <?php foreach ($orphaned_files as $file_info): ?>
                        <label style="display: block; margin: 8px 0; padding: 8px; background: #fffde7; border-radius: 4px;">
                            <input type="checkbox" name="orphaned_files_to_delete[]" 
                                   value="<?php echo htmlspecialchars($file_info['path']); ?>"
                                   style="margin-right: 10px;">
                            <strong><?php echo htmlspecialchars($file_info['name']); ?></strong>
                            <span style="color: #999; font-size: 12px;">(<?php echo htmlspecialchars($file_info['size']); ?> - <?php echo htmlspecialchars($file_info['type']); ?>)</span>
                        </label>
                    <?php endforeach; ?>
                    <input type="hidden" name="cleanup_action" value="1">
                    <input type="hidden" name="delete_orphaned" value="1">
                    <button type="submit" class="submit" style="background: #ff9800; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-size: 14px; margin-top: 10px;">
                        🧹 <?php echo htmlspecialchars($t['DELETE_ORPHANED_BUTTON']); ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <script>
    function toggleAllData(pluginId) {
        // Escape plugin ID for use in selector
        var escapedId = pluginId.replace(/[^a-zA-Z0-9_-]/g, '\\$&');
        var checkboxes = document.querySelectorAll('.select-data-for-' + escapedId);
        if (checkboxes.length > 0) {
            var allChecked = checkboxes[0].checked;
            for (var i = 1; i < checkboxes.length; i++) {
                checkboxes[i].checked = allChecked;
            }
        }
    }
    </script>
    <?php
}

// ============================================================================
// PLUGIN SCANNING FUNCTIONS
// ============================================================================
/**
 * Get all .php plugins in /plugins/
 */
function cleanup_get_all_plugins() {
    $plugins     = array();
    $plugins_dir = GSPLUGINPATH;

    if (!is_dir($plugins_dir)) {
        return $plugins;
    }

    $files = scandir($plugins_dir);
    if ($files === false) {
        error_log("Plugin Cleanup: Failed to scan plugins directory");
        return $plugins;
    }

    foreach ($files as $file) {
        if (in_array($file, array('.', '..', '.htaccess'), true)) {
            continue;
        }
        if (substr($file, -4) === '.php') {
            $plugin_id   = substr($file, 0, -4);
            $plugin_path = $plugins_dir . $file;
            $info        = cleanup_parse_plugin_header($plugin_path);
            $plugins[$plugin_id] = array(
                'id'   => $plugin_id,
                'file' => $file,
                'path' => $plugin_path,
                'info' => $info
            );
        }
    }
    return $plugins;
}

/**
 * Parse plugin header (improved performance)
 */
function cleanup_parse_plugin_header($plugin_path) {
    $info = array(
        'name'        => '',
        'version'     => '',
        'description' => '',
        'author'      => ''
    );

    if (!file_exists($plugin_path)) {
        $info['name'] = basename($plugin_path, '.php');
        return $info;
    }

    // Read only first 8KB for performance
    $handle = fopen($plugin_path, 'r');
    if ($handle === false) {
        $info['name'] = basename($plugin_path, '.php');
        return $info;
    }

    $file_data = fread($handle, 8192);
    fclose($handle);

    if ($file_data === false) {
        $info['name'] = basename($plugin_path, '.php');
        return $info;
    }

    // Parse headers
    if (preg_match('/Plugin\s+Name:\s*(.+?)(\r?\n|$)/i', $file_data, $match)) {
        $info['name'] = trim($match[1]);
    }
    if (preg_match('/Version:\s*(.+?)(\r?\n|$)/i', $file_data, $match)) {
        $info['version'] = trim($match[1]);
    }
    if (preg_match('/Description:\s*(.+?)(\r?\n|$)/i', $file_data, $match)) {
        $info['description'] = trim($match[1]);
    }
    if (preg_match('/Author:\s*(.+?)(\r?\n|$)/i', $file_data, $match)) {
        $info['author'] = trim($match[1]);
    }

    if ($info['name'] === '') {
        $info['name'] = basename($plugin_path, '.php');
    }

    return $info;
}

/**
 * Get active plugins from plugins.xml
 */
function cleanup_get_active_plugins() {
    $active       = array();
    $plugins_file = GSDATAOTHERPATH . 'plugins.xml';

    if (!file_exists($plugins_file)) {
        return $active;
    }

    try {
        libxml_use_internal_errors(true); // Suppress XML errors
        $xml = simplexml_load_file($plugins_file);
        libxml_clear_errors();

        if ($xml && isset($xml->item)) {
            foreach ($xml->item as $item) {
                $plugin_file = (string) $item->plugin;
                $enabled     = ((string) $item->enabled === 'true');
                if ($enabled && $plugin_file !== '') {
                    $plugin_id   = substr($plugin_file, 0, -4);
                    $plugin_path = GSPLUGINPATH . $plugin_file;
                    $info        = cleanup_parse_plugin_header($plugin_path);
                    $active[$plugin_id] = array(
                        'id'   => $plugin_id,
                        'file' => $plugin_file,
                        'info' => $info
                    );
                }
            }
        }
    } catch (Exception $e) {
        error_log("Plugin Cleanup: Error reading plugins.xml - " . $e->getMessage());
    }

    return $active;
}

/**
 * Get plugin folder & data size/files
 */
function cleanup_get_plugin_details($plugin_id) {
    $plugins_dir = GSPLUGINPATH;
    $data_dir    = GSDATAOTHERPATH;

    $details = array(
        'main_file'      => $plugin_id . '.php',
        'main_file_size' => '0 KB',
        'folder_files'   => array(),
        'data_files'     => array(),
        'total_size'     => 0,
        'total_files'    => 0
    );

    $main_file_path = $plugins_dir . $plugin_id . '.php';
    $total_size     = 0;
    $total_files    = 0;

    // Main file
    if (file_exists($main_file_path)) {
        $sz                      = filesize($main_file_path);
        $details['main_file_size'] = cleanup_format_size($sz);
        $total_size             += $sz;
        $total_files++;
    }

    // Plugin folder
    $plugin_folder = $plugins_dir . $plugin_id . '/';
    if (is_dir($plugin_folder)) {
        $res                     = cleanup_scan_dir($plugin_folder);
        $details['folder_files'] = $res['files'];
        $total_size             += $res['size'];
        $total_files            += $res['count'];
    }

    // Data folder
    $data_folder = $data_dir . $plugin_id . '/';
    if (is_dir($data_folder)) {
        $res                     = cleanup_scan_dir($data_folder);
        $details['data_files']   = $res['files'];
        $total_size             += $res['size'];
        $total_files            += $res['count'];
    }

    $details['total_size']  = cleanup_format_size($total_size);
    $details['total_files'] = $total_files;

    return $details;
}

/**
 * Scan directory recursively (with size limits)
 */
function cleanup_scan_dir($dir, $prefix = '', $max_depth = 10, $current_depth = 0) {
    $result = array(
        'files' => array(),
        'size'  => 0,
        'count' => 0
    );

    if (!is_dir($dir) || $current_depth >= $max_depth) {
        return $result;
    }

    $files = scandir($dir);
    if ($files === false) {
        error_log("Plugin Cleanup: Failed to scan directory: $dir");
        return $result;
    }

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = $dir . $file;
        $rel  = $prefix ? $prefix . '/' . $file : $file;

        if (is_dir($path)) {
            $sub = cleanup_scan_dir($path . '/', $rel, $max_depth, $current_depth + 1);
            $result['files'] = array_merge($result['files'], $sub['files']);
            $result['size'] += $sub['size'];
            $result['count'] += $sub['count'];
        } else {
            $sz = filesize($path);
            if ($sz === false) {
                $sz = 0;
            }
            $result['files'][] = htmlspecialchars($rel) . ' (' . cleanup_format_size($sz) . ')';
            $result['size']   += $sz;
            $result['count']++;
        }
    }

    return $result;
}

// ============================================================================
// DATA DETECTION FUNCTIONS
// ============================================================================
/**
 * Analyze plugin code for data patterns (improved)
 */
function cleanup_analyze_plugin_code($plugin_id) {
    $plugin_file = GSPLUGINPATH . $plugin_id . '.php';

    if (!file_exists($plugin_file)) {
        return array('files' => array(), 'methods' => array());
    }

    // Performance: read only first 100KB
    $handle = fopen($plugin_file, 'r');
    if ($handle === false) {
        return array('files' => array(), 'methods' => array());
    }

    $code = fread($handle, 102400);
    fclose($handle);

    if ($code === false) {
        return array('files' => array(), 'methods' => array());
    }

    $methods  = array();
    $patterns = array(
        '/file_(put|get)_contents/i'        => 'file_put_contents',
        '/fopen\s*\(\s*[\'"]([^\'"]+)[\'"]/i' => 'fopen/fwrite',
        '/json_(encode|decode)/i'           => 'JSON',
        '/(simplexml_load_file|SimpleXMLElement|XML)/i' => 'XML',
        '/(sqlite|PDO|mysqli|mysql_|wpdb|db|database)/i' => 'Database'
    );

    foreach ($patterns as $pattern => $method) {
        if (preg_match($pattern, $code)) {
            $methods[] = $method;
        }
    }

    $files = cleanup_find_data_files_by_code($plugin_id, $code);

    return array(
        'methods' => array_unique($methods),
        'files'   => $files
    );
}

/**
 * Find data files by analyzing code
 */
function cleanup_find_data_files_by_code($plugin_id, $code) {
    $files    = array();
    $data_dir = GSDATAOTHERPATH;

    if (!is_dir($data_dir)) {
        return $files;
    }

    // Extract potential file patterns from code
    $patterns = array();

    // Pattern 1: File extensions
    if (preg_match_all('/[\'"]([^\'"]*\.(txt|json|xml|db|dat|csv|log|yaml|yml))[^\'"]*[\'"]/i', $code, $m1)) {
        $patterns = array_merge($patterns, $m1[1]);
    }

    // Pattern 2: Variable assignments
    if (preg_match_all('/\$(\w*file\w*|\w*path\w*|\w*dir\w*)\s*=\s*[\'"]([^\'"]+)[\'"]/', $code, $m2)) {
        $patterns = array_merge($patterns, $m2[2]);
    }

    // Pattern 3: GSDATAOTHERPATH usage
    if (preg_match_all('/GSDATAOTHERPATH\s*[.\+]\s*[\'"]?([^\'"\s\);]+)/i', $code, $m3)) {
        $patterns = array_merge($patterns, $m3[1]);
    }

    $plugin_lower  = strtolower($plugin_id);
    $scan_result   = scandir($data_dir);

    if ($scan_result === false) {
        return $files;
    }

    foreach ($scan_result as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $full = $data_dir . $file;

        if (!is_file($full)) {
            continue;
        }

        // Security: Check file size
        $file_size = filesize($full);
        if ($file_size === false || $file_size > CLEANUP_MAX_FILE_SIZE) {
            continue;
        }

        // Security: Validate path
        if (!cleanup_validate_path($full, $data_dir)) {
            continue;
        }

        // Security: Skip critical files
        if (cleanup_is_critical_file($full)) {
            continue;
        }

        $f_lower = strtolower($file);
        $ext     = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // Only process known data extensions
        if ($ext !== '' && !in_array($ext, array('txt', 'json', 'xml', 'db', 'dat', 'csv', 'log', 'yaml', 'yml'), true)) {
            continue;
        }

        $match = false;

        // Check if filename contains plugin ID
        if (strpos($f_lower, $plugin_lower) !== false) {
            $match = true;
        }

        // Check against extracted patterns
        if (!$match) {
            foreach ($patterns as $p) {
                $p_clean = strtolower(trim($p, '/\\'));
                if ($p_clean !== '' && strpos($f_lower, $p_clean) !== false) {
                    $match = true;
                    break;
                }
            }
        }

        // Check common naming patterns
        if (!$match && preg_match('/^[a-z0-9_-]+_\w+\.(txt|json|xml|db|dat|csv|log|yaml|yml)$/i', $file)) {
            $match = true;
        }

        if ($match) {
            $files[] = array(
                'path' => $full,
                'name' => $file,
                'size' => cleanup_format_size($file_size),
                'type' => $ext ?: 'unknown'
            );
        }
    }

    return $files;
}

/**
 * Find orphaned data files (improved security)
 */
function cleanup_get_orphaned_data_files($known_plugin_ids) {
    $orphaned = array();
    $data_dir = GSDATAOTHERPATH;

    if (!is_dir($data_dir)) {
        return $orphaned;
    }

    $files = scandir($data_dir);

    if ($files === false) {
        error_log("Plugin Cleanup: Failed to scan data directory");
        return $orphaned;
    }

    $known_ids_lower = array_map('strtolower', $known_plugin_ids);
    $data_extensions = array('txt', 'json', 'xml', 'db', 'dat', 'csv', 'log', 'yaml', 'yml');

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $full_path = $data_dir . $file;

        if (!is_file($full_path)) {
            continue;
        }

        // Security: Validate path
        if (!cleanup_validate_path($full_path, $data_dir)) {
            continue;
        }

        // Security: Skip critical files
        if (cleanup_is_critical_file($full_path)) {
            continue;
        }

        // Security: Check file size
        $file_size = filesize($full_path);
        if ($file_size === false || $file_size > CLEANUP_MAX_FILE_SIZE) {
            continue;
        }

        $f_lower = strtolower($file);
        $ext     = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // Skip non-data extensions (if extension exists)
        if ($ext !== '' && !in_array($ext, $data_extensions, true)) {
            continue;
        }

        // Check if belongs to any known plugin
        $belongs_to_known = false;
        foreach ($known_ids_lower as $id) {
            if ($id !== '') {
                $pattern = '/' . preg_quote($id, '/') . '/i';
                if (preg_match($pattern, $f_lower)) {
                    $belongs_to_known = true;
                    break;
                }
            }
        }

        // If no match → potentially orphaned
        if (!$belongs_to_known) {
            $orphaned[] = array(
                'path' => $full_path,
                'name' => $file,
                'size' => cleanup_format_size($file_size),
                'type' => $ext ?: 'unknown'
            );
        }
    }

    return $orphaned;
}

// ============================================================================
// DELETION FUNCTIONS
// ============================================================================
/**
 * Process deletion (with enhanced security)
 */
function cleanup_process_deletion($post_data, $t) {
    $deleted       = 0;
    $errors        = 0;
    $skipped       = 0;
    $deletion_info = array();

    // Handle orphaned files
    if (isset($post_data['delete_orphaned']) && !empty($post_data['orphaned_files_to_delete'])) {
        foreach ($post_data['orphaned_files_to_delete'] as $file_path) {
            // Security: Validate path
            $safe_path = cleanup_validate_path($file_path, GSDATAOTHERPATH);
            if (!$safe_path || !is_file($safe_path)) {
                $deletion_info[] = "⚠️ Invalid path skipped: " . basename($file_path);
                $skipped++;
                continue;
            }

            // Security: Check if critical file
            if (cleanup_is_critical_file($safe_path)) {
                $deletion_info[] = "🔒 Protected critical file: " . basename($safe_path);
                cleanup_log_security_event("Attempted to delete critical file: $safe_path", 'high');
                $skipped++;
                continue;
            }

            // Attempt deletion
            if (@unlink($safe_path)) {
                $deletion_info[] = "✓ Deleted orphaned file: " . basename($safe_path);
                cleanup_log_deletion('orphaned', array(basename($safe_path)), true);
                $deleted++;
            } else {
                $deletion_info[] = "✗ Failed to delete: " . basename($safe_path);
                cleanup_log_deletion('orphaned', array(basename($safe_path)), false);
                $errors++;
            }
        }
    }

    // Handle plugins
    if (isset($post_data['plugins_to_delete']) && !empty($post_data['plugins_to_delete'])) {
        foreach ($post_data['plugins_to_delete'] as $plugin_id) {
            $plugin_id = cleanup_sanitize_plugin_id($plugin_id);
            if ($plugin_id === '') {
                $deletion_info[] = "⚠️ Invalid plugin ID skipped";
                $skipped++;
                continue;
            }

            $deleted_files = array();
            $plugin_errors = 0;

            // Main file
            $plugin_file = GSPLUGINPATH . $plugin_id . '.php';
            if (file_exists($plugin_file)) {
                // Security: Validate it's really a plugin file
                $safe_plugin_path = cleanup_validate_path($plugin_file, GSPLUGINPATH);
                if ($safe_plugin_path && substr($safe_plugin_path, -4) === '.php') {
                    if (@unlink($safe_plugin_path)) {
                        $deletion_info[] = "✓ Deleted main file: $plugin_id.php";
                        $deleted_files[] = "$plugin_id.php";
                        $deleted++;
                    } else {
                        $deletion_info[] = "✗ Failed to delete: $plugin_id.php";
                        $plugin_errors++;
                        $errors++;
                    }
                } else {
                    $deletion_info[] = "⚠️ Invalid plugin path: $plugin_id.php";
                    $skipped++;
                }
            }

            // Plugin folder
            $plugin_folder = GSPLUGINPATH . $plugin_id . '/';
            if (is_dir($plugin_folder)) {
                $safe_folder = cleanup_validate_path($plugin_folder, GSPLUGINPATH);
                if ($safe_folder && is_dir($safe_folder)) {
                    if (cleanup_remove_directory($safe_folder . (substr($safe_folder, -1) === '/' ? '' : '/'))) {
                        $deletion_info[] = "✓ Deleted folder: $plugin_id/";
                        $deleted_files[] = "$plugin_id/ (folder)";
                        $deleted++;
                    } else {
                        $deletion_info[] = "✗ Failed to delete folder: $plugin_id/";
                        $plugin_errors++;
                        $errors++;
                    }
                }
            }

            // Data folder
            $data_dir = GSDATAOTHERPATH . $plugin_id . '/';
            if (is_dir($data_dir)) {
                $safe_data_dir = cleanup_validate_path($data_dir, GSDATAOTHERPATH);
                if ($safe_data_dir && is_dir($safe_data_dir)) {
                    if (cleanup_remove_directory($safe_data_dir . (substr($safe_data_dir, -1) === '/' ? '' : '/'))) {
                        $deletion_info[] = "✓ Deleted data folder: /data/other/$plugin_id/";
                        $deleted_files[] = "data/$plugin_id/ (folder)";
                        $deleted++;
                    } else {
                        $deletion_info[] = "✗ Failed to delete data folder: /data/other/$plugin_id/";
                        $plugin_errors++;
                        $errors++;
                    }
                }
            }

            // User-selected extra data files
            if (!empty($post_data['data_to_delete']) && !empty($post_data['data_to_delete'][$plugin_id])) {
                foreach ($post_data['data_to_delete'][$plugin_id] as $data_file) {
                    $safe_path = cleanup_validate_path($data_file, GSDATAOTHERPATH);
                    if (!$safe_path || !is_file($safe_path)) {
                        $deletion_info[] = "⚠️ Invalid data file path skipped";
                        $skipped++;
                        continue;
                    }
                    if (cleanup_is_critical_file($safe_path)) {
                        $deletion_info[] = "🔒 Protected critical file: " . basename($safe_path);
                        cleanup_log_security_event("Attempted to delete critical file: $safe_path", 'high');
                        $skipped++;
                        continue;
                    }
                    if (@unlink($safe_path)) {
                        $deletion_info[] = "✓ Deleted data file: " . basename($safe_path);
                        $deleted_files[] = basename($safe_path);
                        $deleted++;
                    } else {
                        $deletion_info[] = "✗ Failed to delete: " . basename($safe_path);
                        $plugin_errors++;
                        $errors++;
                    }
                }
            }

            // Log the plugin deletion
            if (!empty($deleted_files)) {
                cleanup_log_deletion($plugin_id, $deleted_files, $plugin_errors === 0);
            }
        }
    }

    // Check if nothing was selected
    $has_plugins  = !empty($post_data['plugins_to_delete']);
    $has_orphaned = !empty($post_data['orphaned_files_to_delete']);
    if (!$has_plugins && !$has_orphaned) {
        echo '<div style="background: #ffcdd2; padding: 12px; margin: 15px 0; border-radius: 4px; border-left: 4px solid #c62828; color: #c62828;">';
        echo '⚠️ ' . htmlspecialchars($t['NO_SELECTED']);
        echo '</div>';
        return;
    }

    // Output report
    echo '<div style="max-width: 1200px;">';
    echo '<h2 style="color: #333; margin-top: 0;">🗑️ ' . htmlspecialchars($t['DELETION_REPORT']) . '</h2>';

    // Details
    if (!empty($deletion_info)) {
        echo '<div style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 4px; border: 1px solid #ddd;">';
        echo '<h3 style="margin-top: 0; color: #333;">📋 ' . htmlspecialchars($t['DETAILS']) . ':</h3>';
        echo '<ul style="list-style: none; padding: 0; margin: 0;">';
        foreach ($deletion_info as $info) {
            $color = '#333';
            if (strpos($info, '✓') === 0) {
                $color = '#2e7d32';
            } elseif (strpos($info, '🔒') === 0) {
                $color = '#1565c0';
            } elseif (strpos($info, '⚠️') === 0) {
                $color = '#f57c00';
            } elseif (strpos($info, '✗') === 0) {
                $color = '#c62828';
            }
            echo '<li style="padding: 8px 0; color: ' . $color . '; border-bottom: 1px solid #e0e0e0;">' . htmlspecialchars($info) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    // Summary
    echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;">';
    if ($deleted > 0) {
        echo '<div style="background: #c8e6c9; padding: 15px; border-radius: 4px; border-left: 4px solid #2e7d32;">';
        echo '<div style="font-size: 32px; font-weight: bold; color: #2e7d32;">' . (int) $deleted . '</div>';
        echo '<div style="color: #2e7d32; font-size: 14px;">✓ ' . htmlspecialchars($t['ITEMS_DELETED']) . '</div>';
        echo '</div>';
    }
    if ($errors > 0) {
        echo '<div style="background: #ffcdd2; padding: 15px; border-radius: 4px; border-left: 4px solid #c62828;">';
        echo '<div style="font-size: 32px; font-weight: bold; color: #c62828;">' . (int) $errors . '</div>';
        echo '<div style="color: #c62828; font-size: 14px;">✗ ' . htmlspecialchars($t['ERRORS']) . '</div>';
        echo '</div>';
    }
    if ($skipped > 0) {
        echo '<div style="background: #fff3e0; padding: 15px; border-radius: 4px; border-left: 4px solid #f57c00;">';
        echo '<div style="font-size: 32px; font-weight: bold; color: #f57c00;">' . (int) $skipped . '</div>';
        echo '<div style="color: #f57c00; font-size: 14px;">⚠️ ' . htmlspecialchars($t['PROTECTED_SKIPPED']) . '</div>';
        echo '</div>';
    }
    echo '</div>';

    // Actions
    echo '<div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-radius: 4px; border-left: 4px solid #2196F3;">';
    echo '<p style="margin: 0 0 10px 0; color: #1565c0;">📝 <strong>' . htmlspecialchars($t['NOTE_LOGGED']) . '</strong></p>';
    echo '<button onclick="location.href=\'?id=' . htmlspecialchars($GLOBALS['thisfile'], ENT_QUOTES) . '\'" class="submit" style="background: #2196F3; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; font-size: 14px;">';
    echo '🔄 ' . htmlspecialchars($t['REFRESH_PAGE']);
    echo '</button>';
    echo '</div>';
    echo '</div>';
}

/**
 * Remove directory recursively (with safety checks)
 */
function cleanup_remove_directory($dir) {
    if (!is_dir($dir)) {
        return false;
    }

    // Security: Validate path
    $real_dir = realpath($dir);
    $allowed1 = realpath(GSPLUGINPATH);
    $allowed2 = realpath(GSDATAOTHERPATH);

    if (
        $real_dir === false ||
        (
            ($allowed1 && strpos($real_dir, $allowed1) !== 0) &&
            ($allowed2 && strpos($real_dir, $allowed2) !== 0)
        )
    ) {
        cleanup_log_security_event("Attempted to delete directory outside allowed paths: $dir", 'critical');
        return false;
    }

    $files = scandir($dir);
    if ($files === false) {
        error_log("Plugin Cleanup: Failed to scan directory for deletion: $dir");
        return false;
    }

    $success = true;
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = $dir . $file;
        if (is_dir($path)) {
            $success = cleanup_remove_directory($path . '/') && $success;
        } else {
            // Security: Don't delete files larger than limit
            $file_size = filesize($path);
            if ($file_size !== false && $file_size <= CLEANUP_MAX_FILE_SIZE) {
                $success = @unlink($path) && $success;
            } else {
                cleanup_log_security_event("Skipped large file during directory deletion: $path", 'medium');
            }
        }
    }

    return $success && @rmdir($dir);
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================
/**
 * Format file size
 */
function cleanup_format_size($bytes) {
    if ($bytes <= 0) {
        return '0 B';
    }
    $units = array('B', 'KB', 'MB', 'GB');
    $i     = min(3, floor(log($bytes, 1024)));
    $value = round($bytes / (1024 ** $i), ($i ? 2 : 0));
    return $value . ' ' . $units[$i];
}

/**
 * Fallback sanitize function
 */
if (!function_exists('sanitize_file_name')) {
    function sanitize_file_name($filename) {
        return preg_replace('/[^a-zA-Z0-9_.-]/', '', $filename);
    }
}
?>
