<?php
/**
 * MTS Al-Ihsan Router - Clean URL Routing System
 * 
 * Handles routing of clean URLs to appropriate handlers
 */

class Router {
    private $uri;
    private $method;
    private $module;
    private $page;
    private $params;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->module = null;
        $this->page = 'dashboard';
        $this->params = $_GET;
        $this->parseUri();
    }

    /**
     * Parse the URI to extract route information
     */
    private function parseUri() {
        try {
            // Get the request URI and remove base path
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            if (!$uri) {
                $uri = '/';
            }
            
            $base = '/mts-alihsan/';
            
            if (strpos($uri, $base) === 0) {
                $uri = substr($uri, strlen($base));
            }
            
            // Remove trailing slash
            $uri = rtrim($uri, '/');
            
            // Remove query string (just in case)
            if (strpos($uri, '?') !== false) {
                $uri = strtok($uri, '?');
            }
            
            $this->uri = $uri;
            
            // Parse the URI
            $this->parseRoute();
        } catch (Exception $e) {
            error_log("Router parseUri error: " . $e->getMessage());
            $this->page = 'dashboard';
        }
    }

    /**
     * Parse route from URI
     */
    private function parseRoute() {
        // Handle empty or root
        if (empty($this->uri) || $this->uri === '/' || $this->uri === 'index.php') {
            $this->page = 'dashboard';
            $this->module = null;
            return;
        }

        // Handle login/logout
        if ($this->uri === 'login' || $this->uri === 'login.php') {
            $this->page = 'login';
            $this->module = null;
            return;
        }

        if ($this->uri === 'logout' || $this->uri === 'logout.php') {
            $this->page = 'logout';
            $this->module = null;
            return;
        }

        // Handle debug pages
        if (strpos($this->uri, 'debug') === 0) {
            // Allow debug pages to pass through
            $this->page = 'debug';
            return;
        }

        // Handle module routes: /modules/absensi or /absensi
        if (preg_match('/^modules\/([a-z0-9_-]+)(?:\/(.*))?$/i', $this->uri, $matches)) {
            $moduleName = $matches[1];
            if ($this->isValidModule($moduleName)) {
                $this->module = $moduleName;
                $this->page = $matches[2] ?? 'index';
            }
            return;
        }

        // Direct module access without /modules/ prefix: /absensi
        if (preg_match('/^([a-z0-9_-]+)(?:\/(.*))?$/i', $this->uri, $matches)) {
            $moduleName = strtolower($matches[1]);
            
            // Check if it's a valid module
            if ($this->isValidModule($moduleName)) {
                $this->module = $moduleName;
                $this->page = $matches[2] ?? 'index';
                return;
            }
        }

        // Default to dashboard if no route matches
        $this->page = 'dashboard';
        $this->module = null;
    }

    /**
     * Check if a module exists
     */
    private function isValidModule($name) {
        if (empty($name) || !is_string($name)) {
            return false;
        }
        
        $modulePath = __DIR__ . '/../modules/' . basename($name);
        return is_dir($modulePath);
    }

    /**
     * Get the current page
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Get the current module
     */
    public function getModule() {
        return $this->module;
    }

    /**
     * Get request parameters
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Get a specific parameter
     */
    public function getParam($key, $default = null) {
        return $this->params[$key] ?? $default;
    }

    /**
     * Get the current URI
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * Load the appropriate file based on route
     */
    public function load() {
        try {
            // Check if page is 'logout'
            if ($this->page === 'logout') {
                if (file_exists(__DIR__ . '/../logout.php')) {
                    require_once __DIR__ . '/../logout.php';
                }
                exit;
            }

            // Check if page is 'login'
            if ($this->page === 'login') {
                if (file_exists(__DIR__ . '/../login.php')) {
                    require_once __DIR__ . '/../login.php';
                }
                exit;
            }

            // If module exists, load module
            if ($this->module) {
                $modulePath = __DIR__ . '/../modules/' . $this->module . '/index.php';
                if (file_exists($modulePath)) {
                    require_once $modulePath;
                    exit;
                }
            }

            // Default to index.php
            if (file_exists(__DIR__ . '/../index.php')) {
                require_once __DIR__ . '/../index.php';
            }
        } catch (Exception $e) {
            error_log("Router load error: " . $e->getMessage());
            die("Error loading page: " . htmlspecialchars($e->getMessage()));
        }
    }
}

// Instantiate router
$router = new Router();
?>
