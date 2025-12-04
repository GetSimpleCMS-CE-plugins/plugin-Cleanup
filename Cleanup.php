<?php
/*
Plugin Name: Plugin Cleanup
Description: Find and safely delete inactive plugins with all their files and detected data.
Version: 1.0
Author: Fahad4x4
Author URI: https://getsimple-ce.ovh/
License: MIT
*/

$thisfile = basename(__FILE__, ".php");

# تعريف الترجمات مباشرة
$translations = array(
    'en' => array(
        'PLUGIN_TITLE' => 'Plugin Cleanup',
        'PLUGIN_DESC' => 'Find and safely delete inactive plugins with all their files',
        'MENU_TITLE' => 'Plugin Cleanup',
        'PAGE_TITLE' => 'Plugin Cleanup Tool',
        'TOTAL_PLUGINS' => 'Total Plugins',
        'ACTIVE' => 'Active',
        'INACTIVE' => 'Inactive',
        'ACTIVE_PLUGINS' => 'Active Plugins',
        'NO_ACTIVE' => 'No active plugins found',
        'INACTIVE_PLUGINS' => 'Inactive Plugins',
        'NO_INACTIVE' => 'No inactive plugins found - Your system is clean!',
        'PLUGIN_NAME' => 'Plugin Name',
        'VERSION' => 'Version',
        'STATUS' => 'Status',
        'HAS_FOLDER' => 'Has folder',
        'FILES' => 'Files',
        'FOLDER_FILES' => 'Folder Contents',
        'DATA' => 'Data & Settings',
        'SIZE' => 'Size',
        'TOTAL_SIZE' => 'Total Size',
        'TOTAL_FILES' => 'Total Files',
        'DELETE_BUTTON' => 'Delete Selected Plugins',
        'NO_SELECTED' => 'No plugins selected for deletion',
        'WARNING' => 'Warning',
        'WARNING_DESC' => 'This action cannot be undone. Make sure you select only the correct plugins.',
        'SUCCESS' => 'Success',
        'PLUGINS_DELETED' => 'plugins deleted successfully',
        'ERROR' => 'Errors',
        'DETECTED_DATA' => 'Detected Data Storage Methods',
        'AUTO_DETECTED' => 'Auto-detected from plugin code',
        'SELECT_DATA' => 'Select data to delete:',
        'SELECT_ALL_DATA' => 'Select All Detected Files',
        'NO_DATA_FOUND' => 'No data storage detected',
    ),
    'ar' => array(
        'PLUGIN_TITLE' => 'تنظيف الإضافات',
        'PLUGIN_DESC' => 'البحث عن الإضافات غير المفعلة وحذفها بأمان مع جميع ملفاتها',
        'MENU_TITLE' => 'تنظيف الإضافات',
        'PAGE_TITLE' => 'أداة تنظيف الإضافات',
        'TOTAL_PLUGINS' => 'إجمالي الإضافات',
        'ACTIVE' => 'النشطة',
        'INACTIVE' => 'غير النشطة',
        'ACTIVE_PLUGINS' => 'الإضافات النشطة',
        'NO_ACTIVE' => 'لا توجد إضافات نشطة',
        'INACTIVE_PLUGINS' => 'الإضافات غير النشطة',
        'NO_INACTIVE' => 'لا توجد إضافات غير نشطة - نظامك نظيف!',
        'PLUGIN_NAME' => 'اسم الإضافة',
        'VERSION' => 'الإصدار',
        'STATUS' => 'الحالة',
        'HAS_FOLDER' => 'يحتوي على مجلد',
        'FILES' => 'الملفات',
        'FOLDER_FILES' => 'محتويات المجلد',
        'DATA' => 'البيانات والإعدادات',
        'SIZE' => 'الحجم',
        'TOTAL_SIZE' => 'الحجم الكلي',
        'TOTAL_FILES' => 'إجمالي الملفات',
        'DELETE_BUTTON' => 'حذف الإضافات المختارة',
        'NO_SELECTED' => 'لم تختر أي إضافة للحذف',
        'WARNING' => 'تحذير',
        'WARNING_DESC' => 'لا يمكن التراجع عن هذه العملية. تأكد من اختيار الإضافات الصحيحة فقط.',
        'SUCCESS' => 'نجح',
        'PLUGINS_DELETED' => 'إضافات تم حذفها بنجاح',
        'ERROR' => 'أخطاء',
        'DETECTED_DATA' => 'طرق تخزين البيانات المكتشفة',
        'AUTO_DETECTED' => 'مكتشفة تلقائياً من كود الإضافة',
        'SELECT_DATA' => 'اختر البيانات للحذف:',
        'SELECT_ALL_DATA' => 'تحديد جميع الملفات المكتشفة',
        'NO_DATA_FOUND' => 'لم يتم اكتشاف تخزين بيانات',
    )
);

