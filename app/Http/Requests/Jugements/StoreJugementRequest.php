<?php
// app/Http/Requests/Jugements/StoreJugementRequest.php

namespace App\Http\Requests\Jugements;

use Illuminate\Foundation\Http\FormRequest;

class StoreJugementRequest extends FormRequest
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
        return [
            'id_dossier_tribunal.required' => 'Veuillez sélectionner un dossier/tribunal.',
            'id_juge.required'             => 'Le juge est obligatoire.',
            'date_jugement.required'       => 'La date du jugement est obligatoire.',
            'date_jugement.before_or_equal'=> 'La date du jugement ne peut pas être dans le futur.',
            'contenu_dispositif.required'  => 'Le dispositif du jugement est obligatoire.',
            'contenu_dispositif.min'       => 'Le dispositif doit contenir au moins 10 caractères.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_definitif' => $this->boolean('est_definitif'),
        ]);
    }
}
