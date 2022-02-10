<?php

namespace App\Services;

use App\Models\Holder;
use App\Models\Project;
use App\Models\ProjectGrowth;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class CoinMarketCapService
{
    const CRYPTOCURRENCY_URL = 'https://api.coinmarketcap.com/data-api/v3/cryptocurrency/listing?start=%s&limit=1000&sortBy=market_cap&sortType=desc&convert=USD,BTC,ETH&cryptoType=tokens&tagType=all&audited=false&aux=ath,atl,high24h,low24h,num_market_pairs,cmc_rank,date_added,tags,platform,max_supply,circulating_supply,total_supply,volume_7d,volume_30d';

    const HOLDERS_URL = 'https://api.coinmarketcap.com/data-api/v3/cryptocurrency/detail/holders/count?id=%s&range=%s';

    /**
     * @var Collection | null
     */
    private $holdersTmp;

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function insertData(string $holdersRange): void
    {
        $start = 1;

        do {
            echo 'Page ' . $start . PHP_EOL;

            $projectsCryptocurrencyList = $this->getCryptocurrencyList($start);
            foreach ($projectsCryptocurrencyList as $projectCryptocurrency) {
                echo 'Processing project ' . $projectCryptocurrency->name . PHP_EOL;

                sleep(rand(5, 7));

                $holderData = $this->getHolders($projectCryptocurrency->id, $holdersRange);
                if (empty($holderData)) {
                    continue;
                }

                $project = $this->getProjectByRemoteId($projectCryptocurrency->id);
                if (!$project) {
                    echo 'Save project ' . $projectCryptocurrency->name . PHP_EOL;

                    $project = $this->createProject($projectCryptocurrency);
                }

                $this->updateCmcRank($project, $projectCryptocurrency->cmcRank);

                foreach ($holderData as $date => $count) {
                    $date = Carbon::parse($date);
                    if ($this->holdersRecordExists($project, $date)) {
                        if ($this->shouldUpdateHolderRecord($date)) {
                            $this->updateHolderRecord($project, $count, $date);
                        }

                        continue;
                    }

                    $this->createHolderRecord($project, $count, $date);
                }

                $this->unsetHoldersTmp();

                echo PHP_EOL;
            }
            $start += 1000;
        } while (!empty($projectsCryptocurrencyList));
    }

    public function updateProjectGrowth(): void
    {
        Project::all()->each(function (Project $project) {
            $holdersYesterday = $project->holders->where('date', Carbon::now()->subDay()->toDateString())->first();
            if (!empty($holdersYesterday)) {
                $data = [
                    ['key' => ProjectGrowth::WEEK, 'holder' => $project->holders->where('date', Carbon::now()->subWeek()->toDateString())->first()],
                    ['key' => ProjectGrowth::TWO_WEEKS, 'holder' => $project->holders->where('date', Carbon::now()->subWeeks(2)->toDateString())->first()],
                    ['key' => ProjectGrowth::MONTH, 'holder' => $project->holders->where('date', Carbon::now()->subMonth()->toDateString())->first()],
                    ['key' => ProjectGrowth::THREE_MONTHS, 'holder' => $project->holders->where('date', Carbon::now()->subMonths(3)->toDateString())->first()],
                    ['key' => ProjectGrowth::SIX_MONTHS, 'holder' => $project->holders->where('date', Carbon::now()->subMonths(6)->toDateString())->first()],
                    ['key' => ProjectGrowth::YEAR, 'holder' => $project->holders->where('date', Carbon::now()->subYear()->toDateString())->first()],
                ];

                foreach ($data as $item) {
                    if (!empty($item['holder'])) {
                        $value = round((($holdersYesterday->count - $item['holder']->count) / $holdersYesterday->count) * 100, 2);
                        $projectGrowth = ProjectGrowth::where('project_id', $project->id)
                            ->where('key', $item['key'])
                            ->first();

                        if (empty($projectGrowth)) {
                            $projectGrowth = new ProjectGrowth;
                            $projectGrowth->project_id = $project->id;
                            $projectGrowth->key = $item['key'];
                        }

                        $projectGrowth->value = $value;
                        $projectGrowth->save();
                    }
                }
            }
        });
    }

    public function recountRating(): void
    {
        $rating = 1;

        ProjectGrowth::where('key', ProjectGrowth::TWO_WEEKS)
            ->orderBy('value', 'desc')
            ->each(function (ProjectGrowth $projectGrowth) use (&$rating) {
                $project = Project::find($projectGrowth->project_id);
                if ($project) {
                    $project->rating = $rating;
                    $project->save();
                }

                $rating++;
            });
    }

    public function getCryptocurrencyUrl(int $start): string
    {
        return sprintf(self::CRYPTOCURRENCY_URL, $start);
    }

    public function getHoldersUrl(int $id, string $range = '7d'): string
    {
        return sprintf(self::HOLDERS_URL, $id, $range);
    }

    public function getCryptocurrencyList($start): array
    {
        $response = $this->client->get($this->getCryptocurrencyUrl($start));
        $projectData = json_decode($response->getBody()->getContents());

        return $projectData->data->cryptoCurrencyList ?? [];
    }

    public function getHolders(int $id, string $holdersRange): array
    {
        $response = $this->client->get($this->getHoldersUrl($id, $holdersRange));
        $holderData = json_decode($response->getBody()->getContents());

        if (empty($holderData->data->points)) {
            return [];
        }

        return (array) $holderData->data->points ?? [];
    }

    private function getProjectByRemoteId(int $coinmarketcapProjectId): ?Project
    {
        return Project::where('coinmarketcap_project_id', $coinmarketcapProjectId)->first();
    }

    private function createProject($cryptoCurrency): Project
    {
        $project = new Project();
        $project->coinmarketcap_project_id = $cryptoCurrency->id;
        $project->name = $cryptoCurrency->name;
        $project->slug = $cryptoCurrency->slug;
        $project->save();
        $project->refresh();

        return $project;
    }

    private function holdersRecordExists(Project $project, Carbon $date): bool
    {
        if (empty($this->holdersTmp)) {
            $this->holdersTmp = Holder::where('project_id', $project->id)->get();
        }

        return $this->holdersTmp->where('date', $date->toDateString())->isNotEmpty();
    }

    private function unsetHoldersTmp(): void
    {
        unset($this->holdersTmp);
    }

    private function shouldUpdateHolderRecord(Carbon $date): bool
    {
        return $date->startOfDay()->equalTo(Carbon::now()->startOfDay())
            || $date->startOfDay()->equalTo(Carbon::now()->subDay()->startOfDay())
            || $date->startOfDay()->equalTo(Carbon::now()->subDays(2)->startOfDay())
            || $date->startOfDay()->equalTo(Carbon::now()->subDays(3)->startOfDay())
            || $date->startOfDay()->equalTo(Carbon::now()->subDays(4)->startOfDay());
    }

    private function createHolderRecord(Project $project, int $count, Carbon $date): void
    {
        $holder = new Holder();
        $holder->project_id = $project->id;
        $holder->count = $count;
        $holder->date = $date;
        $holder->save();
    }

    private function updateHolderRecord(Project $project, int $count, Carbon $date): void
    {
        $holder = Holder::where('project_id', $project->id)
            ->where('date', $date->toDateString())
            ->first();

        if ($holder) {
            $holder->count = $count;
            $holder->save();
        }
    }

    private function updateCmcRank(Project $project, int $cmcRank): void
    {
        $project->cmc_rank = $cmcRank;
        $project->save();
    }
}
