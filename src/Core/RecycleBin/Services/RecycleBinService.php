<?php

namespace GetCandy\Api\Core\RecycleBin\Services;

use GetCandy\Api\Core\RecycleBin\Interfaces\RecycleBinServiceInterface;
use GetCandy\Api\Core\RecycleBin\Models\RecycleBin;

class RecycleBinService implements RecycleBinServiceInterface
{
    /**
     * Gets items that are currently soft deleted
     *
     * @return void
     */
    public function getItems($paginated = true, $perPage = 25, $terms = null)
    {
        $query = RecycleBin::with('recyclable');
        if (!$paginated) {
            return $query->get();
        }
        return $query->paginate($perPage);
    }

    public function findById($id)
    {
        return RecycleBin::findOrFail($id);
    }

    public function forceDelete($id)
    {
        $item = $this->findById($id);
        if (!$item->recyclable) {
            $item->delete();
        } else {
            $item->recyclable->forceDelete();
        }
    }
}