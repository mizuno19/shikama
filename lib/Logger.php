<?php
class Logger {
    const LOG_LEVEL_ERROR = 0;
    const LOG_LEVEL_WARN = 1;
    const LOG_LEVEL_INFO = 2;
    const LOG_LEVEL_DEBUG = 3;

    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    private function __construct() {

    }

    public function e($msg) {
        if (self::LOG_LEVEL_ERROR <= Config::LOG_LEVEL) {
            $this->out('ERROR', $msg);
        }
    }

    public function w($msg) {
        if (self::LOG_LEVEL_WARN <= Config::LOG_LEVEL) {
            $this->out('WARN', $msg);
        }
    }

    public function i($msg) {
        if (self::LOG_LEVEL_INFO <= Config::LOG_LEVEL) {
            $this->out('INFO', $msg);
        }
    }

    public function d($msg) {
        if (self::LOG_LEVEL_DEBUG <= Config::LOG_LEVEL) {
            $this->out('DEBUG', $msg);
        }
    }

    private function out($level, $msg) {
        $pid = getmypid();
        $time = $this->getTime();
        $logMessage = "[{$time}][{$pid}][{$level}] " . rtrim($msg) . PHP_EOL;

        if (Config::IS_LOGFILE) {
            $logFilePath = Config::LOGDIR_PATH . Config::LOGFILE_NAME . '.log';
            
            $result = file_put_contents($logFilePath, $logMessage, FILE_APPEND | LOCK_EX);
            if (!$result) {
                error_log('LogUtil::out error_log ERROR', 0);
            }

            if (Config::LOGFILE_MAXSIZE < filesize($logFilePath)) {
                $oldPath = Config::LOGDIR_PATH . Config::LOGFILE_NAME . "_" . date('YmdHis');
                $oldLogFilePath = $oldPath . '.log';
                rename($logFilePath, $oldLogFilePath);
                $gz = gzopen($oldPath . '.gz', 'w9');
                if ($gz) {
                    gzwrite($gz, file_get_contents($oldLogFilePath));
                    $isClose = gzclose($gz);
                    if ($isClose) {
                        unlink($oldLogFilePath);
                    } else {
                        error_log("gzclose ERROR.", 0);
                    }
                } else {
                    error_log("gzopen ERROR", 0);
                }

                $retentionDate = new DateTime();
                $retentionDate->modify('-' . Config::LOGFILE_PERIOD . ' day');
                if ($dh = opendir(Config::LOGDIR_PATH)) {
                    while (($fileName = readdir($dh)) !== false) {
                        $pm = preg_match("/" . preg_quote(Config::LOGFILE_NAME) . "_(\d{14}).*\.gz/", $fileName, $matches);
                        if ($pm === 1) {
                            $logCreatedDate = DateTime::createFromFormat('YmdHis', $matches[1]);
                            if ($logCreatedDate < $retentionDate) {
                                unlink(Config::LOGDIR_PATH . '/' . $fileName);
                            }
                        }
                    }
                    closedir($dh);
                }
            }
        } else {
            echo "<pre>$logMessage</pre>";
        }
    }

    private function getTime() {
        $miTime = explode('.', microtime(true));
        $msec = str_pad(substr($miTime[1], 0, 3), 3, "0");
        $time = date('Y-m-d H:i:s', $miTime[0]) . '.' . $msec;
        return $time;
    }
}

1;