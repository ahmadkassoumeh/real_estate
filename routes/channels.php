<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{a}.{b}', function ($user, $a, $b) {
    return in_array($user->id, [$a, $b]);
});
