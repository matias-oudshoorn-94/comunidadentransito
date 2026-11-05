<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Plan extends Model
{
    protected static string $table = 'plans';

    public static function active(): array
    {
        return self::where('is_active = 1', [], 'sort ASC');
    }

    public static function price(array $plan, string $currency): float
    {
        return (float) ($currency === 'USD' ? $plan['price_usd'] : $plan['price_ars']);
    }

    /**
     * Para el selector de planes: [periodo => [tier => plan]].
     * Si un nivel no tiene plan en ese período (Orilla es solo mensual), se usa el mensual.
     */
    public static function byPeriod(): array
    {
        $plans = self::active();
        $periods = ['mensual' => [], 'semestral' => [], 'anual' => []];
        foreach ($plans as $plan) {
            $periods[$plan['period']][$plan['tier']] = $plan;
        }
        foreach (['semestral', 'anual'] as $period) {
            foreach ($periods['mensual'] as $tier => $monthly) {
                $periods[$period][$tier] ??= $monthly + ['only_monthly' => true];
            }
        }
        foreach ($periods as $period => $tiers) {
            uksort($tiers, fn ($a, $b) => $a === 'orilla' ? -1 : ($b === 'orilla' ? 1 : 0));
            $periods[$period] = $tiers;
        }
        return array_filter($periods);
    }
}
