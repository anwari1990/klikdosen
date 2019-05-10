<?php

Route::post('follow', [
    'name' => 'api.follow',
    'callback' => 'FollowController@follow'
]);
Route::post('unfollow', [
    'name' => 'api.unfollow',
    'callback' => 'FollowController@unfoll'
]);
Route::get('search', [
    'name' => 'search.api',
    'callback' => 'SearchController@api'
]);
Route::delete('paper/{id}', [
    'name' => 'paper.delete.api',
    'callback' => 'PaperController@delete'
]);