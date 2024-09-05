<?php

/**
 *  Mini Engine: https://github.com/openfunltd/mini-engine
 *  License: BSD 3-Clause License
 */
define('MINI_ENGINE_VERSION', '0.1.0');

class MiniEngine
{
    public static function initEnv()
    {
        self::registerAutoLoad();
        error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);
    }

    public static function defaultErrorHandler($error)
    {
        $message = $error->getMessage();
        $trace = $error->getTrace();
        $file = $trace[0]['file'];
        $line = $trace[0]['line'];
        $trace = array_map(function($idx) use ($trace){
            $level = $idx + 1;
            return "#{$level} {$trace[$idx]['file']}:{$trace[$idx]['line']}";
        }, array_keys($trace));
        $trace = array_merge([
            "#0 $file:$line"
        ], $trace);

        if ('MiniEngine_Controller_NotFound' != get_class($error)) {
            error_log("Error: $message in $file:$line\nStack trace:\n" . implode("\n", $trace));
        } else {
            if (getenv('ENV') == 'production') {
                header('HTTP/1.1 404 Not Found');
                echo "<h1>404 Not Found</h1>";
                exit;
            }
        }

        if (getenv('ENV') == 'production') {
            header('HTTP/1.1 500 Internal Server Error');
            exit;
        }
        echo "<p>Error: " . $error->getMessage() . "</p>";
        echo "<ul>";
        echo "<li>" . $error->getFile() . ":" . $error->getLine() . "</li>";
        foreach ($error->getTrace() as $trace) {
            echo "<li>" . $trace['file'] . ":" . $trace['line'] . "</li>";
        }
        echo "</ul>";

        throw new MiniEngine_Controller_NoView();
    }

    public static function registerAutoLoad()
    {
        spl_autoload_register(array('MiniEngine', 'autoload'));
	}

	public static function autoload($class)
    {
		if (class_exists($class, false) or interface_exists($class, false)) {
			return false;
		}

		$class = str_replace('\\', DIRECTORY_SEPARATOR, str_replace('_', DIRECTORY_SEPARATOR, $class)) . '.php';

		$paths = explode(PATH_SEPARATOR, get_include_path());
		foreach ($paths as $path) {
			$path = rtrim($path, '/');
			if (file_exists($path . '/' . $class)) {
				require $class;
				return true;
			}
		}

		return false;
    }

    public static function getRoot()
    {
        if (defined('MINI_ENGINE_ROOT')) {
            return MINI_ENGINE_ROOT;
        }
        return __DIR__;
    }

    public static function dispatch($custom_function = null)
    {
        try {
            if (strpos($_SERVER['REQUEST_URI'], '/static') === 0) {
                $file = self::getRoot() . $_SERVER['REQUEST_URI'];
                if (file_exists($file) and is_file($file)) {
                    header('Content-Type: ' . mime_content_type($file));
                    readfile($file);
                    return;
                }
            }
            $controller_action_params = self::getControllerAndAction($custom_function);
            $controller = $controller_action_params[0];
            $action = $controller_action_params[1];
            $params = $controller_action_params[2] ?? [];

            self::runControllerAction($controller, $action, $params);
        } catch (Exception $e) {
            self::runControllerAction('error', 'error', [$e]);
            return;
        } catch (Error $e) {
            self::runControllerAction('error', 'error', [$e]);
            return;
        }
    }

    protected static function runControllerAction($controller, $action, $params)
    {
        $controller_class = ucfirst($controller) . 'Controller';
        $controller_file = self::getRoot() . '/controllers/' . $controller_class . '.php';
        if (!file_exists($controller_file)) {
            return self::runControllerAction('error', 'error', [new MiniEngine_Controller_NotFound("Controller not found: {$controller}:{$action}")]);
        }

        if (!class_exists($controller_class)) {
            include($controller_file);
        }

        $controller_instance = new $controller_class();
        $action_method = $action . 'Action';
        if (!method_exists($controller_instance, $action_method)) {
            return self::runControllerAction('error', 'error', [new MiniEngine_Controller_NotFound("Action not found: {$controller}:{$action}")]);
        }

        try {
            call_user_func_array([$controller_instance, 'init'], $params);
            call_user_func_array([$controller_instance, $action_method], $params);
            $view_file = self::getRoot() . '/views/' . $controller . '/' . $action . '.php';
            echo $controller_instance->draw($view_file);
        } catch (MiniEngine_Controller_NoView $e) {
            // do nothing
        } catch (Exception $e) {
            self::runControllerAction('error', 'error', [$e]);
        }
    }

    protected static function getControllerAndAction($custom_function)
    {
        if (!is_null($custom_function)) {
            $uri = $_SERVER['REQUEST_URI'];
            if (strpos($uri, '?')) {
                $uri = explode('?', $uri)[0];
            }
            $result = $custom_function($uri);
            if (!is_null($result)) {
                return $result;
            }
        }

        $uri = $_SERVER['REQUEST_URI'];
        $uri = explode('?', $uri)[0];
        $uri = ltrim($uri, '/');
        $uri = explode('/', $uri);
        $controller = strtolower($uri[0] ?? 'index') ?: 'index';
        $action = strtolower($uri[1] ?? 'index') ?: 'index';
        return [$controller, $action];
    }
}

