<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return in_array($user->role, ['admin', 'director','subdireccion']);
    }

    public function rules(): array
        {
            return [
                'nombre' => ['required', 'string', 'max:255'],
                'categoria' => ['nullable', 'string', 'max:100'],

                'archivo' => [
                    'required',
                    'file',
                    'mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,txt,jpg,png',
                    'max:10240'
                ],
            ];
        }}
