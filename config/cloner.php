<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Persistence Strategies
    |--------------------------------------------------------------------------
    |
    | These relationship types and persistence strategies are those which
    | will be used at runtime to determine how to persist a particular
    | type of relationship.
    |
    */

    'persistence_strategies' => [
        Illuminate\Database\Eloquent\Relations\HasOne::class =>
            Anfischer\Cloner\Strategies\PersistHasOneRelationStrategy::class,
        Illuminate\Database\Eloquent\Relations\HasMany::class =>
            Anfischer\Cloner\Strategies\PersistHasManyRelationStrategy::class,
        Illuminate\Database\Eloquent\Relations\BelongsToMany::class =>
            Anfischer\Cloner\Strategies\PersistBelongsToManyRelationStrategy::class,
    ]
];
