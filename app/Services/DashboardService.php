<?php

namespace App\Services;

use App\Models\Holder;
use App\Models\Project;
use App\Models\ProjectGrowth;
use Carbon\Carbon;

class DashboardService
{
    const LIMIT = 20;
    const COUNT_DAYS = 29;

    public function getData(int $page = 1, string $orderType = 'asc'): array
    {
        $between = $this->getBetween($page);
        if ($orderType == 'asc') {
            $maxRating = Project::max('rating');
            $between = [$maxRating - 20, $maxRating];
        }

        $projects = Project::query()
            ->from('projects as p')
            ->addSelect('p.id')
            ->addSelect('p.rating')
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
            $data[$i]['legendIndex'] = $projectData[0]->rating;

            $monthAgo = Carbon::now()->subMonth();
            $date = $monthAgo->toDateString();
            $start = 0;
            while ($start <= self::COUNT_DAYS) {
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

    public function getDataForPercent(int $page = 1, string $orderType = 'asc'): array
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
            ->whereBetween('p.rating', $this->getBetween($page))
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

        $date = $monthAgo->toDateString();
        while ($start <= self::COUNT_DAYS) {
            $category[] = $date;
            $date = $monthAgo->addDay()->toDateString();

            $start++;
        }

        return $category;
    }

    private function getBetween(int $page = 1): array
    {
        $between = [1, self::LIMIT];
        if ($page > 1) {
            $page = self::LIMIT * $page;
            $between = [($page - self::LIMIT + 1), $page];
        }

        return $between;
    }
}
