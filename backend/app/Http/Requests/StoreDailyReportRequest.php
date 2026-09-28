<?php

namespace App\Http\Requests;

use App\Support\ValidationSchemas;
use Illuminate\Foundation\Http\FormRequest;

class StoreDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ValidationSchemas::toLaravelRules(ValidationSchemas::dailyReport());
    }
}