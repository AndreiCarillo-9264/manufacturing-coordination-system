<?php

namespace App\Traits;

use Illuminate\Validation\Validator;
use Carbon\Carbon;

/**
 * Trait for validating that dates are not in the past
 * Prevents users from creating records with past dates
 */
trait ValidatesDateRestrictions
{
    /**
     * Extended validator to check if a date is not in the past
     * Usage: 'field_name' => 'required|date|no_past_date'
     */
    public function extendValidatorWithDateRestrictions()
    {
        \Illuminate\Support\Facades\Validator::extend('no_past_date', function ($attribute, $value, $parameters, Validator $validator) {
            if (!$value) {
                return true; // Allow empty values to be caught by 'required' rule
            }

            try {
                $date = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
                $today = Carbon::now()->startOfDay();

                if ($date < $today) {
                    return false; // Reject past dates
                }

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }, 'The :attribute must not be in the past.');

        \Illuminate\Support\Facades\Validator::extend('no_past_time', function ($attribute, $value, $parameters, Validator $validator) {
            if (!$value) {
                return true;
            }

            try {
                $dateField = $parameters[0] ?? null;
                $dateValue = $validator->getData()[$dateField] ?? null;

                if (!$dateValue) {
                    return true;
                }

                $selectedDate = Carbon::createFromFormat('Y-m-d', $dateValue)->startOfDay();
                $today = Carbon::now()->startOfDay();
                $time = Carbon::createFromFormat('H:i', $value);
                $now = Carbon::now();

                // If it's today, check that time is not in the past
                if ($selectedDate->isSameDay($today)) {
                    $selectedDateTime = $selectedDate->setHours($time->hour)->setMinutes($time->minute);
                    if ($selectedDateTime < $now) {
                        return false;
                    }
                }

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }, 'The :attribute must not be in the past for today.');
    }

    /**
     * Get validation messages for date restrictions
     */
    public function getDateRestrictionMessages(): array
    {
        return [
            'no_past_date' => 'The :attribute must be today or a future date.',
            'no_past_time' => 'The :attribute must not be in the past.',
        ];
    }
}
