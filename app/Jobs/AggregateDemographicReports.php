<?php

namespace App\Jobs;

use App\Models\Resident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AggregateDemographicReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const CACHE_KEY = 'reports.demographics.v1';

    public function handle(): void
    {
        Cache::forever(self::CACHE_KEY, self::payload());
    }

    public static function payload(): array
    {
        $base = Resident::query()->where('residency_status', 'Active');
        $total = (clone $base)->count();

        return [
            'generated_at' => now()->toIso8601String(),
            'summary' => [
                'total_residents' => $total,
                'registered_voters' => (clone $base)->where('voter_status', 'Registered')->count(),
                'unregistered_voters' => (clone $base)->where('voter_status', 'Unregistered')->count(),
                'senior_citizens' => self::ageCount(60, null),
            ],
            'gender' => self::countByColumn('gender'),
            'voter_status' => self::countByColumn('voter_status'),
            'age_groups' => [
                '0-12' => self::ageCount(0, 12),
                '13-17' => self::ageCount(13, 17),
                '18-35' => self::ageCount(18, 35),
                '36-59' => self::ageCount(36, 59),
                '60+' => self::ageCount(60, null),
            ],
        ];
    }

    private static function countByColumn(string $column): array
    {
        return Resident::query()
            ->where('residency_status', 'Active')
            ->select($column, DB::raw('count(*) as aggregate'))
            ->groupBy($column)
            ->orderBy($column)
            ->pluck('aggregate', $column)
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private static function ageCount(int $minimumAge, ?int $maximumAge): int
    {
        $today = now()->toDateString();

        $query = Resident::query()->where('residency_status', 'Active');

        if ($maximumAge !== null) {
            $query->whereDate('birthdate', '<=', now()->subYears($minimumAge)->toDateString())
                ->whereDate('birthdate', '>', now()->subYears($maximumAge + 1)->toDateString());
        } else {
            $query->whereDate('birthdate', '<=', now()->subYears($minimumAge)->toDateString());
        }

        return $query->whereDate('birthdate', '<=', $today)->count();
    }
}
