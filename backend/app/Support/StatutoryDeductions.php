<?php

namespace App\Support;

use App\Models\SalaryStructure;

/**
 * Indian statutory salary deductions (employee share only).
 *
 *   PF   - 12% of Basic, applied to at most the EPF wage ceiling (₹15,000),
 *          which caps the monthly deduction at ₹1,800.
 *   ESIC - 0.75% of monthly gross, charged only while the gross wage stays at
 *          or below the ESIC ceiling (₹21,000). Statutory rounding is upwards
 *          to the next rupee.
 *
 * The employer's matching contributions are intentionally not modelled: this
 * app only deducts the employee's share from salary.
 */
class StatutoryDeductions
{
    /** Employee Provident Fund rate. */
    public const PF_RATE = 0.12;

    /** EPF wage ceiling; the 12% applies to at most this much Basic. */
    public const PF_WAGE_CEILING = 15000.0;

    /** Employee ESIC rate. */
    public const ESIC_EMPLOYEE_RATE = 0.0075;

    /** ESIC applies only while monthly gross stays at or below this. */
    public const ESIC_WAGE_CEILING = 21000.0;

    /**
     * Full-month gross earnings of a structure (no overtime, no bonus). Used
     * as the ESIC wage base.
     */
    public static function monthlyGross(SalaryStructure $structure): float
    {
        return (float) $structure->basic_salary
            + (float) $structure->hra
            + (float) $structure->conveyance
            + (float) $structure->medical_allowance
            + (float) $structure->special_allowance
            + (float) $structure->site_allowance
            + (float) $structure->travel_allowance
            + (float) $structure->food_allowance
            + (float) $structure->other_earnings;
    }

    /** PF on Basic, capped at the EPF wage ceiling. */
    public static function pf(float $basicSalary): float
    {
        $wage = min(max($basicSalary, 0.0), self::PF_WAGE_CEILING);

        return round($wage * self::PF_RATE, 2);
    }

    /** ESIC on gross, zero above the ceiling, rounded up to the next rupee. */
    public static function esic(float $monthlyGross): float
    {
        if ($monthlyGross <= 0 || $monthlyGross > self::ESIC_WAGE_CEILING) {
            return 0.0;
        }

        return (float) ceil($monthlyGross * self::ESIC_EMPLOYEE_RATE);
    }

    /**
     * @return array{pf: float, esic: float} the statutory amounts for a structure
     */
    public static function forStructure(SalaryStructure $structure): array
    {
        return [
            'pf' => self::pf((float) $structure->basic_salary),
            'esic' => self::esic(self::monthlyGross($structure)),
        ];
    }
}
