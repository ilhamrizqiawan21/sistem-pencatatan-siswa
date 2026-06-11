<?php
/**
 * Fix Module Paths Script
 * 
 * Run this ONCE to fix all relative paths in module files
 * Usage: php fix_module_paths.php
 * 
 * Or curl it: curl https://ilham.didzacorp.com/mts-alihsan/fix_module_paths.php
 */

// Configuration
$basePath = __DIR__;
$modulesPath = $basePath . '/modules';
$logFile = $basePath . '/logs/fix_paths.log';

// Create log directory if needed
@mkdir($basePath . '/logs', 0755, true);

$log = [];
$filesFixed = 0;
$totalChanges = 0;

// Log function
function logMsg($msg) {
    global $log;
    $log[] = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    echo $msg . "\n";
}

logMsg("=== Module Path Fixer ===");
logMsg("Base Path: $basePath");
logMsg("Modules Path: $modulesPath");
logMsg("");

// Patterns to fix
$patterns = [
    "require_once '../../config/" => "require_once dirname(__FILE__, 2) . '/config/",
    "require_once '../../includes/" => "require_once dirname(__FILE__, 2) . '/includes/",
    "require_once '../../vendor/" => "require_once dirname(__FILE__, 2) . '/vendor/",
    'require_once "../../config/' => 'require_once dirname(__FILE__, 2) . "/config/',
    'require_once "../../includes/' => 'require_once dirname(__FILE__, 2) . "/includes/',
    'require_once "../../vendor/' => 'require_once dirname(__FILE__, 2) . "/vendor/',
];

// Find all PHP files in modules directory
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modulesPath),
    RecursiveIteratorIterator::SELF_FIRST
);

$phpFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getRealPath();
    }
}

logMsg("Found " . count($phpFiles) . " PHP files\n");

// Process each file
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    $changes = 0;
    
    // Apply all pattern replacements
    foreach ($patterns as $search => $replace) {
        $oldContent = $content;
        $content = str_replace($search, $replace, $content);
        
        if ($oldContent !== $content) {
            $count = substr_count($oldContent, $search) - substr_count($content, $search);
            $changes += $count;
            logMsg("  - Fixed $count occurrences of '$search'");
        }
    }
    
    // Write file if changes were made
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            $filesFixed++;
            $totalChanges += $changes;
            $relPath = str_replace($basePath . '/', '', $file);
            logMsg("✓ Fixed: $relPath ($changes changes)");
        } else {
            logMsg("✗ ERROR: Could not write to $file");
        }
    }
}

logMsg("");
logMsg("=== Summary ===");
logMsg("Files fixed: $filesFixed");
logMsg("Total changes: $totalChanges");
logMsg("Completed at: " . date('Y-m-d H:i:s'));

// Save log
$logContent = implode("\n", $log);
file_put_contents($logFile, $logContent . "\n", FILE_APPEND);

echo "\n✓ Log saved to: logs/fix_paths.log\n";
?>
