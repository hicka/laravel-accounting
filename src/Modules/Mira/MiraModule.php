<?php


namespace Hickr\Accounting\Modules\Mira;

use Hickr\Accounting\Contracts\RegionalModule;

class MiraModule implements RegionalModule
{
public function getAvailableReports(): array
{
return ['gst201', 'wht', 'income_tax'];
}

public function generateReport(string $reportKey, array $params)
    {

    }
}