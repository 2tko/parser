<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\ProjectService;
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

    public function getGrowthCountHoldersDashboard(Request $request): JsonResponse
    {
        return response()->json([
            'categories' => $this->dashboardService->getCategories(),
            'growth' => $this->dashboardService->getData($request->get('page'), 'desc'),
        ]);
    }

    public function getFallCountHoldersDashboard(Request $request): JsonResponse
    {
        return response()->json([
            'categories' => $this->dashboardService->getCategories(),
            'fall' => $this->dashboardService->getData(),
        ]);
    }

    public function getGrowthPercentCountHoldersDashboard(Request $request): JsonResponse
    {
        return response()->json([
            'growthPercent' => $this->dashboardService->getDataForPercent($request->get('page'), 'desc'),
        ]);
    }
}
