<?php

namespace Zhiyi\Plus\Models\Concerns;

use Zhiyi\Plus\Models\User;
use Zhiyi\Plus\Models\Wallet;
use Illuminate\Support\Facades\Cache;

trait HasUserPaid
{
    // 发起支付节点人钱包.
    public function wallet()
    {
        return $this->hasManyThrough(Wallet::class, User::class, 'id', 'user_id', 'user_id');
    }

    /**
     * Paid node users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'paid_node_users', 'node_id', 'user_id');
    }

    /**
     * 判断是非对节点进行付费，支持过滤发布者.
     *
     * @param int $user User ID
     * @return bool
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function paid(int $user, bool $filter = true): bool
    {
        if ($filter === true && $this->user_id === $user) {
            return true;
        }

        $cacheKey = sprintf('paid:%s,%s', $this->id, $user);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $status = $this->users()->newPivotStatementForId($user)->first() !== null;
        Cache::forever($cacheKey, $status);

        return $status;
    }
}
