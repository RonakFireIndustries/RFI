<?php

namespace App\Services;

use App\Models\Site;

class SiteService
{
    public function listSites(array $filters = [], $perPage = 15)
    {
        $query = Site::query();

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function createSite(array $data): Site
    {
        return Site::create($data);
    }

    public function updateSite(Site $site, array $data): Site
    {
        $site->update($data);
        return $site;
    }

    public function deleteSite(Site $site): void
    {
        $site->delete();
    }
}
