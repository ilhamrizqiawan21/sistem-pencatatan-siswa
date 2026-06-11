<?php
// Revert fixer: replace dirname(__FILE__, 2) . '/... back to '../../...'
$basePath = __DIR__;
$modulesPath = $basePath . '/modules';
$logFile = $basePath . '/logs/revert_fix_paths.log';
@mkdir($basePath . '/logs', 0755, true);
$log = [];
function logMsg($m){ global $log; $log[] = '['.date('Y-m-d H:i:s').'] '.$m; echo $m."\n"; }

$patterns = [
    "require_once dirname(__FILE__, 2) . '/config/" => "require_once '../../config/",
    "require_once dirname(__FILE__, 2) . \"/config/" => "require_once \"../../config/",
    "require_once dirname(__FILE__, 2) . '/includes/" => "require_once '../../includes/",
    "require_once dirname(__FILE__, 2) . \"/includes/" => "require_once \"../../includes/",
    "require_once dirname(__FILE__, 2) . '/vendor/" => "require_once '../../vendor/",
    "require_once dirname(__FILE__, 2) . \"/vendor/" => "require_once \"../../vendor/",
    // footer includes
    "require_once dirname(__FILE__, 2) . '/includes/footer.php'" => "require_once '../../includes/footer.php'",
    "require_once dirname(__FILE__, 2) . \"/includes/footer.php\"" => "require_once \"../../includes/footer.php\"",
    "require_once dirname(__FILE__, 2) . '/includes/header.php'" => "require_once '../../includes/header.php'",
    "require_once dirname(__FILE__, 2) . \"/includes/header.php\"" => "require_once \"../../includes/header.php\"",
];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulesPath), RecursiveIteratorIterator::SELF_FIRST);
$phpFiles = [];
foreach($it as $f){ if($f->isFile() && $f->getExtension()==='php') $phpFiles[] = $f->getRealPath(); }
logMsg("Found ".count($phpFiles)." PHP files in modules/");
$filesChanged = 0; $totalChanges=0;
foreach($phpFiles as $file){
    $content = file_get_contents($file);
    $orig = $content;
    foreach($patterns as $search=>$replace){
        $content = str_replace($search, $replace, $content);
    }
    if ($content !== $orig){
        file_put_contents($file, $content);
        $filesChanged++; // rough
        logMsg("Reverted: $file");
    }
}
logMsg("Done. Files changed: $filesChanged");
file_put_contents($logFile, implode("\n", $log)."\n", FILE_APPEND);
echo "Log: $logFile\n";
