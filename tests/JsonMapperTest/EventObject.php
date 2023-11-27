<?php
class JsonMapperTest_EventObject
{
    /**
     * @var string
     */
    public $pStr;

    private function _deserializePostEvent()
    {
        $this->pStr = 'two';
    }

    /**
     * @param int $arg1
     * @param string $arg2
     */
    private function _deserializePostEventWithArguments($arg1, $arg2)
    {
        $this->pStr = str_repeat($arg2, $arg1);
    }
}
?>
