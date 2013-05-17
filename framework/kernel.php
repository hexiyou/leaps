<?php
/**
 * 核心入口
 *
 * @author Tongle Xu <xutongle@gmail.com> 2013-5-15
 * @copyright Copyright (c) 2003-2103 tintsoft.com
 * @license http://www.tintsoft.com
 * @version $Id: kernel.php 555 2013-05-17 06:17:01Z 85825770@qq.com $
 */
error_reporting(E_ALL);
define ( 'LEAPS_VERSION', '1.2.0' );
define ( 'LEAPS_RELEASE', '20121210' );
define ( 'FW_PATH', dirname ( __FILE__ ) . DIRECTORY_SEPARATOR );
! defined ( 'IS_DEBUG' ) && define ( 'IS_DEBUG', false );
class Core {
	public static $_imports = array ();
	private static $_frontController = null;

	/**
	 * 创建应用程序
	 *
	 * @param string $type
	 */
	public static function application($type = 'Web',$config = array()) {
		if (self::$_frontController === null) {
			$_className = $type . '_Application';
			self::$_frontController = new $_className ($config);
		}
		return self::$_frontController;
	}

	/**
	 * 系统初始化
	 */
	public static function init() {
		if (version_compare ( PHP_VERSION, '5.2.0', '<' )) die ( 'require PHP > 5.2.0 !' );
		define ( 'IS_CGI', substr ( PHP_SAPI, 0, 3 ) == 'cgi' ? true : false );
		define ( 'IS_WIN', strstr ( PHP_OS, 'WIN' ) ? true : false );
		define ( 'IS_CLI', PHP_SAPI == 'cli' ? true : false );
		spl_autoload_register ( array ('Core','autoload' ) );
		if (! defined ( 'CORE_FUNCTION' ) && ! @include (FW_PATH . 'func.php')) exit ( 'func.php is missing' );
	}

	/**
	 * 载入文件
	 *
	 * @param string $name 文件名或带路径的文件名
	 * @param string $folder 文件夹默认为空
	 * @throws Exception
	 * @return boolean
	 */
	public static function import($filePath, $folder = '') {
		if (! $filePath) return;
		if (isset ( self::$_imports [$filePath] )) return self::$_imports [$filePath];
		if (($pos = strrpos ( $filePath, '.' )) !== false)
			$fileName = substr ( $filePath, $pos + 1 );
		elseif (($pos = strrpos ( $filePath, ':' )) !== false)
			$fileName = substr ( $filePath, $pos + 1 );
		else
			$fileName = $filePath;
		if (false !== ($pos = strpos ( $filePath, ':' ))) {
			$namespace = ! empty ( $folder ) ? $folder : APPS_PATH . substr ( $filePath, 0, $pos ) . DIRECTORY_SEPARATOR;
			$path = $namespace . str_replace ( '.', '/', substr ( $filePath, $pos + 1 ) ) . '.php';
		} else {
			$namespace = ! empty ( $folder ) ? $folder : FW_PATH;
			$path = $namespace . str_replace ( '.', '/', $filePath ) . '.php';
		}
		if (self::_file_exists ( $path ) && is_file ( $path )) {
			self::$_imports [$filePath] = $fileName;
			self::autoload ( $fileName, $path );
		} else {
			throw new Exception ( 'Unable to load the file ' . $path . ' , file is not exist.' );
		}
		return $fileName;
	}

	/**
	 * 自动装入
	 *
	 * 如果类名按照Core/Core_BASE 这种方式命名可自动加载
	 *
	 * @param string $class
	 */
	public static function autoload($class, $path = '') {
		if ($path){
			include $path;
		}elseif (strpos ( $class, '_' ) !== false) {
			self::import ( str_replace ( '_', '.', $class ), FW_PATH . 'library' . DIRECTORY_SEPARATOR );
		} else {
			self::import ($class, FW_PATH . 'library' . DIRECTORY_SEPARATOR );
		}
	}

	/**
	 * 获取debug对象
	 * 可安全用于生产环境，在生产环境下将忽略所有debug信息
	 *
	 * @return Debug
	 */
	public static function debug() {
		static $debug = null;
		if (null === $debug) {
			if (! IS_CLI && IS_DEBUG && class_exists ( 'Base_Debug', true )) {
				$debug = Base_Debug::instance ();
			} else {
				$debug = new Base_NoDebug ();
			}
		}
		return $debug;
	}

	/**
	 * 区分大小写的文件存在判断
	 *
	 * @param string $filename 文件地址
	 * @return boolean
	 */
	public static function _file_exists($filename) {
		if (is_file ( $filename )) {
			if (IS_WIN) {
				if (basename ( realpath ( $filename ) ) != basename ( $filename )) return false;
			}
			return true;
		}
		return false;
	}
}
Core::init ();