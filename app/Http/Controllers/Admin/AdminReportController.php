<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Services\ReportService;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(ReportFilterRequest $request): View
    {
        $startDate = $request->validated('start_date');
        $endDate = $request->validated('end_date');

        $revenueReport = $this->reportService->getRevenueReport($startDate, $endDate);
        $courierStats = $this->reportService->getCourierPerformance();
        $summary = $this->reportService->getSummaryStats();

        return view('admin.reports.index', compact('revenueReport', 'courierStats', 'summary'));
    }
}
