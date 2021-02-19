<?php

namespace App\Traits;

use Illuminate\Support\Arr;

trait HasFilter
{
    public function scopeFilter($query, $searchQuery, $filters)
    {
        $filters = $this->formatGivenFilters($filters);

        return $query->where(function ($query) use ($searchQuery, $filters) {
            $i = 0;

            foreach ($filters as $optionalRelationOrScope => $columns) {
                if ('scope' == $optionalRelationOrScope) {
                    $this->doQueryLoop($query, $columns, $searchQuery, $i, true);
                } elseif ($optionalRelationOrScope) {
                    $whereHas = $this->getStatement('whereHas', $i);
                    $query->{$whereHas}($optionalRelationOrScope, function ($q) use ($columns, $searchQuery, $i) {
                        $this->doQueryLoop($q, $columns, $searchQuery, $i);
                    });
                } else {
                    $this->doQueryLoop($query, $columns, $searchQuery, $i);
                }
                ++$i;
            }
        });
    }

    public function scopeSort($query, $sort, $direction)
    {
        $sortables = self::getSortables();
        if ($sort === null || !in_array($sort, array_keys($sortables))) {
            list($sort, $direction) = $this->defaultSort;
        }

        $query->orderBy($sortables[$sort], $direction);


        return $query;
    }

    public static function getSortables()
    {
        return self::$sortables;
    }

    private function doQueryLoop($q, $columns, $searchQuery, $i, $scope = false)
    {
        foreach ($columns as $key => $value) {
            $where = $this->getStatement('where', $i, $scope ? null : $key);
            $scope ? $q->{$value}($searchQuery, $where) : $q->{$where}($value, 'LIKE', '%' . $searchQuery . '%');
        }
    }

    private function getStatement($baseStatement, $i, $key = null)
    {
        if ((null !== $key && $key > 0) || (null === $key && $i > 0)) {
            return 'or' . ucfirst($baseStatement);
        }

        return $baseStatement;
    }

    private function formatGivenFilters($filters)
    {
        $myOwnColumns = array_filter($filters, function ($key) {
            return is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);

        $filtersWithoutMyOwnColumns = array_filter($filters, function ($key) {
            return !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);

        $this->filters = array_merge([null => $myOwnColumns], $filtersWithoutMyOwnColumns);

        return $this->filters;
    }
}