# اختيار اللغة الصحيحة
$lang = substr($LANG, 0, 2);
$lang = isset($translations[$lang]) ? $lang : 'en';
$t = $translations[$lang];

register_plugin(
    $thisfile,
    $t['PLUGIN_TITLE'],
    '1.0',
    'Fahad4x4',
    'https://getsimple-ce.ovh/',
    $t['PLUGIN_DESC'],
    'pages',
    'cleanup_plugins_show'
);

add_action('pages-sidebar', 'createSideMenu', array($thisfile, $t['MENU_TITLE']));

function cleanup_plugins_show() {
    global $LANG, $translations;
    
    $lang = substr($LANG, 0, 2);
    $lang = isset($translations[$lang]) ? $lang : 'en';
    $t = $translations[$lang];
    
    // معالجة الحذف أولاً
    if (isset($_POST['cleanup_action']) && isset($_POST['plugins_to_delete'])) {
        cleanup_process_deletion($_POST['plugins_to_delete'], $_POST, $t);
        return;
    }
    
    $all_plugins = cleanup_get_all_plugins();
    $active_plugins = cleanup_get_active_plugins();
    $inactive_plugins = array_diff_key($all_plugins, $active_plugins);
    
    ?>
    <div style="max-width: 1200px;">
        <h2><?php echo $t['PAGE_TITLE']; ?></h2>
        
        <!-- Statistics -->
        <div style="background: #f0f0f0; padding: 12px; margin: 15px 0; border-radius: 4px; border-right: 4px solid #2196F3;">
            <small style="font-size: 14px;">
                📊 <?php echo $t['TOTAL_PLUGINS']; ?>: <strong><?php echo count($all_plugins); ?></strong> | 
                ✓ <?php echo $t['ACTIVE']; ?>: <strong style="color: green;"><?php echo count($active_plugins); ?></strong> | 
                ✗ <?php echo $t['INACTIVE']; ?>: <strong style="color: red;"><?php echo count($inactive_plugins); ?></strong>
            </small>
        </div>
        
        <!-- Active Plugins -->
        <h3 style="color: green; border-bottom: 2px solid green; padding-bottom: 10px;">
            ✓ <?php echo $t['ACTIVE_PLUGINS']; ?> (<?php echo count($active_plugins); ?>)
        </h3>
        
        <?php if (empty($active_plugins)): ?>
            <p style="color: #999; padding: 10px; background: #f5f5f5; border-radius: 4px;"><?php echo $t['NO_ACTIVE']; ?></p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background: #e8f5e9;">
                        <th style="padding: 12px; text-align: left; border: 1px solid #81c784;"><?php echo $t['PLUGIN_NAME']; ?></th>
                        <th style="padding: 12px; text-align: center; border: 1px solid #81c784; width: 120px;"><?php echo $t['VERSION']; ?></th>
                        <th style="padding: 12px; text-align: center; border: 1px solid #81c784; width: 120px;"><?php echo $t['STATUS']; ?></th>
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
                                ✓ <?php echo $t['ACTIVE']; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <!-- Inactive Plugins -->
        <h3 style="color: red; border-bottom: 2px solid red; padding-bottom: 10px; margin-top: 30px;">
            ✗ <?php echo $t['INACTIVE_PLUGINS']; ?> (<?php echo count($inactive_plugins); ?>)
        </h3>
        
        <?php if (empty($inactive_plugins)): ?>
            <p style="color: #666; padding: 10px; background: #f5f5f5; border-radius: 4px;"><?php echo $t['NO_INACTIVE']; ?></p>
        <?php else: ?>
            <form method="POST" onsubmit="return confirm('<?php echo addslashes($t['WARNING_DESC']); ?>');">
                <?php foreach ($inactive_plugins as $id => $plugin): ?>
                    <div style="background: #fff; border: 1px solid #e0e0e0; padding: 15px; margin: 15px 0; border-radius: 4px;">
                        <!-- Header -->
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="flex: 1;">
                                <input type="checkbox" name="plugins_to_delete[]" value="<?php echo htmlspecialchars($id); ?>" style="margin-right: 10px;">
                                <strong style="font-size: 16px;"><?php echo htmlspecialchars($plugin['info']['name'] ?? $id); ?></strong>
                                <span style="color: #999; margin-left: 10px;">v<?php echo htmlspecialchars($plugin['info']['version'] ?? 'N/A'); ?></span>
                                <span style="color: red; margin-left: 10px;">✗ <?php echo $t['INACTIVE']; ?></span>
                            </div>
                        </div>
                        
                        <!-- Files Details -->
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f0f0f0;">
                            <?php 
                            $plugin_info = cleanup_get_plugin_details($id);
                            $detected_data = cleanup_analyze_plugin_code($id);
                            ?>
                            
                            <!-- Main File -->
                            <p style="margin: 8px 0; color: #333;">
                                <strong>📄 <?php echo $t['FILES']; ?>:</strong>
                            </p>
                            <ul style="margin: 8px 0 8px 20px; list-style: disc; color: #666;">
                                <li><?php echo htmlspecialchars($plugin_info['main_file']); ?> 
                                    <span style="color: #999; font-size: 12px;">(<?php echo $plugin_info['main_file_size']; ?>)</span>
                                </li>
                            </ul>
                            
                            <!-- Folder Contents -->
                            <?php if (!empty($plugin_info['folder_files'])): ?>
                                <p style="margin: 8px 0; color: #333;">
                                    <strong>📁 <?php echo $t['FOLDER_FILES']; ?>:</strong>
                                </p>
                                <ul style="margin: 8px 0 8px 20px; list-style: disc; color: #666; max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($plugin_info['folder_files'] as $file_path): ?>
                                        <li style="font-size: 12px;"><?php echo $file_path; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            
                            <!-- Detected Data Storage -->
                            <?php if (!empty($detected_data['files'])): ?>
                                <p style="margin: 8px 0; color: #333;">
                                    <strong>💾 <?php echo $t['DETECTED_DATA']; ?>:</strong>
                                    <span style="color: #4CAF50; font-size: 12px; margin-left: 10px;">✓ <?php echo $t['AUTO_DETECTED']; ?></span>
                                </p>
                                <div style="background: #f0f8ff; padding: 10px; border: 1px solid #81d4fa; border-radius: 4px; margin: 8px 0 8px 20px;">
                                    <?php if (!empty($detected_data['methods'])): ?>
                                        <p style="margin: 5px 0; font-size: 13px; color: #0277bd;">
                                            <strong>🔍 Detected methods:</strong> 
                                            <?php echo implode(', ', $detected_data['methods']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <label style="display: block; margin: 8px 0;">
                                        <input type="checkbox" class="select-data-for-<?php echo $id; ?>" value="all" onchange="toggleAllData('<?php echo $id; ?>')">
                                        <strong><?php echo $t['SELECT_ALL_DATA']; ?></strong>
                                    </label>
                                    
                                    <?php foreach ($detected_data['files'] as $file_info): ?>
                                        <label style="display: block; margin: 5px 0;">
                                            <input type="checkbox" name="data_to_delete[<?php echo htmlspecialchars($id); ?>][]" 
                                                   value="<?php echo htmlspecialchars($file_info['path']); ?>" 
                                                   class="select-data-for-<?php echo $id; ?>">
                                            <?php echo htmlspecialchars(basename($file_info['path'])); ?>
                                            <span style="color: #999; font-size: 11px;">(<?php echo $file_info['size']; ?> - <?php echo $file_info['type']; ?>)</span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="margin: 8px 0; color: #999;">
                                    💾 <?php echo $t['NO_DATA_FOUND']; ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Summary -->
                            <p style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f0f0f0; color: #999; font-size: 13px;">
                                📊 <?php echo $t['TOTAL_SIZE']; ?>: <strong><?php echo $plugin_info['total_size']; ?></strong> | 
                                <?php echo $t['TOTAL_FILES']; ?>: <strong><?php echo $plugin_info['total_files']; ?></strong>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="padding: 15px; background: #fff3e0; border: 1px solid #ffe0b2; border-radius: 4px; margin: 15px 0;">
                    <p style="margin: 0 0 10px 0; color: #e65100;">
                        ⚠️ <strong><?php echo $t['WARNING']; ?>:</strong> <?php echo $t['WARNING_DESC']; ?>
                    </p>
                    <input type="hidden" name="cleanup_action" value="1">
                    <button type="submit" class="submit" style="background: #f44336; color: white; padding: 10px 25px; border: none; cursor: pointer; border-radius: 4px; font-size: 14px;">
                        🗑️ <?php echo $t['DELETE_BUTTON']; ?>
                    </button>
                </div>
            </form>
            
            <script>
            function toggleAllData(pluginId) {
                var checkboxes = document.querySelectorAll('.select-data-for-' + pluginId);
                var allChecked = checkboxes[0].checked;
                for (var i = 1; i < checkboxes.length; i++) {
                    checkboxes[i].checked = allChecked;
                }
            }
            </script>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * تحليل كود الإضافة واكتشاف طرق تخزين البيانات
 */
function cleanup_analyze_plugin_code($plugin_id) {
    $result = array(
        'files' => array(),
        'methods' => array()
    );
    
    $plugin_file = GSPLUGINPATH . $plugin_id . '.php';
    
    if (!file_exists($plugin_file)) {
        return $result;
    }
    
    $code = file_get_contents($plugin_file);
    
    // أنماط البحث عن طرق تخزين البيانات الشائعة
    $patterns = array(
        // file_put_contents / file_get_contents
        array(
            'pattern' => '/file_(put|get)_contents\s*\(\s*[\'"]?([^\',\)]+)[\'"]?\s*(,|$)/i',
            'method' => 'file_put_contents'
        ),
        // fopen / fwrite
        array(
            'pattern' => '/fopen\s*\(\s*[\'"]([^\'"]+)[\'"]/',
            'method' => 'fopen/fwrite'
        ),
        // json_encode/decode
        array(
            'pattern' => '/json_(encode|decode)/i',
            'method' => 'JSON'
        ),
        // simplexml / XML
        array(
            'pattern' => '/(simplexml_load_file|new\s+SimpleXMLElement|->asXML\()|XML/i',
            'method' => 'XML'
        ),
        // Database access
        array(
            'pattern' => '/(sqlite|PDO|mysqli|mysql_|\$wpdb|db|database)/i',
            'method' => 'Database'
        ),
    );
    
    $found_methods = array();
    
    foreach ($patterns as $pattern_info) {
        if (preg_match($pattern_info['pattern'], $code)) {
            $found_methods[] = $pattern_info['method'];
        }
    }
    
    $result['methods'] = array_unique($found_methods);
    
    // البحث عن الملفات الفعلية
    $detected_files = cleanup_find_data_files_by_code($plugin_id, $code);
    $result['files'] = $detected_files;
    
    return $result;
}

/**
 * البحث عن ملفات البيانات بناءً على كود الإضافة
 */
function cleanup_find_data_files_by_code($plugin_id, $code) {
    $files = array();
    $data_dir = GSDATAOTHERPATH;

    // Extract direct filename patterns from code
    $file_patterns = array();
    
    // Direct paths in strings ending with common data extensions
    if (preg_match_all('/[\'"]([^\'"]*\.(txt|json|xml|db|dat|csv|yaml|yml|log))[^\'"]*[\'"]/i', $code, $matches)) {
        $file_patterns = array_merge($file_patterns, $matches[1]);
    }
    
    // Variables with 'file', 'path', 'dir'
    if (preg_match_all('/\$(\w*file\w*|\w*path\w*|\w*dir\w*)\s*=\s*[\'"]([^\'"]+)[\'"]/', $code, $matches)) {
        $file_patterns = array_merge($file_patterns, $matches[2]);
    }

    // GSDATAOTHERPATH concatenations
    if (preg_match_all('/GSDATAOTHERPATH\s*[.\+]\s*[\'"]?([^\'"\s\);]+)/i', $code, $matches)) {
        $file_patterns = array_merge($file_patterns, $matches[1]);
    }

    // Fallback: scan data dir for plugin-related files
    if (!is_dir($data_dir)) return $files;
    $files_in_data = @scandir($data_dir);
    if (!$files_in_data) return $files;

    $plugin_lower = strtolower($plugin_id);
    foreach ($files_in_data as $file) {
        if ($file === '.' || $file === '..') continue;

        $full_path = $data_dir . $file;
        if (!is_file($full_path)) continue;

        $f_lower = strtolower($file);
        $include = (strpos($f_lower, $plugin_lower) !== false);

        if (!$include) {
            foreach ($file_patterns as $pat) {
                $p_lower = strtolower(trim($pat, '/\\'));
                if (strpos($f_lower, $p_lower) !== false) {
                    $include = true;
                    break;
                }
            }
        }

        // Common data extensions + plugin-prefixed files
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!$include && in_array($ext, ['txt','json','xml','db','dat','csv','log','yaml','yml'])) {
            if (preg_match('/^[a-z0-9_-]+_\w+\.(txt|json|xml|db|dat|csv|log|yaml|yml)$/i', $file)) {
                $include = true;
            }
        }

        if ($include) {
            $files[] = array(
                'path' => $full_path,
                'name' => $file,
                'size' => cleanup_format_size(filesize($full_path)),
                'type' => $ext ?: 'unknown'
            );
        }
    }

    return $files;
}

function cleanup_get_plugin_details($plugin_id) {
    $plugins_dir = GSPLUGINPATH;
    $data_dir = GSDATAOTHERPATH;
    
    $details = array(
        'main_file' => $plugin_id . '.php',
        'main_file_size' => '0 KB',
        'folder_files' => array(),
        'data_files' => array(),
        'total_size' => '0 KB',
        'total_files' => 0
    );
    
    $main_file_path = $plugins_dir . $plugin_id . '.php';
    $total_size = 0;
    $total_files = 0;
    
    if (file_exists($main_file_path)) {
        $file_size = filesize($main_file_path);
        $details['main_file_size'] = cleanup_format_size($file_size);
        $total_size += $file_size;
        $total_files += 1;
    }
    
    $plugin_folder = $plugins_dir . $plugin_id . '/';
    if (is_dir($plugin_folder)) {
        $folder_files = cleanup_get_directory_files($plugin_folder, $plugin_id);
        $details['folder_files'] = $folder_files['files'];
        $total_size += $folder_files['size'];
        $total_files += $folder_files['count'];
    }
    
    $data_plugin_dir = $data_dir . $plugin_id . '/';
    if (is_dir($data_plugin_dir)) {
        $data_files = cleanup_get_directory_files($data_plugin_dir, $plugin_id);
        $details['data_files'] = $data_files['files'];
        $total_size += $data_files['size'];
        $total_files += $data_files['count'];
    }
    
    $details['total_size'] = cleanup_format_size($total_size);
    $details['total_files'] = $total_files;
    
    return $details;
}

function cleanup_get_directory_files($dir, $plugin_id, $prefix = '') {
    $result = array(
        'files' => array(),
        'size' => 0,
        'count' => 0
    );
    
    if (!is_dir($dir)) return $result;
    
    $files = @scandir($dir);
    if (!$files) return $result;
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . $file;
        $rel = $prefix ? $prefix . '/' . $file : $file;
        
        if (is_dir($path)) {
            $sub = cleanup_get_directory_files($path . '/', $plugin_id, $rel);
            $result['files'] = array_merge($result['files'], $sub['files']);
            $result['size'] += $sub['size'];
            $result['count'] += $sub['count'];
        } else {
            $sz = filesize($path);
            $result['files'][] = htmlspecialchars($rel) . ' (' . cleanup_format_size($sz) . ')';
            $result['size'] += $sz;
            $result['count']++;
        }
    }
    
    return $result;
}

