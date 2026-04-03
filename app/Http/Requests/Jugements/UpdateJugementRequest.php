<?php
// app/Http/Requests/Jugements/UpdateJugementRequest.php

namespace App\Http\Requests\Jugements;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJugementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_dossier_tribunal' => 'required|exists:dossier_tribunaux,id',
            'id_juge'             => 'required|exists:juges,id',
            'date_jugement'       => 'required|date|before_or_equal:today',
            'contenu_dispositif'  => 'required|string|min:10',
            'est_definitif'       => 'boolean',
            'parties'             => 'nullable|array',
            'parties.*'           => 'exists:parties,id',
            'montants'            => 'nullable|array',
            'montants.*'          => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return (new StoreJugementRequest())->messages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_definitif' => $this->boolean('est_definitif'),
        ]);
    }
}
