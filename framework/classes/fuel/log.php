<?php
/**
 * NOVIUS OS - Web OS for digital communication
 *
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */

class Log extends Fuel\Core\Log
{
    /**
     * Initialize the class
     */
    public static function _init()
    {
        parent::_init();
        $filepath = \Config::get('log_path').date('Y/m').'/';
        $filename = $filepath.date('d').'.php';
        // load the file config
        \Config::load('file', true);

        static::$monolog = new \Nos\Monolog_Logger('fuelphp');

        // create the streamhandler, and activate the handler
        $stream = new \Monolog\Handler\StreamHandler($filename, \Monolog\Logger::DEBUG);
        $formatter = new \Monolog\Formatter\LineFormatter("%level_name% - %datetime% --> %message%".PHP_EOL, "Y-m-d H:i:s");
        $stream->setFormatter($formatter);
        static::$monolog->pushHandler($stream);
    }

    public static function deprecated($message, $since = null)
    {
        $debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($debug_backtrace);
        $returns = \Event::trigger('nos.deprecated', array('message' => $message, 'since' => $since, 'debug_backtrace' => $debug_backtrace), 'array');
        $returns = array_filter($returns, function ($val) {
            return $val === false;
        });
        if (count($returns) > 0) {
            return;
        }

        if (!empty($debug_backtrace[0]['class'])) {
            $method = $debug_backtrace[0]['class'].$debug_backtrace[0]['type'].$debug_backtrace[0]['function'].'()';
        } else {
            $method = $debug_backtrace[0]['function'].'()';
        }
        $log = 'Deprecated in '.$method;
        if (!empty($since)) {
            $log .= ', since '.$since;
        }
        $log .= ': '.$message;
        logger(\Fuel::L_WARNING, $log);
    }

    public static function exception($e, $prefix = '') {
        \Log::error($prefix.$e->getCode().' - '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
    }
}
