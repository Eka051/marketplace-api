<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\ShopRepositoryInterface;
use App\Models\Shop;

class ShopRepository implements ShopRepositoryInterface
{
    public function searchShops(string $query, int $perPage = 10)
    {
        return Shop::search($query)->query(fn($q) => $q->with('owner'))->paginate($perPage);
    }

    public function getById(string $id)
    {
        return Shop::with('owner')->findOrFail($id);
    }

    public function createShop(array $data)
    {
        return Shop::create($data);
    }

    public function updateShop(string $id, array $data)
    {
        $shop = Shop::findOrFail($id);
        $shop->update($data);
        return $shop;
    }

    public function deleteShop(string $id)
    {
        $shop = Shop::findOrFail($id);
        return $shop->delete();
    }
}
