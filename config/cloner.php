<?php

return [
    'persistence_strategies' => [
        Illuminate\Database\Eloquent\Relations\HasOne::class =>
            Anfischer\Cloner\Strategies\PersistHasOneRelationStrategy::class,
        Illuminate\Database\Eloquent\Relations\HasMany::class =>
            Anfischer\Cloner\Strategies\PersistHasManyRelationStrategy::class,
        Illuminate\Database\Eloquent\Relations\BelongsToMany::class =>
            Anfischer\Cloner\Strategies\PersistBelongsToManyRelationStrategy::class,
    ]
];
