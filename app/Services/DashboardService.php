<?php

namespace App\Services;

use App\Models\Holder;
use App\Models\Project;
use App\Models\ProjectGrowth;
use Carbon\Carbon;

class DashboardService
{
    const LIMIT = 20;

    public function getData(string $orderType = 'asc'): array
    {
        $between = [1, 20];
        if ($orderType == 'asc') {
            $maxRating = Project::max('rating');
            $between = [$maxRating - 20, $maxRating];
        }
        $projects = Project::query()
            ->from('projects as p')
            ->addSelect('p.id')
            ->addSelect('p.name')
            ->addSelect('h.count')
            ->addSelect('h.date')
            ->join('holders as h', 'h.project_id', '=', 'p.id')
            ->whereBetween('p.rating', $between)
            ->where('h.date', '>=', Carbon::now()->subMonth()->toDateString())
            ->orderBy('h.date')
            ->get()
            ->groupBy('name');

        $data = [];
        $i = 0;
        foreach ($projects as $key => $projectData) {
            $data[$i]['name'] = $key;

            $monthAgo = Carbon::now()->subMonth();
            $date = $monthAgo->toDateString();
            $start = 0;
            while ($start <= 30) {
                $count = 0;
                $dataForDate = $projectData->where('date', $monthAgo->toDateString())->first();
                if ($dataForDate) {
                    $count = $dataForDate->count;
                }

                $data[$i]['data'][] = [$date, $count];
                $start++;
                $date = $monthAgo->addDay()->toDateString();

            }

            $i++;
        }

        return $data;
    }

    public function getDataForPercent(string $orderType = 'asc'): array
    {
        $projects = Project::query()
            ->from('projects as p')
            ->addSelect('p.id')
            ->addSelect('p.name')
            ->addSelect('pg.value')
            ->join('projects_growth as pg', function ($join) {
                $join->on('pg.project_id', '=', 'p.id')
                    ->where('pg.key', '=', ProjectGrowth::TWO_WEEKS);
            })
            ->whereBetween('p.rating', [1, 20])
            ->get();

        $data = [];
        $projects->each(function ($project) use (&$data) {
            $data[] = [
                'name' => $project->name,
                'y' => (float)$project->value,
            ];
        });

        return $data;
    }


    public function getCategories(): array
    {
        $category = [];
        $start = 0;
        $monthAgo = Carbon::now()->subMonth();

        while ($start <= 30) {
            $category[] = $monthAgo->addDay()->toDateString();
            $start++;
        }

        return $category;
    }
}