function cleanup_format_size($bytes) {
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = min(3, floor(log($bytes, 1024)));
    return round($bytes / (1024 ** $i), ($i ? 2 : 0)) . ' ' . $units[$i];
}

function cleanup_get_all_plugins() {
    $plugins = array();
    $plugins_dir = GSPLUGINPATH;
    
    if (!is_dir($plugins_dir)) return $plugins;
    
    $files = @scandir($plugins_dir);
    if (!$files) return $plugins;
    
    foreach ($files as $file) {
        if (in_array($file, ['.', '..', '.htaccess'])) continue;
        
        if (substr($file, -4) === '.php') {
            $plugin_id = substr($file, 0, -4);
            $plugin_path = $plugins_dir . $file;
            
            $info = cleanup_parse_plugin_header($plugin_path);
            
            $plugins[$plugin_id] = array(
                'id' => $plugin_id,
                'file' => $file,
                'path' => $plugin_path,
                'folder' => $plugins_dir . $plugin_id . '/',
                'info' => $info,
                'has_folder' => is_dir($plugins_dir . $plugin_id . '/')
            );
        }
    }
    
    return $plugins;
}

function cleanup_parse_plugin_header($plugin_path) {
    $info = array(
        'name' => '',
        'version' => '',
        'description' => '',
        'author' => ''
    );
    
    if (!file_exists($plugin_path)) {
        $info['name'] = basename($plugin_path, '.php');
        return $info;
    }

    $file_data = file_get_contents($plugin_path, false, null, 0, 8192);

    preg_match('/Plugin\s+Name:\s*(.+?)(\r\n|\n|$)/i', $file_data, $match) && $info['name'] = trim($match[1]);
    preg_match('/Version:\s*(.+?)(\r\n|\n|$)/i', $file_data, $match) && $info['version'] = trim($match[1]);
    preg_match('/Description:\s*(.+?)(\r\n|\n|$)/i', $file_data, $match) && $info['description'] = trim($match[1]);
    preg_match('/Author:\s*(.+?)(\r\n|\n|$)/i', $file_data, $match) && $info['author'] = trim($match[1]);

    if (empty($info['name'])) {
        $info['name'] = basename($plugin_path, '.php');
    }

    return $info;
}

