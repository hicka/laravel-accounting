<?php

namespace Hickr\Accounting\Contracts;

interface RegionalModule
{
    public function getAvailableReports(): array;

    public function generateReport(string $reportKey, array $params);
}