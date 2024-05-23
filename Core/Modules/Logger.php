<?php

namespace DiplomaProject\Core\Modules;

use DiplomaProject\Core\Core;
use DiplomaProject\Core\Libs\FileHelper;
use DiplomaProject\Core\Module;

class Logger extends Module
{
    /**
     * the path relative to the application root
     */
    private string $logsdir = '';
    private string $logfile_name = 'common.log';
    private int $level = 0;

    protected function setLogsdir(string $logsdir)
    {
        $real_logsdir = Core::getCurrentApp()->getAppRoot() . $logsdir;

        FileHelper::initDir($real_logsdir, 'Logger');

        $this->logsdir = $real_logsdir;
    }

    protected function setLevel(int $level)
    {
        $this->level = $level;
    }

    private function addError(int $level, string $message)
    {
        if ($this->level > $level) {
            return;
        }

        $level_label = $this->getLevelLabel($level);
        $date = \date('Y-m-d H:i:s');

        $content = "[{$date}] $level_label message: $message \n";

        file_put_contents("{$this->logsdir}/{$this->logfile_name}", $content, FILE_APPEND);
    }

    private function getLevelLabel(int $level)
    {
        switch ($level) {
            case 0:
                return 'debug';
            case 10:
                return 'info';
            case 20:
                return 'warning';
            case 30:
                return 'error';
        }
    }

    public function error(string $message)
    {
        $this->addError(30, $message);
    }

    public function warning(string $message)
    {
        $this->addError(20, $message);
    }

    public function info(string $message)
    {
        $this->addError(10, $message);
    }

    public function debug(string $message)
    {
        $this->addError(0, $message);
    }
}
