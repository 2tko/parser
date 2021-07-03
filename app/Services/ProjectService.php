<?php

namespace App\Services;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    const PER_PAGE_FOR_TABLE = 20;

    public function getTableStatistic(?string $name): LengthAwarePaginator
    {
        $data = Project::query()
            ->from('projects as p')
            ->addSelect('p.id as id')
            ->addSelect('p.cmc_rank')
            ->addSelect('p.rating')
            ->addSelect('p.name')
            ->addSelect('p.slug')
            ->addSelect(\DB::raw('SUM(hy.count) as total_count'))
            ->addSelect(\DB::raw('SUM(hw.count) as total_count_week'))
            ->addSelect(\DB::raw('SUM(h2w.count) as total_count_2_weeks'))
            ->addSelect(\DB::raw('SUM(hm.count) as total_count_month'))
            ->addSelect(\DB::raw('SUM(h3m.count) as total_count_3_month'))
            ->addSelect(\DB::raw('SUM(h6m.count) as total_count_6_month'))
            ->addSelect(\DB::raw('SUM(hye.count) as total_count_year'))
            ->when($name, function ($query) use ($name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->leftJoin('holders as hy', function ($join) {
                $join->on('hy.project_id', '=', 'p.id')
                    ->where('hy.date', '=', Carbon::now()->subDay()->toDateString());
            })
            ->leftJoin('holders as hw', function ($join) {
                $join->on('hw.project_id', '=', 'p.id')
                    ->where('hw.date', '=', Carbon::now()->subWeek()->toDateString());
            })
            ->leftJoin('holders as h2w', function ($join) {
                $join->on('h2w.project_id', '=', 'p.id')
                    ->where('h2w.date', '=', Carbon::now()->subWeeks(2)->toDateString());
            })
            ->leftJoin('holders as hm', function ($join) {
                $join->on('hm.project_id', '=', 'p.id')
                    ->where('hm.date', '=', Carbon::now()->subMonth()->toDateString());
            })
            ->leftJoin('holders as h3m', function ($join) {
                $join->on('h3m.project_id', '=', 'p.id')
                    ->where('h3m.date', '=', Carbon::now()->subMonths(3)->toDateString());
            })
            ->leftJoin('holders as h6m', function ($join) {
                $join->on('h6m.project_id', '=', 'p.id')
                    ->where('h6m.date', '=', Carbon::now()->subMonths(6)->toDateString());
            })
            ->leftJoin('holders as hye', function ($join) {
                $join->on('hye.project_id', '=', 'p.id')
                    ->where('hye.date', '=', Carbon::now()->subYear()->toDateString());
            })
            ->where('p.rating', '>', 0)
            ->groupBy('p.id')
            ->orderBy('p.rating')
            ->paginate(self::PER_PAGE_FOR_TABLE);

        $data->each(function ($item) {
            $item->total_count_week = $this->makePercentage($item->total_count, $item->total_count_week);
            $item->total_count_2_weeks = $this->makePercentage($item->total_count, $item->total_count_2_weeks);
            $item->total_count_month = $this->makePercentage($item->total_count, $item->total_count_month);
            $item->total_count_3_month = $this->makePercentage($item->total_count, $item->total_count_3_month);
            $item->total_count_6_month = $this->makePercentage($item->total_count, $item->total_count_6_month);
            $item->total_count_year = $this->makePercentage($item->total_count, $item->total_count_year);
        });

        return $data;
    }

    private function makePercentage(?int $total, ?int $for): string
    {
        if (is_null($total) || is_null($for)) {
            return '';
        }

        return sprintf(
            '%s ( %s%% )',
            $for,
            round((($total - $for) / $total) * 100, 2)
        );
    }
}