class MiniEngine_Controller_NoView extends Exception
{
}

class MiniEngine_Controller_NotFound extends Exception
{
}

class MiniEngine_Controller_ViewObject
{
    protected $_data = [];

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __get($name)
    {
        return $this->_data[$name] ?? null;
    }

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function partial($file, $data = null)
    {
        if ($data instanceof MiniEngine_Controller_ViewObject) {
            $data = $data->_data;
        } else {
            $data = (array) $data;
        }

        $original_data = $this->_data;
        $this->_data = $data;

        if (!file_exists($file)) {
            if (file_exists(MiniEngine::getRoot() . "/views/{$file}.php")) {
                $file = MiniEngine::getRoot() . "/views/{$file}.php";
            } else {
                throw new Exception("Partial file not found: $file");
            }
        }

        ob_start();
        include($file);
        $content = ob_get_clean();

        $this->_data = $original_data;
        return $content;
    }

    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

class MiniEngine_Controller
{
    protected $view;

    public function __construct()
    {
        $this->view = new MiniEngine_Controller_ViewObject();
    }

    public function init()
    {
    }

    public function draw($view_file)
    {
        if (!file_exists($view_file)) {
            throw new Exception("View file not found: $view_file");
        }
        return $this->view->partial($view_file, $this->view);
    }

    public function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->noview();
    }

    public function cors_json($data)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        return $this->json($data);
    }

    public function noview()
    {
        throw new MiniEngine_Controller_NoView();
    }

    public function redirect($uri, $code = 302)
    {
        header("Location: $uri", true, $code);
        return $this->noview();
    }
}

class MiniEngineCLI
{
    public static function dispatch()
    {
        $cmd = $_SERVER['argv'][1] ?? null;
        switch ($cmd) {
            case 'init':
                self::cmd_init();
                break;
            case 'update':
                self::cmd_update();
                break;
            default:
                self::cmd_help();
                break;
        }
    }

    public static function cmd_update()
    {
        $url = 'https://raw.githubusercontent.com/openfunltd/mini-engine/main/mini-engine.php';
        $current_version = MINI_ENGINE_VERSION;

        $new_mini_engine = file_get_contents($url);
        preg_match('/define\(\'MINI_ENGINE_VERSION\', \'([^\']+)\'\);/', $new_mini_engine, $matches);
        $new_version = $matches[1] ?? null;

        if ($new_mini_engine === false or $new_version === false) {
            error_log("Failed to update Mini Engine.");
            return;
        }

        $current_mini_engine = file_get_contents(__FILE__);
        if ($new_mini_engine === $current_mini_engine) {
            error_log("Mini Engine is already up-to-date.");
            return;
        }

        file_put_contents(__FILE__, $new_mini_engine);
        error_log("Mini Engine updated from $current_version to $new_version.");
    }