function cleanup_get_active_plugins() {
    $active = array();
    $plugins_file = GSDATAOTHERPATH . 'plugins.xml';

    if (!file_exists($plugins_file)) return $active;

    try {
        $xml = simplexml_load_file($plugins_file);
        if ($xml && isset($xml->item)) {
            foreach ($xml->item as $item) {
                $plugin_file = (string)$item->plugin;
                $enabled = ((string)$item->enabled === 'true');
                
                if ($enabled && !empty($plugin_file)) {
                    $plugin_id = substr($plugin_file, 0, -4);
                    $plugin_path = GSPLUGINPATH . $plugin_file;
                    $info = cleanup_parse_plugin_header($plugin_path);
                    $active[$plugin_id] = array('id' => $plugin_id, 'file' => $plugin_file, 'info' => $info);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error reading plugins.xml in Plugin Cleanup: " . $e->getMessage());
    }

    return $active;
}

function cleanup_process_deletion($plugins_to_delete, $post_data, $t) {
    if (empty($plugins_to_delete)) {
        echo '<div style="background: #ffcdd2; padding: 12px; margin: 15px 0; border-radius: 4px; border-right: 4px solid #c62828; color: #c62828;">';
        echo '⚠️ ' . $t['NO_SELECTED'];
        echo '</div>';
        return;
    }

    $deleted = 0;
    $errors = 0;
    $error_list = array();
    $deleted_list = array();
    $deletion_info = array();

    foreach ($plugins_to_delete as $plugin_id) {
        $plugin_id = sanitize_file_name($plugin_id);
        $current_deleted = false;

        // Delete main plugin file
        $plugin_file = GSPLUGINPATH . $plugin_id . '.php';
        if (file_exists($plugin_file)) {
            if (@unlink($plugin_file)) {
                $deleted++;
                $current_deleted = true;
                $deleted_list[] = $plugin_id;
                $deletion_info[] = "✓ Deleted main file: $plugin_id.php";
            } else {
                $errors++;
                $error_list[] = "Failed to delete file: <strong>$plugin_id.php</strong>";
                $deletion_info[] = "✗ Failed to delete: $plugin_id.php";
            }
        }

        // Delete plugin folder
        $plugin_folder = GSPLUGINPATH . $plugin_id . '/';
        if (is_dir($plugin_folder)) {
            if (cleanup_remove_directory($plugin_folder)) {
                if (!$current_deleted) $deleted++;
                $deletion_info[] = "✓ Deleted folder: $plugin_id/";
            } else {
                $errors++;
                $error_list[] = "Failed to delete folder: <strong>$plugin_id/</strong>";
                $deletion_info[] = "✗ Failed to delete folder: $plugin_id/";
            }
        }

        // Delete data folder (if any)
        $data_dir = GSDATAOTHERPATH . $plugin_id . '/';
        if (is_dir($data_dir)) {
            if (cleanup_remove_directory($data_dir)) {
                $deletion_info[] = "✓ Deleted data folder: /data/other/$plugin_id/";
            } else {
                $errors++;
                $error_list[] = "Failed to delete data folder: <strong>$plugin_id/</strong>";
                $deletion_info[] = "✗ Failed to delete data: /data/other/$plugin_id/";
            }
        }

        // Delete user-selected extra data files
        if (!empty($post_data['data_to_delete'][$plugin_id])) {
            foreach ($post_data['data_to_delete'][$plugin_id] as $data_file) {
                $safe_path = realpath($data_file);
                if ($safe_path && strpos($safe_path, GSDATAOTHERPATH) === 0 && is_file($safe_path)) {
                    if (@unlink($safe_path)) {
                        $deletion_info[] = "✓ Deleted data file: " . basename($safe_path);
                    } else {
                        $errors++;
                        $deletion_info[] = "✗ Failed to delete: " . basename($safe_path);
                    }
                }
            }
        }
    }

    // Output results
    echo '<div style="max-width: 1200px;">';
    echo '<h2 style="color: #333; margin-top: 0;">Deletion Report</h2>';

    if ($deletion_info) {
        echo '<div style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 4px; border: 1px solid #ddd;">';
        echo '<h3 style="margin-top: 0; color: #333;">📋 Details:</h3>';
        echo '<ul style="list-style: none; padding: 0; margin: 0;">';
        foreach ($deletion_info as $info) {
            $color = strpos($info, '✓') === 0 ? '#2e7d32' : '#c62828';
            echo '<li style="padding: 8px 0; color: ' . $color . '; border-bottom: 1px solid #e0e0e0;">' . htmlspecialchars($info) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    if ($deleted > 0) {
        echo '<div style="background: #c8e6c9; padding: 12px; margin: 15px 0; border-radius: 4px; border-right: 4px solid #2e7d32; color: #2e7d32;">';
        echo '<strong>✓ ' . $t['SUCCESS'] . ':</strong> ' . $deleted . ' ' . $t['PLUGINS_DELETED'] . '<br>';
        foreach ($deleted_list as $name) {
            echo '• ' . htmlspecialchars($name) . '<br>';
        }
        echo '</div>';
    }

    if ($errors > 0) {
        echo '<div style="background: #ffcdd2; padding: 12px; margin: 15px 0; border-radius: 4px; border-right: 4px solid #c62828; color: #c62828;">';
        echo '<strong>✗ ' . $t['ERROR'] . ' (' . $errors . '):</strong><br>';
        foreach ($error_list as $error) {
            echo '• ' . $error . '<br>';
        }
        echo '</div>';
    }

    echo '<div style="margin-top: 20px;">';
    echo '<button onclick="location.href=\'?id=' . htmlspecialchars($GLOBALS['thisfile']) . '\'" class="submit" style="background: #2196F3; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; font-size: 14px;">';
    echo '🔄 ' . ($lang === 'ar' ? 'تحديث الصفحة' : 'Refresh Page');
    echo '</button>';
    echo '</div>';
    echo '</div>';
}

function cleanup_remove_directory($dir) {
    if (!is_dir($dir)) return false;
    $files = @scandir($dir);
    if (!$files) return false;

    $success = true;
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . $file;
        if (is_dir($path)) {
            $success = cleanup_remove_directory($path . '/') && $success;
        } else {
            $success = @unlink($path) && $success;
        }
    }
    return $success && @rmdir($dir);
}

// Helper (fallback if not defined)
if (!function_exists('sanitize_file_name')) {
    function sanitize_file_name($filename) {
        return preg_replace('/[^\w\.\-]/', '', $filename);
    }
}
?>
