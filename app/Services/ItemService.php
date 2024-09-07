<?php
namespace App\Services;

use App\Models\Item;

class ItemService{

    public function checkOwnItem(int $userId, int $itemId):bool
    {
        $item = Item::where('id', $itemId)->firstOrFail();

        return $item->user_id === $userId;
    }
}