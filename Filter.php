<?php

namespace App\Traits;

use Carbon\Carbon;

trait Filter
{
    public function scopeFilter($query, $searchQuery)
    {
        return $query->where(function ($query) use ($searchQuery) {
            $i = 0;
            foreach ($this->filters[controllerAction()] as $relation => $columns) {
                if ($relation == 'scope') {
                        $this->doQueryLoop($query, $columns, $searchQuery, $i, true);
                } elseif ($relation) {
                    $whereHas = $this->getStatement('whereHas', $i);
                    $query->{$whereHas}($relation, function ($q) use ($columns, $searchQuery, $i) {
                        $this->doQueryLoop($q, $columns, $searchQuery, $i);
                    });
                } else {
                    $this->doQueryLoop($query, $columns, $searchQuery, $i);
                }
                $i++;
            }
        });
    }

    private function doQueryLoop($q, $columns, $searchQuery, $i, $scope = false)
    {
        foreach ($columns as $key => $value) {
            $where = $this->getStatement('where', $i, $scope ? null : $key);
            $scope ? $q->{$value}($searchQuery, $where) : $q->{$where}($value, 'LIKE', '%'. $searchQuery . '%');
        }
    }

    private function getStatement($baseStatement, $i, $key = null)
    {
        if (($key !== null && $key > 0) || ($key === null && $i > 0)) {
            return 'or' . ucfirst($baseStatement);
        }

        return $baseStatement;
    }
}
