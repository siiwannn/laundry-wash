<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $revenueReport = $this->reportService->getRevenueReport($startDate, $endDate);
        $courierStats = $this->reportService->getCourierPerformance();
        $summary = $this->reportService->getSummaryStats();

        return view('admin.reports.index', compact('revenueReport', 'courierStats', 'summary'));
    }
}
