<?php

namespace zaboy\async\Callback\Interrupter;

use zaboy\async\Callback\CallbackException;
use zaboy\async\Callback\Interfaces\InterrupterInterface;
use zaboy\async\Promise\Client as PromiseClient;

class Script implements InterrupterInterface
{
    const DEFAULT_SCRIPT_NAME = 'scripts/scriptProxy.php';

    const DEFAULT_COMMAND_PREFIX = 'php';

    protected $script = self::DEFAULT_SCRIPT_NAME;

    protected $commandPrefix = self::DEFAULT_COMMAND_PREFIX;


    public function interrupt($value, PromiseClient $promise, callable $callback)
    {
        if (is_null($this->script)) {
            $this->setScriptName(self::DEFAULT_SCRIPT_NAME);
        }

        // Files names for stdout and stderr
        $stdOutFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stdout_', 1);
        $stdErrFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stderr_', 1);

        $cmd = $this->commandPrefix . ' ' . $this->script;

        $promiseId = $promise->getId();
        $arrayData = compact('callback', 'value', 'promiseId');
        $serializedData = serialize($arrayData);
        $data64 = base64_encode($serializedData);

        $cmd .= ' ' . $data64;
        $cmd .= " 1>{$stdOutFilename} 2>{$stdErrFilename}";

        shell_exec($cmd);

        return $promise;
    }


    public function setScriptName($script)
    {
        if (!is_file($script)) {
            throw new CallbackException("Specified script \"{$script}\" doesn't exists");
        }
        $this->script = $script;
        return $this;
    }


    public function setCommandPrefix($prefix)
    {
        $this->commandPrefix = $prefix;
        return $this;
    }
}