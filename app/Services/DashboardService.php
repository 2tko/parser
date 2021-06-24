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
        $projects = Project::query()
            ->from('projects as p')
            ->addSelect('p.id')
            ->addSelect('p.name')
            ->join('projects_growth as pg', function ($join) {
                $join->on('pg.project_id', '=', 'p.id')
                    ->where('pg.key', '=', ProjectGrowth::TWO_WEEKS);
            })
            ->orderBy('pg.value', $orderType)
            ->limit(self::LIMIT)
            ->get();

        $holders = Holder::whereIn('project_id', $projects->pluck('id'))
            ->where('date', '>=', Carbon::now()->subMonth()->toDateString())
            ->orderBy('date')
            ->get();

        $i = 0;
        $data = [];
        foreach ($projects as $project) {
            $monthAgo = Carbon::now()->subMonth();
            $start = 0;
            $data[$i]['name'] = $project->name;

            while ($start <= 30) {
                $date = $monthAgo->addDay()->toDateString();
                $holder = $holders->where('project_id', $project->id)->where('date', $date)->first();
                $data[$i]['data'][] = [$date, $holder->count ?? 0];
                $start++;
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
            ->orderBy('pg.value', $orderType)
            ->limit(self::LIMIT)
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
