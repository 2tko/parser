<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\ProjectService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    private $projectService;
    private $dashboardService;

    public function __construct(
        ProjectService $projectService,
        DashboardService $dashboardService
    ) {
        $this->projectService = $projectService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function getProjects(Request $request): JsonResponse
    {
        return response()->json([
            'projects' => $this->projectService->getTableStatistic($request->get('name')),
        ]);
    }

    public function getGrowthCountHoldersDashboard(): JsonResponse
    {
        return response()->json([
            'categories' => $this->dashboardService->getCategories(),
            'growth' => $this->dashboardService->getData('desc'),
        ]);
    }

    public function getFallCountHoldersDashboard(): JsonResponse
    {
        return response()->json([
            'categories' => $this->dashboardService->getCategories(),
            'fall' => $this->dashboardService->getData(),
        ]);
    }

    public function getGrowthPercentCountHoldersDashboard(): JsonResponse
    {
        return response()->json([
            'growthPercent' => $this->dashboardService->getDataForPercent('desc'),
        ]);
    }
}
