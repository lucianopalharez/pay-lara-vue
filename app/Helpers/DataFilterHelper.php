<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class DataFilterHelper
{
    /**
     * Metodo para obter as colunas do banco para salvar.
     *
     * @param  Model $model
     * @param  array $data 
     * 
     * @return array
     */
    public static function filterData($model, $data): array
    {
        $table = $model->getTable();
        $fillableColumns = $model->getFillable();

        return array_filter(
            $data,
            function ($key) use ($fillableColumns) {
                return in_array($key, $fillableColumns);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
