<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use function str_starts_with;


class CodeCoverageCollectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $reportDataKey = 'code_coverage_report_data';
    private static string $codeReportDir;
    private static \Redis $redisConnection;

    public function __construct(private readonly array $coverageData)
    {
    }

    public static function getCodeReportDir(): string
    {
        return config('code_coverage.collect_path',app_path('Services'));
    }

    /**
     * @throws \RedisException
     */
    public function handle(): void
    {

        if(!self::$redisConnection->isConnected()){
            self::$redisConnection = Redis::connection()->client();
        }

        if (empty(self::$codeReportDir)) {
            self::$codeReportDir = self::getCodeReportDir();
        }

        $dataKey = 'code_coverage:request_data:' . md5(serialize($this->coverageData));
        if (self::$redisConnection->exists($dataKey)) {
            return;
        }
        self::$redisConnection->set($dataKey, 1, rand(60, 75));

        $realData = [];
        foreach ($this->coverageData as $fileName => $data) {
            if (!str_starts_with($fileName, self::$codeReportDir)) {
                continue;
            }
            $realData[$fileName] = $data;
        }

        if (empty($realData)) {
            return;
        }

        //判断1分钟内是否请求过
        $reqDataKey = 'code_coverage:request_data:' . md5(serialize($realData));
        if (self::$redisConnection->exists($reqDataKey)) {
            return;
        }
        self::$redisConnection->set($reqDataKey, 1, rand(60, 75));
        unset($reqDataKey);


        foreach ($realData as $fileName => $data) {
            //相对路径
            $filePath = str_replace(app()->basePath() . DIRECTORY_SEPARATOR, '', $fileName);

            $key = 'code_coverage:file:' . $filePath;

            $nowFileContent = file_get_contents($fileName);

            //File not modified
            $contentKey = 'content:' . md5($nowFileContent);
            if (self::$redisConnection->hGet($key, $contentKey)) {
                $historyCoverageData = json_decode(self::$redisConnection->hGet($key, 'coverage_data'), true) ?? [];
                $newCoverageData = [];
                foreach ($historyCoverageData as $line => $historyValue) {
                    $newCoverageData[$line] = $historyValue > 0 ? $historyValue : ($data[$line] ?? -1);
                }
                if ($newCoverageData != $historyCoverageData) {
                    self::$redisConnection->hSet($key, 'coverage_data', json_encode($newCoverageData));
                }
                continue;
            }


            //File has been modified
            $result = self::$redisConnection->hGetAll($key);
            if (empty($result)) {
                self::$redisConnection->hSet($key, 'coverage_data', json_encode($data));
                self::$redisConnection->hSet($key, $contentKey, $nowFileContent);
                continue;
            }

            $historyCoverageData = $newCoverageData = [];
            $historyFileContentValue = '';
            $historyFileContentKey = '';
            foreach ($result as $k => $value) {
                //history coverage_data
                if ($k == 'coverage_data') {
                    $historyCoverageData = json_decode($value, true);
                    continue;
                }
                //history content
                if (str_starts_with($k, 'content:')) {
                    $historyFileContentKey = $k;
                    $historyFileContentValue = $value;
                }
            }

            $historyFileContentValues = explode(PHP_EOL, $historyFileContentValue);
            $nowFileContentValues = explode(PHP_EOL, $nowFileContent);

            foreach ($historyCoverageData as $line => $v) {
                if (!isset($historyFileContentValues[$line])) {
                    continue;
                }
                $lineV = $historyFileContentValues[$line];

                $keys = array_keys($nowFileContentValues, $lineV);
                if (!empty($nowFileContentValues)) {
                    $newLine = $this->findClosestNumber($line, $keys);
                    $newCoverageData[$newLine] = $v;
                }
            }

            self::$redisConnection->multi();
            self::$redisConnection->hDel($key, $historyFileContentKey);
            self::$redisConnection->hSet($key, $contentKey, $nowFileContent);
            self::$redisConnection->hSet($key, 'coverage_data', json_encode($newCoverageData));
            self::$redisConnection->exec();

        }

    }

    private function findClosestNumber($target, $numbers)
    {
        $closest = null;
        $minDiff = PHP_INT_MAX;

        foreach ($numbers as $number) {
            $diff = abs($target - $number);
            if ($diff == 0) {
                return $number;
            }
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $number;
            }
        }
        return $closest;
    }

}