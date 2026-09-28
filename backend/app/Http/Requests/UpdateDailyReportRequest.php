<?php

namespace App\Http\Requests;

use App\Support\ValidationSchemas;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ValidationSchemas::toLaravelRules(ValidationSchemas::dailyReport(), true);
    }
}