    public static function cmd_init()
    {
        // Create directories
        $directories = [
            'controllers',
            'libraries',
            'static',
            'views',
            'views/index',
            'views/common',
        ];
        foreach ($directories  as $dir) {
            if (file_exists($dir)) {
                continue;
            }
            mkdir($dir);
        }
        error_log("created directories: " . implode(', ', $directories));

        // Create init.inc.php
        file_put_contents('init.inc.php', <<<EOF
<?php

define('MINI_ENGINE_LIBRARY', true);
define('MINI_ENGINE_ROOT', __DIR__);
include(__DIR__ . '/mini-engine.php');
if (file_exists(__DIR__ . '/config.inc.php')) {
    include(__DIR__ . '/config.inc.php');
}
set_include_path(
    __DIR__ . '/libraries'
    . PATH_SEPARATOR . __DIR__ . '/models'
);
MiniEngine::initEnv();

EOF
        );
        error_log("created init.inc.php");

        // Create config.sample.inc.php
        file_put_contents('config.sample.inc.php', <<<EOF
<?php

putenv('APP_NAME', 'Mini Engine sample application');

EOF
        );
        error_log("created config.sample.inc.php");

        // Create .gitignore
        file_put_contents('.gitignore', <<<EOF
config.inc.php

EOF
        );
        error_log("created .gitignore");

        // Create index.php
        file_put_contents('index.php', <<<EOF
<?php
include(__DIR__ . '/init.inc.php');

MiniEngine::dispatch(function(\$uri){
    if (\$uri == '/robots.txt') {
        return ['index', 'robots'];
    }
    // default
    return null;
});

EOF
        );

        error_log("created index.php");

        // Create controllers/IndexController.php
        file_put_contents('controllers/IndexController.php', <<<EOF
<?php

class IndexController extends MiniEngine_Controller
{
    public function indexAction()
    {
        \$this->view->app_name = getenv('APP_NAME');
    }

    public function robotsAction()
    {
        header('Content-Type: text/plain');
        echo "#\\n";
        return \$this->noview();
    }
}

EOF
        );
        error_log("created controllers/IndexController.php");

        // Create controllers/ErrorController.php
        file_put_contents('controllers/ErrorController.php', <<<EOF
<?php

class ErrorController extends MiniEngine_Controller
{
    public function errorAction(\$error)
    {
        MiniEngine::defaultErrorHandler(\$error);
    }
}

EOF
        );
        error_log("created controllers/ErrorController.php");

        // Create .htaccess
        file_put_contents('.htaccess', <<<EOF
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [QSA,L]

EOF
        );
        error_log("created .htaccess");

        // Create /views/common/header.php
        file_put_contents('views/common/header.php', <<<EOF
<!DOCTYPE html>
<html>
<head>
<title><?= \$this->escape(\$this->app_name) ?></title>
</head>
<body>

EOF
        );
        error_log("created views/common/header.php");

        // Create /views/common/footer.php
        file_put_contents('views/common/footer.php', <<<EOF
</body>
</html>

EOF
        );
        error_log("created views/common/footer.php");

        // Create /views/index/index.php
        file_put_contents('views/index/index.php', <<<EOF
<?= \$this->partial('common/header') ?>
This is Index page
<?= \$this->partial('common/footer') ?>

EOF
        );
        error_log("created views/index/index.php");

        // Create /libraries/MiniEngineHelper.php
        file_put_contents('libraries/MiniEngineHelper.php', <<<EOF
<?php

class MiniEngineHelper
{
    public static function uniqid(\$len)
    {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', \$len)), 0, \$len);
    }
}

EOF
        );
        error_log("created libraries/MiniEngineHelper.php");

        error_log("Mini Engine project initialized.");
    }

    public static function cmd_help()
    {
        echo "Usage: php mini-engine.php <command>\n";
        echo "Commands:\n";
        echo "  init    Initialize a new Mini Engine project\n";
        echo "  update  Update Mini Engine\n";
    }
}

if (!defined('MINI_ENGINE_LIBRARY')) {
    MiniEngineCLI::dispatch();
}
