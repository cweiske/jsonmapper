<?php

include('vendor/autoload.php');

$timeStart = microtime(true);
$jm = new JsonMapper();
for($i = 0; $i < 100000; $i++) {
    $sn = $jm->map(
        json_decode('{"simpleSetterOnlyTypeHint":{"str":"stringvalue"}}'),
        new JsonMapperTest_Simple()
    );
}
$timeEnd = microtime(true);
$diff = $timeEnd - $timeStart;
printf("PHP Elapsed %0.3f\n", $diff);

